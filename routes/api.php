<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameDayController;
use App\Http\Controllers\GameTableController;
use App\Http\Controllers\PayoutRuleController;
use App\Models\GameType;
use App\Models\PayoutRule;

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

    Route::get('/payout-rules/game-type/{id}', [PayoutRuleController::class, 'apiByGameType']);
    // Route::get('/payout-rules/{id}', [PayoutRuleController::class, 'apiShow'])->whereNumber('id');

    // API route for fetching payout rules by game type (used in form JS)
    Route::get('/payout-rules/{gameTypeId}', function ($gameTypeId) {
        return PayoutRule::where('game_type_id', $gameTypeId)->get();
    });

    // API route for fetching game type config fields (used in form JS)
    Route::get('/game-types/{id}', function ($id) {
        return GameType::findOrFail($id);
    });
});

// https://documenter.getpostman.com/view/31035377/2sBXcEmMLA