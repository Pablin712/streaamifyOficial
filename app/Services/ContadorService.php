<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\DailyStatistic;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Banco;
use App\Models\Cuenta;
use App\Models\ViewUsuarioActivo;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para el Rol Contador
 * Proporciona métodos optimizados para análisis financiero y estadísticas
 * Prioriza uso de DailyStatistics para reducir carga en DB
 */
class ContadorService
{
    /**
     * Obtener resumen de ventas por período
     *
     * @param string $periodo: daily, weekly, monthly, yearly, custom
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @return array
     */
    public function resumenVentas($periodo = 'daily', $fechaInicio = null, $fechaFin = null)
    {
        $fechas = $this->calcularFechas($periodo, $fechaInicio, $fechaFin);

        // Usar DailyStatistics para mejorar performance
        $estadisticas = DailyStatistic::whereBetween('date', [$fechas['inicio'], $fechas['fin']])
            ->select(
                DB::raw('SUM(daily_revenue) as total_ventas'),
                DB::raw('SUM(daily_sales) as cantidad_ventas'),
                DB::raw('AVG(daily_revenue) as promedio_venta'),
                DB::raw('SUM(daily_cost) as total_costos'),
                DB::raw('SUM(daily_bill) as total_gastos'),
                DB::raw('SUM(new_customers) as clientes_nuevos')
            )
            ->first();

        // Obtener desglose por servicio desde ventas reales
        // Estructura: Venta → DetalleVenta → Perfil → Cuenta → Valor → Servicio
        $servicios = Venta::whereBetween('fechaven', [$fechas['inicio'], $fechas['fin']])
            ->join('detalles_venta', 'ventas.idven', '=', 'detalles_venta.idven')
            ->join('perfiles', 'detalles_venta.idper', '=', 'perfiles.idper')
            ->join('cuentas', 'perfiles.idcue', '=', 'cuentas.idcue')
            ->join('valores', 'cuentas.idval', '=', 'valores.idval')
            ->join('servicios', 'valores.idser', '=', 'servicios.idser')
            ->select(
                'servicios.nombreser as servicio',
                DB::raw('COUNT(DISTINCT ventas.idven) as cantidad'),
                DB::raw('SUM(detalles_venta.montodet) as total')
            )
            ->groupBy('servicios.nombreser')
            ->orderByDesc('total')
            ->get();

        // Obtener métodos de pago (a través de transacciones)
        $metodosPago = Venta::whereBetween('fechaven', [$fechas['inicio'], $fechas['fin']])
            ->join('transacciones', 'ventas.transaccion_id', '=', 'transacciones.id')
            ->join('bancos', 'transacciones.banco_id', '=', 'bancos.idban')
            ->select(
                'bancos.nombreban as metodo',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(transacciones.monto_transaccion) as total')
            )
            ->groupBy('bancos.nombreban')
            ->orderByDesc('total')
            ->get();

        return [
            'periodo' => $periodo,
            'fecha_inicio' => $fechas['inicio']->format('Y-m-d'),
            'fecha_fin' => $fechas['fin']->format('Y-m-d'),
            'total_ventas' => round($estadisticas->total_ventas ?? 0, 2),
            'cantidad_ventas' => $estadisticas->cantidad_ventas ?? 0,
            'promedio_venta' => round($estadisticas->promedio_venta ?? 0, 2),
            'total_costos' => round($estadisticas->total_costos ?? 0, 2),
            'total_gastos' => round($estadisticas->total_gastos ?? 0, 2),
            'ganancia_neta' => round(($estadisticas->total_ventas ?? 0) - ($estadisticas->total_costos ?? 0) - ($estadisticas->total_gastos ?? 0), 2),
            'clientes_nuevos' => $estadisticas->clientes_nuevos ?? 0,
            'servicios' => $servicios,
            'metodos_pago' => $metodosPago
        ];
    }

    /**
     * Obtener facturas/ventas pendientes de pago
     * Usa ViewUsuarioActivo para obtener cuentas activas cuya fecha_vencimiento
     * esté próxima (3 días), sea hoy, o ya esté atrasada
     *
     * @param int|null $diasProximos Días hacia adelante para considerar (default 3)
     * @param string|null $servicio ID del servicio para filtrar
     * @return array
     */
    public function facturasPendientes($diasProximos = 3, $servicio = null)
    {
        $hoy = Carbon::now();
        $fechaLimite = $hoy->copy()->addDays($diasProximos);

        // Usar ViewUsuarioActivo: incluye solo cuentas activas (activodet = 1)
        // Filtrar por fecha_vencimiento <= hoy + diasProximos
        $query = ViewUsuarioActivo::with([
            'cliente',
            'detalle_venta.perfil.cuenta.valor.servicio',
            'venta'
        ])
        ->where('fecha_vencimiento', '<=', $fechaLimite);

        if ($servicio) {
            $query->whereHas('detalle_venta.perfil.cuenta.valor.servicio', function($q) use ($servicio) {
                $q->where('idser', $servicio);
            });
        }

        $facturas = $query->orderBy('fecha_vencimiento', 'asc')->get();

        $facturasProcesadas = $facturas->map(function($usuario) use ($hoy) {
            $fechaVencimiento = Carbon::parse($usuario->fecha_vencimiento);
            $diasAtraso = $fechaVencimiento->isPast() ? $fechaVencimiento->diffInDays($hoy) : 0;
            $diasRestantes = $fechaVencimiento->isFuture() ? $hoy->diffInDays($fechaVencimiento) : 0;

            $detalle = $usuario->detalle_venta;
            $servicio = $detalle?->perfil?->cuenta?->valor?->servicio?->nombreser ?? 'N/A';
            $cliente = $usuario->cliente;

            return [
                'id_detalle' => $usuario->iddet,
                'id_venta' => $usuario->idven,
                'id_cliente' => $usuario->idcli,
                'cliente' => $usuario->nombre_cliente,
                'email' => $cliente?->email ?? '',
                'telefono' => $cliente?->telefonocli ?? '',
                'perfil' => $usuario->perfil,
                'servicio' => $servicio,
                'monto' => round($detalle?->montodet ?? 0, 2),
                'fecha_venta' => $usuario->venta?->fechaven?->format('Y-m-d') ?? 'N/A',
                'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                'dias_atraso' => $diasAtraso,
                'dias_restantes' => $diasRestantes,
                'estado_pago' => $fechaVencimiento->isPast() ? 'ATRASADO' : ($fechaVencimiento->isToday() ? 'VENCE_HOY' : 'PROXIMO')
            ];
        });

        // Agrupar por estado
        $atrasados = $facturasProcesadas->where('estado_pago', 'ATRASADO');
        $vencenHoy = $facturasProcesadas->where('estado_pago', 'VENCE_HOY');
        $proximos = $facturasProcesadas->where('estado_pago', 'PROXIMO');

        return [
            'total_pendientes' => $facturasProcesadas->count(),
            'monto_total' => round($facturasProcesadas->sum('monto'), 2),
            'promedio_dias_atraso' => round($atrasados->avg('dias_atraso') ?? 0, 0),
            'resumen' => [
                'atrasados' => $atrasados->count(),
                'vencen_hoy' => $vencenHoy->count(),
                'proximos' => $proximos->count(),
            ],
            'facturas' => $facturasProcesadas
        ];
    }

    /**
     * Obtener facturas que vencen HOY (para recordatorios)
     *
     * @param string|null $servicio ID del servicio para filtrar
     * @return array
     */
    public function facturasVencenHoy($servicio = null)
    {
        $hoy = Carbon::today();

        // Usar ViewUsuarioActivo: incluye solo cuentas activas
        // Filtrar solo por fecha_vencimiento = hoy
        $query = ViewUsuarioActivo::with([
            'cliente',
            'detalle_venta.perfil.cuenta.valor.servicio',
            'venta'
        ])
        ->whereDate('fecha_vencimiento', $hoy);

        if ($servicio) {
            $query->whereHas('detalle_venta.perfil.cuenta.valor.servicio', function($q) use ($servicio) {
                $q->where('idser', $servicio);
            });
        }

        $facturas = $query->orderBy('nombre_cliente', 'asc')->get();

        $facturasProcesadas = $facturas->map(function($usuario) {
            $fechaVencimiento = Carbon::parse($usuario->fecha_vencimiento);
            $detalle = $usuario->detalle_venta;
            $servicio = $detalle?->perfil?->cuenta?->valor?->servicio?->nombreser ?? 'N/A';
            $cliente = $usuario->cliente;

            return [
                'id_detalle' => $usuario->iddet,
                'id_venta' => $usuario->idven,
                'id_cliente' => $usuario->idcli,
                'cliente' => $usuario->nombre_cliente,
                'email' => $cliente?->email ?? '',
                'telefono' => $cliente?->telefonocli ?? '',
                'perfil' => $usuario->perfil,
                'cuenta' => $usuario->idcue,
                'servicio' => $servicio,
                'monto' => round($detalle?->montodet ?? 0, 2),
                'fecha_venta' => $usuario->venta?->fechaven?->format('Y-m-d') ?? 'N/A',
                'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                'hora_vencimiento' => $fechaVencimiento->format('H:i:s'),
            ];
        });

        return [
            'fecha' => $hoy->format('Y-m-d'),
            'total_facturas' => $facturasProcesadas->count(),
            'monto_total' => round($facturasProcesadas->sum('monto'), 2),
            'facturas' => $facturasProcesadas->values()
        ];
    }

    /**
     * Obtener facturas/usuarios vencidos para quitar
     * (fecha de vencimiento: ayer o antes)
     *
     * @param string|null $servicio ID del servicio para filtrar
     * @return array
     */
    public function facturasVencidasParaQuitar($servicio = null)
    {
        $hoy = Carbon::today();
        $fechaCorte = Carbon::yesterday();

        // Usar ViewUsuarioActivo: incluye solo cuentas activas
        // Filtrar vencidas: fecha_vencimiento <= ayer
        $query = ViewUsuarioActivo::with([
            'cliente',
            'detalle_venta.perfil.cuenta.valor.servicio',
            'venta'
        ])
        ->whereDate('fecha_vencimiento', '<=', $fechaCorte);

        if ($servicio) {
            $query->whereHas('detalle_venta.perfil.cuenta.valor.servicio', function($q) use ($servicio) {
                $q->where('idser', $servicio);
            });
        }

        $facturas = $query->orderBy('fecha_vencimiento', 'asc')->get();

        $facturasProcesadas = $facturas->map(function($usuario) use ($hoy) {
            $fechaVencimiento = Carbon::parse($usuario->fecha_vencimiento);
            $dias_atraso = $fechaVencimiento->diffInDays($hoy);
            $detalle = $usuario->detalle_venta;
            $servicio = $detalle?->perfil?->cuenta?->valor?->servicio?->nombreser ?? 'N/A';
            $cliente = $usuario->cliente;

            return [
                'id_detalle' => $usuario->iddet,
                'id_venta' => $usuario->idven,
                'id_cliente' => $usuario->idcli,
                'cliente' => $usuario->nombre_cliente,
                'email' => $cliente?->email ?? '',
                'telefono' => $cliente?->telefonocli ?? '',
                'perfil' => $usuario->perfil,
                'cuenta' => $usuario->idcue,
                'servicio' => $servicio,
                'monto' => round($detalle?->montodet ?? 0, 2),
                'fecha_venta' => $usuario->venta?->fechaven?->format('Y-m-d') ?? 'N/A',
                'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                'dias_atraso' => $dias_atraso,
            ];
        });

        return [
            'fecha_hoy' => $hoy->format('Y-m-d'),
            'fecha_corte' => $fechaCorte->format('Y-m-d'),
            'total_facturas' => $facturasProcesadas->count(),
            'monto_total' => round($facturasProcesadas->sum('monto'), 2),
            'facturas' => $facturasProcesadas->values()
        ];
    }

    /**
     * Evaluar cuentas que conviene renovar o no renovar.
     * Considera cuentas cuya fecha de vencimiento sea <= hoy + 2 días.
     * Para rentabilidad, cuenta usuarios activos con fecha_vencimiento > hoy
     * y compara contra pantminval.
     *
     * @param string|null $servicio ID del servicio para filtrar
     * @return array
     */
    public function evaluarCuentasRenovacion($servicio = null)
    {
        $hoy = Carbon::today();
        $fechaLimite = $hoy->copy()->addDays(2);

        $query = Cuenta::with(['valor.servicio'])
            ->where('activocue', true)
            ->whereDate('fechavencue', '<=', $fechaLimite);

        if ($servicio) {
            $query->whereHas('valor.servicio', function ($q) use ($servicio) {
                $q->where('idser', $servicio);
            });
        }

        $cuentas = $query->orderBy('fechavencue', 'asc')->get();

        if ($cuentas->isEmpty()) {
            return [
                'fecha_hoy' => $hoy->format('Y-m-d'),
                'fecha_limite_evaluacion' => $fechaLimite->format('Y-m-d'),
                'total_cuentas_evaluadas' => 0,
                'totales' => [
                    'a_renovar' => 0,
                    'no_renovar' => 0,
                ],
                'cuentas_a_renovar' => [],
                'cuentas_no_renovar' => [],
            ];
        }

        $idsCuentas = $cuentas->pluck('idcue')->values();

        $usuariosActivosPorCuenta = ViewUsuarioActivo::query()
            ->select('idcue', DB::raw('COUNT(*) as total_activos'))
            ->whereIn('idcue', $idsCuentas)
            ->whereDate('fecha_vencimiento', '>', $hoy)
            ->groupBy('idcue')
            ->pluck('total_activos', 'idcue');

        $procesadas = $cuentas->map(function ($cuenta) use ($usuariosActivosPorCuenta, $hoy) {
            $totalUsuariosActivos = (int) ($usuariosActivosPorCuenta[$cuenta->idcue] ?? 0);
            $minimoRentable = (int) ($cuenta->valor->pantminval ?? 0);
            $convieneRenovar = $totalUsuariosActivos >= $minimoRentable;

            $fechaVencimiento = Carbon::parse($cuenta->fechavencue);
            $diasParaVencer = $hoy->diffInDays($fechaVencimiento, false);

            return [
                'idcue' => $cuenta->idcue,
                'usuario' => $cuenta->usuariocue,
                'contraseña' => $cuenta->contrasenacue,
                'servicio_id' => $cuenta->valor->servicio->idser ?? null,
                'servicio' => $cuenta->valor->servicio->nombreser ?? 'N/A',
                'fecha_vencimiento_cuenta' => $fechaVencimiento->format('Y-m-d'),
                'dias_para_vencer' => $diasParaVencer,
                'total_usuarios_activos' => $totalUsuariosActivos,
                'total_minimo_usuarios_rentable' => $minimoRentable,
                'conviene_renovar' => $convieneRenovar,
            ];
        });

        $cuentasARenovar = $procesadas->where('conviene_renovar', true)->values();
        $cuentasNoRenovar = $procesadas->where('conviene_renovar', false)->values();

        return [
            'fecha_hoy' => $hoy->format('Y-m-d'),
            'fecha_limite_evaluacion' => $fechaLimite->format('Y-m-d'),
            'total_cuentas_evaluadas' => $procesadas->count(),
            'totales' => [
                'a_renovar' => $cuentasARenovar->count(),
                'no_renovar' => $cuentasNoRenovar->count(),
            ],
            'cuentas_a_renovar' => $cuentasARenovar,
            'cuentas_no_renovar' => $cuentasNoRenovar,
        ];
    }

    /**
     * Evaluar usuarios que habría que mudar desde cuentas NO renovables,
     * considerando capacidad disponible en otras cuentas activas y, si hace
     * falta, rescatando cuentas no renovables para renovarlas.
     *
     * NOTA: Este método NO ejecuta cambios, solo genera una propuesta.
     *
     * @param string|null $servicio ID del servicio para filtrar
     * @return array
     */
    public function evaluarUsuariosParaMudanza($servicio = null)
    {
        $hoy = Carbon::today();

        // 1) Base: cuentas a renovar / no renovar (misma lógica existente)
        $evaluacionRenovacion = $this->evaluarCuentasRenovacion($servicio);
        $cuentasNoRenovar = collect($evaluacionRenovacion['cuentas_no_renovar'] ?? []);

        if ($cuentasNoRenovar->isEmpty()) {
            return [
                'fecha_hoy' => $hoy->format('Y-m-d'),
                'servicio_filtro' => $servicio,
                'resumen' => [
                    'total_cuentas_no_renovar' => 0,
                    'total_usuarios_en_cuentas_no_renovar' => 0,
                    'total_usuarios_a_mudar' => 0,
                    'total_espacios_disponibles_sin_rescate' => 0,
                    'deficit_inicial' => 0,
                    'cuentas_rescatadas' => 0,
                    'plan_completo' => true,
                ],
                'por_servicio' => [],
                'cuentas_no_renovar' => [],
                'cuentas_rescatadas_para_renovar' => [],
                'usuarios_a_mudar' => [],
            ];
        }

        $cuentasNoRenovarIds = $cuentasNoRenovar->pluck('idcue')->values();
        $mapCuentaServicio = $cuentasNoRenovar
            ->pluck('servicio_id', 'idcue')
            ->toArray();

        // 2) Usuarios activos (vigentes) dentro de cuentas no renovables
        $usuariosNoRenovar = ViewUsuarioActivo::query()
            ->whereIn('idcue', $cuentasNoRenovarIds)
            ->whereDate('fecha_vencimiento', '>', $hoy)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get()
            ->map(function ($usuario) use ($mapCuentaServicio) {
                return [
                    'id_detalle' => $usuario->iddet,
                    'id_cliente' => $usuario->idcli,
                    'cliente' => $usuario->nombre_cliente,
                    'idcue_origen' => $usuario->idcue,
                    'perfil' => $usuario->perfil,
                    'fecha_vencimiento' => Carbon::parse($usuario->fecha_vencimiento)->format('Y-m-d'),
                    'servicio_id' => $mapCuentaServicio[$usuario->idcue] ?? null,
                ];
            });

        // 3) Cuentas destino iniciales: activas, fuera de las NO renovables,
        //    con espacios disponibles (usuarios_activos < pantmaxval)
        $queryCuentasDestino = Cuenta::with(['valor.servicio'])
            ->where('activocue', true)
            ->whereNotIn('idcue', $cuentasNoRenovarIds);

        if ($servicio) {
            $queryCuentasDestino->whereHas('valor.servicio', function ($q) use ($servicio) {
                $q->where('idser', $servicio);
            });
        }

        $cuentasDestino = $queryCuentasDestino->get();

        $idsDestino = $cuentasDestino->pluck('idcue')->values();
        $activosEnDestino = ViewUsuarioActivo::query()
            ->select('idcue', DB::raw('COUNT(*) as total_activos'))
            ->whereIn('idcue', $idsDestino)
            ->whereDate('fecha_vencimiento', '>', $hoy)
            ->groupBy('idcue')
            ->pluck('total_activos', 'idcue');

        $destinosConCapacidad = $cuentasDestino->map(function ($cuenta) use ($activosEnDestino) {
            $activos = (int) ($activosEnDestino[$cuenta->idcue] ?? 0);
            $pantMax = (int) ($cuenta->valor->pantmaxval ?? 0);
            $espacios = max(0, $pantMax - $activos);

            return [
                'idcue' => $cuenta->idcue,
                'usuario' => $cuenta->usuariocue,
                'servicio_id' => $cuenta->valor->servicio->idser ?? null,
                'servicio' => $cuenta->valor->servicio->nombreser ?? 'N/A',
                'pantmax' => $pantMax,
                'usuarios_activos' => $activos,
                'espacios_disponibles' => $espacios,
            ];
        })->filter(fn ($c) => $c['espacios_disponibles'] > 0)->values();

        // 4) Preparar cuentas no renovables como candidatas de rescate
        $activosEnNoRenovar = ViewUsuarioActivo::query()
            ->select('idcue', DB::raw('COUNT(*) as total_activos'))
            ->whereIn('idcue', $cuentasNoRenovarIds)
            ->whereDate('fecha_vencimiento', '>', $hoy)
            ->groupBy('idcue')
            ->pluck('total_activos', 'idcue');

        $candidatasRescate = $cuentasNoRenovar->map(function ($cuenta) use ($activosEnNoRenovar) {
            $activos = (int) ($activosEnNoRenovar[$cuenta['idcue']] ?? 0);
            // En cuentas no renovar ya tenemos mínimo rentable; usamos pantmax real
            $pantMax = (int) (Cuenta::with('valor')->find($cuenta['idcue'])?->valor?->pantmaxval ?? 0);
            $espacios = max(0, $pantMax - $activos);

            return [
                'idcue' => $cuenta['idcue'],
                'servicio_id' => $cuenta['servicio_id'] ?? null,
                'servicio' => $cuenta['servicio'] ?? 'N/A',
                'fecha_vencimiento_cuenta' => $cuenta['fecha_vencimiento_cuenta'] ?? null,
                'usuarios_activos' => $activos,
                'pantmax' => $pantMax,
                'espacios_disponibles' => $espacios,
            ];
        })->values();

        // 5) Evaluación por servicio: espacios vs usuarios a mudar
        $servicios = $usuariosNoRenovar->pluck('servicio_id')
            ->filter()
            ->unique()
            ->values();

        $porServicio = collect();
        $rescatadas = collect();

        foreach ($servicios as $servicioId) {
            $usuariosServicio = $usuariosNoRenovar->where('servicio_id', $servicioId)->values();
            $destinosServicio = $destinosConCapacidad->where('servicio_id', $servicioId)->values();
            $candidatasServicio = $candidatasRescate
                ->where('servicio_id', $servicioId)
                ->sortByDesc('pantmax')
                ->values();

            $usuariosAMudar = $usuariosServicio->count();
            $espaciosSinRescate = (int) $destinosServicio->sum('espacios_disponibles');
            $deficit = max(0, $usuariosAMudar - $espaciosSinRescate);

            $rescatadasServicio = collect();

            // Si faltan espacios, "rescatar" cuentas no renovables para renovar
            // y así mantener usuarios / liberar capacidad adicional.
            if ($deficit > 0) {
                foreach ($candidatasServicio as $candidata) {
                    $rescatadasServicio->push($candidata);
                    // Ganancia neta por rescatar una cuenta = pantmax
                    $deficit -= (int) $candidata['pantmax'];
                    if ($deficit <= 0) {
                        break;
                    }
                }
            }

            $rescatadas = $rescatadas->merge($rescatadasServicio);

            $porServicio->push([
                'servicio_id' => $servicioId,
                'servicio' => $usuariosServicio->first()['servicio_id']
                    ? ($destinosServicio->first()['servicio'] ?? $candidatasServicio->first()['servicio'] ?? 'N/A')
                    : 'N/A',
                'usuarios_en_no_renovar' => $usuariosAMudar,
                'espacios_disponibles_sin_rescate' => $espaciosSinRescate,
                'deficit_inicial' => max(0, $usuariosAMudar - $espaciosSinRescate),
                'cuentas_rescatadas_requeridas' => $rescatadasServicio->pluck('idcue')->values(),
                'espacios_extra_por_rescate' => (int) $rescatadasServicio->sum('espacios_disponibles'),
                'plan_completo' => $deficit <= 0,
            ]);
        }

        $idsRescatadas = $rescatadas->pluck('idcue')->unique()->values();

        // 6) Usuarios que efectivamente hay que mover (excluye cuentas rescatadas)
        $usuariosAMover = $usuariosNoRenovar
            ->reject(fn ($u) => $idsRescatadas->contains($u['idcue_origen']))
            ->values();

        // 7) Pool final de destino = destinos originales + cuentas rescatadas con espacios
        $poolDestino = $destinosConCapacidad->map(function ($d) {
            $d['es_rescatada'] = false;
            return $d;
        })->values();

        $rescatadasComoDestino = $rescatadas->map(function ($r) {
            return [
                'idcue' => $r['idcue'],
                'usuario' => null,
                'servicio_id' => $r['servicio_id'],
                'servicio' => $r['servicio'],
                'pantmax' => $r['pantmax'],
                'usuarios_activos' => $r['usuarios_activos'],
                'espacios_disponibles' => $r['espacios_disponibles'],
                'es_rescatada' => true,
            ];
        })->filter(fn ($r) => $r['espacios_disponibles'] > 0)->values();

        $poolDestino = $poolDestino->merge($rescatadasComoDestino)->values();

        // 8) Propuesta de asignación (greedy) por servicio
        $asignaciones = collect();
        $destinoMutable = $poolDestino->map(fn ($d) => $d)->values();

        foreach ($usuariosAMover as $usuario) {
            $idx = $destinoMutable->search(function ($dest) use ($usuario) {
                return $dest['servicio_id'] === $usuario['servicio_id']
                    && $dest['espacios_disponibles'] > 0;
            });

            if ($idx === false) {
                $asignaciones->push(array_merge($usuario, [
                    'idcue_destino_sugerido' => null,
                    'asignable' => false,
                ]));
                continue;
            }

            $dest = $destinoMutable[$idx];
            $dest['espacios_disponibles'] = $dest['espacios_disponibles'] - 1;
            $destinoMutable[$idx] = $dest;

            $asignaciones->push(array_merge($usuario, [
                'idcue_destino_sugerido' => $dest['idcue'],
                'destino_es_rescatada' => $dest['es_rescatada'],
                'asignable' => true,
            ]));
        }

        $noAsignables = $asignaciones->where('asignable', false)->count();

        return [
            'fecha_hoy' => $hoy->format('Y-m-d'),
            'servicio_filtro' => $servicio,
            'resumen' => [
                'total_cuentas_no_renovar' => $cuentasNoRenovar->count(),
                'total_usuarios_en_cuentas_no_renovar' => $usuariosNoRenovar->count(),
                'total_usuarios_a_mudar' => $usuariosAMover->count(),
                'total_espacios_disponibles_sin_rescate' => (int) $destinosConCapacidad->sum('espacios_disponibles'),
                'deficit_inicial' => max(0, $usuariosNoRenovar->count() - (int) $destinosConCapacidad->sum('espacios_disponibles')),
                'cuentas_rescatadas' => $idsRescatadas->count(),
                'usuarios_no_asignables' => $noAsignables,
                'plan_completo' => $noAsignables === 0,
            ],
            'por_servicio' => $porServicio->values(),
            'cuentas_no_renovar' => $cuentasNoRenovar->values(),
            'cuentas_rescatadas_para_renovar' => $rescatadas->unique('idcue')->values(),
            'usuarios_a_mudar' => $asignaciones->values(),
        ];
    }

    /**
     * Análisis de ingresos por servicio
     *
     * @param string $periodo
     * @param string|null $fecha
     * @return array
     */
    public function ingresosPorServicio($periodo = 'monthly', $fecha = null)
    {
        $fechas = $this->calcularFechas($periodo, $fecha);

        $servicios = Venta::whereBetween('fechaven', [$fechas['inicio'], $fechas['fin']])
            ->join('detalles_venta', 'ventas.idven', '=', 'detalles_venta.idven')
            ->join('perfiles', 'detalles_venta.idper', '=', 'perfiles.idper')
            ->join('cuentas', 'perfiles.idcue', '=', 'cuentas.idcue')
            ->join('valores', 'cuentas.idval', '=', 'valores.idval')
            ->join('servicios', 'valores.idser', '=', 'servicios.idser')
            ->select(
                'servicios.idser',
                'servicios.nombreser as servicio',
                DB::raw('COUNT(DISTINCT ventas.idven) as cantidad_ventas'),
                DB::raw('SUM(detalles_venta.montodet) as ingresos'),
                DB::raw('COUNT(DISTINCT ventas.idcli) as clientes_unicos')
            )
            ->groupBy('servicios.idser', 'servicios.nombreser')
            ->orderByDesc('ingresos')
            ->get();

        $totalGeneral = $servicios->sum('ingresos');

        $serviciosConPorcentaje = $servicios->map(function($servicio) use ($totalGeneral) {
            return [
                'servicio' => $servicio->servicio,
                'cantidad_ventas' => $servicio->cantidad_ventas,
                'ingresos' => round($servicio->ingresos, 2),
                'clientes_unicos' => $servicio->clientes_unicos,
                'porcentaje' => $totalGeneral > 0 ? round(($servicio->ingresos / $totalGeneral) * 100, 2) : 0,
                'promedio_por_venta' => round($servicio->ingresos / ($servicio->cantidad_ventas ?: 1), 2)
            ];
        });

        return [
            'periodo' => $periodo,
            'fecha_inicio' => $fechas['inicio']->format('Y-m-d'),
            'fecha_fin' => $fechas['fin']->format('Y-m-d'),
            'total_general' => round($totalGeneral, 2),
            'cantidad_servicios' => $servicios->count(),
            'servicios' => $serviciosConPorcentaje
        ];
    }

    /**
     * Listado de clientes morosos (con pagos vencidos)
     * Usa ViewUsuarioActivo para obtener solo cuentas activas vencidas
     *
     * @param int $diasMinimos Días mínimos de atraso para considerar moroso
     * @return array
     */
    public function clientesMorosos($diasMinimos = 3)
    {
        $fechaLimite = Carbon::now()->subDays($diasMinimos);

        // Obtener de ViewUsuarioActivo los usuarios con fecha_vencimiento atrasada
        $usuariosVencidos = ViewUsuarioActivo::with([
            'cliente',
            'detalle_venta.perfil.cuenta.valor.servicio'
        ])
        ->where('fecha_vencimiento', '<=', $fechaLimite)
        ->get();

        // Agrupar por cliente
        $clientesAgrupados = $usuariosVencidos->groupBy('idcli');

        $clientesProcesados = $clientesAgrupados->map(function($usuarios, $idcli) {
            $primerUsuario = $usuarios->first();
            $cliente = $primerUsuario->cliente;

            $deudaTotal = $usuarios->sum(function($usuario) {
                return $usuario->detalle_venta?->montodet ?? 0;
            });

            $diasMaxAtraso = $usuarios->max(function($usuario) {
                return Carbon::parse($usuario->fecha_vencimiento)->diffInDays(Carbon::now());
            });

            $servicios = $usuarios->map(function($usuario) {
                return $usuario->detalle_venta?->perfil?->cuenta?->valor?->servicio?->nombreser ?? 'N/A';
            })->unique()->values()->toArray();

            return [
                'id' => $cliente->idcli ?? $idcli,
                'nombre' => $cliente->nombrecli ?? $primerUsuario->nombre_cliente,
                'email' => $cliente->email ?? '',
                'telefono' => $cliente->telefonocli ?? '',
                'cuentas_vencidas' => $usuarios->count(),
                'deuda_total' => round($deudaTotal, 2),
                'dias_max_atraso' => $diasMaxAtraso,
                'servicios' => $servicios,
                'ultima_venta' => Venta::where('idcli', $idcli)->latest('fechaven')->first()?->fechaven?->format('Y-m-d')
            ];
        })->sortByDesc('deuda_total')->values();

        return [
            'total_morosos' => $clientesProcesados->count(),
            'monto_total_deuda' => round($clientesProcesados->sum('deuda_total'), 2),
            'promedio_deuda' => round($clientesProcesados->avg('deuda_total') ?? 0, 2),
            'clientes' => $clientesProcesados
        ];
    }

    /**
     * Proyección de ingresos basado en renovaciones esperadas
     * Usa ViewUsuarioActivo para obtener cuentas activas que vencen en el período
     *
     * @param string $periodo: next_week, next_month
     * @return array
     */
    public function proyeccionIngresos($periodo = 'next_month')
    {
        $fechas = $this->calcularFechasProyeccion($periodo);

        // Usar ViewUsuarioActivo: cuentas activas que vencen en el período proyectado
        $renovacionesEsperadas = ViewUsuarioActivo::with([
            'detalle_venta.perfil.cuenta.valor.servicio'
        ])
        ->whereBetween('fecha_vencimiento', [$fechas['inicio'], $fechas['fin']])
        ->get();

        $desglosePorServicio = $renovacionesEsperadas->groupBy(function($usuario) {
            return $usuario->detalle_venta?->perfil?->cuenta?->valor?->servicio?->nombreser ?? 'Otro';
        })->map(function($usuarios, $servicio) {
            $montoTotal = $usuarios->sum(function($usuario) {
                return $usuario->detalle_venta?->montodet ?? 0;
            });

            return [
                'servicio' => $servicio,
                'cantidad' => $usuarios->count(),
                'monto_estimado' => round($montoTotal, 2)
            ];
        })->values();

        $ingresoEstimado = $renovacionesEsperadas->sum(function($usuario) {
            return $usuario->detalle_venta?->montodet ?? 0;
        });

        $tasaRenovacion = $this->calcularTasaRenovacion();

        return [
            'periodo' => $periodo,
            'fecha_inicio' => $fechas['inicio']->format('Y-m-d'),
            'fecha_fin' => $fechas['fin']->format('Y-m-d'),
            'renovaciones_esperadas' => $renovacionesEsperadas->count(),
            'ingreso_estimado' => round($ingresoEstimado, 2),
            'tasa_renovacion_historica' => $tasaRenovacion,
            'ingreso_proyectado_real' => round($ingresoEstimado * ($tasaRenovacion / 100), 2),
            'desglose' => $desglosePorServicio
        ];
    }

    /**
     * Estadísticas de métodos de pago utilizados
     *
     * @param string $periodo
     * @return array
     */
    public function estadisticasMetodosPago($periodo = 'monthly')
    {
        $fechas = $this->calcularFechas($periodo);

        $metodosPago = Venta::whereBetween('fechaven', [$fechas['inicio'], $fechas['fin']])
            ->join('transacciones', 'ventas.transaccion_id', '=', 'transacciones.id')
            ->join('bancos', 'transacciones.banco_id', '=', 'bancos.idban')
            ->select(
                'bancos.idban',
                'bancos.nombreban as metodo',
                'bancos.tipoban as tipo',
                DB::raw('COUNT(*) as cantidad_transacciones'),
                DB::raw('SUM(transacciones.monto_transaccion) as monto_total'),
                DB::raw('AVG(transacciones.monto_transaccion) as monto_promedio')
            )
            ->groupBy('bancos.idban', 'bancos.nombreban', 'bancos.tipoban')
            ->orderByDesc('monto_total')
            ->get();

        $totalTransacciones = $metodosPago->sum('cantidad_transacciones');
        $montoTotal = $metodosPago->sum('monto_total');

        $metodosProcesados = $metodosPago->map(function($metodo) use ($totalTransacciones, $montoTotal) {
            return [
                'metodo' => $metodo->metodo,
                'tipo' => $metodo->tipo,
                'cantidad_transacciones' => $metodo->cantidad_transacciones,
                'monto_total' => round($metodo->monto_total, 2),
                'monto_promedio' => round($metodo->monto_promedio, 2),
                'porcentaje_transacciones' => $totalTransacciones > 0 ? round(($metodo->cantidad_transacciones / $totalTransacciones) * 100, 2) : 0,
                'porcentaje_monto' => $montoTotal > 0 ? round(($metodo->monto_total / $montoTotal) * 100, 2) : 0
            ];
        });

        return [
            'periodo' => $periodo,
            'fecha_inicio' => $fechas['inicio']->format('Y-m-d'),
            'fecha_fin' => $fechas['fin']->format('Y-m-d'),
            'total_transacciones' => $totalTransacciones,
            'monto_total' => round($montoTotal, 2),
            'metodos_pago' => $metodosProcesados
        ];
    }

    /**
     * Reporte general completo
     *
     * @param string $periodo
     * @return array
     */
    public function reporteGeneral($periodo = 'monthly')
    {
        $resumen = $this->resumenVentas($periodo);
        $ingresosPorServicio = $this->ingresosPorServicio($periodo);
        $metodosPago = $this->estadisticasMetodosPago($periodo);
        $morosos = $this->clientesMorosos();
        $proyeccion = $this->proyeccionIngresos('next_month');

        // Obtener datos adicionales de DailyStatistics
        $fechas = $this->calcularFechas($periodo);
        $estadisticasDiarias = DailyStatistic::whereBetween('date', [$fechas['inicio'], $fechas['fin']])
            ->select(
                DB::raw('AVG(active_users) as promedio_usuarios_activos'),
                DB::raw('MAX(active_users) as max_usuarios_activos'),
                DB::raw('AVG(accounts) as promedio_cuentas'),
                DB::raw('SUM(new_customers) as total_clientes_nuevos')
            )
            ->first();

        return [
            'periodo' => $periodo,
            'fecha_inicio' => $fechas['inicio']->format('Y-m-d'),
            'fecha_fin' => $fechas['fin']->format('Y-m-d'),
            'generado_en' => Carbon::now()->format('Y-m-d H:i:s'),

            // Resumen Financiero
            'resumen_financiero' => [
                'ingresos_totales' => $resumen['total_ventas'],
                'costos_totales' => $resumen['total_costos'],
                'gastos_totales' => $resumen['total_gastos'],
                'ganancia_neta' => $resumen['ganancia_neta'],
                'margen_ganancia' => $resumen['total_ventas'] > 0
                    ? round(($resumen['ganancia_neta'] / $resumen['total_ventas']) * 100, 2)
                    : 0
            ],

            // Ventas
            'ventas' => [
                'cantidad_total' => $resumen['cantidad_ventas'],
                'promedio_por_venta' => $resumen['promedio_venta'],
                'clientes_nuevos' => $resumen['clientes_nuevos']
            ],

            // Servicios
            'servicios' => $ingresosPorServicio['servicios'],

            // Métodos de Pago
            'metodos_pago' => $metodosPago['metodos_pago'],

            // Clientes Morosos
            'clientes_morosos' => [
                'total' => $morosos['total_morosos'],
                'deuda_total' => $morosos['monto_total_deuda']
            ],

            // Proyección
            'proyeccion_proximo_mes' => [
                'renovaciones_esperadas' => $proyeccion['renovaciones_esperadas'],
                'ingreso_estimado' => $proyeccion['ingreso_estimado'],
                'ingreso_proyectado_real' => $proyeccion['ingreso_proyectado_real']
            ],

            // Métricas Operativas
            'metricas_operativas' => [
                'promedio_usuarios_activos' => round($estadisticasDiarias->promedio_usuarios_activos ?? 0, 0),
                'max_usuarios_activos' => $estadisticasDiarias->max_usuarios_activos ?? 0,
                'promedio_cuentas' => round($estadisticasDiarias->promedio_cuentas ?? 0, 0)
            ]
        ];
    }

    /**
     * Estadísticas generales de clientes
     * Usa ViewUsuarioActivo para determinar clientes con cuentas activas
     *
     * @return array
     */
    public function estadisticasClientes()
    {
        $totalClientes = Cliente::count();

        // Clientes activos: tienen al menos una cuenta en ViewUsuarioActivo
        $clientesActivosIds = ViewUsuarioActivo::distinct('idcli')->pluck('idcli');
        $clientesActivos = $clientesActivosIds->count();
        $clientesInactivos = $totalClientes - $clientesActivos;

        $clientesConSaldo = Cliente::where('saldo', '>', 0)->count();
        $saldoTotalClientes = Cliente::sum('saldo');

        // Top 10 clientes por facturación (suma de montos en detalles_venta)
        $topClientes = Cliente::select('clientes.*')
            ->join('ventas', 'clientes.idcli', '=', 'ventas.idcli')
            ->join('detalles_venta', 'ventas.idven', '=', 'detalles_venta.idven')
            ->selectRaw('clientes.idcli, clientes.nombrecli, clientes.email, clientes.saldo, SUM(detalles_venta.montodet) as total_facturado')
            ->groupBy('clientes.idcli', 'clientes.nombrecli', 'clientes.email', 'clientes.saldo')
            ->orderByDesc('total_facturado')
            ->limit(10)
            ->get()
            ->map(function($cliente) {
                return [
                    'id' => $cliente->idcli,
                    'nombre' => $cliente->nombrecli,
                    'email' => $cliente->email,
                    'total_facturado' => round($cliente->total_facturado ?? 0, 2),
                    'saldo' => round($cliente->saldo, 2)
                ];
            });

        // Contar cuentas activas totales
        $cuentasActivasTotales = ViewUsuarioActivo::count();

        return [
            'total_clientes' => $totalClientes,
            'clientes_activos' => $clientesActivos,
            'clientes_inactivos' => $clientesInactivos,
            'porcentaje_activos' => $totalClientes > 0 ? round(($clientesActivos / $totalClientes) * 100, 2) : 0,
            'cuentas_activas_totales' => $cuentasActivasTotales,
            'clientes_con_saldo' => $clientesConSaldo,
            'saldo_total_clientes' => round($saldoTotalClientes, 2),
            'top_clientes' => $topClientes
        ];
    }

    // ========================================
    // MÉTODOS PRIVADOS / HELPERS
    // ========================================

    /**
     * Calcular fechas según período
     */
    private function calcularFechas($periodo, $fechaInicio = null, $fechaFin = null)
    {
        $ahora = Carbon::now();

        if ($periodo === 'custom' && $fechaInicio && $fechaFin) {
            return [
                'inicio' => Carbon::parse($fechaInicio),
                'fin' => Carbon::parse($fechaFin)
            ];
        }

        switch ($periodo) {
            case 'daily':
                return [
                    'inicio' => $ahora->copy()->startOfDay(),
                    'fin' => $ahora->copy()->endOfDay()
                ];

            case 'weekly':
                return [
                    'inicio' => $ahora->copy()->startOfWeek(),
                    'fin' => $ahora->copy()->endOfWeek()
                ];

            case 'monthly':
                return [
                    'inicio' => $ahora->copy()->startOfMonth(),
                    'fin' => $ahora->copy()->endOfMonth()
                ];

            case 'yearly':
                return [
                    'inicio' => $ahora->copy()->startOfYear(),
                    'fin' => $ahora->copy()->endOfYear()
                ];

            default:
                return [
                    'inicio' => $ahora->copy()->startOfMonth(),
                    'fin' => $ahora->copy()->endOfMonth()
                ];
        }
    }

    /**
     * Calcular fechas para proyección
     */
    private function calcularFechasProyeccion($periodo)
    {
        $ahora = Carbon::now();

        switch ($periodo) {
            case 'next_week':
                return [
                    'inicio' => $ahora->copy()->addDay(),
                    'fin' => $ahora->copy()->addWeek()
                ];

            case 'next_month':
                return [
                    'inicio' => $ahora->copy()->addDay(),
                    'fin' => $ahora->copy()->addMonth()
                ];

            default:
                return [
                    'inicio' => $ahora->copy()->addDay(),
                    'fin' => $ahora->copy()->addMonth()
                ];
        }
    }

    /**
     * Calcular tasa histórica de renovación
     * Compara detalles que vencieron vs los que siguen activos (activodet = 1)
     */
    private function calcularTasaRenovacion()
    {
        // Calcular basado en los últimos 3 meses
        $hace3Meses = Carbon::now()->subMonths(3);
        $hoy = Carbon::now();

        // Total de detalles que vencieron en los últimos 3 meses
        $detallesVencidos = DetalleVenta::where('fechavendet', '>=', $hace3Meses)
            ->where('fechavendet', '<=', $hoy)
            ->count();

        // De esos, contar cuántos siguen activos (fueron renovados)
        // activodet = 1 significa que el cliente sigue usando la cuenta
        $renovadas = DetalleVenta::where('fechavendet', '>=', $hace3Meses)
            ->where('fechavendet', '<=', $hoy)
            ->where('activodet', 1)
            ->count();

        if ($detallesVencidos == 0) {
            return 75; // Tasa por defecto
        }

        return round(($renovadas / $detallesVencidos) * 100, 2);
    }
}
