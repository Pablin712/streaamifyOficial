@extends('layouts.table')

@section('title', 'Empleados')
@section('h1', 'Empleados')
@section('breadcrumb', 'Empleados')

@section('descripcion')
    <!-- Alert Container para mensajes dinámicos -->
    <div id="alert-container"></div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <h3>Información de Empleados</h3>
@endsection

@section('table1')
    @if (Auth::user()->hasPermissionTo('empleados.store'))
        <button type="button" onclick="openCreateModal()" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Crear Empleado
        </button>
    @endif
    <div class="row">
        @foreach ($datos as $dato)
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="{{ $dato['empleado']->foto_url ? asset('storage/' . $dato['empleado']->foto_url) : 'https://via.placeholder.com/100/007bff/ffffff?text=Usuario' }}"
                            alt="Foto de {{ $dato['empleado']->nombreemp }}" class="img-fluid rounded-circle mb-3"
                            style="width: 100px; height: 100px; object-fit: cover;">
                        <h5 class="card-title">{{ $dato['empleado']->nombreemp }}</h5>
                        <p class="card-text">
                            <strong>Teléfono:</strong> {{ $dato['empleado']->telefonoemp }}<br>
                            <strong>Usuario:</strong> {{ $dato['empleado']->usuarioemp }}<br>
                            <strong>Correo:</strong> {{ $dato['empleado']->email }}<br>
                            <strong>Ventas hoy:</strong> {{ $dato['gestionVentasHoy'] }}<br>
                            <strong>Total de Ventas:</strong> {{ $dato['empleado']->ventas_count }}<br>
                            <strong>Total de Conexión:</strong> {{ $dato['total'] }} minutos<br>
                            <strong>Gestión de Clientes:</strong> {{ $dato['gestionClientesHoy'] }}<br>
                            <strong>Gestión de Cuentas:</strong> {{ $dato['gestionCuentasHoy'] }}<br>
                            <strong>Gestión de Inventario:</strong> {{ $dato['gestionInventarioHoy'] }}<br>
                            <strong>Tareas completadas:</strong> {{ $dato['empleado']->num_tareas_completadas }}<br>
                            <strong>Gestión de Recargas:</strong> {{ $dato['gestionRecargasHoy'] }}<br>
                            <strong>Gestión de Productos:</strong> {{ $dato['gestionProductosHoy'] }}<br>
                            <strong>Gestión de Costos:</strong> {{ $dato['gestionCostosHoy'] }}<br>
                            <strong>Rol:</strong>
                            @if ($dato['empleado']->roles->isNotEmpty())
                                {{ implode(', ', $dato['empleado']->roles->pluck('name')->toArray()) }}
                            @else
                                Sin rol asignado
                            @endif
                        </p>
                        <div class="d-flex gap-2 justify-content-center">
                            @if (Auth::user()->hasPermissionTo('empleados.update'))
                                <button onclick="openEditModal('{{ $dato['empleado']->idemp }}')" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('empleados.updateRol'))
                                <a href="{{ route('empleados.editRoles', $dato['empleado']->idemp) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-user-tag"></i> Roles
                                </a>
                            @endif
                            @if (Auth::user()->hasPermissionTo('empleados.destroy'))
                                <button onclick="openDeleteModal('{{ $dato['empleado']->idemp }}')" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modales -->
    @include('employee.modals.create')
    @include('employee.modals.edit')
    @include('employee.modals.delete')
@endsection

@section('scripts')
<script>
    // ============================================================================
    // FUNCIONES DE MODAL - CREAR
    // ============================================================================
    function openCreateModal() {
        console.log('🔷 Abriendo modal de crear empleado...');
        const form = document.getElementById('createEmpleadoForm');
        if (form) form.reset();
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createEmpleadoModal' }));
    }

    function closeCreateModal() {
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createEmpleadoModal' }));
    }

    async function submitCreate(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario de crear empleado...');

        const formData = new FormData(event.target);

        try {
            const response = await fetch('{{ route("empleados.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Empleado creado:', data);
                showAlert(data.message, 'success');
                closeCreateModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('❌ Error al crear:', data);
                showAlert(data.message || 'Error al crear el empleado', 'danger');
            }
        } catch (error) {
            console.error('❌ Error de red:', error);
            showAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
        }
    }

    // ============================================================================
    // FUNCIONES DE MODAL - EDITAR
    // ============================================================================
    function openEditModal(idemp) {
        console.log('🔷 Abriendo modal de editar empleado:', idemp);

        const url = '{{ route("empleados.edit", "__ID__") }}'.replace('__ID__', idemp);

        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_empleado_id').value = data.empleado.idemp;
                document.getElementById('edit_nombreemp').value = data.empleado.nombreemp;
                document.getElementById('edit_telefonoemp').value = data.empleado.telefonoemp;
                document.getElementById('edit_usuarioemp').value = data.empleado.usuarioemp;
                document.getElementById('edit_email').value = data.empleado.email || '';

                // Mostrar foto actual si existe
                const fotoPreview = document.getElementById('edit_foto_preview');
                if (data.empleado.foto_url) {
                    fotoPreview.innerHTML = `<img src="{{ asset('storage') }}/${data.empleado.foto_url}" class="img-fluid rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">`;
                } else {
                    fotoPreview.innerHTML = '<p class="text-muted">Sin foto</p>';
                }

                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editEmpleadoModal' }));
            }
        })
        .catch(error => {
            console.error('Error al cargar empleado:', error);
            showAlert('Error al cargar los datos del empleado', 'danger');
        });
    }

    function closeEditModal() {
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editEmpleadoModal' }));
    }

    async function submitEdit(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario de editar empleado...');

        const idemp = document.getElementById('edit_empleado_id').value;
        const formData = new FormData(event.target);

        const url = '{{ route("empleados.update", "__ID__") }}'.replace('__ID__', idemp);

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Empleado actualizado:', data);
                showAlert(data.message, 'success');
                closeEditModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('❌ Error al actualizar:', data);
                showAlert(data.message || 'Error al actualizar el empleado', 'danger');
            }
        } catch (error) {
            console.error('❌ Error de red:', error);
            showAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
        }
    }

    // ============================================================================
    // FUNCIONES DE MODAL - ELIMINAR
    // ============================================================================
    function openDeleteModal(idemp) {
        console.log('🔷 Abriendo modal de eliminar empleado:', idemp);

        const url = '{{ route("empleados.edit", "__ID__") }}'.replace('__ID__', idemp);

        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('delete_empleado_id').value = data.empleado.idemp;
                document.getElementById('delete_empleado_nombre').textContent = data.empleado.nombreemp;
                document.getElementById('delete_empleado_telefono').textContent = data.empleado.telefonoemp;
                document.getElementById('delete_empleado_usuario').textContent = data.empleado.usuarioemp;
                document.getElementById('delete_empleado_email').textContent = data.empleado.email || 'N/A';

                // Mostrar foto
                const fotoDiv = document.getElementById('delete_empleado_foto');
                if (data.empleado.foto_url) {
                    fotoDiv.innerHTML = `<img src="{{ asset('storage') }}/${data.empleado.foto_url}" class="img-fluid rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">`;
                } else {
                    fotoDiv.innerHTML = '<i class="fas fa-user-circle fa-4x text-muted"></i>';
                }

                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'deleteEmpleadoModal' }));
            }
        })
        .catch(error => {
            console.error('Error al cargar empleado:', error);
            showAlert('Error al cargar los datos del empleado', 'danger');
        });
    }

    function closeDeleteModal() {
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteEmpleadoModal' }));
    }

    async function submitDelete(event) {
        event.preventDefault();
        console.log('🗑️ Eliminando empleado...');

        const idemp = document.getElementById('delete_empleado_id').value;
        const url = '{{ route("empleados.destroy", "__ID__") }}'.replace('__ID__', idemp);

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
                console.log('✅ Empleado eliminado');
                showAlert(data.message, 'success');
                closeDeleteModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('❌ Error al eliminar:', data);
                showAlert(data.message || 'Error al eliminar el empleado', 'danger');
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
