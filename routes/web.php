<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\GameDayController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('permission:view-users');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:create-users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('permission:create-users');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:edit-users');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:edit-users');
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate')->middleware('permission:deactivate-users');


    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:view-roles');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:create-roles');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:create-roles');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:edit-roles');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:edit-roles');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:delete-roles');
    

    Route::post('/game-day/start', [GameDayController::class, 'start'])->name('game-day.start')->middleware('permission:game-day-start');
    Route::post('/game-day/{id}/close', [GameDayController::class, 'close'])->name('game-day.close')->middleware('permission:game-day-close');
});


require __DIR__ . '/auth.php';
