<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use Illuminate\Routing\RouteGroup;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ValorController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CostoController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\TipoGastoController;
use App\Http\Controllers\PermisoController;

Route::get('/', HomeController::class);


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login'); // Muestra la vista del login
Route::post('/login', [LoginController::class, 'login']);                      // Procesa el formulario del login

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');    // Cierra la sesión

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

//rutas de navegación en negocio
Route::get('/inicio', function () {
    //$data = ['user' => 'John Doe'];
    return view('inicio');
})->name('inicio');

Route::get('/dashboard', function () {
    //$data = ['user' => 'John Doe'];
    return view('dashboard');
})->name('dashboard');

//Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios');
Route::controller(ServicioController::class)->group(function () {
    Route::get('/servicios', 'index')->name('servicios');
    Route::get('/servicios/create', 'create')->name('servicios.create');
    Route::post('/servicios/createstore', 'store')->name('servicios.store');
    Route::get('/servicios/{id}/edit', 'edit')->name('servicios.edit');
    Route::put('/servicios/{id}', 'update')->name('servicios.update');
    Route::delete('/servicios/{id}', 'destroy')->name('servicios.destroy');
});

Route::controller(ProveedorController::class)->group(function () {
    Route::get('/proveedores', 'index')->name('proveedores');
    Route::get('/proveedores/create', 'create')->name('proveedores.create');
    Route::post('/proveedores/createstore', 'store')->name('proveedores.store');
    Route::get('/proveedores/{id}/edit', 'edit')->name('proveedores.edit');
    Route::put('/proveedores/{id}', 'update')->name('proveedores.update');
    Route::delete('/proveedores/{id}', 'destroy')->name('proveedores.destroy');
});

Route::controller(ValorController::class)->group(function () {
    Route::get('/valores', 'index')->name('valores');
    Route::get('/valores/create', 'create')->name('valores.create');
    Route::post('/valores/createstore', 'store')->name('valores.store');
    Route::get('/valores/{id}/edit', 'edit')->name('valores.edit');
    Route::put('/valores/{id}', 'update')->name('valores.update');
    Route::delete('/valores/{id}', 'destroy')->name('valores.destroy');
});

Route::controller(ClienteController::class)->group(function () {
    Route::get('/clientes', 'index')->name('clientes');
    Route::get('/clientes/create', 'create')->name('clientes.create');
    Route::post('/clientes/createstore', 'store')->name('clientes.store');
    Route::get('/clientes/{id}/edit', 'edit')->name('clientes.edit');
    Route::put('/clientes/{id}', 'update')->name('clientes.update');
    Route::delete('/clientes/{id}', 'destroy')->name('clientes.destroy');
});

Route::controller(CuentaController::class)->group(function () {
    Route::get('/cuentas', 'index')->name('cuentas');
    Route::get('/cuentas/create', 'create')->name('cuentas.create');
    Route::post('/cuentas/createstore', 'store')->name('cuentas.store');
    Route::patch('/cuentas/{id}/status', 'status')->name('cuentas.status');
    Route::get('/cuentas/{id}', 'mensaje')->name('cuentas.mensaje');
    Route::get('/cuentas/{id}/edit', 'edit')->name('cuentas.edit');
    Route::put('/cuentas/{id}', 'update')->name('cuentas.update');
    Route::delete('/cuentas/{id}', 'destroy')->name('cuentas.destroy');
});

Route::controller(CostoController::class)->group(function () {
    Route::get('/costos', 'index')->name('costos');
    Route::post('/costos', 'store')->name('costos.store');
    Route::put('/costos/{id}', 'update')->name('costos.update');
    Route::delete('/costos/{id}', 'destroy')->name('costos.destroy');
});
Route::controller(GastoController::class)->group(function () {
    Route::get('/gastos', 'index')->name('gastos');
    Route::post('/gastos', 'store')->name('gastos.store');
    Route::put('/gastos/{id}', 'update')->name('gastos.update');
    Route::delete('/gastos/{id}', 'destroy')->name('gastos.destroy');
});
Route::controller(TipoGastoController::class)->group(function () {
    Route::get('/tipos', 'index')->name('tipos');
    Route::post('/tipos', 'store')->name('tipos.store');
    Route::put('/tipos/{id}', 'update')->name('tipos.update');
    Route::delete('/tipos/{id}', 'destroy')->name('tipos.destroy');
});
//Route::resource('perfil',PerfilController::class);
Route::controller(PerfilController::class)->group(function () {
    Route::get('/perfil', 'index')->name('perfil');
    //Route::get('/perfil/create', 'create')->name('perfil.create');
    //Route::post('/perfil/createstore', 'store')->name('perfil.store');
    Route::get('/perfil/{id}/edit', 'edit')->name('perfil.edit');
    Route::put('/perfil/{id}', 'update')->name('perfil.update');
    //Route::delete('/perfil/{id}', 'destroy')->name('perfil.destroy');
});

//Route::resource('permiso',PermisoController::class);
Route::controller(PermisoController::class)->group(function () {
    Route::get('/permisos', 'index')->name('permisos');
    Route::get('/permisos/create', 'create')->name('permisos.create');
    Route::post('/permisos/createstore', 'store')->name('permisos.store');
    Route::get('/permisos/{id}/edit', 'edit')->name('permisos.edit');
    Route::put('/permisos/{id}', 'update')->name('permisos.update');
    Route::delete('/permisos/{id}', 'destroy')->name('permisos.destroy');
});
