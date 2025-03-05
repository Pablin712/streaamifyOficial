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

class CuentaController extends Controller
{
    /*
    // Constructor original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:cuentas')->only('index');
        $this->middleware('can:cuentas.store')->only('create', 'store');
        $this->middleware('can:cuentas.update')->only('edit', 'update');
        $this->middleware('can:cuentas.status')->only('status');
        $this->middleware('can:cuentas.renew')->only('renew');
        $this->middleware('can:cuentas.mensaje')->only('mensaje');
        $this->middleware('can:cuentas.destroy')->only('destroy');
    }
    */
    protected $cuentaService;

    public function __construct(CuentaService $cuentaService)
    {
        $this->cuentaService = $cuentaService;
    }
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('cuentas')) {
            abort(403, 'No tienes permiso para ver las cuentas.');
        }
        $cuentas = Cuenta::with(['valor'])
            ->where('activocue', true)
            ->orderBy('fechavencue')
            ->get();
        $perfiles = collect();
        $idcueSeleccionado = $request->idcue;

        if ($idcueSeleccionado) {
            $perfiles = Perfil::where('idcue', $idcueSeleccionado)->get();
            foreach ($perfiles as $perfil) {
                $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                    ->where('idcue', $idcueSeleccionado)
                    ->count();
                $perfil->usuarios_activos = $usuariosActivos;
            }
        }
        $this->cuentaService->asignarUsuarios($cuentas);

        $espacios_por_servicio = $this->cuentaService->calcularEspaciosPorServicio();

        return view('inventory.cuentas.index', compact('cuentas', 'perfiles', 'idcueSeleccionado', 'espacios_por_servicio'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermissionTo('cuentas.store')) {
            abort(403, 'No tienes permiso para crear cuentas.');
        }
        $valores = Valor::all();
        return view('inventory.cuentas.create', compact('valores'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('cuentas.store')) {
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
                'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(),
                'fecha' => now(),
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
                    'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(),
                    'fecha' => now(),
                ]);
            }
            return redirect()->route('cuentas')->with('success', 'Cuenta creada con éxito.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()]);
        }
    }

    public function status($idcue)
    {
        if (!Auth::user()->hasPermissionTo('cuentas.status')) {
            abort(403, 'No tienes permiso para actualizar el estado de la cuenta.');
        }
        $cuenta = Cuenta::findOrFail($idcue);
        $cuenta->caidacue = !$cuenta->caidacue;
        $cuenta->save();

        Historial::create([
            'accion' => 'Se actualizó el estado de cuenta con ID: ' . $cuenta->idcue,
            'descripcion' => 'Estado cambiado a ' . $cuenta->caidacue,
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);
        return redirect()->route('cuentas')->with('success', 'Estado de la cuenta actualizado correctamente.');
    }

    public function mensaje($perfilId)
    {
        if (!Auth::user()->hasPermissionTo('cuentas.mensaje')) {
            abort(403, 'No tienes permiso para solicitar datos de perfil.');
        }
        $perfil = Perfil::find($perfilId);
        $cuenta = Cuenta::where('idcue', $perfil->idcue)->first();
        $valor = Valor::find($cuenta->idval);
        $servicio = Servicio::find($valor->idser);

        $mensaje = "<strong>{$servicio->nombre}</strong>\n";
        $mensaje .= "Usuario: {$cuenta->usuariocue}\n";
        $mensaje .= "Clave: {$cuenta->contrasenacue}\n";
        $mensaje .= "PIN de perfil {$perfil->numeroper}: {$perfil->pinper}\n";

        Historial::create([
            'accion' => 'Se solicitó los datos de perfil ' . $perfil->numeroper . ' de la cuenta: ' . $cuenta->idcue,
            'descripcion' => null,
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);
        return response()->json(['mensaje' => $mensaje]);
    }

    public function edit($idcue)
    {
        if (!Auth::user()->hasPermissionTo('cuentas.update')) {
            abort(403, 'No tienes permiso para editar cuentas.');
        }
        $cuenta = Cuenta::with(['valor'])->findOrFail($idcue);
        $valores = Valor::all();
        return view('inventory.cuentas.edit', compact('cuenta', 'valores'));
    }

    public function renew($idcue)
    {
        if (!Auth::user()->hasPermissionTo('cuentas.renew')) {
            abort(403, 'No tienes permiso para renovar cuentas.');
        }
        $cuenta = Cuenta::with(['valor'])->findOrFail($idcue);
        $valor = $cuenta->idval;
        return view('inventory.cuentas.renew', compact('cuenta', 'valor'));
    }

    public function update(Request $request, $idcue)
    {
        if (!Auth::user()->hasPermissionTo('cuentas.update')) {
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
                'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
                'fecha' => now(),
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
        if (!Auth::user()->hasPermissionTo('cuentas.destroy')) {
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
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
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
