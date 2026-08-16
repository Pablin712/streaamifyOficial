<?php

namespace App\Services;

use App\Models\Cuenta;
use App\Models\CuentaIncidencia;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Salud operativa de las cuentas (activas / vencidas sin renovar / dañadas)
 * por servicio, y seguimiento de cuanto tiempo pasan dañadas
 * (cuenta_incidencias, alimentada por CuentaObserver). Ver
 * docs/finanzas/dashboardInteligenciaNegocio.md.
 */
class CuentaSaludService
{
    private const SERVICIOS_PRINCIPALES = ['NETFLIX', 'DISNEYP', 'PRIME', 'MAX', 'MAGIS', 'CRUNCHY', 'PARAMOUNT', 'SPOTIFY'];

    private const LABELS = [
        'NETFLIX' => 'Netflix',
        'DISNEYP' => 'Disney',
        'PRIME' => 'Prime',
        'MAX' => 'Max',
        'MAGIS' => 'Flujo',
        'CRUNCHY' => 'Crunchyroll',
        'PARAMOUNT' => 'Paramount',
        'SPOTIFY' => 'Spotify',
        'OTROS' => 'Otros',
    ];

    private function bucket(?string $idser): string
    {
        return in_array($idser, self::SERVICIOS_PRINCIPALES, true) ? $idser : 'OTROS';
    }

    private function cuentasReales(): Collection
    {
        return Cuenta::with('valor.servicio')
            ->where('activocue', true)
            ->get()
            ->filter(fn ($c) => !Str::endsWith($c->idcue, 'Atencion'));
    }

    /**
     * Conteo actual (foto de hoy) de activas/vencidas sin renovar/dañadas por
     * cada uno de los 8 servicios principales + Otros.
     */
    public function resumenPorServicio(): array
    {
        $hoy = Carbon::now();
        $buckets = [];
        foreach (array_merge(self::SERVICIOS_PRINCIPALES, ['OTROS']) as $key) {
            $buckets[$key] = ['activas' => 0, 'vencidas' => 0, 'danadas' => 0, 'total' => 0];
        }

        foreach ($this->cuentasReales() as $c) {
            $b = $this->bucket($c->valor->servicio->idser ?? null);
            $buckets[$b]['total']++;
            if ($c->caidacue) {
                $buckets[$b]['danadas']++;
            } elseif (Carbon::parse($c->fechavencue)->lt($hoy)) {
                $buckets[$b]['vencidas']++;
            } else {
                $buckets[$b]['activas']++;
            }
        }

        $resultado = [];
        foreach ($buckets as $key => $datos) {
            $resultado[] = ['idser' => $key, 'nombre' => self::LABELS[$key]] + $datos;
        }

        return $resultado;
    }

    /**
     * Cuentas nuevas dañadas / reparadas en un rango de fechas, y el tiempo
     * promedio de reparación (solo de las que ya se cerraron en ese rango),
     * por servicio.
     */
    public function incidenciasPorServicio(string $desde, string $hasta): array
    {
        $abiertas = CuentaIncidencia::whereBetween('inicio', [$desde, $hasta])
            ->get()
            ->groupBy(fn ($i) => $this->bucket($i->servicio_idser));

        $cerradas = CuentaIncidencia::whereNotNull('fin')
            ->whereBetween('fin', [$desde, $hasta])
            ->get()
            ->groupBy(fn ($i) => $this->bucket($i->servicio_idser));

        $filas = [];
        foreach (array_merge(self::SERVICIOS_PRINCIPALES, ['OTROS']) as $key) {
            $nuevas = $abiertas->get($key, collect());
            $reparadas = $cerradas->get($key, collect());
            $filas[] = [
                'idser' => $key,
                'nombre' => self::LABELS[$key],
                'nuevas' => $nuevas->count(),
                'reparadas' => $reparadas->count(),
                'promedio_horas' => $reparadas->isNotEmpty() ? round($reparadas->avg('duracion_minutos') / 60, 1) : null,
            ];
        }

        return $filas;
    }

    /**
     * Detalle de incidencias (abiertas o cerradas) que tocan el rango dado,
     * mas recientes primero.
     */
    public function incidenciasDetalle(string $desde, string $hasta, int $limit = 100): Collection
    {
        return CuentaIncidencia::where(function ($q) use ($desde, $hasta) {
                $q->whereBetween('inicio', [$desde, $hasta])
                    ->orWhereBetween('fin', [$desde, $hasta]);
            })
            ->orderByDesc('inicio')
            ->limit($limit)
            ->get()
            ->map(function ($i) {
                return [
                    'idcue' => $i->idcue,
                    'servicio' => self::LABELS[$this->bucket($i->servicio_idser)],
                    'inicio' => $i->inicio,
                    'fin' => $i->fin,
                    'duracion_horas' => $i->duracion_minutos !== null ? round($i->duracion_minutos / 60, 1) : null,
                    'estado' => $i->fin ? 'reparada' : 'en curso',
                ];
            });
    }

    /**
     * Linea de tiempo por cuenta para cada uno de los 9 buckets (servicios
     * principales + Otros): un segmento "activa" desde que se creo hasta que
     * vencio (o hoy), "vencida" desde que vencio hasta hoy si no se ha
     * renovado, y "danada" por cada incidencia registrada. Los segmentos
     * "danada" son exactos desde que existe cuenta_incidencias; "activa" es
     * una aproximacion (no distingue renovaciones puntuales dentro del
     * periodo, solo cuando arranco y cuando vencio por ultima vez).
     */
    public function timelinePorServicio(): array
    {
        $hoy = Carbon::now();
        $cuentas = $this->cuentasReales();

        if ($cuentas->isEmpty()) {
            return [];
        }

        $incidenciasPorCuenta = CuentaIncidencia::whereIn('idcue', $cuentas->pluck('idcue'))
            ->get()
            ->groupBy('idcue');

        $porBucket = $cuentas->groupBy(fn ($c) => $this->bucket($c->valor->servicio->idser ?? null));

        $resultado = [];
        foreach (array_merge(self::SERVICIOS_PRINCIPALES, ['OTROS']) as $key) {
            $grupo = $porBucket->get($key, collect())->sortBy('created_at')->values();

            if ($grupo->isEmpty()) {
                $resultado[$key] = ['nombre' => self::LABELS[$key], 'desde' => null, 'hasta' => $hoy->toDateString(), 'cuentas' => []];
                continue;
            }

            $desde = Carbon::parse($grupo->min('created_at'))->startOfDay();

            // Fechas crudas, no porcentajes: la ventana visible (ultimos 2 meses,
            // deslizable) se calcula en el navegador, no aca — asi no hay que
            // recargar la pagina para navegar el historial.
            $filas = $grupo->map(function (Cuenta $cuenta) use ($hoy, $incidenciasPorCuenta) {
                $segmentos = $this->segmentosCuenta($cuenta, $incidenciasPorCuenta->get($cuenta->idcue, collect()), $hoy);

                $segmentosOut = array_map(function (array $s) {
                    return [
                        'tipo' => $s['tipo'],
                        'inicio' => $s['inicio']->toDateString(),
                        'fin' => $s['fin']->toDateString(),
                        'en_curso' => $s['fin_es_hoy'],
                    ];
                }, $segmentos);

                return [
                    'idcue' => $cuenta->idcue,
                    'caida_ahora' => (bool) $cuenta->caidacue,
                    'segmentos' => $segmentosOut,
                ];
            })->values();

            $resultado[$key] = [
                'nombre' => self::LABELS[$key],
                'desde' => $desde->toDateString(),
                'hasta' => $hoy->toDateString(),
                'cuentas' => $filas,
            ];
        }

        return $resultado;
    }

    private function segmentosCuenta(Cuenta $cuenta, Collection $incidencias, Carbon $hoy): array
    {
        $inicio = Carbon::parse($cuenta->created_at)->startOfDay();
        $finVigencia = Carbon::parse($cuenta->fechavencue)->startOfDay();
        $finActiva = $finVigencia->lt($hoy) ? $finVigencia : $hoy;

        $segmentos = [];

        if ($finActiva->gt($inicio)) {
            $segmentos[] = ['tipo' => 'activa', 'inicio' => $inicio, 'fin' => $finActiva, 'fin_es_hoy' => $finActiva->equalTo($hoy->copy()->startOfDay())];
        }

        if ($finVigencia->lt($hoy)) {
            $segmentos[] = ['tipo' => 'vencida', 'inicio' => $finVigencia, 'fin' => $hoy, 'fin_es_hoy' => true];
        }

        foreach ($incidencias as $incidencia) {
            $inicioInc = Carbon::parse($incidencia->inicio)->startOfDay();
            $finInc = $incidencia->fin ? Carbon::parse($incidencia->fin)->startOfDay() : $hoy;
            $segmentos[] = ['tipo' => 'danada', 'inicio' => $inicioInc, 'fin' => $finInc, 'fin_es_hoy' => is_null($incidencia->fin)];
        }

        return $segmentos;
    }
}
