<?php

Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin']], function () {
    Route::name('admin.news.')->prefix('news')->group(function () {

        $newsController = 'NewsController';
        $moduleName = 'news';

        /**
         * Index page
         */
        Route::get('', $newsController . '@index')
            ->name('index')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'index', true)]);

        /*
         * Create Form.
         */
        Route::get('create', $newsController . '@create')
            ->name('create_form')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);

        /**
         * Edit Form.
         */
        Route::get('edit/{id?}', $newsController . '@edit')
            ->name('edit')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'update', true)]);

        /**
         * View Form.
         */
        Route::get('view/{id?}', $newsController . '@view')
            ->name('view')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'view', true)]);

        /**
         * Get Create/Update form data.
         */
        Route::post('create-form-data', $newsController . '@getCreateData')
            ->name('create_form_data')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);

        /*
         * Save news.
         */
        Route::post('save', $newsController . '@save')
            ->name('save')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);

        /**
         * Delete news.
         */
        Route::post('delete', $newsController . '@delete')
            ->name('delete')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'delete', true)]);

        /**
         * Export news.
         */
        Route::get('export', $newsController . '@export')
            ->name('export')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'export', false)]);

        Route::get('filter-options', $newsController . '@filterOptions')
            ->name('filters');
    });
});

// Developer routes for news
Route::group(['prefix' => 'developer', 'middleware' => ['auth:developer']], function () {
    Route::name('developer.news.')->prefix('news')->group(function () {
        $newsController = 'NewsController';

        /**
         * Developer news index page
         */
        Route::get('', $newsController . '@developerIndex')
            ->name('index');

        /**
         * Developer news create form
         */
        Route::get('create', $newsController . '@developerCreate')
            ->name('create_form');

        /**
         * Developer news edit form
         */
        Route::get('edit/{id?}', $newsController . '@developerEdit')
            ->name('edit');

        /**
         * Developer news view
         */
        Route::get('view/{id?}', $newsController . '@developerView')
            ->name('view');

        /**
         * Get Create/Update form data for developers
         */
        Route::post('create-form-data', $newsController . '@developerGetCreateData')
            ->name('create_form_data');

        /**
         * Save developer news
         */
        Route::post('save', $newsController . '@developerSave')
            ->name('save');

        /**
         * Delete developer news
         */
        Route::post('delete', $newsController . '@developerDelete')
            ->name('delete');
    });
});

// Investor routes for news
Route::group(['prefix' => 'investor', 'middleware' => ['auth:investor']], function () {
    Route::name('investor.news.')->prefix('news')->group(function () {
        $newsController = 'NewsController';

        /**
         * Investor news index page (only news attached to them)
         */
        Route::get('', $newsController . '@investorIndex')
            ->name('index');

        /**
         * Investor news view
         */
        Route::get('view/{id?}', $newsController . '@investorView')
            ->name('view');
    });
});
