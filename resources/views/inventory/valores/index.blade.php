@extends('layouts.navigation')

@section('title', 'Valores')

@section('main')
<div class="container-fluid px-4">
    <!-- Título y breadcrumb -->
    <h1 class="mt-4">Valores de servicios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Valores</li>
    </ol>

    <!-- Descripción y alertas -->
    <!-- Alert Container para mensajes dinámicos -->
    <div id="alert-container"></div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <h3 class="text-primary">Gestión de Valores</h3>
        <p class="text-muted">Revisa el inventario y crea nuevos posibles contratos para luego agregarlas a stock.</p>
    </div>

    <!-- Formulario de pantallas por servicio -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-sliders-h"></i> Configuración de Pantallas por Servicio
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('valores.updatePantallas') }}" method="POST">
                @csrf
                <div class="row">
                    @foreach ($serviciosPrincipales as $servicio)
                        <div class="col-md-3 mb-3">
                            <div class="card border-{{ $servicio->color }} shadow-sm">
                                <div class="card-body text-center">
                                    @if (Str::startsWith($servicio->icon, 'fa-'))
                                        <i class="fab {{ $servicio->icon }} fa-2x text-gray-300"></i>
                                    @else
                                        <img src="{{ asset('images/' . $servicio->icon) }}" width="40" height="40"
                                            alt="{{ $servicio->nombreser }}">
                                    @endif

                                    <h6 class="card-title mt-2">{{ $servicio->nombreser }}</h6>

                                    <div class="row">
                                        <div class="col-6">
                                            <label for="pantmin_{{ $servicio->idser }}" class="form-label small">Pant Min</label>
                                            <input type="number" step="1" class="form-control form-control-sm"
                                                id="pantmin_{{ $servicio->idser }}"
                                                name="pantallas[{{ $servicio->idser }}][pantmin]" required min="1">
                                        </div>
                                        <div class="col-6">
                                            <label for="pantmax_{{ $servicio->idser }}" class="form-label small">Pant Max</label>
                                            <input type="number" step="1" class="form-control form-control-sm"
                                                id="pantmax_{{ $servicio->idser }}"
                                                name="pantallas[{{ $servicio->idser }}][pantmax]" required min="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Botones de acción -->
    @if (Auth::user()->hasPermissionTo('valores.create'))
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            {{-- Crear Valor --}}
            <button type="button" class="btn btn-success" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Crear Valor
            </button>

            {{-- Corregir idval --}}
            <form action="{{ route('valores.corregir') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-tools"></i> Corregir ID de Valores
                </button>
            </form>

            {{-- Borrar innecesarios --}}
            <form action="{{ route('valores.deletegroup') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Borrar Innecesarios
                </button>
            </form>

            {{-- Descargar PDF --}}
            <a href="{{ route('valores.pdf') }}" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF - {{ \Carbon\Carbon::now()->format('Y-m-d') }}
            </a>

            {{-- Nuevo Servicio --}}
            @if (Auth::user()->hasPermissionTo('servicios.create'))
                <button type="button" class="btn btn-info text-white" onclick="openCreateServicioModal()">
                    <i class="fas fa-plus-circle"></i> Nuevo Servicio
                </button>
            @endif

            {{-- Nuevo Proveedor --}}
            @if (Auth::user()->hasPermissionTo('proveedores.create'))
                <button type="button" class="btn btn-secondary" onclick="openCreateProveedorModal()">
                    <i class="fas fa-user-plus"></i> Nuevo Proveedor
                </button>
            @endif
        </div>
    @endif

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Lista de Valores
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="valores-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="valores-table-search"
                           type="text"
                           placeholder="Buscar por ID, servicio, proveedor, tipo..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="valores-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="valores-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="valores-table"
                       data-table="valores-table"
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
                                Servicio
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="2">
                                Proveedor
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="3">
                                Costo
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="4">
                                Tipo
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="5">
                                Min
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="6">
                                Max
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="7">
                                Meses
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th data-type="string">Bot de códigos</th>
                            <th class="sortable" data-type="number" data-col="9">
                                Num cuentas
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            @if (Auth::user()->hasAnyPermission(['valores.edit', 'valores.destroy']))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($valores as $valor)
                            <tr>
                                <td>{{ $valor->idval }}</td>
                                <td>{{ $valor->idser }}</td>
                                <td>{{ $valor->proveedor->nombrepro }}</td>
                                <td>${{ number_format($valor->costoval, 2) }}</td>
                                <td>{{ $valor->tipoval }}</td>
                                <td>{{ $valor->pantminval }}</td>
                                <td>{{ $valor->pantmaxval }}</td>
                                <td>{{ $valor->mesesval }}</td>
                                <td>
                                    @if (!empty($valor->bot))
                                        <a href="{{ $valor->bot }}" target="_blank" class="text-primary">Ver Bot</a>
                                    @else
                                        <span class="text-danger">No disponible</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-success">{{ $valor->num_cuentas }}</span></td>
                                @if (Auth::user()->hasAnyPermission(['valores.edit', 'valores.destroy']))
                                    <td>
                                        <div class="action-buttons">
                                            @if (Auth::user()->hasPermissionTo('valores.edit'))
                                                <button type="button"
                                                        class="btn btn-warning btn-sm"
                                                        onclick="openEditModal('{{ $valor->idval }}')"
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                            @if (Auth::user()->hasPermissionTo('valores.destroy'))
                                                <button type="button"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="openDeleteModal('{{ $valor->idval }}', '{{ $valor->servicio->nombreser }}', '{{ $valor->proveedor->nombrepro }}', {{ $valor->costoval }}, '{{ $valor->tipoval }}')"
                                                        title="Eliminar">
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
                    <div id="valores-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="valores-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('inventory.valores.modals.create')
    @include('inventory.valores.modals.edit')
    @include('inventory.valores.modals.delete')

    <!-- Modals de módulos relacionados -->
    @include('inventory.servicios.modals.create')
    @include('inventory.proveedores.modals.create')
</div>
@endsection

@section('scripts')
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

<script>
    console.log('Vista de valores cargada con Enhanced Table v2.0');
    console.log('Total de valores en la tabla:', {{ $valores->count() }});

    // ========================================
    // 🔷 MODAL: Crear Valor
    // ========================================
    function openCreateModal() {
        console.log('🔷 Abriendo modal de crear valor');
        document.getElementById('createValorForm').reset();
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createValorModal' }));
    }

    async function submitCreate(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario de creación');

        const form = event.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('{{ route("valores.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Valor creado exitosamente');
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createValorModal' }));
                showAlert('Valor creado con éxito', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('❌ Error al crear valor:', data);
                showAlert(data.message || 'Error al crear el valor', 'danger');
            }
        } catch (error) {
            console.error('❌ Error en la petición:', error);
            showAlert('Error al procesar la solicitud', 'danger');
        }
    }

    // ========================================
    // ✏️ MODAL: Editar Valor
    // ========================================
    async function openEditModal(idval) {
        console.log('🔷 Abriendo modal de edición para ID:', idval);

        const url = '{{ route("valores.edit", "__ID__") }}'.replace('__ID__', idval);

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Error al cargar datos');

            const data = await response.json();

            if (data.success) {
                console.log('✅ Datos del valor cargados:', data.valor);

                // Llenar formulario
                document.getElementById('edit_idval').value = data.valor.idval;
                document.getElementById('edit_idval_display').value = data.valor.idval;
                document.getElementById('edit_idser').value = data.valor.idser;
                document.getElementById('edit_idpro').value = data.valor.idpro;
                document.getElementById('edit_costoval').value = data.valor.costoval;
                document.getElementById('edit_tipoval').value = data.valor.tipoval;
                document.getElementById('edit_pantminval').value = data.valor.pantminval;
                document.getElementById('edit_pantmaxval').value = data.valor.pantmaxval;
                document.getElementById('edit_mesesval').value = data.valor.mesesval;
                document.getElementById('edit_bot').value = data.valor.bot || '';

                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editValorModal' }));
            } else {
                showAlert('Error al cargar los datos del valor', 'danger');
            }
        } catch (error) {
            console.error('❌ Error al cargar datos:', error);
            showAlert('Error al cargar los datos del valor', 'danger');
        }
    }

    async function submitEdit(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario de edición');

        const form = event.target;
        const formData = new FormData(form);
        const idval = document.getElementById('edit_idval').value;

        const url = '{{ route("valores.update", "__ID__") }}'.replace('__ID__', idval);

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
                console.log('✅ Valor actualizado exitosamente');
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editValorModal' }));
                showAlert('Valor actualizado con éxito', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('❌ Error al actualizar valor:', data);
                showAlert(data.message || 'Error al actualizar el valor', 'danger');
            }
        } catch (error) {
            console.error('❌ Error en la petición:', error);
            showAlert('Error al procesar la solicitud', 'danger');
        }
    }

    // ========================================
    // 🗑️ MODAL: Eliminar Valor
    // ========================================
    function openDeleteModal(idval, servicio, proveedor, costo, tipo) {
        console.log('🗑️ Abriendo modal de eliminación para:', idval);

        document.getElementById('delete_idval').value = idval;
        document.getElementById('delete_idval_display').textContent = idval;
        document.getElementById('delete_servicio_display').textContent = servicio;
        document.getElementById('delete_proveedor_display').textContent = proveedor;
        document.getElementById('delete_costo_display').textContent = parseFloat(costo).toFixed(2);
        document.getElementById('delete_tipo_display').textContent = tipo;

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'deleteValorModal' }));
    }

    async function confirmDelete(event) {
        event.preventDefault();
        console.log('📤 Confirmando eliminación');

        const idval = document.getElementById('delete_idval').value;
        const url = '{{ route("valores.destroy", "__ID__") }}'.replace('__ID__', idval);

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
                console.log('✅ Valor eliminado exitosamente');
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteValorModal' }));
                showAlert(data.message || 'Valor desactivado con éxito', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('❌ Error al eliminar:', data.message);
                showAlert(data.message || 'Error al eliminar el valor', 'danger');
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
    // 🔗 MODALES DE MÓDULOS RELACIONADOS
    // ========================================

    // Abrir modal de crear servicio
    function openCreateServicioModal() {
        console.log('🔷 Abriendo modal de crear servicio desde valores');
        // Resetear formulario si existe
        const form = document.getElementById('createServicioForm');
        if (form) form.reset();
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createServicioModal' }));
    }

    // Abrir modal de crear proveedor
    function openCreateProveedorModal() {
        console.log('🔷 Abriendo modal de crear proveedor desde valores');
        // Resetear formulario si existe
        const form = document.getElementById('createProveedorForm');
        if (form) form.reset();
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createProveedorModal' }));
    }

    // Funciones de submit para servicios (redirige a valores después de crear)
    async function submitCreateServicio(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario de creación de servicio');

        const form = event.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('{{ route("servicios.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Servicio creado exitosamente');
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createServicioModal' }));
                showAlert('Servicio creado con éxito', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('❌ Error al crear servicio:', data);
                showAlert(data.message || 'Error al crear el servicio', 'danger');
            }
        } catch (error) {
            console.error('❌ Error en la petición:', error);
            showAlert('Error al procesar la solicitud', 'danger');
        }
    }

    // Funciones de submit para proveedores (redirige a valores después de crear)
    async function submitCreateProveedor(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario de creación de proveedor');

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
</script>
@endsection
