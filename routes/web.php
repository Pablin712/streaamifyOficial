<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use Illuminate\Routing\RouteGroup;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ProveedorController;
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


Route::get('/valores', function () {
    //$data = ['user' => 'John Doe'];
    return view('inventory.valores');
})->name('valores');

?>