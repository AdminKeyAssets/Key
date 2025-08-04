<?php

namespace App\Modules\News\Providers;

use App\Modules\News\Repositories\Contracts\INewsRepository;
use App\Modules\News\Repositories\Eloquent\NewsRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'news');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->mapNewsRoutes();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(INewsRepository::class, NewsRepository::class);
    }

    /**
     * Define the "news" routes for the module.
     *
     * @return void
     */
    protected function mapNewsRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace'  => 'App\Modules\News\Http\Controllers',
        ], function ($router) {
            require __DIR__ . '/../Routes/news.php';
        });
    }
}
