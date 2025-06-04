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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

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

        $espacios_por_servicio = $this->cuentaService->calcularEspaciosPorServicio();

        return view('inventory.cuentas.index', compact(
            'cuentas',
            'cuentasDisponibles',
            'cuentasColapsadas',
            'cuentasSinOcupar',
            'cuentasPorVencer',
            'cuentasCaidas',
            'espacios_por_servicio'
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
                'usuariocue' => 'required|string|max:50|unique:cuentas,idcue',
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
            return redirect()->route('cuentas')->with('success', 'Cuenta creada con éxito.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()]);
        }
    }

    public function pdf()
    {
        $cuentas = Cuenta::with('valor.proveedor', 'valor.servicio')->get();
        
        $cuentasRenovar = $cuentas->filter(function($c) {
            return $c->is_conveniente_renovar && $c->costo_mes > 0;
        })->sortBy([
            fn($a) => $a->valor->proveedor->nombrepro ?? 'Desconocido',
            fn($a) => $a->valor->servicio->nombreser ?? 'Servicio'
        ]);

        $agrupadas = $cuentasRenovar->groupBy(fn($c) => $c->valor->proveedor->nombrepro ?? 'Sin proveedor');
    
        $fecha = now()->toDateString();
        $pdf = Pdf::loadView('inventory.cuentas.pdf', compact('cuentas','agrupadas', 'fecha'))
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
        $cuenta = Cuenta::findOrFail($idcue);
        $cuenta->caidacue = !$cuenta->caidacue;
        $cuenta->save();

        Historial::create([
            'accion' => 'Se actualizó el estado de cuenta con ID: ' . $cuenta->idcue,
            'descripcion' => 'Estado cambiado a ' . $cuenta->caidacue,
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);
        return redirect()->route('cuentas')->with('success', 'Estado de la cuenta actualizado correctamente.');
    }

    public function moverClientes(Request $request)
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
        if ($respuesta == 'null') {
            return redirect()->back()->with('error', 'No se pudieron mover los clientes a otro espacio.');
        } elseif ($respuesta == 'incompleto') {
            return redirect()->back()->with('error', 'Ya no quedan espacios, se movieron los que alcanzaron.');
        } else {
            return redirect()->back()->with('success', 'Clientes movidos a otros espacios correctamente.');
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
        $cuenta = Cuenta::with(['valor'])->findOrFail($idcue);
        $valores = Valor::where('activoval', true)->get();
        return view('inventory.cuentas.edit', compact('cuenta', 'valores'));
    }

    public function renew($idcue)
    {
        if (!Gate::allows('cuentas.renew')) {
            abort(403, 'No tienes permiso para renovar cuentas.');
        }
        $cuenta = Cuenta::with(['valor'])->findOrFail($idcue);
        $valor = $cuenta->idval;
        return view('inventory.cuentas.renew', compact('cuenta', 'valor'));
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
            return redirect()->route('cuentas')->with('success', 'Cuenta actualizada con éxito.');
        } catch (\Exception $e) {
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
        return redirect()->route('cuentas')->with('success', 'Cuenta desactivada con éxito.');
    }
}
