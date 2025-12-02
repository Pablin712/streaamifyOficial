@extends('layouts.navigation')

@section('title', 'Mantenimientos')

@section('main')
<div class="container-fluid px-4">
    <!-- Título y breadcrumb -->
    <h1 class="mt-4">Mantenimientos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Mantenimientos</li>
    </ol>

    <!-- Descripción y alertas -->
    <div id="alert-container"></div>

    <div class="mb-4">
        <h3>Gestión de Mantenimientos</h3>
        <p class="text-muted">Visualiza todos los mantenimientos registrados y realiza las acciones necesarias como editar o eliminar.</p>
    </div>

    <!-- Botón crear mantenimiento -->
    @if (Auth::user()->hasPermissionTo('mantenimientos.store'))
        <div class="mb-3">
            <button onclick="openCreateModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear Mantenimiento
            </button>
        </div>
    @endif

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-table"></i> Lista de Mantenimientos
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="mantenimientos-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search"></i> Buscar:
                    </label>
                    <input id="mantenimientos-table-search"
                           type="text"
                           placeholder="Buscar por cuenta, usuario, fecha..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="mantenimientos-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list"></i> Mostrar:
                    </label>
                    <select id="mantenimientos-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="mantenimientos-table"
                       data-table="mantenimientos-table"
                       class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="0">
                                ID Cuenta
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="1">
                                Usuario
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="2">
                                Contraseña
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="3">
                                Fecha de Mantenimiento
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="4">
                                Descripción
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            @if (Auth::user()->hasAnyPermission(['mantenimientos.update', 'mantenimientos.destroy']))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mantenimientos as $mantenimiento)
                            <tr data-id="{{ $mantenimiento->idman }}">
                                <td>{{ $mantenimiento->idcue }}</td>
                                <td>{{ $mantenimiento->cuenta->usuariocue }}</td>
                                <td>{{ $mantenimiento->cuenta->contrasenacue }}</td>
                                <td>{{ $mantenimiento->fechaman }}</td>
                                <td>{{ $mantenimiento->descripcionman }}</td>
                                @if (Auth::user()->hasAnyPermission(['mantenimientos.update', 'mantenimientos.destroy']))
                                    <td>
                                        <div class="action-buttons">
                                            @if (Auth::user()->hasPermissionTo('mantenimientos.update'))
                                                <button onclick="openEditModal({{ $mantenimiento->idman }})" class="btn btn-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                            @if(auth()->user()->can('mantenimientos.destroy'))
                                                <button onclick="openDeleteModal({{ $mantenimiento->idman }})" class="btn btn-danger btn-sm" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer: Info y paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="mantenimientos-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="mantenimientos-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales -->
@include('inventory.mantenimientos.modals.create')
@include('inventory.mantenimientos.modals.edit')
@include('inventory.mantenimientos.modals.delete')
@endsection

@section('scripts')
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

<script>
console.log('Vista de mantenimientos cargada con modales');

// Verificar Alpine.js cuando esté listo
document.addEventListener('alpine:init', () => {
    console.log('Alpine.js inicializado correctamente');
});

// Funciones para manejar modales
function openCreateModal() {
    console.log('🔷 Abriendo modal de crear...');
    const form = document.getElementById('create-form');
    if (form) {
        form.reset();
    }
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-mantenimiento' }));
    console.log('✅ Evento open-modal disparado para create-mantenimiento');
}

function closeCreateModal() {
    console.log('🔶 Cerrando modal de crear...');
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'create-mantenimiento' }));
}

function closeEditModal() {
    console.log('🔶 Cerrando modal de editar...');
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'edit-mantenimiento' }));
}

function openEditModal(id) {
    console.log('🔷 Abriendo modal de editar para ID:', id);
    fetch(`{{ route('mantenimientos') }}/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.mantenimiento) {
                const m = data.mantenimiento;
                document.getElementById('edit-idman').value = m.idman;
                document.getElementById('edit-cuenta-info').value = `${m.idcue} - ${m.cuenta.usuariocue}`;
                document.getElementById('edit-fechaman').value = m.fechaman;
                document.getElementById('edit-descripcionman').value = m.descripcionman;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-mantenimiento' }));
                console.log('✅ Modal de editar abierto con datos cargados');
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            showAlert('Error al cargar los datos del mantenimiento', 'danger');
        });
}

function submitCreate(event) {
    event.preventDefault();
    console.log('📤 Enviando formulario de crear...');
    const formData = new FormData(event.target);

    fetch('{{ route('mantenimientos.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idcue: formData.get('idcue'),
            fechaman: formData.get('fechaman'),
            descripcionman: formData.get('descripcionman')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Mantenimiento creado exitosamente');
            showAlert(data.message, 'success');
            closeCreateModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            console.log('❌ Error en la respuesta:', data.error);
            showAlert(data.error || 'Error al crear el mantenimiento', 'danger');
        }
    })
    .catch(error => {
        console.error('❌ Error en la petición:', error);
        showAlert('Error al procesar la solicitud', 'danger');
    });
}

function submitEdit(event) {
    event.preventDefault();
    console.log('📤 Enviando formulario de editar...');
    const formData = new FormData(event.target);
    const id = document.getElementById('edit-idman').value;

    fetch(`{{ route('mantenimientos') }}/${id}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            fechaman: formData.get('fechaman'),
            descripcionman: formData.get('descripcionman')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Mantenimiento actualizado exitosamente');
            showAlert(data.message, 'success');
            closeEditModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            console.log('❌ Error en la respuesta:', data.error);
            showAlert(data.error || 'Error al actualizar el mantenimiento', 'danger');
        }
    })
    .catch(error => {
        console.error('❌ Error en la petición:', error);
        showAlert('Error al procesar la solicitud', 'danger');
    });
}

function openDeleteModal(id) {
    console.log('🗑️ Abriendo modal de eliminación para ID:', id);

    // Obtener datos del mantenimiento para mostrar en el modal
    fetch(`{{ route('mantenimientos') }}/${id}/edit`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Datos del mantenimiento cargados');

            // Rellenar información en el modal
            document.getElementById('delete-idman').value = data.mantenimiento.idman;
            document.getElementById('delete-cuenta-info').textContent = data.cuenta || 'N/A';
            document.getElementById('delete-fecha-info').textContent = data.mantenimiento.fechaman || 'N/A';
            document.getElementById('delete-descripcion-info').textContent = data.mantenimiento.descripcionman || 'N/A';

            // Abrir modal
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-mantenimiento' }));
        } else {
            console.log('❌ Error al cargar datos:', data.error);
            showAlert('Error al cargar los datos del mantenimiento', 'danger');
        }
    })
    .catch(error => {
        console.error('❌ Error en la petición:', error);
        showAlert('Error al procesar la solicitud', 'danger');
    });
}

function closeDeleteModal() {
    console.log('🔷 Cerrando modal de eliminación');
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'delete-mantenimiento' }));
}

function confirmDelete() {
    const id = document.getElementById('delete-idman').value;
    console.log('📤 Confirmando eliminación del mantenimiento ID:', id);

    fetch(`{{ route('mantenimientos') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Mantenimiento eliminado exitosamente');
            showAlert(data.message, 'success');
            closeDeleteModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            console.log('❌ Error en la respuesta:', data.error);
            showAlert(data.error || 'Error al eliminar el mantenimiento', 'danger');
        }
    })
    .catch(error => {
        console.error('❌ Error en la petición de eliminación:', error);
        showAlert('Error al procesar la solicitud', 'danger');
    });
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alert = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    alertContainer.innerHTML = alert;

    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}
</script>
@endsection
