@php
    /*
     * Reporte mensual de Streamify.
     *
     * Todo lo que se imprime sale del MES SOLICITADO, incluidos los graficos:
     * antes llegaban como imagenes capturadas del dashboard en pantalla, asi
     * que un reporte de junio generado en septiembre mostraba las cifras de
     * septiembre. Ahora se dibujan aqui, con las series que devuelve
     * DashboardService para ese periodo.
     *
     * Los graficos son barras hechas con tablas y divs: dompdf no ejecuta
     * JavaScript ni renderiza canvas, y su soporte de SVG es limitado, pero
     * las tablas con fondos de color las imprime de forma fiable.
     */

    $fmt = fn($n, $d = 2) => number_format((float) $n, $d, ',', '.');

    // El signo va delante del simbolo, no entre medias: un saldo negativo se
    // escribe -$1.564,21 y no $-1.564,21.
    $money = function ($n, $d = 2) {
        $n = (float) $n;
        return ($n < 0 ? '-$' : '$') . number_format(abs($n), $d, ',', '.');
    };

    // Variacion mensual: se pinta en verde o rojo segun el signo.
    $delta = function ($v) use ($fmt) {
        if ($v === null) return null;
        return ($v >= 0 ? '+' : '') . $fmt($v, 1) . '%';
    };

    $serieDiaria = $serie_diaria ?? [];
    $serieMeses  = $serie_meses ?? [];
    $porServicio = $por_servicio ?? [];

    $maxDia = collect($serieDiaria)->max('ingresos') ?: 1;
    $maxMes = collect($serieMeses)->max('ingresos') ?: 1;
    $maxSrv = collect($porServicio)->max('ingresos') ?: 1;
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte mensual {{ ucfirst($mes) }} {{ $year }} — Streamify</title>
    <style>
        /* Margenes reales de documento. Antes no habia regla @page, asi que
           dompdf usaba su minimo y el contenido quedaba pegado al borde. El
           margen superior deja sitio a la cabecera repetida de cada pagina. */
        @page {
            margin: 26mm 16mm 20mm 16mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.45;
            color: #1c1c1a;
        }

        /* --- Cabecera y pie repetidos en todas las paginas --- */
        .doc-header {
            position: fixed;
            top: -18mm; left: 0; right: 0;
            border-bottom: 0.6pt solid #d8d4cb;
            padding-bottom: 5pt;
        }
        .doc-header .marca {
            font-size: 12pt; font-weight: bold; letter-spacing: -0.2pt; color: #274698;
        }
        .doc-header .periodo { float: right; font-size: 8pt; color: #6e6c66; padding-top: 3pt; }

        .doc-footer {
            position: fixed;
            bottom: -13mm; left: 0; right: 0;
            border-top: 0.6pt solid #d8d4cb;
            padding-top: 4pt;
            font-size: 7.5pt; color: #8a8884;
        }
        .doc-footer .der { float: right; }

        /* --- Portada del reporte --- */
        .titulo-bloque { margin-bottom: 14pt; }
        .eyebrow {
            font-size: 7.5pt; font-weight: bold; text-transform: uppercase;
            letter-spacing: 1.2pt; color: #8a8884; margin-bottom: 3pt;
        }
        h1 { font-size: 21pt; letter-spacing: -0.4pt; color: #14213d; margin-bottom: 2pt; }
        .subtitulo { font-size: 9pt; color: #6e6c66; }

        /* --- Cifra principal --- */
        .destacado {
            border: 0.8pt solid #274698;
            border-left: 3.5pt solid #274698;
            background: #f4f6fc;
            padding: 12pt 14pt;
            margin-bottom: 14pt;
        }
        .destacado .etq {
            font-size: 7.5pt; font-weight: bold; text-transform: uppercase;
            letter-spacing: 1pt; color: #274698;
        }
        .destacado .val { font-size: 26pt; font-weight: bold; color: #14213d; letter-spacing: -0.8pt; }
        .destacado .nota { font-size: 8.5pt; color: #55534e; }

        /* --- Secciones --- */
        h2 {
            font-size: 8.5pt; font-weight: bold; text-transform: uppercase;
            letter-spacing: 1.1pt; color: #14213d;
            border-bottom: 0.6pt solid #d8d4cb;
            padding-bottom: 3pt; margin: 16pt 0 8pt;
        }

        /* --- Rejilla de indicadores (tablas: dompdf no tiene flexbox) --- */
        table.kpis { width: 100%; border-collapse: separate; border-spacing: 5pt 0; margin-bottom: 4pt; }
        table.kpis td {
            width: 25%;
            border: 0.6pt solid #ddd9d0;
            padding: 7pt 8pt;
            vertical-align: top;
        }
        .kpi-etq {
            font-size: 6.8pt; font-weight: bold; text-transform: uppercase;
            letter-spacing: 0.7pt; color: #8a8884;
        }
        .kpi-val { font-size: 14pt; font-weight: bold; color: #1c1c1a; letter-spacing: -0.3pt; }
        .kpi-sub { font-size: 7.5pt; color: #6e6c66; }
        .sube { color: #0f7a45; font-weight: bold; }
        .baja { color: #b3261e; font-weight: bold; }

        /* --- Tablas de datos --- */
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 4pt; }
        table.datos th {
            font-size: 6.8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.6pt;
            color: #6e6c66; text-align: left;
            border-bottom: 0.8pt solid #b9b5ab;
            padding: 4pt 5pt;
        }
        table.datos td {
            font-size: 8.5pt;
            border-bottom: 0.4pt solid #e8e5dd;
            padding: 4pt 5pt;
        }
        table.datos tr.total td {
            font-weight: bold;
            border-top: 0.8pt solid #b9b5ab;
            border-bottom: none;
            background: #f6f4ef;
        }
        .der { text-align: right; }
        .cen { text-align: center; }
        .destacar { background: #f4f6fc; font-weight: bold; }

        /* --- Graficos de barras --- */
        .gr-titulo { font-size: 8pt; font-weight: bold; color: #14213d; margin-bottom: 5pt; }
        .gr-nota { font-size: 7.5pt; color: #8a8884; margin-top: 3pt; }

        /* Barras verticales: cada dia es una celda, con la barra pegada abajo. */
        table.gr-dias { width: 100%; border-collapse: collapse; }
        table.gr-dias td { vertical-align: bottom; text-align: center; padding: 0 0.4pt; }
        table.gr-dias .barra { background: #274698; width: 100%; }
        table.gr-dias .eje { font-size: 5.5pt; color: #8a8884; padding-top: 2pt; }

        /* Barras horizontales: etiqueta, barra proporcional y valor. */
        table.gr-barras { width: 100%; border-collapse: collapse; }
        table.gr-barras td { padding: 2.2pt 0; font-size: 8pt; vertical-align: middle; }
        table.gr-barras td.etq { width: 20%; color: #55534e; }
        table.gr-barras td.val { width: 18%; text-align: right; font-weight: bold; }
        .pista { background: #eeece6; width: 100%; }
        .relleno { height: 9pt; }

        .leyenda { font-size: 7.5pt; color: #6e6c66; margin-top: 4pt; }
        .leyenda .muestra { display: inline-block; width: 7pt; height: 7pt; margin-right: 2pt; }

        .aviso {
            font-size: 7.5pt; color: #6e6c66;
            background: #f6f4ef; border-left: 2pt solid #c9c5ba;
            padding: 5pt 7pt; margin-top: 5pt;
        }

        .salto { page-break-before: always; }
    </style>
</head>

<body>

    {{-- Cabecera y pie fijos: se repiten en cada pagina --}}
    <div class="doc-header">
        <span class="marca">Streamify</span>
        <span class="periodo">Reporte mensual · {{ ucfirst($mes) }} {{ $year }}</span>
    </div>

    <div class="doc-footer">
        Streamify HQ · Documento interno
        <span class="der">Generado el {{ now()->format('d/m/Y H:i') }}</span>
    </div>

    {{-- ── Portada ─────────────────────────────────────────────── --}}
    <div class="titulo-bloque">
        <div class="eyebrow">Reporte mensual de resultados</div>
        <h1>{{ ucfirst($mes) }} {{ $year }}</h1>
        <div class="subtitulo">
            Cierre del periodo con {{ $dias_con_datos ?? 0 }} días registrados.
            Comparado con {{ $comparativa['mes_anterior'] ?? 'el mes anterior' }}.
        </div>
    </div>

    <div class="destacado">
        <div class="etq">Utilidad real del mes</div>
        <div class="val">{{ $money($balance) }}</div>
        <div class="nota">
            {{ $fmt($balance_pct, 1) }}% de los ingresos
            @if ($delta($comparativa['utilidad'] ?? null))
                · <span class="{{ ($comparativa['utilidad'] ?? 0) >= 0 ? 'sube' : 'baja' }}">{{ $delta($comparativa['utilidad']) }}</span>
                frente a {{ $comparativa['mes_anterior'] }}
            @endif
        </div>
    </div>

    {{-- ── Indicadores ─────────────────────────────────────────── --}}
    <h2>Resultados del periodo</h2>
    <table class="kpis">
        <tr>
            <td>
                <div class="kpi-etq">Ingresos del mes</div>
                <div class="kpi-val">{{ $money($ingresos_mes) }}</div>
                <div class="kpi-sub">
                    @if ($delta($comparativa['ingresos'] ?? null))
                        <span class="{{ ($comparativa['ingresos'] ?? 0) >= 0 ? 'sube' : 'baja' }}">{{ $delta($comparativa['ingresos']) }}</span> vs. mes anterior
                    @else — @endif
                </div>
            </td>
            <td>
                <div class="kpi-etq">Ventas del mes</div>
                <div class="kpi-val">{{ $fmt($ventas_mes, 0) }}</div>
                <div class="kpi-sub">
                    @if ($delta($comparativa['ventas'] ?? null))
                        <span class="{{ ($comparativa['ventas'] ?? 0) >= 0 ? 'sube' : 'baja' }}">{{ $delta($comparativa['ventas']) }}</span> vs. mes anterior
                    @else — @endif
                </div>
            </td>
            <td>
                <div class="kpi-etq">Ticket promedio</div>
                <div class="kpi-val">{{ $money($ticket_promedio ?? 0) }}</div>
                <div class="kpi-sub">por venta</div>
            </td>
            <td>
                <div class="kpi-etq">Ingresos del año</div>
                <div class="kpi-val">{{ $money($ingresos_ano, 0) }}</div>
                <div class="kpi-sub">acumulado {{ $year }}</div>
            </td>
        </tr>
    </table>

    <h2>Clientes y usuarios al cierre del mes</h2>
    <table class="kpis">
        <tr>
            <td>
                <div class="kpi-etq">Clientes activos</div>
                <div class="kpi-val">{{ $fmt($clientes_activos, 0) }}</div>
                <div class="kpi-sub">
                    @if ($delta($comparativa['clientes'] ?? null))
                        <span class="{{ ($comparativa['clientes'] ?? 0) >= 0 ? 'sube' : 'baja' }}">{{ $delta($comparativa['clientes']) }}</span> vs. mes anterior
                    @else — @endif
                </div>
            </td>
            <td>
                <div class="kpi-etq">Clientes nuevos</div>
                <div class="kpi-val sube">{{ $fmt($clientes_nuevos ?? 0, 0) }}</div>
                <div class="kpi-sub">altas durante el mes</div>
            </td>
            <td>
                <div class="kpi-etq">Clientes perdidos</div>
                <div class="kpi-val {{ ($clientes_perdidos ?? 0) > 0 ? 'baja' : '' }}">{{ $fmt($clientes_perdidos ?? 0, 0) }}</div>
                <div class="kpi-sub">bajas durante el mes</div>
            </td>
            <td>
                <div class="kpi-etq">Usuarios activos</div>
                <div class="kpi-val">{{ $fmt($total_usuarios_activos, 0) }}</div>
                <div class="kpi-sub">
                    @if ($delta($comparativa['usuarios'] ?? null))
                        <span class="{{ ($comparativa['usuarios'] ?? 0) >= 0 ? 'sube' : 'baja' }}">{{ $delta($comparativa['usuarios']) }}</span> vs. mes anterior
                    @else — @endif
                </div>
            </td>
        </tr>
    </table>

    <h2>Operación al cierre del mes</h2>
    <table class="kpis">
        <tr>
            <td>
                <div class="kpi-etq">Cuentas activas</div>
                <div class="kpi-val">{{ $fmt($num_cuentas, 0) }}</div>
                <div class="kpi-sub">en servicio</div>
            </td>
            <td>
                <div class="kpi-etq">Espacios disponibles</div>
                <div class="kpi-val">{{ $fmt($espacios, 0) }}</div>
                <div class="kpi-sub">perfiles libres</div>
            </td>
            <td>
                <div class="kpi-etq">Cuentas caídas</div>
                <div class="kpi-val {{ $cuentas_caidas > 0 ? 'baja' : '' }}">{{ $fmt($cuentas_caidas, 0) }}</div>
                <div class="kpi-sub">requieren atención</div>
            </td>
            <td>
                <div class="kpi-etq">Pagos pendientes</div>
                <div class="kpi-val">{{ $fmt($usuarios_acobrar, 0) }}</div>
                <div class="kpi-sub">usuarios por cobrar</div>
            </td>
        </tr>
    </table>

    {{-- ── Resumen financiero ──────────────────────────────────── --}}
    <h2>Resumen financiero</h2>
    <table class="datos">
        <thead>
            <tr>
                <th style="width:50%">Concepto</th>
                <th class="der" style="width:25%">Monto</th>
                <th class="der" style="width:25%">% de ingresos</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ingresos</td>
                <td class="der">{{ $money($ingresos_mes) }}</td>
                <td class="der">100,0%</td>
            </tr>
            <tr>
                <td>Costos de las cuentas</td>
                <td class="der">−{{ $money($costos_mes) }}</td>
                <td class="der">{{ $fmt($costos_pct, 1) }}%</td>
            </tr>
            <tr>
                <td>Gastos operativos</td>
                <td class="der">−{{ $money($gastos_mes) }}</td>
                <td class="der">{{ $fmt($gastos_pct, 1) }}%</td>
            </tr>
            <tr class="total">
                <td>Utilidad real del negocio</td>
                <td class="der">{{ $money($balance) }}</td>
                <td class="der">{{ $fmt($balance_pct, 1) }}%</td>
            </tr>
        </tbody>
    </table>
    <div class="aviso">
        El retiro personal del mes ({{ $money($gastos_personal_mes) }}) no se resta de la utilidad real:
        es dinero que sale de esa utilidad, no un gasto del negocio.
    </div>

    @if (!empty($gastos))
        <h2>Desglose de gastos operativos</h2>
        <table class="datos">
            <thead>
                <tr>
                    <th style="width:50%">Concepto</th>
                    <th class="der" style="width:25%">Monto</th>
                    <th class="der" style="width:25%">% de ingresos</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($gastos as $g)
                    <tr>
                        <td>
                            {{ $g['concepto'] ?? '—' }}
                            @if (!empty($g['excluido_de_ganancia']))
                                <span style="color:#8a8884; font-size:7.5pt">(no afecta la utilidad)</span>
                            @endif
                        </td>
                        <td class="der">{{ $money($g['total'] ?? 0) }}</td>
                        <td class="der">{{ $fmt($g['porcentaje'] ?? 0, 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ── Graficos ────────────────────────────────────────────── --}}
    <div class="salto"></div>

    <h2>Evolución diaria de {{ ucfirst($mes) }}</h2>
    @if (count($serieDiaria))
        <div class="gr-titulo">Ingresos por día (USD)</div>
        <table class="gr-dias">
            <tr>
                @foreach ($serieDiaria as $d)
                    @php $alto = max(1, round(($d['ingresos'] / $maxDia) * 60)); @endphp
                    <td style="height:62pt">
                        <div class="barra" style="height:{{ $alto }}pt"></div>
                    </td>
                @endforeach
            </tr>
            <tr>
                @foreach ($serieDiaria as $d)
                    <td class="eje">{{ $d['dia'] % 5 === 0 || $d['dia'] === 1 ? $d['dia'] : '' }}</td>
                @endforeach
            </tr>
        </table>
        <div class="gr-nota">
            Día de mayor ingreso: {{ $money($maxDia) }} ·
            Promedio diario: {{ $money($promedio_pagos_mes) }}
        </div>
    @else
        <div class="aviso">No hay registros diarios para este mes.</div>
    @endif

    <h2>Tendencia de los últimos seis meses</h2>
    @if (count($serieMeses))
        <div class="gr-titulo">Ingresos y utilidad por mes (USD)</div>
        <table class="gr-barras">
            @foreach ($serieMeses as $m)
                @php
                    $pIng = $maxMes > 0 ? round(($m['ingresos'] / $maxMes) * 100) : 0;
                    $pUti = $maxMes > 0 ? round((max(0, $m['utilidad']) / $maxMes) * 100) : 0;
                @endphp
                <tr>
                    <td class="etq" rowspan="2">{{ $m['etiqueta'] }}</td>
                    <td>
                        <table class="pista"><tr><td class="relleno" style="width:{{ $pIng }}%; background:#274698"></td><td></td></tr></table>
                    </td>
                    <td class="val">{{ $money($m['ingresos'], 0) }}</td>
                </tr>
                <tr>
                    <td>
                        <table class="pista"><tr><td class="relleno" style="width:{{ $pUti }}%; background:#7f9bd4"></td><td></td></tr></table>
                    </td>
                    <td class="val" style="color:#55534e">{{ $money($m['utilidad'], 0) }}</td>
                </tr>
            @endforeach
        </table>
        <div class="leyenda">
            <span class="muestra" style="background:#274698"></span> Ingresos &nbsp;
            <span class="muestra" style="background:#7f9bd4"></span> Utilidad
        </div>
        <div class="gr-nota">
            La serie termina en {{ ucfirst($mes) }} {{ $year }}, el mes de este reporte.
        </div>
    @endif

    {{-- ── Resultados por servicio ─────────────────────────────── --}}
    <h2>Resultados por servicio</h2>
    @if (count($porServicio))
        <div class="gr-titulo">Participación en los ingresos del mes</div>
        <table class="gr-barras">
            @foreach ($porServicio as $s)
                @if ($s['ingresos'] > 0)
                    <tr>
                        <td class="etq">{{ $s['servicio'] }}</td>
                        <td>
                            <table class="pista"><tr>
                                <td class="relleno" style="width:{{ $maxSrv > 0 ? round(($s['ingresos'] / $maxSrv) * 100) : 0 }}%; background:#274698"></td><td></td>
                            </tr></table>
                        </td>
                        <td class="val">{{ $money($s['ingresos']) }}</td>
                    </tr>
                @endif
            @endforeach
        </table>

        <table class="datos" style="margin-top:10pt">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th class="der">Perfiles facturados</th>
                    <th class="der">Ingresos</th>
                    <th class="der">Costos</th>
                    <th class="der">Ganancia</th>
                    <th class="der">Margen</th>
                    <th class="der">% ingresos</th>
                </tr>
            </thead>
            <tbody>
                @php $tPerf = $tIng = $tCos = $tGan = 0; @endphp
                @foreach ($porServicio as $s)
                    @php
                        $tPerf += $s['perfiles']; $tIng += $s['ingresos'];
                        $tCos += $s['costos'];    $tGan += $s['ganancia'];
                    @endphp
                    <tr>
                        <td>{{ $s['servicio'] }}</td>
                        <td class="der">{{ $s['perfiles'] ? $fmt($s['perfiles'], 0) : '—' }}</td>
                        <td class="der">{{ $s['ingresos'] ? $money($s['ingresos']) : '—' }}</td>
                        <td class="der">{{ $s['costos'] ? $money($s['costos']) : '—' }}</td>
                        <td class="der">{{ $money($s['ganancia']) }}</td>
                        {{-- Sin ingresos el margen no significa nada: se omite. --}}
                        <td class="der">{{ $s['ingresos'] > 0 ? $fmt($s['margen'], 1) . '%' : '—' }}</td>
                        <td class="der">{{ $s['peso'] > 0 ? $fmt($s['peso'], 1) . '%' : '—' }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td>Total</td>
                    <td class="der">{{ $fmt($tPerf, 0) }}</td>
                    <td class="der">{{ $money($tIng) }}</td>
                    <td class="der">{{ $money($tCos) }}</td>
                    <td class="der">{{ $money($tGan) }}</td>
                    <td class="der">{{ $tIng > 0 ? $fmt(($tGan / $tIng) * 100, 1) . '%' : '—' }}</td>
                    <td class="der">100,0%</td>
                </tr>
            </tbody>
        </table>

        <div class="aviso">
            «Perfiles facturados» son los perfiles distintos con venta registrada dentro del mes, no los
            activos hoy. «Costos generales» agrupa los costos que no corresponden a un servicio concreto,
            por eso no tiene margen.
        </div>
    @else
        <div class="aviso">No hay ventas registradas para este mes.</div>
    @endif

</body>

</html>
