<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewUsuarioActivo;
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
    public function edit($idcli)
    {
        $usuario = ViewUsuarioActivo::findOrFail($idcli);
        return view('inventory.usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, $idser)
    {
        $request->validate([
            'nombreser' => 'required|string|max:20', // varchar(20)
            'completoser' => 'nullable|numeric',
            'precioser' => 'nullable|numeric',
            'comboser' => 'nullable|numeric',
            'reventaser' => 'nullable|numeric',
            'revcompser' => 'nullable|numeric',
        ]);

        //$servicio = Servicio::findOrFail($idser);
        //$servicio->update($request->all());

        return redirect()->route('usuarios')->with('success', 'Usuario actualizado con éxito.');
    }

    // Eliminar un usuario
    public function destroy($idcli)
    {
        $usuario = ViewUsuarioActivo::findOrFail($idcli);
        //$usuario->delete(); es una vista, hay que implementar lógica, se elimina al usuario, mediante
        //poner false en activodet de la tabla detalles_venta, de esta forma se quitará un usuario

        return redirect()->route('usuarios')->with('success', 'Usuario eliminado con éxito.');
    }
}
