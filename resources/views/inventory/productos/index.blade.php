@extends('layouts.table')

@section('title', 'Productos')
@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Select2 Dark Mode -->
    <link href="{{ asset('css/select2-dark-mode.css') }}" rel="stylesheet" />

    <style>
        /* Personalizando el fondo oscuro de las filas de la tabla */
        .table-dark {
            background-color: #4CAF50 !important;
            /* Verde personalizado */
            color: white !important;
        }

        /* Personalizando el badge bg-dark */
        .badge.bg-dark {
            background-color: #4CAF50 !important;
            color: white !important;
        }

        .badge.bg-dark:hover {
            background-color: #388E3C !important;
        }
    </style>
@endsection
@section('h1', 'Productos')
@section('breadcrumb')
    Productos
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
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
    <h3>Gestiona los productos disponibles en tu inventario.</h3>
    <p>Aquí puedes gestionar tus productos, ver detalles asociados y mantener el catálogo actualizado.</p>

    <form action="{{ route('productos.updatePrecios') }}" method="POST">
        @csrf
        <div class="row">
            @foreach ($serviciosConfig as $key => $servicioInfo)
                <div class="col-md-3 mb-3">
                    <div class="card border-{{ $servicioInfo['color'] }} shadow-sm">
                        <div class="card-body text-center">
                            @if (Str::startsWith($servicioInfo['icon'], 'fa-'))
                                <i class="fab {{ $servicioInfo['icon'] }} fa-2x text-gray-300"></i>
                            @else
                                <img src="{{ asset('images/' . $servicioInfo['icon']) }}" width="40" height="40"
                                    alt="{{ $servicioInfo['nombre'] }}">
                            @endif
                            <h6 class="card-title mt-2">{{ $servicioInfo['nombre'] }}</h6>
                            <div class="row">
                                <div class="col-6">
                                    <label for="precio_{{ $key }}" class="form-label small">Precio ($)</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm"
                                        id="precio_{{ $key }}" name="precios[{{ $key }}][precio]"
                                        value="{{ old('precios.' . $key . '.precioser', $servicioInfo['precioser'] ?? '') }}"
                                        required>
                                </div>
                                <div class="col-6">
                                    <label for="combo_{{ $key }}" class="form-label small">Combo ($)</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm"
                                        id="combo_{{ $key }}" name="precios[{{ $key }}][combo]"
                                        value="{{ old('precios.' . $key . '.comboser', $servicioInfo['comboser'] ?? '') }}"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mb-3">
            <button type="submit" class="btn btn-primary btn-sm">Guardar Precios</button>
        </div>
    </form>
@endsection

@section('btncrear')
    <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
        @if (Auth::user()->hasPermissionTo('productos.create'))
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Crear Producto
            </button>
        @endif

        <a href="{{ route('productos.pdf') }}" class="btn btn-success">
            <i class="fas fa-file-pdf"></i> Descargar Catálogo PDF
        </a>

        <a href="{{ route('productos.exportarSRI') }}" class="btn btn-warning">
            <i class="fas fa-file-excel"></i> Exportar para SRI
        </a>

        @if (Auth::user()->hasPermissionTo('productos.edit'))
            <button type="button" class="btn btn-info" id="btnCurarCodigos">
                <i class="fas fa-magic"></i> Curar Códigos SRI
            </button>
        @endif

        <button type="button" class="btn btn-secondary" id="copiarMensaje">
            <i class="fas fa-copy"></i> Copiar Mensaje PG
        </button>

        <button type="button" class="btn btn-secondary" id="copiarMensajeProductos">
            <i class="fas fa-copy"></i> Copiar Mensaje PE
        </button>

        <button type="button" class="btn btn-secondary" id="copiarMensajeCombos">
            <i class="fas fa-copy"></i> Copiar Mensaje de Combos
        </button>
    </div>
    <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
        <select id="servicioSelect" class="form-select form-select-sm" style="width: 200px;">
            <option value="" selected disabled>Selecciona un servicio</option>
            @foreach ($servicios as $servicio)
                <option value="{{ $servicio->idser }}">{{ $servicio->nombreser }}</option>
            @endforeach
        </select>

        <button type="button" class="btn btn-secondary" id="copiarPlanesServicio">
            <i class="fas fa-copy"></i> Copiar Planes del Servicio
        </button>
    </div>
@endsection

@section('tablename', 'Productos')

@section('table1')
    <!-- Controles de búsqueda y registros -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="productos-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="productos-table-search"
                   type="text"
                   placeholder="Buscar producto..."
                   class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="productos-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="productos-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="20">20 registros</option>
                <option value="50">50 registros</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="productos-table" data-table="productos-table" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th class="sortable" data-type="number" data-col="0">
                    ID Producto
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="1">
                    Código
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="2">
                    Nombre
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="3">
                    Precio
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="4">
                    Categoría
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="5">
                    Tipo
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="6">
                    Activo
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                @if (Auth::user()->hasAnyPermission(['productos.edit', 'productos.show', 'productos.destroy']))
                    <th data-type="actions">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $producto)
                <tr>
                    <td>{{ $producto->id }}</td>
                    <td>{{ $producto->codigopro }}</td>
                    <td>{{ $producto->nombrepro }}</td>
                    <td>${{ number_format($producto->preciopro, 2) }}</td>
                    <td>{{ $producto->categoria->nombre }}</td>
                    <td>{{ $producto->tipoProducto->nombre }}</td>
                    <td>
                        @if ($producto->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Inactivo</span>
                        @endif
                    </td>
                    @if (Auth::user()->hasAnyPermission(['productos.edit', 'productos.show', 'productos.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('productos.show'))
                                <button type="button" class="btn btn-info btn-sm" onclick="openShowModal({{ $producto->id }})">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('productos.edit'))
                                <button type="button" class="btn btn-warning btn-sm" onclick="openEditModal({{ $producto->id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('productos.destroy'))
                                <button type="button" class="btn btn-danger btn-sm" onclick="openDeleteModal({{ $producto->id }})">
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
            <div id="productos-table-row-info" class="text-muted"></div>
        </div>
        <div class="col-md-6 col-12">
            <div id="productos-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
        </div>
    </div>

    </div>

    {{-- Modales de Productos --}}
    @include('inventory.productos.modals.create')
    @include('inventory.productos.modals.edit')
    @include('inventory.productos.modals.delete')
    @include('inventory.productos.modals.show')
    @include('inventory.productos.modals.detalles-modals')
@endsection

@section('scripts')
    <!-- jQuery (debe cargarse primero) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 (debe cargarse después de jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Inicializador de searchable-selects -->
    <script src="{{ asset('js/searchable-select.js') }}"></script>

    <!-- Scripts específicos de productos -->
    <script src="{{ asset('js/productos.js') }}"></script>

    {{-- ============================================================================ --}}
    {{-- FUNCIONES DE MODAL - SHOW --}}
    {{-- ============================================================================ --}}
    <script>
        function openShowModal(id) {
            console.log('🔷 Abriendo modal de ver producto:', id);

            fetch(`{{ route('productos.show', '') }}/${id}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(producto => {
                document.getElementById('show_producto_nombre').textContent = producto.nombrepro;
                document.getElementById('show_producto_codigo').textContent = producto.codigopro;
                document.getElementById('show_producto_precio').textContent = '$' + parseFloat(producto.preciopro).toFixed(2);

                // Estrellas
                let estrellas = '';
                if (producto.estrellaspro) {
                    for (let i = 0; i < producto.estrellaspro; i++) {
                        estrellas += '⭐';
                    }
                } else {
                    estrellas = 'Sin calificación';
                }
                document.getElementById('show_producto_estrellas').textContent = estrellas;

                document.getElementById('show_producto_descripcion').textContent = producto.descripcionpro || 'Sin descripción';
                document.getElementById('show_producto_categoria').textContent = producto.categoria?.nombre || 'N/A';
                document.getElementById('show_producto_tipo').textContent = producto.tipo_producto?.nombre || 'N/A';

                // Estado
                const estadoBadge = producto.activo
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-danger">Inactivo</span>';
                document.getElementById('show_producto_estado').innerHTML = estadoBadge;

                // Foto
                const fotoDiv = document.getElementById('show_producto_foto');
                if (producto.foto) {
                    fotoDiv.innerHTML = `<img src="/public/${producto.foto}" class="img-fluid rounded shadow" alt="Foto del producto">`;
                } else {
                    fotoDiv.innerHTML = '<p class="text-muted">Sin imagen disponible</p>';
                }

                // Detalles
                const tbody = document.getElementById('show_producto_detalles');
                tbody.innerHTML = '';
                if (producto.detalles && producto.detalles.length > 0) {
                    producto.detalles.forEach(detalle => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${detalle.idser}</td>
                                <td>${detalle.descripcion}</td>
                                <td>${detalle.meses}</td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-muted">Sin detalles</td></tr>';
                }

                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'showProductoModal' }));
            })
            .catch(error => {
                console.error('Error al cargar producto:', error);
                alert('Error al cargar los datos del producto');
            });
        }
    </script>

    {{-- ============================================================================ --}}
    {{-- FUNCIONES DE MODAL - CREAR --}}
    {{-- ============================================================================ --}}
    <script>
        // Array para almacenar detalles temporalmente
        let detallesCreate = [];
        let detallesEdit = [];

        function openCreateModal() {
            console.log('🔷 Abriendo modal de crear producto...');
            const form = document.getElementById('createProductoForm');
            if (form) form.reset();
            detallesCreate = [];
            document.getElementById('create_tabla_detalles').innerHTML = '';
            document.getElementById('create_detalles_producto').value = '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createProductoModal' }));
        }

        function closeCreateModal() {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createProductoModal' }));
        }

        async function submitCreate(event) {
            event.preventDefault();
            console.log('📤 Enviando formulario de crear producto...');

            const form = document.getElementById('createProductoForm');
            const formData = new FormData(form);

            // Agregar detalles al FormData
            formData.set('detalles_producto', JSON.stringify(detallesCreate));

            try {
                const response = await fetch('{{ route("productos.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    console.log('✅ Producto creado:', data);
                    closeCreateModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    console.error('❌ Error al crear:', data);
                    alert(data.message || 'Error al crear el producto');
                }
            } catch (error) {
                console.error('❌ Error de red:', error);
                alert('Error de conexión. Por favor, intenta nuevamente.');
            }
        }

        // Funciones para manejo de detalles en modal Create
        function openAgregarDetalleModal() {
            document.getElementById('formAgregarDetalle').reset();
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'agregarDetalleModal' }));
        }

        function closeAgregarDetalleModal() {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'agregarDetalleModal' }));
        }

        function guardarDetalle() {
            const idser = document.getElementById('detalle_idser').value;
            const descripcion = document.getElementById('detalle_descripcion').value;
            const meses = document.getElementById('detalle_meses').value;

            if (!idser || !descripcion || !meses) {
                alert('Por favor complete todos los campos.');
                return;
            }

            detallesCreate.push({ idser, descripcion, meses });
            renderDetallesCreate();
            closeAgregarDetalleModal();
        }

        function renderDetallesCreate() {
            const tbody = document.getElementById('create_tabla_detalles');
            tbody.innerHTML = '';

            if (detallesCreate.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-muted text-center">No hay detalles agregados</td></tr>';
                return;
            }

            detallesCreate.forEach((detalle, index) => {
                const row = `
                    <tr>
                        <td>${detalle.idser}</td>
                        <td>${detalle.descripcion}</td>
                        <td>${detalle.meses}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarDetalleCreate(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            document.getElementById('create_detalles_producto').value = JSON.stringify(detallesCreate);
        }

        function eliminarDetalleCreate(index) {
            detallesCreate.splice(index, 1);
            renderDetallesCreate();
        }
    </script>

    {{-- ============================================================================ --}}
    {{-- FUNCIONES DE MODAL - EDITAR --}}
    {{-- ============================================================================ --}}
    <script>
        function openEditModal(id) {
            console.log('🔷 Abriendo modal de editar producto:', id);

            fetch(`{{ route('productos.show', '') }}/${id}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(producto => {
                document.getElementById('edit_producto_id').value = producto.id;
                document.getElementById('edit_codigopro').value = producto.codigopro;
                document.getElementById('edit_nombrepro').value = producto.nombrepro;
                document.getElementById('edit_preciopro').value = producto.preciopro;
                document.getElementById('edit_estrellaspro').value = producto.estrellaspro || '';
                document.getElementById('edit_descripcionpro').value = producto.descripcionpro || '';
                document.getElementById('edit_tipo_producto_id').value = producto.tipo_producto_id;
                document.getElementById('edit_categoria_id').value = producto.categoria_id;
                document.getElementById('edit_activo').value = producto.activo ? '1' : '0';

                // Mostrar foto actual si existe
                const fotoPreview = document.getElementById('edit_foto_preview');
                if (producto.foto) {
                    fotoPreview.innerHTML = `<img src="/public/${producto.foto}" class="img-thumbnail mt-2" width="150">`;
                } else {
                    fotoPreview.innerHTML = '';
                }

                // Cargar detalles
                detallesEdit = producto.detalles || [];
                renderDetallesEdit();

                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editProductoModal' }));
            })
            .catch(error => {
                console.error('Error al cargar producto:', error);
                alert('Error al cargar los datos del producto');
            });
        }

        function closeEditModal() {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editProductoModal' }));
        }

        async function submitEdit(event) {
            event.preventDefault();
            console.log('📤 Enviando formulario de editar producto...');

            const form = document.getElementById('editProductoForm');
            const formData = new FormData(form);
            const id = document.getElementById('edit_producto_id').value;

            formData.set('detalles_producto', JSON.stringify(detallesEdit));

            try {
                const response = await fetch(`{{ route('productos.update', '') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    console.log('✅ Producto actualizado:', data);
                    closeEditModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    console.error('❌ Error al actualizar:', data);
                    alert(data.message || 'Error al actualizar el producto');
                }
            } catch (error) {
                console.error('❌ Error de red:', error);
                alert('Error de conexión. Por favor, intenta nuevamente.');
            }
        }

        function openAgregarDetalleModalEdit() {
            const form = document.getElementById('formAgregarDetalle');
            if (form) form.reset();
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'agregarDetalleModal' }));
        }

        function renderDetallesEdit() {
            const tbody = document.getElementById('edit_tabla_detalles');
            tbody.innerHTML = '';

            if (detallesEdit.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-muted text-center">No hay detalles agregados</td></tr>';
                return;
            }

            detallesEdit.forEach((detalle, index) => {
                const row = `
                    <tr>
                        <td>${detalle.idser}</td>
                        <td>${detalle.descripcion}</td>
                        <td>${detalle.meses}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" onclick="editarDetalleEdit(${index})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarDetalleEdit(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            document.getElementById('edit_detalles_producto').value = JSON.stringify(detallesEdit);
        }

        function eliminarDetalleEdit(index) {
            detallesEdit.splice(index, 1);
            renderDetallesEdit();
        }

        function editarDetalleEdit(index) {
            const detalle = detallesEdit[index];
            document.getElementById('editar_detalle_index').value = index;
            document.getElementById('editar_idser').value = detalle.idser;
            document.getElementById('editar_descripcion').value = detalle.descripcion;
            document.getElementById('editar_meses').value = detalle.meses;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editarDetalleModal' }));
        }

        function closeEditarDetalleModal() {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editarDetalleModal' }));
        }

        function guardarCambiosDetalle() {
            const index = document.getElementById('editar_detalle_index').value;
            const idser = document.getElementById('editar_idser').value;
            const descripcion = document.getElementById('editar_descripcion').value;
            const meses = document.getElementById('editar_meses').value;

            if (!idser || !descripcion || !meses) {
                alert('Por favor complete todos los campos.');
                return;
            }

            detallesEdit[index] = { idser, descripcion, meses };
            renderDetallesEdit();
            closeEditarDetalleModal();
        }
    </script>

    {{-- ============================================================================ --}}
    {{-- FUNCIONES DE MODAL - ELIMINAR --}}
    {{-- ============================================================================ --}}
    <script>
        function openDeleteModal(id) {
            console.log('🔷 Abriendo modal de eliminar producto:', id);

            fetch(`{{ route('productos.show', '') }}/${id}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(producto => {
                document.getElementById('delete_producto_id').value = producto.id;
                document.getElementById('delete_producto_codigo').textContent = producto.codigopro;
                document.getElementById('delete_producto_nombre').textContent = producto.nombrepro;
                document.getElementById('delete_producto_precio').textContent = '$' + parseFloat(producto.preciopro).toFixed(2);
                document.getElementById('delete_producto_tipo').textContent = producto.tipo_producto?.nombre || 'N/A';
                document.getElementById('delete_producto_categoria').textContent = producto.categoria?.nombre || 'N/A';
                document.getElementById('delete_producto_estado').textContent = producto.activo ? 'Activo' : 'Inactivo';

                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'deleteProductoModal' }));
            })
            .catch(error => {
                console.error('Error al cargar producto:', error);
                alert('Error al cargar los datos del producto');
            });
        }

        function closeDeleteModal() {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteProductoModal' }));
        }

        async function submitDelete(event) {
            event.preventDefault();
            console.log('📤 Enviando solicitud de eliminar producto...');

            const id = document.getElementById('delete_producto_id').value;

            try {
                const response = await fetch(`{{ route('productos.destroy', '') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    console.log('✅ Producto eliminado');
                    closeDeleteModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    console.error('❌ Error al eliminar:', data);
                    alert(data.message || 'Error al eliminar el producto');
                }
            } catch (error) {
                console.error('❌ Error de red:', error);
                alert('Error de conexión. Por favor, intenta nuevamente.');
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copiarMensajeProductosBtn = document.getElementById('copiarMensajeProductos');

            copiarMensajeProductosBtn.addEventListener('click', function() {
                // Generar el mensaje basado en los productos individuales con meses = 1
                let mensaje = '*Precios 1 mes 1 disp*\n';
                @foreach ($productos as $producto)
                    @if ($producto->detalles->where('meses', 1)->isNotEmpty() && $producto->categoria->nombre == 'Individual')
                        mensaje +=
                            `{{ $producto->nombrepro }}: ${{ number_format($producto->preciopro, 2) }}\n`;
                    @endif
                @endforeach

                // Copiar el mensaje al portapapeles
                navigator.clipboard.writeText(mensaje).then(() => {
                    alert('Mensaje copiado al portapapeles');
                }).catch(err => {
                    console.error('Error al copiar el mensaje: ', err);
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copiarMensajeCombosBtn = document.getElementById('copiarMensajeCombos');

            copiarMensajeCombosBtn.addEventListener('click', function() {
                // Generar el mensaje basado en los productos de la categoría "Combos" con meses = 1
                let mensaje = '*Combos 1 mes 1 disp*\n';
                @foreach ($productos as $producto)
                    @if ($producto->detalles->where('meses', 1)->isNotEmpty() && $producto->categoria->nombre == 'Combo')
                        mensaje +=
                            `{{ $producto->nombrepro }}: ${{ number_format($producto->preciopro, 2) }}\n`;
                    @endif
                @endforeach

                // Copiar el mensaje al portapapeles
                navigator.clipboard.writeText(mensaje).then(() => {
                    alert('Mensaje copiado al portapapeles');
                }).catch(err => {
                    console.error('Error al copiar el mensaje: ', err);
                });
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const servicioSelect = document.getElementById("servicioSelect");
            const copiarPlanesServicioBtn = document.getElementById("copiarPlanesServicio");

            // Convertimos los datos de Laravel en un array JS
            const productos = @json($productos);

            if (copiarPlanesServicioBtn) {
                copiarPlanesServicioBtn.addEventListener("click", function() {
                    const servicioId = servicioSelect.value;

                    if (!servicioId) {
                        alert("Por favor, selecciona un servicio.");
                        return;
                    }

                    const nombreServicio = servicioSelect.options[servicioSelect.selectedIndex].text;
                    let mensaje = `*Planes de ${nombreServicio}*\n`;

                    let tienePlanes = false;

                    productos.forEach(producto => {
                        const detalles = producto.detalles || [];
                        const categoriaNombre = producto.categoria?.nombre;

                        const relacionados = detalles.filter(det => det.idser == servicioId);

                        if (relacionados.length > 0 && categoriaNombre === 'Individual') {
                            mensaje +=
                                `${producto.nombrepro}: $${parseFloat(producto.preciopro).toFixed(2)}\n`;
                            tienePlanes = true;
                        }
                    });

                    if (!tienePlanes) {
                        alert("No se encontraron planes para el servicio seleccionado.");
                        return;
                    }

                    navigator.clipboard.writeText(mensaje).then(() => {
                        alert("Planes copiados al portapapeles.");
                    }).catch((err) => {
                        console.error("Error al copiar el mensaje: ", err);
                    });
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copiarMensajeBtn = document.getElementById('copiarMensaje');

            copiarMensajeBtn.addEventListener('click', function() {
                // Generar el mensaje basado en los servicios presentes
                let mensaje = '*Precios 1 mes 1 disp*\n';
                @foreach ($serviciosConfig as $key => $servicioInfo)
                    mensaje +=
                        `{{ $servicioInfo['nombre'] }}: ${{ $servicioInfo['precioser'] ?? 'N/A' }}\n`;
                @endforeach

                // Copiar el mensaje al portapapeles
                navigator.clipboard.writeText(mensaje).then(() => {
                    alert('Mensaje copiado al portapapeles');
                }).catch(err => {
                    console.error('Error al copiar el mensaje: ', err);
                });
            });
        });
    </script>

    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('btnCurarCodigos');
            if (!btn) return;

            btn.addEventListener('click', async function () {
                if (!confirm('¿Deseas recalcular todos los códigos de productos al formato estándar del SRI? Esta acción actualiza los códigos en la base de datos.')) return;

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Curando...';

                try {
                    const response = await fetch('{{ route("productos.curarCodigos") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        let msg = data.message;
                        if (data.conflictos && data.conflictos.length > 0) {
                            msg += '\n\nConflictos omitidos:\n• ' + data.conflictos.join('\n• ');
                        }
                        alert(msg);
                        location.reload();
                    } else {
                        alert(data.message || 'Error al curar los códigos.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error de conexión. Por favor, intenta nuevamente.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-magic"></i> Curar Códigos SRI';
                }
            });
        });
    </script>
@endsection
