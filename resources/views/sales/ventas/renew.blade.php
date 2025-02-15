@extends('layouts.static')
@section('title', 'Renovar Venta')
@section('h1', 'Renovar Venta')
@section('breadcrumb')
    <a href="{{ route('ventas') }}">Ventas</a>
@endsection
@section('breadcrumb2')
    Renovar Venta
@endsection
@section('content')
    <div class="container">
        <h2>Renovar Venta</h2>
        <form id="form-venta" method="POST" action="{{ route('ventas.storeRenew') }}">
            @csrf
            <div class="form-group mb-3">

                <div class="form-group mb-3">
                    <label for="idcli">Cliente</label>
                    <input type="text" class="form-control" value="{{ $venta->cliente->nombrecli }}" readonly>
                    <!-- Campo oculto para enviar el idcli al servidor -->
                    <input type="hidden" name="idcli" id="idcli" value="{{ $venta->cliente->idcli }}">
                </div>
                <input type="hidden" name="idvenPasado" value="{{ $venta->idven }}">

            </div>

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
                        @foreach ($detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->perfil->cuenta->idcue }}</td>
                                <td>{{ $detalle->perfil->numeroper }}</td>
                                <td>Renovacion Cuenta</td>
                                <td>{{ $detalle->fechavendet_suma }}</td>
                                <td>${{ number_format($detalle->montodet, 2) }}</td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm editarDetalleBtn"
                                        data-cuenta="{{ $detalle->perfil->cuenta->idcue }}" 
                                        data-perfil="{{ $detalle->perfil->numeroper }}"
                                        data-descripcion="Renovacion Cuenta"
                                        data-fechavencimiento="{{ $detalle->fechavendet_suma }}"
                                        data-monto="{{ $detalle->montodet }}"
                                        data-id="{{ $detalle->iddet }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editarDetalleModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm eliminarDetalleBtn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        <!-- Los detalles se agregarán aquí mediante JavaScript -->
                    </tbody>
                </table>

                <div class="text-end">
                    <strong>Total Venta: $<span id="total-venta">{{ number_format($totalVenta, 2) }}</span></strong>
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
                    <h5 class="modal-title" id="editarDetalleModalLabel">Editar Detalle de la Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarDetalle">
                        <!-- Cuenta -->
                        <div class="mb-3">
                            <label for="editarCuenta" class="form-label">Cuenta</label>
                            <input type="text" class="form-control" id="editarCuenta" readonly>
                        </div>

                        <!-- Perfil -->
                        <div class="mb-3">
                            <label for="editarPerfil" class="form-label">Perfil</label>
                            <input type="text" class="form-control" id="editarPerfil" readonly>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="editarDescripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="editarDescripcion" rows="2" required></textarea>
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

                        <button type="button" class="btn btn-primary" id="guardarCambiosDetalleBtn">Guardar
                            Cambios</button>
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
        let detalleEditando = null; // Variable para rastrear la fila en edición

        // Abrir el modal de edición y cargar los datos
        $(document).on('click', '.editarDetalleBtn', function() {
            const cuenta = $(this).closest('tr').find('td').eq(0).text();
            const perfil = $(this).closest('tr').find('td').eq(1).text();
            const descripcion = $(this).closest('tr').find('td').eq(2).text();
            const fechaVencimiento = $(this).closest('tr').find('td').eq(3).text();
            const monto = parseFloat($(this).closest('tr').find('td').eq(4).text().replace('$', '').trim());

            // Guardar la referencia de la fila que se está editando
            detalleEditando = $(this).closest('tr');

            // Cargar los valores en el modal
            $('#editarCuenta').val(cuenta);
            $('#editarPerfil').val(perfil);
            $('#editarDescripcion').val(descripcion);
            $('#editarFechaVencimiento').val(fechaVencimiento);
            $('#editarMonto').val(monto);

            // Mostrar el modal
            $('#editarDetalleModal').modal('show');
        });

        // Guardar cambios en el detalle editado
        $('#guardarCambiosDetalleBtn').on('click', function() {
            if (!detalleEditando) return; // Si no hay detalle cargado, salir

            // Obtener los valores del modal
            const descripcion = $('#editarDescripcion').val();
            const fechaVencimiento = $('#editarFechaVencimiento').val();
            const monto = parseFloat($('#editarMonto').val());

            // Validar los campos
            if (descripcion && fechaVencimiento && !isNaN(monto)) {
                // Actualizar los datos en la fila
                detalleEditando.find('td').eq(2).text(descripcion);
                detalleEditando.find('td').eq(3).text(fechaVencimiento);
                detalleEditando.find('td').eq(4).text(`$${monto.toFixed(2)}`);

                // Actualizar el total de la venta
                actualizarTotalVenta();

                // Cerrar el modal
                $('#editarDetalleModal').modal('hide');
            } else {
                alert('Por favor completa todos los campos correctamente.');
            }
        });

        // Agregar un detalle nuevo
        $('#guardarDetalleBtn').on('click', function() {
            const cuenta = $('#selectCuenta').val();
            const perfil = $('#selectPerfil').val();
            const fechaVencimiento = $('#fechaVencimiento').val();
            const descripcion = $('#descripcion').val();
            const monto = parseFloat($('#monto').val());

            if (cuenta && perfil && fechaVencimiento && descripcion && monto) {
                const nuevaFila = `<tr>
                <td>${cuenta}</td>
                <td>${perfil}</td>
                <td>${descripcion}</td>
                <td>${fechaVencimiento}</td>
                <td>$${monto.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-warning btn-sm editarDetalleBtn">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm eliminarDetalleBtn">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;

                $('#tabla-detalles').append(nuevaFila);

                // Actualizar el total de la venta
                actualizarTotalVenta();

                // Limpiar los campos del modal de agregar
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

        // Eliminar un detalle
        $(document).on('click', '.eliminarDetalleBtn', function() {
            const montoEliminado = parseFloat(
                $(this).closest('tr').find('td').eq(4).text().replace('$', '').trim()
            );

            $(this).closest('tr').remove();

            // Actualizar el total de la venta
            actualizarTotalVenta();
        });

        // Función para calcular y actualizar el total de la venta
        function actualizarTotalVenta() {
            let total = 0;
            $('#tabla-detalles tr').each(function() {
                const monto = parseFloat($(this).find('td').eq(4).text().replace('$', '').trim());
                if (!isNaN(monto)) {
                    total += monto;
                }
            });
            $('#total-venta').text(total.toFixed(2));
        }

        // Enviar los detalles al backend
        $('#form-venta').on('submit', function(event) {
            const detalles = [];
            $('#tabla-detalles tr').each(function() {
                const cuenta = $(this).find('td').eq(0).text();
                const perfil = $(this).find('td').eq(1).text();
                const descripcion = $(this).find('td').eq(2).text();
                const fechaVencimiento = $(this).find('td').eq(3).text();
                const monto = parseFloat($(this).find('td').eq(4).text().replace('$', '').trim());

                if (cuenta && perfil && descripcion && fechaVencimiento && monto) {
                    detalles.push({
                        cuenta,
                        perfil,
                        descripcion,
                        fecha_vencimiento: fechaVencimiento,
                        monto,
                    });
                }
            });

            $('#detalles_venta').val(JSON.stringify(detalles));
            this.submit();
        });
    </script>
@endsection
