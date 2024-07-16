<?php
Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin']], function () {
    Route::name('admin.investor.')->prefix('investors')->group(function () {

        $investorController = 'User\InvestorController';
        $moduleName = 'investor';

        /**
         * Index page
         */
        Route::get('', $investorController . '@index')
            ->name('index')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'index', true)]);

        /*
         * Create Form.
         */
        Route::get('create', $investorController . '@create')
            ->name('create_form')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);

        Route::get('edit/{id?}', $investorController . '@edit')
            ->name('edit')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'update', true)]);

        /**
         * Get Create/Update form data.
         */
        Route::post('create-form-data', $investorController . '@getCreateData')
            ->name('create_form_data')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);

        /*
         * Save investor.
         */
        Route::post('save', $investorController . '@save')
            ->name('save')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);

        /**
         * Delete investor.
         */
        Route::post('delete', $investorController . '@delete')
            ->name('delete')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'delete', true)]);

        /**
         * Export investors.
         */
        Route::get('export', $investorController . '@export')
            ->name('export')
            ->middleware(['permission:' . getPermissionKey($moduleName, 'export', false)]);

        Route::get('filter-options', $investorController . '@filterOptions')
            ->name('filters');
    });


});


Route::group(['prefix' => 'investor', 'middleware' => ['auth:investor']], function () {
    /**
     * Profile manage.
     */
    Route::name('investor.profile.')->prefix('profile')->group(function () {

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
