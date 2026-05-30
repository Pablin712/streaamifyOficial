@extends('layouts.navigation')

@section('title', 'Servicios')

@section('main')
<div class="container-fluid px-4">
    <!-- Título y breadcrumb -->
    <h1 class="mt-4">Servicios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Servicios</li>
    </ol>

    <!-- Descripción y alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <h3 class="text-primary">Gestión de Servicios</h3>
        <p class="text-muted">Muestra tabla de todos los servicios y sus precios para la venta.</p>
    </div>

    <!-- Botón crear servicio -->
    @if (Auth::user()->hasPermissionTo('servicios.create'))
        <div class="mb-3">
            <button onclick="openCreateModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear Servicio
            </button>
        </div>
    @endif

    <!-- Contenedor de alertas -->
    <div id="alert-container"></div>

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Lista de Servicios
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="servicios-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="servicios-table-search"
                           type="text"
                           placeholder="Buscar por código, nombre, precio..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="servicios-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="servicios-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="servicios-table"
                       data-table="servicios-table"
                       class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="0">
                                Código
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
                            <th class="sortable" data-type="number" data-col="2">
                                PVP completo
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="3">
                                PVP individual
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="4">
                                PVP combo
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="5">
                                Reventa 1 pant
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="6">
                                Reventa 1 cuent
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            @if (Auth::user()->hasAnyPermission(['servicios.edit', 'servicios.destroy']))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($servicios as $servicio)
                            <tr>
                                <td>{{ $servicio->idser }}</td>
                                <td>{{ $servicio->nombreser }}</td>
                                <td>${{ number_format($servicio->completoser, 2) }}</td>
                                <td>${{ number_format($servicio->precioser, 2) }}</td>
                                <td>${{ number_format($servicio->comboser, 2) }}</td>
                                <td>${{ number_format($servicio->reventaser, 2) }}</td>
                                <td>${{ number_format($servicio->revcompser, 2) }}</td>
                                @if (Auth::user()->hasAnyPermission(['servicios.edit', 'servicios.destroy']))
                                    <td>
                                        <div class="action-buttons">
                                            @if (Auth::user()->hasPermissionTo('servicios.edit'))
                                                <button onclick="openEditModal('{{ $servicio->idser }}')" class="btn btn-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                            @if (Auth::user()->hasPermissionTo('servicios.destroy'))
                                                <button onclick="openDeleteModal('{{ $servicio->idser }}')" class="btn btn-danger btn-sm" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay servicios disponibles.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer: Info y paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="servicios-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="servicios-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales -->
@include('inventory.servicios.modals.create')
@include('inventory.servicios.modals.edit')
@include('inventory.servicios.modals.delete')
@endsection

@section('scripts')
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>

<script>
console.log('Vista de servicios cargada con modales');

// Verificar Alpine.js
document.addEventListener('alpine:init', () => {
    console.log('✅ Alpine.js inicializado correctamente');
});

// ============================================================================
// FUNCIONES DE MODAL - CREAR
// ============================================================================
function openCreateModal() {
    console.log('🔷 Abriendo modal de crear servicio...');
    const form = document.getElementById('createServicioForm');
    if (form) form.reset();
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createServicioModal' }));
}

function closeCreateModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createServicioModal' }));
}

function submitCreate(event) {
    event.preventDefault();
    console.log('📤 Enviando formulario de crear servicio...');

    const formData = new FormData(event.target);

    fetch('{{ route("servicios.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Servicio creado exitosamente');
            showAlert(data.message, 'success');
            closeCreateModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            console.log('❌ Error en la respuesta:', data.error);
            showAlert(data.error || 'Error al crear el servicio', 'danger');
        }
    })
    .catch(error => {
        console.error('❌ Error en la petición:', error);
        showAlert('Error al procesar la solicitud', 'danger');
    });
}

// ============================================================================
// FUNCIONES DE MODAL - EDITAR
// ============================================================================
function openEditModal(idser) {
    console.log('🔷 Abriendo modal de editar para ID:', idser);

    const url = '{{ route("servicios.edit", "__ID__") }}'.replace('__ID__', idser);

    fetch(url, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Datos del servicio cargados');

            const s = data.servicio;
            document.getElementById('edit-idser').value = s.idser;
            document.getElementById('edit-idser-display').value = s.idser;
            document.getElementById('edit-nombreser').value = s.nombreser;
            document.getElementById('edit-completoser').value = s.completoser;
            document.getElementById('edit-precioser').value = s.precioser;
            document.getElementById('edit-comboser').value = s.comboser;
            document.getElementById('edit-reventaser').value = s.reventaser;
            document.getElementById('edit-revcompser').value = s.revcompser;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-servicio' }));
        } else {
            console.log('❌ Error al cargar datos:', data.error);
            showAlert('Error al cargar los datos del servicio', 'danger');
        }
    })
    .catch(error => {
        console.error('❌ Error en la petición:', error);
        showAlert('Error al procesar la solicitud', 'danger');
    });
}function closeEditModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'edit-servicio' }));
}

function submitEdit(event) {
    event.preventDefault();
    console.log('📤 Enviando formulario de editar servicio...');

    const idser = document.getElementById('edit-idser').value;
    const formData = new FormData(event.target);

    const url = '{{ route("servicios.update", "__ID__") }}'.replace('__ID__', idser);

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            _method: 'PUT',
            nombreser: formData.get('nombreser'),
            completoser: formData.get('completoser'),
            precioser: formData.get('precioser'),
            comboser: formData.get('comboser'),
            reventaser: formData.get('reventaser'),
            revcompser: formData.get('revcompser')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Servicio actualizado exitosamente');
            showAlert(data.message, 'success');
            closeEditModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            console.log('❌ Error en la respuesta:', data.error);
            showAlert(data.error || 'Error al actualizar el servicio', 'danger');
        }
    })
    .catch(error => {
        console.error('❌ Error en la petición:', error);
        showAlert('Error al procesar la solicitud', 'danger');
    });
}

// ============================================================================
// FUNCIONES DE MODAL - ELIMINAR
// ============================================================================
function openDeleteModal(idser) {
    console.log('🗑️ Abriendo modal de eliminación para ID:', idser);

    const url = '{{ route("servicios.edit", "__ID__") }}'.replace('__ID__', idser);

    fetch(url, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Datos del servicio cargados');

            const s = data.servicio;
            document.getElementById('delete-idser').value = s.idser;
            document.getElementById('delete-idser-info').textContent = s.idser;
            document.getElementById('delete-nombreser-info').textContent = s.nombreser;
            document.getElementById('delete-completoser-info').textContent = parseFloat(s.completoser).toFixed(2);
            document.getElementById('delete-precioser-info').textContent = parseFloat(s.precioser).toFixed(2);

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-servicio' }));
        } else {
            console.log('❌ Error al cargar datos:', data.error);
            showAlert('Error al cargar los datos del servicio', 'danger');
        }
    })
    .catch(error => {
        console.error('❌ Error en la petición:', error);
        showAlert('Error al procesar la solicitud', 'danger');
    });
}function closeDeleteModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'delete-servicio' }));
}

function confirmDelete() {
    const idser = document.getElementById('delete-idser').value;
    console.log('📤 Confirmando eliminación del servicio ID:', idser);

    const url = '{{ route("servicios.destroy", "__ID__") }}'.replace('__ID__', idser);

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Servicio eliminado exitosamente');
            showAlert(data.message, 'success');
            closeDeleteModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            console.log('❌ Error en la respuesta:', data.error);
            showAlert(data.error || 'Error al eliminar el servicio', 'danger');
        }
    })
    .catch(error => {
        console.error('❌ Error en la petición de eliminación:', error);
        showAlert('Error al procesar la solicitud', 'danger');
    });
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
