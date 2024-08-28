<?php

namespace App\Modules\Lead\Providers;

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
        $this->loadTranslationsFrom(module_path('lead', 'Resources/Lang', 'app'), 'lead');
        $this->loadViewsFrom(module_path('lead', 'Resources/Views', 'app'), 'lead');
        $this->loadMigrationsFrom(module_path('lead', 'Database/Migrations', 'app'));
        if(!$this->app->configurationIsCached()) {
            $this->loadConfigsFrom(module_path('lead', 'Config', 'app'));
        }
        $this->loadFactoriesFrom(module_path('lead', 'Database/Factories', 'app'));
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
