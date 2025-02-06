<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;

Route::post('login', [LoginController::class, 'loginApi']);
Route::post('logout', [LoginController::class, 'logoutApi']);

Route::middleware('auth:api')->get('ventas', [VentaController::class, 'indexApi']);
Route::middleware('auth:api')->get('clientes', [ClienteController::class, 'indexApi']);



