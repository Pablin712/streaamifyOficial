<?php

namespace App\Http\Controllers;

use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Proveedor;
use App\Models\Cuenta;
use App\Models\Historial;
use App\Models\Perfil;
use App\Models\ViewUsuarioActivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CuentaService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\ValorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
class ValorController extends Controller
{
    protected $cuentaService;
    protected $valorService;
    /**
     * ValorController constructor.
     *
     * @param CuentaService $cuentaService
     * @param ValorService $valorService
     */

    public function __construct(CuentaService $cuentaService, ValorService $valorService)
    {
        $this->cuentaService = $cuentaService;
        $this->valorService = $valorService;
    }
    public function index()
    {
        if (!Gate::allows('valores')) {
            abort(403, 'No tienes permiso para ver los valores.');
        }
        $valores = Valor::with(['proveedor', 'servicio'])->where('activoval', true)->get();
        $serviciosPrincipales = $this->valorService->obtenerServiciosPrincipales(Servicio::all());

        // Variables necesarias para los modales
        $servicios = Servicio::all();
        $proveedores = Proveedor::where('activopro', true)->get();

        return view('inventory.valores.index', compact('valores', 'serviciosPrincipales', 'servicios', 'proveedores'));
    }

    /**
     * Ya no existe una vista `create` para valores: se crean desde un modal
     * dentro del listado. Esta ruta devolvia "View not found" (error 500), asi
     * que ahora redirige al listado con el modal ya abierto. Se conserva para
     * que enlaces antiguos y marcadores sigan funcionando.
     */
    public function create()
    {
        if (!Gate::allows('valores.store')) {
            abort(403, 'No tienes permiso para crear valores.');
        }

        return redirect()->route('valores', ['modal' => 'createValorModal']);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('valores.store')) {
            abort(403, 'No tienes permiso para crear valores.');
        }

        $request->validate([
            'idser' => 'required|exists:servicios,idser',
            'idpro' => 'required|exists:proveedores,idpro',
            'costoval' => 'required|numeric|min:0|max:999.99',
            'tipoval' => 'required|in:completo,individual,hibrido',
            'pantminval' => 'required|integer|min:1',
            'pantmaxval' => 'required|integer|min:1',
            'mesesval' => 'required|integer|min:1',
            'bot' => 'nullable|url|max:255',
        ]);

        $request->merge([
            'idval' => strtoupper($request->idval)
        ]);

        $valor = Valor::create($request->all());

        Historial::create([
            'accion' => 'Creación de Valor',
            'descripcion' => 'Datos: ' . json_encode($valor),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Valor creado con éxito.',
                'valor' => $valor
            ]);
        }

        return redirect()->route('valores')->with('success', 'Valor creado con éxito.');
    }

    public function pdf()
    {
        $valores = Valor::with('proveedor')->where('activoval', true)->get();
        $mejoresValores = $this->valorService->obtenerTodosTresMejoresValoresCompletosPrincipales(1);
        $fecha = Carbon::now()->format('Y-m-d');
        $pdf = Pdf::loadView('inventory.valores.pdf', compact('valores', 'fecha', 'mejoresValores'))
            ->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif']);
        $nombreArchivo = "TopValores-{$fecha}.pdf";
        return $pdf->download($nombreArchivo);
    }

    public function edit($idval)
    {
        if (!Gate::allows('valores.update')) {
            abort(403, 'No tienes permiso para editar valores.');
        }
        $valor = Valor::with(['proveedor', 'servicio'])->findOrFail($idval);

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'valor' => $valor
            ]);
        }

        $proveedores = Proveedor::where('activopro', true)->get();
        $servicios = Servicio::all();
        return view('inventory.valores.edit', compact('valor', 'proveedores', 'servicios'));
    }

    public function update(Request $request, $idval)
    {
        if (!Gate::allows('valores.update')) {
            abort(403, 'No tienes permiso para actualizar valores.');
        }

        $request->validate([
            'idser' => 'required|exists:servicios,idser',
            'idpro' => 'required|exists:proveedores,idpro',
            'costoval' => 'required|numeric|min:0|max:999.99',
            'tipoval' => 'required|in:completo,individual,hibrido',
            'pantminval' => 'required|integer|min:1',
            'pantmaxval' => 'required|integer|min:1',
            'mesesval' => 'required|integer|min:1',
            'bot' => 'nullable|url|max:255',
        ]);

        $valor = Valor::findOrFail($idval);

        Historial::create([
            'accion' => 'Actualización de Valor',
            'descripcion' => 'Datos antiguos: ' . json_encode($valor),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $valor->update($request->all());

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Valor actualizado con éxito.',
                'valor' => $valor
            ]);
        }

        return redirect()->route('valores')->with('success', 'Valor actualizado con éxito.');
    }

    public function updatePantallas(Request $request)
    {
        if (!Gate::allows('valores.update')) {
            abort(403, 'No tienes permiso para actualizar valores.');
        }
        $datos = $request->input('pantallas');
        $request->validate([
            'pantallas' => 'required|array',
            'pantallas.*.pantmin' => 'required|integer|min:1',
            'pantallas.*.pantmax' => 'required|integer|min:1',
        ]);

        foreach ($datos as $servicioId => $valores) {
            Valor::where('idser', $servicioId)
                ->where('tipoval', 'completo')
                ->update([
                    'pantminval' => $valores['pantmin'],
                    'pantmaxval' => $valores['pantmax'],
                ]);
        }

        return redirect()->back()->with('success', 'Valores actualizados correctamente.');
    }

    public function destroy($idval)
    {
        try {
            // Verificar permisos
            if (!Gate::allows('valores.destroy')) {
                abort(403, 'No tienes permiso para eliminar valores.');
            }

            // Buscar el valor
            $valor = Valor::findOrFail($idval);

            $cuentas = Cuenta::with(['valor.servicio', 'perfiles'])
                ->where('idval', $valor->idval)
                ->get();

            $cuentasConUsuariosActivos = $cuentas->isNotEmpty()
                ? ViewUsuarioActivo::whereIn('idcue', $cuentas->pluck('idcue'))->exists()
                : false;

            if ($cuentasConUsuariosActivos) {
                // Triple verificación AJAX
                if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar porque tiene cuentas con usuarios activos.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'No se puede eliminar porque tiene cuentas con usuarios activos.');
            }

            if (!$this->historicoDesacopladoDisponible()) {
                $message = 'No se puede eliminar el valor todavía. Falta ejecutar la migración de desacople histórico para preservar ventas, costos y mantenimientos.';

                if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 409);
                }

                return redirect()->route('valores')->with('error', $message);
            }

            // Registrar en historial
            Historial::create([
                'accion' => 'Se eliminó el valor con ID: ' . $valor->idval,
                'descripcion' => 'Valor eliminado físicamente preservando histórico: ' . json_encode($valor),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            DB::transaction(function () use ($cuentas, $valor) {
                foreach ($cuentas as $cuenta) {
                    $this->preservarHistoricoCuenta($cuenta);
                }

                $valor->delete();
            });

            $this->cuentaService->actualizarEstadoProductos($valor->idser);

            // Triple verificación AJAX
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Valor eliminado con éxito.'
                ]);
            }

            return redirect()->route('valores')->with('success', 'Valor eliminado con éxito.');
        } catch (\Exception $e) {
            return redirect()->route('valores')->with('error', 'Error al eliminar el valor: ' . $e->getMessage());
        }
    }

    private function preservarHistoricoCuenta(Cuenta $cuenta): void
    {
        $cuenta->loadMissing(['valor.servicio', 'perfiles']);

        $servicioNombre = $cuenta->valor?->servicio?->nombreser ?? $cuenta->valor?->idser;

        foreach ($cuenta->perfiles as $perfil) {
            DB::table('detalles_venta')
                ->where('idper', $perfil->idper)
                ->update([
                    'idper_snapshot' => DB::raw("COALESCE(idper_snapshot, '" . addslashes($perfil->idper) . "')"),
                    'idcue_snapshot' => DB::raw("COALESCE(idcue_snapshot, '" . addslashes($cuenta->idcue) . "')"),
                    'idval_snapshot' => DB::raw("COALESCE(idval_snapshot, '" . addslashes((string) $cuenta->idval) . "')"),
                    'servicio_snapshot' => DB::raw("COALESCE(servicio_snapshot, '" . addslashes((string) $servicioNombre) . "')"),
                    'cuenta_usuario_snapshot' => DB::raw("COALESCE(cuenta_usuario_snapshot, '" . addslashes((string) $cuenta->usuariocue) . "')"),
                    'perfil_numeroper_snapshot' => DB::raw('COALESCE(perfil_numeroper_snapshot, ' . (int) $perfil->numeroper . ')'),
                ]);
        }

        DB::table('costos')
            ->where('idcue', $cuenta->idcue)
            ->update([
                'idcue_snapshot' => DB::raw("COALESCE(idcue_snapshot, '" . addslashes($cuenta->idcue) . "')"),
                'idval_snapshot' => DB::raw("COALESCE(idval_snapshot, '" . addslashes((string) $cuenta->idval) . "')"),
                'servicio_snapshot' => DB::raw("COALESCE(servicio_snapshot, '" . addslashes((string) $servicioNombre) . "')"),
                'cuenta_usuario_snapshot' => DB::raw("COALESCE(cuenta_usuario_snapshot, '" . addslashes((string) $cuenta->usuariocue) . "')"),
            ]);

        DB::table('mantenimientos')
            ->where('idcue', $cuenta->idcue)
            ->update([
                'idcue_snapshot' => DB::raw("COALESCE(idcue_snapshot, '" . addslashes($cuenta->idcue) . "')"),
                'idval_snapshot' => DB::raw("COALESCE(idval_snapshot, '" . addslashes((string) $cuenta->idval) . "')"),
                'servicio_snapshot' => DB::raw("COALESCE(servicio_snapshot, '" . addslashes((string) $servicioNombre) . "')"),
                'cuenta_usuario_snapshot' => DB::raw("COALESCE(cuenta_usuario_snapshot, '" . addslashes((string) $cuenta->usuariocue) . "')"),
            ]);
    }

    private function historicoDesacopladoDisponible(): bool
    {
        return Schema::hasColumn('detalles_venta', 'idper_snapshot')
            && Schema::hasColumn('detalles_venta', 'idcue_snapshot')
            && Schema::hasColumn('detalles_venta', 'idval_snapshot')
            && Schema::hasColumn('detalles_venta', 'servicio_snapshot')
            && Schema::hasColumn('detalles_venta', 'cuenta_usuario_snapshot')
            && Schema::hasColumn('detalles_venta', 'perfil_numeroper_snapshot')
            && Schema::hasColumn('costos', 'idcue_snapshot')
            && Schema::hasColumn('costos', 'idval_snapshot')
            && Schema::hasColumn('costos', 'servicio_snapshot')
            && Schema::hasColumn('costos', 'cuenta_usuario_snapshot')
            && Schema::hasColumn('mantenimientos', 'idcue_snapshot')
            && Schema::hasColumn('mantenimientos', 'idval_snapshot')
            && Schema::hasColumn('mantenimientos', 'servicio_snapshot')
            && Schema::hasColumn('mantenimientos', 'cuenta_usuario_snapshot');
    }
    public function corregir()
    {
        if (!Gate::allows('valores.update')) {
            abort(403, 'No tienes permiso para actualizar valores.');
        }
        $this->valorService->corregirTodosIDValor();
        return redirect()->route('valores')->with('success', 'IDs de valores corregidos con éxito.');
    }
    public function deletegroup()
    {
        if (!Gate::allows('valores.destroy')) {
            abort(403, 'No tienes permiso para eliminar valores.');
        }

        if (!$this->historicoDesacopladoDisponible()) {
            return redirect()->route('valores')->with('error', 'No se pueden eliminar los valores todavía. Falta ejecutar la migración de desacople histórico para preservar ventas, costos y mantenimientos.');
        }

        $valores = Valor::where('activoval', true)->get();
        $eliminados = 0;
        $omitidos = 0;
        $serviciosActualizados = [];

        foreach ($valores as $valor) {
            $cuentas = Cuenta::with(['valor.servicio', 'perfiles'])
                ->where('idval', $valor->idval)
                ->get();

            $cuentasConUsuariosActivos = $cuentas->isNotEmpty()
                ? ViewUsuarioActivo::whereIn('idcue', $cuentas->pluck('idcue'))->exists()
                : false;

            if ($cuentasConUsuariosActivos) {
                $omitidos++;
                continue;
            }

            Historial::create([
                'accion' => 'Se eliminó el valor con ID: ' . $valor->idval,
                'descripcion' => 'Valor eliminado físicamente desde borrado masivo preservando histórico: ' . json_encode($valor),
                'empleado_id' => Auth::user()->idemp ?? null,
                'created_at' => now(),
            ]);

            DB::transaction(function () use ($cuentas, $valor) {
                foreach ($cuentas as $cuenta) {
                    $this->preservarHistoricoCuenta($cuenta);
                }

                $valor->delete();
            });

            $eliminados++;
            if ($valor->idser) {
                $serviciosActualizados[$valor->idser] = true;
            }
        }

        foreach (array_keys($serviciosActualizados) as $idser) {
            $this->cuentaService->actualizarEstadoProductos($idser);
        }

        if ($eliminados === 0) {
            return redirect()->route('valores')->with('warning', 'No se eliminó ningún valor. ' . $omitidos . ' valores fueron omitidos porque aún tienen cuentas con usuarios activos.');
        }

        return redirect()->route('valores')->with('success', "Borrado masivo completado: {$eliminados} valores eliminados y {$omitidos} omitidos por usuarios activos.");
    }
}
