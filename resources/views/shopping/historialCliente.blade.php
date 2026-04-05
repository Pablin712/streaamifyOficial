@extends('layouts.cliente')

@section('menu')
    <div class="dropdown me-lg-3">
        <button class="btn btn-light border rounded-pill dropdown-toggle fw-bold" type="button" id="dropdownAcerca"
            data-bs-toggle="dropdown" aria-expanded="false">
            Acerca de
        </button>
        <ul class="dropdown-menu shadow" aria-labelledby="dropdownAcerca">
            <li><a class="dropdown-item" href="{{ route('principal') }}#registro">Registro</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#features">Fortalezas</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#combos">Promociones</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#servicios">Otros Servicios</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#redes">Redes Sociales</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#faq">Preguntas Frecuentes</a></li>
        </ul>
    </div>

    <div class="dropdown me-lg-3">
        <button class="btn btn-light border rounded-pill dropdown-toggle fw-bold" type="button" id="dropdownCatalogo"
            data-bs-toggle="dropdown" aria-expanded="false">
            Catálogo
        </button>
        <ul class="dropdown-menu shadow" aria-labelledby="dropdownCatalogo">
            <li><a class="dropdown-item" href="{{ route('shop') }}#inmediata-individual">Entrega Inmediata - Individual</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#combos">Entrega Inmediata - Combos</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#pedidos">Pedidos</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#personalizadas">Personalizadas</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#completos">Cuentas completas</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#juegos">Juegos</a></li>
        </ul>
    </div>
@endsection

@section('title', 'Mi Actividad')

@section('styles')
    <style>
        .activity-shell {
            position: relative;
            padding-top: 2rem;
        }

        .activity-hero {
            background:
                radial-gradient(circle at top right, rgba(228, 177, 0, 0.18), transparent 28%),
                linear-gradient(135deg, #10245f 0%, #1b3278 48%, #0f1831 100%);
            color: #fff;
            border-radius: 32px;
            padding: 2.5rem;
            box-shadow: 0 24px 60px rgba(16, 36, 95, 0.28);
        }

        .activity-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 0.85rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .activity-hero h1 {
            font-size: clamp(2rem, 4vw, 3.4rem);
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        .activity-hero p {
            color: rgba(255, 255, 255, 0.82);
            max-width: 760px;
            font-size: 1.05rem;
        }

        .activity-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .activity-kpis {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .activity-kpi {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 22px;
            padding: 1.15rem 1.2rem;
            backdrop-filter: blur(10px);
        }

        .activity-kpi strong {
            display: block;
            font-size: 1.85rem;
            line-height: 1;
        }

        .activity-kpi span {
            color: rgba(255, 255, 255, 0.74);
            font-size: 0.9rem;
        }

        .activity-panels {
            margin-top: 2rem;
        }

        .activity-card {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 18px 40px rgba(15, 24, 49, 0.08);
            overflow: hidden;
        }

        .activity-card .card-body {
            padding: 1.4rem;
        }

        .activity-nav {
            border: 0;
            gap: 0.65rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .activity-nav .nav-link {
            border: 0;
            border-radius: 999px;
            padding: 0.8rem 1.1rem;
            background: #fff;
            color: #24314f;
            font-weight: 700;
            box-shadow: 0 10px 22px rgba(15, 24, 49, 0.06);
        }

        .activity-nav .nav-link.active {
            background: #10245f;
            color: #fff;
            box-shadow: 0 14px 28px rgba(16, 36, 95, 0.22);
        }

        .activity-section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .activity-section-title h3 {
            margin-bottom: 0.2rem;
        }

        .table-soft {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(16, 36, 95, 0.04);
            --bs-table-hover-bg: rgba(228, 177, 0, 0.09);
            vertical-align: middle;
        }

        .service-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            background: rgba(16, 36, 95, 0.08);
            color: #10245f;
            font-weight: 700;
            font-size: 0.92rem;
        }

        .support-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .support-step-list {
            display: grid;
            gap: 0.95rem;
        }

        .support-step {
            display: flex;
            align-items: flex-start;
            gap: 0.9rem;
            padding: 1rem;
            border-radius: 18px;
            background: rgba(16, 36, 95, 0.04);
        }

        .support-step-number {
            width: 2rem;
            height: 2rem;
            flex: 0 0 2rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #10245f;
            color: #fff;
            font-weight: 800;
        }

        .support-summary {
            display: grid;
            gap: 1rem;
        }

        .support-summary-card {
            padding: 1.1rem 1.2rem;
            border-radius: 20px;
            background: #fff8e1;
            border: 1px solid rgba(228, 177, 0, 0.35);
        }

        .support-summary-card.is-success {
            background: #ecfdf3;
            border-color: rgba(25, 135, 84, 0.24);
        }

        .support-summary-card strong {
            display: block;
            font-size: 1.8rem;
            line-height: 1;
            margin-bottom: 0.4rem;
        }

        .support-empty {
            padding: 1.4rem;
            border-radius: 20px;
            background: rgba(16, 36, 95, 0.04);
            color: #4a5877;
            text-align: center;
        }

        .support-account-preview {
            display: block;
            font-size: 0.84rem;
            color: #6c757d;
            margin-top: 0.15rem;
        }

        .mini-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .mini-badge.warning {
            background: #fff3cd;
            color: #8a6218;
        }

        .mini-badge.success {
            background: #d1e7dd;
            color: #146c43;
        }

        .activity-alert {
            margin-top: 1.5rem;
            border-radius: 18px;
            border: 0;
            box-shadow: 0 12px 28px rgba(15, 24, 49, 0.08);
        }

        @media (max-width: 991.98px) {
            .activity-kpis,
            .support-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .activity-hero {
                padding: 1.5rem;
                border-radius: 24px;
            }

            .activity-kpis,
            .support-grid {
                grid-template-columns: 1fr;
            }

            .activity-section-title {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endsection

@section('header')
    @php
        $suscripcionesTotales = method_exists($usuarios_activos, 'total') ? $usuarios_activos->total() : $usuarios_activos->count();
        $comprasTotales = method_exists($ventas, 'total') ? $ventas->total() : $ventas->count();
        $recargasTotales = method_exists($recargas, 'total') ? $recargas->total() : $recargas->count();
        $soportesPendientes = $soportes->where('estado', 'pendiente')->count();
    @endphp

    <div class="container px-4 px-lg-5 activity-shell">
        <div class="activity-hero">
            <span class="activity-eyebrow">
                <i class="bi bi-stars"></i>
                Centro de actividad
            </span>

            <h1>Controla tus servicios y pide ayuda sin salir de Streamify.</h1>
            <p>
                Revisa compras, recargas, pedidos, suscripciones activas y ahora también tus soportes desde una sola vista.
                Si algo falla, puedes reportarlo con la cuenta exacta para acelerar la atención técnica.
            </p>

            <div class="activity-actions">
                <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold" onclick="switchToTab('#soportes')">
                    <i class="bi bi-life-preserver me-2"></i>Generar soporte
                </button>
                <button type="button" class="btn btn-outline-light rounded-pill px-4 fw-bold" onclick="switchToTab('#suscripciones')">
                    <i class="bi bi-play-circle me-2"></i>Ver suscripciones
                </button>
            </div>

            <div class="activity-kpis">
                <div class="activity-kpi">
                    <strong>{{ $suscripcionesTotales }}</strong>
                    <span>suscripciones registradas</span>
                </div>
                <div class="activity-kpi">
                    <strong>{{ $comprasTotales }}</strong>
                    <span>compras realizadas</span>
                </div>
                <div class="activity-kpi">
                    <strong>{{ $recargasTotales }}</strong>
                    <span>recargas en historial</span>
                </div>
                <div class="activity-kpi">
                    <strong>{{ $soportesPendientes }}</strong>
                    <span>soportes pendientes</span>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success activity-alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger activity-alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
            </div>
        @endif
    </div>

    @if (session('renovacion_exitosa'))
        <div class="modal fade show d-block" id="renovacionExitosaModal" tabindex="-1"
            aria-labelledby="renovacionExitosaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="renovacionExitosaLabel">Renovación Exitosa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            onclick="cerrarRenovacionModal()"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="bi bi-arrow-repeat text-primary" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Tu suscripción a <b>{{ session('renovacion_exitosa')['nombre'] }}</b> ha sido renovada</h5>
                        <p class="text-muted">Nueva fecha de vencimiento:
                            <b>{{ session('renovacion_exitosa')['fecha_vencimiento'] }}</b>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="cerrarRenovacionModal()">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="modal fade show d-block" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="errorModalLabel">Ocurrió un problema</h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"
                            onclick="cerrarErrorModal()"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">{{ session('error') }}</p>
                        <p>Si el problema persiste, crea un soporte con la cuenta afectada para acelerar la atención.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="cerrarErrorModal()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('sections')
    <div class="container px-4 px-lg-5 my-5 activity-panels">
        <ul class="nav nav-tabs activity-nav" id="historialTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="suscripciones-tab" data-bs-toggle="tab" data-bs-target="#suscripciones"
                    type="button" role="tab" aria-controls="suscripciones" aria-selected="false">
                    <i class="bi bi-tv me-2"></i>Mis Suscripciones
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ventas-tab" data-bs-toggle="tab" data-bs-target="#ventas"
                    type="button" role="tab" aria-controls="ventas" aria-selected="true">
                    <i class="bi bi-bag-check me-2"></i>Compras
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pedidos-tab" data-bs-toggle="tab" data-bs-target="#pedidos" type="button"
                    role="tab" aria-controls="pedidos" aria-selected="false">
                    <i class="bi bi-box-seam me-2"></i>Pedidos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="recargas-tab" data-bs-toggle="tab" data-bs-target="#recargas"
                    type="button" role="tab" aria-controls="recargas" aria-selected="false">
                    <i class="bi bi-wallet2 me-2"></i>Recargas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="soportes-tab" data-bs-toggle="tab" data-bs-target="#soportes"
                    type="button" role="tab" aria-controls="soportes" aria-selected="false">
                    <i class="bi bi-life-preserver me-2"></i>Soportes
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="referidos-tab" data-bs-toggle="tab" data-bs-target="#referidos"
                    type="button" role="tab" aria-controls="referidos" aria-selected="false">
                    <i class="bi bi-people me-2"></i>Referidos
                </button>
            </li>
        </ul>

        <div class="tab-content" id="historialTabsContent">
            <div class="tab-pane fade show active" id="ventas" role="tabpanel" aria-labelledby="ventas-tab">
                <div class="card activity-card">
                    <div class="card-body">
                        <div class="activity-section-title">
                            <div>
                                <h3>Historial de compras</h3>
                                <p class="text-muted mb-0">Consulta tus compras y revisa los perfiles entregados en cada operación.</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-soft table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>ID Compra</th>
                                        <th>Fecha</th>
                                        <th>Total pagado</th>
                                        <th>Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($ventas as $venta)
                                        <tr>
                                            <td><strong>#{{ $venta->idven }}</strong></td>
                                            <td>{{ $venta->fechaven->format('d/m/Y') }}</td>
                                            <td>${{ number_format($venta->detalles_venta->sum('montodet'), 2) }}</td>
                                            <td>
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#detalleVentaModal-{{ $venta->idven }}">
                                                    <i class="bi bi-eye me-1"></i>Ver compra
                                                </button>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="detalleVentaModal-{{ $venta->idven }}" tabindex="-1"
                                            aria-labelledby="detalleVentaLabel-{{ $venta->idven }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="detalleVentaLabel-{{ $venta->idven }}">Compra #{{ $venta->idven }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-6">
                                                                <div class="support-step">
                                                                    <div class="support-step-number"><i class="bi bi-calendar-event"></i></div>
                                                                    <div>
                                                                        <strong>Fecha</strong>
                                                                        <div>{{ $venta->fechaven->format('d/m/Y') }}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="support-step">
                                                                    <div class="support-step-number"><i class="bi bi-cash-stack"></i></div>
                                                                    <div>
                                                                        <strong>Total pagado</strong>
                                                                        <div>${{ number_format($venta->detalles_venta->sum('montodet'), 2) }}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <ul class="list-group">
                                                            @foreach ($venta->detalles_venta as $detalle)
                                                                <li class="list-group-item">
                                                                    <strong>{{ optional(optional(optional(optional($detalle->perfil)->cuenta)->valor)->servicio)->nombreser ?? 'Servicio' }}</strong><br>
                                                                    Fecha de vencimiento: {{ $detalle->fechavendet->format('d/m/Y') }}<br>
                                                                    Monto: ${{ number_format($detalle->montodet, 2) }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="4"><div class="support-empty">Aún no tienes compras registradas.</div></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="suscripciones" role="tabpanel" aria-labelledby="suscripciones-tab">
                <div class="card activity-card">
                    <div class="card-body">
                        <div class="activity-section-title">
                            <div>
                                <h3>Mis suscripciones activas</h3>
                                <p class="text-muted mb-0">Si alguna cuenta presenta fallas, puedes reportarla desde la pestaña de soportes.</p>
                            </div>
                            <button type="button" class="btn btn-outline-warning rounded-pill fw-bold" onclick="switchToTab('#soportes')">
                                <i class="bi bi-life-preserver me-2"></i>Necesito ayuda
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-soft table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Compra</th>
                                        <th>Servicio</th>
                                        <th>Cuenta</th>
                                        <th>Contraseña</th>
                                        <th>Perfil</th>
                                        <th>Vence</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($usuarios_activos as $usuario)
                                        @php
                                            $servicio = optional(optional(optional($usuario->cuenta)->valor)->servicio)->nombreser ?? 'Servicio';
                                            $pinPerfil = optional($usuario->profile)->pinper ?? 'N/A';
                                            $cuentaSpotifyRestringida = optional(optional($usuario->cuenta)->valor)->idser === 'SPOTIFY' && (int) $usuario->perfil !== 1;
                                            $canRequestNetflixCode = strtoupper((string) (optional(optional($usuario->cuenta)->valor)->idser ?? '')) === strtoupper((string) config('services.netflix_code.service_id', 'NETFLIX'));
                                        @endphp
                                        <tr>
                                            <td><strong>#{{ $usuario->idven }}</strong></td>
                                            <td><span class="service-pill"><i class="bi bi-play-btn"></i>{{ $servicio }}</span></td>
                                            <td>{{ $cuentaSpotifyRestringida ? 'Acceso restringido' : optional($usuario->cuenta)->usuariocue }}</td>
                                            <td>{{ $cuentaSpotifyRestringida ? 'Acceso restringido' : optional($usuario->cuenta)->contrasenacue }}</td>
                                            <td>{{ $usuario->perfil }} : {{ $pinPerfil }}</td>
                                            <td>{{ \Carbon\Carbon::parse($usuario->fecha_vencimiento)->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#renovarModal{{ $usuario->idven }}">
                                                        <i class="bi bi-arrow-repeat me-1"></i>Renovar
                                                    </button>
                                                    @if ($canRequestNetflixCode)
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-danger btn-sm"
                                                            data-iddet="{{ $usuario->iddet }}"
                                                            data-cuenta="{{ optional($usuario->cuenta)->usuariocue }}"
                                                            data-proveedor="{{ optional(optional(optional($usuario->cuenta)->valor)->proveedor)->nombrepro ?? 'Proveedor' }}"
                                                            onclick="openClienteNetflixCodeModal(this)"
                                                        >
                                                            <i class="bi bi-key me-1"></i>Pedir codigo
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="renovarModal{{ $usuario->idven }}" tabindex="-1"
                                            aria-labelledby="renovarModalLabel{{ $usuario->idven }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="renovarModalLabel{{ $usuario->idven }}">Confirmar renovación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('cliente.renovar', $usuario->idven) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <h6 class="mb-3">Selecciona los perfiles a renovar</h6>
                                                            <ul class="list-group">
                                                                @foreach ($usuario->venta->detalles_venta as $detalle)
                                                                    <li class="list-group-item d-flex justify-content-between align-items-center gap-3">
                                                                        <div>
                                                                            <input class="form-check-input me-2" type="checkbox" name="detalles[]"
                                                                                value="{{ $detalle->iddet }}" id="detalle-{{ $detalle->iddet }}">
                                                                            <label class="form-check-label" for="detalle-{{ $detalle->iddet }}">
                                                                                <strong>{{ optional(optional(optional(optional($detalle->perfil)->cuenta)->valor)->servicio)->nombreser ?? 'Servicio' }}</strong><br>
                                                                                Cuenta: <strong>{{ optional(optional($detalle->perfil)->cuenta)->usuariocue }}</strong><br>
                                                                                Perfil: <strong>{{ optional($detalle->perfil)->numeroper }}</strong><br>
                                                                                Nueva fecha: <strong>{{ \Carbon\Carbon::parse($detalle->fechavendet)->addMonth()->format('d/m/Y') }}</strong>
                                                                            </label>
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-primary">Confirmar renovación</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="7"><div class="support-empty">No tienes suscripciones activas en este momento.</div></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pedidos" role="tabpanel" aria-labelledby="pedidos-tab">
                <div class="card activity-card">
                    <div class="card-body">
                        <div class="activity-section-title">
                            <div>
                                <h3>Historial de pedidos</h3>
                                <p class="text-muted mb-0">Monitorea tus pedidos y la respuesta del equipo cuando aplique.</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-soft table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Respuesta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pedidos as $pedido)
                                        <tr>
                                            <td>#{{ $pedido->id }}</td>
                                            <td>{{ $pedido->producto->nombrepro }}</td>
                                            <td>{{ $pedido->producto->descripcionpro }}</td>
                                            <td>
                                                <span class="badge @if ($pedido->estado->nombre === 'Pendiente') bg-warning text-dark @elseif ($pedido->estado->nombre === 'Rechazado') bg-danger @else bg-success @endif">
                                                    {{ ucfirst($pedido->estado->nombre) }}
                                                </span>
                                            </td>
                                            <td>{{ $pedido->fechapedido->format('d/m/Y H:i') }}</td>
                                            <td>{{ $pedido->respuesta ?? 'Sin respuesta' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6"><div class="support-empty">Todavía no has generado pedidos.</div></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="recargas" role="tabpanel" aria-labelledby="recargas-tab">
                <div class="card activity-card">
                    <div class="card-body">
                        <div class="activity-section-title">
                            <div>
                                <h3>Historial de recargas</h3>
                                <p class="text-muted mb-0">Consulta el estado de tus recargas y cuándo fueron procesadas.</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-soft table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recargas as $recarga)
                                        <tr>
                                            <td>${{ number_format($recarga->valor, 2) }}</td>
                                            <td>
                                                <span class="badge @if ($recarga->estado->nombre === 'Pendiente') bg-warning text-dark @elseif ($recarga->estado->nombre === 'Rechazado') bg-danger @else bg-success @endif">
                                                    {{ ucfirst($recarga->estado->nombre) }}
                                                </span>
                                            </td>
                                            <td>{{ $recarga->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3"><div class="support-empty">No tienes recargas registradas.</div></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $recargas->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="soportes" role="tabpanel" aria-labelledby="soportes-tab">
                <div class="card activity-card">
                    <div class="card-body">
                        <div class="activity-section-title">
                            <div>
                                <h3>Soporte técnico</h3>
                                <p class="text-muted mb-0">Reporta fallas con la cuenta exacta y sigue el estado de atención.</p>
                            </div>
                            <button type="button" class="btn btn-warning rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#crearSoporteModal">
                                <i class="bi bi-plus-circle me-2"></i>Crear soporte
                            </button>
                        </div>

                        <div class="support-grid">
                            <div class="support-step-list">
                                <div class="support-step">
                                    <span class="support-step-number">1</span>
                                    <div>
                                        <strong>Selecciona la cuenta afectada</strong>
                                        <div class="text-muted">Escoge una de tus cuentas activas para que el equipo técnico vea el servicio correcto.</div>
                                    </div>
                                </div>
                                <div class="support-step">
                                    <span class="support-step-number">2</span>
                                    <div>
                                        <strong>Describe el problema</strong>
                                        <div class="text-muted">Indica si es falta de suscripción, contraseña incorrecta, exceso de dispositivos u otro caso.</div>
                                    </div>
                                </div>
                                <div class="support-step">
                                    <span class="support-step-number">3</span>
                                    <div>
                                        <strong>Recibe seguimiento</strong>
                                        <div class="text-muted">Tu soporte quedará visible aquí con estado pendiente o atendido y la solución registrada.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="support-summary">
                                <div class="support-summary-card">
                                    <strong>{{ $soportes->where('estado', 'pendiente')->count() }}</strong>
                                    <span>soportes pendientes</span>
                                </div>
                                <div class="support-summary-card is-success">
                                    <strong>{{ $soportes->where('estado', 'atendido')->count() }}</strong>
                                    <span>soportes atendidos</span>
                                </div>
                                <div class="support-summary-card" style="background:#f4f6ff;border-color:rgba(16,36,95,.18);">
                                    <strong>{{ $cuentasSoporte->count() }}</strong>
                                    <span>cuentas disponibles para reportar</span>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-soft table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cuenta</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Solución</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($soportes as $soporte)
                                        <tr>
                                            <td><strong>#{{ $soporte->idsop }}</strong></td>
                                            <td>
                                                <span class="service-pill">
                                                    <i class="bi bi-play-btn"></i>{{ optional(optional(optional($soporte->cuenta)->valor)->servicio)->nombreser ?? 'Servicio' }}
                                                </span>
                                                <span class="support-account-preview">{{ optional($soporte->cuenta)->usuariocue }}</span>
                                            </td>
                                            <td>{{ ucfirst($soporte->tipo) }}</td>
                                            <td>
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    onclick="openClientTextModal('Descripción del soporte', {{ json_encode($soporte->descripcion) }})">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                            <td>
                                                @if ($soporte->estado === 'pendiente')
                                                    <span class="mini-badge warning"><i class="bi bi-hourglass-split"></i>Pendiente</span>
                                                @else
                                                    <span class="mini-badge success"><i class="bi bi-check-circle"></i>Atendido</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($soporte->solucion)
                                                    <button type="button" class="btn btn-outline-success btn-sm"
                                                        onclick="openClientTextModal('Solución registrada', {{ json_encode($soporte->solucion) }})">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted small">Sin solución aún</span>
                                                @endif
                                            </td>
                                            <td>{{ $soporte->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7"><div class="support-empty">Aún no has creado soportes. Si algo falla con una cuenta, repórtalo aquí.</div></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="referidos" role="tabpanel" aria-labelledby="referidos-tab">
                <div class="card activity-card">
                    <div class="card-body">
                        <div class="activity-section-title">
                            <div>
                                <h3>Mis referidos</h3>
                                <p class="text-muted mb-0">Sigue quiénes se registraron por tu código y si ya generaron compras.</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-soft table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Referido</th>
                                        <th>Correo</th>
                                        <th>Unión</th>
                                        <th>Compró</th>
                                        <th>Ganancia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($referidos as $usuario)
                                        <tr>
                                            <td>{{ $usuario->nombrecli }}</td>
                                            <td>{{ $usuario->email }}</td>
                                            <td>{{ $usuario->created_at->format('d/m/Y H:i') }}</td>
                                            <td><span class="badge {{ $usuario->ya_compro ? 'bg-success' : 'bg-danger' }}">{{ $usuario->ya_compro ? 'Sí' : 'No' }}</span></td>
                                            <td><span class="badge {{ $usuario->ya_compro ? 'bg-success' : 'bg-danger' }}">{{ $usuario->ya_compro ? '$1,00' : '$0,00' }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5"><div class="support-empty">Aún no tienes referidos registrados.</div></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="crearSoporteModal" tabindex="-1" aria-labelledby="crearSoporteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('cliente.soportes.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="crearSoporteModalLabel">Generar soporte</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="soporte-idcue" class="form-label fw-bold">Seleccione la cuenta</label>
                            <select name="idcue" id="soporte-idcue" class="form-select" required>
                                <option value="">Seleccione una cuenta</option>
                                @foreach ($cuentasSoporte as $cuentaSoporte)
                                    @php
                                        $servicioCuenta = optional(optional(optional($cuentaSoporte->cuenta)->valor)->servicio)->nombreser ?? 'Servicio';
                                        $fechaCuenta = \Carbon\Carbon::parse($cuentaSoporte->fecha_vencimiento)->format('d/m/Y');
                                    @endphp
                                    <option value="{{ $cuentaSoporte->idcue }}" @selected(old('idcue') === $cuentaSoporte->idcue)>
                                        {{ $servicioCuenta }} - {{ optional($cuentaSoporte->cuenta)->usuariocue }} - {{ $fechaCuenta }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="soporte-tipo" class="form-label fw-bold">Tipo</label>
                            <select name="tipo" id="soporte-tipo" class="form-select" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="sin suscripcion" @selected(old('tipo') === 'sin suscripcion')>Sin suscripción</option>
                                <option value="contrasena incorrecta" @selected(old('tipo') === 'contrasena incorrecta')>Contraseña incorrecta</option>
                                <option value="muchos dispositivos" @selected(old('tipo') === 'muchos dispositivos')>Muchos dispositivos</option>
                                <option value="otro" @selected(old('tipo') === 'otro')>Otro</option>
                            </select>
                        </div>

                        <div class="mb-0">
                            <label for="soporte-descripcion" class="form-label fw-bold">Descripción</label>
                            <textarea name="descripcion" id="soporte-descripcion" rows="5" class="form-control" required
                                placeholder="Describe qué sucede, qué mensaje te aparece y desde cuándo ocurre.">{{ old('descripcion') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning fw-bold">Enviar soporte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clientTextModal" tabindex="-1" aria-labelledby="clientTextModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientTextModalLabel">Detalle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="clientTextModalContent"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clienteNetflixCodeModal" tabindex="-1" aria-labelledby="clienteNetflixCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clienteNetflixCodeModalLabel">Pedir codigo de Netflix</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="cliente_netflix_code_request_state">
                        <div class="alert alert-warning">
                            Vas a solicitar un codigo de Netflix para esta suscripcion. Espera la respuesta del sistema para ver el codigo aqui mismo.
                        </div>
                        <div class="small text-muted">
                            <div><strong>Cuenta:</strong> <span id="cliente_netflix_code_cuenta">-</span></div>
                            <div><strong>Proveedor:</strong> <span id="cliente_netflix_code_proveedor">-</span></div>
                        </div>
                    </div>

                    <div id="cliente_netflix_code_loading_state" class="text-center py-4 d-none">
                        <div class="spinner-border text-danger mb-3" role="status"></div>
                        <div class="fw-semibold">Solicitando codigo de Netflix...</div>
                        <div class="text-muted small">Espera mientras el webhook responde.</div>
                    </div>

                    <div id="cliente_netflix_code_result_state" class="text-center py-3 d-none">
                        <div class="alert alert-success">
                            <div class="fw-bold">Codigo de Netflix</div>
                            <div class="small text-muted">Usalo antes de que venza.</div>
                        </div>
                        <div id="cliente_netflix_code_result_code" class="display-4 fw-bold text-danger mb-2">0000</div>
                        <div id="cliente_netflix_code_result_expiration" class="text-muted">En 15 minutos vence.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="cliente_netflix_code_close_btn" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="cliente_netflix_code_copy_btn" class="btn btn-outline-primary d-none" onclick="copyClienteNetflixCodeResult()">
                        <i class="bi bi-copy me-1"></i>Copiar codigo
                    </button>
                    <button type="button" id="cliente_netflix_code_confirm_btn" class="btn btn-danger" onclick="confirmClienteNetflixCodeRequest()">
                        <i class="bi bi-key me-1"></i>Confirmar solicitud
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let clienteNetflixCodeButtonRef = null;

        function cerrarRenovacionModal() {
            const modal = document.getElementById('renovacionExitosaModal');
            if (modal) {
                modal.classList.remove('show');
                modal.classList.add('d-none');
            }
        }

        function cerrarErrorModal() {
            const modal = document.getElementById('errorModal');
            if (modal) {
                modal.classList.remove('show');
                modal.classList.add('d-none');
            }
        }

        function openClientTextModal(title, content) {
            document.getElementById('clientTextModalLabel').textContent = title;
            document.getElementById('clientTextModalContent').textContent = content;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('clientTextModal')).show();
        }

        function setClienteNetflixCodeModalState(state, payload = {}) {
            const requestState = document.getElementById('cliente_netflix_code_request_state');
            const loadingState = document.getElementById('cliente_netflix_code_loading_state');
            const resultState = document.getElementById('cliente_netflix_code_result_state');
            const confirmBtn = document.getElementById('cliente_netflix_code_confirm_btn');
            const copyBtn = document.getElementById('cliente_netflix_code_copy_btn');
            const modalTitle = document.getElementById('clienteNetflixCodeModalLabel');

            requestState?.classList.toggle('d-none', state !== 'request');
            loadingState?.classList.toggle('d-none', state !== 'loading');
            resultState?.classList.toggle('d-none', state !== 'result');

            if (confirmBtn) {
                confirmBtn.classList.toggle('d-none', state === 'result');
                confirmBtn.disabled = state === 'loading';
            }

            if (copyBtn) {
                copyBtn.classList.toggle('d-none', state !== 'result');
            }

            if (modalTitle) {
                modalTitle.textContent = state === 'result' ? 'Codigo de Netflix' : 'Pedir codigo de Netflix';
            }

            if (state === 'result') {
                document.getElementById('cliente_netflix_code_result_code').textContent = payload.code || '0000';
                document.getElementById('cliente_netflix_code_result_expiration').textContent = payload.expirationText || 'En 15 minutos vence.';
            }
        }

        function openClienteNetflixCodeModal(button) {
            clienteNetflixCodeButtonRef = button;
            document.getElementById('cliente_netflix_code_cuenta').textContent = button.dataset.cuenta || '-';
            document.getElementById('cliente_netflix_code_proveedor').textContent = button.dataset.proveedor || 'Proveedor';
            setClienteNetflixCodeModalState('request');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('clienteNetflixCodeModal')).show();
        }

        async function confirmClienteNetflixCodeRequest() {
            if (!clienteNetflixCodeButtonRef) {
                return;
            }

            setClienteNetflixCodeModalState('loading');

            try {
                const url = '{{ route("cliente.pedirCodigoNetflix", ":iddet") }}'.replace(':iddet', clienteNetflixCodeButtonRef.dataset.iddet || '');
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'No se pudo obtener el codigo de Netflix.');
                }

                setClienteNetflixCodeModalState('result', {
                    code: data.code,
                    expirationText: data.expires_in_minutes ? `En ${data.expires_in_minutes} minutos vence.` : 'En 15 minutos vence.',
                });
            } catch (error) {
                setClienteNetflixCodeModalState('request');
                openClientTextModal('No se pudo pedir el codigo', error.message || 'Intenta nuevamente en unos minutos.');
            }
        }

        function copyClienteNetflixCodeResult() {
            const code = document.getElementById('cliente_netflix_code_result_code')?.textContent || '';
            if (!code) {
                return;
            }

            navigator.clipboard.writeText(code)
                .then(() => openClientTextModal('Codigo copiado', 'El codigo de Netflix fue copiado al portapapeles.'))
                .catch(() => openClientTextModal('No se pudo copiar', 'Copia el codigo manualmente antes de cerrar el modal.'));
        }

        function switchToTab(target) {
            const trigger = document.querySelector(`[data-bs-target="${target}"]`);
            if (!trigger) {
                return;
            }

            bootstrap.Tab.getOrCreateInstance(trigger).show();
            localStorage.setItem('activeTab', target);
            window.scrollTo({ top: document.getElementById('historialTabs').offsetTop - 90, behavior: 'smooth' });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const forcedTab = @json(session('active_tab'));
            const rememberedTab = localStorage.getItem('activeTab');
            const initialTab = forcedTab || rememberedTab || '#ventas';

            if (initialTab) {
                const trigger = document.querySelector(`[data-bs-target="${initialTab}"]`);
                if (trigger) {
                    bootstrap.Tab.getOrCreateInstance(trigger).show();
                }
            }

            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(event) {
                    localStorage.setItem('activeTab', event.target.getAttribute('data-bs-target'));
                });
            });

            @if ($errors->any() && old('descripcion'))
                bootstrap.Modal.getOrCreateInstance(document.getElementById('crearSoporteModal')).show();
                switchToTab('#soportes');
            @endif
        });
    </script>
@endsection
