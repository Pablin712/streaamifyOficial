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

@php
    /*
     * Generadores de graficos en SVG.
     *
     * dompdf trae php-svg-lib, pero NO renderiza SVG en linea: hay que pasarlo
     * como imagen embebida (data:image/svg+xml;base64). Asi si funciona, y se
     * pueden hacer graficos de verdad -lineales, de barras y circulares- en vez
     * de las barras de tabla que se usaban antes.
     *
     * Solo se usan atributos de presentacion (fill, stroke...) y elementos
     * basicos: php-svg-lib no interpreta CSS dentro del SVG.
     */

    $svg = fn(string $cuerpo, int $w, int $h) =>
        'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">'
            . $cuerpo . '</svg>'
        );

    $AZUL   = '#274698';
    $AZULC  = '#8fa6d8';
    $ORO    = '#c9930a';
    $TINTA  = '#55534e';
    $REJILLA = '#e2ded5';

    /* ---------- Grafico lineal: evolucion diaria ---------- */
    $graficoLineal = function (array $serie) use ($svg, $AZUL, $AZULC, $TINTA, $REJILLA, $money) {
        if (count($serie) < 2) return null;

        // 504 = ancho util en puntos (178 mm). dompdf no escala el <img>
        // de un SVG, asi que se dibuja ya al tamano final.
        $w = 504; $h = 190;
        $mIzq = 46; $mDer = 10; $mSup = 10; $mInf = 24;
        $pw = $w - $mIzq - $mDer;
        $ph = $h - $mSup - $mInf;

        $ing = array_column($serie, 'ingresos');
        $uti = array_column($serie, 'utilidad');
        $max = max(max($ing), max($uti), 1);
        // Escala redondeada hacia arriba para que el eje tenga numeros limpios.
        $paso = pow(10, floor(log10($max))) / 2;
        $tope = ceil($max / $paso) * $paso;

        $n = count($serie);
        $x = fn($i) => $mIzq + ($n > 1 ? ($i / ($n - 1)) * $pw : 0);
        $y = fn($v) => $mSup + $ph - ($tope > 0 ? ($v / $tope) * $ph : 0);

        $out = '';

        // Rejilla horizontal y etiquetas del eje Y
        for ($k = 0; $k <= 4; $k++) {
            $v  = $tope * $k / 4;
            $yy = round($y($v), 1);
            $out .= '<line x1="' . $mIzq . '" y1="' . $yy . '" x2="' . ($w - $mDer) . '" y2="' . $yy . '" stroke="' . $REJILLA . '" stroke-width="1"/>';
        }

        // Area bajo ingresos
        $area = '';
        foreach ($serie as $i => $d) $area .= round($x($i), 1) . ',' . round($y($d['ingresos']), 1) . ' ';
        $area .= round($x($n - 1), 1) . ',' . round($y(0), 1) . ' ' . round($x(0), 1) . ',' . round($y(0), 1);
        $out .= '<polygon points="' . $area . '" fill="' . $AZULC . '" fill-opacity="0.25"/>';

        // Linea de utilidad (debajo, mas clara)
        $pu = '';
        foreach ($serie as $i => $d) $pu .= round($x($i), 1) . ',' . round($y($d['utilidad']), 1) . ' ';
        $out .= '<polyline points="' . trim($pu) . '" fill="none" stroke="' . $AZULC . '" stroke-width="1.6"/>';

        // Linea de ingresos
        $pi = '';
        foreach ($serie as $i => $d) $pi .= round($x($i), 1) . ',' . round($y($d['ingresos']), 1) . ' ';
        $out .= '<polyline points="' . trim($pi) . '" fill="none" stroke="' . $AZUL . '" stroke-width="2.2"/>';


        // Ejes
        $out .= '<line x1="' . $mIzq . '" y1="' . ($mSup + $ph) . '" x2="' . ($w - $mDer) . '" y2="' . ($mSup + $ph) . '" stroke="#b9b5ab" stroke-width="1"/>';

        return ['img' => $svg($out, $w, $h), 'tope' => $tope];
    };

    /* ---------- Grafico de barras: seis meses ---------- */
    $graficoBarras = function (array $meses) use ($svg, $AZUL, $AZULC, $TINTA, $REJILLA) {
        if (!count($meses)) return null;

        $w = 504; $h = 180;
        $mIzq = 46; $mDer = 10; $mSup = 10; $mInf = 28;
        $pw = $w - $mIzq - $mDer; $ph = $h - $mSup - $mInf;

        $max = max(max(array_column($meses, 'ingresos')), 1);
        $paso = pow(10, floor(log10($max))) / 2;
        $tope = ceil($max / $paso) * $paso;
        $y = fn($v) => $mSup + $ph - ($tope > 0 ? ($v / $tope) * $ph : 0);

        $n = count($meses);
        $ancho = $pw / $n;
        $barra = $ancho * 0.30;

        $out = '';
        for ($k = 0; $k <= 4; $k++) {
            $v = $tope * $k / 4; $yy = round($y($v), 1);
            $out .= '<line x1="' . $mIzq . '" y1="' . $yy . '" x2="' . ($w - $mDer) . '" y2="' . $yy . '" stroke="' . $REJILLA . '" stroke-width="1"/>';
        }

        foreach ($meses as $i => $m) {
            $cx = $mIzq + $ancho * $i + $ancho / 2;
            $y0 = $mSup + $ph;

            $yi = round($y($m['ingresos']), 1);
            $out .= '<rect x="' . round($cx - $barra - 2, 1) . '" y="' . $yi . '" width="' . round($barra, 1) . '" height="' . round($y0 - $yi, 1) . '" fill="' . $AZUL . '"/>';

            $yu = round($y(max(0, $m['utilidad'])), 1);
            $out .= '<rect x="' . round($cx + 2, 1) . '" y="' . $yu . '" width="' . round($barra, 1) . '" height="' . round($y0 - $yu, 1) . '" fill="' . $AZULC . '"/>';

        }

        $out .= '<line x1="' . $mIzq . '" y1="' . ($mSup + $ph) . '" x2="' . ($w - $mDer) . '" y2="' . ($mSup + $ph) . '" stroke="#b9b5ab" stroke-width="1"/>';
        return ['img' => $svg($out, $w, $h), 'tope' => $tope];
    };

    /* ---------- Grafico circular: reparto de ingresos ---------- */
    $graficoDona = function (array $servicios) use ($svg) {
        $conIngreso = array_values(array_filter($servicios, fn($s) => $s['ingresos'] > 0));
        if (!count($conIngreso)) return null;

        // Con once porciones no se lee nada: se muestran las seis mayores y el
        // resto se agrupa.
        $top = array_slice($conIngreso, 0, 6);
        $resto = array_slice($conIngreso, 6);
        if ($resto) {
            $top[] = ['servicio' => 'Resto', 'ingresos' => array_sum(array_column($resto, 'ingresos'))];
        }

        $total = array_sum(array_column($top, 'ingresos'));
        if ($total <= 0) return null;

        $paleta = ['#274698', '#4a6fc4', '#8fa6d8', '#c9930a', '#e0b64a', '#9a9186', '#c4bcae'];

        $w = 168; $h = 168; $cx = 84; $cy = 84; $rE = 70; $rI = 40;
        $out = '';
        $ang = -M_PI / 2; // arranca arriba

        foreach ($top as $i => $s) {
            $frac = $s['ingresos'] / $total;
            $fin  = $ang + $frac * 2 * M_PI;
            $largo = $frac > 0.5 ? 1 : 0;

            $x1 = $cx + $rE * cos($ang);  $y1 = $cy + $rE * sin($ang);
            $x2 = $cx + $rE * cos($fin);  $y2 = $cy + $rE * sin($fin);
            $x3 = $cx + $rI * cos($fin);  $y3 = $cy + $rI * sin($fin);
            $x4 = $cx + $rI * cos($ang);  $y4 = $cy + $rI * sin($ang);

            // Un anillo completo no se puede dibujar con un solo arco.
            if ($frac >= 0.999) {
                $out .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . (($rE + $rI) / 2) . '" fill="none" stroke="' . $paleta[$i % 7] . '" stroke-width="' . ($rE - $rI) . '"/>';
            } else {
                $out .= '<path d="M ' . round($x1, 2) . ' ' . round($y1, 2)
                     . ' A ' . $rE . ' ' . $rE . ' 0 ' . $largo . ' 1 ' . round($x2, 2) . ' ' . round($y2, 2)
                     . ' L ' . round($x3, 2) . ' ' . round($y3, 2)
                     . ' A ' . $rI . ' ' . $rI . ' 0 ' . $largo . ' 0 ' . round($x4, 2) . ' ' . round($y4, 2)
                     . ' Z" fill="' . $paleta[$i % 7] . '"/>';
            }
            $ang = $fin;
        }

        return ['img' => $svg($out, $w, $h), 'items' => $top, 'total' => $total, 'paleta' => $paleta];
    };
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

        /* Reset acotado, NUNCA universal: un `* { margin:0 }` alcanza tambien
           al elemento raiz y dompdf descarta con el el margen de @page. Era la
           causa de que el documento saliera pegado al borde del papel pese a
           tener la regla puesta. */
        body, h1, h2, h3, p, table, div, ul, ol, li { margin: 0; padding: 0; }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.45;
            color: #1c1c1a;
        }

        /* --- Cabecera y pie repetidos en todas las paginas --- */
        .doc-header {
            position: fixed;
            /* El margen superior es de 26 mm; a -17 mm la cabecera
               queda a unos 9 mm del borde del papel. */
            top: -17mm; left: 0; right: 0;
            border-bottom: 0.6pt solid #d8d4cb;
            padding-bottom: 5pt;
        }
        .doc-header .marca {
            font-size: 12pt; font-weight: bold; letter-spacing: -0.2pt; color: #274698;
        }
        .doc-header .periodo { float: right; font-size: 8pt; color: #6e6c66; padding-top: 3pt; }

        .doc-footer {
            position: fixed;
            /* Dentro del margen inferior de 20 mm, pero dejando ~11 mm
               libres hasta el borde: menos que eso y la impresora
               empieza a comerse el texto. */
            bottom: -9mm; left: 0; right: 0;
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
            /* Sin `width:25%`: con border-spacing, cuatro celdas al 25% suman
               100% MAS el espaciado y el bloque se salia 26 mm del papel por la
               derecha. Dejandolas automaticas, la tabla reparte dentro del
               100% y respeta el margen. */
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

        /* Un grafico que no cabe debe pasar entero a la pagina
           siguiente; si se parte, el eje se sale del margen. */
        .bloque { page-break-inside: avoid; }
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

    {{-- ── Altas y bajas por servicio ──────────────────────────
         Va lo antes posible, justo tras el resumen financiero: es la
         informacion que se usa para decidir donde actuar. Sin salto de
         pagina forzado, para que empiece en la segunda y no en la tercera. --}}
    <h2>Movimiento de clientes por servicio</h2>

    @php
        $rot = $rotacion ?? [];
        $totNuevos   = array_sum(array_column($rot, 'nuevos'));
        $totPerdidos = array_sum(array_column($rot, 'perdidos'));
        $totPrevios  = array_sum(array_column($rot, 'previos'));
        $retGlobal   = $totPrevios > 0 ? (($totPrevios - $totPerdidos) / $totPrevios) * 100 : null;

        // El que mas pierde y el que mas gana, para el resumen de arriba.
        $peor  = collect($rot)->sortByDesc('perdidos')->first();
        $mejor = collect($rot)->sortByDesc('neto')->first();
    @endphp

    @if (count($rot))
        <table class="kpis">
            <tr>
                <td>
                    <div class="kpi-etq">Altas del mes</div>
                    <div class="kpi-val sube">{{ $fmt($totNuevos, 0) }}</div>
                    <div class="kpi-sub">clientes que entraron</div>
                </td>
                <td>
                    <div class="kpi-etq">Bajas del mes</div>
                    <div class="kpi-val baja">{{ $fmt($totPerdidos, 0) }}</div>
                    <div class="kpi-sub">clientes que se fueron</div>
                </td>
                <td>
                    <div class="kpi-etq">Saldo neto</div>
                    <div class="kpi-val {{ $totNuevos - $totPerdidos >= 0 ? 'sube' : 'baja' }}">
                        {{ ($totNuevos - $totPerdidos >= 0 ? '+' : '') . $fmt($totNuevos - $totPerdidos, 0) }}
                    </div>
                    <div class="kpi-sub">altas menos bajas</div>
                </td>
                <td>
                    <div class="kpi-etq">Retención global</div>
                    <div class="kpi-val">{{ $retGlobal !== null ? $fmt($retGlobal, 0) . '%' : '—' }}</div>
                    <div class="kpi-sub">siguen del mes anterior</div>
                </td>
            </tr>
        </table>

        {{-- Lectura directa: donde duele y donde crece --}}
        <table style="width:100%; border-collapse:separate; border-spacing:5pt 0; margin-top:8pt">
            <tr>
                <td style="border:0.8pt solid #b3261e; border-left:3pt solid #b3261e; background:#fdf3f2; padding:8pt 10pt; vertical-align:top">
                    <div class="kpi-etq" style="color:#b3261e">Donde más se pierde</div>
                    <div style="font-size:12pt; font-weight:bold">{{ $peor['servicio'] }}</div>
                    <div class="kpi-sub">
                        {{ $fmt($peor['perdidos'], 0) }} bajas ·
                        retención {{ $peor['retencion'] !== null ? $fmt($peor['retencion'], 0) . '%' : '—' }} ·
                        pasó de {{ $fmt($peor['previos'], 0) }} a {{ $fmt($peor['activos'], 0) }} clientes
                    </div>
                </td>
                <td style="border:0.8pt solid #0f7a45; border-left:3pt solid #0f7a45; background:#f1f8f4; padding:8pt 10pt; vertical-align:top">
                    <div class="kpi-etq" style="color:#0f7a45">Donde más se gana</div>
                    <div style="font-size:12pt; font-weight:bold">{{ $mejor['servicio'] }}</div>
                    <div class="kpi-sub">
                        {{ ($mejor['neto'] >= 0 ? '+' : '') . $fmt($mejor['neto'], 0) }} neto ·
                        {{ $fmt($mejor['nuevos'], 0) }} altas ·
                        pasó de {{ $fmt($mejor['previos'], 0) }} a {{ $fmt($mejor['activos'], 0) }} clientes
                    </div>
                </td>
            </tr>
        </table>

        <table class="datos" style="margin-top:12pt">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th class="der">{{ ucfirst($comparativa['mes_anterior'] ?? 'Mes anterior') }}</th>
                    <th class="der">{{ ucfirst($mes) }}</th>
                    <th class="der">Altas</th>
                    <th class="der">Bajas</th>
                    <th class="der">Neto</th>
                    <th class="der">Retención</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rot as $r)
                    <tr>
                        <td>{{ $r['servicio'] }}</td>
                        <td class="der">{{ $fmt($r['previos'], 0) }}</td>
                        <td class="der">{{ $fmt($r['activos'], 0) }}</td>
                        <td class="der sube">{{ $r['nuevos'] ? '+' . $fmt($r['nuevos'], 0) : '—' }}</td>
                        <td class="der baja">{{ $r['perdidos'] ? '−' . $fmt($r['perdidos'], 0) : '—' }}</td>
                        <td class="der {{ $r['neto'] >= 0 ? 'sube' : 'baja' }}">
                            {{ ($r['neto'] >= 0 ? '+' : '') . $fmt($r['neto'], 0) }}
                        </td>
                        <td class="der">{{ $r['retencion'] !== null ? $fmt($r['retencion'], 0) . '%' : '—' }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td>Total</td>
                    <td class="der">{{ $fmt($totPrevios, 0) }}</td>
                    <td class="der">{{ $fmt(array_sum(array_column($rot, 'activos')), 0) }}</td>
                    <td class="der">+{{ $fmt($totNuevos, 0) }}</td>
                    <td class="der">−{{ $fmt($totPerdidos, 0) }}</td>
                    <td class="der">{{ ($totNuevos - $totPerdidos >= 0 ? '+' : '') . $fmt($totNuevos - $totPerdidos, 0) }}</td>
                    <td class="der">{{ $retGlobal !== null ? $fmt($retGlobal, 0) . '%' : '—' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="aviso">
            Un cliente cuenta como activo en un servicio cuando su suscripción cubre el mes, aunque la
            haya pagado antes. «Altas» son quienes no estaban el mes anterior y «bajas» quienes estaban
            y ya no. Un mismo cliente puede darse de baja en un servicio y seguir en otro, por eso estos
            totales no coinciden con las altas y bajas del negocio en conjunto.
        </div>
    @else
        <div class="aviso">No hay datos suficientes para comparar con el mes anterior.</div>
    @endif

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

    <div class="bloque">
    <h2>Evolución diaria de {{ ucfirst($mes) }}</h2>
    @php $gLinea = $graficoLineal($serieDiaria); @endphp
    @if ($gLinea)
        <div class="gr-titulo">Ingresos y utilidad, día a día (USD)</div>
        {{-- Las etiquetas de los ejes van en HTML, no dentro del SVG:
             php-svg-lib coloca mal el <text> y una acababa fuera del margen. --}}
        <table style="width:100%; border-collapse:collapse">
            <tr>
                <td style="width:34pt; font-size:7pt; color:#8a8884; text-align:right; padding-right:4pt; vertical-align:top">{{ $money($gLinea['tope'], 0) }}</td>
                <td rowspan="2"><img src="{{ $gLinea['img'] }}"></td>
            </tr>
            <tr><td style="font-size:7pt; color:#8a8884; text-align:right; padding-right:4pt; vertical-align:bottom">$0</td></tr>
        </table>
        <div style="font-size:7pt; color:#8a8884; text-align:center">
            Día 1 &nbsp;·&nbsp; a &nbsp;·&nbsp; Día {{ count($serieDiaria) }} de {{ ucfirst($mes) }}
        </div>
        <div class="leyenda">
            <span class="muestra" style="background:#274698"></span> Ingresos &nbsp;
            <span class="muestra" style="background:#8fa6d8"></span> Utilidad
        </div>
        <div class="gr-nota">
            Día de mayor ingreso: {{ $money($maxDia) }} · Promedio diario: {{ $money($promedio_pagos_mes) }}
        </div>
    @else
        <div class="aviso">No hay registros diarios suficientes para este mes.</div>
    @endif
    </div>

    {{-- Pagina propia: dompdf no corta antes de una imagen alta, asi que
         si este grafico empieza al final de la pagina anterior sus barras
         se salen por el borde inferior. --}}
    <div class="salto"></div>
    <div class="bloque">
    <h2>Tendencia de los últimos seis meses</h2>
    @php $maxM = collect($serieMeses)->max('ingresos') ?: 1; @endphp
    @if (count($serieMeses))
        <div class="gr-titulo">Ingresos y utilidad por mes (USD)</div>
        {{-- Barras en HTML y no en SVG: dompdf coloca la imagen de un SVG alto
             al fondo de la pagina, fuera del margen inferior, sin forma fiable
             de evitarlo. Con tablas la posicion es determinista. --}}
        <table style="width:100%; border-collapse:collapse">
            @foreach ($serieMeses as $m)
                <tr>
                    <td style="width:38pt; font-size:8pt; color:#55534e; padding:2pt 0">{{ $m['etiqueta'] }}</td>
                    <td style="padding:2pt 0">
                        <table style="width:100%; border-collapse:collapse; background:#eeece6">
                            <tr><td style="height:9pt; width:{{ round(($m['ingresos'] / $maxM) * 100) }}%; background:#274698"></td><td></td></tr>
                        </table>
                        <table style="width:100%; border-collapse:collapse; background:#eeece6; margin-top:1.5pt">
                            <tr><td style="height:7pt; width:{{ round((max(0, $m['utilidad']) / $maxM) * 100) }}%; background:#8fa6d8"></td><td></td></tr>
                        </table>
                    </td>
                    <td style="width:62pt; font-size:8pt; text-align:right; font-weight:bold; padding:2pt 0 2pt 6pt">{{ $money($m['ingresos'], 0) }}</td>
                    <td style="width:58pt; font-size:8pt; text-align:right; color:#55534e; padding:2pt 0 2pt 4pt">{{ $money($m['utilidad'], 0) }}</td>
                </tr>
            @endforeach
        </table>
        <div class="leyenda">
            <span class="muestra" style="background:#274698"></span> Ingresos &nbsp;
            <span class="muestra" style="background:#8fa6d8"></span> Utilidad
        </div>
        <div class="gr-nota">
            La serie termina en {{ ucfirst($mes) }} {{ $year }}, el mes de este reporte.
        </div>
    @endif
    </div>

    {{-- ── Resultados por servicio ─────────────────────────────── --}}
    {{-- Pagina propia: dompdf no respeta page-break-inside, asi que si
         esta seccion empieza al final de la de graficos, la tabla se
         sale del margen inferior. --}}
    <div class="salto"></div>
    <h2>Resultados por servicio</h2>
    @if (count($porServicio))
        @php $dona = $graficoDona($porServicio); @endphp
        @if ($dona)
            <div class="gr-titulo">Participación en los ingresos del mes</div>
            <table style="width:100%">
                <tr>
                    <td style="width:180pt; vertical-align:middle">
                        <img src="{{ $dona['img'] }}">
                    </td>
                    <td style="vertical-align:middle">
                        <table style="width:100%; border-collapse:collapse">
                            @foreach ($dona['items'] as $i => $it)
                                <tr>
                                    <td style="width:14pt; padding:2.5pt 0">
                                        <div style="width:8pt; height:8pt; background:{{ $dona['paleta'][$i % 7] }}"></div>
                                    </td>
                                    <td style="font-size:8.5pt; padding:2.5pt 0">{{ $it['servicio'] }}</td>
                                    <td style="font-size:8.5pt; padding:2.5pt 0; text-align:right; font-weight:bold">
                                        {{ $money($it['ingresos']) }}
                                    </td>
                                    <td style="font-size:8pt; padding:2.5pt 0 2.5pt 8pt; text-align:right; color:#6e6c66">
                                        {{ $fmt(($it['ingresos'] / $dona['total']) * 100, 1) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            </table>
        @endif

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
