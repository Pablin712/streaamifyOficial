<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banco;
use App\Models\Recarga;
use Illuminate\Support\Facades\Auth;

class RecargaController extends Controller
{
    public function recargarSaldo()
    {
        $bancos = Banco::all(); // Obtiene todos los bancos
        return view('shopping.recargas', compact('bancos'));
    }

    public function procesarRecarga(Request $request)
    {
        $request->validate([
            'idban' => 'required|exists:bancos,idban',
            'numcomprobante' => 'required|string|max:255|unique:recargas,numcomprobante',
            'valor' => 'required|numeric|min:1',
            'foto' => 'required|image|max:2048',
        ], [
            'numcomprobante.unique' => 'Comprobante ya enviado',
        ]);

        $fotoPath = $request->file('foto')->store('comprobantes', 'public');

        Recarga::create([
            'idcli' => Auth::guard('cliente')->user()->idcli,
            'idban' => $request->idban,
            'numcomprobante' => $request->numcomprobante,
            'valor' => $request->valor,
            'foto' => $fotoPath,
            'idestado' => 1, // Estado inicial, por ejemplo "Pendiente"
        ]);

        return redirect()->route('recargar.index')->with('success', '¡Recarga enviada con éxito, porfavor tener paciencia!');
    }
}
