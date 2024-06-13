<?php


Route::group([ 'prefix' => 'admin', 'middleware' => ['auth:admin'] ], function () {


    Route::name('admin.investor.')->prefix('investors')->group(function(){

        $userController = 'User\InvestorController';
        $moduleName = 'investor';

        /**
         * Index page
         */
        Route::get('', $userController . '@index')
            ->name('index')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'index', true)]);

        /*
         * Create Form.
         */
        Route::get('create', $userController . '@create')
            ->name('create_form')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'create', true)]);

        Route::get('edit/{id?}', $userController . '@edit')
            ->name('edit')
            ->middleware(['permission:'.getPermissionKey($moduleName, 'update', true)]);

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
