<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameDayController;
use App\Http\Controllers\GameTableController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    Route::get('/game-day/current', [GameDayController::class, 'current']);
    Route::post('/game-day/start', [GameDayController::class, 'start']);
    Route::post('/game-day/close', [GameDayController::class, 'close']);

    Route::get('/game-tables/active', [GameTableController::class, 'apiActive']);
    Route::get('/game-tables/by-mac/{mac}', [GameTableController::class, 'apiByMac']);
    Route::get('/game-tables/{id}/configuration', [GameTableController::class, 'apiConfiguration']);

    Route::get('/game-tables', [GameTableController::class, 'apiIndex']);
    Route::get('/game-tables/{id}', [GameTableController::class, 'apiShow'])->whereNumber('id');
});

// https://documenter.getpostman.com/view/31035377/2sBXcEmMLA