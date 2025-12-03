@extends('layouts.navigation')

@section('title', 'Ventas')

@section('main')
<div class="container-fluid px-4")
    <!-- Título y breadcrumb -->
    <h1 class="mt-4">Ventas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Ventas</li>
    </ol>

    <!-- Descripción y alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Contenedor para alertas dinámicas -->
    <div id="alert-container"></div>

    <div class="mb-4">
        <h3 class="text-primary">Gestión de Ventas</h3>
        <p class="text-muted">
            Revisa las ventas activas y detalles de los servicios vendidos.
            Aquí podrás gestionar las ventas y los detalles asociados, ver los usuarios activos y las opciones de renovación.
            <strong>Es necesario realizar más de 5 ventas al día.</strong>
        </p>
    </div>
    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ingresos (Hoy)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ $ingresos_dia }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ventas (Hoy)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $ventas_dia }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ingresos/Venta</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if ($ventas_dia > 0)
                                    ${{ number_format($ingresos_dia / $ventas_dia, 2) }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Rendimiento del Día -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Rendimiento del Día</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $rendimiento = '';
                                    if ($ventas_dia < 5) {
                                        $rendimiento = 'Bajo';
                                    } elseif ($ventas_dia <= 10) {
                                        $rendimiento = 'Regular';
                                    } elseif ($ventas_dia <= 25) {
                                        $rendimiento = 'Bueno';
                                    } else {
                                        $rendimiento = 'Excelente';
                                    }
                                @endphp
                                {{ $rendimiento }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Clientes Autenticados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $autenticados }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-id-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ventas Automatizadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $ventasLaravel }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-robot fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Recargas pendientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $recargasPendientes }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Pedidos pendientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pedidosPendientes }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón crear venta -->
    @if (Auth::user()->hasPermissionTo('ventas.create'))
        <div class="mb-3">
            <a href="{{ route('ventas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear Venta
            </a>
        </div>
    @endif

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Lista de Ventas
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="ventas-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="ventas-table-search"
                           type="text"
                           placeholder="Buscar por cliente, empleado, ID..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="ventas-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="ventas-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="ventas-table"
                       data-table="ventas-table"
                       data-server-side="true"
                       data-search-url="{{ route('ventas') }}"
                       class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="idven">
                                ID Recibo
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="1">
                                Cliente
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="2">
                                Empleado
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="fechaven">
                                Fecha
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="totalpagoven">
                                Total
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            @if (Auth::user()->hasAnyPermission(['ventas.edit', 'ventas.renew', 'ventas.sendInvoice', 'ventas.destroy']))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargarán via AJAX -->
                        <tr>
                            <td colspan="6" class="text-center p-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer: Info y paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="ventas-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="ventas-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    @include('sales.ventas.modals.view-details')
    @include('sales.ventas.modals.confirm-delete')
    @include('sales.ventas.modals.confirm-send-invoice')

</div>
@endsection

@section('scripts')
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

<!-- Opcional: Librerías para exportación -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<script>
    console.log('🔷 Vista de ventas cargada con Enhanced Table v2.0 Server-side');

    // =================================================================
    // VER DETALLES DE VENTA
    // =================================================================
    function openViewDetailsModal(idven) {
        console.log('🔷 Abriendo modal de detalles para venta:', idven);

        // Abrir modal
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'view-venta-details' }));

        // Actualizar número de venta
        document.getElementById('view_venta_number').textContent = idven;

        // Mostrar loading
        document.getElementById('view_details_loading').style.display = 'block';
        document.getElementById('view_details_content').style.display = 'none';
        document.getElementById('view_details_error').style.display = 'none';

        // Cargar detalles vía AJAX
        const url = '{{ route("ventas.details", "__ID__") }}'.replace('__ID__', idven);

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Detalles cargados:', data);

            if (data.success) {
                // Llenar datos del cliente
                document.getElementById('view_cliente_nombre').textContent = data.venta.cliente.nombrecli;
                document.getElementById('view_cliente_telefono').textContent = data.venta.cliente.telefonocli;
                document.getElementById('view_cliente_email').textContent = data.venta.cliente.email;

                // Llenar datos del empleado y venta
                document.getElementById('view_empleado_nombre').textContent = data.venta.empleado.nombreemp;
                document.getElementById('view_venta_fecha').textContent = data.venta.fechaven;
                document.getElementById('view_venta_metodo_pago').textContent = data.venta.metodopagoven;

                // Llenar tabla de detalles
                const tbody = document.getElementById('view_details_items');
                tbody.innerHTML = '';

                data.venta.detalles.forEach(detalle => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${detalle.cuenta}</td>
                        <td>${detalle.perfil}</td>
                        <td>${detalle.descripcion}</td>
                        <td class="text-center">${detalle.cantidad}</td>
                        <td class="text-end">$${detalle.precio}</td>
                        <td class="text-end"><strong>$${detalle.subtotal}</strong></td>
                    `;
                    tbody.appendChild(row);
                });

                // Total
                document.getElementById('view_venta_total').textContent = '$' + data.venta.totalpagoven;

                // Ocultar loading, mostrar content
                document.getElementById('view_details_loading').style.display = 'none';
                document.getElementById('view_details_content').style.display = 'block';
            } else {
                throw new Error(data.message || 'Error al cargar los detalles');
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);

            document.getElementById('view_details_loading').style.display = 'none';
            document.getElementById('view_details_error').style.display = 'block';
            document.getElementById('view_details_error_message').textContent = error.message;
        });
    }

    // =================================================================
    // ELIMINAR VENTA
    // =================================================================
    function openDeleteModal(idven, cliente, total) {
        console.log('🗑️ Abriendo modal de eliminar para venta:', idven);

        document.getElementById('delete_venta_id').value = idven;
        document.getElementById('delete_venta_number').textContent = idven;
        document.getElementById('delete_cliente_nombre').textContent = cliente;
        document.getElementById('delete_venta_total').textContent = '$' + total;

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-delete-venta' }));
    }

    function submitDeleteVenta(event) {
        event.preventDefault();
        console.log('📤 Eliminando venta...');

        const idven = document.getElementById('delete_venta_id').value;
        const url = '{{ route("ventas.destroy", "__ID__") }}'.replace('__ID__', idven);

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('✅ Respuesta:', data);

            if (data.success) {
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-delete-venta' }));
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.message || 'Error al eliminar la venta');
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            showAlert(error.message || 'Error al eliminar la venta', 'danger');
        });
    }

    // =================================================================
    // ENVIAR FACTURA
    // =================================================================
    function openSendInvoiceModal(idven, cliente, email, total) {
        console.log('📧 Abriendo modal de enviar factura para venta:', idven);

        if (!email || email === 'No especificado' || email === '') {
            showAlert('El cliente no tiene email registrado', 'warning');
            return;
        }

        document.getElementById('send_venta_id').value = idven;
        document.getElementById('send_venta_number').textContent = idven;
        document.getElementById('send_cliente_nombre').textContent = cliente;
        document.getElementById('send_cliente_email').textContent = email;
        document.getElementById('send_venta_total').textContent = '$' + total;

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-send-invoice' }));
    }

    function submitSendInvoice(event) {
        event.preventDefault();
        console.log('📤 Enviando factura...');

        const idven = document.getElementById('send_venta_id').value;
        const url = '{{ route("ventas.sendInvoice", "__ID__") }}'.replace('__ID__', idven);

        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Enviando...';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('✅ Respuesta:', data);

            if (data.success) {
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-send-invoice' }));
                showAlert(data.message, 'success');
            } else {
                throw new Error(data.message || 'Error al enviar la factura');
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            showAlert(error.message || 'Error al enviar la factura', 'danger');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    // =================================================================
    // UTILIDADES
    // =================================================================
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        if (!alertContainer) {
            console.warn('Alert container no encontrado');
            alert(message);
            return;
        }

        const alertClass = type === 'success' ? 'alert-success' :
                          type === 'warning' ? 'alert-warning' : 'alert-danger';
        const iconClass = type === 'success' ? 'fa-check-circle' :
                         type === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle';

        const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${iconClass} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        alertContainer.innerHTML = alert;
        setTimeout(() => alertContainer.innerHTML = '', 5000);
    }
</script>
@endsection
