<?php

Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin']], function () {

    /**
     * News management.
     */
    Route::name('admin.news.')->prefix('news')->group(function() {

        $newsController = 'NewsController';
        $moduleName = 'news';

        /**
         * Get data for view page (for Vue component)
         */
        Route::post('get-save-data', $newsController . '@getSaveData')
            ->name('getSaveData')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'index', true)]);

        /**
         * Index page
         */
        Route::get('', $newsController . '@index')
            ->name('index')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'index', true)]);

        /*
         * Create Form.
         */
        Route::get('create/{id?}', $newsController . '@create')
            ->name('create_form')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'create', true)]);

        /**
         * Get Create/Update form data.
         */
        Route::post('create-form-data', $newsController . '@getCreateData')
            ->name('create_form_data')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'create', true)]);

        /*
         * Save news.
         */
        Route::post('save', $newsController . '@save')
            ->name('save')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'create', true)]);

        /**
         * Delete news.
         */
        Route::post('delete', $newsController . '@delete')
            ->name('delete')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'delete', true)]);

        /**
         * View news.
         */
        Route::get('view/{id}', $newsController . '@view')
            ->name('view')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'index', true)]);

    });

});
