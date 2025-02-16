<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Empleado;
use Spatie\Permission\Models\Role;
use App\Models\Historial;
use Carbon\Carbon;
use App\Models\Rol;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $this->authorizeRole(['administrador']);
        //$empleados = Empleado::all(); // Recuperar todos los empleados
        $empleados = Empleado::with(['ventas' => function ($query) {
            $query->select('idemp'); // Solo seleccionamos idemp para optimizar
        }])
            ->withCount('ventas') // Total de ventas por empleado
            ->withCount([
                'ventas as ventas_mes_actual' => function ($query) {
                    $query->whereMonth('fechaven', Carbon::now()->month)
                        ->whereYear('fechaven', Carbon::now()->year);
                }
            ])
            ->get();
        $roles = Role::all();
        return view('employee.index', compact('empleados', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeRole(['administrador']);
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
            'email' => 'nullable|email|max:255',
        ]);

        $data = $request->all();

        // Encriptar la contraseña
        //$data['passwordemp'] = bcrypt($request->passwordemp);

        // Subir la foto si existe
        if ($request->hasFile('foto_url')) {
            $file = $request->file('foto_url');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension(); // Generar un nombre único
            $destinationPath = public_path('storage/fotos'); // Carpeta en public/storage/fotos
            $file->move($destinationPath, $filename); // Mover el archivo
            $data['foto_url'] = 'fotos/' . $filename; // Ruta para guardar
        }

        $empleado = Empleado::create($data);
        Historial::create([
            'accion' => 'Creación de empleado',
            'descripcion' =>  'Datos: ' . json_encode($empleado), // Campo opcional
            'realizado_por' => (Auth::user()->nombreemp ?? 'laravel') . ' | ' . $request->ip(), // Almacena el nombre del usuario o 'laravel' si no hay nombreemp 
            'fecha' => now(),
        ]);
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
        if (Auth::user()->idemp == $id || Auth::user()->idemp == 1) {
            return view('employee.edit', compact('empleado'));
        } else {
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.')->send();
        }
    }

    public function editRoles(string $id)
    {
        $roles = Role::all();
        $empleado = Empleado::findOrFail($id);
        return view('employee.roles', compact('roles', 'empleado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idemp)
    {
        $empleado = Empleado::findOrFail($idemp);
        $rules = [
            'nombreemp' => 'required|string|max:255',
            'telefonoemp' => 'required|string|max:15',
            'usuarioemp' => 'required|string|max:255|unique:empleados,usuarioemp,' . $idemp . ',idemp',
            'passwordemp' => 'nullable|string|min:4',
            'foto_url' => 'nullable|image|max:2048',
            'email' => 'nullable|email|max:255',
        ];

        // Si el usuario es administrador, validar el campo `idrol`
        if (Auth::user()->idrol === 'administrador') {
            $rules['idrol'] = 'required|string';
        }

        // Validar los datos
        $request->validate($rules);

        $data = $request->all();

        // Si el usuario NO es administrador, conservar el rol actual
        if (Auth::user()->idrol !== 'administrador') {
            $data['idrol'] = $empleado->idrol; // Mantener el rol actual
        }
        // Contraseña opcional
        if ($request->filled('passwordemp')) {
        } else {
            unset($data['passwordemp']); // No actualizar si no se envía
        }
        //Email opcional
        if ($request->filled('email')) {
        } else {
            unset($data['email']); // No actualizar si no se envía
        }

        // Subir la foto si existe
        if ($request->hasFile('foto_url')) {
            // Eliminar la foto anterior si existe
            if (!empty($empleado->foto_url) && file_exists(public_path('storage/' . $empleado->foto_url))) {
                unlink(public_path('storage/' . $empleado->foto_url)); // Eliminar el archivo antiguo
            }
            $file = $request->file('foto_url');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension(); // Generar un nombre único
            $destinationPath = public_path('storage/fotos'); // Carpeta en public/storage/fotos
            $file->move($destinationPath, $filename); // Mover el archivo
            $data['foto_url'] = 'fotos/' . $filename; // Ruta para guardar
        }
        Historial::create([
            'accion' => 'Actualización de empleado',
            'descripcion' =>  'Datos antigüos: ' . json_encode($empleado), // Campo opcional
            'realizado_por' => (Auth::user()->nombreemp ?? 'laravel') . ' | ' . $request->ip(), // Almacena el nombre del usuario o 'laravel' si no hay nombreemp 
            'fecha' => now(),
        ]);

        $empleado->update($data);

        return redirect()->route('empleados')->with('success', 'Empleado actualizado exitosamente.');
    }

    public function updateRol(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        $user = Auth::user();
        // Validación
        $request->validate([
            'idrol' => 'required|exists:roles,idrol', // Asegura que el rol exista en la BD
        ]);
        // Si el empleado es administrador, solo `idemp = 1` puede modificarlo
        if ($empleado->idrol === 'administrador' && $user->idemp != 1) {
            return redirect()->back()->with('error', 'No tienes permiso para cambiar el rol de un administrador.');
        }
        // Actualiza el rol
        $empleado->idrol = $request->idrol;
        $empleado->save();
        Historial::create([
            'accion' => 'Cambio de Rol de empleado',
            'descripcion' =>  'Empleado: ' . json_encode($empleado), // Campo opcional
            'realizado_por' => $user->nombreemp . ' | ' . $request->ip(), // Almacena el nombre del usuario o 'laravel' si no hay nombreemp 
            'fecha' => now(),
        ]);
        return redirect()->back()->with('success', 'Rol actualizado correctamente.');
    }

    public function updateRoles(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id); // O usa tu modelo de Empleado
        $empleado->syncRoles($request->input('roles', [])); // Asigna roles

        return redirect()->route('empleados')->with('success', 'Roles actualizados correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $empleado = Empleado::findOrFail($id);

        // Opcional: Validar restricciones si es necesario
        // Por ejemplo, no permitir eliminar administradores (puedes ajustar esta lógica según tus necesidades)
        if ($empleado->idrol === 'administrador') {
            return redirect()->route('empleados')->withErrors(['error' => 'No puedes eliminar a un administrador.']);
        }
        Historial::create([
            'accion' => 'Eliminación de empleado',
            'descripcion' =>  'Datos: ' . json_encode($empleado), // Campo opcional
            'realizado_por' => (Auth::user()->nombreemp ?? 'laravel'), // Almacena el nombre del usuario o 'laravel' si no hay nombreemp 
            'fecha' => now(),
        ]);

        $empleado->delete();

        return redirect()->route('empleados')->with('success', 'Empleado eliminado exitosamente.');
    }
    private function authorizeRole(array $roles)
    {
        $userRole = Auth::user()->idrol;

        if (!in_array($userRole, $roles)) {
            // Redirigir a la vista anterior con una alerta
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.')->send();
        }
    }
}
