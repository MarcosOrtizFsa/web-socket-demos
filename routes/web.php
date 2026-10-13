<?php

declare(strict_types=1);

use App\Http\Controllers\MensajeController;
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
Route::controller(MensajeController::class)->group(function (): void {
    Route::get('/', 'emisor');
    Route::get('/emisor', 'emisor')->name('emisor');
    Route::get('/receptor', 'receptor')->name('receptor');
    Route::post('/mensajes', 'enviar')->name('mensaje.enviar');
});
