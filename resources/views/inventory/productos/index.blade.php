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
            <a href="{{ route('productos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear Producto
            </a>
        @endif

        <a href="{{ route('productos.pdf') }}" class="btn btn-success">
            <i class="fas fa-file-pdf"></i> Descargar Catálogo PDF
        </a>

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
    <script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
@endsection
