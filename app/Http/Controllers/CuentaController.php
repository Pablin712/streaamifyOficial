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
        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)
                ->where('fecha_vencimiento', '>', now())
                ->count();
            $cuenta->usuarios_activos = $usuarios;
        }

        $espacios_por_servicio = $this->calcularEspaciosPorServicio();

        return view('inventory.cuentas.index', compact('cuentas', 'perfiles', 'idcueSeleccionado', 'espacios_por_servicio'));
    }
    private function calcularEspaciosPorServicio(){
        $servicios = ['NETFLIX', 'DISNEYP', 'DISNEYS', 'MAX', 'PRIME', 'PARAMOUNT', 'CRUNCHY', 'SPOTIFY', 'MAGIS'];
        $espacios_por_servicio = [];

        foreach ($servicios as $servicio) {
            // Obtener todas las cuentas activas que pertenezcan a este servicio
            $cuentas = Cuenta::with(['valor.servicio']) // Cargamos servicio a través de valor
                ->whereHas('valor.servicio', function ($query) use ($servicio) {
                    $query->where('idser', $servicio); // Filtrar por nombre del servicio
                })
                ->where('activocue', true)
                ->orderBy('fechavencue')
                ->get();
            $espacios = 0;
            foreach ($cuentas as $cuenta) {
                $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->where('fecha_vencimiento', '>', now())->count();
                $pantmaxval = $cuenta->valor->pantmaxval ?? 0; // Verificamos que el valor no sea nulo
                $resta = $pantmaxval - $usuarios;
                $espacios += max($resta, 0); // Evitamos valores negativos
            }
            // Guardar el total de espacios disponibles para este servicio
            $espacios_por_servicio[$servicio] = $espacios;
        }
        return $espacios_por_servicio;
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
            $this->actualizarEstadoProductos($cuenta->valor->idser);

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

        $this->actualizarEstadoProductos($cuenta->valor->idser);
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
            $this->actualizarEstadoProductos($cuenta->valor->idser);
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

        $nuevoId = $this->generarNuevoId($cuenta->idcue);
        $perfiles = Perfil::where('idcue', $cuenta->idcue)->get();

        foreach ($perfiles as $perfil) {
            $nuevoIdPer = $this->generarNuevoIdPerfil($perfil->idper);
            $perfil->update([
                'idper' => $nuevoIdPer
            ]);
        }

        $cuenta->update([
            'activocue' => false,
            'idcue' => $nuevoId
        ]);
        $this->actualizarEstadoProductos($cuenta->valor->idser);
        return redirect()->route('cuentas')->with('success', 'Cuenta desactivada con éxito.');
    }

    private function actualizarEstadoProductos($idser)
    {
        $productos = Producto::where('tipo_producto_id', 1)
            ->whereHas('detalles', function ($query) use ($idser) {
                $query->where('idser', $idser);
            })->get();

        foreach ($productos as $producto) {
            $cuentaDisponible = $this->buscarCuentaDisponible($idser);
            $producto->update(['activo' => $cuentaDisponible ? true : false]);
        }
    }

    private function buscarCuentaDisponible($idser)
    {
        return Cuenta::whereHas('valor', function ($query) use ($idser) {
            $query->where('idser', $idser);
        })
            ->where('caidacue', false)
            ->where('activocue', true)
            ->whereHas('valor', function ($query) {
                $query->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos WHERE view_usuarios_activos.idcue = cuentas.idcue) < valores.pantmaxval');
            })
            ->first();
    }

    private function generarNuevoId($idcue)
    {
        $baseId = preg_replace('/_borrada\d*$/', '', $idcue);
        $contador = 1;

        $ultimoId = Cuenta::where('idcue', 'LIKE', "{$baseId}_borrada%")
            ->orderByRaw("LENGTH(idcue) DESC")
            ->orderBy('idcue', 'DESC')
            ->pluck('idcue')
            ->first();

        if ($ultimoId) {
            preg_match('/_borrada(\d+)$/', $ultimoId, $matches);
            if (!empty($matches[1])) {
                $contador = (int) $matches[1] + 1;
            }
        }

        return "{$baseId}_borrada{$contador}";
    }

    private function generarNuevoIdPerfil($idper)
    {
        $baseId = preg_replace('/_borrada\d*$/', '', $idper);
        $contador = 1;

        $ultimoId = Perfil::where('idper', 'LIKE', "{$baseId}_borrada%")
            ->orderByRaw("LENGTH(idper) DESC")
            ->orderBy('idper', 'DESC')
            ->pluck('idper')
            ->first();

        if ($ultimoId) {
            preg_match('/_borrada(\d+)$/', $ultimoId, $matches);
            if (!empty($matches[1])) {
                $contador = (int) $matches[1] + 1;
            }
        }

        return "{$baseId}_borrada{$contador}";
    }
}
