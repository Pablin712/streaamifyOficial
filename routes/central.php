<?php

use App\Http\Controllers\Central\AuthController;
use App\Http\Controllers\Central\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas centrales (dominio de administracion de Tenants)
|--------------------------------------------------------------------------
|
| Este dominio NUNCA inicializa tenancy (ver PreventAccessFromCentralDomains
| y el orden de registro en bootstrap/app.php). Autenticacion propia via
| guard 'central' (App\Models\SuperAdmin, tabla en la BD central) — no
| tiene relacion con los guards 'empleado'/'cliente' de cada Tenant.
|
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('central.login');
Route::post('/login', [AuthController::class, 'login'])->name('central.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('central.logout');

Route::middleware('auth.central')->group(function () {
    Route::get('/', [TenantController::class, 'index'])->name('central.dashboard');
    Route::post('/tenants', [TenantController::class, 'store'])->name('central.tenants.store');
});
