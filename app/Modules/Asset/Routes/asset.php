<?php
Route::prefix('assets')->name('asset.')->group(function () {
    $controller = 'AssetController';
    $moduleName = 'asset';

    Route::get('', $controller . '@index')->name('index')->middleware(['permission:' . getPermissionKey($moduleName, 'index', true)]);
    //Create/Update view.
    Route::get('/create', $controller . '@create')->name('create')->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);
    Route::post('/create-data', $controller . '@createData')->name('create_data')->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);
    Route::get('/edit/{id?}', $controller . '@edit')->name('edit')->middleware(['permission:' . getPermissionKey($moduleName, 'update', true)]);;
    //Save
    Route::post('/store', $controller . '@store')->name('store')->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);
    // Delete
    Route::post('/delete', $controller . '@destroy')->name('delete')->middleware(['permission:' . getPermissionKey($moduleName, 'delete', true)]);;
});

