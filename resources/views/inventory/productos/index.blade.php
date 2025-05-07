@extends('layouts.table')

@section('title', 'Productos')
@section('styles')
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
                                <img src="{{ asset('images/' . $servicioInfo['icon']) }}" width="40" height="40" alt="{{ $servicioInfo['nombre'] }}">
                            @endif
                            <h6 class="card-title mt-2">{{ $servicioInfo['nombre'] }}</h6>
                            <div class="row">
                                <div class="col-6">
                                    <label for="precio_{{ $key }}" class="form-label small">Precio ($)</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" id="precio_{{ $key }}"
                                        name="precios[{{ $key }}][precio]" value="{{ old('precios.' . $key . '.precioser', $servicioInfo['precioser'] ?? '') }}" required>
                                </div>
                                <div class="col-6">
                                    <label for="combo_{{ $key }}" class="form-label small">Combo ($)</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" id="combo_{{ $key }}"
                                        name="precios[{{ $key }}][combo]" value="{{ old('precios.' . $key . '.comboser', $servicioInfo['comboser'] ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-sm">Guardar Precios</button>
        </div>
    </form>
@endsection

@section('btncrear')
    @if (Auth::user()->hasPermissionTo('productos.create'))
        <a href="{{ route('productos.create') }}" class="btn btn-primary mb-3">Crear Producto</a>
    @endif
    <a href="{{ route('productos.pdf') }}" class="btn btn-success mb-3">Descargar Catálogo PDF</a>
@endsection

@section('tablename', 'Productos')

@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID Producto</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Categoría</th>
                <th>Tipo</th>
                <th>Activo</th>
                @if (Auth::user()->hasAnyPermission(['productos.edit', 'productos.show', 'productos.destroy']))
                    <th>Acciones</th>
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
                                <!-- Botón para abrir el modal -->
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalProducto{{ $producto->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('productos.edit'))
                                <a href="{{ route('productos.edit', $producto->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if (Auth::user()->hasPermissionTo('productos.destroy'))
                                <!-- Eliminar producto -->
                                <form action="{{ route('productos.destroy', $producto->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Estás seguro de eliminar este producto?')">
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
    <!-- 🔹 Sección de Modales fuera del foreach -->
    @foreach ($productos as $producto)
        <div class="modal fade" id="modalProducto{{ $producto->id }}" tabindex="-1"
            aria-labelledby="modalProductoLabel{{ $producto->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalProductoLabel{{ $producto->id }}">
                            Detalles del Producto
                        </h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Información del Producto -->
                            <div class="col-md-8">
                                <h4 class="text-primary">{{ $producto->nombrepro }}</h4>
                                <p><strong>Código:</strong> {{ $producto->codigopro }}</p>
                                <p><strong>Precio:</strong> ${{ number_format($producto->preciopro, 2) }}</p>
                                <p><strong>Descripción:</strong> {{ $producto->descripcionpro }}</p>
                                <p><strong>Categoría:</strong> {{ $producto->categoria->nombre }}</p>
                                <p><strong>Tipo de Producto:</strong> {{ $producto->tipoProducto->nombre }}</p>
                                <p><strong>Estado:</strong>
                                    @if ($producto->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </p>
                            </div>
                            <!-- Imagen del Producto -->
                            <div class="col-md-4 text-center">
                                @if ($producto->foto)
                                    <img src="{{ asset('public/' . $producto->foto) }}" alt="Foto del Producto"
                                        class="img-fluid rounded shadow">
                                @else
                                    <p class="text-muted">Sin imagen disponible</p>
                                @endif
                            </div>
                        </div>

                        <!-- 🔹 Hacer la tabla de detalles desplazable en móviles -->
                        <div class="mt-4">
                            <h5>Detalles del Producto</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered text-center">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>ID Servicio</th>
                                            <th>Descripción</th>
                                            <th>Meses</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($producto->detalles as $detalle)
                                            <tr>
                                                <td>{{ $detalle->idser }}</td>
                                                <td>{{ $detalle->descripcion }}</td>
                                                <td>{{ $detalle->meses }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> <!-- Fin tabla responsive -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializa DataTables
            $('#datatablesSimple').DataTable();
            // Script adicional si deseas agregar eventos específicos
        });
    </script>
    <script src="{{ asset('js/productos.js') }}"></script>
@endsection
