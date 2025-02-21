<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistorialController extends Controller
{
    public function show()
    {
        if (!Auth::user()->hasPermissionTo('historial')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }
        $historial = Historial::orderBy('fecha', 'desc')->get();
        return view('historial.index', compact('historial'));
    }
}
