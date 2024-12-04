@extends('layouts.static')

@section('title', 'Crear Venta')

@section('h1', 'Crear Venta')
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
    En esta vista, se agrega una venta a la tabla ventas y detalles_venta.
    <h5>Por completar:</h5>
    <strong>Modal de crear cliente:</strong> que al registrar nuevo cliente, se seleccione
    automáticamente para la venta actual. <br>
    <strong>Modal de agregar detalle:</strong> que al llenar los datos en modal, y dar clic en agregar, se agregue
    a la tabla de la vista actual, <strong>NO A LA BD</strong> todavia, es solo para llenar el formulario de la
    creación de venta y sus detalles. <br>
    <strong>Botón Registrar Venta:</strong> que al dar clic, registrar la venta ahora si en la BD, registrar también
    sus detalles, que fueron almacenados en la tabla de la vista, ahora si se almacenarán en la tabla oficial de
    detalles_venta y ventas.
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
                        <tr>
                            <td>
                                <input type="hidden" class="cuenta" value="${cuenta}">
                                ${cuenta}
                            </td>
                            <td>
                                <input type="hidden" class="perfil" value="${perfil}">
                                ${perfil}
                            </td>
                            <td>
                                <input type="hidden" class="descripcion" value="${descripcion}">
                                ${descripcion}
                            </td>
                            <td>
                                <input type="hidden" class="fecha-vencimiento" value="${fechaVencimiento}">
                                ${fechaVencimiento}
                            </td>
                            <td>
                                <input type="hidden" class="monto" value="${monto}">
                                $${monto.toFixed(2)}
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm eliminarDetalleBtn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>                        
                    </tbody>
                </table>

                <div class="text-end">
                    <strong>Total Venta: $<span id="total-venta">0.00</span></strong>
                </div>
            </div>
            <!-- Campo oculto para enviar los detalles de la venta -->
            <input type="hidden" name="detalles_venta" id="detalles_venta">
            {{-- Campo oculto para enviar la información del empleado que registró la venta (CON LOGIN)--}}
            <input type="hidden" name="idemp" id="idemp" value=1>
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
                    <form action="{{ route('clientes.store2') }}" method="POST">
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
@endsection
@section('pie')
    <p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
    <a href="{{ route('ventas') }}" class="btn btn-secondary">Volver a Ventas</a>
@endsection

@section('scripts')
    <script>
        // Inicializa Select2 en el select con el id 'idcli'
        $(document).ready(function() {
            $('#idcli').select2({
                placeholder: "Selecciona un Cliente",
                allowClear: true // Permite borrar la selección
            });
        });
    </script>
    {{-- script modal para cliente --}}
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
    {{-- script para agregar y eliminar detalles de la tabla --}}
    <script>
        // Función para agregar un detalle a la tabla
        $('#guardarDetalleBtn').on('click', function() {
            // Obtener los valores del modal
            var cuenta = $('#selectCuenta').val();
            var perfil = $('#selectPerfil').val();
            var fechaVencimiento = $('#fechaVencimiento').val();
            var descripcion = $('#descripcion').val();
            var monto = parseFloat($('#monto').val());

            // Validar que todos los campos estén completos
            if (cuenta && perfil && fechaVencimiento && descripcion && monto) {
                // Calcular el nuevo total
                var totalVenta = parseFloat($('#total-venta').text()) + monto;

                // Crear una nueva fila con los datos del detalle
                var nuevaFila = `<tr>
            <td>${cuenta}</td>
            <td>${perfil}</td>
            <td>${descripcion}</td>
            <td>${fechaVencimiento}</td>
            <td>$${monto.toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm eliminarDetalleBtn">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;

                // Agregar la nueva fila a la tabla
                $('#tabla-detalles').append(nuevaFila);

                // Actualizar el total de la venta
                $('#total-venta').text(totalVenta.toFixed(2));

                // Limpiar los campos del modal
                $('#selectCuenta').val('');
                $('#selectPerfil').val('');
                $('#fechaVencimiento').val('');
                $('#descripcion').val('');
                $('#monto').val('');

                // Cerrar el modal
                $('#agregarDetalleModal').modal('hide');
            } else {
                alert('Por favor complete todos los campos.');
            }
        });

        // Eliminar fila de la tabla
        $('#tabla-detalles').on('click', '.eliminarDetalleBtn', function() {
            // Obtener el monto de la fila a eliminar
            var montoEliminado = parseFloat($(this).closest('tr').find('td').eq(5).text().replace('$', ''));

            // Restar el monto eliminado del total
            var totalVenta = parseFloat($('#total-venta').text()) - montoEliminado;

            // Eliminar la fila
            $(this).closest('tr').remove();

            // Actualizar el total de la venta
            $('#total-venta').text(totalVenta.toFixed(2));
        });
    </script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 en el select de cuentas
            $('#selectCuent').select2({
                placeholder: 'Seleccione una cuenta',
                allowClear: true
            });
        });
    </script>
    {{-- script para enviar los detalles_venta --}}
    <script>
        document.getElementById('form-venta').addEventListener('submit', function(event) {
            event.preventDefault(); // Evitar que se envíe el formulario inmediatamente

            // Crear un arreglo para almacenar los detalles de venta
            let detalles = [];

            // Obtener todas las filas de la tabla
            document.querySelectorAll('#tabla-detalles tr').forEach(function(row) {
                let cuenta = row.querySelector('.cuenta').value;
                let perfil = row.querySelector('.perfil').value;
                let descripcion = row.querySelector('.descripcion').value;
                let fechaVencimiento = row.querySelector('.fecha-vencimiento').value;
                let monto = row.querySelector('.monto').value;

                console.log(cuenta, perfil, descripcion, fechaVencimiento, monto); // Verifica si se están seleccionando correctamente
                // Agregar cada detalle al arreglo
                detalles.push({
                    cuenta: cuenta,
                    perfil: perfil,
                    descripcion: descripcion,
                    fecha_vencimiento: fechaVencimiento,
                    monto: monto
                });
            });

            console.log("Detalles a enviar: ", detalles);
            // Asignar los detalles al campo oculto
            document.getElementById('detalles_venta').value = JSON.stringify(detalles);

            // Ahora enviamos el formulario
            this.submit();
        });
    </script>
@endsection
