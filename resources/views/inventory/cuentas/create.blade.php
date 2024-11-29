@extends('layouts.static')

@section('title', 'Crear Cuenta')

@section('h1', 'Crear Cuenta')

@section('introduccion')
    Aquí puedes agregar una nueva cuenta para llenar el stock de cuentas disponibles para los servicios.
    Asegúrate de ingresar todos los campos correctamente.
    En esta vista, se agrega una cuenta a la tabla cuentas.
    <h5>Por completar:</h5>
    <strong>input costo y descripcion costo: </strong> que al registrar la nueva cuenta, se pueda registrar de una vez el costo, 
    solo con descripcion costo y el monto que se pagó, y se registra tanto la cuenta como el costo, esto puede ser solucionado
    con modal, inputs con sentencia. <Strong>Nota:</Strong> El registro del costo es voluntario, por lo que se puede registrar
    una cuenta sin necesidad de registrar el costo, solo la cuenta.
@endsection

@section('content')
    <form action="{{ route('cuentas.store') }}" method="POST">
        @csrf

        <!-- Campo para el ID de la cuenta -->
        <div class="form-group mb-3">
            <label for="idcue">ID de Cuenta</label>
            <input type="text" name="idcue" id="idcue" class="form-control" maxlength="20" required>
        </div>

        <!-- Selección del Valor -->
        <div class="form-group mb-3">
            <label for="idval">ID del Valor</label>
            <select name="idval" id="idval" class="form-control" required>
                @foreach ($valores as $valor)
                    <option value="{{ $valor->idval }}">{{ $valor->idval }} - {{ $valor->idser }}
                        ({{ $valor->proveedor->nombrepro }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Campo para el nombre de usuario de la cuenta -->
        <div class="form-group mb-3">
            <label for="usuariocue">Usuario</label>
            <input type="text" name="usuariocue" id="usuariocue" class="form-control" required>
        </div>

        <!-- Campo para la contraseña de la cuenta -->
        <div class="form-group mb-3">
            <label for="contrasenacue">Contraseña</label>
            <input type="password" name="contrasenacue" id="contrasenacue" class="form-control" required>
        </div>

        <!-- Fecha de vencimiento de la cuenta -->
        <div class="form-group mb-3">
            <label for="fechavencue">Fecha de Vencimiento</label>
            <input type="date" name="fechavencue" id="fechavencue" class="form-control" required>
        </div>

        <!-- Campo para indicar si la cuenta está activa -->
        <div class="form-group mb-3">
            <label for="caidacue">¿Cuenta Activa?</label>
            <select name="caidacue" id="caidacue" class="form-control" required>
                <option value="0">Sí</option>
                <option value="1">No</option>
            </select>
        </div>

        <!-- Botón para abrir el modal de Costo -->
        <button type="button" class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#seleccionarCuentaModal">
            Agregar Costo
        </button>
        <!-- Mostrar datos de costo si se ingresaron en el modal -->
        <div class="form-group mb-3">
            <label for="descripcioncos">Descripción del Costo</label>
            <p id="descripcioncos" class="form-control">
                @if (session('descripcioncos'))
                    {{ session('descripcioncos') }}
                @else
                    No se ha registrado un costo.
                @endif
            </p>
        </div>
        <div class="form-group mb-3">
            <label for="montocos">Monto del Costo</label>
            <p id="montocos" class="form-control">
                @if (session('montocos'))
                    ${{ number_format(session('montocos'), 2) }}
                @else
                    No se ha registrado un costo.
                @endif
            </p>
        </div>


        <button type="submit" class="btn btn-success">Guardar Cuenta</button>
    </form>
    <!-- Modal para crear un nuevo costo -->
    <div class="modal fade" id="seleccionarCuentaModal" tabindex="-1" aria-labelledby="seleccionarCuentaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="seleccionarCuentaModalLabel">Registrar Costo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('costos.store') }}" method="POST">
                        @csrf
                        <!-- Campos del Costo -->
                        <div class="form-group mb-3">
                            <label for="descripcioncos">Descripción</label>
                            <input type="text" name="descripcioncos" id="descripcioncos" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="montocos">Monto</label>
                            <input type="number" name="montocos" id="montocos" class="form-control" step="0.01"
                                required>
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


@endsection


@section('pie')
    <p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
    <a href="{{ route('cuentas') }}" class="btn btn-secondary">Volver a Cuentas</a>
@endsection 

<script>
    document.getElementById('seleccionarCuentaModal').addEventListener('show.bs.modal', function(event) {
        // Obtener el botón que abrió el modal
        var button = event.relatedTarget;

        // Limpiar los campos del formulario en el modal
        document.getElementById('descripcioncos').value = '';
        document.getElementById('montocos').value = '';
    });

    // Cuando se guarda el costo en el modal
    document.getElementById('modal-costos-form').addEventListener('submit', function(event) {
        event.preventDefault(); // Evitar el submit para validación antes

        // Obtener los valores de los campos del modal
        var descripcioncos = document.getElementById('descripcioncos').value;
        var montocos = document.getElementById('montocos').value;

        // Si hay valores, llenar los campos en el formulario
        if (descripcioncos && montocos) {
            document.getElementById('descripcioncos_show').value = descripcioncos;
            document.getElementById('montocos_show').value = montocos;

            // También podemos agregar los valores al formulario para enviarlos
            var form = document.getElementById('modal-costos-form');
            var hiddenInputDesc = document.createElement('input');
            hiddenInputDesc.type = 'hidden';
            hiddenInputDesc.name = 'descripcioncos';
            hiddenInputDesc.value = descripcioncos;
            form.appendChild(hiddenInputDesc);

            var hiddenInputMonto = document.createElement('input');
            hiddenInputMonto.type = 'hidden';
            hiddenInputMonto.name = 'montocos';
            hiddenInputMonto.value = montocos;
            form.appendChild(hiddenInputMonto);
        }

        // Finalmente, cerrar el modal
        var modal = new bootstrap.Modal(document.getElementById('seleccionarCuentaModal'));
        modal.hide();

        // Ahora podemos proceder con el submit real
        form.submit();
    });
</script>
