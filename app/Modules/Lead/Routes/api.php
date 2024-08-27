<?php

use App\Modules\Lead\Http\Controllers\LeadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/lead', function (Request $request) {
    // return $request->lead();
})->middleware('auth:api');

Route::post('/leads', [LeadController::class, 'storeApi'])->middleware('auth:api');

Route::get('/statistics', [LeadController::class, 'statistics'])->middleware('auth:api');

