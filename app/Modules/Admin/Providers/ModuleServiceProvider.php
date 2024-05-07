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
use Caffeinated\Modules\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the module services.
     *
     * @return void
     * @throws \Caffeinated\Modules\Exceptions\ModuleNotFoundException
     */
    public function boot()
    {
        $this->loadTranslationsFrom(module_path('admin', 'Resources/Lang', 'app'), 'admin');
        $this->loadViewsFrom(module_path('admin', 'Resources/Views', 'app'), 'admin');
        $this->loadMigrationsFrom(module_path('admin', 'Database/Migrations', 'app'));
        if(!$this->app->configurationIsCached()) {
            $this->loadConfigsFrom(module_path('admin', 'Config', 'app'));
        }
        $this->loadFactoriesFrom(module_path('admin', 'Database/Factories', 'app'));
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(AdminModuleServiceProvider::class);

        $this->bindRepositories();

    }

    /**
     * Bind repositories
     */
    public function bindRepositories()
    {
        $this->app->bind(IPermissionRepository::class, PermissionRepository::class);
        $this->app->bind(IAdminRepository::class, AdminRepository::class);
        $this->app->bind(IRoleRepository::class, RoleRepository::class);
        $this->app->bind(ITextRepository::class, TextRepository::class);
        $this->app->bind(IFileRepository::class, FileRepository::class);

    }

}
