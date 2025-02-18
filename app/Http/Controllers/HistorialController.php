<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistorialController extends Controller
{
    public function show()
    {
        $historial = Historial::orderBy('fecha', 'desc')->get();
        return view('historial.index', compact('historial'));
    }
}
