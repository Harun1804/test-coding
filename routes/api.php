<?php

use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PertamaController;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('store',[PertamaController::class,'store']);

Route::prefix('inventory')->name('inventory')->group(function(){
    Route::post('orderQty',[InventoryController::class,'ordererQty']);
    Route::post('currentQty',[InventoryController::class,'currentQty']);
    Route::put('update/{inventory}',[InventoryController::class,'update']);
    Route::delete('delete/{inventory}',[InventoryController::class,'destroy']);
});
