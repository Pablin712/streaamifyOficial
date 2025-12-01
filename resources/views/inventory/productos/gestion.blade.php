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
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#crearCategoriaModal">
            Crear Categoría
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
                                <button type="button" class="btn btn-warning fas fa-edit" data-bs-toggle="modal"
                                    data-bs-target="#editarCategoriaModal" data-id="{{ $categoria->id }}"
                                    data-nombre="{{ $categoria->nombre }}" data-descripcion="{{ $categoria->descripcion }}">
                                    Editar
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('categorias.destroy'))
                                <!-- Formulario para eliminar categoría -->
                                <form action="{{ route('categorias.destroy', $categoria->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('¿Estás seguro?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
    <h3>Tipos de Producto</h3>
    @if (Auth::user()->hasPermissionTo('tipos_producto.store'))
        <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#crearTipoProductoModal">
            Crear Tipo de Producto
        </button>
    @endif

    <!-- Controles de búsqueda y registros -->
    <div class="row mb-3 align-items-end">
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

    <div class="table-responsive">
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
                                <button type="button" class="btn btn-warning fas fa-edit" data-bs-toggle="modal"
                                    data-bs-target="#editarTipoProductoModal" data-id="{{ $tipoProducto->id }}"
                                    data-nombre="{{ $tipoProducto->nombre }}"
                                    data-descripcion="{{ $tipoProducto->descripcion }}">
                                    Editar
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('tipos_producto.destroy'))
                                <form action="{{ route('tipos_producto.destroy', $tipoProducto->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('¿Estás seguro?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
            <div id="tipos-producto-table-row-info" class="text-muted"></div>
        </div>
        <div class="col-md-6 col-12">
            <div id="tipos-producto-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
        </div>
    </div>
@endsection
<!-- Modal para crear una nueva categoría -->
<div class="modal fade" id="crearCategoriaModal" tabindex="-1" aria-labelledby="crearCategoriaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crearCategoriaModalLabel">Crear Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('categorias.store') }}" method="POST">
                    @csrf
                    <!-- Campos del formulario -->
                    <div class="form-group mb-3">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="descripcion">Descripción</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar una categoría -->
<div class="modal fade" id="editarCategoriaModal" tabindex="-1" aria-labelledby="editarCategoriaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarCategoriaModalLabel">Editar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoriaForm" method="POST">
                    @csrf
                    @method('PUT')
                    <!-- Campos del formulario -->
                    <div class="form-group mb-3">
                        <label for="edit_nombre">Nombre</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_descripcion">Descripción</label>
                        <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal para Crear Tipo de Producto -->
<div class="modal fade" id="crearTipoProductoModal" tabindex="-1" aria-labelledby="crearTipoProductoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('tipos_producto.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="crearTipoProductoModalLabel">Crear Tipo de Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Editar Tipo de Producto -->
<div class="modal fade" id="editarTipoProductoModal" tabindex="-1" aria-labelledby="editarTipoProductoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <form id="editTipoProductoForm" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editarTipoProductoModalLabel">Editar Tipo de Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="edit_nombre">Nombre</label>
                    <input type="text" name="nombre" id="edit_tipo_nombre" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label for="edit_descripcion">Descripción</label>
                    <textarea name="descripcion" id="edit_tipo_descripcion" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
    <script>
        // Llenar el modal de edición con los datos de la categoría
        $('#editarCategoriaModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Botón que abrió el modal
            var id = button.data('id');
            var nombre = button.data('nombre');
            var descripcion = button.data('descripcion');

            var modal = $(this);
            modal.find('#edit_nombre').val(nombre);
            modal.find('#edit_descripcion').val(descripcion);

            // Cambiar la acción del formulario para la categoría específica
            var formAction = "{{ route('categorias.update', '') }}/" + id;
            modal.find('#editCategoriaForm').attr('action', formAction);
        });
        // Llenar los datos en el modal para editar tipo de producto
        $('#editarTipoProductoModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var nombre = button.data('nombre');
            var descripcion = button.data('descripcion');

            var modal = $(this);
            modal.find('#edit_tipo_nombre').val(nombre);
            modal.find('#edit_tipo_descripcion').val(descripcion);

            var formAction = "{{ route('tipos_producto.update', '') }}/" + id;
            modal.find('#editTipoProductoForm').attr('action', formAction);
        });
    </script>

    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
@endsection
