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
use Illuminate\Support\Facades\Gate;
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

        return view('inventory.valores.index', compact('valores', 'serviciosPrincipales'));
    }

    public function create()
    {
        if (!Gate::allows('valores.store')) {
            abort(403, 'No tienes permiso para crear valores.');
        }
        $proveedores = Proveedor::where('activopro', true)->get();
        $servicios = Servicio::all();
        return view('inventory.valores.create', compact('servicios', 'proveedores'));
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

            $cuentasAsociadas = Cuenta::where('idval', $valor->idval)->where('activocue', true)->exists();
            if ($cuentasAsociadas) {
                return redirect()->back()->with('error', 'No se puede eliminar porque tiene cuentas asociadas.');
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
        // Verificar permisos
        if (!Gate::allows('valores.destroy')) {
            abort(403, 'No tienes permiso para eliminar valores.');
        }
        $valores = Valor::where('activoval', true)->get();
        foreach ($valores as $valor) {
            // Verificar si tiene cuentas activas asociadas
            $cuentasAsociadas = Cuenta::where('idval', $valor->idval)
                ->where('activocue', true)
                ->exists();
            if ($cuentasAsociadas) {
                // Opcional: podrías registrar en historial que no se eliminó por cuentas asociadas
                continue;
            }

            // Generar nuevo ID para el valor
            if (isset($this->cuentaService)) {
                $nuevoIdVal = $this->cuentaService->generarNuevoIdValor($valor->idval);
            } else {
                $nuevoIdVal = $valor->idval . '-inactivo';
            }

            // Registrar en historial
            Historial::create([
                'accion' => 'Se desactivó el valor con ID: ' . $valor->idval,
                'descripcion' => 'Datos inactivos: ' . json_encode($valor),
                'empleado_id' => Auth::user()->idemp ?? null,
                'created_at' => now(),
            ]);

            // Desactivar el valor y actualizar idval
            $valor->update([
                'activoval' => false,
                'idval' => $nuevoIdVal
            ]);
        }
        return redirect()->route('valores')->with('success', 'Valores inactivos procesados correctamente.');
    }
}
