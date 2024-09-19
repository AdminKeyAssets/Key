<?php


Route::prefix('sale')->name('sale.')->group(function () {
    $controller = 'SaleController';
    $moduleName = 'sale';

    Route::get('/list', $controller . '@index')->name('index')->middleware(['permission:' . getPermissionKey($moduleName, 'index', true)]);
    //Create/Update view.
    Route::get('/create', $controller . '@create')->name('create')->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);
    Route::post('/create-data', $controller . '@createData')->name('create_data');
    Route::get('/edit/{id?}', $controller . '@edit')->name('edit')->middleware(['permission:' . getPermissionKey($moduleName, 'update', true)]);
    Route::get('/view/{id?}', $controller . '@view')->name('view')->middleware(['permission:' . getPermissionKey($moduleName, 'view', true)]);
//
//    //Save
    Route::post('/store', $controller . '@store')->name('store')->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);

//    // Delete
    Route::post('/delete', $controller . '@destroy')->name('delete')->middleware(['permission:' . getPermissionKey($moduleName, 'delete', true)]);

    Route::get('filter-options', $controller . '@filterOptions')
        ->name('filters');

    Route::post('update-manager', $controller . '@updateManager')->name('update.manager')->middleware(['permission:' . getPermissionKey($moduleName, 'update', true)]);


    /**
     * Export sales.
     */
    Route::get('export', $controller . '@export')
        ->name('export')
        ->middleware(['permission:' . getPermissionKey($moduleName, 'export', false)]);

});

