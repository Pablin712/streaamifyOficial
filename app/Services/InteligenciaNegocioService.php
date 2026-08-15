<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as RequestFacade;

class InteligenciaNegocioService
{
    private const SEGMENTOS = [
        'fiel' => 'Más fieles (vigentes)',
        'gastador' => 'Los que más gastan (vigentes)',
        'nuevo' => 'Nuevos (primera compra vigente)',
        'regular' => 'Activos regulares',
        'en_riesgo' => 'En riesgo (eran fieles, dejaron de comprar)',
        'ocasional' => 'Compraban rara vez (inactivos)',
        'temporada' => 'De una temporada (se fueron)',
    ];

    /**
     * Desglose de ventas por tipo (nueva/renovacion/ampliacion/reactivacion)
     * dentro de un rango de fechas, con monto y cantidad de cada uno.
     */
    public function desgloseVentasPorTipo(string $desde, string $hasta): array
    {
        $filas = Venta::whereBetween('fechaven', [$desde, $hasta])
            ->select('tipo_venta', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(totalpagoven) as monto'))
            ->groupBy('tipo_venta')
            ->get()
            ->keyBy(fn ($f) => $f->tipo_venta ?? 'sin_clasificar');

        $tipos = ['nueva', 'ampliacion', 'renovacion', 'reactivacion', 'sin_clasificar'];
        $etiquetas = [
            'nueva' => 'Cliente nuevo',
            'ampliacion' => 'Cliente activo (compra más)',
            'renovacion' => 'Renovación',
            'reactivacion' => 'Cliente que vuelve',
            'sin_clasificar' => 'Sin clasificar (histórico)',
        ];

        $totalCantidad = $filas->sum('cantidad');
        $totalMonto = (float) $filas->sum('monto');

        $desglose = [];
        foreach ($tipos as $tipo) {
            $fila = $filas->get($tipo);
            $cantidad = $fila->cantidad ?? 0;
            $monto = (float) ($fila->monto ?? 0);
            $desglose[] = [
                'tipo' => $tipo,
                'etiqueta' => $etiquetas[$tipo],
                'cantidad' => $cantidad,
                'monto' => $monto,
                'pct_cantidad' => $totalCantidad > 0 ? round($cantidad / $totalCantidad * 100, 1) : 0,
                'pct_monto' => $totalMonto > 0 ? round($monto / $totalMonto * 100, 1) : 0,
            ];
        }

        return [
            'desglose' => $desglose,
            'total_cantidad' => $totalCantidad,
            'total_monto' => $totalMonto,
        ];
    }

    /**
     * Ventas por tipo, mes a mes, para los últimos $meses (grafico de barras apiladas).
     */
    public function ventasMensualesPorTipo(int $meses = 12): array
    {
        $desde = Carbon::now()->startOfMonth()->subMonths($meses - 1);

        $filas = Venta::where('fechaven', '>=', $desde->toDateString())
            ->select(
                DB::raw("DATE_FORMAT(fechaven, '%Y-%m') as mes"),
                DB::raw("COALESCE(tipo_venta, 'sin_clasificar') as tipo_venta"),
                DB::raw('COUNT(*) as cantidad')
            )
            ->groupBy('mes', 'tipo_venta')
            ->get();

        $labels = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $labels[] = Carbon::now()->subMonths($i)->format('Y-m');
        }

        $tipos = ['nueva', 'ampliacion', 'renovacion', 'reactivacion', 'sin_clasificar'];
        $series = array_fill_keys($tipos, array_fill_keys($labels, 0));

        foreach ($filas as $fila) {
            if (isset($series[$fila->tipo_venta][$fila->mes])) {
                $series[$fila->tipo_venta][$fila->mes] = (int) $fila->cantidad;
            }
        }

        return [
            'labels' => $labels,
            'series' => array_map('array_values', $series),
        ];
    }

    /**
     * Nuevos y perdidos por servicio, mes a mes, ultimos $meses. "Nuevo" = primera
     * vez que ese cliente compra ese servicio (por fecha de venta). "Perdido" = el
     * ultimo detalle de venta de ese cliente para ese servicio ya vencio y no se
     * volvio a renovar.
     */
    public function nuevosPerdidosPorServicio(int $meses = 12): array
    {
        $desde = Carbon::now()->startOfMonth()->subMonths($meses - 1)->toDateString();

        $nuevos = DB::select("
            SELECT mes, idser, nombreser, COUNT(*) as cantidad FROM (
                SELECT v.idcli, s.idser, s.nombreser,
                       DATE_FORMAT(v.fechaven, '%Y-%m') as mes,
                       ROW_NUMBER() OVER (PARTITION BY v.idcli, s.idser ORDER BY v.fechaven ASC) as rn
                FROM detalles_venta dv
                JOIN ventas v ON v.idven = dv.idven
                JOIN perfiles p ON p.idper = dv.idper
                JOIN cuentas c ON c.idcue = p.idcue
                JOIN valores va ON va.idval = c.idval
                JOIN servicios s ON s.idser = va.idser
            ) ranked
            WHERE rn = 1 AND mes >= ?
            GROUP BY mes, idser, nombreser
            ORDER BY mes
        ", [Carbon::parse($desde)->format('Y-m')]);

        $perdidos = DB::select("
            SELECT mes, idser, nombreser, COUNT(*) as cantidad FROM (
                SELECT v.idcli, s.idser, s.nombreser,
                       DATE_FORMAT(dv.fechavendet, '%Y-%m') as mes,
                       dv.fechavendet,
                       ROW_NUMBER() OVER (PARTITION BY v.idcli, s.idser ORDER BY dv.fechavendet DESC) as rn
                FROM detalles_venta dv
                JOIN ventas v ON v.idven = dv.idven
                JOIN perfiles p ON p.idper = dv.idper
                JOIN cuentas c ON c.idcue = p.idcue
                JOIN valores va ON va.idval = c.idval
                JOIN servicios s ON s.idser = va.idser
            ) ranked
            WHERE rn = 1 AND fechavendet < CURDATE() AND mes >= ?
            GROUP BY mes, idser, nombreser
            ORDER BY mes
        ", [Carbon::parse($desde)->format('Y-m')]);

        $labels = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $labels[] = Carbon::now()->subMonths($i)->format('Y-m');
        }

        $servicios = \App\Models\Servicio::pluck('nombreser', 'idser');
        $porServicio = [];
        foreach ($servicios as $idser => $nombre) {
            $porServicio[$idser] = [
                'nombre' => $nombre,
                'nuevos' => array_fill_keys($labels, 0),
                'perdidos' => array_fill_keys($labels, 0),
            ];
        }

        foreach ($nuevos as $fila) {
            if (isset($porServicio[$fila->idser]['nuevos'][$fila->mes])) {
                $porServicio[$fila->idser]['nuevos'][$fila->mes] = (int) $fila->cantidad;
            }
        }
        foreach ($perdidos as $fila) {
            if (isset($porServicio[$fila->idser]['perdidos'][$fila->mes])) {
                $porServicio[$fila->idser]['perdidos'][$fila->mes] = (int) $fila->cantidad;
            }
        }

        return ['labels' => $labels, 'servicios' => $porServicio];
    }

    /**
     * Segmentacion RFM (Recency/Frequency/Monetary) de todos los clientes con al
     * menos una venta. Se calcula en memoria (volumen actual: unos pocos miles de
     * clientes, sin problema); si crece mucho, cachear en una tabla resumen.
     */
    public function clientesRFM(int $page = 1, int $perPage = 25, ?string $segmento = null, ?string $search = null): LengthAwarePaginator
    {
        $todos = $this->calcularRFM();

        if ($segmento) {
            $todos = $todos->where('segmento', $segmento)->values();
        }
        if ($search) {
            $needle = mb_strtolower($search);
            $todos = $todos->filter(function ($c) use ($needle) {
                return str_contains(mb_strtolower($c['nombre']), $needle)
                    || str_contains((string) $c['telefono'], $needle);
            })->values();
        }

        $slice = $todos->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $todos->count(),
            $perPage,
            $page,
            ['path' => RequestFacade::url(), 'query' => RequestFacade::query()]
        );
    }

    public function segmentos(): array
    {
        return self::SEGMENTOS;
    }

    public function resumenSegmentos(): Collection
    {
        return $this->calcularRFM()
            ->groupBy('segmento')
            ->map(fn ($grupo, $seg) => [
                'segmento' => $seg,
                'etiqueta' => self::SEGMENTOS[$seg] ?? $seg,
                'cantidad' => $grupo->count(),
                'monetary_total' => round($grupo->sum('monetary'), 2),
            ])
            ->sortByDesc('cantidad')
            ->values();
    }

    private function calcularRFM(): Collection
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $filas = DB::table('ventas as v')
            ->join('clientes as c', 'c.idcli', '=', 'v.idcli')
            ->select(
                'c.idcli',
                'c.nombrecli',
                'c.telefonocli',
                DB::raw('COUNT(v.idven) as frequency'),
                DB::raw('SUM(v.totalpagoven) as monetary'),
                DB::raw('MAX(v.fechaven) as ultima_compra'),
                DB::raw('MIN(v.fechaven) as primera_compra')
            )
            ->groupBy('c.idcli', 'c.nombrecli', 'c.telefonocli')
            ->get();

        // Vencimiento real de la ultima suscripcion (aparte, para no multiplicar
        // totalpagoven al hacer join 1-a-muchos con detalles_venta).
        $vencimientos = DB::table('detalles_venta as dv')
            ->join('ventas as v', 'v.idven', '=', 'dv.idven')
            ->select('v.idcli', DB::raw('MAX(dv.fechavendet) as vencimiento_max'))
            ->groupBy('v.idcli')
            ->pluck('vencimiento_max', 'idcli');

        $hoy = Carbon::today();

        $cache = $filas->map(function ($f) use ($hoy, $vencimientos) {
            $ultima = Carbon::parse($f->ultima_compra);
            $primera = Carbon::parse($f->primera_compra);
            $recency = $ultima->diffInDays($hoy);
            $antiguedad = $primera->diffInDays($hoy);
            $frequency = (int) $f->frequency;
            $monetary = (float) $f->monetary;
            $vencimiento = $vencimientos[$f->idcli] ?? null;
            $vigente = $vencimiento && Carbon::parse($vencimiento)->gte($hoy);

            return [
                'idcli' => $f->idcli,
                'nombre' => $f->nombrecli,
                'telefono' => $f->telefonocli,
                'frequency' => $frequency,
                'monetary' => $monetary,
                'recency_dias' => $recency,
                'antiguedad_dias' => $antiguedad,
                'primera_compra' => $primera->toDateString(),
                'ultima_compra' => $ultima->toDateString(),
                'vigente' => $vigente,
                'segmento' => null, // se llena abajo, necesita los umbrales
            ];
        });

        // Umbrales relativos (top 20%) sobre toda la base, para "los que mas
        // gastan" y "los mas fieles" — se combinan con un piso fijo razonable
        // para este negocio (compras de bajo monto y ciclos cortos).
        $umbralGastador = $cache->sortByDesc('monetary')
            ->values()
            ->get((int) floor($cache->count() * 0.2))['monetary'] ?? 0;
        $umbralFrequency = max(4, $cache->sortByDesc('frequency')
            ->values()
            ->get((int) floor($cache->count() * 0.2))['frequency'] ?? 4);

        $cache = $cache->map(function ($c) use ($umbralGastador, $umbralFrequency) {
            $c['segmento'] = $this->clasificarSegmento($c, $umbralGastador, $umbralFrequency);
            return $c;
        });

        return $cache;
    }

    /**
     * Clasificacion basada en si el cliente sigue con algo vigente (pagado y no
     * vencido) o no, en vez de umbrales de dias fijos: en este negocio los
     * ciclos de suscripcion varian (mensual, trimestral, anual...), asi que "se
     * fue" solo se puede afirmar con certeza cuando su ultima suscripcion ya
     * vencio y no volvio a comprar — no por una cantidad arbitraria de dias.
     */
    private function clasificarSegmento(array $c, float $umbralGastador, int $umbralFrequency): string
    {
        if ($c['vigente']) {
            if ($c['frequency'] >= $umbralFrequency) {
                return 'fiel';
            }
            if ($c['monetary'] >= $umbralGastador && $umbralGastador > 0) {
                return 'gastador';
            }
            if ($c['frequency'] === 1) {
                return 'nuevo';
            }
            return 'regular';
        }

        // Ya vencio y no ha vuelto a comprar.
        if ($c['frequency'] === 1) {
            return 'temporada'; // compro una sola vez y no volvio
        }
        if ($c['frequency'] >= $umbralFrequency) {
            return 'en_riesgo'; // era un cliente frecuente/fiel y dejo de comprar
        }
        return 'ocasional'; // compraba de vez en cuando, ahora inactivo
    }

    /**
     * Cohortes de retencion: de los clientes cuya primera compra fue en cada uno
     * de los ultimos $meses meses, que % sigue con una compra registrada 1/3/6
     * meses despues de esa primera compra.
     */
    public function cohortesRetencion(int $meses = 12): array
    {
        $cohortes = [];
        $offsets = [1, 3, 6];

        for ($i = $meses; $i >= 1; $i--) {
            $inicioCohorte = Carbon::now()->startOfMonth()->subMonths($i);
            $finCohorte = $inicioCohorte->copy()->endOfMonth();

            $clientesCohorte = DB::table('ventas as v')
                ->select('v.idcli', DB::raw('MIN(v.fechaven) as primera'))
                ->groupBy('v.idcli')
                ->havingRaw('MIN(v.fechaven) BETWEEN ? AND ?', [$inicioCohorte->toDateString(), $finCohorte->toDateString()])
                ->get();

            $totalCohorte = $clientesCohorte->count();
            if ($totalCohorte === 0) {
                continue;
            }
            $idclis = $clientesCohorte->pluck('idcli');

            $fila = [
                'mes' => $inicioCohorte->format('Y-m'),
                'clientes' => $totalCohorte,
            ];

            foreach ($offsets as $offset) {
                $fechaCorte = $inicioCohorte->copy()->addMonths($offset);
                if ($fechaCorte->gt(Carbon::now())) {
                    $fila["m{$offset}"] = null; // todavia no paso ese tiempo para esta cohorte
                    continue;
                }
                $retenidos = DB::table('ventas')
                    ->whereIn('idcli', $idclis)
                    ->where('fechaven', '>=', $fechaCorte->toDateString())
                    ->distinct('idcli')
                    ->count('idcli');

                $fila["m{$offset}"] = round($retenidos / $totalCohorte * 100, 1);
            }

            $cohortes[] = $fila;
        }

        return $cohortes;
    }
}
