# Laravel 7 → 13 Upgrade & Code-Quality Guide

Written against the actual state of this repo on 2026-08-03 (branch `staging`).
Every version number below was verified against Packagist / the official Laravel upgrade docs at that date.

---

## 0. Where you are today

| | Current | Target |
|---|---|---|
| Laravel | 7.x | **13.x** (latest = 13.23) |
| PHP | 7.4.33 | **8.3** (8.4 also OK; 8.3/8.4 already installed on this box) |
| Node | 16.14 | **20 LTS** |
| Build | laravel-mix 5 / webpack 4 | laravel-mix 6 (**stay on Mix, do not move to Vite yet**) |
| Frontend | Vue 2.6 + Element-UI + CKEditor 5 + Blade | **unchanged** |
| Modules | `caffeinated/modules` 6.2 (**dead package**) | plain Laravel service providers |
| Tests | 2 stub tests, zero real coverage | characterization tests before you start |

Codebase size: 389 PHP files in `app/`, 107 Blade views, 109 `.vue` components, 3 modules (`Admin`, `Asset`, `Lead`), 27 controllers.

Two facts that make this much less scary than it looks:

1. **All your PHP files already parse cleanly under PHP 8.3.** I linted every file in `app/`, `config/`, `routes/`, `database/`, `tests/` with `php8.3 -l` — zero syntax errors. You have no PHP-8 syntax blockers, only runtime/API blockers.
2. **Every third-party package except one has a Laravel 13-compatible release.** See §3.

---

## 1. Strategy

### 1.1 Your frontend will not break — here's why

The single biggest fear in a Laravel upgrade this size is "the admin panel stops working." Structurally, it can't, for one reason: **Laravel does not own your frontend.** Your UI is Blade templates that mount Vue 2 components against bundles built by Laravel Mix. The framework's only contact points with that layer are:

* `mix()` in `app/Modules/Admin/Resources/Views/layouts/layout.blade.php:42` — the helper still exists in Laravel 13, unchanged.
* Blade syntax — `{{ }}`, `@if`, `@foreach`, `@include` are all still identical.
* The CSRF token meta tag that `resources/js/bootstrap.js` reads into the axios default header.
* JSON responses from your controllers.

So the rule for this whole project is:

> **Rule 1 — Do not touch `resources/js/`, `*.vue`, or the Blade markup during the framework upgrade.**
> Vue 2 → Vue 3 and Mix → Vite are separate projects with separate risk. Bundling them into the Laravel upgrade is how these migrations die.

The only frontend work that is genuinely *required* is `laravel-mix 5 → 6` (§5), because Mix 5 pins webpack 4, which cannot build under modern Node. That's a build-tool change, not an application change — your component code is untouched.

There are exactly **two** places where the backend upgrade can visibly affect the frontend, and both are listed with fixes:

* **Laravel 13 renames the CSRF middleware and adds `Sec-Fetch-Site` origin checking** (§4.7). This is the one change that can silently break axios POSTs. Read that section carefully.
* **Laravel 12 changed nested-array request merging and route precedence** (§4.6), which can affect how your filter forms arrive at the controller.

### 1.2 Go one major at a time

Do **not** put `"laravel/framework": "^13.0"` in `composer.json` and hope. Six majors of behavioural change land at once and you'll have no idea which one broke what.

```
7 → 8 → [replace caffeinated] → 9 → 10 → 11 → 12 → 13
```

One major per branch, per PR, per deploy-to-staging. Official estimated times are 15 / 30 / 10 / 15 / 5 / 10 minutes respectively — for a *clean* app. Budget realistically:

| Step | Real effort for this repo | Risk |
|---|---|---|
| 7 → 8 | half a day | low |
| replace `caffeinated/modules` | half a day | **medium — do it here, not later** |
| 8 → 9 | 2–3 days | **high** (PHP 8, Symfony 6, Flysystem 3, Symfony Mailer) |
| 9 → 10 | half a day | low |
| 10 → 11 | 1–2 days | medium (Carbon 3, DBAL removal) |
| 11 → 12 | 2 hours | low |
| 12 → 13 | half a day | medium (CSRF middleware) |

### 1.3 The one real blocker

`caffeinated/modules` is abandoned. Its last release supports Laravel 6/7 and there is no path forward. This is the only package with no upgrade target.

**Good news:** I grepped the entire codebase. It is referenced in exactly **4 places**:

```
app/Modules/Admin/Providers/ModuleServiceProvider.php:15
app/Modules/Asset/Providers/ModuleServiceProvider.php:5
app/Modules/Lead/Providers/ModuleServiceProvider.php:5
config/modules.php:81
```

…plus the `module_path()` helper it provides, used inside the three `RouteServiceProvider`s. That is a half-day of work to remove entirely (§4.2). **Do not** replace it with `nwidart/laravel-modules` — that swaps one dependency for another and forces a directory restructure. Your modules are just namespaced folders; plain service providers do the job.

---

## 2. Before you change a single dependency

This is the part people skip and then regret. You have **no test coverage** — `tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php` are the Laravel stubs. Upgrading 6 majors with no safety net across an app that handles assets, payments, revenue and investor data is not a good trade.

### 2.1 Write characterization tests first (1–2 days, non-negotiable)

You don't need real unit tests. You need *smoke tests that fail loudly if a page 500s*. Aim for one test per controller route that renders or returns JSON:

```php
// tests/Feature/Smoke/AdminPagesTest.php
public function test_admin_pages_render(): void
{
    $admin = Admin::factory()->create();          // or a seeded fixture
    $admin->assignRole('administrator');

    foreach ([
        '/admin/asset', '/admin/asset/create', '/admin/revenue',
        '/admin/investor', '/admin/lead', '/admin/news',
        '/admin/user', '/admin/role', '/admin/sale',
    ] as $url) {
        $this->actingAs($admin, 'admin')->get($url)->assertSuccessful();
    }
}
```

Thirty of these will catch ~80% of what an upgrade breaks. Add JSON-shape assertions for the endpoints your Vue components call — those are the ones whose breakage is invisible until a user complains.

> **`phpunit.xml` bug (fix now, before writing tests):** it sets `DB_CONNECTION=sqlite` / `:memory:`, but your code uses MySQL-only SQL — `orderByRaw('ISNULL(area), area ASC')` at `AssetController.php:250,252,473,475`, plus `GROUP BY` queries whose behaviour depends on MySQL's `sql_mode`. Those tests would fail on SQLite for reasons unrelated to the upgrade.
>
> **Decision taken:** point the suite at a real MySQL database (`key_assets_test`) rather than rewriting working SQL for portability's sake. `phpunit.xml` now overrides only `DB_DATABASE`, so credentials keep coming from the gitignored `.env`. Recreate the test database with:
> ```bash
> mysql -e "DROP DATABASE IF EXISTS key_assets_test; CREATE DATABASE key_assets_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
> mysqldump key_assets | mysql key_assets_test
> DB_DATABASE=key_assets_test php artisan migrate --force
> DB_DATABASE=key_assets_test php artisan db:seed --class="App\Modules\Admin\Database\Seeds\PermissionSeeder" --force
> DB_DATABASE=key_assets_test php artisan db:seed --class="App\Modules\Admin\Database\Seeds\RoleSeeder" --force
> ```
> Testing against MySQL with a clone of real data is also strictly better coverage: it is what surfaced the four pre-existing bugs in §6.0.

### 2.2 The rest of the prep checklist

```bash
git checkout -b upgrade/laravel-8 staging
mysqldump ... > backup-pre-upgrade.sql      # and verify you can restore it
composer install && npm ci                  # known-good baseline
```

* Snapshot the current UI: screenshot every admin page, or at minimum the asset index, asset form, revenue view, and investor list. Your visual regression check for the whole project.
* Record the current `composer.lock` — it's your rollback.
* Set up a staging environment that mirrors production PHP/MySQL versions. Never validate an upgrade step only on localhost.
* Fix `composer.json`: `"minimum-stability": "dev"` should be `"stable"`. It is currently letting unstable package versions resolve into your lock file, which will make every upgrade step noisier than it needs to be.

### 2.3 Install the tooling that does the mechanical work

```bash
composer require --dev rector/rector driftingly/rector-laravel
composer require --dev larastan/larastan
composer require --dev laravel/pint
```

**Rector** automates the majority of the syntax-level migration (PHP 7.4 → 8.3 idioms, deprecated Laravel APIs). **Larastan** (PHPStan for Laravel) finds calls to methods that no longer exist — which is precisely the failure mode of a multi-major upgrade. **Pint** normalises style so upgrade diffs stay readable.

`rector.php`:

```php
<?php
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use RectorLaravel\Set\LaravelLevelSetList;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/app', __DIR__ . '/config', __DIR__ . '/database', __DIR__ . '/routes', __DIR__ . '/tests'])
    ->withPhpSets(php83: true)
    ->withSets([LaravelLevelSetList::UP_TO_LARAVEL_130])
    ->withImportNames(removeUnusedImports: true);
```

Run it **one level at a time, in step with the framework version** — not all at once at the end. `vendor/bin/rector --dry-run` first, always.

Also consider [Laravel Shift](https://laravelshift.com) (paid, ~$29–$49 per major). For a 6-major jump on a real codebase it usually pays for itself on the 8→9 step alone.

---

## 3. Dependency map (verified on Packagist, 2026-08-03)

| Package | You have | Laravel 13-compatible | Notes |
|---|---|---|---|
| `laravel/framework` | ^7.0 | **^13.0** | requires PHP ^8.3 |
| `laravel/tinker` | ^2.0 | **^3.0** | |
| `caffeinated/modules` | ^6.2 | ❌ **none** | remove — see §4.2 |
| `barryvdh/laravel-debugbar` | ^3.3 | ^4.4 | move to `require-dev` while you're in there |
| `barryvdh/laravel-dompdf` | ^0.8.6 | ^3.1 | **API change**: `Barryvdh\DomPDF\Facade` → `Barryvdh\DomPDF\Facade\Pdf` |
| `barryvdh/laravel-translation-manager` | ^0.5.8 | ^0.6.9 | |
| `doctrine/dbal` | ^2.10 | — | **delete it** at step 10→11; Laravel 11 modifies columns natively |
| `fideloper/proxy` | ^4.0 | — | **delete it** at step 8→9; replaced by `Illuminate\Http\Middleware\TrustProxies` |
| `guzzlehttp/guzzle` | ^6.5 | ^7.10 | required by L9+ |
| `intervention/image` | ^2.5 | ^4.2 | **full API rewrite** — see §4.4 |
| `kalnoy/nestedset` | ^5.0 | ^7.0 | |
| `maatwebsite/excel` | ^3.1 | ^3.1.69 | same major — painless |
| `owen-it/laravel-auditing` | ^10.0 | ^14.0 | read its own UPGRADE.md; config keys moved |
| `psr/simple-cache` | ^1.0 | ^3.0 | hard conflict with L11+ if left pinned |
| `rap2hpoutre/laravel-log-viewer` | ^1.6 | ^3.1 | |
| `spatie/laravel-permission` | ^3.13 | ^8.3 | **5 majors** — read §4.3 |
| `google/recaptcha` | ^1.2 | ^1.3 | fine |
| `facade/ignition` (dev) | ^2.0 | → `spatie/laravel-ignition ^2.0` | swap at 8→9 |
| `fzaninotto/faker` (dev) | ^1.9 | → `fakerphp/faker ^1.23` | abandoned; swap at 7→8 |
| `laravel/ui` (dev) | ^2.0 | ^4.6 | |
| `nunomaduro/collision` (dev) | ^4.1 | ^8.9 | |
| `phpunit/phpunit` (dev) | ^8.5 | ^12.0 | config schema changes at 9.x and 10.x |

---

## 4. The upgrade, step by step

Each step: branch → change deps → apply the code changes → `composer update` → run tests → click through the admin panel → merge → deploy to staging → **let it sit a day** → next step.

### 4.0 Step 0 — switch local PHP to 8.3 last, not first

Laravel 7 technically allows PHP 8.0, but don't. Run steps 7→8 on PHP 7.4, then move to PHP 8.0+ as part of 8→9 where it's mandatory. Your CLI already has `php8.1` through `php8.4` available, so you can pin per-step:

```bash
php8.1 /usr/local/bin/composer update    # during the 9→10 step, etc.
```

### 4.1 Step 1 — Laravel 7 → 8

Docs: `https://laravel.com/docs/8.x/upgrade`

**composer.json:**
```jsonc
"laravel/framework": "^8.0",
"guzzlehttp/guzzle": "^7.0.1",
"facade/ignition": "^2.5",
"nunomaduro/collision": "^5.0",
"phpunit/phpunit": "^9.3",
"laravel/ui": "^3.0",
// remove "fzaninotto/faker", add:
"fakerphp/faker": "^1.9.1",
```

**Code changes required in this repo:**

1. **Seeders → PSR-4.** You have `database/seeds/DatabaseSeeder.php` plus module seeders in `app/Modules/Admin/Database/Seeds/` (`PermissionSeeder`, `RoleSeeder`, `AdminSeeder`, `CountrySeeder`). Rename `database/seeds` → `database/seeders`, add `namespace Database\Seeders;`, and namespace the module seeders (`App\Modules\Admin\Database\Seeds`). Then drop the `classmap` from `composer.json`:

   ```jsonc
   "autoload": {
       "files": ["app/helpers.php"],
       "psr-4": {
           "App\\": "app/",
           "Database\\Factories\\": "database/factories/",
           "Database\\Seeders\\": "database/seeders/"
       }
   }
   ```
   Then `composer dump-autoload`.

2. **Factories → class-based.** You have exactly one: `database/factories/UserFactory.php`, still using `$factory->define(User::class, ...)`. Convert it:

   ```php
   <?php
   namespace Database\Factories;

   use App\User;
   use Illuminate\Database\Eloquent\Factories\Factory;

   class UserFactory extends Factory
   {
       protected $model = User::class;

       public function definition(): array
       {
           return [
               'name'              => $this->faker->name(),
               'email'             => $this->faker->unique()->safeEmail(),
               'email_verified_at' => now(),
               'password'          => bcrypt('password'),
               'remember_token'    => Str::random(10),
           ];
       }
   }
   ```
   Add `use HasFactory;` to any model you want `Model::factory()` on. (You'll want this for the smoke tests from §2.1.)

3. **Maintenance-mode middleware rename.** `app/Http/Kernel.php:19` references `\App\Http\Middleware\CheckForMaintenanceMode::class`. Rename the class and file to `PreventRequestsDuringMaintenance` and have it extend `Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance`.

4. **Controller namespace prefixing.** Laravel 8 removed the automatic `$namespace` prefix from the *default* `RouteServiceProvider`. Your module route providers (`app/Modules/*/Providers/RouteServiceProvider.php`) set `'namespace' => $this->namespace` explicitly inside `Route::group()` — **that still works and still works in Laravel 13.** Leave them alone. Just confirm `app/Providers/RouteServiceProvider.php` keeps its `protected $namespace` property if `routes/web.php` uses string controller references.

**Verify:** `php artisan route:list` returns the same route count as before. Run your smoke tests. Click through every admin page.

### 4.2 Step 2 — Remove `caffeinated/modules` (do this now, on Laravel 8)

Do it here, while you're still on a Laravel version the package supports, so you can verify the swap in isolation. If you leave it for the 8→9 step you'll be debugging two things at once.

**a) Add a `module_path()` shim** to `app/helpers.php` so all existing call sites keep working unchanged:

```php
if (! function_exists('module_path')) {
    /**
     * Resolve a path inside app/Modules. Drop-in replacement for the
     * helper formerly provided by caffeinated/modules.
     *
     * @param  string  $slug      module slug, e.g. 'admin'
     * @param  string  $file      path within the module, e.g. 'Routes/web.php'
     * @param  string|null $location  ignored; kept for call-site compatibility
     */
    function module_path(string $slug, string $file = '', ?string $location = null): string
    {
        $base = app_path('Modules/' . \Illuminate\Support\Str::studly($slug));

        return $file === '' ? $base : $base . '/' . ltrim($file, '/');
    }
}
```

That single function keeps all three `RouteServiceProvider`s working with zero edits.

**b) Rewrite the three `ModuleServiceProvider`s** to extend Laravel's own provider. Here's `app/Modules/Admin/Providers/ModuleServiceProvider.php` — the other two are simpler versions of the same shape:

```php
<?php

namespace App\Modules\Admin\Providers;

use App\Modules\Admin\Repositories\Contracts\IAdminRepository;
use App\Modules\Admin\Repositories\Contracts\IFileRepository;
use App\Modules\Admin\Repositories\Contracts\IPermissionRepository;
use App\Modules\Admin\Repositories\Contracts\IRoleRepository;
use App\Modules\Admin\Repositories\Contracts\ITextRepository;
use App\Modules\Admin\Repositories\Eloquent\AdminRepository;
use App\Modules\Admin\Repositories\Eloquent\FileRepository;
use App\Modules\Admin\Repositories\Eloquent\PermissionRepository;
use App\Modules\Admin\Repositories\Eloquent\RoleRepository;
use App\Modules\Admin\Repositories\Eloquent\TextRepository;
use Illuminate\Support\ServiceProvider;   // <- was Caffeinated\Modules\Support\ServiceProvider

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(AdminModuleServiceProvider::class);

        $this->bindRepositories();
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(module_path('admin', 'Resources/Lang'), 'admin');
        $this->loadViewsFrom(module_path('admin', 'Resources/Views'), 'admin');
        $this->loadMigrationsFrom(module_path('admin', 'Database/Migrations'));
    }

    private function bindRepositories(): void
    {
        $this->app->bind(IPermissionRepository::class, PermissionRepository::class);
        $this->app->bind(IAdminRepository::class, AdminRepository::class);
        $this->app->bind(IRoleRepository::class, RoleRepository::class);
        $this->app->bind(ITextRepository::class, TextRepository::class);
        $this->app->bind(IFileRepository::class, FileRepository::class);
    }
}
```

Note what's gone: `loadConfigsFrom()` and `loadFactoriesFrom()` were Caffeinated-only methods, and both target directories (`app/Modules/Admin/Config/`, `app/Modules/Admin/Database/Factories/`) are **empty** in this repo, so dropping them is a no-op. If you ever add module configs, use Laravel's `mergeConfigFrom($path, $key)`.

**c) Register the providers explicitly** in `config/app.php`, in the `Application Service Providers` block (after `App\Providers\RouteServiceProvider::class`, around line 198):

```php
App\Modules\Admin\Providers\ModuleServiceProvider::class,
App\Modules\Asset\Providers\ModuleServiceProvider::class,
App\Modules\Lead\Providers\ModuleServiceProvider::class,
```

Order matters if one module's bindings are consumed by another during `register()`. `Admin` first is the safe default here.

**d) Clean up:**
```bash
composer remove caffeinated/modules
rm config/modules.php
rm app/Modules/*/module.json     # only after confirming nothing reads them
```

**e) Verify before moving on:**
```bash
grep -rn "Caffeinated\|modules(" --include=*.php app config routes    # must return nothing
php artisan route:list | wc -l                                        # same count as before
php artisan view:clear && php artisan config:clear
```
Then click through pages from all three modules. Views resolve via the `admin::`/`asset::`/`lead::` namespaces — if a view 404s, `loadViewsFrom` got the wrong path.

### 4.3 Step 3 — Laravel 8 → 9 (the hard one)

Docs: `https://laravel.com/docs/9.x/upgrade`

This step carries most of the total risk. Budget 2–3 days and do nothing else in the branch.

**PHP 8.0.2 minimum.** Switch your local and staging PHP here.

**composer.json:**
```jsonc
"php": "^8.0.2",
"laravel/framework": "^9.0",
"nunomaduro/collision": "^6.1",
"guzzlehttp/guzzle": "^7.2",
// remove "fideloper/proxy"    -> now built into the framework
// remove "facade/ignition"    -> replace with:
"spatie/laravel-ignition": "^1.0",
```

**Code changes required in this repo:**

1. **`TrustProxies`** — `app/Http/Middleware/TrustProxies.php:5` imports `Fideloper\Proxy\TrustProxies`. Change to:
   ```php
   use Illuminate\Http\Middleware\TrustProxies as Middleware;
   ```
   and update `$headers` to the `Request::HEADER_X_FORWARDED_*` bitmask constants per the L9 skeleton.

2. **Mail: SwiftMailer → Symfony Mailer.** Your `.env` uses `MAIL_DRIVER=smtp` — Laravel 9 reads **`MAIL_MAILER`**. Update `.env`, `.env.example`, and any deployment/CI env templates. Grep `app/Mail/` for `->getSwiftMessage()` or `Swift_` (I found none, so this should be config-only — but re-check after the update).

3. **Flysystem 3.** Behaviour changes that matter for your file uploads (`app/Modules/Admin/Repositories/Eloquent/FileRepository.php`): writes now overwrite by default, reading a missing file returns `false` instead of throwing, and `delete()` on a missing file returns `true`. Audit every `Storage::` call for code that depended on the old exceptions.

4. **`FILESYSTEM_DRIVER` → `FILESYSTEM_DISK`** in `.env` and `config/filesystems.php`.

5. **Spatie Permission 3 → 5.** Do this in the same PR (v4/v5 are the L9-compatible line). Read `https://spatie.be/docs/laravel-permission/v5/upgrading`. Key points for your code: the config file gained keys, the cache key changed, and `Role`/`Permission` model resolution moved. Your usages are concentrated in `app/Modules/Admin/Database/Seeds/RoleSeeder.php`, `app/Modules/Admin/Repositories/Eloquent/RoleRepository.php` and `app/Modules/Admin/Http/Controllers/User/UserController.php`, and you use multiple guards (`admin`) — republish the config, diff it against yours, and **run the permission seeders on a scratch database first**. Take spatie to `^8.0` later, at the Laravel 11 step, in its own commit.

6. Run `vendor/bin/rector` with `LaravelLevelSetList::UP_TO_LARAVEL_90` and `withPhpSets(php80: true)`.

**Verify:** this is where you spend the most time clicking. Prioritise: file/image upload, PDF generation (dompdf), Excel export (`app/Modules/Admin/Exports/AssetExport.php`, `RevenueExport.php`), mail sending, and every permission-gated page.

### 4.4 Step 4 — Laravel 9 → 10

Docs: `https://laravel.com/docs/10.x/upgrade`

PHP 8.1 minimum. Mostly mechanical.

**composer.json:** `"laravel/framework": "^10.0"`, `"phpunit/phpunit": "^10.0"`, `"nunomaduro/collision": "^7.0"`, `"laravel/ui": "^4.0"`, `"spatie/laravel-ignition": "^2.0"`, `"php": "^8.1"`. Also set `"minimum-stability": "stable"` now if you haven't.

**Code changes required in this repo:**

1. **`$dates` removed — must fix.** Three files use it:
   * `app/InvestorNewsRead.php:17`
   * `app/Modules/Admin/Models/InvestorNewsRead.php:17`
   * `app/Modules/Admin/Models/Statics/File.php:69`

   Move each entry into `$casts`:
   ```php
   // before
   protected $dates = ['deleted_at', 'read_at'];
   // after
   protected $casts = ['deleted_at' => 'datetime', 'read_at' => 'datetime'];
   ```
   This is silent breakage if missed — the attributes just stop being Carbon instances, and `->toDateString()` calls in Blade start throwing on strings.

   > Side note: `app/InvestorNewsRead.php` and `app/Modules/Admin/Models/InvestorNewsRead.php` look like a duplicated model. Confirm which one is actually referenced and delete the other (§6).

2. **Language directory.** `resources/lang` → `lang/` at the project root. Note `.gitignore` currently ignores `/resources/lang` — update that entry to `/lang` or you'll silently keep ignoring your translations. (Your translation-manager package also writes there — check `app/Modules/Admin/Utilities/LangFiles.php`.)

3. **Middleware aliases** moved from `$routeMiddleware` to `$middlewareAliases` in `app/Http/Kernel.php`.

4. **Monolog 3** — only matters if you have custom log formatters. `config/logging.php` looks standard; verify.

5. **`Bus::dispatchNow`** removed → `dispatchSync`. Grep for it.

Run Rector at `UP_TO_LARAVEL_100` + `php81`.

### 4.5 Step 5 — Laravel 10 → 11

Docs: `https://laravel.com/docs/11.x/upgrade`

PHP 8.2 minimum.

> **Read this before anything else:** Laravel 11 introduced a new slim application skeleton (no `app/Http/Kernel.php`, no `config/*` by default, `bootstrap/app.php` does everything). The official docs say explicitly: *"we do **not recommend** that Laravel 10 applications upgrading to Laravel 11 attempt to migrate their application structure."* **Keep your existing structure.** Laravel 11, 12 and 13 all fully support it. Restructuring is a separate, optional, later project.

**composer.json:** `"laravel/framework": "^11.0"`, `"php": "^8.2"`, `"phpunit/phpunit": "^11.0"`, `"nunomaduro/collision": "^8.1"`, and **bump `"psr/simple-cache"` to `^3.0` or just remove the explicit requirement** — leaving it at `^1.0` produces an unresolvable conflict.

**Code changes required in this repo:**

1. **Remove `doctrine/dbal`.** Laravel 11 changes columns natively. Delete it from `composer.json`. Then check every migration that uses `->change()` — the new implementation preserves *only the attributes you explicitly list*, so `$table->string('name')->change()` on a column that was nullable will **drop the nullable flag**. You have migrations doing exactly this:
   * `app/Modules/Admin/Database/Migrations/2025_06_05_174853_change_id_to_nullable.php`
   * `app/Modules/Admin/Database/Migrations/2025_06_05_191526_change_developer_fields_to_nullable.php`
   * `app/Modules/Admin/Database/Migrations/2025_08_04_171444_make_name_nullable_for_all_admin_users.php`

   These have already run in production, so they won't re-execute — but if you ever rebuild from scratch (including in CI), re-check each `->change()` call restates *every* attribute (`->nullable()->default(...)->unsigned()`).

2. **Carbon 3.** Date-heavy code is a real exposure for you — `AssetController::generatePaymentsList()`, the payment/rental/renovation schedule logic, and `explode(',', $request->payment_date)` parsing throughout. Carbon 3 tightened parsing and changed some diff/floor behaviour. Test payment-schedule generation deliberately.

3. **Model `casts()` method.** Optional, but if you convert `protected $casts = []` to `protected function casts(): array` anywhere, do it consistently.

4. **Spatie Permission → `^8.0`** here (its own commit, own testing pass).

Run Rector at `UP_TO_LARAVEL_110` + `php82`.

### 4.6 Step 6 — Laravel 11 → 12

Docs: `https://laravel.com/docs/12.x/upgrade`

Genuinely small — the official estimate of 5 minutes is close to honest.

**composer.json:** `"laravel/framework": "^12.0"`, `"phpunit/phpunit": "^11.0"`.

**Watch for, in this repo:**

* **Nested array request merging** changed. Your filter forms (`AssetFilters.vue`, `RevenueFilters.vue`, `LeadFilters.vue`) post nested arrays; if any controller uses `$request->merge()` with nested data, re-verify.
* **Route precedence** changed for overlapping routes. You have a lot of routes in `app/Modules/Asset/Routes/asset.php` (14 KB). Diff `php artisan route:list` before and after.
* **Image validation now excludes SVG** by default. If any upload accepts SVG, add `->allowSvg()` to the rule.
* **Local disk root path** default changed — check `config/filesystems.php` if you rely on the default.

### 4.7 Step 7 — Laravel 12 → 13 (the frontend-relevant one)

Docs: `https://laravel.com/docs/13.x/upgrade`

PHP 8.3 minimum.

**composer.json:** `"laravel/framework": "^13.0"`, `"laravel/tinker": "^3.0"`, `"phpunit/phpunit": "^12.0"`, `"php": "^8.3"`.

**CSRF middleware rename — verified safe for your frontend:**

> Laravel's CSRF middleware has been renamed from `VerifyCsrfToken` to `PreventRequestForgery`, and now includes request-origin verification using the `Sec-Fetch-Site` header.

I originally flagged the `Sec-Fetch-Site` check as the main risk to your Vue/axios calls. Reading the shipped implementation, that is **wrong** — worth correcting, because it changes what you need to watch for. `Illuminate\Foundation\Http\Middleware\PreventRequestForgery::handle()` is:

```php
if (
    $this->isReading($request) ||
    $this->runningUnitTests() ||
    $this->inExceptArray($request) ||
    $this->hasValidOrigin($request) ||   // <- new, OR'd in
    $this->tokensMatch($request)         // <- the classic check, unchanged
) { ... }
```

`hasValidOrigin()` is an **additional way to pass**, not an additional requirement. A request with `Sec-Fetch-Site: same-origin` short-circuits to success; anything else simply falls through to the same token comparison as before. The strict mode that *would* reject on origin alone is `PreventRequestForgery::useOriginOnly()`, and `$originOnly` defaults to `false` — nothing in this codebase enables it.

**So the change is strictly more permissive for you.** Your axios setup in `resources/js/bootstrap.js` keeps working unchanged, and there is no new failure mode behind a reverse proxy or in an iframe.

What was actually required: `app/Http/Middleware/VerifyCsrfToken.php` renamed to `PreventRequestForgery.php` extending the new base class, with `$addHttpCookie` and `$except` carried over, and the alias in `app/Http/Kernel.php` updated. (`VerifyCsrfToken` survives as a deprecated alias, so leaving it would have worked too — but it is scheduled for removal.)

Note that CSRF cannot be covered by the PHPUnit suite: `runningUnitTests()` short-circuits the middleware in the test environment. Verify the heaviest POST flows manually after deploying — asset form submit, lead import, save project details.

**Other L13 changes worth checking here:**
* `Js::from` now emits unescaped Unicode by default — relevant if you pass data to Vue via `@json`/`Js::from` in Blade. Your Blade views do embed data into Vue props; spot-check a page with non-ASCII content (you have a translation manager, so this is a live concern).
* Bootstrap pagination view names changed — you're on Bootstrap 4, so check any `->links('pagination::bootstrap-4')` calls.
* Database `upsert` behaviour changed on MySQL/MariaDB. Grep for `upsert(`.

There's also a first-party AI-assisted path: install `laravel/boost ^2.0` and run `/upgrade-laravel-v13` in Claude Code. Useful for this last step specifically.

---

## 5. Frontend & build toolchain

Do this **as its own PR**, either before step 1 or after step 7 — never in the middle.

### 5.1 What must change

Only the build tooling. Mix 5 pins webpack 4, which won't run on modern Node.

```jsonc
// package.json
"devDependencies": {
    "laravel-mix": "^6.0.49",
    "sass": "^1.77.0",
    "sass-loader": "^12.1.0",
    "resolve-url-loader": "^5.0.0",
    "postcss": "^8.4.0",
    "vue-loader": "^15.9.7",
    "vue-template-compiler": "^2.6.14",
    "vue": "^2.7.16",
    // ...keep axios/bootstrap/jquery/popper/lodash as-is
}
```

Then simplify the scripts (Mix 6 ships its own CLI):

```jsonc
"scripts": {
    "dev": "mix",
    "watch": "mix watch",
    "watch-poll": "mix watch -- --watch-options-poll=1000",
    "hot": "mix watch --hot",
    "prod": "mix --production"
}
```

`webpack.mix.js` needs one addition for Vue 2 under Mix 6 — Mix 6 no longer assumes Vue:

```js
const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js').vue({ version: 2 })
   .js('resources/js/admin.js', 'public/js').vue({ version: 2 })
   .sass('resources/sass/app.scss', 'public/css')
   .sass('resources/sass/admin.scss', 'public/css')
   .version();
```

Bump Node to 20 LTS. Then:
```bash
rm -rf node_modules package-lock.json && npm install && npm run prod
```

Diff the generated `public/js/*.js` file sizes against the current build — a wildly different size means a plugin silently dropped out.

**Vue stays at 2.7** (the final Vue 2 release, which backports the composition API). Element-UI, `vue2-editor`, `vue2-google-maps`, `vuedraggable@2` and `vuex@3` are all Vue-2-only and all keep working. Vue 2 is past EOL, so plan a Vue 3 migration eventually — but as its own project, with Element-UI → Element Plus as the dominant cost. It is *not* a prerequisite for Laravel 13.

### 5.2 Frontend issues worth fixing while you're in there

* **`resources/js/bootstrap.js:23,26`** — `window.axios = require('axios')` appears twice, verbatim. Delete one.
* **`resources/js/admin.js:29`** — `window.Vue = require('vue')` is assigned *after* the `Vue.use(...)` calls above it, and `bootstrap.js` already sets it on line 2. The duplicate is redundant; the ordering is confusing enough that someone will eventually "fix" it wrong. Remove line 29 and rely on `bootstrap.js`.
* **`resources/js/admin.js:22`** — the Google Maps API key is hardcoded in source. It's a client-side key so it's inherently public, but it should come from `process.env.MIX_GOOGLE_MAPS_KEY` and be locked to your domains by HTTP-referrer restriction in Google Cloud Console. Right now it's usable by anyone who reads your bundle, and billed to you.
* `admin.js` registers ~60 components globally, so every admin page ships every component. After the upgrade, consider async component registration for the heavy ones (CKEditor, Google Maps) — a meaningful load-time win with zero API change.

---

## 6. Code quality — findings and fixes

These came out of a scan of the actual codebase. Ordered by payoff.

### 6.0 Pre-existing bugs found and fixed by the smoke tests (before any upgrade)

Building the safety net in §2.1 immediately surfaced four defects **on Laravel 7, with no upgrade
applied**. This is the return on writing the tests first.

| # | Where | Defect | Status |
|---|---|---|---|
| 1 | `LeadController.php:333` | `select('name','surname','full_name', COUNT(*))` with `groupBy('full_name')` violates MySQL's `ONLY_FULL_GROUP_BY` (default since 5.7). **`GET lead/filter-options` returned a 500** — the lead filter dropdown was dead on any default-configured MySQL. | ✅ fixed — group by all three non-aggregated columns |
| 2 | `asset/index.blade.php:5`, `asset/index_developer.blade.php:5` | `getUrlWithSortParams()` declared at Blade top level, in **two** views. A function declared in a Blade template is redeclared on every include, so rendering both views in one PHP process fatals with `Cannot redeclare`. Latent under PHP-FPM, fatal under any long-running worker (Octane, queue, test runner). | ✅ fixed — moved to `app/helpers.php` |
| 3 | same two views, line 61 | `hasEmptyColumn()` — identical problem, identical duplication. | ✅ fixed — moved to `app/helpers.php` |
| 4 | `config/permission_list.php` | The `'news'` key was declared **twice** in the same array literal. The second silently overwrote the first. Harmless today because the values matched, but it is exactly the kind of duplicate-key defect that becomes a real bug the moment the two copies diverge. | ✅ fixed — duplicate removed |

**🔴 Deployment gap — needs your attention on staging and production**

The News module is **completely non-functional** in the development database, for two independent reasons:

1. **Five migrations have never been run:**
   ```
   2025_08_12_160724_create_news_table
   2025_08_12_160805_create_news_images_table
   2025_08_12_160902_create_news_investors_table
   2025_08_14_150244_add_developer_support_to_news_table
   2025_08_27_141346_create_investor_news_read_table
   ```
   `GET admin/news` returned a 500: `Table 'news' doesn't exist`.

2. **The `news_*` permissions have never been seeded.** `config/permission_list.php` declares them, but
   `permissions` contains `sale_*` and `template_*` and no `news_*` rows — so `GET admin/news` returned
   a **403 for every admin, including the administrator role**.

Both were fixed in the *test* database only (`php artisan migrate` + re-running `PermissionSeeder` and
`RoleSeeder`, which are idempotent). **The development database was deliberately left untouched.**

Before deploying, run on each environment:
```bash
php artisan migrate:status | grep -i "^| No"    # what is pending?
php artisan migrate --force
php artisan db:seed --class="App\Modules\Admin\Database\Seeds\PermissionSeeder" --force
php artisan db:seed --class="App\Modules\Admin\Database\Seeds\RoleSeeder" --force
php artisan permission:cache-reset
```
If production shows the same five pending migrations, the news feature has never worked there either —
worth confirming with whoever owns that feature before assuming the deploy simply fixes it.

### 6.1 Correctness / logic issues (fix these regardless of the upgrade)

| # | Where | Issue |
|---|---|---|
| 1 | `app/Modules/Lead/Routes/web.php:16` | **`dd()` in a live route.** `dd('This is the Lead module index page. Build something great!')` — a scaffolding leftover that kills the request. If this route is reachable in production it's a broken page; if it isn't, delete the route. |
| 2 | `app/Modules/Admin/Utilities/LangFiles.php:74` | **`print_r($string, true)` wrapping a string** that's passed to `file_put_contents`. `print_r` on a string returns the string unchanged, so this is a no-op that looks like it does something. Delete the wrapper — it obscures the intent and will confuse the next reader. |
| 3 | `AssetController.php:250,252,473,475` | **`orderByRaw('ISNULL(area), area ASC')` is MySQL-only** and contradicts the SQLite test config in `phpunit.xml`. Use `orderByRaw('CASE WHEN area IS NULL THEN 1 ELSE 0 END, area ASC')`. |
| 4 | `app/InvestorNewsRead.php` vs `app/Modules/Admin/Models/InvestorNewsRead.php` | **Two identical-looking models** for the same table. Determine which is referenced, delete the other. Duplicated models mean divergent `$fillable`/`$casts` and eventually a real data bug. |
| 5 | `.env` (local) | `APP_DEBUG=true` with `APP_ENV=local` is correct locally — **verify production has `APP_DEBUG=false`**. With debugbar in `require` (not `require-dev`), a debug-enabled production leaks queries and env. Move `barryvdh/laravel-debugbar` to `require-dev`. |
| 6 | `.DS_Store`, `public/.DS_Store` | Tracked in git. `git rm --cached` them and add `.DS_Store` to `.gitignore`. `NEWS_IMAGE_FIX.md` is an empty tracked file — delete it. |
| 7 | `composer.json` | `"minimum-stability": "dev"` — should be `"stable"`. This lets dev-branch package versions into your lock file. |

### 6.2 N+1 queries (real, measurable)

Confirmed instances where a relation is walked inside a view loop with no eager loading. You have 26 `->with([...])` calls across the app but they don't cover these:

* **`app/Modules/Asset/Resources/Views/admin/asset/index_developer.blade.php:308`**
  ```blade
  {!! $item->investors->first()->admin->name !!} {!! $item->investors->first()->admin->surname !!}
  ```
  Two levels deep, inside a table loop, and `->first()` is called twice per row. For a 50-row page that's up to 200 queries. Fix: eager load `->with('investors.admin')` in the controller query, and hoist `$manager = $item->investors->first()?->admin` into a variable.
* **Same file, lines 82, 131, 262** — `$item->investors->count()`, `$item->payments->where('status', 0)` per row.
* **`app/Modules/Admin/Resources/Views/admin/investor/index.blade.php:89`** — `$item->admin->name` per row; needs `->with('admin')`.
* **`app/Modules/Admin/Resources/Views/admin/news/developer_index.blade.php:86-92`** — `$item->investors` accessed four times per row.

Install `barryvdh/laravel-debugbar` (dev) and turn on **strict mode** in a non-production environment to catch the rest automatically:

```php
// app/Providers/AppServiceProvider.php::boot()
Model::preventLazyLoading(! $this->app->isProduction());
```
That single line turns every N+1 into an exception in local/staging. It's the highest-leverage change in this document.

### 6.3 Architecture — the god controllers

| File | Lines |
|---|---|
| `app/Modules/Asset/Http/Controllers/AssetController.php` | **1,929** |
| `app/Modules/Admin/Http/Controllers/NewsController.php` | ~1,000 |
| `app/Modules/Asset/Http/Controllers/RevenueController.php` | ~950 |

`AssetController` has 28 methods and holds filtering, sorting, permission checks, payment-schedule generation, cloning, archiving, selling, exporting and developer-access management. Its `index()` and `myassets()` methods duplicate large blocks of filter logic, as do `filterOptions()`, `investorFilterOptions()` and `developerFilterOptions()`.

Don't rewrite it. Extract incrementally, in this order:

1. **Query scopes → the model.** Move the repeated `where` chains into `Asset` scopes: `scopeVisibleTo($query, $user)`, `scopeWithStatus($query, $status)`, `scopeSorted($query, $field, $order)`. This alone deletes several hundred duplicated lines across `index()`/`myassets()`.
2. **A dedicated filter object.** The `$request->investor`, `$request->type`, `$request->asset_status`… chain belongs in an `AssetFilter` class that takes the request and applies to a builder. `RevenueController`'s repeated `select('type', DB::raw('MAX(id) as max_id'))` blocks (lines 759–854) collapse into one method.
3. **Actions/services for the verbs.** `generatePaymentsList()`, `clone()`, `sell()`, `archive()` are domain operations, not HTTP concerns. Move them to `app/Modules/Asset/Services/`. You already have a `Services/` directory and a repository layer — the pattern exists, the controllers just bypass it.
4. **Form Requests for validation.** You have `AssetRequest` and `AssetSaleRequest` already; several other actions validate inline or not at all. Extend the pattern.

Do this **after** the upgrade, not during. Mixing refactors into upgrade commits makes bisecting a regression impossible.

### 6.4 Error handling

Roughly 30 `try/catch` blocks across the Lead and Asset controllers follow this shape:

```php
} catch (\Exception $ex) {
    throw new Exception($ex->getMessage(), $ex->getCode());
}
```
(`LeadController.php:163,302`, `LeadCommentController.php:88`, and others.)

This is strictly worse than not catching: it discards the original exception type, the stack trace origin, and the previous-exception chain, while pretending to handle something. Either remove the catch entirely, or preserve the chain:

```php
} catch (\Throwable $ex) {
    report($ex);
    throw new AssetOperationException('Could not save asset.', previous: $ex);
}
```

Also note `$ex->getCode()` on a PDO exception returns a *string* SQLSTATE like `'23000'`, which is an invalid `$code` for `Exception::__construct()` — under PHP 8 that throws a `TypeError` and you lose the real error entirely. This is a live bug, not just a style issue.

### 6.5 Dead code

* Commented-out `dd()` calls: `LeadCommentController.php:49`, `RevenueController.php:638,649,697`, `DeveloperController.php:248,257`, `AssetRequest.php:45`, `developer/create.blade.php:11`.
* Commented-out query lines: `AssetController.php:109` (`// $query->where('sale_status', 'active');`).
* Commented-out component registration: `admin.js` (`update-developer-asset`).

Delete all of it. Git remembers.

### 6.6 Static analysis baseline

Once you're on Laravel 13, lock in the quality:

`phpstan.neon`:
```yaml
includes:
    - vendor/larastan/larastan/extension.neon
parameters:
    level: 3          # start here; the codebase will not pass level 5 yet
    paths:
        - app
    excludePaths:
        - app/Modules/*/Database/Migrations/*
```
Generate a baseline (`--generate-baseline`) so existing violations don't block CI, then ratchet the level up one notch per sprint. Add `vendor/bin/pint --test` and `vendor/bin/phpstan analyse` to CI.

### 6.7 Security items to check while you're in the code

* **Role checks by string name.** `AssetController.php:123` does `auth()->user()->getRolesNameAttribute() != 'administrator'`. Two problems: an accessor is being called as a method (should be `$user->roles_name`), and authorization is string-comparing a role name. Since you already have `spatie/laravel-permission`, use `$user->hasRole('administrator')` — or better, permission-based checks (`$user->can('assets.view-all')`) so adding a role doesn't require touching controllers.
* **Redundant `auth()->user()` calls.** `AssetController::index()` assigns `$user = auth()->user()` on line 103, then calls `auth()->user()` again on line 123. Minor, but it's the kind of thing that hides a real auth-guard bug when there are multiple guards (`web`, `admin`, `investor`) — as there are here.
* Good news: **zero `env()` calls outside config files** (so `config:cache` is safe), and **no `Model::create($request->all())` mass-assignment** anywhere. Both are better than typical for a codebase this age.

---

## 7. Per-step verification checklist

Run this after **every** major version bump, before merging:

```bash
composer update
php artisan config:clear && php artisan cache:clear && php artisan view:clear
php artisan route:list | wc -l          # compare against the pre-step count
php artisan migrate --pretend           # no unexpected pending migrations
vendor/bin/phpunit                      # your smoke tests from §2.1
npm run prod                            # assets still build
vendor/bin/phpstan analyse              # once installed
```

Then manually, in the browser — these are the flows most likely to break, in priority order:

1. Login as admin / as investor / as developer (three guards — the most fragile part of any Laravel upgrade)
2. Asset index → filter → sort → paginate
3. Asset create → save → edit → save (the biggest form in the app)
4. Payment schedule generation (Carbon-sensitive)
5. Revenue view and its filters
6. Excel export (assets, revenue, leads)
7. PDF generation (dompdf)
8. File/image upload (Intervention + Flysystem)
9. Lead import
10. Email sending
11. Permission-gated pages for a non-administrator role

---

## 8. Suggested order of work

```
Week 1   Smoke tests (§2.1) + tooling (§2.3) + prep cleanup (§2.2)
Week 2   Laravel 7→8, then remove caffeinated/modules
Week 3   Laravel 8→9              ← the hard one, give it the whole week
Week 4   Laravel 9→10, 10→11
Week 5   Laravel 11→12, 12→13 + frontend build chain (§5)
Week 6   Code-quality pass (§6.1, §6.2, §6.4, §6.5) + PHPStan baseline
Later    AssetController refactor (§6.3) — ongoing, incremental
Later    Vue 2 → Vue 3 + Vite — separate project
```

Merge to `staging` and deploy after each step. Never batch two majors into one deploy.
