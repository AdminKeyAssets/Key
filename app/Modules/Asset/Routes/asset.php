<?php


Route::prefix('assets')->name('asset.')->group(function () {
    $controller = 'AssetController';
    $moduleName = 'asset';

    Route::get('/list', $controller . '@index')->name('index')->middleware(['permission:' . getPermissionKey($moduleName, 'index', true)]);
    //Create/Update view.
    Route::get('/create', $controller . '@create')->name('create')->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);
    Route::post('/create-data', $controller . '@createData')->name('create_data');
    Route::get('/edit/{id?}', $controller . '@edit')->name('edit')->middleware(['permission:' . getPermissionKey($moduleName, 'update', true)]);
    Route::get('/view/{id?}', $controller . '@view')->name('view')->middleware(['permission:' . getPermissionKey($moduleName, 'view', true)]);
    Route::get('/names', $controller . '@getAssetsToClone')->name('assets.to.clone')->middleware(['permission:' . getPermissionKey($moduleName, 'view', true)]);
    Route::post('/clone/{name?}', $controller . '@clone')->name('clone')->middleware(['permission:' . getPermissionKey($moduleName, 'view', true)]);

    //For investor
    Route::get('', $controller . '@myassets')->name('myassets')->middleware(['auth:investor']);
    Route::get('/details/{id?}', $controller . '@investorView')->name('details')->middleware(['auth:investor']);
    //Save
    Route::post('/store', $controller . '@store')->name('store')->middleware(['permission:' . getPermissionKey($moduleName, 'create', true)]);
    // Delete
    Route::post('/delete', $controller . '@destroy')->name('delete')->middleware(['permission:' . getPermissionKey($moduleName, 'delete', true)]);
    Route::get('/filter-options', $controller . '@filterOptions')
        ->name('assets.filters');


    $paymentController = 'PaymentsHistoryController';
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
    //Export Rentals
    Route::get('/{asset}/payments/export', $paymentController . '@export')
        ->name('export');
    Route::get('/{asset}/payments-history/export', $paymentController . '@exportHistory')
        ->name('export.history');

    $commentController = 'CommentController';
    $commentModuleName = 'comment';

    Route::get('/{asset}/comments', $commentController . '@index')->name('comments.list');
    Route::get('/comments/unread', $commentController . '@unread')->name('comments.list.unread');
    Route::get('/comments/{id}', $commentController . '@read')->name('comments.view');
    //Save
    Route::post('/{asset}/comments', $commentController . '@store')->name('comments.store')->middleware(['permission:' . getPermissionKey($commentModuleName, 'create', true)]);
    Route::post('/{asset}/investor/comments', $commentController . '@investorStore')->name('comments.investor.store')->middleware(['auth:investor']);
    // Delete
    Route::post('/{asset}/comments/delete/{id?}', $commentController . '@destroy')->name('comments.delete')->middleware(['permission:' . getPermissionKey($commentModuleName, 'delete', true)]);


    $leaseController = 'RentalPaymentsHistoryController';
    $leaseModuleName = 'rental';

    Route::get('/{asset}/rental', $leaseController . '@index')->name('rental.index')->middleware(['permission:' . getPermissionKey($leaseModuleName, 'index', true)]);
    //Create/Update view.
    Route::get('/{asset}/rental/create', $leaseController . '@create')->name('rental.create')->middleware(['permission:' . getPermissionKey($leaseModuleName, 'create', true)]);
    Route::post('/{asset}/rental/create-data', $leaseController . '@createData')->name('rental.create_data')->middleware(['permission:' . getPermissionKey($leaseModuleName, 'view', true)]);
    Route::get('/{asset}/rental/edit/{id?}', $leaseController . '@edit')->name('rental.edit')->middleware(['permission:' . getPermissionKey($leaseModuleName, 'update', true)]);
    Route::get('/{asset}/rental/view/{id?}', $leaseController . '@view')->name('rental.view')->middleware(['permission:' . getPermissionKey($leaseModuleName, 'view', true)]);
    //Save
    Route::post('/{asset}/rental/store', $leaseController . '@store')->name('rental.store')->middleware(['permission:' . getPermissionKey($leaseModuleName, 'create', true)]);
    // Delete
    Route::post('/{asset}/rental/delete', $leaseController . '@destroy')->name('rental.delete')->middleware(['permission:' . getPermissionKey($leaseModuleName, 'delete', true)]);
    //Complete
    Route::post('/{asset}/rental/complete', $leaseController . '@complete')->name('rental.complete')->middleware(['permission:' . getPermissionKey($leaseModuleName, 'update', true)]);
    //Export Rentals
    Route::get('/{asset}/rental/export', $leaseController . '@export')
        ->name('export');
    Route::get('/{asset}/rental-payments-history/export', $leaseController . '@exportHistory')
        ->name('export.history');


    $revenueController = 'RevenueController';
    $revenueModuleName = 'revenue';

    Route::get('/revenue', $revenueController . '@investorRevenues')->name('revenue.investor')->middleware(['auth:investor']);
    Route::get('/revenues', $revenueController . '@index')->name('revenue.index')->middleware(['permission:' . getPermissionKey($moduleName, 'index', true)]);
    Route::get('/revenues/export', $revenueController . '@export')
        ->name('revenue_export');
    Route::get('/revenues/view/{id?}', $revenueController . '@view')->name('revenue.view');
    Route::get('/revenues/details/{id?}', $revenueController . '@investorView')->name('revenue.details')->middleware(['auth:investor']);
    Route::post('/revenues/create-data', $revenueController . '@createData')->name('revenue_create_form_data');
    Route::get('/revenues/filter-options', $revenueController . '@filterOptions')
        ->name('revenues.filters');

    $notificationController = 'NotificationController';
    $notificationModuleName = 'notification';

    Route::get('/notifications/pending-payment', $notificationController . '@payment')->name('notification.list.payment');
    Route::get('/notifications/pending-rentals', $notificationController . '@rental')->name('notification.list.rental');


    $currentValueController = 'CurrentValueController';
    $currentValueModuleName = 'currentValue';

    Route::post('/current-value/update/{currentValue}', $currentValueController . '@update')->name('update.current_value');
    Route::delete('/current-value/delete/{currentValue}', $currentValueController . '@destroy')->name('delete.current_value');


    $investmentController = 'InvestmentController';
    $investmentModuleName = 'investment';

    Route::get('/{asset}/investment', $investmentController . '@index')->name('investment.index')->middleware(['permission:' . getPermissionKey($investmentModuleName, 'index', true)]);
    //Create/Update view.
    Route::get('/{asset}/investment/create', $investmentController . '@create')->name('investment.create')->middleware(['permission:' . getPermissionKey($investmentModuleName, 'create', true)]);
    Route::post('/{asset}/investment/create-data', $investmentController . '@createData')->name('investment.create_data')->middleware(['permission:' . getPermissionKey($investmentModuleName, 'view', true)]);
    Route::get('/{asset}/investment/edit/{id?}', $investmentController . '@edit')->name('investment.edit')->middleware(['permission:' . getPermissionKey($investmentModuleName, 'update', true)]);
    Route::get('/{asset}/investment/view/{id?}', $investmentController . '@view')->name('investment.view')->middleware(['permission:' . getPermissionKey($investmentModuleName, 'view', true)]);
    //Save
    Route::post('/{asset}/investment/store', $investmentController . '@store')->name('investment.store')->middleware(['permission:' . getPermissionKey($investmentModuleName, 'create', true)]);
    // Delete
    Route::post('/{asset}/investment/delete', $investmentController . '@destroy')->name('investment.delete')->middleware(['permission:' . getPermissionKey($investmentModuleName, 'delete', true)]);

});

