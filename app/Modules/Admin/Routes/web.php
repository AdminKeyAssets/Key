<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your module. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin']], function () {

    // Error log show.
    Route::get('log', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')
        ->name('admin.log.show');

});


Route::group([
    'prefix' => 'admin',
    'namespace' => 'Admin',
    'middleware' => ['auth:admin'],
], function () {

    /**
     * Admin Dashboard page.
     */
    Route::get('dashboard', 'DashboardController@index')
        ->name('admin.dashboard');

});

