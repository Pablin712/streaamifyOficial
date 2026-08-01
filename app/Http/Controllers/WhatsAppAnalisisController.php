<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\WhatsappAnalisisConversacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Inteligencia de negocio sobre las conversaciones de WhatsApp ya analizadas por IA
 * (whatsapp:analizar-satisfaccion). Fase 1 del plan en docs/optimizacion/idea-soporte.md:
 * solo lectura/agregacion, no reparte puntos.
 */
class WhatsAppAnalisisController extends Controller
{
    public function index(Request $request)
    {
        if (! Gate::allows('empleados')) {
            abort(403);
        }

        $periodo = $request->get('periodo', 'semana');
        $desde = match ($periodo) {
            'hoy' => Carbon::today(),
            'mes' => Carbon::now()->startOfMonth(),
            'todo' => null,
            default => Carbon::now()->startOfWeek(),
        };
        $hasta = Carbon::now()->endOfDay();

        $query = WhatsappAnalisisConversacion::query();
        if ($desde) {
            $query->whereDate('fecha_conversacion', '>=', $desde->toDateString());
        }
        $query->whereDate('fecha_conversacion', '<=', $hasta->toDateString());

        $analisis = $query->get();

        $cuentasActivasPorServicio = DB::table('cuentas')
            ->join('valores', 'cuentas.idval', '=', 'valores.idval')
            ->where('cuentas.activocue', true)
            ->select('valores.idser', DB::raw('count(*) as total'))
            ->groupBy('valores.idser')
            ->pluck('total', 'idser');

        $servicios = Servicio::orderBy('nombreser')->get();

        $porServicio = $servicios->map(function (Servicio $servicio) use ($analisis, $cuentasActivasPorServicio) {
            $delServicio = $analisis->where('servicio_idser', $servicio->idser);
            $cuentasActivas = (int) ($cuentasActivasPorServicio[$servicio->idser] ?? 0);
            $totalConversaciones = $delServicio->count();
            $tiempos = $delServicio->pluck('tiempo_respuesta_promedio_segundos')->filter();

            return [
                'servicio' => $servicio,
                'cuentas_activas' => $cuentasActivas,
                'total_conversaciones' => $totalConversaciones,
                'satisfaccion_promedio' => $totalConversaciones > 0
                    ? round($delServicio->avg('satisfaccion_score'), 2)
                    : null,
                'tiempo_respuesta_promedio_min' => $tiempos->isNotEmpty()
                    ? round($tiempos->avg() / 60, 1)
                    : null,
                'por_motivo' => collect(['soporte_tecnico', 'solicitar_codigo', 'compra', 'renovacion', 'consulta_general', 'otro'])
                    ->mapWithKeys(fn ($motivo) => [$motivo => $delServicio->where('motivo_contacto', $motivo)->count()]),
                'perdidas' => $delServicio->where('motivo_perdida', '!=', 'no_aplica')->count(),
                // Contactos por cada 100 cuentas activas: permite comparar servicios de
                // distinto tamano sin que el mas grande "gane" solo por tener mas cuentas.
                'tasa_por_100_cuentas' => $cuentasActivas > 0
                    ? round(($totalConversaciones / $cuentasActivas) * 100, 1)
                    : null,
            ];
        })->filter(fn ($fila) => $fila['total_conversaciones'] > 0 || $fila['cuentas_activas'] > 0)
          ->sortByDesc('total_conversaciones')
          ->values();

        $sinServicio = $analisis->whereNull('servicio_idser')->count();
        $total = $analisis->count();

        $distribucionSatisfaccion = collect(range(1, 5))->mapWithKeys(
            fn ($score) => [$score => $analisis->where('satisfaccion_score', $score)->count()]
        );
        $moda = $total > 0 ? $distribucionSatisfaccion->sortDesc()->keys()->first() : null;

        $motivoContactoGlobal = collect(['soporte_tecnico', 'solicitar_codigo', 'compra', 'renovacion', 'consulta_general', 'otro'])
            ->mapWithKeys(fn ($motivo) => [$motivo => $analisis->where('motivo_contacto', $motivo)->count()]);

        $motivoPerdidaGlobal = collect(['mala_atencion', 'sin_respuesta', 'sin_presupuesto', 'no_continuara', 'otro_servicio', 'otro'])
            ->mapWithKeys(fn ($motivo) => [$motivo => $analisis->where('motivo_perdida', $motivo)->count()])
            ->filter();

        $cruceEmpleados = collect(['ninguno', 'tardado', 'dividido'])
            ->mapWithKeys(fn ($tipo) => [$tipo => $analisis->where('cruce_empleados', $tipo)->count()]);

        $tiemposGlobal = $analisis->pluck('tiempo_respuesta_promedio_segundos')->filter();

        return view('whatsapp.analisis', [
            'periodo' => $periodo,
            'desde' => $desde,
            'porServicio' => $porServicio,
            'totalAnalizadas' => $total,
            'sinServicio' => $sinServicio,
            'satisfaccionGlobal' => $total > 0 ? round($analisis->avg('satisfaccion_score'), 2) : null,
            'distribucionSatisfaccion' => $distribucionSatisfaccion,
            'moda' => $moda,
            'buenas' => $analisis->whereIn('satisfaccion_score', [4, 5])->count(),
            'regulares' => $analisis->where('satisfaccion_score', 3)->count(),
            'malas' => $analisis->whereIn('satisfaccion_score', [1, 2])->count(),
            'motivoContactoGlobal' => $motivoContactoGlobal,
            'motivoPerdidaGlobal' => $motivoPerdidaGlobal,
            'totalPerdidas' => $analisis->where('motivo_perdida', '!=', 'no_aplica')->count(),
            'cruceEmpleados' => $cruceEmpleados,
            'tiempoRespuestaPromedioMin' => $tiemposGlobal->isNotEmpty() ? round($tiemposGlobal->avg() / 60, 1) : null,
            'sinRespuesta' => $analisis->where('respondido', false)->count(),
        ]);
    }
}
