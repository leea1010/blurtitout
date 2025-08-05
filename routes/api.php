<?php

use App\Http\Controllers\PropertySaleHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::apiResource('property-sale-histories', PropertySaleHistoryController::class);

Route::get('property-sale-histories/property-number/{propertyNumber}', [PropertySaleHistoryController::class, 'getByPropertyNumber'])
    ->name('property-sale-histories.by-property-number');
