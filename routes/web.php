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

/*Route::get('cursos/{curso}', function($curso){
    
});

Route::get('cursos/{curso}/{categoria?}', function ($curso, $categoria = null) {
    if($categoria){
        return "Bienvenido al curso $curso, de la categoria $categoria";
    }
    else{
        return "Bienvenido al curso:".$curso;
    }
});*/
?>