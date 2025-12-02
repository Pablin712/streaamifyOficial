@extends('layouts.navigation')

@section('title', 'Clientes')

@section('main')
<div class="container-fluid px-4">
    <!-- Título y breadcrumb -->
    <h1 class="mt-4">Clientes</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Clientes</li>
    </ol>

    <!-- Descripción y alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <h3 class="text-primary">Información de clientes</h3>
        <p class="text-muted">Muestra la tabla de clientes, el número de usuarios que posee y lo facturado en el mes actual.</p>
    </div>

    <!-- Estadísticas -->
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
    </div>

    <!-- Alert Container para mensajes dinámicos -->
    <div id="alert-container"></div>

    <!-- Botones -->
    @if (Auth::user()->hasPermissionTo('clientes.create'))
        <div class="mb-3">
            <button type="button" onclick="openCreateModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear Cliente
            </button>
            <a href="{{ route('clientes.export') }}" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
        </div>
    @endif

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Lista de Clientes
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="clientes-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="clientes-table-search"
                           type="text"
                           placeholder="Buscar por nombre, teléfono, correo..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="clientes-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="clientes-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="clientes-table"
                       data-table="clientes-table"
                       data-server-side="true"
                       data-search-url="{{ route('clientes') }}"
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
                                Nombre
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="2">
                                Teléfono
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="3">
                                Correo
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="4">
                                Usuarios
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="5">
                                Facturado este mes
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="6">
                                Saldo
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="7">
                                Autenticado
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            @if (Auth::user()->hasAnyPermission(['clientes.edit', 'clientes.destroy']))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="9" class="text-center p-4">
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
                    <div id="clientes-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="clientes-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales -->
@include('sales.clientes.modals.create')
@include('sales.clientes.modals.edit')
@include('sales.clientes.modals.delete')
@endsection

@section('scripts')
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

<script>
    console.log('Vista de clientes cargada con Enhanced Table v2.0 Server-side');

    // ============================================================================
    // FUNCIONES DE MODAL - CREAR
    // ============================================================================
    function openCreateModal() {
        console.log('🔷 Abriendo modal de crear cliente...');
        const form = document.getElementById('createClienteForm');
        if (form) form.reset();
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createClienteModal' }));
    }

    function closeCreateModal() {
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createClienteModal' }));
    }

    async function submitCreate(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario de crear cliente...');

        const formData = new FormData(event.target);

        try {
            const response = await fetch('{{ route("clientes.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Cliente creado:', data);
                showAlert(data.message, 'success');
                closeCreateModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('❌ Error al crear:', data);
                showAlert(data.message || 'Error al crear el cliente', 'danger');
            }
        } catch (error) {
            console.error('❌ Error de red:', error);
            showAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
        }
    }

    // ============================================================================
    // FUNCIONES DE MODAL - EDITAR
    // ============================================================================
    function openEditModal(idcli) {
        console.log('🔷 Abriendo modal de editar cliente:', idcli);

        const url = '{{ route("clientes.edit", "__ID__") }}'.replace('__ID__', idcli);

        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_cliente_id').value = data.cliente.idcli;
                document.getElementById('edit_nombrecli').value = data.cliente.nombrecli;
                document.getElementById('edit_telefonocli').value = data.cliente.telefonocli;

                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editClienteModal' }));
            }
        })
        .catch(error => {
            console.error('Error al cargar cliente:', error);
            showAlert('Error al cargar los datos del cliente', 'danger');
        });
    }

    function closeEditModal() {
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editClienteModal' }));
    }

    async function submitEdit(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario de editar cliente...');

        const idcli = document.getElementById('edit_cliente_id').value;
        const formData = new FormData(event.target);

        const url = '{{ route("clientes.update", "__ID__") }}'.replace('__ID__', idcli);

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'PUT',
                    nombrecli: formData.get('nombrecli'),
                    telefonocli: formData.get('telefonocli')
                })
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Cliente actualizado:', data);
                showAlert(data.message, 'success');
                closeEditModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('❌ Error al actualizar:', data);
                showAlert(data.message || 'Error al actualizar el cliente', 'danger');
            }
        } catch (error) {
            console.error('❌ Error de red:', error);
            showAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
        }
    }

    // ============================================================================
    // FUNCIONES DE MODAL - ELIMINAR
    // ============================================================================
    function openDeleteModal(idcli) {
        console.log('🔷 Abriendo modal de eliminar cliente:', idcli);

        const url = '{{ route("clientes.edit", "__ID__") }}'.replace('__ID__', idcli);

        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('delete_cliente_id').value = data.cliente.idcli;
                document.getElementById('delete_cliente_idcli').textContent = data.cliente.idcli;
                document.getElementById('delete_cliente_nombre').textContent = data.cliente.nombrecli;
                document.getElementById('delete_cliente_telefono').textContent = data.cliente.telefonocli;

                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'deleteClienteModal' }));
            }
        })
        .catch(error => {
            console.error('Error al cargar cliente:', error);
            showAlert('Error al cargar los datos del cliente', 'danger');
        });
    }

    function closeDeleteModal() {
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteClienteModal' }));
    }

    async function submitDelete(event) {
        event.preventDefault();
        console.log('🗑️ Eliminando cliente...');

        const idcli = document.getElementById('delete_cliente_id').value;
        const url = '{{ route("clientes.destroy", "__ID__") }}'.replace('__ID__', idcli);

        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Cliente eliminado');
                showAlert(data.message, 'success');
                closeDeleteModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('❌ Error al eliminar:', data);
                showAlert(data.message || 'Error al eliminar el cliente', 'danger');
            }
        } catch (error) {
            console.error('❌ Error de red:', error);
            showAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
        }
    }

    // ============================================================================
    // FUNCIÓN DE ALERTAS
    // ============================================================================
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        const alert = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        alertContainer.innerHTML = alert;
        setTimeout(() => alertContainer.innerHTML = '', 5000);
    }
</script>
@endsection
