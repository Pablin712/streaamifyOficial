@extends('layouts.table')

@section('title', 'Costos')

@section('h1', 'Costos')

@section('descripcion')
    <h3>Gestión de Costos</h3>
    <h4>Iniciado por Pablo Jiménez, terminado por Andrés Rincón</h4>
    <p>Aquí puedes ver todos los costos asociados al negocio y registrar nuevos costos. Si deseas ver los costos de una cuenta específica, selecciona una cuenta en el modal.</p>
@endsection

@section('btncrear')
    <!-- Botón para abrir el modal de selección de cuentas -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#seleccionarCuentaModal">
        Crear Costo
    </button>
@endsection
@section('tablename', 'Costos')
@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cuenta</th>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Monto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($costos as $costo)
                <tr>
                    <td>{{ $costo->idcos }}</td>
                    <td>{{ $costo->cuenta->idcue }} - {{ $costo->cuenta->usuariocue }}</td>
                    <td>{{ \Carbon\Carbon::parse($costo->fechacos)->format('d/m/Y') }}</td>
                    <td>{{ $costo->descripcioncos }}</td>
                    <td>${{ number_format($costo->montocos, 2) }}</td>
                    <td>
                        <!-- Editar costo (abre el modal con los datos del costo) -->
                        <button type="button" class="btn btn-warning fas fa-edit" data-bs-toggle="modal" data-bs-target="#editarCostoModal" 
                            data-id="{{ $costo->idcos }}"
                            data-idcue="{{ $costo->idcue }}"
                            data-descripcioncos="{{ $costo->descripcioncos }}"
                            data-montocos="{{ $costo->montocos }}"
                            data-fechacos="{{ $costo->fechacos }}">
                            Editar
                        </button>
                        <!-- Eliminar costo -->
                        <form action="{{ route('costos.destroy', $costo->idcos) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger " onclick="return confirm('¿Estás seguro?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

<!-- Modal para crear un nuevo costo -->
<div class="modal fade" id="seleccionarCuentaModal" tabindex="-1" aria-labelledby="seleccionarCuentaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="seleccionarCuentaModalLabel">Seleccionar Cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('costos.store') }}" method="POST">
                    @csrf
                    <!-- Selector de Cuentas -->
                    <div class="form-group mb-3">
                        <label for="idcue">Seleccionar Cuenta</label>
                        <select name="idcue" id="idcue" class="form-control" required>
                            <option value="">-- Selecciona una Cuenta --</option>
                            @foreach ($cuentas as $cuenta)
                                <option value="{{ $cuenta->idcue }}">
                                    {{ $cuenta->idcue }} - {{ $cuenta->usuariocue }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Campos del Costo -->
                    <div class="form-group mb-3">
                        <label for="descripcioncos">Descripción</label>
                        <input type="text" name="descripcioncos" id="descripcioncos" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="montocos">Monto</label>
                        <input type="number" name="montocos" id="montocos" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="fechacos">Fecha</label>
                        <input type="date" name="fechacos" id="fechacos" class="form-control" value="{{ now()->format('Y-m-d') }}">
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
<!-- Modal para editar el costo -->
<div class="modal fade" id="editarCostoModal" tabindex="-1" aria-labelledby="editarCostoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarCostoModalLabel">Editar Costo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="editCostoForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group mb-3">
                        <label for="edit_idcue">Cuenta</label>
                        <!-- Campo solo lectura para la cuenta -->
                        <input type="text" id="edit_idcue" class="form-control" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_descripcioncos">Descripción</label>
                        <input type="text" name="descripcioncos" id="edit_descripcioncos" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_montocos">Monto</label>
                        <input type="number" name="montocos" id="edit_montocos" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_fechacos">Fecha</label>
                        <input type="date" name="fechacos" id="edit_fechacos" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Función para llenar el formulario del modal con los datos del costo a editar
    $('#editarCostoModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Botón que abrió el modal
        var idcos = button.data('id');
        var idcue = button.data('idcue'); // ID de la cuenta seleccionada
        var descripcioncos = button.data('descripcioncos');
        var montocos = button.data('montocos');
        var fechacos = button.data('fechacos');

        var modal = $(this);
        modal.find('#edit_idcue').val(idcue); // Mostrar el ID de la cuenta asociada (campo solo lectura)
        modal.find('#edit_descripcioncos').val(descripcioncos);
        modal.find('#edit_montocos').val(montocos);
        modal.find('#edit_fechacos').val(fechacos);

        // Cambiar la acción del formulario de edición a la ruta del costo específico
        var formAction = "{{ route('costos.update', '') }}/" + idcos;
        modal.find('#editCostoForm').attr('action', formAction);
    });
</script>
@endsection