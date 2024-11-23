<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactoController extends Controller
{
    
    public function store(Request $request){ 
        request()->validate([ 
        'nombre'=>'required', 
        'correo'=>'required|email', 
        'contenido'=>'required|min:3' 
        ]); 
        return 'Procesar el formulario'; 
        }
}
