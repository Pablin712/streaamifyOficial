<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use App\Models\Fondo;
use App\Models\MneRecarga;
use App\Services\MneRecargaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Panel de "Mi Negocio Efectivo": ver docs/finanzas/miNegocioEfectivo.md
 */
class MneController extends Controller
{
    public function __construct(protected MneRecargaService $mneRecargaService)
    {
    }

    public function index(Request $request)
    {
        $fondoMne = Fondo::where('nombre', 'Mi Negocio Efectivo')->firstOrFail();
        $fondoEfectivo = Fondo::where('nombre', 'Efectivo')->first();
        $bancos = Banco::orderBy('nombreban')->get();

        $hoy = Carbon::today();
        $inicioSemana = Carbon::now()->startOfWeek();
        $inicioMes = Carbon::now()->startOfMonth();

        $base = MneRecarga::where('anulada', false);

        $gananciaHoy = (clone $base)->whereDate('fecha', $hoy)->sum('ganancia');
        $gananciaSemana = (clone $base)->where('fecha', '>=', $inicioSemana)->sum('ganancia');
        $gananciaMes = (clone $base)->where('fecha', '>=', $inicioMes)->sum('ganancia');
        $recargasHoy = (clone $base)->whereDate('fecha', $hoy)->count();

        $recargas = MneRecarga::with(['banco', 'fondoCobro'])
            ->orderBy('fecha', 'desc')
            ->limit(50)
            ->get();

        return view('finanzas.mne.index', compact(
            'fondoMne', 'fondoEfectivo', 'bancos',
            'gananciaHoy', 'gananciaSemana', 'gananciaMes', 'recargasHoy', 'recargas'
        ));
    }

    public function storeRecarga(Request $request)
    {
        $request->validate([
            'operadora' => 'required|string|max:50',
            'cliente_nombre' => 'nullable|string|max:150',
            'cliente_telefono' => 'nullable|string|max:20',
            'valor_cobrado' => 'required|numeric|min:0.01',
            'costo_fondo' => 'required|numeric|min:0.0001',
            'fondo_id' => 'required|exists:fondos,id',
            'cobro_banco_id' => 'nullable|exists:bancos,idban',
            'cobro_fondo_id' => 'nullable|exists:fondos,id',
            'notas' => 'nullable|string',
        ]);

        try {
            $this->mneRecargaService->registrarRecarga($request->only([
                'operadora', 'cliente_nombre', 'cliente_telefono', 'valor_cobrado',
                'costo_fondo', 'fondo_id', 'cobro_banco_id', 'cobro_fondo_id', 'notas',
            ]));

            return redirect()->route('mne.index')->with('success', 'Recarga registrada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('mne.index')->with('error', $e->getMessage());
        }
    }

    public function anularRecarga($id)
    {
        try {
            $this->mneRecargaService->anularRecarga((int) $id);

            return redirect()->route('mne.index')->with('success', 'Recarga anulada, saldos revertidos.');
        } catch (\Exception $e) {
            return redirect()->route('mne.index')->with('error', $e->getMessage());
        }
    }
}
