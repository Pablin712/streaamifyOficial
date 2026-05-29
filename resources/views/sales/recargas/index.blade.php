@extends('layouts.navigation')

@section('title', 'Solicitudes de Recarga')

@section('main')
    <div class="container-fluid px-4">
        <!-- Título y breadcrumb -->
        <h1 class="mt-4">Solicitudes de Recarga</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active">Recargas</li>
        </ol>

        <!-- Descripción y alertas -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="mb-4">
            <h3 class="text-primary">Gestión de Recargas</h3>
            <p class="text-muted">Visualiza todas las solicitudes de recarga enviadas por los clientes y realiza las
                acciones necesarias para aprobar o
                rechazar.</p>
        </div>

        <!-- Tabla Enhanced v2 -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-table"></i> Solicitudes de Recarga
                </h6>
            </div>
            <div class="card-body">
                <!-- Encabezado: Búsqueda y Registros por página -->
                <div class="row mb-3 align-items-end">
                    <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                        <label for="recargas-table-search" class="form-label fw-semibold">
                            <i class="fas fa-search text-primary"></i> Buscar:
                        </label>
                        <input id="recargas-table-search" type="text" placeholder="Buscar por cliente, banco, valor..."
                            class="form-control">
                    </div>
                    <div class="col-lg-4 col-md-5 col-12">
                        <label for="recargas-table-rows-per-page" class="form-label fw-semibold">
                            <i class="fas fa-list text-primary"></i> Mostrar:
                        </label>
                        <select id="recargas-table-rows-per-page" class="form-select">
                            <option value="5" selected>5 registros</option>
                            <option value="10">10 registros</option>
                            <option value="20">20 registros</option>
                            <option value="50">50 registros</option>
                            <option value="100">100 registros</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="recargas-table" data-table="recargas-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="sortable" data-type="number" data-col="0">
                                    ID
                                    <span class="sort-arrow">
                                        <svg width="14" height="14" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5" />
                                        </svg>
                                    </span>
                                </th>
                                <th class="sortable" data-type="string" data-col="1">
                                    Cliente
                                    <span class="sort-arrow">
                                        <svg width="14" height="14" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5" />
                                        </svg>
                                    </span>
                                </th>
                                <th class="sortable" data-type="string" data-col="2">
                                    Banco
                                    <span class="sort-arrow">
                                        <svg width="14" height="14" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5" />
                                        </svg>
                                    </span>
                                </th>
                                <th data-type="string">Comprobante</th>
                                <th class="sortable" data-type="number" data-col="4">
                                    Valor
                                    <span class="sort-arrow">
                                        <svg width="14" height="14" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5" />
                                        </svg>
                                    </span>
                                </th>
                                <th class="sortable" data-type="string" data-col="5">
                                    Fecha
                                    <span class="sort-arrow">
                                        <svg width="14" height="14" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5" />
                                        </svg>
                                    </span>
                                </th>
                                <th class="sortable" data-type="string" data-col="6">
                                    Estado
                                    <span class="sort-arrow">
                                        <svg width="14" height="14" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5" />
                                        </svg>
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recargas as $recarga)
                                <tr>
                                    <td>{{ $recarga->idrec }}</td>
                                    <td>{{ $recarga->cliente->nombrecli }}</td>
                                    <td>{{ $recarga->banco->nombreban }}</td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span class="me-2 badge bg-secondary">{{ $recarga->numcomprobante }}</span>
                                            <button type="button" class="btn btn-info btn-sm"
                                                onclick="verComprobante('{{ asset('storage/' . $recarga->foto) }}')"
                                                title="Ver comprobante">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>${{ number_format($recarga->valor, 2) }}</td>
                                    <td>{{ $recarga->created_at }}</td>
                                    <td>
                                        <span
                                            class="badge
                                        @if ($recarga->estado->nombre === 'Pendiente') bg-warning
                                        @elseif ($recarga->estado->nombre === 'Rechazado') bg-danger
                                        @elseif ($recarga->estado->nombre === 'Aprobado') bg-success @endif">
                                            {{ ucfirst($recarga->estado->nombre) }}
                                        </span>
                                        @if ($recarga->estado->nombre === 'Pendiente')
                                            @if (Auth::user()->hasPermissionTo('empleado.recargas.updateEstado'))
                                                <button type="button" class="btn btn-success btn-sm"
                                                    onclick="confirmarAprobarRecarga({{ $recarga->idrec }}, '{{ route('empleado.recargas.updateEstado', $recarga->idrec) }}')"
                                                    title="Aprobar recarga">
                                                    Aprobar
                                                </button>

                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmarRechazarRecarga({{ $recarga->idrec }}, '{{ route('empleado.recargas.updateEstado', $recarga->idrec) }}')"
                                                    title="Rechazar recarga">
                                                    Rechazar
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer: Info y paginación -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div id="recargas-table-row-info" class="text-muted"></div>
                    </div>
                    <div class="col-md-6">
                        <div id="recargas-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de comprobante -->
        @include('sales.recargas.modals.comprobante')
        @include('sales.recargas.modals.aprobar')
        @include('sales.recargas.modals.rechazar')
    </div>
@endsection
@section('scripts')
    <!-- Enhanced Table v2.0 -->
    <script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>

    <script>
        console.log('Vista de recargas cargada con Enhanced Table v2.0');
        console.log('Total de recargas en la tabla:', {{ $recargas->count() }});

        // ============================================================================
        // FUNCIÓN DE MODAL - VER COMPROBANTE
        // ============================================================================
        function verComprobante(imgUrl) {
            console.log('🔷 Abriendo modal de comprobante:', imgUrl);

            // Setear la imagen
            document.getElementById('comprobanteImg').src = imgUrl;

            // Abrir modal
            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'ver-comprobante'
            }));
        }

        // ============================================================================
        // FUNCIONES DE MODAL - APROBAR/RECHAZAR RECARGA
        // ============================================================================
        function confirmarAprobarRecarga(id, url) {
            console.log('🔷 Abriendo modal de aprobar recarga:', id);
            const form = document.getElementById('aprobarRecargaForm');
            form.action = url;
            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'confirmar-aprobar-recarga'
            }));
        }

        function confirmarRechazarRecarga(id, url) {
            console.log('🔷 Abriendo modal de rechazar recarga:', id);
            const form = document.getElementById('rechazarRecargaForm');
            form.action = url;
            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'confirmar-rechazar-recarga'
            }));
        }
    </script>
@endsection
