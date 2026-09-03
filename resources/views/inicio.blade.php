@extends('layouts.static')

@section('title', 'Inicio')

@section('h1')
    <i class="fas fa-house me-2"></i> Panel de Control
@endsection

@section('breadcrumb')
    Inicio
@endsection

@section('introduccion')
    <h3 class="mb-1">👋 ¡Bienvenido, <strong>{{ Auth::user()->nombreemp }}</strong>!</h3>
    <p class="mb-0 text-muted">Accede rápidamente a cualquier parte del sistema.</p>
@endsection

@section('styles')
    <style>
        /* Los acentos salen de .sf-tint (streamify-ui.css), que se deriva de los
           tokens del tema. Antes esta vista usaba `bg-<color> bg-opacity-10` con
           `text-<color>`: al tocar yo las utilidades .bg-* del sistema con
           !important se perdía el modificador de opacidad y el círculo quedaba
           de color sólido, con el icono del mismo tono encima — invisible. */
        .ini-buscador {
            position: relative;
            max-width: 26rem;
            margin-bottom: var(--sf-space-6);
        }

        .ini-buscador input {
            width: 100%;
            padding: 0.6rem 0.9rem 0.6rem 2.3rem;
            font-size: var(--sf-text-sm);
            color: var(--sf-ink);
            background: var(--sf-surface-card);
            border: 1px solid var(--sf-border-strong);
            border-radius: var(--sf-radius-sm);
        }

        .ini-buscador input:focus {
            outline: none;
            border-color: var(--sf-brand);
            box-shadow: 0 0 0 3px var(--sf-brand-soft);
        }

        .ini-buscador i {
            position: absolute;
            left: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--sf-ink-muted);
            font-size: 0.85rem;
        }

        .ini-grupo + .ini-grupo { margin-top: var(--sf-space-7); }

        .ini-grupo-cabecera {
            display: flex;
            align-items: baseline;
            gap: var(--sf-space-3);
            border-bottom: 1px solid var(--sf-border);
            padding-bottom: var(--sf-space-3);
            margin-bottom: var(--sf-space-4);
        }

        /* `.ini-grupo .ini-grupo-titulo` y no solo la clase: themes.css declara
           `[data-dark-mode="true"] h4 { color: var(--text-primary) }` con
           especificidad (0,2,0) y se comia el tono atenuado de la etiqueta. */
        .ini-grupo .ini-grupo-titulo {
            font-family: var(--sf-font-sans);
            font-size: var(--sf-text-xs);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: var(--sf-ink-muted) !important;
            margin: 0;
        }

        .ini-grupo-conteo {
            font-family: var(--sf-font-mono);
            font-size: var(--sf-text-xs);
            color: var(--sf-ink-muted);
        }

        .ini-rejilla {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(158px, 1fr));
            gap: var(--sf-space-3);
        }

        .ini-acceso {
            display: flex;
            align-items: center;
            gap: var(--sf-space-3);
            padding: var(--sf-space-3) var(--sf-space-4);
            background: var(--sf-surface-card);
            border: 1px solid var(--sf-border);
            border-radius: var(--sf-radius);
            color: var(--sf-ink);
            text-decoration: none;
            transition: border-color var(--sf-transition), transform var(--sf-transition),
                        box-shadow var(--sf-transition);
        }

        .ini-acceso:hover {
            color: var(--sf-ink);
            border-color: var(--sf-brand);
            transform: translateY(-2px);
            box-shadow: var(--sf-shadow);
        }

        .ini-acceso:focus-visible {
            outline: 2px solid var(--sf-brand);
            outline-offset: 2px;
        }

        /* Pastilla del icono: hereda el tinte del acento (.sf-tint--*). */
        .ini-icono {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            flex: none;
            border-radius: var(--sf-radius-sm);
            background: var(--sf-tint-chip, var(--sf-surface-sunken));
            color: var(--sf-tint-ink, var(--sf-ink-secondary));
            font-size: 1rem;
        }

        .ini-texto { min-width: 0; }

        .ini-nombre {
            display: block;
            font-weight: 600;
            font-size: var(--sf-text-sm);
            line-height: 1.25;
        }

        .ini-desc {
            display: block;
            font-size: var(--sf-text-xs);
            color: var(--sf-ink-muted);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Los accesos de creación se distinguen con un borde punteado. */
        .ini-acceso--crear { border-style: dashed; }

        .ini-sin-resultados {
            display: none;
            padding: var(--sf-space-5);
            text-align: center;
            color: var(--sf-ink-muted);
            font-size: var(--sf-text-sm);
        }
    </style>
@endsection

@section('content')
    @php
        /*
         * Catálogo de accesos rápidos.
         *
         * `perm` se comprueba con can(), NO con hasPermissionTo(): este último
         * lanza PermissionDoesNotExist si el permiso no está sembrado, y eso
         * tumbaba la página entera. Con can() el acceso simplemente no se
         * muestra, así que añadir un módulo nuevo aquí nunca rompe el Inicio.
         *
         * Algunos módulos no tienen permiso propio (calendario, rendimiento,
         * inteligencia de negocio…); se apoyan en el permiso del área a la que
         * pertenecen, igual que hace la barra lateral.
         */
        $grupos = [
            'Principal' => [
                ['tareas.index',            'fa-list-check',        'brand',    'Tareas',             'Tablero de tareas',      'tareas.index'],
                ['dashboard',               'fa-chart-line',        'info',     'Dashboard',          'Estadísticas del mes',   'dashboard'],
                ['calendario',              'fa-calendar-days',     'brand',    'Calendario',         'Vencimientos y citas',   'cuentas'],
                ['chat.whatsapp',           'fa-comments',          'good',     'Chat WhatsApp',      'Atención al cliente',    'chat.ver'],
                ['agente.biblioteca',       'fa-book-open',         'gold',     'Biblioteca',         'Recursos del agente',    'chat.ver'],
            ],
            'Comercio' => [
                ['ventas',                  'fa-cart-shopping',     'brand',    'Ventas',             'Registro de ventas',     'ventas'],
                ['empleado.pedidos.index',  'fa-truck-fast',        'warning',  'Pedidos',            'Pedidos pendientes',     'empleado.pedidos.index'],
                ['clientes',                'fa-user-group',        'good',     'Clientes',           'Base de clientes',       'clientes'],
                ['productos.index',         'fa-box',               'brand',    'Productos',          'Catálogo',               'productos.index'],
                ['gestion.index',           'fa-boxes-stacked',     'info',     'Gestión productos',  'Stock y entregas',       'gestion'],
            ],
            'Cuentas' => [
                ['cuentas',                 'fa-tv',                'brand',    'Cuentas',            'Cuentas y perfiles',     'cuentas'],
                ['usuarios',                'fa-user-check',        'good',     'Usuarios activos',   'Suscripciones vivas',    'usuarios'],
                ['mantenimientos',          'fa-screwdriver-wrench','warning',  'Mantenimientos',     'Incidencias de cuentas', 'mantenimientos'],
                ['soportes.index',          'fa-headset',           'critical', 'Soportes',           'Tickets abiertos',       'soportes'],
            ],
            'Finanzas' => [
                ['bancos.index',            'fa-building-columns',  'brand',    'Bancos',             'Cuentas y movimientos',  'bancos.index'],
                ['mne.index',               'fa-sack-dollar',       'good',     'Mi negocio efectivo','Caja y recargas',        'mne.index'],
                ['empleado.recargas.index', 'fa-mobile-screen-button','info',   'Recargas',           'Recargas de clientes',   'empleado.recargas.index'],
                ['costos',                  'fa-file-invoice-dollar','warning', 'Costos',             'Costos del negocio',     'costos'],
                ['gastos',                  'fa-receipt',           'critical', 'Gastos',             'Gastos operativos',      'gastos'],
                ['inteligencia-negocio',    'fa-brain',             'gold',     'Inteligencia',       'Análisis del negocio',   'bancos.index'],
            ],
            'Equipo' => [
                ['empleados',               'fa-user-tie',          'brand',    'Empleados',          'Fichas del equipo',      'empleados'],
                ['asistencias.index',       'fa-user-clock',        'info',     'Actividad',          'Conexión por empleado',  'empleados'],
                ['empleados.rendimiento',   'fa-ranking-star',      'good',     'Rendimiento',        'Ranking del equipo',     'empleados'],
                ['whatsapp.analisis',       'fa-comment-dots',      'gold',     'WhatsApp IA',        'Análisis de chats',      'empleados'],
                ['roles.index',             'fa-user-shield',       'critical', 'Roles',              'Permisos del sistema',   'roles.index'],
            ],
            'Donna' => [
                ['donna.dashboard',         'fa-robot',             'brand',    'Panel Donna',        'Resumen del asistente',  'donna.dashboard'],
                ['donna.conversaciones.index','fa-comment',         'info',     'Conversaciones',     'Chats de Donna',         'donna.dashboard'],
                ['donna.planes.index',      'fa-layer-group',       'gold',     'Planes',             'Planes de Donna',        'donna.planes'],
                ['donna.suscripciones.index','fa-id-card',          'good',     'Suscripciones',      'Clientes de Donna',      'donna.suscripciones'],
                ['donna.solicitudes.index', 'fa-inbox',             'warning',  'Solicitudes',        'Altas pendientes',       'donna.solicitudes'],
                ['donna.referidos.index',   'fa-share-nodes',       'brand',    'Referidos',          'Socios y comisiones',    'donna.referidos'],
            ],
            'Inventario' => [
                ['servicios',               'fa-server',            'brand',    'Servicios',          'Catálogo de servicios',  'servicios'],
                ['proveedores',             'fa-truck',             'warning',  'Proveedores',        'Quién nos surte',        'proveedores'],
                ['valores',                 'fa-coins',             'gold',     'Valores',            'Precios y tarifas',      'valores'],
                ['mails.index',             'fa-envelope',          'info',     'Correos',            'Cuentas de correo',      'mails.index'],
            ],
            'Sistema' => [
                ['historial',               'fa-clock-rotate-left', 'info',     'Historial',          'Registro de actividad',  'historial'],
            ],
        ];

        /*
         * Crear rapido.
         *
         * Casi ningun modulo tiene ya vista `create` propia: se crea desde un
         * modal dentro del listado. Las rutas *.create siguen declaradas pero
         * apuntan a vistas que ya no existen, asi que enlazarlas daba un 500.
         *
         * Por eso cada entrada lleva el listado + el nombre de su modal, y el
         * puente del layout (?modal=) lo abre al llegar. Las dos excepciones
         * que si tienen pagina propia -ventas y roles- van directas.
         *
         * Formato: [ruta, modal|null, icono, acento, etiqueta, permiso]
         */
        $crear = [
            ['ventas.create',  null,                   'fa-cart-plus',      'brand',    'Nueva venta',         'ventas.create'],
            ['clientes',       'createClienteModal',   'fa-user-plus',      'good',     'Nuevo cliente',       'clientes.store'],
            ['cuentas',        'createCuentaModal',    'fa-plus',           'brand',    'Nueva cuenta',        'cuentas.store'],
            ['productos.index','createProductoModal',  'fa-box-open',       'info',     'Nuevo producto',      'productos.store'],
            ['servicios',      'createServicioModal',  'fa-bell-concierge', 'gold',     'Nuevo servicio',      'servicios.store'],
            ['proveedores',    'createProveedorModal', 'fa-truck-ramp-box', 'warning',  'Nuevo proveedor',     'proveedores.store'],
            ['valores',        'createValorModal',     'fa-tag',            'gold',     'Nuevo valor',         'valores.store'],
            ['mantenimientos', 'create-mantenimiento', 'fa-wrench',         'critical', 'Nuevo mantenimiento', 'mantenimientos.store'],
            ['empleados',      'createEmpleadoModal',  'fa-user-tie',       'brand',    'Nuevo empleado',      'empleados.store'],
            ['roles.create',   null,                   'fa-user-shield',    'critical', 'Nuevo rol',           'roles.store'],
        ];

        $esAdmin = Auth::user()->hasRole('Admin');
    @endphp

    <div class="ini-buscador">
        <i class="fas fa-magnifying-glass"></i>
        <input type="search" id="iniBuscar" placeholder="Buscar un acceso…" autocomplete="off"
               aria-label="Filtrar accesos rápidos">
    </div>

    @foreach ($grupos as $nombreGrupo => $accesos)
        @php
            // Se resuelve antes de pintar para no dibujar un grupo vacío.
            $visibles = array_filter($accesos, function ($a) {
                return Auth::user()->can($a[5]) && Route::has($a[0]);
            });
        @endphp

        @if (count($visibles))
            <section class="ini-grupo" data-grupo>
                <div class="ini-grupo-cabecera">
                    <h4 class="ini-grupo-titulo">{{ $nombreGrupo }}</h4>
                    <span class="ini-grupo-conteo">{{ count($visibles) }}</span>
                </div>
                <div class="ini-rejilla">
                    @foreach ($visibles as $a)
                        <a href="{{ route($a[0]) }}" class="ini-acceso sf-tint--{{ $a[2] }}"
                           data-buscar="{{ Str::lower($nombreGrupo . ' ' . $a[3] . ' ' . $a[4]) }}"
                           title="{{ $a[3] }} — {{ $a[4] }}">
                            <span class="ini-icono"><i class="fas {{ $a[1] }}"></i></span>
                            <span class="ini-texto">
                                <span class="ini-nombre">{{ $a[3] }}</span>
                                <span class="ini-desc">{{ $a[4] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

    {{-- Apariencia del sistema: solo administradores --}}
    @if ($esAdmin && Route::has('sistema.index'))
        <section class="ini-grupo" data-grupo>
            <div class="ini-grupo-cabecera">
                <h4 class="ini-grupo-titulo">Administración</h4>
                <span class="ini-grupo-conteo">1</span>
            </div>
            <div class="ini-rejilla">
                <a href="{{ route('sistema.index') }}" class="ini-acceso sf-tint--gold"
                   data-buscar="sistema apariencia temas">
                    <span class="ini-icono"><i class="fas fa-palette"></i></span>
                    <span class="ini-texto">
                        <span class="ini-nombre">Sistema</span>
                        <span class="ini-desc">Temas y apariencia</span>
                    </span>
                </a>
            </div>
        </section>
    @endif

    @php
        $crearVisibles = array_filter($crear, fn($c) => Auth::user()->can($c[5]) && Route::has($c[0]));
    @endphp

    @if (count($crearVisibles))
        <section class="ini-grupo" data-grupo>
            <div class="ini-grupo-cabecera">
                <h4 class="ini-grupo-titulo">Crear rápido</h4>
                <span class="ini-grupo-conteo">{{ count($crearVisibles) }}</span>
            </div>
            <div class="ini-rejilla">
                @foreach ($crearVisibles as $c)
                    @php
                        // Con modal: se abre solo al llegar al listado.
                        $destino = $c[1] ? route($c[0]) . '?modal=' . $c[1] : route($c[0]);
                    @endphp
                    <a href="{{ $destino }}" class="ini-acceso ini-acceso--crear sf-tint--{{ $c[3] }}"
                       data-buscar="{{ Str::lower('crear rapido nuevo ' . $c[4]) }}"
                       title="{{ $c[4] }}">
                        <span class="ini-icono"><i class="fas {{ $c[2] }}"></i></span>
                        <span class="ini-texto">
                            <span class="ini-nombre">{{ $c[4] }}</span>
                            <span class="ini-desc">{{ $c[1] ? 'Abre el formulario' : 'Página completa' }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <p class="ini-sin-resultados" id="iniSinResultados">
        No hay ningún acceso que coincida con lo que buscas.
    </p>
@endsection

@section('pie')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <i class="fas fa-lightbulb me-2"></i>
            <strong>Tip:</strong> escribe en el buscador de arriba para encontrar cualquier sección al instante.
        </div>
        <div class="text-muted small">
            <i class="fas fa-clock me-1"></i>
            {{ now()->locale('es')->translatedFormat('l d \d\e F, H:i') }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Filtro en cliente: con más de treinta accesos, buscar es más rápido
        // que recorrerlos con la vista.
        (function () {
            const campo = document.getElementById('iniBuscar');
            if (!campo) return;

            const accesos = Array.from(document.querySelectorAll('[data-buscar]'));
            const grupos = Array.from(document.querySelectorAll('[data-grupo]'));
            const sinResultados = document.getElementById('iniSinResultados');

            function normalizar(t) {
                // Sin acentos: buscar "gestion" debe encontrar "Gestión".
                return t.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
            }

            campo.addEventListener('input', function () {
                const q = normalizar(campo.value.trim());
                let visibles = 0;

                accesos.forEach(a => {
                    const coincide = !q || normalizar(a.dataset.buscar).includes(q);
                    a.hidden = !coincide;
                    if (coincide) visibles++;
                });

                // Un grupo sin accesos visibles se oculta entero, con su título.
                grupos.forEach(g => {
                    g.hidden = !g.querySelector('[data-buscar]:not([hidden])');
                });

                sinResultados.style.display = visibles === 0 ? 'block' : 'none';
            });

            // Escribir directamente sin tener que hacer clic en el campo.
            document.addEventListener('keydown', e => {
                if (e.key === '/' && document.activeElement !== campo) {
                    e.preventDefault();
                    campo.focus();
                }
            });
        })();
    </script>
@endsection
