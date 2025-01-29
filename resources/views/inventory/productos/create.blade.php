@extends('layouts.static')

@section('title', 'Crear Producto')
@section('styles')
    <style>
        .star {
            font-size: 1.5rem;
            color: #ccc;
            cursor: pointer;
        }

        .star.selected {
            color: #ffc107;
        }

        .star:hover~.star {
            color: #ccc !important;
        }

        .star:hover {
            color: #ffc107;
        }
    </style>
@endsection
@section('h1', 'Crear Producto')
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
    Aquí puedes agregar un nuevo producto y sus detalles asociados.
@endsection

@section('content')
    <div class="container">
        <h2>Crear Nuevo Producto</h2>
        <form id="form-producto" method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Campos del Producto -->
            <div class="form-group mb-3">
                <label for="codigopro">Código del Producto</label>
                <input type="text" name="codigopro" id="codigopro" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="nombrepro">Nombre del Producto</label>
                <input type="text" name="nombrepro" id="nombrepro" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="preciopro">Precio</label>
                <input type="number" name="preciopro" id="preciopro" class="form-control" step="0.01" min="0"
                    required>
            </div>
            <div class="form-group mb-3">
                <label for="estrellaspro">Calificación (Estrellas)</label>
                <select name="estrellaspro" id="estrellaspro" class="form-control" required>
                    <option value="">-- Selecciona una Calificación --</option>
                    <option value="1">1 Estrella</option>
                    <option value="2">2 Estrellas</option>
                    <option value="3">3 Estrellas</option>
                    <option value="4">4 Estrellas</option>
                    <option value="5">5 Estrellas</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="descripcionpro">Descripción</label>
                <textarea name="descripcionpro" id="descripcionpro" class="form-control" rows="4"></textarea>
            </div>
            <div class="form-group mb-3">
                <label for="foto">Foto del Producto</label>
                <input type="file" name="foto" id="foto" class="form-control">
            </div>
            <div class="form-group mb-3">
                <label for="tipo_producto_id">Tipo de Producto</label>
                <select name="tipo_producto_id" id="tipo_producto_id" class="form-control" required>
                    <option value="">-- Selecciona un Tipo --</option>
                    @foreach ($tipos_producto as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="categoria_id">Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-control" required>
                    <option value="">-- Selecciona una Categoría --</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="activo">Estado</label>
                <select name="activo" id="activo" class="form-control" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>

            <!-- Detalles del Producto -->
            <div class="mt-4">
                <h4>Detalles del Producto</h4>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarDetalleModal">
                    Agregar Detalle
                </button>

                <table class="table table-bordered mt-3" id="tabla-detalles">
                    <thead>
                        <tr>
                            <th>ID Servicio</th>
                            <th>Descripción</th>
                            <th>Meses</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-detalles">
                        <!-- Los detalles se agregarán aquí mediante JavaScript -->
                    </tbody>
                </table>

            </div>

            
                <!-- Campo oculto para enviar los detalles del producto -->
                <input type="hidden" name="detalles_producto" id="detalles_producto">
            <button type="submit" class="btn btn-primary mt-4">Registrar Producto</button>
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
                        <!-- ID Servicio -->
                        <div class="mb-3">
                            <label for="idser" class="form-label">ID Servicio</label>
                            <select class="form-select" id="idser" required>
                                <option value="">Seleccione un Servicio</option>
                                @foreach ($servicios as $servicio)
                                    <option value="{{ $servicio->idser }}">
                                        {{ $servicio->idser }}: {{ $servicio->nombreser }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" rows="3" required></textarea>
                        </div>

                        <!-- Meses -->
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

    <!-- Modal para editar detalle del producto -->
    <div class="modal fade" id="editarDetalleModal" tabindex="-1" aria-labelledby="editarDetalleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarDetalleModalLabel">Editar Detalle del Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarDetalle">
                        <!-- ID Servicio -->
                        <div class="mb-3">
                            <label for="editarIdServicio" class="form-label">ID Servicio</label>
                            <select class="form-select" id="editarIdServicio" required>
                                <option value="">Seleccione un Servicio</option>
                                @foreach ($servicios as $servicio)
                                    <option value="{{ $servicio->idser }}">
                                        {{ $servicio->idser }}: {{ $servicio->nombreser }}
                                    </option>
                                @endforeach
                            </select>
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
                        <button type="button" class="btn btn-primary" id="guardarCambiosDetalle">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pie')
    <p>¿No deseas agregar un producto? Vuelve a la página de listado:</p>
    <a href="{{ route('productos.index') }}" class="btn btn-secondary">Volver a Productos</a>
@endsection

@section('scripts')
    <script>
        // Agregar detalle a la tabla
        $('#guardarDetalleBtn').on('click', function() {
            // Obtener los valores del formulario de detalle
            var idser = $('#idser').val();
            var descripcion = $('#descripcion').val();
            var meses = $('#meses').val();

            // Validar que todos los campos estén completos
            if (idser && descripcion && meses) {
                // Crear una nueva fila con los datos del detalle
                var nuevaFila = `<tr>
                    <td>${idser}</td>
                    <td>${descripcion}</td>
                    <td>${meses}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm eliminarDetalleBtn">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm editarDetalleBtn">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>`;

                // Agregar la nueva fila a la tabla
                $('#tabla-detalles tbody').append(nuevaFila);

                // Limpiar los campos del modal
                $('#idser').val('');
                $('#descripcion').val('');
                $('#meses').val('');

                // Cerrar el modal
                $('#agregarDetalleModal').modal('hide');
            } else {
                alert('Por favor complete todos los campos.');
            }
        });

        // Eliminar fila de la tabla
        $('#tabla-detalles').on('click', '.eliminarDetalleBtn', function() {
            $(this).closest('tr').remove();
        });

        // Editar fila de la tabla
        $('#tabla-detalles').on('click', '.editarDetalleBtn', function() {
            var fila = $(this).closest('tr');
            var idser = fila.find('td').eq(0).text();
            var descripcion = fila.find('td').eq(1).text();
            var meses = fila.find('td').eq(2).text();
            $('#editarIdServicio').val(idser);
            $('#editarDescripcion').val(descripcion);
            $('#editarMeses').val(meses);
            $('#editarDetalleModal').modal('show');
        });

        // Guardar cambios del detalle editado
        $('#guardarCambiosDetalle').on('click', function() {
            var idser = $('#editarIdServicio').val();
            var descripcion = $('#editarDescripcion').val();
            var meses = $('#editarMeses').val();
            var fila = $('#tabla-detalles').find('tr').filter(function() {
                return $(this).find('td').eq(0).text() === idser;
            });
            fila.find('td').eq(1).text(descripcion);
            fila.find('td').eq(2).text(meses);
            $('#editarDetalleModal').modal('hide');
        });

        // Enviar detalles al backend
        $('#form-producto').on('submit', function(event) {
            let detalles = [];
            $('#tabla-detalles tbody tr').each(function() {
                let idser = $(this).find('td').eq(0).text();
                let descripcion = $(this).find('td').eq(1).text();
                let meses = $(this).find('td').eq(2).text();
                if (idser && descripcion && meses) {
                    detalles.push({
                        idser: idser,
                        descripcion: descripcion,
                        meses: meses
                    });
                }
            });

            $('#detalles_producto').val(JSON.stringify(detalles));

            this.submit(); // Enviar el formulario
        });

        // Script para manejar la selección de estrellas
        $(document).ready(function() {
            $('.star').on('click', function() {
                const value = $(this).data('value');
                $('#estrellaspro').val(value); // Asignar el valor seleccionado al input oculto
                $('.star').removeClass('selected'); // Quitar la selección previa
                $(this).addClass('selected'); // Agregar selección a la estrella actual y las anteriores
                $(this).prevAll().addClass('selected'); // Agregar selección a las estrellas anteriores
            });
        });

        // Función para abrir el modal de edición con los datos del detalle seleccionado
        function editarDetalle(idServicio, descripcion, meses) {
            $('#editarIdServicio').val(idServicio);
            $('#editarDescripcion').val(descripcion);
            $('#editarMeses').val(meses);
            $('#editarDetalleModal').modal('show');
        }

        // Ejemplo de cómo agregar una fila con el botón de editar
        function agregarFilaDetalle(idServicio, descripcion, meses) {
            var tabla = document.getElementById('tabla-detalles');
            var fila = tabla.insertRow();
            fila.insertCell(0).innerText = idServicio;
            fila.insertCell(1).innerText = descripcion;
            fila.insertCell(2).innerText = meses;
            var acciones = fila.insertCell(3);
            var botonEditar = document.createElement('button');
            botonEditar.className = 'btn btn-warning';
            botonEditar.innerText = 'Editar';
            botonEditar.onclick = function() {
                editarDetalle(idServicio, descripcion, meses);
            };
            acciones.appendChild(botonEditar);
        }
    </script>
@endsection
