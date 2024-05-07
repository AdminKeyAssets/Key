<?php

Route::group([ 'prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth:admin'] ], function () {
    /**
     * File manage.
     */
    Route::name('admin.file.')->prefix('files')->group(function() {
        //Upload file.
        Route::post('/upload', 'FileController@upload')->name('upload');
        Route::post('/upload_editor', 'FileController@uploadForEditor')->name('upload_editor');
    });
});
