@extends('layouts.static')

@section('title', 'Editar Producto')

@section('h1', 'Editar Producto')
@section('introduccion')
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
    En esta vista, puedes editar un producto existente y sus detalles asociados.
@endsection

@section('content')
    <div class="container">
        <h2>Editar Producto</h2>
        <form id="form-producto" method="POST" action="{{ route('productos.update', $producto->id) }}"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Campos del Producto -->
            <div class="form-group mb-3">
                <label for="codigopro">Código del Producto</label>
                <input type="text" name="codigopro" id="codigopro" class="form-control" value="{{ $producto->codigopro }}"
                    required>
            </div>
            <div class="form-group mb-3">
                <label for="nombrepro">Nombre del Producto</label>
                <input type="text" name="nombrepro" id="nombrepro" class="form-control"
                    value="{{ $producto->nombrepro }}" required>
            </div>
            <div class="form-group mb-3">
                <label for="preciopro">Precio</label>
                <input type="number" name="preciopro" id="preciopro" class="form-control" step="0.01" min="0"
                    value="{{ $producto->preciopro }}" required>
            </div>
            <div class="form-group mb-3">
                <label for="descripcionpro">Descripción</label>
                <textarea name="descripcionpro" id="descripcionpro" class="form-control" rows="4">{{ $producto->descripcionpro }}</textarea>
            </div>
            <div class="form-group mb-3">
                <label for="foto">Foto del Producto (opcional)</label>
                <input type="file" name="foto" id="foto" class="form-control">
                <img src="{{ asset('storage/' . $producto->foto) }}" alt="Foto del producto" class="img-thumbnail mt-2"
                    width="150">
            </div>
            <div class="form-group mb-3">
                <label for="tipo_producto_id">Tipo de Producto</label>
                <select name="tipo_producto_id" id="tipo_producto_id" class="form-control" required>
                    @foreach ($tipos_producto as $tipo)
                        <option value="{{ $tipo->id }}"
                            {{ $producto->tipo_producto_id == $tipo->id ? 'selected' : '' }}>
                            {{ $tipo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="categoria_id">Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-control" required>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}"
                            {{ $producto->categoria_id == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="activo">Estado</label>
                <select name="activo" id="activo" class="form-control" required>
                    <option value="1" {{ $producto->activo ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ !$producto->activo ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>

            <!-- Detalles del Producto -->
            <div class="mt-4">
                <h4>Detalles del Producto</h4>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarDetalleModal">
                    Agregar Detalle
                </button>

                <table class="table table-bordered mt-3" id="detalles-producto">
                    <thead>
                        <tr>
                            <th>ID Servicio</th>
                            <th>Descripción</th>
                            <th>Meses</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-detalles">
                        @foreach ($producto->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->idser }}</td>
                                <td>{{ $detalle->descripcion }}</td>
                                <td>{{ $detalle->meses }}</td>
                                <td>
                                    <!-- Botón Editar -->
                                    <button type="button" class="btn btn-warning btn-sm editarDetalleBtn"
                                        data-idser="{{ $detalle->idser }}" data-descripcion="{{ $detalle->descripcion }}"
                                        data-meses="{{ $detalle->meses }}" data-bs-toggle="modal"
                                        data-bs-target="#editarDetalleModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm eliminarDetalleBtn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Campo oculto para enviar los detalles del producto -->
            <input type="hidden" name="detalles_producto" id="detalles_producto">

            <button type="submit" class="btn btn-primary mt-4">Actualizar Producto</button>
        </form>
    </div>

    <!-- Modal para agregar detalle -->
    <div class="modal fade" id="agregarDetalleModal" tabindex="-1" aria-labelledby="agregarDetalleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agregarDetalleModalLabel">Agregar Detalle al Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formDetalle">
                        <div class="mb-3">
                            <label for="idser" class="form-label">ID Servicio</label>
                            <select class="form-select" id="selectServicio" required>
                                <option value="">Seleccione un Servicio</option>
                                @foreach ($servicios as $servicio)
                                    <option value="{{ $servicio->idser }}">
                                        {{ $servicio->idser }}: {{ $servicio->nombreser }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="meses" class="form-label">Meses</label>
                            <input type="number" class="form-control" id="meses" min="1" required>
                        </div>
                        <button type="button" class="btn btn-primary" id="guardarDetalleBtn">Guardar Detalle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para editar detalle -->
    <div class="modal fade" id="editarDetalleModal" tabindex="-1" aria-labelledby="editarDetalleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarDetalleModalLabel">Editar Detalle del Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarDetalle">
                        <!-- ID Servicio -->
                        <div class="mb-3">
                            <label for="editarIdser" class="form-label">ID Servicio</label>
                            <input type="text" class="form-control" id="editarIdser" readonly>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="editarDescripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="editarDescripcion" rows="3" required></textarea>
                        </div>

                        <!-- Meses -->
                        <div class="mb-3">
                            <label for="editarMeses" class="form-label">Meses</label>
                            <input type="number" class="form-control" id="editarMeses" min="1" required>
                        </div>

                        <button type="button" class="btn btn-primary" id="guardarCambiosDetalleBtn">Guardar
                            Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pie')
    <p>¿No deseas editar este producto? Vuelve a la página de listado:</p>
    <a href="{{ route('productos.index') }}" class="btn btn-secondary">Volver a Productos</a>
@endsection

@section('scripts')
<script>
    let detalleEditando = null; // Variable para guardar el detalle que se está editando

    // Abrir el modal de edición y cargar los datos
    $(document).on('click', '.editarDetalleBtn', function() {
        const idser = $(this).data('idser');
        const descripcion = $(this).data('descripcion');
        const meses = $(this).data('meses');

        // Guardar la referencia a la fila que se está editando
        detalleEditando = $(this).closest('tr');

        // Cargar los valores en los campos del modal
        $('#editarIdser').val(idser);
        $('#editarDescripcion').val(descripcion);
        $('#editarMeses').val(meses);
    });

    // Guardar los cambios del detalle
    $('#guardarCambiosDetalleBtn').on('click', function() {
        if (!detalleEditando) return; // Si no hay un detalle cargado, salir

        // Obtener los valores del modal
        const idser = $('#editarIdser').val();
        const descripcion = $('#editarDescripcion').val();
        const meses = $('#editarMeses').val();

        // Validar los campos
        if (descripcion && meses) {
            // Actualizar los datos en la fila correspondiente
            detalleEditando.find('td').eq(1).text(descripcion);
            detalleEditando.find('td').eq(2).text(meses);

            // Cerrar el modal
            $('#editarDetalleModal').modal('hide');
        } else {
            alert('Por favor, completa todos los campos.');
        }
    });

    // Agregar un detalle nuevo
    $('#guardarDetalleBtn').on('click', function() {
        const idser = $('#idser').val();
        const descripcion = $('#descripcion').val();
        const meses = $('#meses').val();

        if (idser && descripcion && meses) {
            const nuevaFila = `<tr>
                <td>${idser}</td>
                <td>${descripcion}</td>
                <td>${meses}</td>
                <td>
                    <button type="button" class="btn btn-warning btn-sm editarDetalleBtn"
                        data-idser="${idser}" data-descripcion="${descripcion}" data-meses="${meses}"
                        data-bs-toggle="modal" data-bs-target="#editarDetalleModal">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm eliminarDetalleBtn">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;

            $('#tabla-detalles').append(nuevaFila);

            // Limpiar los campos
            $('#idser').val('');
            $('#descripcion').val('');
            $('#meses').val('');

            // Cerrar el modal de agregar
            $('#agregarDetalleModal').modal('hide');
        } else {
            alert('Por favor complete todos los campos.');
        }
    });

    // Eliminar una fila de detalles
    $(document).on('click', '.eliminarDetalleBtn', function() {
        $(this).closest('tr').remove();
    });

    // Guardar los detalles al enviar el formulario
    $('#form-producto').on('submit', function(event) {
        const detalles = [];
        $('#tabla-detalles tr').each(function() {
            const idser = $(this).find('td').eq(0).text();
            const descripcion = $(this).find('td').eq(1).text();
            const meses = $(this).find('td').eq(2).text();
            detalles.push({ idser, descripcion, meses });
        });
        $('#detalles_producto').val(JSON.stringify(detalles));
    });
</script>
@endsection
