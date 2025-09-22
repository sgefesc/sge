<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::group(['middleware' => []], function () {
    /*Route::get('catraca',function(){
        return response()->json(['message' => 'Catraca API is working'], 200);
    })->withoutMiddleware(\App\Http\Middleware\TrustHosts::class);*/
    Route::get('catraca', [CatracaController::class, 'sendData']);
    Route::post('catraca', [CatracaController::class, 'importData']);
});
route::get('tag/{tag}/{key}', [TagController::class,'tagAccess']);