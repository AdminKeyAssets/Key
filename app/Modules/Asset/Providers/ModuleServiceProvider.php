<?php

namespace App\Modules\Asset\Providers;

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
        $this->loadTranslationsFrom(module_path('asset', 'Resources/Lang'), 'asset');
        $this->loadViewsFrom(module_path('asset', 'Resources/Views'), 'asset');
        $this->loadMigrationsFrom(module_path('asset', 'Database/Migrations'));
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        
        // Register Developer Access middleware
        $this->app['router']->aliasMiddleware('developer.access', \App\Modules\Asset\Http\Middleware\DeveloperAccess::class);
    }
}
