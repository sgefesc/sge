<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IntegracaoBBController;
use App\Http\Controllers\CatracaController;
use App\Http\Controllers\TagController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::group(['middleware' => []], function () {
    /*Route::get('catraca',function(){
        return response()->json(['message' => 'Catraca API is working'], 200);
    })->withoutMiddleware(\App\Http\Middleware\TrustHosts::class);*/
    Route::get('catraca', [CatracaController::class, 'sendData']); // catraca importa tags
    Route::post('catraca', [CatracaController::class, 'importData']); // catraca envia dados presenças
});
route::get('tag/{tag}/{key}', [TagController::class,'tagAccess']);
Route::get('webhook-bb', [IntegracaoBBController::class, 'respostaWebHookCobranca']);
Route::post('webhook-bb', [IntegracaoBBController::class, 'webHookCobranca']);