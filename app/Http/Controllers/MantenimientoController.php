<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mantenimiento;
use App\Models\Cuenta;

class MantenimientoController extends Controller
{
    public function index()
    {
        // Obtener todos los mantenimientos
        $mantenimientos = Mantenimiento::orderBy('fechaman', 'asc')->get();

        // Retornar la vista con los mantenimientos
        return view('inventory.mantenimientos.index', compact('mantenimientos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $cuentas = Cuenta::all();
        // Retornar la vista de crear mantenimiento con las cuentas
        return view('inventory.mantenimientos.create', compact('cuentas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validación de los datos
        $request->validate([
            'idcue' => 'required|exists:cuentas,idcue',  // Aseguramos que idcue exista en la tabla cuentas
            'fechaman' => 'required|date',               // Fecha de mantenimiento válida
            'descripcionman' => 'required|string|max:255', // Descripción obligatoria
        ]);
        // Crear el nuevo mantenimiento
        Mantenimiento::create([
            'idcue' => $request->idcue,                  // Asignamos el idcue del formulario
            'fechaman' => $request->fechaman,            // Asignamos la fecha de mantenimiento
            'descripcionman' => $request->descripcionman, // Asignamos la descripción del mantenimiento
        ]);
        // Redirigir al índice de mantenimientos con mensaje de éxito
        return redirect()->route('mantenimientos')->with('success', 'Mantenimiento creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mantenimiento  $mantenimiento
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        // Obtener el mantenimiento a editar
        $mantenimiento = Mantenimiento::findOrFail($id);

        // Obtener todas las cuentas disponibles

        // Retornar la vista con el mantenimiento y las cuentas
        return view('inventory.mantenimientos.edit', compact('mantenimiento'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mantenimiento  $mantenimiento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validar los datos recibidos
        $request->validate([
            //'idcue' => 'required|exists:cuentas,idcue',  // idcue debe ser válido
            'fechaman' => 'required|date',               // Fecha de mantenimiento válida
            'descripcionman' => 'required|string|max:255', // Descripción válida
        ]);

        // Obtener el mantenimiento a editar
        $mantenimiento = Mantenimiento::findOrFail($id);

        // Actualizar los datos
        $mantenimiento->update([
            //'idcue' => $request->idcue,
            'fechaman' => $request->fechaman,
            'descripcionman' => $request->descripcionman,
        ]);

        // Redirigir con mensaje de éxito
        return redirect()->route('mantenimientos')->with('success', 'Mantenimiento actualizado exitosamente.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mantenimiento  $mantenimiento
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Buscar el mantenimiento por su ID
        $mantenimiento = Mantenimiento::findOrFail($id);
        // Eliminar el mantenimiento
        $mantenimiento->delete();
        // Redirigir al índice de mantenimientos con un mensaje de éxito
        return redirect()->route('mantenimientos')->with('success', 'Mantenimiento eliminado exitosamente.');
    }
}
