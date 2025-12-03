@extends('layouts.static')
@section('title', 'Renovar Venta')

@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Select2 Dark Mode -->
    <link href="{{ asset('css/select2-dark-mode.css') }}" rel="stylesheet" />
@endsection

@section('h1', 'Renovar Venta')
@section('breadcrumb')
    <a href="{{ route('ventas') }}">Ventas</a>
@endsection
@section('breadcrumb2')
    Renovar Venta
@endsection
@section('content')
    <div class="container">
        <!-- Alerta Informativa -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Recordatorio:</strong> Esta renovación no se guardará hasta que presiones el botón
            <strong>"Registrar Venta"</strong> al final de la página.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

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
                <button type="button" class="btn btn-success"
                    onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'agregar-detalle-modal' }))">
                    <i class="fas fa-plus-circle me-1"></i> Agregar Detalle
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
                                        data-monto="{{ $detalle->montodet }}" data-id="{{ $detalle->iddet }}">
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

    @include('shared.modals.venta-agregar-detalle')
    @include('shared.modals.venta-editar-detalle')
@endsection
@section('pie')
    <p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
    <a href="{{ route('ventas') }}" class="btn btn-secondary">Volver a Ventas</a>
@endsection

@section('scripts')
    <!-- jQuery (debe cargarse primero) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 (debe cargarse después de jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Inicializador de searchable-selects -->
    <script src="{{asset('js/searchable-select.js')}}"></script>

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
            $('#editarSelectCuenta').val(cuenta);
            $('#editarSelectPerfil').val(perfil);
            $('#editarDescripcion').val(descripcion);
            $('#editarFechaVencimiento').val(fechaVencimiento);
            $('#editarMonto').val(monto);

            // Mostrar el modal usando Alpine.js
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editar-detalle-modal' }));
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

                // Cerrar el modal usando Alpine.js
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editar-detalle-modal' }));
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

                // Cerrar el modal usando Alpine.js
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'agregar-detalle-modal' }));
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
