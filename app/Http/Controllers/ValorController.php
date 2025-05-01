<?php

namespace App\Http\Controllers;

use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Proveedor;
use App\Models\Cuenta;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CuentaService;

class ValorController extends Controller
{
    /*
    // Constructor original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:valores')->only('index');
        $this->middleware('can:valores.store')->only('create', 'store');
        $this->middleware('can:valores.update')->only('edit', 'update');
        $this->middleware('can:valores.destroy')->only('destroy');
    }
    */
    protected $cuentaService;

    public function __construct(CuentaService $cuentaService)
    {
        $this->cuentaService = $cuentaService;
    }
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('valores')) {
            abort(403, 'No tienes permiso para ver los valores.');
        }
        $valores = Valor::with(['proveedor', 'servicio'])->where('activoval', true)->get();
        return view('inventory.valores.index', compact('valores'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermissionTo('valores.store')) {
            abort(403, 'No tienes permiso para crear valores.');
        }
        $proveedores = Proveedor::where('activopro', true)->get();
        $servicios = Servicio::all();
        return view('inventory.valores.create', compact('servicios', 'proveedores'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('valores.store')) {
            abort(403, 'No tienes permiso para crear valores.');
        }

        $request->validate([
            'idval' => 'required|string|max:20|unique:valores,idval',
            'idser' => 'required|exists:servicios,idser',
            'idpro' => 'required|exists:proveedores,idpro',
            'costoval' => 'required|numeric|min:0|max:999.99',
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

        return redirect()->route('valores')->with('success', 'Valor creado con éxito.');
    }

    public function edit($idval)
    {
        if (!Auth::user()->hasPermissionTo('valores.update')) {
            abort(403, 'No tienes permiso para editar valores.');
        }
        $valor = Valor::with(['proveedor', 'servicio'])->findOrFail($idval);
        $proveedores = Proveedor::where('activopro', true)->get();
        $servicios = Servicio::all();
        return view('inventory.valores.edit', compact('valor', 'proveedores', 'servicios'));
    }

    public function update(Request $request, $idval)
    {
        if (!Auth::user()->hasPermissionTo('valores.update')) {
            abort(403, 'No tienes permiso para actualizar valores.');
        }

        $request->validate([
            'idser' => 'required|exists:servicios,idser',
            'idpro' => 'required|exists:proveedores,idpro',
            'costoval' => 'required|numeric|min:0|max:999.99',
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

        return redirect()->route('valores')->with('success', 'Valor actualizado con éxito.');
    }

    public function destroy($idval)
    {
        try {
            // Verificar permisos
            if (!Auth::user()->hasPermissionTo('valores.destroy')) {
                abort(403, 'No tienes permiso para eliminar valores.');
            }

            // Buscar el valor
            $valor = Valor::findOrFail($idval);

            $cuentasAsociadas = Cuenta::where('idval', $valor->idval)->where('activocue', true)->exists();
            if ($cuentasAsociadas) {
                return redirect()->route('proveedores')->with('error', 'No se puede eliminar porque tiene cuentas asociadas.');
            }

            // Generar nuevo ID para el valor
            $nuevoIdVal = $this->cuentaService->generarNuevoIdValor($valor->idval);

            // Registrar en historial
            Historial::create([
                'accion' => 'Se desactivó el valor con ID: ' . $valor->idval,
                'descripcion' => 'Datos inactivos: ' . json_encode($valor),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);
            // Desactivar el valor en lugar de eliminarlo
            $valor->update([
                'activoval' => false,
                'idval' => $nuevoIdVal
            ]);

            return redirect()->route('valores')->with('success', 'Valor desactivado con éxito.');
        } catch (\Exception $e) {
            return redirect()->route('valores')->with('error', 'Error al desactivar el valor: ' . $e->getMessage());
        }
    }
}
