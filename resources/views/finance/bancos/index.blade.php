@extends('layouts.navigation')

@section('title', 'Gestión Financiera')

@section('main')
<div class="container-fluid px-4">
    <!-- Título y breadcrumb -->
    <h1 class="mt-4">Gestión Financiera</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Finanzas</li>
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
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @push('scripts')
    <script>
        // Auto-dismiss mensajes después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Cerrar todos los modales al cargar si hay mensajes de sesión
            @if (session('success') || session('error'))
                // Cerrar todos los modales abiertos
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'crear-banco' }));
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editar-banco' }));
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'registrar-transaccion' }));
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'pagar-deuda' }));
            @endif
        });
    </script>
    @endpush

    <div class="mb-4">
        <h3 class="text-primary">Control Financiero</h3>
        <p class="text-muted">Administra bancos, transacciones y deudas en un solo lugar.</p>
    </div>

    <!-- Cards de Resumen Financiero -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-university text-success"></i> Total Disponible
                            </h6>
                            <h2 class="mb-0 text-success fw-bold">
                                ${{ number_format($totalDisponible ?? 0, 2) }}
                            </h2>
                            <small class="text-muted">Suma de todos los bancos</small>
                        </div>
                        <div class="text-success" style="font-size: 3rem; opacity: 0.2;">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card border-danger shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-file-invoice-dollar text-danger"></i> Deudas Pendientes
                            </h6>
                            <h2 class="mb-0 text-danger fw-bold">
                                ${{ number_format($totalDeudasPendientes ?? 0, 2) }}
                            </h2>
                            <small class="text-muted">Total por pagar a proveedores</small>
                        </div>
                        <div class="text-danger" style="font-size: 3rem; opacity: 0.2;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-4 mb-3">
            <div class="card border-info shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-balance-scale text-info"></i> Balance Neto
                            </h6>
                            <h2 class="mb-0 fw-bold" style="color: {{ ($totalDisponible - $totalDeudasPendientes) >= 0 ? '#28a745' : '#dc3545' }}">
                                ${{ number_format(($totalDisponible ?? 0) - ($totalDeudasPendientes ?? 0), 2) }}
                            </h2>
                            <small class="text-muted">Disponible - Deudas</small>
                        </div>
                        <div class="text-info" style="font-size: 3rem; opacity: 0.2;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación de pestañas -->
    <ul class="nav nav-tabs mb-4" id="financeTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="bancos-tab" data-bs-toggle="tab" data-bs-target="#bancos" type="button" role="tab" aria-controls="bancos" aria-selected="true">
                <i class="fas fa-university"></i> Bancos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="deudas-tab" data-bs-toggle="tab" data-bs-target="#deudas" type="button" role="tab" aria-controls="deudas" aria-selected="false">
                <i class="fas fa-file-invoice-dollar"></i> Deudas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="transacciones-tab" data-bs-toggle="tab" data-bs-target="#transacciones" type="button" role="tab" aria-controls="transacciones" aria-selected="false">
                <i class="fas fa-exchange-alt"></i> Transacciones
            </button>
        </li>
    </ul>

    <!-- Contenido de las pestañas -->
    <div class="tab-content" id="financeTabsContent">
        <!-- PESTAÑA BANCOS -->
        <div class="tab-pane fade show active" id="bancos" role="tabpanel" aria-labelledby="bancos-tab">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-university"></i> Lista de Bancos
                    </h6>
                    @if (Auth::user()->hasPermissionTo('bancos.store'))
                        <button type="button" class="btn btn-primary btn-sm" onclick="openCreateBancoModal()">
                            <i class="fas fa-plus"></i> Nuevo Banco
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="openTransferirFondosModal()">
                            <i class="fas fa-plus"></i> Transferir Fondos
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <!-- Encabezado: Búsqueda y Registros por página -->
                    <div class="row mb-3 align-items-end">
                        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                            <label for="bancos-table-search" class="form-label fw-semibold">
                                <i class="fas fa-search text-primary"></i> Buscar:
                            </label>
                            <input id="bancos-table-search"
                                   type="text"
                                   placeholder="Buscar por nombre, tipo, número..."
                                   class="form-control">
                        </div>
                        <div class="col-lg-4 col-md-5 col-12">
                            <label for="bancos-table-rows-per-page" class="form-label fw-semibold">
                                <i class="fas fa-list text-primary"></i> Mostrar:
                            </label>
                            <select id="bancos-table-rows-per-page" class="form-select">
                                <option value="5">5 registros</option>
                                <option value="10" selected>10 registros</option>
                                <option value="20">20 registros</option>
                                <option value="50">50 registros</option>
                                <option value="100">100 registros</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="bancos-table"
                               data-table="bancos-table"
                               class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th class="sortable" data-type="number" data-col="0">
                                        ID
                                        <span class="sort-arrow">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                            </svg>
                                        </span>
                                    </th>
                                    <th class="sortable" data-type="string" data-col="1">
                                        Banco
                                        <span class="sort-arrow">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                            </svg>
                                        </span>
                                    </th>
                                    <th>Tipo</th>
                                    <th>Número Cuenta</th>
                                    <th>Monto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bancos as $banco)
                                    <tr>
                                        <td>{{ $banco->idban }}</td>
                                        <td>{{ $banco->nombreban }}</td>
                                        <td>{{ $banco->tipoban }}</td>
                                        <td>{{ $banco->numeroban }}</td>
                                        <td class="fw-bold text-success" style="font-size: 1.1em;">${{ number_format($banco->monto ?? 0, 2) }}</td>
                                        <td>
                                            @if (Auth::user()->hasPermissionTo('bancos.update'))
                                            <button type="button" class="btn btn-sm btn-warning" onclick="editarBanco({{ $banco->idban }}, '{{ $banco->nombreban }}', '{{ $banco->tipoban }}', '{{ $banco->detalleban }}')" title="Editar banco">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @endif
                                            @if (Auth::user()->hasPermissionTo('bancos.transacciones.store'))
                                            <button type="button" class="btn btn-sm btn-success" onclick="openTransaccionModal({{ $banco->idban }})" title="Registrar transacción">
                                                <i class="fas fa-dollar-sign"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No hay bancos registrados</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Información de paginación y controles -->
                    <div class="row mt-3 align-items-center">
                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                            <div id="bancos-table-row-info" class="text-muted"></div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div id="bancos-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA DEUDAS -->
        <div class="tab-pane fade" id="deudas" role="tabpanel" aria-labelledby="deudas-tab">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-invoice-dollar"></i> Lista de Deudas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3 align-items-end">
                        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                            <label for="deudas-table-search" class="form-label fw-semibold">
                                <i class="fas fa-search text-primary"></i> Buscar:
                            </label>
                            <input id="deudas-table-search"
                                   type="text"
                                   placeholder="Buscar por proveedor, monto..."
                                   class="form-control">
                        </div>
                        <div class="col-lg-4 col-md-5 col-12">
                            <label for="deudas-table-rows-per-page" class="form-label fw-semibold">
                                <i class="fas fa-list text-primary"></i> Mostrar:
                            </label>
                            <select id="deudas-table-rows-per-page" class="form-select">
                                <option value="5">5 registros</option>
                                <option value="10" selected>10 registros</option>
                                <option value="20">20 registros</option>
                                <option value="50">50 registros</option>
                                <option value="100">100 registros</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="deudas-table"
                               data-table="deudas-table"
                               class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th class="sortable" data-type="number" data-col="0">
                                        ID
                                        <span class="sort-arrow">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                            </svg>
                                        </span>
                                    </th>
                                    <th>Proveedor</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deudas as $deuda)
                                <tr>
                                    <td>{{ $deuda->id }}</td>
                                    <td>{{ $deuda->proveedor->nombrepro ?? 'N/A' }}</td>
                                    <td class="fw-bold text-danger" style="font-size: 1.1em;">
                                        ${{ number_format($deuda->monto, 2) }}
                                        @if($deuda->monto_pagado > 0)
                                            <br><small class="text-muted">Pagado: ${{ number_format($deuda->monto_pagado, 2) }}</small>
                                            <br><small class="text-success">Restante: ${{ number_format($deuda->monto_restante, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $deuda->estado === 'pendiente' ? 'warning' : 'success' }}">
                                            {{ ucfirst($deuda->estado) }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($deuda->updated_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($deuda->estado === 'pendiente' && Auth::user()->hasPermissionTo('bancos.transacciones.store'))
                                        <button type="button" class="btn btn-sm btn-success" onclick="pagarDeuda({{ $deuda->id }}, {{ $deuda->monto_restante }})" title="Pagar deuda">
                                            <i class="fas fa-dollar-sign"></i> Pagar
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No hay deudas registradas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Información de paginación y controles -->
                    <div class="row mt-3 align-items-center">
                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                            <div id="deudas-table-row-info" class="text-muted"></div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div id="deudas-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA TRANSACCIONES -->
        <div class="tab-pane fade" id="transacciones" role="tabpanel" aria-labelledby="transacciones-tab">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exchange-alt"></i> Lista de Transacciones
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3 align-items-end">
                        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                            <label for="transacciones-table-search" class="form-label fw-semibold">
                                <i class="fas fa-search text-primary"></i> Buscar:
                            </label>
                            <input id="transacciones-table-search"
                                   type="text"
                                   placeholder="Buscar por banco, referencia..."
                                   class="form-control">
                        </div>
                        <div class="col-lg-4 col-md-5 col-12">
                            <label for="transacciones-table-rows-per-page" class="form-label fw-semibold">
                                <i class="fas fa-list text-primary"></i> Mostrar:
                            </label>
                            <select id="transacciones-table-rows-per-page" class="form-select">
                                <option value="5">5 registros</option>
                                <option value="10" selected>10 registros</option>
                                <option value="20">20 registros</option>
                                <option value="50">50 registros</option>
                                <option value="100">100 registros</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="transacciones-table"
                               data-table="transacciones-table"
                               class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th class="sortable" data-type="number" data-col="0">
                                        ID
                                        <span class="sort-arrow">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                            </svg>
                                        </span>
                                    </th>
                                    <th>Banco</th>
                                    <th>Tipo</th>
                                    <th>Monto Anterior</th>
                                    <th>Monto Actualizado</th>
                                    <th>Monto Transacción</th>
                                    <th>Referencia</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allTransactions as $transaccion)
                                <tr>
                                    <td>{{ $transaccion->id }}</td>
                                    <td>{{ $transaccion->banco->nombreban ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaccion->tipo === 'ingreso' ? 'success' : 'danger' }}">
                                            {{ ucfirst($transaccion->tipo) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($transaccion->monto_anterior, 2) }}</td>
                                    <td>${{ number_format($transaccion->monto_actualizado, 2) }}</td>
                                    <td class="fw-bold text-{{ $transaccion->tipo === 'ingreso' ? 'success' : 'danger' }}" style="font-size: 1.1em;">${{ number_format($transaccion->monto_transaccion, 2) }}</td>
                                    <td>{{ $transaccion->referencia ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($transaccion->fecha)->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No hay transacciones registradas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Información de paginación y controles -->
                    <div class="row mt-3 align-items-center">
                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                            <div id="transacciones-table-row-info" class="text-muted"></div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div id="transacciones-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Transacción -->
@include('finance.bancos.modals.transaccion')

<!-- Modal Crear Banco -->
@include('finance.bancos.modals.crear')

<!-- Modal Editar Banco -->
@include('finance.bancos.modals.editar')

<!-- Modal Pagar Deuda -->
@include('finance.bancos.modals.pagar-deuda')

<!-- Modal Transferir Fondos -->
@include('finance.bancos.modals.transferir')

<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
<script>
    // Inicializar enhanced tables
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tablas enhanced
        if (typeof window.enhancedTable !== 'undefined') {
            window.enhancedTable.init('bancos-table');
            window.enhancedTable.init('deudas-table');
            window.enhancedTable.init('transacciones-table');
        }
    });

    // Función para abrir modal de transacción
    function openTransaccionModal(bancoId) {
        document.getElementById('banco_id').value = bancoId;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'registrar-transaccion' }));
    }

    // Función para crear nuevo banco
    function openCreateBancoModal() {
        // Limpiar formulario
        document.getElementById('crear_nombreban').value = '';
        document.getElementById('crear_tipoban').value = '';
        document.getElementById('crear_numeroban').value = '';
        document.getElementById('crear_propietarioban').value = '';
        document.getElementById('crear_cedulaban').value = '';
        document.getElementById('crear_detalleban').value = '';
        document.getElementById('crear_foto').value = '';
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'crear-banco' }));
    }

    // Función para editar banco
    function editarBanco(idban, nombreban, tipoban, detalleban) {
        document.getElementById('edit_nombreban').value = nombreban;
        document.getElementById('edit_tipoban').value = tipoban;
        document.getElementById('edit_detalleban').value = detalleban || '';

        // Actualizar acción del formulario
        const form = document.getElementById('editarBancoForm');
        const routeTemplate = "{{ route('bancos.update', ['id' => ':ID:']) }}";
        form.action = routeTemplate.replace(':ID:', idban);

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editar-banco' }));
    }

    // Función para pagar deuda
    window.pagarDeuda = function(deudaId, montoRestante) {
        // Buscar datos de la deuda desde la tabla
        const deudas = @json($deudas);
        const deuda = deudas.find(d => d.id === deudaId);

        if (!deuda) {
            alert('Deuda no encontrada');
            return;
        }

        const montoTotal = parseFloat(deuda.monto);
        const montoPagado = parseFloat(deuda.monto_pagado) || 0;
        const restante = montoTotal - montoPagado;

        // Llenar campos del modal
        document.getElementById('deuda_id_display').textContent = deuda.id;
        document.getElementById('monto_total_display').textContent = montoTotal.toFixed(2);
        document.getElementById('monto_restante_display').textContent = restante.toFixed(2);
        document.getElementById('deuda_id_input').value = deuda.id;
        document.getElementById('monto_restante_input').value = restante;
        document.getElementById('monto_abono').value = '';
        document.getElementById('monto_abono').max = restante;

        // Actualizar acción del formulario
        const form = document.getElementById('pagarDeudaForm');
        const routeTemplate = "{{ route('bancos.pagar-deuda', ['id' => ':ID:']) }}";
        form.action = routeTemplate.replace(':ID:', deuda.id);

        // Abrir modal
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'pagar-deuda' }));
    };
    // Función para abrir modal de transferir fondos
    function openTransferirFondosModal() {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'transferir' }));
    }
</script>
@endsection
