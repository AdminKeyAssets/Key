<?php

Route::group([ 'prefix' => 'admin', 'middleware' => ['auth:admin'] ], function () {

    /**
     * Role manage.
     */
    Route::name('admin.role.')->prefix('roles')->group(function() {

        $roleController = 'User\RoleController';
        $moduleName = 'role';

        /**
         * Index page
         */
        Route::get('', $roleController . '@index')
            ->name('index')
                ->middleware(['permission:'.getPermissionKey($moduleName, 'index', true)]);

        /*
         * Create Form.
         */
        Route::get('create/{id?}', $roleController . '@create')
            ->name('create_form')
                ->middleware(['permission:'.getPermissionKey($moduleName, 'create', true)]);

        /**
         * Get Create/Update form data.
         */
        Route::post('create-form-data', $roleController . '@getCreateData')
            ->name('create_form_data')
                ->middleware(['permission:'.getPermissionKey($moduleName, 'create', true)]);

        /*
         * Save role.
         */
        Route::post('save', $roleController . '@save')
            ->name('save')
                ->middleware(['permission:'.getPermissionKey($moduleName, 'create', true)]);

        /**
         * Delete user.
         */
        Route::delete('delete', $roleController . '@delete')
            ->name('delete')
                ->middleware(['permission:'.getPermissionKey($moduleName, 'delete', true)]);

    });

});
