@extends('layouts.table')

@section('title', 'Categorías y Tipos de producto')

@section('h1', 'Gestión de Categorías y Tipos de producto')
@section('breadcrumb')
    <a href="{{ route('productos.index') }}">Productos</a>
@endsection
@section('breadcrumb2')
    Gestión de Productos
@endsection
@section('styles')
    <link rel="icon" href="{{ asset('images/Icono.png') }}" type="image/x-icon">
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Administra las categorías y los tipos de producto en esta sección. Puedes crear, editar o eliminar registros
        fácilmente.</p>
@endsection

@section('btncrear')
    @if (Auth::user()->hasPermissionTo('categorias.store'))
        <!-- Botón para abrir el modal de crear categoría -->
        <button type="button" class="btn btn-primary mb-3" onclick="openCreateCategoriaModal()">
            <i class="fas fa-plus-circle me-1"></i> Crear Categoría
        </button>
    @endif
@endsection

@section('tablename', 'Categorías')
@section('table1')
    <!-- Controles de búsqueda y registros -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="categorias-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="categorias-table-search"
                   type="text"
                   placeholder="Buscar categoría..."
                   class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="categorias-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="categorias-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="20">20 registros</option>
                <option value="50">50 registros</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="categorias-table" data-table="categorias-table" class="table table-striped table-bordered">
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
                    Descripción
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                @if (Auth::user()->hasAnyPermission(['categorias.update', 'categorias.destroy']))
                    <th data-type="actions">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($categorias as $categoria)
                <tr>
                    <td>{{ $categoria->id }}</td>
                    <td>{{ $categoria->nombre }}</td>
                    <td>{{ $categoria->descripcion }}</td>
                    @if (Auth::user()->hasAnyPermission(['categorias.update', 'categorias.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('categorias.update'))
                                <!-- Botón para editar categoría -->
                                <button type="button" class="btn btn-warning btn-sm"
                                    onclick="editarCategoria({{ $categoria->id }}, '{{ $categoria->nombre }}', '{{ $categoria->descripcion }}')"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('categorias.destroy'))
                                <!-- Botón para eliminar categoría -->
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="confirmarEliminarCategoria({{ $categoria->id }})"
                                    title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
<!-- Tabla de Tipos de Producto -->
@section('table2')
    <div class="px-3 pt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Tipos de Producto</h3>
            @if (Auth::user()->hasPermissionTo('tipos_producto.store'))
                <button type="button" class="btn btn-success" onclick="openCreateTipoProductoModal()">
                    <i class="fas fa-plus-circle me-1"></i> Crear Tipo de Producto
                </button>
            @endif
        </div>
    </div>

    <!-- Controles de búsqueda y registros -->
    <div class="row mb-3 align-items-end px-3">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="tipos-producto-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="tipos-producto-table-search"
                   type="text"
                   placeholder="Buscar tipo de producto..."
                   class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="tipos-producto-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="tipos-producto-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="20">20 registros</option>
                <option value="50">50 registros</option>
            </select>
        </div>
    </div>

    <div class="table-responsive px-3">
        <table id="tipos-producto-table" data-table="tipos-producto-table" class="table table-striped table-bordered">
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
                    Descripción
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                @if (Auth::user()->hasAnyPermission(['tipos_producto.update', 'tipos_producto.destroy']))
                    <th data-type="actions">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($tiposProducto as $tipoProducto)
                <tr>
                    <td>{{ $tipoProducto->id }}</td>
                    <td>{{ $tipoProducto->nombre }}</td>
                    <td>{{ $tipoProducto->descripcion }}</td>
                    @if (Auth::user()->hasAnyPermission(['tipos_producto.update', 'tipos_producto.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('tipos_producto.update'))
                                <button type="button" class="btn btn-warning btn-sm"
                                    onclick="editarTipoProducto({{ $tipoProducto->id }}, '{{ $tipoProducto->nombre }}', '{{ $tipoProducto->descripcion }}')"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('tipos_producto.destroy'))
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="confirmarEliminarTipoProducto({{ $tipoProducto->id }})"
                                    title="Eliminar">
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
    <div class="row mt-3 align-items-center px-3 pb-3">
        <div class="col-md-6 col-12 mb-2 mb-md-0">
            <div id="tipos-producto-table-row-info" class="text-muted"></div>
        </div>
        <div class="col-md-6 col-12">
            <div id="tipos-producto-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
        </div>
    </div>
@endsection

<!-- Modales de Categorías -->
@include('inventory.productos.modals.crear-categoria')
@include('inventory.productos.modals.editar-categoria')
@include('inventory.productos.modals.eliminar-categoria')

<!-- Modales de Tipos de Producto -->
@include('inventory.productos.modals.crear-tipo-producto')
@include('inventory.productos.modals.editar-tipo-producto')
@include('inventory.productos.modals.eliminar-tipo-producto')

@section('scripts')
    <script>
        console.log('Vista de gestión de productos cargada');

        // ============================================================================
        // FUNCIONES DE MODAL - CREAR CATEGORÍA
        // ============================================================================
        function openCreateCategoriaModal() {
            console.log('🔷 Abriendo modal de crear categoría...');
            const form = document.getElementById('createCategoriaForm');
            if (form) form.reset();

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'crear-categoria' }));
        }

        // ============================================================================
        // FUNCIONES DE MODAL - EDITAR CATEGORÍA
        // ============================================================================
        function editarCategoria(id, nombre, descripcion) {
            console.log('🔷 Abriendo modal de editar categoría:', id);

            // Llenar el formulario
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_descripcion').value = descripcion;

            // Actualizar la acción del formulario
            const form = document.getElementById('editCategoriaForm');
            form.action = "{{ route('categorias.update', '') }}/" + id;

            // Abrir modal
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editar-categoria' }));
        }

        // ============================================================================
        // FUNCIONES DE MODAL - ELIMINAR CATEGORÍA
        // ============================================================================
        function confirmarEliminarCategoria(id) {
            console.log('🔷 Abriendo modal de eliminar categoría:', id);

            const form = document.getElementById('deleteCategoriaForm');
            form.action = "{{ route('categorias.destroy', '') }}/" + id;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'eliminar-categoria' }));
        }

        // ============================================================================
        // FUNCIONES DE MODAL - CREAR TIPO PRODUCTO
        // ============================================================================
        function openCreateTipoProductoModal() {
            console.log('🔷 Abriendo modal de crear tipo producto...');
            const form = document.getElementById('createTipoProductoForm');
            if (form) form.reset();

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'crear-tipo-producto' }));
        }

        // ============================================================================
        // FUNCIONES DE MODAL - EDITAR TIPO PRODUCTO
        // ============================================================================
        function editarTipoProducto(id, nombre, descripcion) {
            console.log('🔷 Abriendo modal de editar tipo producto:', id);

            // Llenar el formulario
            document.getElementById('edit_tipo_nombre').value = nombre;
            document.getElementById('edit_tipo_descripcion').value = descripcion;

            // Actualizar la acción del formulario
            const form = document.getElementById('editTipoProductoForm');
            form.action = "{{ route('tipos_producto.update', '') }}/" + id;

            // Abrir modal
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editar-tipo-producto' }));
        }

        // ============================================================================
        // FUNCIONES DE MODAL - ELIMINAR TIPO PRODUCTO
        // ============================================================================
        function confirmarEliminarTipoProducto(id) {
            console.log('🔷 Abriendo modal de eliminar tipo producto:', id);

            const form = document.getElementById('deleteTipoProductoForm');
            form.action = "{{ route('tipos_producto.destroy', '') }}/" + id;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'eliminar-tipo-producto' }));
        }
    </script>

    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
@endsection
