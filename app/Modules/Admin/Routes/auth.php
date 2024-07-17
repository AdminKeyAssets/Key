<?php

Route::group(['prefix' => 'admin', 'middleware' => ['guest:admin'], 'namespace' => 'Auth'], function () {

    /**
     * Admin show login form.
     */
    Route::get('', 'LoginController@showLoginForm')
        ->name('admin.login_form');

    /**
     * Login method.
     */
    Route::post('', 'LoginController@login')
        ->name('admin.login');

});

Route::group(['prefix' => 'login', 'middleware' => ['guest:investor'], 'namespace' => 'Auth'], function () {

    /**
     * Admin show login form.
     */
    Route::get('', 'InvestorLoginController@showLoginForm')
        ->name('admin.investor_login_form');

    /**
     * Login method.
     */
    Route::post('', 'InvestorLoginController@login')
        ->name('admin.investor_login');

});

Route::group(['prefix' => 'admin', 'middleware' => ['guest:admin'], 'namespace' => 'Auth'], function () {

    /**
     * Logout method.
     */
    Route::get('logout', 'LoginController@logout')
        ->name('admin.logout');

});


Route::group(['prefix' => 'investor','namespace' => 'Auth'], function () {

    /**
     * Logout method.
     */
    Route::get('logout', 'InvestorLoginController@logout')
        ->name('investor.logout');

});
