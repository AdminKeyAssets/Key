<?php

namespace App\Modules\Lead\Providers;

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
        $this->loadTranslationsFrom(module_path('lead', 'Resources/Lang'), 'lead');
        $this->loadViewsFrom(module_path('lead', 'Resources/Views'), 'lead');
        $this->loadMigrationsFrom(module_path('lead', 'Database/Migrations'));
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
