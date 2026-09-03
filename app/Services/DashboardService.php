<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\DailyStatistic;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Services\CuentaService;
use App\Models\Costo;
use App\Models\Gasto;
use App\Models\ViewClientesUsuarios;
use App\Models\ViewUsuarioActivo;
use App\Models\Tarea;
use App\Models\Historial;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    protected $cuentaService;

    public function __construct(CuentaService $cuentaService)
    {
        $this->cuentaService = $cuentaService;
    }
    public function obtenerDatosDashboard()
    {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $usuarios = ViewUsuarioActivo::all();
        $cuentas = $this->cuentaService->obtenerCuentas();
        $usuarios_acobrar = $this->cuentaService->contarUsuariosACobrar($usuarios);
        $espacios = $this->cuentaService->calcularEspaciosTotales();

        $ingresos_mes = Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->sum('totalpagoven');
        $costos_mes = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->sum('montocos');
        // Gastos operativos: excluyen los tipos marcados como "excluir_de_ganancia" (ej: pago de personal / retiro del dueño)
        $gastos_mes = Gasto::operativos()->whereMonth('fechagas', $month)->whereYear('fechagas', $year)->sum('montogas');
        $gastos_personal_mes = Gasto::personal()->whereMonth('fechagas', $month)->whereYear('fechagas', $year)->sum('montogas');
        $ingresos = $ingresos_mes > 0 ? $ingresos_mes : 1; // Evita división por cero
        $costos_pct = ($costos_mes / $ingresos) * 100;
        $gastos_pct = ($gastos_mes / $ingresos) * 100;
        $balance = $ingresos_mes - $costos_mes - $gastos_mes;
        $balance_pct = ($balance / $ingresos) * 100;
        return [
            'total_usuarios_activos' => ViewUsuarioActivo::count(),
            'ingresos_mes' => $ingresos_mes,
            'ingresos_ano' => Venta::whereYear('fechaven', $year)->sum('totalpagoven'),
            'ingresos' => $ingresos,
            'costos_mes' => $costos_mes,
            'costos_pct' => $costos_pct,
            'gastos_mes' => $gastos_mes,
            'gastos_pct' => $gastos_pct,
            'gastos_personal_mes' => $gastos_personal_mes,
            'balance' => $balance,
            'balance_pct' => $balance_pct,
            'clientes_activos' => ViewClientesUsuarios::count(),
            'cuentas_caidas' => $this->cuentaService->contarCuentasCaidas($cuentas),
            'num_cuentas' => $this->cuentaService->contarCuentasActivas($cuentas),
            'ventas_mes' => Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->count(),
            'ventas_ano' => Venta::whereYear('fechaven', $year)->count(),
            'usuarios_acobrar' => $usuarios_acobrar,
            'promedio_pagos_mes' => Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->avg('totalpagoven'),
            'cliente_mas_facturado' => ViewClientesUsuarios::orderByDesc('facturado')->select('nombre_cliente', 'facturado')->first(),
            'cuentas' => $cuentas,
            'espacios' => $espacios,
        ] + $this->calcularChurnDiario(Carbon::today()->toDateString());
    }

    /**
     * Calcula el churn (rotación de clientes) de una fecha puntual a partir del historial
     * de "Cuenta-Quitada". clientes_perdidos cuenta, de los clientes con una remoción ese día,
     * cuántos quedaron sin usuarios activos actualmente (usuarios=0).
     */
    private function calcularChurnDiario(string $date): array
    {
        $usuariosRemovidos = Historial::where('accion', 'Cuenta-Quitada')
            ->whereDate('created_at', $date)
            ->count();

        $clientesConRemocion = Historial::where('accion', 'Cuenta-Quitada')
            ->whereDate('created_at', $date)
            ->whereNotNull('idcli')
            ->distinct()
            ->pluck('idcli');

        $clientesPerdidos = 0;
        if ($clientesConRemocion->isNotEmpty()) {
            $clientesConUsuariosActivos = ViewUsuarioActivo::whereIn('idcli', $clientesConRemocion)
                ->distinct()
                ->pluck('idcli');

            $clientesPerdidos = $clientesConRemocion->diff($clientesConUsuariosActivos)->count();
        }

        return [
            'usuarios_removidos' => $usuariosRemovidos,
            'clientes_perdidos' => $clientesPerdidos,
        ];
    }

    /**
     * Resumen mensual completo, con los datos del MES SOLICITADO.
     *
     * La version anterior tenia dos fallos de fondo:
     *
     *  1. Para las metricas de estado (cuentas, usuarios, clientes) hacia
     *     pluck()->unique()->count(), que cuenta VALORES DISTINTOS a lo largo
     *     del mes, no la magnitud. Por eso el reporte de junio decia
     *     "1 cuenta activa" y "1 usuario activo" cuando habia 299 y 1854.
     *  2. Para `espacios` y `usuarios_a_cobrar` devolvia la coleccion entera,
     *     y Blade la imprimia como "[212]" y "[188]".
     *
     * daily_statistics es una foto diaria, asi que:
     *   - lo que es un ESTADO (cuentas, usuarios, clientes, espacios) se toma
     *     del ultimo dia del mes;
     *   - lo que es un FLUJO (ingresos, costos, ventas, clientes nuevos y
     *     perdidos) se suma a lo largo del mes.
     *
     * Devuelve ademas la comparacion con el mes anterior y las series que
     * necesitan los graficos del PDF, para que se generen del periodo pedido
     * y no del momento en que se imprime.
     */
    public function obtenerDatosDashboardMensuales($month, $year)
    {
        $inicio = Carbon::create($year, $month, 1)->startOfMonth();
        $fin    = Carbon::create($year, $month, 1)->endOfMonth();

        $dias = DailyStatistic::whereBetween('date', [$inicio, $fin])
            ->orderBy('date')
            ->get();

        $ultimo = $dias->last();          // foto de cierre del mes

        // --- Flujos del mes ---
        $ingresos_mes        = (float) $dias->sum('daily_revenue');
        $costos_mes          = (float) $dias->sum('daily_cost');
        $gastos_mes          = (float) $dias->sum('daily_bill');
        $gastos_personal_mes = (float) $dias->sum('daily_bill_personal');
        $ventas_mes          = (int) $dias->sum('daily_sales');
        $clientes_nuevos     = (int) $dias->sum('new_customers');
        $clientes_perdidos   = (int) $dias->sum('clientes_perdidos');
        $usuarios_removidos  = (int) $dias->sum('usuarios_removidos');

        $ingresos    = $ingresos_mes > 0 ? $ingresos_mes : 1; // evita division por cero
        $balance     = $ingresos_mes - $costos_mes - $gastos_mes;
        $costos_pct  = ($costos_mes / $ingresos) * 100;
        $gastos_pct  = ($gastos_mes / $ingresos) * 100;
        $balance_pct = ($balance / $ingresos) * 100;

        // --- Estado al cierre del mes ---
        $num_cuentas            = (int) ($ultimo->accounts ?? 0);
        $total_usuarios_activos = (int) ($ultimo->active_users ?? 0);
        $clientes_activos       = (int) ($ultimo->total_customers ?? 0);
        $cuentas_caidas         = (int) ($ultimo->danger_accounts ?? 0);
        $espacios               = (int) ($ultimo->espacios ?? 0);
        $usuarios_acobrar       = (int) ($ultimo->usuarios_a_cobrar ?? 0);
        $clientes_afectados     = (int) ($ultimo->affected_customers ?? 0);
        $pagos_pendientes       = (int) ($ultimo->pending_payments ?? 0);

        // --- Comparacion con el mes anterior ---
        $antInicio = $inicio->copy()->subMonth()->startOfMonth();
        $antFin    = $antInicio->copy()->endOfMonth();
        $diasAnt   = DailyStatistic::whereBetween('date', [$antInicio, $antFin])->orderBy('date')->get();
        $ultimoAnt = $diasAnt->last();

        $ingresos_ant = (float) $diasAnt->sum('daily_revenue');
        $balance_ant  = $ingresos_ant - (float) $diasAnt->sum('daily_cost') - (float) $diasAnt->sum('daily_bill');

        $variacion = function ($actual, $anterior) {
            if (empty($anterior)) {
                return null; // sin mes anterior no hay porcentaje que mostrar
            }
            return (($actual - $anterior) / abs($anterior)) * 100;
        };

        $comparativa = [
            'ingresos'     => $variacion($ingresos_mes, $ingresos_ant),
            'utilidad'     => $variacion($balance, $balance_ant),
            'ventas'       => $variacion($ventas_mes, (int) $diasAnt->sum('daily_sales')),
            'clientes'     => $variacion($clientes_activos, (int) ($ultimoAnt->total_customers ?? 0)),
            'usuarios'     => $variacion($total_usuarios_activos, (int) ($ultimoAnt->active_users ?? 0)),
            'mes_anterior' => $antInicio->locale('es')->translatedFormat('F'),
        ];

        // --- Series para los graficos del PDF (del periodo pedido) ---
        $serie_diaria = $dias->map(function ($d) {
            $ing = (float) $d->daily_revenue;
            return [
                'dia'      => Carbon::parse($d->date)->day,
                'ingresos' => $ing,
                'costos'   => (float) $d->daily_cost,
                'gastos'   => (float) $d->daily_bill,
                'utilidad' => $ing - (float) $d->daily_cost - (float) $d->daily_bill,
            ];
        })->values()->all();

        // Seis meses hasta el mes solicitado (no hasta hoy).
        $serie_meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $m  = $inicio->copy()->subMonths($i);
            $ds = DailyStatistic::whereBetween('date', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])->get();
            $ing = (float) $ds->sum('daily_revenue');
            $serie_meses[] = [
                'etiqueta' => ucfirst($m->locale('es')->translatedFormat('M')),
                'ingresos' => $ing,
                'utilidad' => $ing - (float) $ds->sum('daily_cost') - (float) $ds->sum('daily_bill'),
            ];
        }

        $ingresos_ano = (float) DailyStatistic::whereBetween('date', [
            Carbon::create($year, 1, 1)->startOfYear(),
            Carbon::create($year, 12, 31)->endOfYear(),
        ])->sum('daily_revenue');

        $ventas_ano = (int) DailyStatistic::whereBetween('date', [
            Carbon::create($year, 1, 1)->startOfYear(),
            Carbon::create($year, 12, 31)->endOfYear(),
        ])->sum('daily_sales');

        // Cliente que mas facturo dentro del mes: el valor mas repetido.
        $cliente_mas_facturado = $dias->pluck('cliente_mas_facturado')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        return [
            'total_usuarios_activos' => $total_usuarios_activos,
            'ingresos_mes'           => $ingresos_mes,
            'ingresos_ano'           => $ingresos_ano,
            'ingresos'               => $ingresos,
            'costos_mes'             => $costos_mes,
            'costos_pct'             => $costos_pct,
            'gastos_mes'             => $gastos_mes,
            'gastos_pct'             => $gastos_pct,
            'gastos_personal_mes'    => $gastos_personal_mes,
            'balance'                => $balance,
            'balance_pct'            => $balance_pct,
            'clientes_activos'       => $clientes_activos,
            'cuentas_caidas'         => $cuentas_caidas,
            'num_cuentas'            => $num_cuentas,
            'ventas_mes'             => $ventas_mes,
            'ventas_ano'             => $ventas_ano,
            'usuarios_acobrar'       => $usuarios_acobrar,
            'promedio_pagos_mes'     => $dias->count() ? $ingresos_mes / $dias->count() : 0,
            'cliente_mas_facturado'  => $cliente_mas_facturado,
            'espacios'               => $espacios,

            // Nuevos indicadores del periodo
            'clientes_nuevos'        => $clientes_nuevos,
            'clientes_perdidos'      => $clientes_perdidos,
            'usuarios_removidos'     => $usuarios_removidos,
            'clientes_afectados'     => $clientes_afectados,
            'pagos_pendientes'       => $pagos_pendientes,
            'ticket_promedio'        => $ventas_mes ? $ingresos_mes / $ventas_mes : 0,
            'ingreso_por_cliente'    => $clientes_activos ? $ingresos_mes / $clientes_activos : 0,
            'dias_con_datos'         => $dias->count(),

            'comparativa'            => $comparativa,
            'serie_diaria'           => $serie_diaria,
            'serie_meses'            => $serie_meses,
        ];
    }


    /**
     * Palabra clave -> nombre de servicio. Se usa tanto para el resumen de
     * resultados como para la rotacion de clientes, para que ambos cuadros
     * hablen exactamente de los mismos servicios.
     */
    private function reglasDeServicio(): array
    {
        return [
            'netflix'   => 'Netflix',
            'disney'    => 'Disney+',
            'prime'     => 'Prime Video',
            'paramount' => 'Paramount+',
            'spotify'   => 'Spotify',
            'crunchy'   => 'Crunchyroll',
            'magis'     => 'Magis TV',
            'flujo'     => 'Magis TV',
            'vix'       => 'Vix',
            'canva'     => 'Canva',
            'max'       => 'HBO Max',
            'hbo'       => 'HBO Max',
        ];
    }

    /** Primer candidato con valor decide el servicio; si ninguno encaja, "Otros". */
    private function clasificarServicio(?string ...$candidatos): string
    {
        foreach ($candidatos as $texto) {
            if (empty($texto)) {
                continue;
            }
            foreach ($this->reglasDeServicio() as $clave => $nombre) {
                if (stripos($texto, $clave) !== false) {
                    return $nombre;
                }
            }
        }
        return 'Otros';
    }

    /**
     * Clientes distintos que compraron cada servicio en un mes.
     *
     * Devuelve ['Netflix' => [idcli => true, ...], ...]
     */
    private function clientesPorServicioEnMes(int $month, int $year): array
    {
        $inicio = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $fin    = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $mapa = [];

        /*
         * Activo = la suscripcion CUBRE el mes, no que se haya comprado dentro
         * de el. `fechavendet` es la fecha de vencimiento de cada linea y de
         * media dura 41 dias, con casos de mas de un ano: contar solo las
         * compras del mes marcaba como perdido a quien habia pagado varios
         * meses por adelantado y hundia la retencion artificialmente.
         *
         * Si la linea no tiene vencimiento se le supone un mes desde la venta,
         * que es la duracion habitual.
         */
        DetalleVenta::query()
            ->join('ventas', 'ventas.idven', '=', 'detalles_venta.idven')
            ->where('ventas.fechaven', '<=', $fin)
            ->whereRaw(
                'COALESCE(detalles_venta.fechavendet, DATE_ADD(ventas.fechaven, INTERVAL 1 MONTH)) >= ?',
                [$inicio]
            )
            ->whereNotNull('ventas.idcli')
            ->select([
                'ventas.idcli',
                'detalles_venta.idper',
                'detalles_venta.idper_snapshot',
                'detalles_venta.servicio_snapshot',
            ])
            ->chunk(2000, function ($filas) use (&$mapa) {
                foreach ($filas as $f) {
                    $s = $this->clasificarServicio($f->servicio_snapshot, $f->idper, $f->idper_snapshot);
                    $mapa[$s][$f->idcli] = true;
                }
            });

        return $mapa;
    }

    /**
     * Rotacion de clientes POR SERVICIO en el mes pedido.
     *
     * La tabla daily_statistics solo guarda totales del negocio
     * (clientes_perdidos, new_customers): no dice de que servicio se fue cada
     * cliente, que es justo lo que hace falta para decidir donde actuar.
     *
     * Se deduce comparando quien compro cada servicio este mes y el anterior,
     * que es la definicion habitual en un negocio de suscripcion mensual:
     *
     *   activo en S el mes M  =  el cliente compro S dentro de M
     *   nuevo    =  activo en M y no en M-1
     *   perdido  =  activo en M-1 y no en M
     *   retenido =  activo en los dos
     *
     * OJO: estas cifras no tienen por que cuadrar con los totales de
     * daily_statistics. Aquellos cuentan clientes del negocio entero; estos
     * cuentan altas y bajas servicio por servicio, y un mismo cliente puede
     * darse de baja en uno y seguir en otro. Sirven para comparar servicios
     * entre si, que es para lo que se piden.
     */
    public function obtenerRotacionPorServicio($month, $year): array
    {
        $ahora  = $this->clientesPorServicioEnMes((int) $month, (int) $year);

        $ant    = Carbon::create($year, $month, 1)->subMonth();
        $antes  = $this->clientesPorServicioEnMes((int) $ant->month, (int) $ant->year);

        $servicios = array_unique(array_merge(array_keys($ahora), array_keys($antes)));

        $filas = [];
        foreach ($servicios as $s) {
            $hoy  = $ahora[$s] ?? [];
            $prev = $antes[$s] ?? [];

            $nuevos   = count(array_diff_key($hoy, $prev));
            $perdidos = count(array_diff_key($prev, $hoy));
            $retenidos = count(array_intersect_key($hoy, $prev));

            $filas[] = [
                'servicio'   => $s,
                'activos'    => count($hoy),
                'previos'    => count($prev),
                'nuevos'     => $nuevos,
                'perdidos'   => $perdidos,
                'retenidos'  => $retenidos,
                'neto'       => $nuevos - $perdidos,
                // Retencion: de los que estaban el mes pasado, cuantos siguen.
                'retencion'  => count($prev) > 0 ? ($retenidos / count($prev)) * 100 : null,
                // Fuga: cuantos de los que estaban se fueron.
                'fuga'       => count($prev) > 0 ? ($perdidos / count($prev)) * 100 : null,
            ];
        }

        // Los que mas clientes pierden, primero: es donde hay que mirar.
        usort($filas, fn($a, $b) => $b['perdidos'] <=> $a['perdidos']);

        return $filas;
    }

    /**
     * Resultados por servicio DEL MES PEDIDO.
     *
     * Dos problemas del calculo anterior:
     *
     *  1. Los metodos getNetflix(), getDisney()... sacan las cuentas y los
     *     usuarios del estado ACTUAL del negocio. En el reporte de junio salian
     *     los 463 usuarios de Netflix de hoy junto a los ingresos de junio.
     *
     *  2. Clasificar por el prefijo de `idper` se dejaba fuera mas de la mitad
     *     del dinero: las ventas nuevas guardan `idper` en nulo y ponen el
     *     nombre del servicio en `servicio_snapshot`. En junio de 2026 eran 953
     *     de 1.753 detalles, 3.455 USD que no aparecian en ninguna fila.
     *
     * Aqui se clasifica por palabra clave sobre el primer campo que tenga
     * valor: servicio_snapshot, idper o idper_snapshot. Asi entran las ventas
     * de las dos epocas, y todo lo que no encaja cae en "Otros" en vez de
     * desaparecer.
     */
    public function obtenerResumenPorServicio($month, $year): array
    {
        // Se usa el mismo clasificador que la rotacion de clientes.
        $clasificar = fn(...$c) => $this->clasificarServicio(...$c);

        $inicio = Carbon::create($year, $month, 1)->startOfMonth();
        $fin    = Carbon::create($year, $month, 1)->endOfMonth();

        // --- Ingresos y perfiles facturados, por servicio ---
        $acumulado = [];

        DetalleVenta::query()
            ->join('ventas', 'ventas.idven', '=', 'detalles_venta.idven')
            ->whereBetween('ventas.fechaven', [$inicio->toDateString(), $fin->toDateString()])
            ->select([
                'detalles_venta.idper',
                'detalles_venta.idper_snapshot',
                'detalles_venta.servicio_snapshot',
                'detalles_venta.montodet',
            ])
            ->chunk(2000, function ($filas) use (&$acumulado, $clasificar) {
                foreach ($filas as $f) {
                    $servicio = $clasificar($f->servicio_snapshot, $f->idper, $f->idper_snapshot);

                    $acumulado[$servicio] ??= ['ingresos' => 0.0, 'costos' => 0.0, 'perfiles' => []];
                    $acumulado[$servicio]['ingresos'] += (float) $f->montodet;

                    $perfil = $f->idper ?: $f->idper_snapshot;
                    if ($perfil) {
                        $acumulado[$servicio]['perfiles'][$perfil] = true;
                    }
                }
            });

        // --- Costos, clasificados igual por la cuenta ---
        Costo::query()
            ->whereBetween('fechacos', [$inicio->toDateString(), $fin->toDateString()])
            ->select(['idcue', 'montocos'])
            ->chunk(2000, function ($filas) use (&$acumulado, $clasificar) {
                foreach ($filas as $c) {
                    $servicio = $clasificar($c->idcue);

                    // Un costo que no corresponde a ningun servicio no debe
                    // caer en la fila "Otros": alli hay ingresos de servicios
                    // sueltos y el margen salia en -25.970%. Va a su propia
                    // linea, sin margen, para que el total siga cuadrando.
                    if ($servicio === 'Otros') {
                        $servicio = 'Costos generales';
                    }

                    $acumulado[$servicio] ??= ['ingresos' => 0.0, 'costos' => 0.0, 'perfiles' => []];
                    $acumulado[$servicio]['costos'] += (float) $c->montocos;
                }
            });

        $totalIngresos = array_sum(array_column($acumulado, 'ingresos'));

        $filas = [];
        foreach ($acumulado as $servicio => $d) {
            $ganancia = $d['ingresos'] - $d['costos'];
            $perfiles = count($d['perfiles']);

            $filas[] = [
                'servicio'   => $servicio,
                'perfiles'   => $perfiles,
                'ingresos'   => $d['ingresos'],
                'costos'     => $d['costos'],
                'ganancia'   => $ganancia,
                'margen'     => $d['ingresos'] > 0 ? ($ganancia / $d['ingresos']) * 100 : null,
                'peso'       => $totalIngresos > 0 ? ($d['ingresos'] / $totalIngresos) * 100 : 0,
                'por_perfil' => $perfiles > 0 ? $d['ingresos'] / $perfiles : 0,
            ];
        }

        // De mayor a menor ingreso: lo importante arriba.
        usort($filas, fn($a, $b) => $b['ingresos'] <=> $a['ingresos']);

        return $filas;
    }

    public function getNetflix($month, $year)
    {
        $cuentas = $this->cuentaService->obtenerCuentas();
        $cuentas_netflix = $cuentas->filter(function ($cuenta) {
            return Str::startsWith($cuenta->idcue, 'NETFLIX');
        })->count();
        $usuarios_netflix = ViewUsuarioActivo::where('idcue', 'like', 'NETFLIX%')->count();
        $ingresos_netflix = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'NETFLIX%')
            ->sum('montodet');
        $costos_netflix = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'NETFLIX%')->sum('montocos');
        return compact('cuentas_netflix', 'usuarios_netflix', 'ingresos_netflix', 'costos_netflix');
    }
    public function getDisney($month, $year)
    {
        $cuentas = $this->cuentaService->obtenerCuentas();
        $cuentas_disney = $cuentas->filter(function ($cuenta) {
            return Str::startsWith($cuenta->idcue, 'DISNEY');
        })->count();
        $usuarios_disney = ViewUsuarioActivo::where('idcue', 'like', 'DISNEY%')->count();
        $ingresos_disney = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'DISNEY%')
            ->sum('montodet');
        $costos_disney = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'DISNEY%')->sum('montocos');
        return compact('cuentas_disney', 'usuarios_disney', 'ingresos_disney', 'costos_disney');
    }
    public function getPrime($month, $year)
    {
        $cuentas = $this->cuentaService->obtenerCuentas();
        $cuentas_prime = $cuentas->filter(function ($cuenta) {
            return Str::startsWith($cuenta->idcue, 'PRIME');
        })->count();
        $usuarios_prime = ViewUsuarioActivo::where('idcue', 'like', 'PRIME%')->count();
        $ingresos_prime = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'PRIME%')
            ->sum('montodet');
        $costos_prime = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'PRIME%')->sum('montocos');
        return compact('cuentas_prime', 'usuarios_prime', 'ingresos_prime', 'costos_prime');
    }
    public function getMax($month, $year)
    {
        $cuentas = $this->cuentaService->obtenerCuentas();
        $cuentas_max = $cuentas->filter(function ($cuenta) {
            return Str::startsWith($cuenta->idcue, 'MAX');
        })->count();
        $usuarios_max = ViewUsuarioActivo::where('idcue', 'like', 'MAX%')->count();
        $ingresos_max = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'MAX%')
            ->sum('montodet');
        $costos_max = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'MAX%')->sum('montocos');
        return compact('cuentas_max', 'usuarios_max', 'ingresos_max', 'costos_max');
    }
    public function getMagis($month, $year)
    {
        $cuentas = $this->cuentaService->obtenerCuentas();
        $cuentas_magis = $cuentas->filter(function ($cuenta) {
            return Str::startsWith($cuenta->idcue, 'FLUJO');
        })->count();
        $usuarios_magis = ViewUsuarioActivo::where('idcue', 'like', 'FLUJO%')->count();
        $ingresos_magis = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'FLUJO%')
            ->sum('montodet');
        $costos_magis = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'FLUJO%')->sum('montocos');
        return compact('cuentas_magis', 'usuarios_magis', 'ingresos_magis', 'costos_magis');
    }
    public function getCrunchyroll($month, $year)
    {
        $cuentas = $this->cuentaService->obtenerCuentas();
        $cuentas_crunchy = $cuentas->filter(function ($cuenta) {
            return Str::startsWith($cuenta->idcue, 'CRUNCHY');
        })->count();
        $usuarios_crunchy = ViewUsuarioActivo::where('idcue', 'like', 'CRUNCHY%')->count();
        $ingresos_crunchy = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'CRUNCHY%')
            ->sum('montodet');
        $costos_crunchy = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'CRUNCHY%')->sum('montocos');
        return compact('cuentas_crunchy', 'usuarios_crunchy', 'ingresos_crunchy', 'costos_crunchy');
    }
    public function getParamount($month, $year)
    {
        $cuentas = $this->cuentaService->obtenerCuentas();
        $cuentas_paramount = $cuentas->filter(function ($cuenta) {
            return Str::startsWith($cuenta->idcue, 'PARAMOUNT');
        })->count();
        $usuarios_paramount = ViewUsuarioActivo::where('idcue', 'like', 'PARAMOUNT%')->count();
        $ingresos_paramount = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'PARAMOUNT%')
            ->sum('montodet');
        $costos_paramount = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'PARAMOUNT%')->sum('montocos');
        return compact('cuentas_paramount', 'usuarios_paramount', 'ingresos_paramount', 'costos_paramount');
    }
    public function getSpotify($month, $year)
    {
        $cuentas = $this->cuentaService->obtenerCuentas();
        $cuentas_spotify = $cuentas->filter(function ($cuenta) {
            return Str::startsWith($cuenta->idcue, 'SPOTIFY');
        })->count();
        $usuarios_spotify = ViewUsuarioActivo::where('idcue', 'like', 'SPOTIFY%')->count();
        $ingresos_spotify = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'SPOTIFY%')
            ->sum('montodet');
        $costos_spotify = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'SPOTIFY%')->sum('montocos');
        return compact('cuentas_spotify', 'usuarios_spotify', 'ingresos_spotify', 'costos_spotify');
    }
    public function getOtros($month, $year)
    {
        $cuentas = $this->cuentaService->obtenerCuentas();
        $cuentas_otros = $cuentas->filter(function ($cuenta) {
            $prefixes = ['NETFLIX', 'DISNEY', 'PRIME', 'MAX', 'FLUJO', 'CRUNCHY', 'PARAMOUNT', 'SPOTIFY'];
            foreach ($prefixes as $prefix) {
                if (Str::startsWith($cuenta->idcue, $prefix)) {
                    return false; // excluir
                }
            }
            return true; // incluir
        })->count();
        $usuarios_otros = ViewUsuarioActivo::where('idcue', 'not like', 'NETFLIX%')
            ->where('idcue', 'not like', 'DISNEY%')
            ->where('idcue', 'not like', 'PRIME%')
            ->where('idcue', 'not like', 'MAX%')
            ->where('idcue', 'not like', 'FLUJO%')
            ->where('idcue', 'not like', 'CRUNCHY%')
            ->where('idcue', 'not like', 'PARAMOUNT%')
            ->where('idcue', 'not like', 'SPOTIFY%')
            ->count();
        $ingresos_otros = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'not like', 'NETFLIX%')
            ->where('idper', 'not like', 'DISNEY%')
            ->where('idper', 'not like', 'PRIME%')
            ->where('idper', 'not like', 'MAX%')
            ->where('idper', 'not like', 'FLUJO%')
            ->where('idper', 'not like', 'CRUNCHY%')
            ->where('idper', 'not like', 'PARAMOUNT%')
            ->where('idper', 'not like', 'SPOTIFY%')
            ->sum('montodet');
        $costos_otros = Costo::whereMonth('fechacos', $month)
            ->whereYear('fechacos', $year)
            ->where('idcue', 'not like', 'NETFLIX%')
            ->where('idcue', 'not like', 'DISNEY%')
            ->where('idcue', 'not like', 'PRIME%')
            ->where('idcue', 'not like', 'MAX%')
            ->where('idcue', 'not like', 'FLUJO%')
            ->where('idcue', 'not like', 'CRUNCHY%')
            ->where('idcue', 'not like', 'PARAMOUNT%')
            ->where('idcue', 'not like', 'SPOTIFY%')
            ->sum('montocos');
        return compact('cuentas_otros', 'usuarios_otros', 'ingresos_otros', 'costos_otros');
    }

    private function getIngresosMonth(int $month, int $year): float
    {
        return Venta::whereMonth('fechaven', $month)
            ->whereYear('fechaven', $year)
            ->sum('totalpagoven');
    }
    private function getIngresosDay(string $date): float
    {
        return Venta::whereDate('fechaven', $date)
            ->sum('totalpagoven');
    }
    public function getIngresosBetweenDates(string $date1, string $date2): float
    {
        return Venta::whereBetween('fechaven', [$date1, $date2])
            ->sum('totalpagoven');
    }
    public function getIngresosChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];
        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i);
                    $formattedDate = $date->format('d M'); // Ejemplo: "01 Mar"
                    $data[$formattedDate] = $this->getIngresosDay($date->toDateString());
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek();
                    $weekNumber = $startOfWeek->weekOfMonth;
                    $monthName = $startOfWeek->translatedFormat('F'); // Nombre del mes en español
                    $formattedWeek = "Week $weekNumber $monthName"; // Ejemplo: "Week 2 Febrero"
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$formattedWeek] = $this->getIngresosBetweenDates($startOfWeek->toDateString(), $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F'); // Nombre del mes en español
                    $year = $date->year;
                    $formattedMonth = "$monthName $year"; // Ejemplo: "Marzo 2024"
                    $data[$formattedMonth] = $this->getIngresosMonth($date->month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter();
                    $quarterNumber = ceil($startOfQuarter->month / 3);
                    $year = $startOfQuarter->year;
                    $formattedQuarter = "Tr $quarterNumber $year"; // Ejemplo: "Trimestre 1 de 2024"
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$formattedQuarter] = $this->getIngresosBetweenDates($startOfQuarter->toDateString(), $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = Venta::whereYear('fechaven', $year)->sum('totalpagoven');
                }
                break;

            default: // Si el intervalo es inválido, usar 1M como predeterminado
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F');
                    $year = $date->year;
                    $formattedMonth = "$monthName $year";
                    $data[$formattedMonth] = $this->getIngresosMonth($date->month, $year);
                }
                break;
        }
        return $data;
    }

    private function getCostosMonth(int $month, int $year): float
    {
        return Costo::whereMonth('fechacos', $month)
            ->whereYear('fechacos', $year)
            ->sum('montocos');
    }
    private function getCostosDay(string $date): float
    {
        return Costo::whereDate('fechacos', $date)
            ->sum('montocos');
    }
    public function getCostosBetweenDates(string $date1, string $date2): float
    {
        return Costo::whereBetween('fechacos', [$date1, $date2])
            ->sum('montocos');
    }
    public function getCostosChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i);
                    $formattedDate = $date->format('d M'); // Ejemplo: "01 Mar"
                    $data[$formattedDate] = $this->getCostosDay($date->toDateString());
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek();
                    $weekNumber = $startOfWeek->weekOfMonth;
                    $monthName = $startOfWeek->translatedFormat('F'); // Nombre del mes en español
                    $formattedWeek = "Week $weekNumber $monthName"; // Ejemplo: "Week 2 Febrero"
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$formattedWeek] = $this->getCostosBetweenDates($startOfWeek->toDateString(), $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F'); // Nombre del mes en español
                    $year = $date->year;
                    $formattedMonth = "$monthName $year"; // Ejemplo: "Marzo 2024"
                    $data[$formattedMonth] = $this->getCostosMonth($date->month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter();
                    $quarterNumber = ceil($startOfQuarter->month / 3);
                    $year = $startOfQuarter->year;
                    $formattedQuarter = "Tr $quarterNumber $year"; // Ejemplo: "Tr 1 2024"
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$formattedQuarter] = $this->getCostosBetweenDates($startOfQuarter->toDateString(), $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = Costo::whereYear('fechacos', $year)->sum('montocos');
                }
                break;

            default: // Si el intervalo es inválido, usar 1M como predeterminado
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F');
                    $year = $date->year;
                    $formattedMonth = "$monthName $year";
                    $data[$formattedMonth] = $this->getCostosMonth($date->month, $year);
                }
                break;
        }
        return $data;
    }
    private function getGastosMonth(int $month, int $year): float
    {
        return Gasto::whereMonth('fechagas', $month)
            ->whereYear('fechagas', $year)
            ->sum('montogas');
    }
    private function getGastosDay(string $date): float
    {
        return Gasto::whereDate('fechagas', $date)
            ->sum('montogas');
    }
    public function getGastosBetweenDates(string $date1, string $date2): float
    {
        return Gasto::whereBetween('fechagas', [$date1, $date2])
            ->sum('montogas');
    }
    public function getGastosChartData(string $interval): array
    {
        Carbon::setLocale('es'); // Asegurar que los nombres de los meses estén en español
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i);
                    $formattedDate = $date->format('d M'); // Ejemplo: "01 Mar"
                    $data[$formattedDate] = $this->getGastosDay($date->toDateString());
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek();
                    $weekNumber = $startOfWeek->weekOfYear;
                    $monthName = $startOfWeek->translatedFormat('F');
                    $formattedWeek = "Semana $weekNumber de $monthName"; // Ejemplo: "Semana 6 de Febrero"
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$formattedWeek] = $this->getGastosBetweenDates($startOfWeek->toDateString(), $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F');
                    $year = $date->year;
                    $formattedMonth = "$monthName $year"; // Ejemplo: "Marzo 2024"
                    $data[$formattedMonth] = $this->getGastosMonth($date->month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter();
                    $quarterNumber = ceil($startOfQuarter->month / 3);
                    $year = $startOfQuarter->year;
                    $formattedQuarter = "Tr $quarterNumber $year"; // Ejemplo: "Tr 1 2024"
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$formattedQuarter] = $this->getGastosBetweenDates($startOfQuarter->toDateString(), $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = Gasto::whereYear('fechagas', $year)->sum('montogas');
                }
                break;

            default: // Si el intervalo es inválido, usar 1M como predeterminado
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F');
                    $year = $date->year;
                    $formattedMonth = "$monthName $year";
                    $data[$formattedMonth] = $this->getGastosMonth($date->month, $year);
                }
                break;
        }
        return $data;
    }

    private function getGananciasMonth(int $month, int $year): float
    {
        $ingresos = $this->getIngresosMonth($month, $year);
        $costos = $this->getCostosMonth($month, $year);
        $gastos = $this->getGastosMonth($month, $year);
        return $ingresos - ($costos + $gastos);
    }
    private function getGananciasDay(string $date): float
    {
        $ingresos = $this->getIngresosDay($date);
        $costos = $this->getCostosDay($date);
        $gastos = $this->getGastosDay($date);
        return $ingresos - ($costos + $gastos);
    }
    public function getGananciasBetweenDates(string $date1, string $date2): float
    {
        $ingresos = $this->getIngresosBetweenDates($date1, $date2);
        $costos = $this->getCostosBetweenDates($date1, $date2);
        $gastos = $this->getGastosBetweenDates($date1, $date2);
        return $ingresos - ($costos + $gastos);
    }
    public function getGananciasChartData(string $interval): array
    {
        $ingresos = $this->getIngresosChartData($interval);
        $costos = $this->getCostosChartData($interval);
        $gastos = $this->getGastosChartData($interval);

        $ganancias = [];

        foreach ($ingresos as $key => $valorIngreso) {
            $costo = $costos[$key] ?? 0;
            $gasto = $gastos[$key] ?? 0;
            $ganancias[$key] = $valorIngreso - ($costo + $gasto);
        }

        return $ganancias;
    }

    private function getVentasMonth(int $month, int $year): float
    {
        return Venta::whereMonth('fechaven', $month)
            ->whereYear('fechaven', $year)
            ->count();
    }
    private function getVentasDay(string $date): float
    {
        return Venta::whereDate('fechaven', $date)->count();
    }
    public function getVentasBetweenDates(string $date1, string $date2): float
    {
        return Venta::whereBetween('fechaven', [$date1, $date2])
            ->count();
    }
    public function getVentasChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i)->toDateString();
                    $data[$date] = $this->getVentasDay($date);
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek()->toDateString();
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$startOfWeek] = $this->getVentasBetweenDates($startOfWeek, $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getVentasMonth($month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter()->toDateString();
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$startOfQuarter] = $this->getVentasBetweenDates($startOfQuarter, $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = Venta::whereYear('fechaven', $year)->count();
                }
                break;

            default:
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getVentasMonth($month, $year);
                }
                break;
        }
        //return dd($data); // Detiene la ejecución y muestra los datos
        return $data;
    }

    private function getNewCustomersMonth(int $month, int $year): float
    {
        return Cliente::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();
    }
    private function getNewCustomersDay(string $date): float
    {
        return Cliente::whereDate('created_at', $date)->count();
    }
    public function getNewCustomersBetweenDates(string $date1, string $date2): float
    {
        return Cliente::whereBetween('created_at', [$date1, $date2])
            ->count();
    }
    public function getNewCustomersChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i)->toDateString();
                    $data[$date] = $this->getNewCustomersDay($date);
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek()->toDateString();
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$startOfWeek] = $this->getNewCustomersBetweenDates($startOfWeek, $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getNewCustomersMonth($month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter()->toDateString();
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$startOfQuarter] = $this->getNewCustomersBetweenDates($startOfQuarter, $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = Cliente::whereYear('created_at', $year)->count();
                }
                break;

            default:
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getNewCustomersMonth($month, $year);
                }
                break;
        }
        //return dd($data); // Detiene la ejecución y muestra los datos
        return $data;
    }

    private function getUsersMonth(int $month, int $year): float
    {
        $dato = DailyStatistic::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->max('active_users');
        return (float) $dato;
    }
    private function getUsersDay(string $date): float
    {
        return DailyStatistic::whereDate('date', $date)->value('active_users') ?? 0;
    }
    public function getUsersBetweenDates(string $date1, string $date2): float
    {
        return (float) DailyStatistic::whereBetween('created_at', [$date1, $date2])
            ->max('active_users');
    }
    public function getUsersChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i)->toDateString();
                    $data[$date] = $this->getUsersDay($date);
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek()->toDateString();
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$startOfWeek] = $this->getUsersBetweenDates($startOfWeek, $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getUsersMonth($month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter()->toDateString();
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$startOfQuarter] = $this->getUsersBetweenDates($startOfQuarter, $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = DailyStatistic::whereYear('date', $year)->max('active_users');
                }
                break;

            default:
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getUsersMonth($month, $year);
                }
                break;
        }
        //return dd($data); // Detiene la ejecución y muestra los datos
        return $data;
    }
    private function getAccountsMonth(int $month, int $year): float
    {
        $dato = DailyStatistic::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->max('accounts');
        return (float) $dato;
    }
    private function getAccountsDay(string $date): float
    {
        return DailyStatistic::whereDate('date', $date)->value('accounts') ?? 0;
    }
    public function getAccountsBetweenDates(string $date1, string $date2): float
    {
        return (float) DailyStatistic::whereBetween('created_at', [$date1, $date2])
            ->max('accounts');
    }
    public function getAccountsChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i)->toDateString();
                    $data[$date] = $this->getAccountsDay($date);
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek()->toDateString();
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$startOfWeek] = $this->getAccountsBetweenDates($startOfWeek, $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getAccountsMonth($month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter()->toDateString();
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$startOfQuarter] = $this->getAccountsBetweenDates($startOfQuarter, $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = DailyStatistic::whereYear('date', $year)->max('accounts');
                }
                break;

            default:
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getAccountsMonth($month, $year);
                }
                break;
        }
        //return dd($data); // Detiene la ejecución y muestra los datos
        return $data;
    }
    private function getDangerAccountsMonth(int $month, int $year): float
    {
        $dato = DailyStatistic::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->max('danger_accounts');
        return (float) $dato;
    }
    private function getDangerAccountsDay(string $date): float
    {
        return DailyStatistic::whereDate('date', $date)->value('danger_accounts') ?? 0;
    }
    public function getDangerAccountsBetweenDates(string $date1, string $date2): float
    {
        return (float) DailyStatistic::whereBetween('created_at', [$date1, $date2])
            ->max('danger_accounts');
    }
    public function getDangerAccountsChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i)->toDateString();
                    $data[$date] = $this->getDangerAccountsDay($date);
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek()->toDateString();
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$startOfWeek] = $this->getDangerAccountsBetweenDates($startOfWeek, $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getDangerAccountsMonth($month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter()->toDateString();
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$startOfQuarter] = $this->getDangerAccountsBetweenDates($startOfQuarter, $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = DailyStatistic::whereYear('date', $year)->max('danger_accounts');
                }
                break;

            default:
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getDangerAccountsMonth($month, $year);
                }
                break;
        }
        //return dd($data); // Detiene la ejecución y muestra los datos
        return $data;
    }
    private function getAffectedCustomersMonth(int $month, int $year): float
    {
        $dato = DailyStatistic::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->max('affected_customers');
        return (float) $dato;
    }
    private function getAffectedCustomersDay(string $date): float
    {
        return DailyStatistic::whereDate('date', $date)->value('affected_customers') ?? 0;
    }
    public function getAffectedCustomersBetweenDates(string $date1, string $date2): float
    {
        return (float) DailyStatistic::whereBetween('created_at', [$date1, $date2])
            ->max('affected_customers');
    }
    public function getAffectedCustomersChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i)->toDateString();
                    $data[$date] = $this->getAffectedCustomersDay($date);
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek()->toDateString();
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$startOfWeek] = $this->getAffectedCustomersBetweenDates($startOfWeek, $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getAffectedCustomersMonth($month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter()->toDateString();
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$startOfQuarter] = $this->getAffectedCustomersBetweenDates($startOfQuarter, $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = DailyStatistic::whereYear('date', $year)->max('affected_customers');
                }
                break;

            default:
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getAffectedCustomersMonth($month, $year);
                }
                break;
        }
        //return dd($data); // Detiene la ejecución y muestra los datos
        return $data;
    }
    private function getPendingPaymentsMonth(int $month, int $year): float
    {
        $dato = DailyStatistic::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->max('pending_payments');
        return (float) $dato;
    }
    private function getPendingPaymentsDay(string $date): float
    {
        return DailyStatistic::whereDate('date', $date)->value('pending_payments') ?? 0;
    }
    public function getPendingPaymentsBetweenDates(string $date1, string $date2): float
    {
        return (float) DailyStatistic::whereBetween('created_at', [$date1, $date2])
            ->max('pending_payments');
    }
    public function getPendingPaymentsChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i)->toDateString();
                    $data[$date] = $this->getPendingPaymentsDay($date);
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek()->toDateString();
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$startOfWeek] = $this->getPendingPaymentsBetweenDates($startOfWeek, $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getPendingPaymentsMonth($month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter()->toDateString();
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$startOfQuarter] = $this->getPendingPaymentsBetweenDates($startOfQuarter, $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = DailyStatistic::whereYear('date', $year)->max('pending_payments');
                }
                break;

            default:
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getPendingPaymentsMonth($month, $year);
                }
                break;
        }
        //return dd($data); // Detiene la ejecución y muestra los datos
        return $data;
    }
    public function guardar($date)
    {
        $usuarios = ViewUsuarioActivo::all();
        $activeUsers = $usuarios->count();
        $cc = $this->cuentaService->obtenerCuentas();
        $accounts = $cc->count();
        $dangerAccounts = $this->cuentaService->contarCuentasCaidas($cc);
        $affectedCustomers = Cuenta::where('activocue', true)
            ->where('caidacue', true)
            ->get()
            ->sum('usuarios_activos');
        $pendingPayments = $this->cuentaService->contarUsuariosACobrar($usuarios);

        // CORREGIDO: Usar fechaven en lugar de created_at para ventas
        $dailyRevenue = Venta::whereDate('fechaven', $date)->sum('totalpagoven');
        $dailyCost = Costo::whereDate('fechacos', $date)->sum('montocos');
        // CORREGIDO: Usar fechagas en lugar de created_at para gastos
        // daily_bill = solo gastos operativos (afectan la utilidad); daily_bill_personal = pago de personal/retiro del dueño (informativo)
        $dailyBill = Gasto::operativos()->whereDate('fechagas', $date)->sum('montogas');
        $dailyBillPersonal = Gasto::personal()->whereDate('fechagas', $date)->sum('montogas');
        $dailySales = Venta::whereDate('fechaven', $date)->count();
        $dailyTasks = Tarea::whereDate('created_at', $date)->count();
        $newCustomers = Cliente::whereDate('created_at', $date)->count();
        $espacios = $this->cuentaService->calcularEspaciosTotales();
        $usuarios_acobrar = $this->cuentaService->contarUsuariosACobrar($usuarios);
        $cliente_mas_facturado = ViewClientesUsuarios::orderByDesc('facturado')->select('nombre_cliente', 'facturado')->first();
        $total_customers = ViewClientesUsuarios::count();
        $churn = $this->calcularChurnDiario($date);

        DailyStatistic::updateOrCreate(
            ['date' => $date], // Fecha única
            [
                'active_users' => $activeUsers,
                'affected_customers' => $affectedCustomers,
                'pending_payments' => $pendingPayments,
                'danger_accounts' => $dangerAccounts,
                'accounts' => $accounts,
                'daily_revenue' => $dailyRevenue,
                'daily_cost' => $dailyCost,
                'daily_bill' => $dailyBill,
                'daily_bill_personal' => $dailyBillPersonal,
                'daily_sales' => $dailySales,
                'daily_tasks' => $dailyTasks,
                'new_customers' => $newCustomers,
                'usuarios_a_cobrar' => $usuarios_acobrar,
                'espacios' => $espacios,
                'cliente_mas_facturado' => $cliente_mas_facturado->nombre_cliente ?? '',
                'total_customers' => $total_customers,
                'usuarios_removidos' => $churn['usuarios_removidos'],
                'clientes_perdidos' => $churn['clientes_perdidos'],
            ]
        );

        // AGREGADO: Retornar datos para verificación en la API
        return [
            'success' => true,
            'date' => $date,
            'data' => [
                'active_users' => $activeUsers,
                'accounts' => $accounts,
                'daily_revenue' => $dailyRevenue,
                'daily_cost' => $dailyCost,
                'daily_bill' => $dailyBill,
                'daily_bill_personal' => $dailyBillPersonal,
                'daily_sales' => $dailySales,
                'balance' => $dailyRevenue - $dailyCost - $dailyBill,
                'usuarios_removidos' => $churn['usuarios_removidos'],
                'clientes_perdidos' => $churn['clientes_perdidos'],
            ]
        ];
    }
    public function getGastos($ingresos_mes, $month = null, $year = null)
    {
        // Evitar división por cero
        $ingresos = $ingresos_mes > 0 ? $ingresos_mes : 1;
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;
        // Obtener todos los tipos de gastos con sus montos sumados para el mes y año dados
        $gastos = Gasto::selectRaw('idtip, SUM(montogas) as total')
            ->whereMonth('fechagas', $month)
            ->whereYear('fechagas', $year)
            ->groupBy('idtip')
            ->with('tipoGasto') // Relación para obtener el nombre del tipo de gasto
            ->havingRaw('SUM(montogas) > 0') // Corrección: repetir SUM() en HAVING
            ->orderByDesc('total') // Ordenar de mayor a menor
            ->get();
        // Formatear la respuesta
        $gastosData = $gastos->map(function ($gasto) use ($ingresos) {
            return [
                'concepto' => $gasto->tipoGasto->detalletip ?? 'Desconocido', // Nombre del tipo de gasto
                'porcentaje' => round(($gasto->total / $ingresos) * 100, 2),  // % sobre ingresos
                'total' => $gasto->total, // Total gastado
                'excluido_de_ganancia' => (bool) ($gasto->tipoGasto->excluir_de_ganancia ?? false), // No cuenta como gasto operativo
            ];
        });
        return $gastosData->toArray();
    }

    /**
     * Obtener ingresos mensuales por rango de fechas
     * @param int $month
     * @param int $year
     * @return float
     */
    public function getIngresosMensuales(int $month, int $year): float
    {
        return Venta::whereMonth('fechaven', $month)
            ->whereYear('fechaven', $year)
            ->sum('totalpagoven');
    }

    /**
     * Obtener costos mensuales por rango de fechas
     * @param int $month
     * @param int $year
     * @return float
     */
    public function getCostosMensuales(int $month, int $year): float
    {
        return Costo::whereMonth('fechacos', $month)
            ->whereYear('fechacos', $year)
            ->sum('montocos');
    }

    /**
     * Obtener gastos mensuales por rango de fechas
     * @param int $month
     * @param int $year
     * @return float
     */
    public function getGastosMensuales(int $month, int $year): float
    {
        return Gasto::operativos()->whereMonth('fechagas', $month)
            ->whereYear('fechagas', $year)
            ->sum('montogas');
    }

    /**
     * Obtener gastos de personal/retiro del dueño mensuales (informativo, no afecta la utilidad)
     * @param int $month
     * @param int $year
     * @return float
     */
    public function getGastosPersonalMensuales(int $month, int $year): float
    {
        return Gasto::personal()->whereMonth('fechagas', $month)
            ->whereYear('fechagas', $year)
            ->sum('montogas');
    }

    /**
     * Obtener cantidad de ventas mensuales
     * @param int $month
     * @param int $year
     * @return int
     */
    public function getVentasMensuales(int $month, int $year): int
    {
        return Venta::whereMonth('fechaven', $month)
            ->whereYear('fechaven', $year)
            ->count();
    }

    /**
     * Obtener datos consolidados anuales
     * @param int $year
     * @return array
     */
    public function getDatosAnuales(int $year): array
    {
        $datosAnuales = [
            'ingresos_totales' => 0,
            'costos_totales' => 0,
            'gastos_totales' => 0,
            'gastos_personal_totales' => 0,
            'ventas_totales' => 0,
            'balance_anual' => 0,
            'meses_data' => []
        ];

        // Recopilar datos por cada mes del año
        for ($month = 1; $month <= 12; $month++) {
            $mesCarbon = Carbon::create($year, $month, 1);
            $nombreMes = $mesCarbon->translatedFormat('F');

            $ingresosMes = $this->getIngresosMensuales($month, $year);
            $costosMes = $this->getCostosMensuales($month, $year);
            $gastosMes = $this->getGastosMensuales($month, $year);
            $gastosPersonalMes = $this->getGastosPersonalMensuales($month, $year);
            $ventasMes = $this->getVentasMensuales($month, $year);
            $balanceMes = $ingresosMes - $costosMes - $gastosMes;

            // Acumular totales
            $datosAnuales['ingresos_totales'] += $ingresosMes;
            $datosAnuales['costos_totales'] += $costosMes;
            $datosAnuales['gastos_totales'] += $gastosMes;
            $datosAnuales['gastos_personal_totales'] += $gastosPersonalMes;
            $datosAnuales['ventas_totales'] += $ventasMes;

            // Guardar datos del mes
            $datosAnuales['meses_data'][] = [
                'mes' => $nombreMes,
                'ingresos' => round($ingresosMes, 2),
                'costos' => round($costosMes, 2),
                'gastos' => round($gastosMes, 2),
                'gastos_personal' => round($gastosPersonalMes, 2),
                'ventas' => $ventasMes,
                'balance' => round($balanceMes, 2)
            ];
        }

        // Calcular balance anual y redondear totales
        $datosAnuales['balance_anual'] = round(
            $datosAnuales['ingresos_totales'] -
            $datosAnuales['costos_totales'] -
            $datosAnuales['gastos_totales'],
            2
        );

        $datosAnuales['ingresos_totales'] = round($datosAnuales['ingresos_totales'], 2);
        $datosAnuales['costos_totales'] = round($datosAnuales['costos_totales'], 2);
        $datosAnuales['gastos_totales'] = round($datosAnuales['gastos_totales'], 2);
        $datosAnuales['gastos_personal_totales'] = round($datosAnuales['gastos_personal_totales'], 2);

        return $datosAnuales;
    }

    /**
     * Datos del gráfico de área con paginación por cursor (carga progresiva).
     *
     * @param  string      $interval  '1d' | '1w' | '1m' | '3m' | '1y'
     * @param  int         $limit     Cantidad de puntos a devolver
     * @param  string|null $before    Fecha ISO — devuelve datos ANTERIORES a esta fecha
     * @return array  { labels, ingresos, …, has_more, oldest_date }
     */
    public function getChartData(string $interval, int $limit = 60, ?string $before = null): array
    {
        Carbon::setLocale('es');

        $labels = $ingresos = $costos = $gastos = $ganancias = [];
        $ventasChart = $newCustomers = $users = $accounts = [];
        $dangerAccounts = $pendingPayments = $affectedCustomers = [];
        $clientesPerdidos = [];
        $hasMore   = false;
        $oldestDate = null;

        // Límites razonables por intervalo
        $defaults = ['1d'=>60,'1w'=>26,'1m'=>18,'3m'=>12,'1y'=>10];
        $limit = min($limit ?: ($defaults[$interval] ?? 60), 120);

        switch ($interval) {

            /* ── Diario ──────────────────────────────────────────────────── */
            case '1d':
                $q = DB::table('daily_statistics')
                    ->select('date','daily_revenue','daily_cost','daily_bill','daily_sales',
                             'active_users','accounts','danger_accounts','pending_payments',
                             'affected_customers','new_customers','clientes_perdidos')
                    ->orderBy('date', 'DESC')
                    ->limit($limit + 1);
                if ($before) $q->where('date', '<', $before);

                $rows = $q->get();
                $hasMore = $rows->count() > $limit;
                $rows    = $rows->take($limit)->reverse()->values();

                foreach ($rows as $r) {
                    $labels[]            = Carbon::parse($r->date)->isoFormat('DD MMM');
                    $rev = (float)($r->daily_revenue ?? 0);
                    $cos = (float)($r->daily_cost    ?? 0);
                    $gas = (float)($r->daily_bill    ?? 0);
                    $ingresos[]          = $rev;  $costos[]  = $cos;  $gastos[]  = $gas;
                    $ganancias[]         = $rev - $cos - $gas;
                    $ventasChart[]       = (int)($r->daily_sales        ?? 0);
                    $newCustomers[]      = (int)($r->new_customers      ?? 0);
                    $users[]             = (int)($r->active_users       ?? 0);
                    $accounts[]          = (int)($r->accounts           ?? 0);
                    $dangerAccounts[]    = (int)($r->danger_accounts    ?? 0);
                    $pendingPayments[]   = (int)($r->pending_payments   ?? 0);
                    $affectedCustomers[] = (int)($r->affected_customers ?? 0);
                    $clientesPerdidos[]  = (int)($r->clientes_perdidos  ?? 0);
                }
                $oldestDate = $rows->first()?->date;
                break;

            /* ── Semanal ─────────────────────────────────────────────────── */
            case '1w':
                $q = DB::table('daily_statistics')
                    ->selectRaw("YEARWEEK(date,1) as yw, MIN(date) as week_start,
                        SUM(daily_revenue) as rev, SUM(daily_cost) as cos,
                        SUM(daily_bill) as gas, SUM(daily_sales) as sales,
                        MAX(active_users) as users, MAX(accounts) as accs,
                        MAX(danger_accounts) as da, MAX(pending_payments) as pp,
                        MAX(affected_customers) as ac, SUM(new_customers) as nc,
                        SUM(clientes_perdidos) as cp")
                    ->groupBy(DB::raw('YEARWEEK(date,1)'))
                    ->orderBy('yw', 'DESC')
                    ->limit($limit + 1);
                if ($before) $q->havingRaw('MIN(date) < ?', [$before]);

                $rows = $q->get();
                $hasMore = $rows->count() > $limit;
                $rows    = $rows->take($limit)->reverse()->values();

                foreach ($rows as $r) {
                    $d = Carbon::parse($r->week_start)->startOfWeek();
                    $labels[]            = 'Sem ' . $d->weekOfYear . ' ' . $d->isoFormat('MMM YY');
                    $rev = (float)($r->rev ?? 0); $cos = (float)($r->cos ?? 0); $gas = (float)($r->gas ?? 0);
                    $ingresos[]          = $rev;  $costos[]  = $cos;  $gastos[]  = $gas;
                    $ganancias[]         = $rev - $cos - $gas;
                    $ventasChart[]       = (int)($r->sales ?? 0); $newCustomers[]      = (int)($r->nc ?? 0);
                    $users[]             = (int)($r->users ?? 0); $accounts[]          = (int)($r->accs ?? 0);
                    $dangerAccounts[]    = (int)($r->da    ?? 0); $pendingPayments[]   = (int)($r->pp   ?? 0);
                    $affectedCustomers[] = (int)($r->ac    ?? 0); $clientesPerdidos[]  = (int)($r->cp   ?? 0);
                }
                $oldestDate = $rows->first()?->week_start;
                break;

            /* ── Mensual ─────────────────────────────────────────────────── */
            case '1m':
                $q = DB::table('daily_statistics')
                    ->selectRaw("YEAR(date) as yr, MONTH(date) as mo, MIN(date) as period_start,
                        SUM(daily_revenue) as rev, SUM(daily_cost) as cos,
                        SUM(daily_bill) as gas, SUM(daily_sales) as sales,
                        MAX(active_users) as users, MAX(accounts) as accs,
                        MAX(danger_accounts) as da, MAX(pending_payments) as pp,
                        MAX(affected_customers) as ac, SUM(new_customers) as nc,
                        SUM(clientes_perdidos) as cp")
                    ->groupBy('yr', 'mo')
                    ->orderByRaw('yr DESC, mo DESC')
                    ->limit($limit + 1);
                if ($before) $q->havingRaw('MIN(date) < ?', [$before]);

                $rows = $q->get();
                $hasMore = $rows->count() > $limit;
                $rows    = $rows->take($limit)->reverse()->values();

                foreach ($rows as $r) {
                    $labels[]            = Carbon::create($r->yr, $r->mo, 1)->isoFormat('MMMM YYYY');
                    $rev = (float)($r->rev ?? 0); $cos = (float)($r->cos ?? 0); $gas = (float)($r->gas ?? 0);
                    $ingresos[]          = $rev;  $costos[]  = $cos;  $gastos[]  = $gas;
                    $ganancias[]         = $rev - $cos - $gas;
                    $ventasChart[]       = (int)($r->sales ?? 0); $newCustomers[]      = (int)($r->nc ?? 0);
                    $users[]             = (int)($r->users ?? 0); $accounts[]          = (int)($r->accs ?? 0);
                    $dangerAccounts[]    = (int)($r->da    ?? 0); $pendingPayments[]   = (int)($r->pp   ?? 0);
                    $affectedCustomers[] = (int)($r->ac    ?? 0); $clientesPerdidos[]  = (int)($r->cp   ?? 0);
                }
                $oldestDate = $rows->first()?->period_start;
                break;

            /* ── Trimestral ──────────────────────────────────────────────── */
            case '3m':
                $q = DB::table('daily_statistics')
                    ->selectRaw("YEAR(date) as yr, QUARTER(date) as qt, MIN(date) as period_start,
                        SUM(daily_revenue) as rev, SUM(daily_cost) as cos,
                        SUM(daily_bill) as gas, SUM(daily_sales) as sales,
                        MAX(active_users) as users, MAX(accounts) as accs,
                        MAX(danger_accounts) as da, MAX(pending_payments) as pp,
                        MAX(affected_customers) as ac, SUM(new_customers) as nc,
                        SUM(clientes_perdidos) as cp")
                    ->groupBy('yr', 'qt')
                    ->orderByRaw('yr DESC, qt DESC')
                    ->limit($limit + 1);
                if ($before) $q->havingRaw('MIN(date) < ?', [$before]);

                $rows = $q->get();
                $hasMore = $rows->count() > $limit;
                $rows    = $rows->take($limit)->reverse()->values();

                foreach ($rows as $r) {
                    $labels[]            = "Tr {$r->qt} {$r->yr}";
                    $rev = (float)($r->rev ?? 0); $cos = (float)($r->cos ?? 0); $gas = (float)($r->gas ?? 0);
                    $ingresos[]          = $rev;  $costos[]  = $cos;  $gastos[]  = $gas;
                    $ganancias[]         = $rev - $cos - $gas;
                    $ventasChart[]       = (int)($r->sales ?? 0); $newCustomers[]      = (int)($r->nc ?? 0);
                    $users[]             = (int)($r->users ?? 0); $accounts[]          = (int)($r->accs ?? 0);
                    $dangerAccounts[]    = (int)($r->da    ?? 0); $pendingPayments[]   = (int)($r->pp   ?? 0);
                    $affectedCustomers[] = (int)($r->ac    ?? 0); $clientesPerdidos[]  = (int)($r->cp   ?? 0);
                }
                $oldestDate = $rows->first()?->period_start;
                break;

            /* ── Anual ───────────────────────────────────────────────────── */
            case '1y':
            default:
                $q = DB::table('daily_statistics')
                    ->selectRaw("YEAR(date) as yr, MIN(date) as period_start,
                        SUM(daily_revenue) as rev, SUM(daily_cost) as cos,
                        SUM(daily_bill) as gas, SUM(daily_sales) as sales,
                        MAX(active_users) as users, MAX(accounts) as accs,
                        MAX(danger_accounts) as da, MAX(pending_payments) as pp,
                        MAX(affected_customers) as ac, SUM(new_customers) as nc,
                        SUM(clientes_perdidos) as cp")
                    ->groupBy('yr')
                    ->orderBy('yr', 'DESC')
                    ->limit($limit + 1);
                if ($before) $q->havingRaw('MIN(date) < ?', [$before]);

                $rows = $q->get();
                $hasMore = $rows->count() > $limit;
                $rows    = $rows->take($limit)->reverse()->values();

                foreach ($rows as $r) {
                    $labels[]            = (string) $r->yr;
                    $rev = (float)($r->rev ?? 0); $cos = (float)($r->cos ?? 0); $gas = (float)($r->gas ?? 0);
                    $ingresos[]          = $rev;  $costos[]  = $cos;  $gastos[]  = $gas;
                    $ganancias[]         = $rev - $cos - $gas;
                    $ventasChart[]       = (int)($r->sales ?? 0); $newCustomers[]      = (int)($r->nc ?? 0);
                    $users[]             = (int)($r->users ?? 0); $accounts[]          = (int)($r->accs ?? 0);
                    $dangerAccounts[]    = (int)($r->da    ?? 0); $pendingPayments[]   = (int)($r->pp   ?? 0);
                    $affectedCustomers[] = (int)($r->ac    ?? 0); $clientesPerdidos[]  = (int)($r->cp   ?? 0);
                }
                $oldestDate = $rows->first()?->period_start;
                break;
        }

        return array_merge(
            compact('labels','ingresos','costos','gastos','ganancias',
                    'ventasChart','newCustomers','users','accounts',
                    'dangerAccounts','pendingPayments','affectedCustomers',
                    'clientesPerdidos'),
            ['has_more' => $hasMore, 'oldest_date' => $oldestDate]
        );
    }
}
