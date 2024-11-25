<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\LoginController;
use Illuminate\Routing\RouteGroup;
Route::get('/', HomeController::class);

Route::get('/login2',LoginController::class);

Route::controller(CursoController::class)->group(function(){
    Route::get('/cursos', 'index');
    Route::get('/cursos/create', 'create');
    Route::get('/cursos/{curso}', 'show');    
});

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

Route::get('/servicios', function () {
    //$data = ['user' => 'John Doe'];
    return view('servicios');
})->name('servicios');


//ing Mauricio Rea
?>