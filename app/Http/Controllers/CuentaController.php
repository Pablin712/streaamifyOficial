<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Perfil;
use App\Models\Costo;
use App\Models\ViewUsuarioActivo;
use App\Models\Producto;
use App\Models\Historial;
use App\Services\CuentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
class CuentaController extends Controller
{
    protected $cuentaService;

    public function __construct(CuentaService $cuentaService)
    {
        $this->cuentaService = $cuentaService;
    }
    public function index(Request $request)
    {
        if (!Gate::allows('cuentas')) {
            abort(403, 'No tienes permiso para ver las cuentas.');
        }
        $cuentas = $this->cuentaService->obtenerCuentasSegunPermiso($empleado = Auth::user());
        //$this->cuentaService->asignarUsuarios($cuentas);
        // Filtrar las cuentas en diferentes categorías
        $cuentasColapsadas = $this->cuentaService->obtenerCuentasColapsadas($cuentas);
        $cuentasDisponibles = $this->cuentaService->obtenerCuentasDisponibles($cuentas);
        $cuentasSinOcupar = $this->cuentaService->obtenerCuentasSinOcupar($cuentas);
        $cuentasPorVencer = $this->cuentaService->obtenerCuentasPorVencer($cuentas);
        $cuentasCaidas = $this->cuentaService->obtenerCuentasCaidas($cuentas);
        $mesa = $this->cuentaService->obtenerMesasDeTrabajo();

        $espacios_por_servicio = $this->cuentaService->calcularEspaciosPorServicio();

        // Obtener valores activos para los modales
        $valores = Valor::where('activoval', true)->get();

        // Obtener servicios y proveedores para el modal de crear valor
        $servicios = \App\Models\Servicio::all();
        $proveedores = \App\Models\Proveedor::all();

        return view('inventory.cuentas.index', compact(
            'cuentas',
            'cuentasDisponibles',
            'cuentasColapsadas',
            'cuentasSinOcupar',
            'cuentasPorVencer',
            'cuentasCaidas',
            'mesa',
            'espacios_por_servicio',
            'valores',
            'servicios',
            'proveedores'
        ));
    }

    public function create()
    {
        if (!Gate::allows('cuentas.store')) {
            abort(403, 'No tienes permiso para crear cuentas.');
        }
        $valores = Valor::where('activoval', true)->get();
        return view('inventory.cuentas.create', compact('valores'));
    }
    public function show($idcue)
    {
        if (!Gate::allows('cuentas.mensaje')) {
            abort(403, 'No tienes permiso para ver una cuenta con sus perfiles.');
        }
        $cuenta = Cuenta::with(['valor', 'costos'])->findOrFail($idcue);
        $perfiles = $this->cuentaService->calcularUsuariosPorPerfil($cuenta);
        return view('inventory.cuentas.show', compact('cuenta', 'perfiles'));
    }

    public function PerfilesSpotify()
    {
        if (!Gate::allows('todas_las_cuentas') || !Gate::allows('spotify')) {
            abort(403, 'No tienes permiso para ver las cuentas de spotify.');
        }
        $cuentas = $this->cuentaService->obtenerCuentasPorServicioSinFiltros("SPOTIFY");
        return view('inventory.cuentas.spotify', compact('cuentas'));
    }

    public function store(Request $request)
    {
        if (!Gate::allows('cuentas.store')) {
            abort(403, 'No tienes permiso para crear cuentas.');
        }
        try {
            $request->merge([
                'idcue' => strtoupper($request->idcue)
            ]);
            $validated = $request->validate([
                'idcue' => 'required|string|max:20|unique:cuentas,idcue',
                'idval' => 'required|exists:valores,idval',
                'fechavencue' => 'required|date',
                'usuariocue' => 'required|string|max:50|unique:cuentas,usuariocue',
                'contrasenacue' => 'required|string|max:50',
                'caidacue' => 'required|boolean',
            ]);

            $cuenta = Cuenta::create($validated);

            Historial::create([
                'accion' => 'Se creó la cuenta con ID: ' . $cuenta->idcue,
                'descripcion' => 'Datos: ' . json_encode($cuenta),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            // Actualizar estado de productos relacionados con el servicio de la cuenta creada
            $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);

            if ($request->filled('descripcioncos') || $request->filled('montocos')) {
                $validatedCosto = $request->validate([
                    'descripcioncos' => 'required|string|max:50',
                    'montocos' => 'required|numeric|min:0',
                ]);

                $costo = Costo::create([
                    'idcue' => $request->idcue,
                    'fechacos' => now(),
                    'montocos' => $validatedCosto['montocos'],
                    'descripcioncos' => $validatedCosto['descripcioncos'],
                ]);

                Historial::create([
                    'accion' => 'Se creó el costo con ID: ' . $costo->idcos,
                    'descripcion' => 'Datos: ' . json_encode($costo),
                    'empleado_id' => Auth::user()->idemp,
                    'created_at' => now(),
                ]);
            }

            // Triple verificación AJAX
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Cuenta creada con éxito.',
                    'cuenta' => $cuenta
                ]);
            }

            return redirect()->route('cuentas')->with('success', 'Cuenta creada con éxito.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()]);
        }
    }

    public function pdf()
    {
        $cuentas = Cuenta::with('valor.proveedor', 'valor.servicio')->where('activocue', true)->get();

        $cuentasRenovar = $cuentas->filter(function ($c) {
            return $c->is_conveniente_renovar && $c->costo_mes > 0;
        })->sortBy([
            fn($a) => $a->valor->proveedor->nombrepro ?? 'Desconocido',
            fn($a) => $a->valor->servicio->nombreser ?? 'Servicio'
        ]);

        $agrupadas = $cuentasRenovar->groupBy(fn($c) => $c->valor->proveedor->nombrepro ?? 'Sin proveedor');

        $fecha = now()->toDateString();
        $pdf = Pdf::loadView('inventory.cuentas.pdf', compact('cuentas', 'agrupadas', 'fecha'))
            ->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif']);
        $nombreArchivo = "Inventario-Cuentas-{$fecha}.pdf";
        return $pdf->download($nombreArchivo);
    }

    public function status($idcue)
    {
        if (!Gate::allows('cuentas.status')) {
            abort(403, 'No tienes permiso para actualizar el estado de la cuenta.');
        }
        $cuenta = Cuenta::with('valor')->findOrFail($idcue);
        $cuenta->caidacue = !$cuenta->caidacue;
        $cuenta->save();

        Historial::create([
            'accion' => 'Se actualizó el estado de cuenta con ID: ' . $cuenta->idcue,
            'descripcion' => 'Estado cambiado a ' . ($cuenta->caidacue ? 'Dañada' : 'Activa'),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);

        // Triple verificación AJAX - TIEMPO REAL
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            // Calcular nuevo estado para badge
            $fechaVencimiento = Carbon::parse($cuenta->fechavencue);
            $diasRestantes = Carbon::today()->diffInDays($fechaVencimiento, false);

            if ($cuenta->caidacue) {
                $statusClass = 'dark';
                $statusText = 'Dañada';
            } elseif ($diasRestantes <= 0) {
                $statusClass = 'danger';
                $statusText = 'Vencida';
            } elseif ($diasRestantes <= 5) {
                $statusClass = 'warning';
                $statusText = 'Ya vence';
            } else {
                $statusClass = 'success';
                $statusText = 'Activa';
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado de la cuenta actualizado correctamente.',
                'cuenta' => $cuenta,
                'statusClass' => $statusClass,
                'statusText' => $statusText
            ]);
        }

        return redirect()->route('cuentas')->with('success', 'Estado de la cuenta actualizado correctamente.');
    }

    public function moverClientes(Request $request) //mesa de trabajo
    {
        if (!Gate::allows('usuarios.change')) {
            abort(403, 'No tienes permiso para cambiar usuarios de la cuenta.');
        }
        $cuentaOrigen = Cuenta::find($request->input('cuenta_origen'));
        $cuentaDestino = $this->cuentaService->obtenerCuentaDestino($cuentaOrigen);

        // Actualiza todos los clientes/perfiles de la cuenta origen a la cuenta destino
        $respuesta = $this->cuentaService->moverClientesDeCuenta($cuentaOrigen, $cuentaDestino);
        if (!$respuesta) {
            return redirect()->back()->with('error', 'No se pudieron mover los clientes a la mesa de trabajo.');
        }
        Historial::create([
            'accion' => 'Mover-Usuarios-Mesa',
            'descripcion' => 'Se movieron clientes de la cuenta con ID: ' . $cuentaOrigen->idcue . ' a la cuenta con ID: ' . $cuentaDestino->idcue,
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Clientes movidos a la mesa de trabajo correctamente.');
    }

    public function moverClientesDisperso(Request $request)
    {
        if (!Gate::allows('usuarios.change')) {
            abort(403, 'No tienes permiso para cambiar usuarios de la cuenta.');
        }
        $cuentaOrigen = Cuenta::find($request->input('cuenta_origen'));

        $respuesta = $this->cuentaService->mudarClientesAOtraCuenta($cuentaOrigen);
        if ($respuesta === 'null') {
            return redirect()->back()->with('error', 'No se pudieron mover los clientes a otro espacio.');
        } elseif (Str::startsWith($respuesta, 'incompleto')) {
            return redirect()->back()->with('warning', $respuesta);
        } elseif (Str::startsWith($respuesta, 'success')) {
            return redirect()->back()->with('success', $respuesta);
        } else {
            return redirect()->back()->with('info', 'Resultado desconocido: ' . $respuesta);
        }
    }

    public function mensaje($perfilId)
    {
        if (!Gate::allows('cuentas.mensaje')) {
            abort(403, 'No tienes permiso para solicitar datos de perfil.');
        }
        $perfil = Perfil::find($perfilId);
        $cuenta = Cuenta::where('idcue', $perfil->idcue)->first();
        $valor = Valor::find($cuenta->idval);
        $servicio = Servicio::find($valor->idser);
        $bot = $valor->botval;
        dd($bot);

        $mensaje = "*{$servicio->nombre}*\n";
        $mensaje .= "Usuario: {$cuenta->usuariocue}\n";
        $mensaje .= "Clave: {$cuenta->contrasenacue}\n";
        $mensaje .= "PIN de perfil {$perfil->numeroper}: {$perfil->pinper}\n";

        // Agregar mensaje adicional si tiene un bot de códigos
        if (!empty($bot)) {
            $mensaje .= "\n\n*Nota importante:*\n";
            $mensaje .= "Esta cuenta incluye un bot de códigos. Si en algún momento se te solicita un código de acceso, puedes obtenerlo ingresando al siguiente enlace:\n";
            $mensaje .= "{$bot}\n";
            $mensaje .= "Por favor, sigue las instrucciones del bot para obtener el código de acceso. ¡Gracias por tu confianza!";
        }
        Historial::create([
            'accion' => 'Se solicitó los datos de perfil ' . $perfil->numeroper . ' de la cuenta: ' . $cuenta->idcue,
            'descripcion' => null,
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        return response()->json(['mensaje' => $mensaje]);
    }

    public function edit($idcue)
    {
        if (!Gate::allows('cuentas.update')) {
            abort(403, 'No tienes permiso para editar cuentas.');
        }
        $cuenta = Cuenta::with(['valor.proveedor'])->findOrFail($idcue);
        $valores = Valor::where('activoval', true)->get();

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'cuenta' => [
                    'idcue' => $cuenta->idcue,
                    'idval' => $cuenta->idval,
                    'usuariocue' => $cuenta->usuariocue,
                    'contrasenacue' => $cuenta->contrasenacue,
                    'fechavencue' => $cuenta->fechavencue,
                    'caidacue' => $cuenta->caidacue,
                    'usuarios_activos' => $cuenta->usuarios_activos,
                    'valor' => [
                        'idser' => $cuenta->valor->idser ?? null,
                        'proveedor' => [
                            'nombrepro' => $cuenta->valor->proveedor->nombrepro ?? null,
                        ]
                    ]
                ]
            ]);
        }

        return view('inventory.cuentas.edit', compact('cuenta', 'valores'));
    }

    public function renew($idcue)
    {
        if (!Gate::allows('cuentas.renew')) {
            abort(403, 'No tienes permiso para renovar cuentas.');
        }
        $cuenta = Cuenta::with(['valor.proveedor'])->findOrFail($idcue);
        $valor = $cuenta->idval;

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'cuenta' => [
                    'idcue' => $cuenta->idcue,
                    'idval' => $cuenta->idval,
                    'usuariocue' => $cuenta->usuariocue,
                    'contrasenacue' => $cuenta->contrasenacue,
                    'fechavencue' => $cuenta->fechavencue,
                    'caidacue' => $cuenta->caidacue,
                    'valor' => [
                        'idser' => $cuenta->valor->idser ?? null,
                        'proveedor' => [
                            'nombrepro' => $cuenta->valor->proveedor->nombrepro ?? null,
                        ]
                    ]
                ]
            ]);
        }

        return view('inventory.cuentas.renew', compact('cuenta', 'valor'));
    }

    public function saveRenew(Request $request, $idcue)
    {
        if (!Gate::allows('cuentas.renew')) {
            abort(403, 'No tienes permiso para renovar cuentas.');
        }

        try {
            $request->validate([
                'nuevafechavencue' => 'required|date|after:today',
                'montocos' => 'required|numeric|min:0',
                'descripcioncos' => 'required|string|max:50'
            ]);

            $cuenta = Cuenta::findOrFail($idcue);

            // Crear historial
            Historial::create([
                'accion' => 'Renovación de Cuenta',
                'descripcion' => 'Fecha antigua: ' . $cuenta->fechavencue . ' | Nueva fecha: ' . $request->nuevafechavencue,
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            // Actualizar fecha de vencimiento
            $cuenta->update([
                'fechavencue' => $request->nuevafechavencue
            ]);

            // Crear registro de costo
            Costo::create([
                'idcue' => $cuenta->idcue,
                'descripcioncos' => $request->descripcioncos,
                'montocos' => $request->montocos,
                'fechacos' => now(),
            ]);

            // Actualizar estado de productos
            $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);

            // Triple verificación AJAX
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Cuenta renovada exitosamente',
                    'cuenta' => $cuenta->fresh()->load('valor')
                ]);
            }

            return redirect()->route('cuentas')->with('success', 'Cuenta renovada con éxito.');
        } catch (\Exception $e) {
            // Triple verificación AJAX para errores
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al renovar la cuenta: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Hubo un problema al renovar la cuenta: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $idcue)
    {
        if (!Gate::allows('cuentas.update')) {
            abort(403, 'No tienes permiso para actualizar cuentas.');
        }
        try {
            $request->validate([
                'idval' => 'required|exists:valores,idval',
                'fechavencue' => 'required|date',
                'usuariocue' => 'required|string|max:50',
                'contrasenacue' => 'required|string|max:50',
                'caidacue' => 'required|boolean|min:1'
            ]);
            $request->merge([
                'idcue' => strtoupper($request->idcue)
            ]);
            $cuenta = Cuenta::findOrFail($idcue);

            Historial::create([
                'accion' => 'Actualización de Cuenta',
                'descripcion' => 'Datos antiguos: ' . json_encode($cuenta),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            $cuenta->update($request->all());
            $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);
            if (!empty($request->descripcioncos) && !empty($request->montocos)) {
                $validatedCosto = $request->validate([
                    'descripcioncos' => 'string|max:50',
                    'montocos' => 'numeric|min:0',
                ]);
                Costo::create([
                    'idcue' => $cuenta->idcue,
                    'descripcioncos' => $request->descripcioncos,
                    'montocos' => $request->montocos,
                    'fechacos' => now(),
                ]);
            }

            // Triple verificación AJAX
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Cuenta actualizada exitosamente',
                    'cuenta' => $cuenta->fresh()->load('valor')
                ]);
            }

            return redirect()->route('cuentas')->with('success', 'Cuenta actualizada con éxito.');
        } catch (\Exception $e) {
            // Triple verificación AJAX para errores
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la cuenta: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()]);
        }
    }

    public function destroy($idcue)
    {
        if (!Gate::allows('cuentas.destroy')) {
            abort(403, 'No tienes permiso para eliminar cuentas.');
        }
        $cuenta = Cuenta::findOrFail($idcue);
        $cuentaInUsuariosActivos = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->exists();

        if ($cuentaInUsuariosActivos) {
            // Triple verificación AJAX para error
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la cuenta porque uno o más clientes aun la usan'
                ], 422);
            }
            return redirect()->route('cuentas')->with('error', 'No se puede eliminar la cuenta porque uno o más clientes aun la usan');
        }

        Historial::create([
            'accion' => 'Se desactivó la cuenta con ID: ' . $cuenta->idcue,
            'descripcion' => 'Datos inactivos: ' . json_encode($cuenta),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $nuevoId = $this->cuentaService->generarNuevoId($cuenta->idcue);
        $perfiles = Perfil::where('idcue', $cuenta->idcue)->get();

        foreach ($perfiles as $perfil) {
            $nuevoIdPer = $this->cuentaService->generarNuevoIdPerfil($perfil->idper);
            $perfil->update([
                'idper' => $nuevoIdPer
            ]);
        }

        $cuenta->update([
            'activocue' => false,
            'idcue' => $nuevoId
        ]);
        $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Cuenta desactivada exitosamente'
            ]);
        }

        return redirect()->route('cuentas')->with('success', 'Cuenta desactivada con éxito.');
    }
}
