<?php


namespace App\Modules\Admin\Providers;

use App\Modules\Admin\Services\Admin\TextService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class AdminModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register facades services.
     */
    public function register()
    {
        $this->app->bind('TextService', function(){
            return new TextService();
        });
    }

}
