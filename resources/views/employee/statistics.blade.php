@extends('layouts.static')

@section('h1', 'Actividad de Empleados')
@section('breadcrumb')
    Actividad de Empleados
@endsection
@section('introduccion')
    Actividad de cada empleado durante {{ \Carbon\Carbon::createFromDate($anio, $mes, 1)->locale('es')->monthName }}
    de {{ $anio }}, ordenada del más activo al menos activo. Desliza de izquierda a derecha para pasar de uno a otro.
@endsection

@section('styles')
    <style>
        /* Carrusel: una diapositiva por empleado, con anclaje de scroll.
           Antes se pintaban todas las tarjetas a la vez en una rejilla de dos
           columnas y el navegador tenía que construir un gráfico por empleado
           en la carga inicial. */
        .act-carrusel {
            position: relative;
        }

        .act-pista {
            /* `position: relative` no es decorativo: hace que el offsetLeft de
               cada diapositiva se mida contra la pista y no contra
               .act-carrusel. El JS compara ese offsetLeft con pista.scrollLeft,
               y sin esto los dos valores viven en sistemas de coordenadas
               distintos: la navegación y el punto activo se desfasan. */
            position: relative;
            display: flex;
            gap: var(--sf-space-4);
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            padding-bottom: var(--sf-space-3);
            /* La barra nativa se oculta: se navega con los botones y el teclado. */
            scrollbar-width: none;
        }

        .act-pista::-webkit-scrollbar {
            display: none;
        }

        .act-slide {
            flex: 0 0 100%;
            scroll-snap-align: center;
            min-width: 0;
        }

        .act-cabecera {
            display: flex;
            align-items: center;
            gap: var(--sf-space-3);
            flex-wrap: wrap;
            margin-bottom: var(--sf-space-4);
        }

        .act-rango {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            flex: none;
            border-radius: 50%;
            background: var(--sf-brand-soft);
            color: var(--sf-brand);
            font-family: var(--sf-font-mono);
            font-weight: 600;
            font-size: var(--sf-text-sm);
        }

        .act-slide:first-child .act-rango {
            background: var(--sf-gold);
            color: var(--sf-gold-contrast);
        }

        .act-nombre {
            font-family: var(--sf-font-display);
            font-weight: 600;
            font-size: var(--sf-text-xl);
            margin: 0;
            flex: 1;
            min-width: 12ch;
        }

        .act-metricas {
            display: flex;
            gap: var(--sf-space-5);
            flex-wrap: wrap;
        }

        .act-metrica-valor {
            display: block;
            font-family: var(--sf-font-mono);
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            font-size: var(--sf-text-lg);
            letter-spacing: -0.02em;
        }

        .act-metrica-etiqueta {
            font-size: var(--sf-text-xs);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--sf-ink-muted);
        }

        .act-lienzo {
            position: relative;
            height: 340px;
        }

        /* Marcador de carga mientras el gráfico aún no se ha construido. */
        .act-lienzo[data-listo="0"]::after {
            content: 'Cargando gráfico…';
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--sf-text-sm);
            color: var(--sf-ink-muted);
        }

        /* Controles */
        .act-controles {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sf-space-3);
            margin-bottom: var(--sf-space-4);
            flex-wrap: wrap;
        }

        .act-nav {
            display: flex;
            gap: var(--sf-space-2);
        }

        .act-nav button {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid var(--sf-border-strong);
            background: var(--sf-surface-card);
            color: var(--sf-ink);
            cursor: pointer;
            transition: background-color var(--sf-transition), border-color var(--sf-transition), opacity var(--sf-transition);
        }

        .act-nav button:hover:not(:disabled) {
            background: var(--sf-brand-soft);
            border-color: var(--sf-brand);
            color: var(--sf-brand);
        }

        .act-nav button:disabled {
            opacity: 0.35;
            cursor: default;
        }

        .act-posicion {
            font-size: var(--sf-text-sm);
            color: var(--sf-ink-secondary);
        }

        .act-posicion strong {
            font-family: var(--sf-font-mono);
            font-variant-numeric: tabular-nums;
        }

        /* Puntos: también sirven para saltar directamente a un empleado. */
        .act-puntos {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .act-punto {
            width: 9px;
            height: 9px;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: var(--sf-border-strong);
            cursor: pointer;
            transition: background-color var(--sf-transition), transform var(--sf-transition);
        }

        .act-punto:hover {
            background: var(--sf-ink-muted);
        }

        .act-punto.activo {
            background: var(--sf-brand);
            transform: scale(1.3);
        }

        @media (max-width: 575.98px) {
            .act-lienzo { height: 280px; }
            .act-metricas { gap: var(--sf-space-4); }
        }
    </style>
@endsection

@section('content')
    @php
        // Carga útil compacta para JavaScript. Antes el Blade escribía a mano
        // diez bucles anidados por empleado dentro del <script>, lo que generaba
        // miles de líneas de JS en el HTML.
        $metricas = [
            'asistencias' => 'Horas conectado',
            'ventas'      => 'Ventas',
            'recargas'    => 'Recargas',
            'productos'   => 'Productos',
            'inventario'  => 'Inventario',
            'cuentas'     => 'Cuentas',
            'tareas'      => 'Tareas',
            'costos'      => 'Costos',
            'clientes'    => 'Clientes',
            'gastos'      => 'Gastos',
        ];

        $cargaUtil = [];
        foreach ($estadisticasOrdenadas as $idemp => $datos) {
            $series = [];
            foreach (array_keys($metricas) as $clave) {
                $series[$clave] = array_map(fn($dia) => $dia[$clave], array_values($datos['dias']));
            }

            $cargaUtil[] = [
                'id'       => $idemp,
                'nombre'   => $datos['nombre'],
                'etiquetas' => array_map(
                    fn($fecha) => \Carbon\Carbon::parse($fecha)->format('d'),
                    array_keys($datos['dias'])
                ),
                'series'   => $series,
            ];
        }
    @endphp

    @if (empty($cargaUtil))
        <div class="sf-panel">
            <p class="sf-card-text mb-0">Todavía no hay actividad registrada para este mes.</p>
        </div>
    @else
        <div class="act-carrusel" id="actCarrusel" data-total="{{ count($cargaUtil) }}">

            <div class="act-controles">
                <div class="act-nav">
                    <button type="button" id="actAnterior" aria-label="Empleado anterior">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" id="actSiguiente" aria-label="Empleado siguiente">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <p class="act-posicion mb-0">
                    <strong id="actIndice">1</strong> de <strong>{{ count($cargaUtil) }}</strong>
                    · ordenados del más activo al menos activo
                </p>

                <div class="act-puntos" id="actPuntos" role="tablist" aria-label="Ir a un empleado">
                    @foreach ($cargaUtil as $i => $emp)
                        <button type="button" class="act-punto {{ $i === 0 ? 'activo' : '' }}"
                                data-indice="{{ $i }}" role="tab"
                                aria-label="{{ $emp['nombre'] }}" title="{{ $emp['nombre'] }}"></button>
                    @endforeach
                </div>
            </div>

            <div class="act-pista" id="actPista" tabindex="0" aria-live="polite">
                @foreach ($estadisticasOrdenadas as $idemp => $datos)
                    <div class="act-slide">
                        <div class="sf-panel">
                            <div class="act-cabecera">
                                <span class="act-rango">{{ $loop->iteration }}</span>
                                <h3 class="act-nombre">{{ $datos['nombre'] }}</h3>
                                <div class="act-metricas">
                                    <div>
                                        <span class="act-metrica-valor">{{ number_format($datos['total_horas'], 1) }}</span>
                                        <span class="act-metrica-etiqueta">Horas del mes</span>
                                    </div>
                                    <div>
                                        <span class="act-metrica-valor">{{ number_format($datos['total_acciones']) }}</span>
                                        <span class="act-metrica-etiqueta">Acciones</span>
                                    </div>
                                </div>
                            </div>

                            <div class="act-lienzo" data-listo="0">
                                <canvas data-grafico="{{ $loop->index }}"></canvas>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <script id="actDatos" type="application/json">@json($cargaUtil, JSON_UNESCAPED_UNICODE)</script>
        <script id="actMetricas" type="application/json">@json($metricas, JSON_UNESCAPED_UNICODE)</script>
    @endif
@endsection

@section('scripts')
    @if (!empty($cargaUtil))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const carrusel = document.getElementById('actCarrusel');
                if (!carrusel || typeof Chart === 'undefined') return;

                const datos = JSON.parse(document.getElementById('actDatos').textContent);
                const metricas = JSON.parse(document.getElementById('actMetricas').textContent);
                const pista = document.getElementById('actPista');
                const slides = Array.from(pista.querySelectorAll('.act-slide'));
                const puntos = Array.from(document.querySelectorAll('.act-punto'));
                const btnAnterior = document.getElementById('actAnterior');
                const btnSiguiente = document.getElementById('actSiguiente');
                const indiceTexto = document.getElementById('actIndice');

                /* Paleta categórica de tonos medios: legible tanto en claro como
                   en oscuro. Los colores de serie NO siguen al tema a propósito
                   — si cambiaran con el tema se perdería la identidad de cada
                   métrica entre una visita y otra. */
                const COLORES = {
                    asistencias: '#2a9d8f', ventas: '#3b82f6', recargas: '#8b5cf6',
                    productos: '#f97316', inventario: '#0891b2', cuentas: '#d946ef',
                    tareas: '#65a30d', costos: '#e11d48', clientes: '#0ea5e9',
                    gastos: '#b45309',
                };
                /* Solo estas dos se muestran al abrir; el resto se activa desde
                   la leyenda. Con diez series encimadas no se lee nada. */
                const VISIBLES = ['asistencias', 'ventas'];

                const graficos = new Map();

                /* Los colores de ejes y rejilla SÍ salen de los tokens, para que
                   el gráfico acompañe al tema y al modo claro/oscuro. */
                function token(nombre) {
                    return getComputedStyle(document.documentElement).getPropertyValue(nombre).trim();
                }

                function construir(indice) {
                    if (graficos.has(indice)) return;

                    const emp = datos[indice];
                    if (!emp) return;

                    const lienzo = slides[indice].querySelector('canvas');
                    const contenedor = slides[indice].querySelector('.act-lienzo');

                    const grafico = new Chart(lienzo, {
                        type: 'line',
                        data: {
                            labels: emp.etiquetas,
                            datasets: Object.keys(metricas).map(clave => ({
                                label: metricas[clave],
                                data: emp.series[clave],
                                borderColor: COLORES[clave],
                                backgroundColor: COLORES[clave] + '22',
                                fill: VISIBLES.includes(clave),
                                tension: 0.3,
                                borderWidth: 2,
                                pointRadius: 2,
                                pointHoverRadius: 5,
                                hidden: !VISIBLES.includes(clave),
                            })),
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: token('--sf-ink-secondary'),
                                        boxWidth: 10,
                                        boxHeight: 10,
                                        usePointStyle: true,
                                        font: { size: 11 },
                                    },
                                },
                                tooltip: {
                                    backgroundColor: token('--sf-surface-card'),
                                    titleColor: token('--sf-ink'),
                                    bodyColor: token('--sf-ink-secondary'),
                                    borderColor: token('--sf-border'),
                                    borderWidth: 1,
                                    padding: 10,
                                },
                            },
                            scales: {
                                x: {
                                    title: { display: true, text: 'Día del mes', color: token('--sf-ink-muted') },
                                    ticks: { color: token('--sf-ink-muted'), font: { size: 10 } },
                                    grid: { display: false },
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: token('--sf-ink-muted'), font: { size: 10 }, precision: 0 },
                                    grid: { color: token('--sf-border-soft') },
                                },
                            },
                        },
                    });

                    graficos.set(indice, grafico);
                    contenedor.dataset.listo = '1';
                }

                /* Se construye el gráfico solo cuando la diapositiva entra en
                   pantalla, más el vecino inmediato para que al pasar ya esté
                   dibujado. Así la carga inicial monta un gráfico, no doce. */
                const observador = new IntersectionObserver(entradas => {
                    entradas.forEach(entrada => {
                        if (!entrada.isIntersecting) return;
                        const i = slides.indexOf(entrada.target);
                        construir(i);
                        construir(i + 1);
                    });
                }, { root: pista, threshold: 0.25 });

                slides.forEach(s => observador.observe(s));

                /* --- Navegación --- */
                let actual = 0;

                function irA(indice) {
                    actual = Math.max(0, Math.min(slides.length - 1, indice));

                    // Se construye aqui tambien, sin esperar al observador: si
                    // este tarda o no llega a disparar, la diapositiva se
                    // quedaria con el cartel de "Cargando grafico" para siempre.
                    construir(actual);
                    construir(actual + 1);

                    // scrollLeft en vez de scrollIntoView: este ultimo tambien
                    // desplaza la pagina entera y da saltos molestos.
                    pista.scrollTo({
                        left: slides[actual].offsetLeft - (pista.clientWidth - slides[actual].offsetWidth) / 2,
                        behavior: 'smooth',
                    });

                    pintarEstado();
                }

                function pintarEstado() {
                    indiceTexto.textContent = actual + 1;
                    btnAnterior.disabled = actual === 0;
                    btnSiguiente.disabled = actual === slides.length - 1;
                    puntos.forEach((p, i) => p.classList.toggle('activo', i === actual));
                }

                btnAnterior.addEventListener('click', () => irA(actual - 1));
                btnSiguiente.addEventListener('click', () => irA(actual + 1));
                puntos.forEach(p => p.addEventListener('click', () => irA(Number(p.dataset.indice))));

                pista.addEventListener('keydown', e => {
                    if (e.key === 'ArrowRight') { e.preventDefault(); irA(actual + 1); }
                    if (e.key === 'ArrowLeft') { e.preventDefault(); irA(actual - 1); }
                });

                /* Si se desliza con el dedo o la rueda, se sincroniza el estado. */
                let temporizador;
                pista.addEventListener('scroll', () => {
                    clearTimeout(temporizador);
                    temporizador = setTimeout(() => {
                        const centro = pista.scrollLeft + pista.clientWidth / 2;
                        let masCercano = 0;
                        let menorDistancia = Infinity;
                        slides.forEach((s, i) => {
                            const d = Math.abs(s.offsetLeft + s.offsetWidth / 2 - centro);
                            if (d < menorDistancia) { menorDistancia = d; masCercano = i; }
                        });
                        if (masCercano !== actual) { actual = masCercano; pintarEstado(); }
                    }, 90);
                }, { passive: true });

                pintarEstado();

                /* Al cambiar el tema o el modo claro/oscuro hay que rehacer los
                   gráficos: Chart.js copia los colores al construirse y no los
                   vuelve a leer. */
                function rehacer() {
                    const indices = Array.from(graficos.keys());
                    graficos.forEach(g => g.destroy());
                    graficos.clear();
                    slides.forEach(s => { s.querySelector('.act-lienzo').dataset.listo = '0'; });
                    indices.forEach(construir);
                }

                window.addEventListener('temaCambiado', rehacer);
                window.addEventListener('esquemaCambiado', rehacer);
            });
        </script>
    @endif
@endsection
