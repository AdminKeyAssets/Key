<?php

namespace App\Modules\Asset\Providers;

use Caffeinated\Modules\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(module_path('asset', 'Resources/Lang', 'app'), 'asset');
        $this->loadViewsFrom(module_path('asset', 'Resources/Views', 'app'), 'asset');
        $this->loadMigrationsFrom(module_path('asset', 'Database/Migrations', 'app'));
        if(!$this->app->configurationIsCached()) {
            $this->loadConfigsFrom(module_path('asset', 'Config', 'app'));
        }
        $this->loadFactoriesFrom(module_path('asset', 'Database/Factories', 'app'));
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
