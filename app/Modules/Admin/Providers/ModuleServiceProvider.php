<?php

namespace App\Modules\Admin\Providers;

use App\Modules\Admin\Repositories\Contracts\IAdminRepository;
use App\Modules\Admin\Repositories\Contracts\IFileRepository;
use App\Modules\Admin\Repositories\Contracts\IPermissionRepository;
use App\Modules\Admin\Repositories\Contracts\IRoleRepository;
use App\Modules\Admin\Repositories\Eloquent\AdminRepository;
use App\Modules\Admin\Repositories\Eloquent\FileRepository;
use App\Modules\Admin\Repositories\Eloquent\PermissionRepository;
use App\Modules\Admin\Repositories\Eloquent\RoleRepository;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(module_path('admin', 'Resources/Lang'), 'admin');
        $this->loadViewsFrom(module_path('admin', 'Resources/Views'), 'admin');
        $this->loadMigrationsFrom(module_path('admin', 'Database/Migrations'));
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
        $this->app->bind(IFileRepository::class, FileRepository::class);

    }

}
