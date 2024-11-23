<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PortafolioController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $proyectos = [ 
            ['titulo'=>'Proyecto Nro. 1'], 
            ['titulo'=>'Proyecto Nro. 2'], 
            ['titulo'=>'Proyecto Nro. 3'], 
            ['titulo'=>'Proyecto Nro. 4'] 
        ]; 
        return view('portafolio',['proyectos'=>$proyectos]); 
    }
}
