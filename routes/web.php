<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\InvitacionController;
use App\Http\Controllers\MiembroController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('grupos.index'));

// Autenticación por sesiones
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::resource('grupos', GrupoController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/grupos/{grupo}/saldos', [GrupoController::class, 'saldos'])->name('grupos.saldos');

    // Gastos (carga manual; la lectura de boletas con IA va en GroqController)
    Route::get('/grupos/{grupo}/gastos/nuevo', [GastoController::class, 'create'])->name('gastos.create');
    Route::post('/grupos/{grupo}/gastos', [GastoController::class, 'store'])->name('gastos.store');

    // Integrantes sin cuenta
    Route::post('/grupos/{grupo}/miembros', [MiembroController::class, 'store'])->name('miembros.store');

    // Pagos entre integrantes
    Route::post('/grupos/{grupo}/pagos', [PagoController::class, 'store'])->name('pagos.store');

    // Invitar: código del grupo y solicitudes pendientes
    Route::get('/grupos/{grupo}/invitar', [InvitacionController::class, 'show'])->name('invitar.show');
    Route::post('/grupos/{grupo}/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');

    Route::scopeBindings()->group(function () {
        Route::delete('/grupos/{grupo}/gastos/{gasto}', [GastoController::class, 'destroy'])->name('gastos.destroy');
        Route::delete('/grupos/{grupo}/pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy');
        Route::post('/grupos/{grupo}/solicitudes/{solicitud}/aceptar', [SolicitudController::class, 'aceptar'])->name('solicitudes.aceptar');
        Route::post('/grupos/{grupo}/solicitudes/{solicitud}/rechazar', [SolicitudController::class, 'rechazar'])->name('solicitudes.rechazar');
    });

    // Unirse con el código del grupo
    Route::get('/unirse', [InvitacionController::class, 'unirse'])->name('unirse.index');
    Route::post('/unirse', [InvitacionController::class, 'buscar'])->name('unirse.buscar');
    Route::get('/unirse/{codigo}', [InvitacionController::class, 'grupo'])->name('unirse.show');

    // Endpoint JSON para polling cada 3 segundos
    Route::get('/grupos/{grupo}/estado', [GrupoController::class, 'estadoJson'])->name('grupos.estado');
});
