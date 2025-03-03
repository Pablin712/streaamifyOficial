@extends('layouts.static')

@section('title', 'Crear Venta')
@section('styles')
    <!-- CSS de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('h1', 'Crear Venta')
@section('breadcrumb')
    <a href="{{ route('ventas') }}">Ventas</a>
@endsection
@section('breadcrumb2')
    Registrar Venta
@endsection
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
    Aquí puedes agregar una nueva venta.
@endsection
@section('content')
    <div class="container">
        <h2>Crear Nueva Venta</h2>
        <form id="form-venta" method="POST" action="{{ route('ventas.store') }}">
            @csrf
            <div class="form-group mb-3">
                <label for="idcli">Seleccionar Cliente</label>

                <select name="idcli" id="idcli" class="form-control" required>
                    <option value="">-- Selecciona un Cliente --</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->idcli }}" {{ request('idcli') == $cliente->idcli ? 'selected' : '' }}>
                            {{ $cliente->nombrecli }} - {{ $cliente->telefonocli }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal"
                data-bs-target="#registrarClienteModal">
                Nuevo Cliente
            </button>

            <div class="mt-4">
                <h4>Detalles de Venta</h4>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarDetalleModal">
                    Agregar Detalle
                </button>

                <table class="table table-bordered mt-3" id="detalles-venta">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Perfil</th>
                            <th>Descripción</th>
                            <th>Fecha de Vencimiento</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-detalles">
                        <!-- Los detalles se agregarán aquí mediante JavaScript -->
                    </tbody>
                </table>

                <div class="text-end">
                    <strong>Total Venta: $<span id="total-venta">0.00</span></strong>
                </div>
            </div>
            <!-- Campo oculto para enviar los detalles de la venta -->
            <input type="hidden" name="detalles_venta" id="detalles_venta">
            {{-- Campo oculto para enviar la información del empleado que registró la venta (CON LOGIN) --}}
            <input type="hidden" name="idemp" id="idemp" value={{ Auth::user()->idemp }}>
            <button type="submit" class="btn btn-primary mt-4" id="registrar-venta">Registrar Venta</button>
        </form>
    </div>
    <br>

    <!-- Modal para crear un nuevo cliente -->
    <div class="modal fade" id="registrarClienteModal" tabindex="-1" aria-labelledby="registrarClienteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrarClienteModalLabel">Registrar nuevo cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('clientes.storeInVenta') }}" method="POST">
                        @csrf
                        <!-- Campos del Cliente -->
                        <div class="form-group mb-3">
                            <label for="nombrecli">Nombre:</label>
                            <input type="text" name="nombrecli" id="nombrecli" class="form-control" required>
                            {{-- antes: descripcioncos --}}
                        </div>
                        <div class="form-group mb-3">
                            <label for="telefonocli">Teléfono:</label>
                            <input type="text" name="telefonocli" id="telefonocli" class="form-control" required>
                            {{-- antes: descripcioncos --}}
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

    <!-- Modal para agregar detalle -->
    <div class="modal fade" id="agregarDetalleModal" tabindex="-1" aria-labelledby="agregarDetalleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agregarDetalleModalLabel">Agregar Detalle a la Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formDetalle">
                        <!-- Select Cuenta -->
                        <div class="mb-3">
                            <label for="selectCuenta" class="form-label">Cuenta</label>
                            <select class="form-select" id="selectCuenta" required>
                                <option value="">Seleccione una cuenta</option>
                                @foreach ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->idcue }}">
                                        {{ $cuenta->idcue }}: Oc: {{ $cuenta->usuarios_activos }} ::
                                        @foreach ($cuenta->perfiles as $perfil)
                                            <!-- Mostrar todos los perfiles y sus usuarios activos -->
                                            P{{ $perfil->numeroper }}: {{ $perfil->usuarios_activos }}&nbsp;&nbsp;
                                        @endforeach
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Select Perfil -->
                        <div class="mb-3">
                            <label for="selectPerfil" class="form-label">Perfil</label>
                            <input type="number" class="form-control" id="selectPerfil" min="1" max='7'
                                required>
                        </div>

                        <!-- Fecha de vencimiento -->
                        <div class="mb-3">
                            <label for="fechaVencimiento" class="form-label">Fecha de Vencimiento</label>
                            <input type="date" class="form-control" id="fechaVencimiento" required>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="descripcion" required>
                        </div>

                        <!-- Monto -->
                        <div class="mb-3">
                            <label for="monto" class="form-label">Monto</label>
                            <input type="number" class="form-control" id="monto" step="0.01" min="0"
                                required>
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
                    <h5 class="modal-title" id="editarDetalleModalLabel">Editar Detalle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editarDetalleForm">
                        <!-- Select Cuenta -->
                        <div class="mb-3">
                            <label for="editarSelectCuenta" class="form-label">Cuenta</label>
                            <select class="form-select" id="editarSelectCuenta" required>
                                <option value="">Seleccione una cuenta</option>
                                @foreach ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->idcue }}">
                                        {{ $cuenta->idcue }}: Oc: {{ $cuenta->usuarios_activos }} ::
                                        @foreach ($cuenta->perfiles as $perfil)
                                            <!-- Mostrar todos los perfiles y sus usuarios activos -->
                                            P{{ $perfil->numeroper }}: {{ $perfil->usuarios_activos }}&nbsp;&nbsp;
                                        @endforeach
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Select Perfil -->
                        <div class="mb-3">
                            <label for="editarSelectPerfil" class="form-label">Perfil</label>
                            <input type="number" class="form-control" id="editarSelectPerfil" min="1"
                                max="7" required>
                        </div>

                        <!-- Fecha de Vencimiento -->
                        <div class="mb-3">
                            <label for="editarFechaVencimiento" class="form-label">Fecha de Vencimiento</label>
                            <input type="date" class="form-control" id="editarFechaVencimiento" required>
                        </div>

                        <!-- Monto -->
                        <div class="mb-3">
                            <label for="editarMonto" class="form-label">Monto</label>
                            <input type="number" class="form-control" id="editarMonto" step="0.01" min="0"
                                required>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="editarDescripcion" class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="editarDescripcion" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="guardarCambiosDetalleBtn">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('pie')
    <p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
    <a href="{{ route('ventas') }}" class="btn btn-secondary">Volver a Ventas</a>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="{{asset('js/createVenta.js')}}"></script>
    <script>
        // Script que se ejecuta cuando se abre el modal
        $('#registrarClienteModal').on('shown.bs.modal', function(event) {
            var modal = $(this);
            modal.find('#nombrecli').val(''); // Limpiar el campo de nombre
            modal.find('#telefonocli').val(''); // Limpiar el campo de teléfono
        });

        // Verificar si la variable cliente está presente (es decir, se pasó desde el controlador)
        @isset($cliente)
            // Agregar la nueva opción al select
            $('#clienteSelect').append(
                '<option value="{{ $cliente->idcli }}" selected>{{ $cliente->nombrecli }} - {{ $cliente->telefonocli }}</option>'
            );
        @endisset

        // Manejo del formulario para registrar un cliente
        $('#formRegistrarCliente').submit(function(e) {
            e.preventDefault(); // Evitar el envío normal del formulario

            var form = $(this);
            var formData = form.serialize(); // Obtener los datos del formulario

            // Enviar los datos al servidor usando AJAX
            $.ajax({
                url: form.attr('action'), // URL del formulario
                method: 'POST',
                data: formData,
                success: function(response) {
                    // Suponemos que la respuesta contiene los datos del nuevo cliente (id y nombre)
                    var nuevoCliente = response.cliente;

                    // Agregar la nueva opción al select
                    $('#clienteSelect').append(
                        '<option value="' + nuevoCliente.idcli + '" selected>' + nuevoCliente
                        .nombrecli +
                        '</option>'
                    );

                    // Cerrar el modal
                    $('#registrarClienteModal').modal('hide');
                },
                error: function(xhr, status, error) {
                    // Manejar cualquier error
                    alert('Ocurrió un error al registrar el cliente.');
                }
            });
        });
    </script>
@endsection
