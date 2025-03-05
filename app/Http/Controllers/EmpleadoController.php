<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use Spatie\Permission\Models\Role;
use App\Models\Historial;
use Carbon\Carbon;
use App\Models\Rol; // Verifica si lo necesitas o se utiliza en el código
use Illuminate\Support\Facades\Auth;

class EmpleadoController extends Controller
{
    /*
    // Constructor original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:empleados')->only('index');
        $this->middleware('can:empleados.store')->only('create', 'store');
        $this->middleware('can:empleados.update')->only('edit', 'update');
        $this->middleware('can:empleados.destroy')->only('destroy');
    }
    */

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('empleados')) {
            abort(403, 'No tienes permiso para ver los empleados.');
        }

        // Recuperar empleados junto con la cantidad de ventas y ventas del mes actual
        $empleados = Empleado::with(['ventas' => function ($query) {
            $query->select('idemp');
        }])
            ->withCount('ventas')
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
        if (!Auth::user()->hasPermissionTo('empleados.store')) {
            abort(403, 'No tienes permiso para crear empleados.');
        }
        return view('employee.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('empleados.store')) {
            abort(403, 'No tienes permiso para crear empleados.');
        }

        $request->validate([
            'nombreemp' => 'required|string|max:255',
            'telefonoemp' => 'required|string|max:15',
            'usuarioemp' => 'required|string|max:255|unique:empleados,usuarioemp',
            'passwordemp' => 'required|string|min:4',
            'foto_url' => 'nullable|image|max:2048',
            'email' => 'nullable|email|max:255',
        ]);

        $data = $request->all();

        // Aquí puedes encriptar la contraseña si es necesario
        // $data['passwordemp'] = bcrypt($request->passwordemp);

        // Subir la foto si existe
        if ($request->hasFile('foto_url')) {
            $file = $request->file('foto_url');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('storage/fotos');
            $file->move($destinationPath, $filename);
            $data['foto_url'] = 'fotos/' . $filename;
        }

        $empleado = Empleado::create($data);

        Historial::create([
            'accion' => 'Creación de empleado',
            'descripcion' => 'Datos: ' . json_encode($empleado),
            'realizado_por' => (Auth::user()->nombreemp ?? 'laravel') . ' | ' . $request->ip(),
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
        return view('employee.edit', compact('empleado'));
    }

    public function editRoles(string $id)
    {
        // Verifica si el usuario tiene permiso para actualizar roles de empleados
        if (!Auth::user()->hasPermissionTo('empleados.update')) {
            abort(403, 'No tienes permiso para editar roles.');
        }
        $roles = Role::all();
        $empleado = Empleado::findOrFail($id);
        return view('employee.roles', compact('roles', 'empleado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('empleados.update') || (Auth::user()->idemp != $id && Auth::user()->idemp != 1)) {
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.')->send();
        }
        $empleado = Empleado::findOrFail($id);
        $rules = [
            'nombreemp' => 'required|string|max:255',
            'telefonoemp' => 'required|string|max:15',
            'usuarioemp' => 'required|string|max:255|unique:empleados,usuarioemp,' . $id . ',idemp',
            'passwordemp' => 'nullable|string|min:4',
            'foto_url' => 'nullable|image|max:2048',
            'email' => 'nullable|email|max:255',
        ];

        $request->validate($rules);
        $data = $request->all();
        // Si no se envía contraseña, no actualizarla
        if (!$request->filled('passwordemp')) {
            unset($data['passwordemp']);
        }
        // Si no se envía email, no actualizarlo
        if (!$request->filled('email')) {
            unset($data['email']);
        }

        // Subir foto si existe
        if ($request->hasFile('foto_url')) {
            if (!empty($empleado->foto_url) && file_exists(public_path('storage/' . $empleado->foto_url))) {
                unlink(public_path('storage/' . $empleado->foto_url));
            }
            $file = $request->file('foto_url');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('storage/fotos');
            $file->move($destinationPath, $filename);
            $data['foto_url'] = 'fotos/' . $filename;
        }

        Historial::create([
            'accion' => 'Actualización de empleado',
            'descripcion' => 'Datos antiguos: ' . json_encode($empleado),
            'realizado_por' => (Auth::user()->nombreemp ?? 'laravel') . ' | ' . $request->ip(),
            'fecha' => now(),
        ]);

        $empleado->update($data);

        return redirect()->route('empleados.edit', ['id' => $empleado->idemp])
            ->with('success', 'Perfil actualizado exitosamente.');
    }

    public function updateRoles(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->syncRoles($request->input('roles', []));
        return redirect()->route('empleados')->with('success', 'Roles actualizados correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!Auth::user()->hasPermissionTo('empleados.destroy')) {
            abort(403, 'No tienes permiso para eliminar empleados.');
        }
        $empleado = Empleado::findOrFail($id);
        Historial::create([
            'accion' => 'Eliminación de empleado',
            'descripcion' => 'Datos: ' . json_encode($empleado),
            'realizado_por' => (Auth::user()->nombreemp ?? 'laravel'),
            'fecha' => now(),
        ]);
        $empleado->delete();
        return redirect()->route('empleados')->with('success', 'Empleado eliminado exitosamente.');
    }
}
