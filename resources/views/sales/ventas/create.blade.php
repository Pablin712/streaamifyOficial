@extends('layouts.static')

@section('title', 'Crear Venta')

@section('h1', 'Crear Venta')
@section('introduccion')
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
            <select name="idcli" id="idcli" class="form-control select2">
                <option value="">-- Selecciona un Cliente --</option>
                @foreach ($clientes as $cliente)
                    <option value="{{ $cliente->idcli }}">{{ $cliente->nombrecli }}</option>
                @endforeach
            </select>
        </div>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">
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
                        <th>Descripción</th>
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

        <button type="submit" class="btn btn-primary mt-4" id="registrar-venta">Registrar Venta</button>
    </form>
</div>
<br>

<!-- Modal Nuevo Cliente -->
<div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevoClienteModalLabel">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="nuevo-cliente-form">
                    <div class="form-group">
                        <label for="nombrecli">Nombre Cliente</label>
                        <input type="text" id="nombrecli" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefonocli" class="form-control" required>
                    </div>
                    <button type="button" class="btn btn-primary mt-3" id="guardar-cliente">Guardar Cliente</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Detalle -->
<div class="modal fade" id="agregarDetalleModal" tabindex="-1" aria-labelledby="agregarDetalleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarDetalleModalLabel">Agregar Detalle de Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-detalle">
                    <div class="form-group">
                        <label for="descripciondet">Descripción</label>
                        <input type="text" id="descripciondet" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="montodet">Monto</label>
                        <input type="number" id="montodet" class="form-control" required>
                    </div>
                    <button type="button" class="btn btn-success mt-3" id="agregar-detalle">Agregar Detalle</button>
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
    // Variables para manejar los detalles y el total
    let detallesVenta = [];
    let totalVenta = 0;

    // Función para agregar un detalle a la tabla
    document.getElementById('agregar-detalle').addEventListener('click', function() {
        const descripcion = document.getElementById('descripciondet').value;
        const monto = parseFloat(document.getElementById('montodet').value);

        if (descripcion && !isNaN(monto) && monto > 0) {
            // Agregar detalle al arreglo
            detallesVenta.push({ descripcion, monto });
            totalVenta += monto;

            // Actualizar la tabla
            const row = `<tr>
                            <td>${descripcion}</td>
                            <td>$${monto.toFixed(2)}</td>
                            <td><button class="btn btn-danger btn-sm" onclick="eliminarDetalle(this)">Eliminar</button></td>
                          </tr>`;
            document.getElementById('tabla-detalles').innerHTML += row;

            // Actualizar total
            document.getElementById('total-venta').textContent = totalVenta.toFixed(2);

            // Limpiar el formulario del modal
            document.getElementById('form-detalle').reset();
            // Cerrar modal
            bootstrap.Modal.getInstance(document.getElementById('agregarDetalleModal')).hide();
        } else {
            alert("Por favor, ingresa una descripción y monto válidos.");
        }
    });

    // Función para eliminar un detalle
    function eliminarDetalle(button) {
        const row = button.closest('tr');
        const monto = parseFloat(row.children[1].textContent.replace('$', ''));
        
        // Eliminar del arreglo y actualizar el total
        detallesVenta = detallesVenta.filter(detalle => detalle.monto !== monto);
        totalVenta -= monto;

        // Actualizar la tabla y el total
        row.remove();
        document.getElementById('total-venta').textContent = totalVenta.toFixed(2);
    }

    // Función para guardar un nuevo cliente (aún no implementado backend)
    document.getElementById('guardar-cliente').addEventListener('click', function() {
        const nombre = document.getElementById('nombrecli').value;
        const correo = document.getElementById('correo').value;
        const telefono = document.getElementById('telefono').value;

        if (nombre && correo && telefono) {
            // Aquí agregaríamos una llamada AJAX o un formulario real para guardar el cliente
            alert("Nuevo cliente agregado: " + nombre);
            bootstrap.Modal.getInstance(document.getElementById('nuevoClienteModal')).hide();
        } else {
            alert("Por favor, completa todos los campos.");
        }
    });

    // Enviar el formulario de venta al registrar la venta
    document.getElementById('form-venta').addEventListener('submit', function(event) {
        event.preventDefault(); // Evita que se envíe el formulario antes de completar la venta

        // Aquí procesarías el envío de la venta con los detalles y el cliente
        alert("Venta registrada con éxito. (Esta parte aún debe conectarse al backend)");
    });
</script>
@endsection
