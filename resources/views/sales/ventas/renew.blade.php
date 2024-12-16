@extends('layouts.static')
@section('title', 'Renovar Venta')
@section('h1', 'Renovar Venta')
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
        <input type="hidden" name="idemp" id="idemp" value=1>
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
@endsection
@section('pie')
<p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
<a href="{{ route('ventas') }}" class="btn btn-secondary">Volver a Ventas</a>
@endsection

@section('scripts')
</script>
{{-- script para agregar y eliminar detalles de la tabla --}}
<script>
    // Función para agregar un detalle a la tabla
$('#guardarDetalleBtn').on('click', function () {
    var cuenta = $('#selectCuenta').val();
    var perfil = $('#selectPerfil').val();
    var fechaVencimiento = $('#fechaVencimiento').val();
    var descripcion = $('#descripcion').val();
    var monto = parseFloat($('#monto').val());

    if (cuenta && perfil && fechaVencimiento && descripcion && monto) {
        var totalVenta = parseFloat($('#total-venta').text()) + monto;

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

        $('#tabla-detalles').append(nuevaFila);

        $('#total-venta').text(totalVenta.toFixed(2));

        $('#selectCuenta').val('');
        $('#selectPerfil').val('');
        $('#fechaVencimiento').val('');
        $('#descripcion').val('');
        $('#monto').val('');

        $('#agregarDetalleModal').modal('hide');
    } else {
        alert('Por favor complete todos los campos.');
    }
});
// Eliminar fila de la tabla
$('#tabla-detalles').on('click', '.eliminarDetalleBtn', function () {
    var montoEliminado = parseFloat(
        $(this).closest('tr').find('td').eq(4).text().replace('$', '').trim()
    );

    var totalVenta = parseFloat($('#total-venta').text()) - montoEliminado;

    $(this).closest('tr').remove();

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

        // Obtener todas las filas de la tabla #tabla-detalles (cada fila es un detalle de venta)
        document.querySelectorAll('#tabla-detalles tr').forEach(function(row) {
            // Obtener los valores de cada celda de la fila
            let cuenta = row.cells[0].innerText; // La primera celda es la Cuenta
            let perfil = row.cells[1].innerText; // La segunda celda es el Perfil
            let descripcion = row.cells[2].innerText; // La tercera celda es la Descripción
            let fechaVencimiento = row.cells[3].innerText; // La cuarta celda es la Fecha de Vencimiento
            let monto = parseFloat(row.cells[4].innerText.replace('$', '')
                .trim()); // La quinta celda es el Monto

            // Asegurarse de que los campos no estén vacíos (esto es opcional, según tu caso)
            if (cuenta && perfil && descripcion && fechaVencimiento && monto) {
                // Agregar cada detalle al arreglo
                detalles.push({
                    cuenta: cuenta,
                    perfil: perfil,
                    descripcion: descripcion,
                    fecha_vencimiento: fechaVencimiento,
                    monto: monto
                });
            }
        });

        // Asignar los detalles serializados al campo oculto para enviarlos en el formulario
        document.getElementById('detalles_venta').value = JSON.stringify(detalles);

        // Ahora enviamos el formulario
        this.submit();
    });
</script>
@endsection