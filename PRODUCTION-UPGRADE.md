# Production Upgrade Runbook

Ubuntu 20.04 + PHP 7.4 + Laravel 7 → Ubuntu 22.04 + PHP 8.3 + Laravel 13.

This is not a theoretical plan. Every command below was executed against staging
(`test.keyasset.ge`, `68.183.222.15`) on 2026-08-03 and the site came back up on
Laravel 13.23.0. The four traps in §3 are ones staging actually hit — each cost
time to diagnose there so it costs none here.

Companion document: `UPGRADE-GUIDE.md` covers the application-code upgrade
(Laravel 7 → 13) and the reasoning behind it. This file covers only getting that
code onto a server.

**Expect 30–45 minutes of downtime.** The site is hard down from the moment the
OS upgrade begins until nginx is serving through PHP 8.3, because Ubuntu 22.04
has no PHP 7.4 packages — the old stack cannot run once the OS moves. There is
no partial state to fall back to. Plan a maintenance window.

---

## 1. Why the OS has to move

Laravel 13 requires PHP 8.3. On Ubuntu 20.04 that is not installable:

* Ubuntu 20.04's own repositories top out at PHP 7.4.
* The `ondrej/php` PPA — the normal way to get newer PHP on Ubuntu — **no longer
  publishes anything for 20.04**. Ubuntu 20.04 reached end of life in April 2025
  and the PPA dropped it. Verified directly:

  ```
  focal  (20.04): package index =     20 bytes  → php8.3-fpm: NOT FOUND
  jammy  (22.04): package index = 303,502 bytes → php8.3-fpm: available
  noble  (24.04): package index = 289,246 bytes → php8.3-fpm: available
  ```

  A 20-byte index is an empty file.

So the OS upgrade is a hard prerequisite, not a nice-to-have. 22.04 was chosen
over 24.04 because it is a single release hop; 24.04 would require two.

---

## 2. Pre-flight

### 2.1 Confirm production matches the assumptions

Staging was Ubuntu 20.04 / nginx / MySQL 8.0 / PHP 7.4 / app at `/var/www/Key`.
Confirm production is the same shape before trusting the rest of this document:

```bash
ssh root@<PROD_IP>

lsb_release -ds
php -v | head -1
nginx -v
mysql --version
ls /etc/nginx/sites-enabled/
grep -hE "server_name|root " /etc/nginx/sites-enabled/*
cd /var/www/Key && git branch --show-current && git log --oneline -1
grep -E "^(APP_ENV|APP_DEBUG|APP_URL|DB_DATABASE)=" .env
df -h /
```

If anything differs materially — a different app path, Apache instead of nginx,
PHP already ≥ 8.2, an OS other than 20.04 — **stop and re-plan.** The steps below
assume the staging shape.

### 2.2 Take a DigitalOcean snapshot

This was skipped on staging deliberately. **Do not skip it on production.** It is
the only rollback that covers a failed OS upgrade; the file-level backups in §2.3
only help if the machine still boots.

DigitalOcean panel → Droplet → Snapshots → *Take Snapshot*. Wait for completion
before proceeding.

### 2.3 Backups

```bash
cd /var/www/Key
STAMP=$(date +%Y%m%d-%H%M%S)
mkdir -p /root/backups/$STAMP
DBU=$(grep -E "^DB_USERNAME=" .env | cut -d= -f2-)
DBP=$(grep -E "^DB_PASSWORD=" .env | cut -d= -f2-)
DBN=$(grep -E "^DB_DATABASE=" .env | cut -d= -f2-)

mysqldump -u"$DBU" -p"$DBP" --single-transaction --routines --events "$DBN" \
  > /root/backups/$STAMP/$DBN.sql
cp .env                        /root/backups/$STAMP/env.backup
cp composer.lock               /root/backups/$STAMP/composer.lock
cp /etc/apt/sources.list       /root/backups/$STAMP/sources.list
cp /etc/nginx/sites-available/* /root/backups/$STAMP/ 2>/dev/null
git rev-parse HEAD             > /root/backups/$STAMP/git-HEAD.txt
tar czf /root/backups/$STAMP/vendor.tar.gz vendor
echo "$STAMP" > /root/backups/LATEST
```

Verify the dump completed, then **copy it off the server**:

```bash
tail -1 /root/backups/$STAMP/$DBN.sql   # must read "-- Dump completed on ..."
grep -c "CREATE TABLE" /root/backups/$STAMP/$DBN.sql
```

```bash
# from your machine
scp root@<PROD_IP>:/root/backups/<STAMP>/{*.sql,env.backup} ./prod-backup/
```

### 2.4 Note the uploads directory

`storage/app/public` holds user uploads and is **not** in git. It is untouched by
this procedure, but confirm it is included in whatever normal backup you run, and
that `public/storage` is a symlink:

```bash
ls -la public/storage
find storage/app/public -type f | wc -l
```

---

## 3. The four traps staging hit

Handled inline in §4 — listed here so the failures are recognisable if they
appear in a different order.

### Trap 1 — `do-release-upgrade` refuses: "install all available updates first"

Focal must be fully patched before it will offer the release upgrade.
Fix: run a full `dist-upgrade` first (§4.1).

### Trap 2 — `do-release-upgrade` refuses: "you have not rebooted"

The `dist-upgrade` in Trap 1 pulls a new kernel. Fix: reboot (§4.2).

### Trap 3 — "No 'ubuntu-minimal' available/downloadable" ← the dangerous one

The upgrader does not recognise `mirrors.digitalocean.com` as an official Ubuntu
mirror. Rather than rewriting those entries to jammy it **disables them all**:

```
DEBUG entry '# deb http://mirrors.digitalocean.com/ubuntu/ jammy main restricted
             # disabled on upgrade to jammy' was disabled (unknown mirror)
```

That leaves only `security.ubuntu.com`, which does not carry `ubuntu-minimal`, so
the upgrade aborts. It aborts *safely* — `sources.list` is restored to focal and
apt keeps working — but it will never succeed until fixed.

Fix: point `sources.list` at `archive.ubuntu.com` before upgrading (§4.3).

### Trap 4 — nginx fails to start after the upgrade: `bind() to 0.0.0.0:80 failed`

The upgrade pulls in **apache2**, which starts on boot and takes port 80 before
nginx. `nginx -t` passes, which makes this confusing.

Diagnose and fix:

```bash
ss -ltnp "sport = :80"        # shows apache2 holding it
systemctl stop apache2 && systemctl disable apache2
apt-get purge -y apache2 apache2-bin apache2-data apache2-utils
systemctl restart nginx
```

---

## 4. The upgrade

Run everything inside `tmux`. If SSH drops mid-upgrade without it, the machine
can be left in an unbootable half-upgraded state.

### 4.1 Fully patch the current release

```bash
tmux new -s upgrade

export DEBIAN_FRONTEND=noninteractive
printf '%s\n' 'Dpkg::Options { "--force-confdef"; "--force-confold"; };' \
  > /etc/apt/apt.conf.d/99keepconf

apt-get update
apt-get -y dist-upgrade
apt-get -y autoremove
```

`99keepconf` makes every subsequent step keep your existing nginx / MySQL / PHP
config files instead of prompting or overwriting them.

### 4.2 Reboot

```bash
reboot
```

Reconnect and confirm services returned:

```bash
uptime
for s in nginx mysql php7.4-fpm; do printf "%-14s %s\n" "$s" "$(systemctl is-active $s)"; done
```

### 4.3 Fix the mirror (Trap 3)

```bash
cp /etc/apt/sources.list /root/backups/sources.list.do-mirror
sed -i 's|http://mirrors.digitalocean.com/ubuntu/\?|http://archive.ubuntu.com/ubuntu/|g' \
  /etc/apt/sources.list
apt-get update
apt-cache policy ubuntu-minimal | head -3     # must show a candidate
```

Also disable the now-dead focal PHP PPA so it cannot confuse the upgrader:

```bash
sed -i 's|^deb |#deb |' /etc/apt/sources.list.d/ondrej-ubuntu-php-focal.list
```

### 4.4 Release upgrade to 22.04

```bash
tmux new -s osupgrade
export DEBIAN_FRONTEND=noninteractive
do-release-upgrade -f DistUpgradeViewNonInteractive 2>&1 | tee /root/os-upgrade.log
```

Roughly 17 minutes of package work on staging. Detach with `Ctrl-b d`; follow
progress from another session with:

```bash
tail -f /var/log/dist-upgrade/apt-term.log
```

On completion:

```bash
grep VERSION= /etc/os-release      # expect 22.04.5 LTS (Jammy Jellyfish)
reboot
```

### 4.5 After reboot: clear Trap 4, install PHP 8.3

```bash
ss -ltnp "sport = :80"
systemctl stop apache2 2>/dev/null; systemctl disable apache2 2>/dev/null
apt-get purge -y apache2 apache2-bin apache2-data apache2-utils
systemctl restart nginx && systemctl is-active nginx

export DEBIAN_FRONTEND=noninteractive
add-apt-repository -y ppa:ondrej/php
apt-get update

apt-get install -y php8.3-cli php8.3-fpm php8.3-common php8.3-bcmath \
  php8.3-curl php8.3-gd php8.3-mbstring php8.3-mysql php8.3-opcache \
  php8.3-readline php8.3-xml php8.3-zip php8.3-intl

php8.3 -v | head -1
systemctl is-active php8.3-fpm
ls /run/php/                        # expect php8.3-fpm.sock
```

That extension list mirrors what PHP 7.4 had loaded on staging. If production's
`php -m` shows anything extra, add the matching `php8.3-*` package.

### 4.6 Deploy the application

```bash
cd /var/www/Key
git checkout -- .                      # discards .gitignore mode-only noise
git fetch origin
git checkout upgrade/laravel-13
git pull origin upgrade/laravel-13

export COMPOSER_ALLOW_SUPERUSER=1
php8.3 /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction
```

Built frontend assets (`public/js`, `public/css`, `public/mix-manifest.json`) are
committed on the branch, so **Node is not needed on the server**.

### 4.7 Point nginx at PHP 8.3

```bash
cp /etc/nginx/sites-available/staging /root/backups/nginx.pre-php83.conf
sed -i 's|php7\.4-fpm\.sock|php8.3-fpm.sock|g' /etc/nginx/sites-available/<SITE>
grep -n fastcgi_pass /etc/nginx/sites-available/<SITE>
nginx -t && systemctl reload nginx
```

Replace `<SITE>` with the production site file found in §2.1.

### 4.8 Permissions and caches

```bash
cd /var/www/Key
chown -R www-data:www-data storage bootstrap/cache
php8.3 artisan config:clear
php8.3 artisan cache:clear
php8.3 artisan view:clear
php8.3 artisan route:clear
php8.3 artisan --version              # expect Laravel Framework 13.23.0
```

### 4.9 Migrations

Staging had **zero** pending migrations — its database was already ahead of the
development database. Check production rather than assuming either way:

```bash
php8.3 artisan migrate:status | grep -i pending
```

If any are pending (the five `news` migrations are the likely candidates):

```bash
php8.3 artisan migrate --force
php8.3 artisan db:seed --class="App\Modules\Admin\Database\Seeds\PermissionSeeder" --force
php8.3 artisan db:seed --class="App\Modules\Admin\Database\Seeds\RoleSeeder" --force
php8.3 artisan permission:cache-reset
```

Both seeders are idempotent (`updateOrCreate`), so re-running them is safe.

### 4.10 Optional: re-cache config for production

Only after everything below verifies. Config caching hides `.env` changes until
re-run, so it is deliberately left until last:

```bash
php8.3 artisan config:cache
php8.3 artisan route:cache
```

---

## 5. Verification

### 5.1 Automated

```bash
cd /var/www/Key
grep VERSION= /etc/os-release
php8.3 artisan --version
for s in nginx mysql php8.3-fpm; do printf "%-14s %s\n" "$s" "$(systemctl is-active $s)"; done

for p in / /admin /login; do
  printf "%-10s %s\n" "$p" \
    "$(curl -sk -o /dev/null -w '%{http_code}' https://<PROD_DOMAIN>$p)"
done

: > storage/logs/laravel.log
curl -sk -o /dev/null https://<PROD_DOMAIN>/admin
wc -l < storage/logs/laravel.log     # expect 0
```

A `403` on permission-guarded URLs while logged out is correct, not a failure —
the permission middleware returns 403 rather than redirecting. `500` is a
failure; check `/var/log/nginx/error.log` first, since a fatal before Laravel
boots leaves `storage/logs/laravel.log` empty.

### 5.2 Manual — the parts no test covers

The automated suite asserts on server-rendered HTML and cannot see client-side
behaviour or CSRF. Log in and check, in priority order:

1. **Log in** on each guard that exists in production: admin, investor, developer.
2. **Save an asset** — the largest form and the heaviest POST path. This is the
   first real exercise of axios 1.x plus the Laravel 13 CSRF middleware, which no
   automated test can reach (`runningUnitTests()` short-circuits it).
3. **Asset list** — the row action buttons (edit, developer access, archive,
   delete) must all appear, and pagination must show numbered links, not just
   "Previous / Next".
4. **Asset view** — the image carousel must slide (Swiper 12).
5. **An Excel export** and a **PDF** — exercises maatwebsite/excel and dompdf.
6. **Lead import** — file upload through the Flysystem 3 path.
7. **A CKEditor form** and the **Google Maps** picker on the asset form.

If Vue components are missing anywhere, open the browser console. The signature
is custom element tags left unrendered in the DOM — that means the bundle threw
during startup.

---

## 6. Rollback

### If the application is broken but the server is healthy

```bash
cd /var/www/Key
git checkout staging
php8.3 /usr/local/bin/composer install --no-dev --optimize-autoloader
php8.3 artisan config:clear && php8.3 artisan view:clear
```

This will **not** fully work after the OS upgrade, because Laravel 7 needs PHP
7.4 and 22.04 has no such package. Treat it as a way to inspect the old code, not
as a working restore.

### If the upgrade fails or the site cannot be recovered

Restore the DigitalOcean snapshot from §2.2. That is the real rollback.

### Database only

```bash
mysql -u<user> -p<pass> <db> < /root/backups/<STAMP>/<db>.sql
```

---

## 7. Post-upgrade cleanup

Not urgent, but worth doing once production is stable:

```bash
# orphaned PHP 7.4 packages (inert, but noise)
apt-get purge -y 'php7.4-*'

# restore the DigitalOcean mirror: faster and does not bill bandwidth
cp /root/backups/sources.list.do-mirror /etc/apt/sources.list
sed -i 's|focal|jammy|g' /etc/apt/sources.list
apt-get update
```

Verify `apt-get update` succeeds after the mirror swap; if it does not, revert to
`archive.ubuntu.com`, which is known to work.

Also worth doing:

* **Rotate the root password** and move to SSH key authentication.
* Set `APP_ENV=production` if production still says `local` (staging did).
* Confirm `APP_DEBUG=false`.

---

## 8. Known gaps

Recorded honestly so nobody assumes more coverage than exists.

* **Write paths are unverified.** Everything exercised on staging was a read.
  Saving an asset, importing leads and uploading files all go through axios 1.x
  POST requests that have not been run anywhere. §5.2 item 2 is the priority.
* **CSRF cannot be tested automatically.** Laravel's middleware short-circuits on
  `runningUnitTests()`. Reading the Laravel 13 implementation shows the new
  `Sec-Fetch-Site` origin check is OR'd *alongside* the existing token check
  rather than added as a requirement, and the strict `useOriginOnly()` mode
  defaults off — so it should be strictly more permissive. That is a code reading,
  not a live test.
* **Google Maps** will not initialise on any host not whitelisted for the API key.
* **`storage/app/public`** is not covered by this procedure. It contains user
  uploads and is not in git.
