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
            <label for="idcue">Seleccionar Cliente</label>
            
            <form method="GET" action="{{ route('cuentas') }}#tabla-perfiles">
                <select name="idcli" id="idcli" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Selecciona un Cliente --</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->idcli }}" {{ request('idcli') == $cliente->idcli ? 'selected' : '' }}>
                            {{ $cliente->nombrecli }} - {{ $cliente->telefonocli }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
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

<!-- Modal para agregar un nuevo cliente -->
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" role="dialog" aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoClienteLabel">Nuevo Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formNuevoCliente">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombrecli">Nombre del Cliente</label>
                        <input type="text" class="form-control" id="nombrecli" required>
                    </div>
                    <div class="form-group">
                        <label for="telefonocli">Teléfono</label>
                        <input type="text" class="form-control" id="telefonocli" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" form="editClienteForm" class="btn btn-primary">Registrar Cliente</button>
                </div>
            </form>
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
    // Inicializa Select2 en el select con el id 'idcli'
    $(document).ready(function() {
        $('#idcli').select2({
            placeholder: "Selecciona un Cliente",
            allowClear: true  // Permite borrar la selección
        });
    });
</script>
{{-- script modal para cliente --}}
<script>
    $('#modalNuevoCliente').on('shown.bs.modal', function(event) {
        var button = $(event.relatedTarget); // El botón que activó el modal
        var clienteId = button.data('id'); // Obtener el ID del perfil
        var pinper = button.data('pin'); // Obtener el PIN del perfil

        var modal = $(this);
        modal.find('#clienteId').val(clienteId); // Asignar el ID al campo oculto
        modal.find('#pinper').val(pinper); // Asignar el PIN al campo de texto
        // Actualizar la URL del formulario para apuntar al perfil correcto
        var formAction = "{{ route('ventas.storeCliente', ':id') }}".replace(':id', clienteId);
        modal.find('#editClienteForm').attr('action', formAction); // Asignar la URL correcta al formulario
    });
</script>

@endsection
