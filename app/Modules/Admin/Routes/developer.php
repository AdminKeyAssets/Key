<?php

Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin']], function () {
    Route::name('admin.developer.')->prefix('developers')->group(function () {

        $developerController = 'User\DeveloperController';
        $moduleName = 'developer';

        /**
         * Index page
         */
        Route::get('', $developerController . '@index')
            ->name('index')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'index', true)]);

        /*
         * Create Form.
         */
        Route::get('create', $developerController . '@create')
            ->name('create_form')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);

        /**
         * Edit Form.
         */
        Route::get('edit/{id?}', $developerController . '@edit')
            ->name('edit')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'update', true)]);

        /**
         * View Form.
         */
        Route::get('view/{id?}', $developerController . '@view')
            ->name('view')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'view', true)]);

        /**
         * Get Create/Update form data.
         */
        Route::post('create-form-data', $developerController . '@getCreateData')
            ->name('create_form_data')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);

        /*
         * Save developer.
         */
        Route::post('save', $developerController . '@save')
            ->name('save')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);

        /**
         * Delete developer.
         */
        Route::post('delete', $developerController . '@delete')
            ->name('delete')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'delete', true)]);
    });
});
