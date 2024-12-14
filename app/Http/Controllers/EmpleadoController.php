<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empleados = Empleado::all(); // Recuperar todos los empleados
        return view('employee.index', compact('empleados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employee.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombreemp' => 'required|string|max:255',
            'telefonoemp' => 'required|string|max:15',
            'usuarioemp' => 'required|string|max:255|unique:empleados,usuarioemp', // Validar que el usuario sea único
            'passwordemp' => 'required|string|min:4', // Validar longitud y confirmación
            'idrol' => 'required|string',
            'foto_url' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        // Encriptar la contraseña
        $data['passwordemp'] = bcrypt($request->passwordemp);

        // Subir la foto si existe
        if ($request->hasFile('foto_url')) {
            $data['foto_url'] = $request->file('foto_url')->store('fotos', 'public');
        }

        Empleado::create($data);

        return redirect()->route('empleados')->with('success', 'Empleado creado exitosamente.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $empleado = Empleado::findOrFail($id);
        return view('employee.show', compact('empleado'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $empleado = Empleado::findOrFail($id);
        return view('employee.edit', compact('empleado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idemp)
    {
        $request->validate([
            'nombreemp' => 'required|string|max:255',
            'telefonoemp' => 'required|string|max:15',
            'usuarioemp' => 'required|string|max:255|unique:empleados,usuarioemp,' . $idemp . ',idemp',
            'passwordemp' => 'nullable|string|min:4', // Opcional
            'idrol' => 'required|string',
            'foto_url' => 'nullable|image|max:2048',
        ]);

        $empleado = Empleado::findOrFail($idemp);
        $data = $request->all();

        // Encriptar la nueva contraseña solo si se proporciona
        if ($request->filled('passwordemp')) {
            $data['passwordemp'] = bcrypt($request->passwordemp);
        } else {
            unset($data['passwordemp']); // No actualizar si no se envía
        }

        // Subir la foto si existe
        if ($request->hasFile('foto_url')) {
            $data['foto_url'] = $request->file('foto_url')->store('fotos', 'public');
        }

        $empleado->update($data);

        return redirect()->route('empleados')->with('success', 'Empleado actualizado exitosamente.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $empleado = Empleado::findOrFail($id);

        if ($empleado->idrol === 'administrador') {
            return redirect()->route('empleados')->with('error', 'No puedes eliminar a un administrador.');
        }

        $empleado->delete();

        return redirect()->route('empleados')->with('success', 'Empleado eliminado exitosamente.');
    }
}
