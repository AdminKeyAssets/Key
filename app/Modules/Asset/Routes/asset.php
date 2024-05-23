<?php


Route::prefix('assets')->name('asset.')->group(function () {
    $controller = 'AssetController';
    $moduleName = 'asset';

    Route::get('list', $controller . '@index')->name('index')->middleware(['permission:' . getPermissionKey($moduleName, 'index', true)]);
    Route::get('my-assets', $controller . '@myassets')->name('myassets')->middleware(['permission:' . getPermissionKey($moduleName, 'view', true)]);
    //Create/Update view.
    Route::get('/create', $controller . '@create')->name('create')->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);
    Route::post('/create-data', $controller . '@createData')->name('create_data')->middleware(['permission:' . getPermissionKey($moduleName, 'view', true)]);
    Route::get('/edit/{id?}', $controller . '@edit')->name('edit')->middleware(['permission:' . getPermissionKey($moduleName, 'update', true)]);
    Route::get('/view/{id?}', $controller . '@view')->name('view')->middleware(['permission:' . getPermissionKey($moduleName, 'view', true)]);
    //Save
    Route::post('/store', $controller . '@store')->name('store')->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);
    // Delete
    Route::post('/delete', $controller . '@destroy')->name('delete')->middleware(['permission:' . getPermissionKey($moduleName, 'delete', true)]);


    $paymentController = 'PaymentController';
    $paymentModuleName = 'payment';

    Route::get('/{asset}/payments', $paymentController . '@index')->name('payments.list')->middleware(['permission:' . getPermissionKey($paymentModuleName, 'index', true)]);
    //Create/Update view.
    Route::get('/{asset}/payments/create', $paymentController . '@create')->name('payments.create')->middleware(['permission:' . getPermissionKey($paymentModuleName, 'create', true)]);
    Route::post('/{asset}/payments/create-data', $paymentController . '@createData')->name('payments.create_data')->middleware(['permission:' . getPermissionKey($paymentModuleName, 'view', true)]);
    Route::get('/{asset}/payments/edit/{id?}', $paymentController . '@edit')->name('payments.edit')->middleware(['permission:' . getPermissionKey($paymentModuleName, 'update', true)]);
    Route::get('/{asset}/payments/view/{id?}', $paymentController . '@view')->name('payments.view')->middleware(['permission:' . getPermissionKey($paymentModuleName, 'view', true)]);
    //Save
    Route::post('/{asset}/payments/store', $paymentController . '@store')->name('payments.store')->middleware(['permission:' . getPermissionKey($paymentModuleName, 'create', true)]);
    // Delete
    Route::post('/{asset}/payments/delete', $paymentController . '@destroy')->name('payments.delete')->middleware(['permission:' . getPermissionKey($paymentModuleName, 'delete', true)]);


    $commentController = 'CommentController';
    $commentModuleName = 'comment';

    Route::get('/{asset}/comments', $commentController . '@index')->name('comments.list')->middleware(['permission:' . getPermissionKey($commentModuleName, 'index', true)]);
    //Save
    Route::post('/{asset}/comments', $commentController . '@store')->name('comments.store')->middleware(['permission:' . getPermissionKey($commentModuleName, 'create', true)]);
    // Delete
    Route::post('/{asset}/comments/delete/{id?}', $commentController . '@destroy')->name('comments.delete')->middleware(['permission:' . getPermissionKey($commentModuleName, 'delete', true)]);

});

