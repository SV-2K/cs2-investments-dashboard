<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemsController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->middleware('guest')->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'signIn')->name('signIn');
    Route::get('/register', 'register')->name('register');
    Route::post('/register', 'signUp')->name('signUp');
});

Route::middleware('auth')->group(function () {
    Route::get('/', function() {
        return redirect()->route('items.index');
    });
    Route::get('/items', [ItemsController::class, 'index'])->name('items.index');
});
