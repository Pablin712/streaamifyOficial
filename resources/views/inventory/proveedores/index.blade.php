@extends('layouts.table')
@section('title')
    Proveedores
@endsection
@section('h1', 'Proveedores')
@section('breadcrumb')
    Proveedores
@endsection
@section('descripcion')
    <!-- Alert Container para mensajes dinámicos -->
    <div id="alert-container"></div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Agrega un nuevo proveedor al negocio, para poder contactarlo y adquirir sus cuentas.</p>
@endsection
@section('tablename', 'Proveedores')
@section('table1')
    <h1>Proveedores</h1>
    @if (Auth::user()->hasPermissionTo('proveedores.create'))
        <button type="button" class="btn btn-primary mb-3" onclick="openCreateModal()">
            <i class="fas fa-plus me-1"></i>Crear Proveedor
        </button>
    @endif
    <!-- Filtros con Checkboxes -->
    <div class="mb-3">
        <label><input type="checkbox" class="column-toggle" data-column="3" checked> Total de cuentas</label>
        <label><input type="checkbox" class="column-toggle" data-column="4"> Netflix</label>
        <label><input type="checkbox" class="column-toggle" data-column="5"> Disney Premium</label>
        <label><input type="checkbox" class="column-toggle" data-column="6"> Disney</label>
        <label><input type="checkbox" class="column-toggle" data-column="7"> MAX</label>
        <label><input type="checkbox" class="column-toggle" data-column="8"> Prime</label>
        <label><input type="checkbox" class="column-toggle" data-column="9"> Spotify</label>
        <label><input type="checkbox" class="column-toggle" data-column="10"> Otros</label>
        <label><input type="checkbox" class="column-toggle" data-column="11"> Se debe</label>
        <label><input type="checkbox" class="column-toggle" data-column="12"> Pagar en el mes</label>
    </div>

    <!-- Controles de búsqueda y registros -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="proveedores-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="proveedores-table-search"
                   type="text"
                   placeholder="Buscar proveedor..."
                   class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="proveedores-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="proveedores-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="20">20 registros</option>
                <option value="50">50 registros</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="proveedores-table" data-table="proveedores-table" class="table table-striped table-bordered">
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
                <th class="sortable" data-type="number" data-col="3">
                    Total de cuentas
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="4">
                    Netflix
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="5">
                    Disney Premium
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="6">
                    Disney
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="7">
                    MAX
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="8">
                    Prime
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="9">
                    Spotify
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="10">
                    Otros
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="11">
                    Se debe
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="12">
                    Pagar en el mes
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                @if (Auth::user()->hasAnyPermission(['proveedores.edit', 'proveedores.destroy']))
                    <th data-type="actions">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($proveedores as $proveedor)
                <tr>
                    <td>{{ $proveedor->idpro }}</td>
                    <td>{{ $proveedor->nombrepro }}</td>
                    <td>{{ $proveedor->telefonopro }}</td>
                    <td>{{ $proveedor->total_cuentas }}</td>
                    <td>{{ $proveedor->cuentas_netflix }}</td>
                    <td>{{ $proveedor->cuentas_disney_p }}</td>
                    <td>{{ $proveedor->cuentas_disney_s }}</td>
                    <td>{{ $proveedor->cuentas_max }}</td>
                    <td>{{ $proveedor->cuentas_prime_v }}</td>
                    <td>{{ $proveedor->cuentas_spotify }}</td>
                    <td>{{ $proveedor->otras_cuentas }}</td>
                    <td>
                        @if ($proveedor->se_debe > 0)
                            <span class="badge bg-danger">
                                ${{ $proveedor->se_debe }}
                            </span>
                        @else
                            <span class="badge bg-success">$0</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-warning">
                            ${{ $proveedor->se_debe_mes }}
                        </span>
                    </td>
                    @if (Auth::user()->hasAnyPermission(['proveedores.edit', 'proveedores.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('proveedores.edit'))
                                <button type="button"
                                        class="btn btn-warning"
                                        onclick="openEditModal({{ $proveedor->idpro }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('proveedores.destroy'))
                                <button type="button"
                                        class="btn btn-danger btn-circle"
                                        onclick="openDeleteModal({{ $proveedor->idpro }}, '{{ $proveedor->nombrepro }}', '{{ $proveedor->telefonopro }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <!-- Información de paginación y controles -->
    <div class="row mt-3 align-items-center">
        <div class="col-md-6 col-12 mb-2 mb-md-0">
            <div id="proveedores-table-row-info" class="text-muted"></div>
        </div>
        <div class="col-md-6 col-12">
            <div id="proveedores-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
        </div>
    </div>

    <!-- Modals -->
    @include('inventory.proveedores.modals.create')
    @include('inventory.proveedores.modals.edit')
    @include('inventory.proveedores.modals.delete')
@endsection
@section('scripts')
    <script>
        // ========================================
        // 🔷 MODAL: Crear Proveedor
        // ========================================
        function openCreateModal() {
            console.log('🔷 Abriendo modal de crear proveedor');
            document.getElementById('createProveedorForm').reset();
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createProveedorModal' }));
        }

        async function submitCreate(event) {
            event.preventDefault();
            console.log('📤 Enviando formulario de creación');

            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch('{{ route("proveedores.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    console.log('✅ Proveedor creado exitosamente');
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createProveedorModal' }));
                    showAlert('Proveedor creado con éxito', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    console.error('❌ Error al crear proveedor:', data);
                    showAlert(data.message || 'Error al crear el proveedor', 'danger');
                }
            } catch (error) {
                console.error('❌ Error en la petición:', error);
                showAlert('Error al procesar la solicitud', 'danger');
            }
        }

        // ========================================
        // ✏️ MODAL: Editar Proveedor
        // ========================================
        async function openEditModal(idpro) {
            console.log('🔷 Abriendo modal de edición para ID:', idpro);

            const url = '{{ route("proveedores.edit", "__ID__") }}'.replace('__ID__', idpro);

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Error al cargar datos');

                const data = await response.json();

                if (data.success) {
                    console.log('✅ Datos del proveedor cargados:', data.proveedor);

                    // Llenar formulario
                    document.getElementById('edit_idpro').value = data.proveedor.idpro;
                    document.getElementById('edit_idpro_display').value = data.proveedor.idpro;
                    document.getElementById('edit_nombrepro').value = data.proveedor.nombrepro;
                    document.getElementById('edit_telefonopro').value = data.proveedor.telefonopro || '';

                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editProveedorModal' }));
                } else {
                    showAlert('Error al cargar los datos del proveedor', 'danger');
                }
            } catch (error) {
                console.error('❌ Error al cargar datos:', error);
                showAlert('Error al cargar los datos del proveedor', 'danger');
            }
        }

        async function submitEdit(event) {
            event.preventDefault();
            console.log('📤 Enviando formulario de edición');

            const form = event.target;
            const formData = new FormData(form);
            const idpro = document.getElementById('edit_idpro').value;

            const url = '{{ route("proveedores.update", "__ID__") }}'.replace('__ID__', idpro);

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
                    console.log('✅ Proveedor actualizado exitosamente');
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editProveedorModal' }));
                    showAlert('Proveedor actualizado con éxito', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    console.error('❌ Error al actualizar proveedor:', data);
                    showAlert(data.message || 'Error al actualizar el proveedor', 'danger');
                }
            } catch (error) {
                console.error('❌ Error en la petición:', error);
                showAlert('Error al procesar la solicitud', 'danger');
            }
        }

        // ========================================
        // 🗑️ MODAL: Eliminar Proveedor
        // ========================================
        function openDeleteModal(idpro, nombrepro, telefonopro) {
            console.log('🗑️ Abriendo modal de eliminación para:', nombrepro);

            document.getElementById('delete_idpro').value = idpro;
            document.getElementById('delete_idpro_display').textContent = idpro;
            document.getElementById('delete_nombrepro_display').textContent = nombrepro;
            document.getElementById('delete_telefonopro_display').textContent = telefonopro || 'N/A';

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'deleteProveedorModal' }));
        }

        async function confirmDelete(event) {
            event.preventDefault();
            console.log('📤 Confirmando eliminación');

            const idpro = document.getElementById('delete_idpro').value;
            const url = '{{ route("proveedores.destroy", "__ID__") }}'.replace('__ID__', idpro);

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    console.log('✅ Proveedor eliminado exitosamente');
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteProveedorModal' }));
                    showAlert(data.message || 'Proveedor desactivado con éxito', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    console.error('❌ Error al eliminar:', data.message);
                    showAlert(data.message || 'Error al eliminar el proveedor', 'danger');
                }
            } catch (error) {
                console.error('❌ Error en la petición:', error);
                showAlert('Error al procesar la solicitud', 'danger');
            }
        }

        // ========================================
        // 📢 SISTEMA DE ALERTAS
        // ========================================
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.role = 'alert';
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);

            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 5000);
        }

        // ========================================
        // 🔍 CHECKBOXES: Toggle de columnas
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('#datatablesSimpl');
            const checkboxes = document.querySelectorAll('.column-toggle');

            checkboxes.forEach(checkbox => {
                const column = checkbox.dataset.column;
                const isChecked = checkbox.checked;

                if (column !== "3") {
                    checkbox.checked = false;
                }

                toggleColumn(table, column, checkbox.checked);

                checkbox.addEventListener('change', function() {
                    toggleColumn(table, column, this.checked);
                });
            });

            function toggleColumn(table, columnIndex, show) {
                const rows = table.querySelectorAll('tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('th, td');
                    if (cells[columnIndex]) {
                        cells[columnIndex].style.display = show ? '' : 'none';
                    }
                });
            }
        });
    </script>

    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
@endsection
