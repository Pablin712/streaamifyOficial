<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class HorarioController extends Controller
{
    /** Tope de ocurrencias por serie, para no permitir crear cientos de filas por error. */
    private const MAX_OCURRENCIAS = 52;

    public function store(Request $request)
    {
        if (!Gate::allows('cuentas')) {
            abort(403);
        }

        $validated = $request->validate([
            'empleado_id'   => 'nullable|exists:empleados,idemp',
            'fecha'         => 'required|date|after_or_equal:today',
            'hora_inicio'   => 'nullable|date_format:H:i',
            'hora_fin'      => 'nullable|date_format:H:i',
            'notas'         => 'nullable|string|max:300',
            'repetir'       => 'nullable|in:no,semanal,dias_semana',
            'dias_semana'   => 'required_if:repetir,dias_semana|array',
            'dias_semana.*' => 'integer|between:1,7',
            'repetir_hasta' => 'required_if:repetir,semanal|required_if:repetir,dias_semana|nullable|date|after_or_equal:fecha',
        ]);

        $user = Auth::user();
        $empleadoId = ($user->hasAnyRole(['Admin', 'Gerente']) && !empty($validated['empleado_id']))
            ? (int) $validated['empleado_id']
            : $user->idemp;

        $repetir = $validated['repetir'] ?? 'no';
        $fechas  = $this->calcularFechasOcurrencias(
            Carbon::parse($validated['fecha']),
            $repetir,
            $validated['dias_semana'] ?? [],
            $validated['repetir_hasta'] ?? null
        );

        $recurrenciaId = count($fechas) > 1 ? (string) Str::uuid() : null;

        $horarios = collect($fechas)->map(function (Carbon $fecha) use ($validated, $empleadoId, $user, $recurrenciaId) {
            return Horario::create([
                'empleado_id'    => $empleadoId,
                'fecha'          => $fecha->toDateString(),
                'hora_inicio'    => $validated['hora_inicio'] ?? null,
                'hora_fin'       => $validated['hora_fin'] ?? null,
                'notas'          => $validated['notas'] ?? null,
                'creado_por'     => $user->idemp,
                'cancelado'      => false,
                'recurrencia_id' => $recurrenciaId,
            ]);
        });

        $primero = $horarios->first();

        return response()->json([
            'success'        => true,
            'count'          => $horarios->count(),
            'recurrencia_id' => $recurrenciaId,
            // Compatibilidad con el flujo sin repetición (una sola ocurrencia)
            'id'             => $primero->id,
            'nombre'         => $user->nombreemp,
            'empleado_id'    => $empleadoId,
            'es_mio'         => $empleadoId === $user->idemp,
            'estado'         => 'programado',
            'hora_inicio'    => $primero->hora_inicio ? substr($primero->hora_inicio, 0, 5) : null,
            'hora_fin'       => $primero->hora_fin    ? substr($primero->hora_fin,    0, 5) : null,
            'notas'          => $primero->notas,
            // Lista completa de ocurrencias creadas, para pintarlas todas sin recargar la página
            'horarios'       => $horarios->map(fn($h) => [
                'id'             => $h->id,
                'empleado_id'    => $empleadoId,
                'nombre'         => $user->nombreemp,
                'fecha'          => $h->fecha->format('Y-m-d'),
                'hora_inicio'    => $h->hora_inicio ? substr($h->hora_inicio, 0, 5) : null,
                'hora_fin'       => $h->hora_fin    ? substr($h->hora_fin,    0, 5) : null,
                'sesiones'       => [],
                'estado'         => 'programado',
                'es_mio'         => $empleadoId === $user->idemp,
                'notas'          => $h->notas,
                'recurrencia_id' => $recurrenciaId,
            ])->values(),
        ]);
    }

    /**
     * Calcula las fechas de cada ocurrencia de la serie.
     * - 'no': solo la fecha ancla.
     * - 'semanal': la fecha ancla y luego cada 7 días hasta $hasta (inclusive).
     * - 'dias_semana': cada semana, en los días de la semana indicados (1=lunes..7=domingo),
     *   desde la fecha ancla hasta $hasta (inclusive). Si la fecha ancla no cae en uno de los
     *   días elegidos, igual se agenda (es la ocurrencia que el usuario pidió explícitamente).
     */
    private function calcularFechasOcurrencias(Carbon $anchor, string $repetir, array $diasSemana, ?string $hasta): array
    {
        if ($repetir === 'no' || empty($hasta)) {
            return [$anchor->copy()];
        }

        $limite = Carbon::parse($hasta)->endOfDay();
        $fechas = [$anchor->copy()];

        if ($repetir === 'semanal') {
            $cursor = $anchor->copy()->addWeek();
            while ($cursor->lte($limite) && count($fechas) < self::MAX_OCURRENCIAS) {
                $fechas[] = $cursor->copy();
                $cursor->addWeek();
            }
            return $fechas;
        }

        // dias_semana
        $diasSemana = array_values(array_unique(array_map('intval', $diasSemana)));
        if (empty($diasSemana)) {
            return [$anchor->copy()];
        }

        $cursor = $anchor->copy()->addDay();
        while ($cursor->lte($limite) && count($fechas) < self::MAX_OCURRENCIAS) {
            if (in_array($cursor->isoWeekday(), $diasSemana, true)) {
                $fechas[] = $cursor->copy();
            }
            $cursor->addDay();
        }

        return $fechas;
    }

    public function destroy(Request $request, int $id)
    {
        $horario = Horario::findOrFail($id);
        $user = Auth::user();

        if ($horario->empleado_id !== $user->idemp && !$user->hasAnyRole(['Admin', 'Gerente'])) {
            abort(403);
        }

        // alcance=serie: cancela esta ocurrencia y todas las futuras de la misma serie recurrente
        if ($request->query('alcance') === 'serie' && $horario->recurrencia_id) {
            $ids = Horario::where('recurrencia_id', $horario->recurrencia_id)
                ->where('fecha', '>=', $horario->fecha)
                ->pluck('id');

            Horario::whereIn('id', $ids)->delete();

            return response()->json(['success' => true, 'ids' => $ids->values()]);
        }

        $horario->delete();

        return response()->json(['success' => true, 'ids' => [$id]]);
    }
}
