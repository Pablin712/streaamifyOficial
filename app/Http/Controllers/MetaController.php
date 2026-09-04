<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use App\Models\Meta;
use App\Services\MetaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class MetaController extends Controller
{
    public function __construct(
        private MetaService $metaService
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('metas')) {
            abort(403, 'No tienes permiso para ver las metas.');
        }

        $hoy  = Carbon::now();
        $mes  = (int) $request->input('mes', $hoy->month);
        $anio = (int) $request->input('anio', $hoy->year);

        if ($mes < 1 || $mes > 12) {
            $mes = $hoy->month;
        }

        $tablero = $this->metaService->tablero($mes, $anio);

        return view('finance.metas.index', [
            'tablero'  => $tablero,
            'resumen'  => $this->metaService->resumen($tablero),
            'metas'    => Meta::orderBy('activo', 'desc')->orderBy('kpi')->get(),
            'catalogo' => $this->metaService->catalogoPorGrupo(),
            'mes'      => $mes,
            'anio'     => $anio,
            'periodo'  => Carbon::create($anio, $mes, 1)->locale('es')->translatedFormat('F Y'),
            'servicio' => $this->metaService,
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('metas.store')) {
            abort(403, 'No tienes permiso para crear metas.');
        }

        $datos = $this->validar($request);

        Meta::updateOrCreate(
            [
                'kpi'  => $datos['kpi'],
                'anio' => $datos['anio'],
                'mes'  => $datos['mes'],
            ],
            $datos
        );

        $this->registrar('Creación de meta', $datos);

        return redirect()->back()->with('success', 'Meta guardada.');
    }

    public function update(Request $request, int $idmet)
    {
        if (!Gate::allows('metas.update')) {
            abort(403, 'No tienes permiso para editar metas.');
        }

        $meta  = Meta::findOrFail($idmet);
        $datos = $this->validar($request, $meta);

        $meta->update($datos);

        $this->registrar('Edición de meta', $datos);

        return redirect()->back()->with('success', 'Meta actualizada.');
    }

    public function destroy(int $idmet)
    {
        if (!Gate::allows('metas.destroy')) {
            abort(403, 'No tienes permiso para eliminar metas.');
        }

        $meta = Meta::findOrFail($idmet);
        $kpi  = $meta->kpi;
        $meta->delete();

        $this->registrar('Eliminación de meta', ['kpi' => $kpi]);

        return redirect()->back()->with('success', 'Meta eliminada.');
    }

    /**
     * El periodo determina que campos de fecha tienen sentido: una meta anual
     * no lleva mes, y una meta permanente (recurrente) no lleva ninguno.
     */
    private function validar(Request $request, ?Meta $meta = null): array
    {
        $datos = $request->validate([
            'kpi'             => ['required', 'string', Rule::in(array_keys($this->metaService->catalogo()))],
            'objetivo'        => ['required', 'numeric', 'min:0', 'max:99999999'],
            'periodo'         => ['required', Rule::in(['mensual', 'anual'])],
            'alcance'         => ['required', Rule::in(['periodo', 'permanente'])],
            'anio'            => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'mes'             => ['nullable', 'integer', 'min:1', 'max:12'],
            'umbral_atencion' => ['nullable', 'integer', 'min:10', 'max:100'],
            'activo'          => ['nullable', 'boolean'],
            'nota'            => ['nullable', 'string', 'max:255'],
        ]);

        $permanente = $datos['alcance'] === 'permanente';

        return [
            'kpi'             => $datos['kpi'],
            'objetivo'        => $datos['objetivo'],
            'periodo'         => $datos['periodo'],
            'anio'            => $permanente ? null : ($datos['anio'] ?? Carbon::now()->year),
            'mes'             => $permanente || $datos['periodo'] === 'anual'
                                    ? null
                                    : ($datos['mes'] ?? Carbon::now()->month),
            'umbral_atencion' => $datos['umbral_atencion'] ?? 90,
            'activo'          => (bool) ($datos['activo'] ?? true),
            'nota'            => $datos['nota'] ?? null,
        ];
    }

    private function registrar(string $accion, array $datos): void
    {
        Historial::create([
            'accion'      => $accion,
            'descripcion' => 'Meta ' . ($datos['kpi'] ?? '?') . ': ' . json_encode($datos),
            'empleado_id' => Auth::user()->idemp,
            'created_at'  => now(),
        ]);
    }
}
