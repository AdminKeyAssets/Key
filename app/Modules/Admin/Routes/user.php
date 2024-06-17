<?php
Route::group([ 'prefix' => 'admin', 'middleware' => ['auth:admin'] ], function () {
    Route::name('admin.user.')->prefix('users')->group(function(){

        $userController = 'User\UserController';
        $moduleName = 'user';

        /**
         * Index page
         */
        Route::get('', $userController . '@index')
            ->name('index')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'index', true)]);

        /*
         * Create Form.
         */
        Route::get('create/{id?}', $userController . '@create')
            ->name('create_form')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'create', true)]);

        /**
         * Get Create/Update form data.
         */
        Route::post('create-form-data', $userController . '@getCreateData')
            ->name('create_form_data')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'create', true)]);


        /*
         * Save user.
         */
        Route::post('save', $userController . '@save')
            ->name('save')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'create', true)]);

        /**
         * Delete user.
         */
        Route::post('delete', $userController . '@delete')
            ->name('delete')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'delete', true)]);

        /**
         * Export users.
         */
        Route::get('export', $userController . '@export')
            ->name('export')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'export', false)]);

        Route::get('filter-options', $userController . '@filterOptions')
            ->name('filters');
    });

    /**
     * Profile manage.
     */
    Route::name('admin.profile.')->prefix('profile')->group(function() {

        /**
         * Profile edit page
         */
        Route::get('', 'User\ProfileController@create')
            ->name('index');

        /**
         * Get Update form data.
         */
        Route::post('profile-form-data', 'User\ProfileController@getCreateData')
            ->name('form_data');

        /**
         * Profile save.
         */
        Route::post('save', 'User\ProfileController@save')
            ->name('save');
    });
});
