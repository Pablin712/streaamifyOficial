<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use Spatie\Permission\Models\Role;
use App\Models\Historial;
use Carbon\Carbon;
use App\Models\Rol; // Verifica si lo necesitas o se utiliza en el código
use Illuminate\Support\Facades\Auth;
use App\Services\EmpleadoService;
use Illuminate\Support\Facades\Gate;
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
    protected $empleadoService;
    /**
     * Constructor de la clase AsistenciaController.
     *
     * @param EmpleadoService $empleadoService
     */
    // Constructor de la clase AsistenciaController.
    public function __construct(EmpleadoService $empleadoService)
    {
        $this->empleadoService = $empleadoService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Gate::allows('empleados')) {
            abort(403, 'No tienes permiso para ver los empleados.');
        }

        // Recuperar empleados junto con la cantidad de ventas y ventas del mes actual
        $empleados = $this->obtenerEmpleadosConVentasYAsistencias();
        $roles = Role::all();
        $hoy = Carbon::today()->format('Y-m-d');
        $datos = $empleados->map(function ($empleado) use ($hoy) {
            return $this->procesarEmpleado($empleado, $hoy);
        });
        return view('employee.index', compact('datos', 'roles'));
    }

    protected function obtenerEmpleadosConVentasYAsistencias()
    {
        return Empleado::with([
            'ventas' => function ($query) {
                $query->select('idemp', 'fechaven', 'totalpagoven'); // Selecciona solo las columnas necesarias
            },
            'asistencias' => function ($query) {
                $query->select('empleado_id', 'created_at'); // Selecciona solo las columnas necesarias
            }
        ])
            ->withCount('ventas') // Total de ventas
            ->withCount([
                'ventas as ventas_mes_actual' => function ($query) {
                    $query->whereMonth('fechaven', Carbon::now()->month)
                        ->whereYear('fechaven', Carbon::now()->year);
                },
            ])
            ->withCount([
                'ventas as ventas_hoy' => function ($query) {
                    $query->whereDate('fechaven', Carbon::today());
                },
            ])
            ->withCount([
                'asistencias as asistencias_hoy' => function ($query) {
                    $query->whereDate('created_at', Carbon::today());
                },
            ])
            ->get();
    }
    protected function procesarEmpleado(Empleado $empleado, string $fecha)
    {
        $lapsos = $this->empleadoService->obtenerLapsosDeAsistenciasPorDia($empleado->idemp, $fecha);

        return [
            'empleado' => $empleado,
            'lapsos' => $lapsos['lapsos'],
            'total' => $lapsos['total_conexion'],
            'gestionClientesHoy' => $this->empleadoService->contarGestionClientesPorDia($empleado->idemp, $fecha),
            'gestionVentasHoy' => $this->empleadoService->contarVentasPorDia($empleado->idemp, $fecha),
            'gestionCuentasHoy' => $this->empleadoService->contarGestionCuentasPorDia($empleado->idemp, $fecha),
            'gestionInventarioHoy' => $this->empleadoService->contarGestionInventarioPorDia($empleado->idemp, $fecha),
            'gestionTareasHoy' => $this->empleadoService->contarGestionTareasPorDia($empleado->idemp, $fecha),
            'gestionRecargasHoy' => $this->empleadoService->contarGestionRecargasPorDia($empleado->idemp, $fecha),
            'gestionProductosHoy' => $this->empleadoService->contarGestionProductosPorDia($empleado->idemp, $fecha),
            'gestionCostosHoy' => $this->empleadoService->contarGestionCostosPorDia($empleado->idemp, $fecha),
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Gate::allows('empleados.store')) {
            abort(403, 'No tienes permiso para crear empleados.');
        }
        return view('employee.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('empleados.store')) {
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
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        // Triple verificación AJAX
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Empleado creado exitosamente',
                'empleado' => $empleado
            ]);
        }

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

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'empleado' => $empleado
            ]);
        }

        return view('employee.edit', compact('empleado'));
    }

    public function editRoles(string $id)
    {
        // Verifica si el usuario tiene permiso para actualizar roles de empleados
        if (!Gate::allows('empleados.update')) {
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
        if (!Gate::allows('empleados.update') && Auth::user()->idemp != $id) {
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        $empleado = Empleado::findOrFail($id);

        $rules = [
            'nombreemp' => 'required|string|max:50',
            'usuarioemp' => 'required|string|max:20|unique:empleados,usuarioemp,' . $id . ',idemp',
            'email' => 'nullable|email|max:50',
            'telefonoemp' => 'nullable|string|max:15',
            'foto_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $request->validate($rules);

        $empleado->nombreemp = $request->nombreemp;
        $empleado->usuarioemp = $request->usuarioemp;
        $empleado->email = $request->email;
        $empleado->telefonoemp = $request->telefonoemp;

        // Manejar subida de foto
        if ($request->hasFile('foto_url')) {
            // Eliminar foto anterior si existe
            if ($empleado->foto_url && \Storage::disk('public')->exists($empleado->foto_url)) {
                \Storage::disk('public')->delete($empleado->foto_url);
            }

            // Guardar nueva foto
            $path = $request->file('foto_url')->store('empleados', 'public');
            $empleado->foto_url = $path;
        }

        Historial::create([
            'accion' => 'Actualización de perfil',
            'descripcion' => 'El empleado ' . $empleado->nombreemp . ' actualizó su perfil',
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $empleado->save();

        return redirect()->back()->with('success', 'Perfil actualizado exitosamente');
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
        if (!Gate::allows('empleados.destroy')) {
            abort(403, 'No tienes permiso para eliminar empleados.');
        }
        $empleado = Empleado::findOrFail($id);
        Historial::create([
            'accion' => 'Eliminación de empleado',
            'descripcion' => 'Datos: ' . json_encode($empleado),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        $empleado->delete();

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Empleado eliminado exitosamente'
            ]);
        }

        return redirect()->route('empleados')->with('success', 'Empleado eliminado exitosamente');
    }

    /**
     * Update employee password
     */
    public function updatePassword(Request $request, $id)
    {
        // Solo el propio usuario o admin puede cambiar la contraseña
        if (Auth::user()->idemp != $id && Auth::user()->idemp != 1) {
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        $empleado = Empleado::findOrFail($id);

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        // Verificar contraseña actual
        if ($empleado->passwordemp !== $request->current_password) {
            return redirect()->back()->with('error', 'La contraseña actual es incorrecta.');
        }

        // Actualizar contraseña
        $empleado->passwordemp = $request->password;
        $empleado->save();

        Historial::create([
            'accion' => 'Cambio de contraseña',
            'descripcion' => 'El empleado ' . $empleado->nombreemp . ' cambió su contraseña',
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Contraseña actualizada exitosamente');
    }
}

