<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Tarea;
use Spatie\Permission\Models\Role;
use App\Models\Historial;
use Carbon\Carbon;
use App\Models\Rol; // Verifica si lo necesitas o se utiliza en el código
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\EmpleadoService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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
            ->withCount('tareasAsignadas as tareas_pendientes')
            ->withCount([
                'tareasCompletadas as tareas_completadas_hoy' => fn($q) => $q
                    ->where('completada', true)
                    ->whereDate('fecha_completada', Carbon::today()),
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
            'tareasPorTipo' => Tarea::where('assignee_id', $empleado->idemp)
                ->where('completada', false)
                ->select('tipo_tarea', DB::raw('COUNT(*) as total'))
                ->groupBy('tipo_tarea')
                ->pluck('total', 'tipo_tarea')
                ->toArray(),
        ];
    }

    public function rendimiento(Request $request)
    {
        if (!Gate::allows('empleados')) {
            abort(403);
        }

        $periodo = $request->get('periodo', 'semana');
        $desde = match($periodo) {
            'hoy'  => Carbon::today(),
            'mes'  => Carbon::now()->startOfMonth(),
            default => Carbon::now()->startOfWeek(),
        };
        $dias = match($periodo) {
            'hoy'  => 1,
            'mes'  => max(1, Carbon::today()->day),
            default => max(1, Carbon::now()->dayOfWeekIso),
        };

        $puntosPorTipo = [
            'cobrar_usuario'    => 1,
            'quitar_usuario'    => 9,
            'renovar_cuenta'    => 5,
            'cuenta_caida'      => 8,
            'colapso_cuenta'    => 5,
            'soporte_pendiente' => 9,
            'agregar_stock'     => 2,
            'manual'            => 1,
        ];

        $tiposConfig = [
            'cobrar_usuario'    => ['label' => 'Cobrar usuario',  'color' => '#f59e0b'],
            'quitar_usuario'    => ['label' => 'Quitar usuario',  'color' => '#ef4444'],
            'renovar_cuenta'    => ['label' => 'Renovar cuenta',  'color' => '#3b82f6'],
            'cuenta_caida'      => ['label' => 'Cuenta caída',    'color' => '#dc2626'],
            'colapso_cuenta'    => ['label' => 'Ajustar espacio', 'color' => '#8b5cf6'],
            'soporte_pendiente' => ['label' => 'Soporte',         'color' => '#06b6d4'],
            'agregar_stock'     => ['label' => 'Agregar stock',   'color' => '#10b981'],
            'manual'            => ['label' => 'Manual',          'color' => '#64748b'],
        ];

        $niveles = [
            ['min' => 0,   'label' => 'Sin actividad',  'bg' => '#94a3b8', 'text' => '#fff'],
            ['min' => 50,  'label' => 'Poco esfuerzo',  'bg' => '#fbbf24', 'text' => '#000'],
            ['min' => 150, 'label' => 'Medio tiempo',   'bg' => '#3b82f6', 'text' => '#fff'],
            ['min' => 300, 'label' => 'Trabajo normal', 'bg' => '#10b981', 'text' => '#fff'],
            ['min' => 500, 'label' => 'Buen trabajo',   'bg' => '#8b5cf6', 'text' => '#fff'],
            ['min' => 700, 'label' => 'Extra ⭐',       'bg' => '#f43f5e', 'text' => '#fff'],
        ];

        $empleados = Empleado::whereHas('roles')->with('roles')->get();

        $data = $empleados->map(function ($emp) use ($desde, $puntosPorTipo, $niveles, $dias) {
            $tareas = Tarea::where('completada_por', $emp->idemp)
                ->where('completada', true)
                ->where('fecha_completada', '>=', $desde)
                ->get(['id', 'nombretarea', 'tipo_tarea', 'fecha_completada', 'assigned_at']);

            $puntos = $tareas->sum(fn($t) => $puntosPorTipo[$t->tipo_tarea] ?? 0);
            $promDiario = $dias > 0 ? (int) round($puntos / $dias) : 0;
            $nivel = collect($niveles)->last(fn($n) => $promDiario >= $n['min']) ?? $niveles[0];

            $porTipo = [];
            foreach (array_keys($puntosPorTipo) as $tipo) {
                $porTipo[$tipo] = $tareas->where('tipo_tarea', $tipo)->count();
            }

            $sospechosas = $tareas->filter(function ($t) {
                if (!$t->assigned_at || !$t->fecha_completada) return false;
                return $t->fecha_completada->diffInSeconds($t->assigned_at) < 60;
            })->count();

            $ventas = DB::table('ventas')
                ->where('idemp', $emp->idemp)
                ->where('fechaven', '>=', $desde->toDateString())
                ->count();

            return [
                'id'           => $emp->idemp,
                'nombre'       => $emp->nombreemp,
                'foto'         => $emp->foto_url,
                'roles'        => $emp->roles->pluck('name')->toArray(),
                'puntos'       => $puntos,
                'prom_diario'  => $promDiario,
                'nivel'        => $nivel,
                'total_tareas' => $tareas->count(),
                'por_tipo'     => $porTipo,
                'ventas'       => $ventas,
                'sospechosas'  => $sospechosas,
            ];
        })->sortByDesc('puntos')->values();

        return view('employee.rendimiento', compact(
            'data', 'periodo', 'dias', 'desde', 'tiposConfig', 'niveles', 'puntosPorTipo'
        ));
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
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción.'
                ], 403);
            }
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        $empleado = Empleado::findOrFail($id);

        $rules = [
            'nombreemp' => 'required|string|max:50',
            'usuarioemp' => 'required|string|max:20|unique:empleados,usuarioemp,' . $id . ',idemp',
            'email' => 'nullable|email|max:50',
            'telefonoemp' => 'nullable|string|max:15',
            'foto_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'passwordemp' => 'nullable|string|min:4',
        ];

        $request->validate($rules);

        $empleado->nombreemp = $request->nombreemp;
        $empleado->usuarioemp = $request->usuarioemp;
        $empleado->email = $request->email;
        $empleado->telefonoemp = $request->telefonoemp;

        // Actualizar contraseña solo si se proporciona
        if ($request->filled('passwordemp')) {
            $empleado->passwordemp = $request->passwordemp;
        }

        // Manejar subida de foto
        if ($request->hasFile('foto_url')) {
            // Eliminar foto anterior si existe
            if ($empleado->foto_url && Storage::disk('public')->exists($empleado->foto_url)) {
                Storage::disk('public')->delete($empleado->foto_url);
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

        // Triple verificación AJAX
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'empleado' => $empleado
            ]);
        }

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
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción.'
                ], 403);
            }
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        $empleado = Empleado::findOrFail($id);

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        // Verificar contraseña actual
        if ($empleado->passwordemp !== $request->current_password) {
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta.'
                ], 422);
            }
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

        // Triple verificación AJAX
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente'
            ]);
        }

        return redirect()->back()->with('success', 'Contraseña actualizada exitosamente');
    }
}

