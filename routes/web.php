<?php

use App\Http\Controllers\CredentialController;
use App\Http\Controllers\MkiController;
use App\Http\Controllers\SpbeGatewayController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MkiController::class, 'index'])->name('dashboard');
Route::post('/gateway/login', [SpbeGatewayController::class, 'login'])->name('gateway.login');

Route::prefix('api')->group(function () {
    Route::get('/credentials', [CredentialController::class, 'index']);
    Route::post('/credentials', [CredentialController::class, 'store']);
    Route::put('/credentials/{credential}', [CredentialController::class, 'update']);
    Route::delete('/credentials/{credential}', [CredentialController::class, 'destroy']);
    Route::post('/credentials/verify', [CredentialController::class, 'verifyAccess']);

    Route::get('/datalogs', [SpbeGatewayController::class, 'logs'])->name('api.datalogs');
});




// Route::get('/', [MkiController::class, 'index'])->name('mki.dashboard');