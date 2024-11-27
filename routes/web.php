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
use App\Models\Servicio;

Route::get('/', HomeController::class);

Route::get('/login2',LoginController::class);

Route::get('/login', function () {
    return view('auth.login');
}) -> name('login');

Route::get('/register', function () {
    return view('auth.register');
})-> name('register');

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
Route::controller(ServicioController::class)->group(function(){
    Route::get('/servicios', 'index')->name('servicios');
    Route::get('/servicios/create', 'create')->name('servicios.create');
    Route::post('/servicios/createstore', 'store')->name('servicios.store');
    Route::get('/servicios/{id}/edit', 'edit')->name('servicios.edit');
    Route::put('/servicios/{id}', 'update')->name('servicios.update');
    Route::delete('/servicios/{id}', 'destroy')->name('servicios.destroy');
});

Route::controller(ProveedorController::class)->group(function(){
    Route::get('/proveedores', 'index')->name('proveedores');
    Route::get('/proveedores/create', 'create')->name('proveedores.create');
    Route::post('/proveedores/createstore', 'store')->name('proveedores.store');
    Route::get('/proveedores/{id}/edit', 'edit')->name('proveedores.edit');
    Route::put('/proveedores/{id}', 'update')->name('proveedores.update');
    Route::delete('/proveedores/{id}', 'destroy')->name('proveedores.destroy');
});

Route::controller(ValorController::class)->group(function(){
    Route::get('/valores', 'index')->name('valores');
    Route::get('/valores/create', 'create')->name('valores.create');
    Route::post('/valores/createstore', 'store')->name('valores.store');
    Route::get('/valores/{id}/edit', 'edit')->name('valores.edit');
    Route::put('/valores/{id}', 'update')->name('valores.update');
    Route::delete('/valores/{id}', 'destroy')->name('valores.destroy');
});

Route::controller(ClienteController::class)->group(function(){
    Route::get('/clientes', 'index')->name('clientes');
    Route::get('/clientes/create', 'create')->name('clientes.create');
    Route::post('/clientes/createstore', 'store')->name('clientes.store');
    Route::get('/clientes/{id}/edit', 'edit')->name('clientes.edit');
    Route::put('/clientes/{id}', 'update')->name('clientes.update');
    Route::delete('/clientes/{id}', 'destroy')->name('clientes.destroy');
});

Route::controller(CuentaController::class)->group(function(){
    Route::get('/cuentas', 'index')->name('cuentas');
    Route::get('/cuentas/create', 'create')->name('cuentas.create');
    Route::post('/cuentas/createstore', 'store')->name('cuentas.store');
    Route::get('/cuentas/{id}/edit', 'edit')->name('cuentas.edit');
    Route::put('/cuentas/{id}', 'update')->name('cuentas.update');
    Route::delete('/cuentas/{id}', 'destroy')->name('cuentas.destroy');
});

Route::controller(CostoController::class)->group(function(){
    Route::get('/costos','index')->name('costos');
    Route::post('/costos', 'store')->name('costos.store');
    Route::put('/costos/{id}', 'update')->name('costos.update');
    Route::delete('/costos/{id}','destroy')->name('costos.destroy');
});


?>