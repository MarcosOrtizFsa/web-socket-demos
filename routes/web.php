<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas del ejemplo WebSocket
|--------------------------------------------------------------------------
|
| El grupo centraliza las pantallas y el endpoint del ejemplo para que sea
| sencillo agregar middleware o un prefijo cuando la aplicación crezca.
|
*/
Route::middleware('guest')->controller(AuthController::class)->group(function (): void {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('login.store');
    Route::get('/registro', 'showRegister')->name('register');
    Route::post('/registro', 'register')->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::redirect('/', '/chat');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::controller(ChatController::class)->prefix('chat')->name('chat.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/conversaciones', 'start')->name('start');
        Route::get('/conversaciones/{conversation}', 'show')->name('show');
        Route::post('/conversaciones/{conversation}/mensajes', 'send')->name('send');
    });
});
