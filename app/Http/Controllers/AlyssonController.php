<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AlyssonController extends Controller
{
    public function index()
    {
        $cliente = Auth::guard('cliente')->user();
        $esAlysson = $cliente
            && $cliente->nombrecli === 'Alysson Julieth Pilataxi Valencia'
            && $cliente->telefonocli === '+593 97 890 0148';
        return view('principal', compact('esAlysson'));
    }
    public function exclusive()
    {
        $cliente = Auth::guard('cliente')->user();
        // Solo deja pasar si es Alysson
        if (
            !$cliente ||
            $cliente->nombrecli !== 'Alysson Julieth Pilataxi Valencia' ||
            $cliente->telefonocli !== '+593 97 890 0148'
        ) {
            abort(403, 'No autorizado');
        }
        return view('shopping.alysson');
    }
}
