<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
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
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('permission:store-users');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:edit-users');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:update-users');
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate')->middleware('permission:deactivate-users');


    Route::get('/roles', [UserController::class, 'index'])->name('roles.index')->middleware('permission:view-roles');
    Route::get('/roles/create', [UserController::class, 'create'])->name('roles.create')->middleware('permission:create-roles');
    Route::post('/roles', [UserController::class, 'store'])->name('roles.store')->middleware('permission:store-roles');
    Route::get('/roles/{role}/edit', [UserController::class, 'edit'])->name('roles.edit')->middleware('permission:edit-roles');
    Route::put('/roles/{role}', [UserController::class, 'update'])->name('roles.update')->middleware('permission:update-roles');
    

    Route::post('/roles/{id}/permissions')->middleware('permission:assign-permissions');
});


require __DIR__ . '/auth.php';
