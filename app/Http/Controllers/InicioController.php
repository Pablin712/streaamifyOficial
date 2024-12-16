<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InicioController extends Controller
{
    public function show()
    {
        // Opcionalmente, puedes pasar datos a la vista si es necesario
        return view('inicio');
    }
}
