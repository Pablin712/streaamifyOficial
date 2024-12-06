<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewUsuarioActivo;
use App\Models\DetalleVenta;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = ViewUsuarioActivo::orderBy('fecha_vencimiento')->orderBy('nombre_cliente')->get();
        return view('inventory.usuarios.index', compact('usuarios'));
    }
    // Crear
    public function create()
    {
        //return view('inventory.servicios.create');
    }

    public function store(Request $request)
    {
        //es una vista
    }

    // Editar un usuario existente
    public function change($iddet)
    {
        $usuario = ViewUsuarioActivo::where('iddet',$iddet);
        return view('inventory.usuarios.change', compact('usuario'));
    }

    public function update(Request $request, $iddet)
    {
        $request->validate([
            'idcue' => 'required|exists:cuentas,id', // La cuenta debe existir en la tabla 'cuentas'
            'perfil' => 'required|integer|min:1', // Validación para un número entero
            'fecha_vencimiento' => 'required'
        ]);
        //$iddet = $request->iddet;
        $detalle = DetalleVenta::findOrFail($iddet);

        // Actualizar los campos del usuario
        $detalle->idper = $request->idcue.'.'.$request->perfil;
        $detalle->fechavendet = $request->fecha_vencimiento;
        // Guardar los cambios
        $detalle->save();

        // Redirigir con un mensaje de éxito
        return redirect()->route('usuarios')->with('success', 'Usuario actualizado exitosamente.');
    }

    // Eliminar un usuario
    public function destroy($iddet)
    {
        $detalle = DetalleVenta::findOrFail($iddet);
        // Cambiar el estado de activodet (de true a false o de false a true)
        $detalle->activodet = !$detalle->activodet; // Invertir el valor (true -> false o false -> true)
        // Guardar el cambio en la base de datos
        $detalle->save();

        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('usuarios')->with('success', 'Usuario eliminado con éxito.');;
    }
}
