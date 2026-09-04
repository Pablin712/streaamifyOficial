<?php

namespace App\Services;

use App\Models\Meta;
use App\Models\Soporte;
use Carbon\Carbon;

/**
 * Metas de negocio sobre los KPI del dashboard.
 *
 * Traduce una meta ("este mes quiero $5.000 de utilidad") en las cifras que
 * hacen falta para actuar: cuanto llevas, cuanto te falta, a que ritmo diario
 * tendrias que ir el resto del mes y donde vas a cerrar si sigues asi.
 *
 * La semantica es la de un cuadro de mando: el color no dice si el numero es
 * alto o bajo, dice si vas a cumplir.
 */
class MetaService
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /* ───────────────────────── Catalogo ───────────────────────── */

    /**
     * KPI sobre los que se puede fijar una meta.
     *
     * direccion    'subir'  mas es mejor  |  'bajar'  menos es mejor
     * acumulativo  true     se suma a lo largo del periodo (admite ritmo diario)
     *              false    es una foto o un promedio (no se reparte por dias)
     */
    public function catalogo(): array
    {
        $catalogo = [
            'utilidad_mes' => [
                'label'       => 'Utilidad del mes',
                'grupo'       => 'Finanzas',
                'unidad'      => 'dinero',
                'direccion'   => 'subir',
                'acumulativo' => true,
                'icono'       => 'fa-sack-dollar',
                'ayuda'       => 'Ingresos menos costos y gastos operativos.',
            ],
            'ingresos_mes' => [
                'label'       => 'Ingresos del mes',
                'grupo'       => 'Finanzas',
                'unidad'      => 'dinero',
                'direccion'   => 'subir',
                'acumulativo' => true,
                'icono'       => 'fa-money-bill-wave',
                'ayuda'       => 'Facturacion total del periodo.',
            ],
            'ventas_mes' => [
                'label'       => 'Ventas cerradas',
                'grupo'       => 'Finanzas',
                'unidad'      => 'cantidad',
                'direccion'   => 'subir',
                'acumulativo' => true,
                'icono'       => 'fa-cart-shopping',
                'ayuda'       => 'Numero de ventas registradas en el periodo.',
            ],
            'ticket_promedio' => [
                'label'       => 'Ticket promedio',
                'grupo'       => 'Finanzas',
                'unidad'      => 'dinero',
                'direccion'   => 'subir',
                'acumulativo' => false,
                'icono'       => 'fa-receipt',
                'ayuda'       => 'Ingreso medio por venta del periodo.',
            ],
            'costos_mes' => [
                'label'       => 'Costos del mes',
                'grupo'       => 'Finanzas',
                'unidad'      => 'dinero',
                'direccion'   => 'bajar',
                'acumulativo' => true,
                'icono'       => 'fa-file-invoice-dollar',
                'ayuda'       => 'Lo que cuestan las cuentas y perfiles vendidos.',
            ],
            'gastos_mes' => [
                'label'       => 'Gastos operativos',
                'grupo'       => 'Finanzas',
                'unidad'      => 'dinero',
                'direccion'   => 'bajar',
                'acumulativo' => true,
                'icono'       => 'fa-wallet',
                'ayuda'       => 'Gasto del negocio, sin contar retiros personales.',
            ],

            'clientes_nuevos' => [
                'label'       => 'Clientes nuevos',
                'grupo'       => 'Clientes',
                'unidad'      => 'cantidad',
                'direccion'   => 'subir',
                'acumulativo' => true,
                'icono'       => 'fa-user-plus',
                'ayuda'       => 'Altas de clientes durante el periodo.',
            ],
            'clientes_perdidos' => [
                'label'       => 'Clientes perdidos',
                'grupo'       => 'Clientes',
                'unidad'      => 'cantidad',
                'direccion'   => 'bajar',
                'acumulativo' => true,
                'icono'       => 'fa-user-minus',
                'ayuda'       => 'Bajas de clientes durante el periodo. La meta es un techo.',
            ],
            'clientes_activos' => [
                'label'       => 'Clientes activos',
                'grupo'       => 'Clientes',
                'unidad'      => 'cantidad',
                'direccion'   => 'subir',
                'acumulativo' => false,
                'icono'       => 'fa-users',
                'ayuda'       => 'Cartera al cierre del periodo.',
            ],
            'usuarios_activos' => [
                'label'       => 'Usuarios activos',
                'grupo'       => 'Clientes',
                'unidad'      => 'cantidad',
                'direccion'   => 'subir',
                'acumulativo' => false,
                'icono'       => 'fa-user-check',
                'ayuda'       => 'Perfiles en uso al cierre del periodo.',
            ],

            'cuentas_caidas' => [
                'label'       => 'Cuentas caidas',
                'grupo'       => 'Operaciones',
                'unidad'      => 'cantidad',
                'direccion'   => 'bajar',
                'acumulativo' => false,
                'icono'       => 'fa-triangle-exclamation',
                'ayuda'       => 'Cuentas en estado critico al cierre del periodo.',
            ],
            'pagos_pendientes' => [
                'label'       => 'Pagos pendientes',
                'grupo'       => 'Operaciones',
                'unidad'      => 'cantidad',
                'direccion'   => 'bajar',
                'acumulativo' => false,
                'icono'       => 'fa-hourglass-half',
                'ayuda'       => 'Cobros sin conciliar al cierre del periodo.',
            ],

            'soporte_horas' => [
                'label'       => 'Tiempo medio de atencion',
                'grupo'       => 'Soporte',
                'unidad'      => 'horas',
                'direccion'   => 'bajar',
                'acumulativo' => false,
                'icono'       => 'fa-stopwatch',
                'ayuda'       => 'Horas entre que se abre un soporte y se marca atendido.',
            ],
            'soporte_pendientes' => [
                'label'       => 'Soportes sin atender',
                'grupo'       => 'Soporte',
                'unidad'      => 'cantidad',
                'direccion'   => 'bajar',
                'acumulativo' => false,
                'icono'       => 'fa-life-ring',
                'ayuda'       => 'Tickets abiertos ahora mismo.',
            ],
            'soporte_resueltos' => [
                'label'       => 'Soportes resueltos',
                'grupo'       => 'Soporte',
                'unidad'      => 'cantidad',
                'direccion'   => 'subir',
                'acumulativo' => true,
                'icono'       => 'fa-circle-check',
                'ayuda'       => 'Tickets cerrados durante el periodo.',
            ],
        ];

        // Rotacion por servicio: se genera del mismo catalogo de servicios que
        // usan los reportes, para que ambos cuadros hablen de lo mismo.
        foreach ($this->dashboardService->serviciosConocidos() as $servicio) {
            $catalogo['perdidos_servicio:' . $servicio] = [
                'label'       => 'Bajas ' . $servicio,
                'grupo'       => 'Por servicio',
                'unidad'      => 'cantidad',
                'direccion'   => 'bajar',
                'acumulativo' => true,
                'icono'       => 'fa-arrow-trend-down',
                'ayuda'       => 'Clientes que dejaron ' . $servicio . ' en el periodo. La meta es un techo.',
                'aviso_parcial' => 'La rotacion por servicio solo cuadra con el mes cerrado: a mitad de mes, un cliente que todavia no ha renovado cuenta como baja y vuelve a contar como retenido en cuanto renueva.',
                'servicio'    => $servicio,
            ];
            $catalogo['nuevos_servicio:' . $servicio] = [
                'label'       => 'Altas ' . $servicio,
                'grupo'       => 'Por servicio',
                'unidad'      => 'cantidad',
                'direccion'   => 'subir',
                'acumulativo' => true,
                'icono'       => 'fa-arrow-trend-up',
                'ayuda'       => 'Clientes que entraron a ' . $servicio . ' en el periodo.',
                'aviso_parcial' => 'La rotacion por servicio solo cuadra con el mes cerrado: a mitad de mes, un cliente que todavia no ha renovado cuenta como baja y vuelve a contar como retenido en cuanto renueva.',
                'servicio'    => $servicio,
            ];
            $catalogo['retencion_servicio:' . $servicio] = [
                'label'       => 'Retencion ' . $servicio,
                'grupo'       => 'Por servicio',
                'unidad'      => 'porcentaje',
                'direccion'   => 'subir',
                'acumulativo' => false,
                'icono'       => 'fa-shield-halved',
                'ayuda'       => 'Porcentaje de clientes de ' . $servicio . ' que siguen respecto al mes anterior.',
                'aviso_parcial' => 'La rotacion por servicio solo cuadra con el mes cerrado: a mitad de mes, un cliente que todavia no ha renovado cuenta como baja y vuelve a contar como retenido en cuanto renueva.',
                'servicio'    => $servicio,
            ];
        }

        return $catalogo;
    }

    public function definicion(string $kpi): ?array
    {
        return $this->catalogo()[$kpi] ?? null;
    }

    /** Catalogo agrupado, para pintar el selector del formulario. */
    public function catalogoPorGrupo(): array
    {
        $grupos = [];
        foreach ($this->catalogo() as $codigo => $def) {
            $grupos[$def['grupo']][$codigo] = $def;
        }
        return $grupos;
    }

    /* ───────────────────────── Valores reales ───────────────────────── */

    /**
     * Valor actual de cada KPI pedido. Solo calcula lo que se necesita: la
     * rotacion por servicio recorre detalles_venta y se salta si nadie la pidio.
     *
     * @param  array<int,string>  $kpis  codigos del catalogo
     * @return array<string,float>
     */
    public function valoresActuales(array $kpis, int $mes, int $anio): array
    {
        $kpis    = array_unique($kpis);
        $valores = [];

        $delDashboard = [
            'utilidad_mes', 'ingresos_mes', 'ventas_mes', 'ticket_promedio',
            'costos_mes', 'gastos_mes', 'clientes_nuevos', 'clientes_perdidos',
            'clientes_activos', 'usuarios_activos', 'cuentas_caidas', 'pagos_pendientes',
        ];

        if (array_intersect($kpis, $delDashboard)) {
            $d = $this->dashboardService->obtenerDatosDashboardMensuales($mes, $anio);

            $valores['utilidad_mes']      = (float) $d['balance'];
            $valores['ingresos_mes']      = (float) $d['ingresos_mes'];
            $valores['ventas_mes']        = (float) $d['ventas_mes'];
            $valores['ticket_promedio']   = (float) $d['ticket_promedio'];
            $valores['costos_mes']        = (float) $d['costos_mes'];
            $valores['gastos_mes']        = (float) $d['gastos_mes'];
            $valores['clientes_nuevos']   = (float) $d['clientes_nuevos'];
            $valores['clientes_perdidos'] = (float) $d['clientes_perdidos'];
            $valores['clientes_activos']  = (float) $d['clientes_activos'];
            $valores['usuarios_activos']  = (float) $d['total_usuarios_activos'];
            $valores['cuentas_caidas']    = (float) $d['cuentas_caidas'];
            $valores['pagos_pendientes']  = (float) $d['pagos_pendientes'];
        }

        if (array_intersect($kpis, ['soporte_horas', 'soporte_pendientes', 'soporte_resueltos'])) {
            $valores += $this->metricasSoporte($mes, $anio);
        }

        $porServicio = array_filter($kpis, fn ($k) => str_contains($k, '_servicio:'));

        if ($porServicio) {
            $rotacion = collect($this->dashboardService->obtenerRotacionPorServicio($mes, $anio))
                ->keyBy('servicio');

            foreach ($porServicio as $codigo) {
                [$tipo, $servicio] = explode(':', $codigo, 2);
                $fila = $rotacion->get($servicio);

                $valores[$codigo] = match ($tipo) {
                    'perdidos_servicio'  => (float) ($fila['perdidos'] ?? 0),
                    'nuevos_servicio'    => (float) ($fila['nuevos'] ?? 0),
                    'retencion_servicio' => (float) ($fila['retencion'] ?? 0),
                    default              => 0.0,
                };
            }
        }

        return $valores;
    }

    /** Tiempo medio de atencion, cola abierta y cerrados del periodo. */
    private function metricasSoporte(int $mes, int $anio): array
    {
        $inicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fin    = Carbon::create($anio, $mes, 1)->endOfMonth();

        $atendidos = Soporte::where('estado', 'atendido')
            ->whereBetween('updated_at', [$inicio, $fin])
            ->selectRaw('COUNT(*) AS total, AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) AS minutos')
            ->first();

        return [
            'soporte_horas'      => round(((float) ($atendidos->minutos ?? 0)) / 60, 2),
            'soporte_resueltos'  => (float) ($atendidos->total ?? 0),
            'soporte_pendientes' => (float) Soporte::where('estado', 'pendiente')->count(),
        ];
    }

    /* ───────────────────────── Evaluacion ───────────────────────── */

    /**
     * Traduce meta + valor real en el estado del semaforo y en las cifras
     * accionables: cuanto falta, a que ritmo y donde se cierra el periodo.
     */
    public function evaluar(Meta $meta, array $def, float $actual, ?Carbon $hoy = null): array
    {
        $hoy = $hoy ?: Carbon::now();

        [$inicio, $fin] = $this->limitesPeriodo($meta, $hoy);

        // Carbon devuelve dias fraccionarios: hay que truncar antes de contar,
        // o el numero de dias arrastra decimales por toda la aritmetica.
        $diasTotales = (int) $inicio->diffInDays($fin) + 1;

        if ($hoy->lt($inicio)) {
            $transcurridos = 0;                       // periodo que aun no empieza
        } elseif ($hoy->gt($fin)) {
            $transcurridos = $diasTotales;            // periodo cerrado
        } else {
            $transcurridos = (int) $inicio->diffInDays($hoy) + 1;
        }

        $cerrado   = $hoy->gt($fin);
        $restantes = max(0, $diasTotales - $transcurridos);

        $objetivo = (float) $meta->objetivo;
        $umbral   = max(1, (int) $meta->umbral_atencion) / 100;
        $subir    = $def['direccion'] === 'subir';
        $acumula  = (bool) $def['acumulativo'];

        // Avance: en "mas es mejor" es cuanto llevas del objetivo; en "menos es
        // mejor" es cuanto llevas consumido del presupuesto.
        $avance = $objetivo != 0.0 ? ($actual / $objetivo) * 100 : 0.0;

        $esperado = $acumula && $diasTotales > 0
            ? $objetivo * ($transcurridos / $diasTotales)
            : $objetivo;

        $proyeccion = $acumula && $transcurridos > 0
            ? ($actual / $transcurridos) * $diasTotales
            : $actual;

        $ritmoActual = $acumula && $transcurridos > 0 ? $actual / $transcurridos : null;

        // Lo que hay que hacer cada dia con los dias que quedan: en "subir" es
        // lo que falta por conseguir, en "bajar" el margen que aun te queda.
        $ritmoNecesario = $acumula && $restantes > 0
            ? max(0, $objetivo - $actual) / $restantes
            : null;

        $estado = $this->estado($subir, $acumula, $cerrado, $actual, $objetivo, $proyeccion, $umbral);

        return [
            'meta'            => $meta,
            'kpi'             => $meta->kpi,
            'definicion'      => $def,
            'objetivo'        => $objetivo,
            'actual'          => $actual,
            'avance'          => $avance,
            'avance_barra'    => max(0, min(100, $avance)),
            'esperado'        => $esperado,
            'marca_ritmo'     => $acumula && $diasTotales > 0
                                    ? max(0, min(100, ($transcurridos / $diasTotales) * 100))
                                    : null,
            'proyeccion'      => $proyeccion,
            'ritmo_actual'    => $ritmoActual,
            'ritmo_necesario' => $ritmoNecesario,
            'falta'           => max(0, $subir ? $objetivo - $actual : $objetivo - $actual),
            'exceso'          => max(0, $actual - $objetivo),
            'dias_totales'    => $diasTotales,
            'dias_pasados'    => $transcurridos,
            'dias_restantes'  => $restantes,
            'en_curso'        => $hoy->between($inicio, $fin),
            'cerrado'         => $cerrado,
            'acumulativo'     => $acumula,
            'estado'          => $estado,
            'color'           => $this->color($estado),
            'etiqueta'        => $this->etiqueta($estado, $acumula),
            'mensaje'         => $this->mensaje($estado, $def, $subir, $acumula, $actual, $objetivo, $ritmoNecesario, $restantes, $proyeccion),
            'inicio'          => $inicio,
            'fin'             => $fin,

            // Advertencia solo mientras el periodo no ha cerrado: hay metricas
            // que a mitad de periodo dan una lectura enganosa.
            'aviso'           => !$cerrado ? ($def['aviso_parcial'] ?? null) : null,

            // Ya formateados, para que la vista no tenga que decidir nada.
            'f_actual'        => $this->formatear($actual, $def['unidad']),
            'f_objetivo'      => $this->formatear($objetivo, $def['unidad']),
            'f_proyeccion'    => $this->formatear($proyeccion, $def['unidad']),
            'f_esperado'      => $this->formatear($esperado, $def['unidad']),
            'f_ritmo'         => $ritmoNecesario !== null ? $this->formatearRitmo($ritmoNecesario, $def['unidad']) : null,
            'f_ritmo_actual'  => $ritmoActual !== null ? $this->formatearRitmo($ritmoActual, $def['unidad']) : null,
        ];
    }

    private function limitesPeriodo(Meta $meta, Carbon $hoy): array
    {
        if ($meta->periodo === 'anual') {
            $base = Carbon::create($meta->anio ?: $hoy->year, 1, 1);
            return [$base->copy()->startOfYear(), $base->copy()->endOfYear()];
        }

        $base = Carbon::create($meta->anio ?: $hoy->year, $meta->mes ?: $hoy->month, 1);
        return [$base->copy()->startOfMonth(), $base->copy()->endOfMonth()];
    }

    private function estado(bool $subir, bool $acumula, bool $cerrado, float $actual, float $objetivo, float $proyeccion, float $umbral): string
    {
        $cumple = $subir ? $actual >= $objetivo : $actual <= $objetivo;

        if ($cerrado) {
            return $cumple ? 'logrado' : 'fallado';
        }

        if ($subir && $cumple) {
            return 'logrado';
        }

        // "Excedido" solo tiene sentido en un acumulado: un techo de bajas que
        // ya te pasaste no se puede deshacer. Un promedio (tiempo de atencion)
        // si puede volver a bajar, asi que ese se juzga con la tolerancia.
        if (!$subir && $acumula && $actual > $objetivo) {
            return 'excedido';
        }

        if (!$acumula) {
            $tolerancia = $subir ? $objetivo * $umbral : $objetivo * (2 - $umbral);
            $dentro     = $subir ? $actual >= $tolerancia : $actual <= $tolerancia;

            return $cumple ? 'logrado' : ($dentro ? 'atencion' : 'riesgo');
        }

        if ($subir) {
            if ($proyeccion >= $objetivo) {
                return 'en_ritmo';
            }
            if ($proyeccion >= $objetivo * $umbral) {
                return 'atencion';
            }
            return 'riesgo';
        }

        if ($proyeccion <= $objetivo) {
            return 'en_ritmo';
        }
        if ($proyeccion <= $objetivo * (2 - $umbral)) {
            return 'atencion';
        }
        return 'riesgo';
    }

    private function color(string $estado): string
    {
        return match ($estado) {
            'logrado', 'en_ritmo' => 'good',
            'atencion'            => 'warning',
            default               => 'critical',
        };
    }

    /**
     * "Ritmo" solo se puede decir de un acumulado. En un promedio o una foto
     * no hay ritmo que llevar, se esta dentro o fuera del objetivo.
     */
    private function etiqueta(string $estado, bool $acumula): string
    {
        return match ($estado) {
            'logrado'  => 'Meta alcanzada',
            'en_ritmo' => $acumula ? 'En ritmo' : 'En objetivo',
            'atencion' => 'Ajustado',
            'riesgo'   => $acumula ? 'Fuera de ritmo' : 'Fuera de objetivo',
            'excedido' => 'Limite superado',
            'fallado'  => 'No alcanzada',
            default    => $estado,
        };
    }

    /** La frase accionable de la tarjeta: que hay que hacer para llegar. */
    private function mensaje(string $estado, array $def, bool $subir, bool $acumula, float $actual, float $objetivo, ?float $ritmo, int $restantes, float $proyeccion): string
    {
        $u = fn (float $v) => $this->formatear($v, $def['unidad']);

        if ($estado === 'logrado') {
            return $subir
                ? 'Objetivo cubierto, con ' . $u($actual - $objetivo) . ' de margen.'
                : 'Por debajo del limite, con ' . $u($objetivo - $actual) . ' de holgura.';
        }

        if ($estado === 'fallado') {
            return $subir
                ? 'Cerro ' . $u($objetivo - $actual) . ' por debajo del objetivo.'
                : 'Cerro ' . $u($actual - $objetivo) . ' por encima del limite.';
        }

        // Una foto o un promedio no tiene ritmo: se esta dentro o fuera.
        if (!$acumula) {
            return $subir
                ? 'Faltan ' . $u($objetivo - $actual) . ' para el objetivo.'
                : 'Hay que bajar ' . $u($actual - $objetivo) . ' para entrar en objetivo.';
        }

        if ($estado === 'excedido') {
            return 'Ya vas ' . $u($actual - $objetivo) . ' por encima del limite del periodo.';
        }

        if ($restantes <= 0) {
            return 'Ultimo dia del periodo.';
        }

        $porDia = $this->formatearRitmo($ritmo ?? 0, $def['unidad']);
        $dias   = $restantes === 1 ? '1 dia' : $restantes . ' dias';

        $cierre = 'Al ritmo actual cerrarias en ' . $u($proyeccion) . '.';
        $accion = $subir
            ? 'Necesitas ' . $porDia . ' por dia durante ' . $dias . '.'
            : 'Te quedan ' . $porDia . ' por dia de margen durante ' . $dias . '.';

        return $estado === 'en_ritmo'
            ? 'Al ritmo actual cierras en ' . $u($proyeccion) . '. ' . $accion
            : $accion . ' ' . $cierre;
    }

    /**
     * Un ritmo diario pequeno redondeado a entero pierde el sentido: "1 baja
     * al dia" no distingue entre 1,2 y 1,9. Con cifras pequenas se guarda un
     * decimal; a partir de 10 el entero ya es suficientemente informativo.
     */
    public function formatearRitmo(float $valor, string $unidad): string
    {
        if (in_array($unidad, ['cantidad', 'porcentaje'], true) && abs($valor) < 10) {
            return number_format($valor, 1, ',', '.') . ($unidad === 'porcentaje' ? '%' : '');
        }

        return $this->formatear($valor, $unidad);
    }

    public function formatear(float $valor, string $unidad): string
    {
        return match ($unidad) {
            'dinero'     => '$' . number_format($valor, 2, ',', '.'),
            'horas'      => $valor < 1
                                ? round($valor * 60) . ' min'
                                : number_format($valor, 1, ',', '.') . ' h',
            'porcentaje' => number_format($valor, 1, ',', '.') . '%',
            default      => number_format($valor, 0, ',', '.'),
        };
    }

    /* ───────────────────────── Tablero ───────────────────────── */

    /**
     * Metas vigentes de un periodo, evaluadas y ordenadas por urgencia:
     * primero lo que esta en riesgo, al final lo ya conseguido.
     */
    public function tablero(int $mes, int $anio, ?Carbon $hoy = null): array
    {
        $metas = Meta::vigentes($mes, $anio)->get();

        if ($metas->isEmpty()) {
            return [];
        }

        $catalogo = $this->catalogo();

        // Descarta metas de KPI que ya no existan en el catalogo.
        $metas = $metas->filter(fn ($m) => isset($catalogo[$m->kpi]));

        if ($metas->isEmpty()) {
            return [];
        }

        $valores = $this->valoresActuales($metas->pluck('kpi')->all(), $mes, $anio);

        $orden = [
            'excedido' => 0, 'riesgo'   => 1, 'fallado' => 2,
            'atencion' => 3, 'en_ritmo' => 4, 'logrado' => 5,
        ];

        return $metas
            ->map(fn ($m) => $this->evaluar($m, $catalogo[$m->kpi], (float) ($valores[$m->kpi] ?? 0), $hoy))
            ->sortBy(fn ($e) => $orden[$e['estado']] ?? 9)
            ->values()
            ->all();
    }

    /** Resumen de una linea para la cabecera del dashboard. */
    public function resumen(array $tablero): array
    {
        $cuenta = fn (array $estados) => count(array_filter(
            $tablero,
            fn ($e) => in_array($e['estado'], $estados, true)
        ));

        return [
            'total'    => count($tablero),
            'bien'     => $cuenta(['logrado', 'en_ritmo']),
            'atencion' => $cuenta(['atencion']),
            'mal'      => $cuenta(['riesgo', 'excedido', 'fallado']),
        ];
    }
}
