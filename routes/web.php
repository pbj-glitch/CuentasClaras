<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('grupos.index'));

// Autenticación por sesiones
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::resource('grupos', GrupoController::class)->only(['index', 'store', 'show']);
    Route::post('/grupos/{grupo}/gastos', [GastoController::class, 'store'])->name('gastos.store');
    Route::post('/grupos/{grupo}/pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::post('/grupos/{grupo}/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');

    // Endpoint JSON para polling cada 3 segundos
    Route::get('/grupos/{grupo}/estado', [GrupoController::class, 'estadoJson'])->name('grupos.estado');
});
