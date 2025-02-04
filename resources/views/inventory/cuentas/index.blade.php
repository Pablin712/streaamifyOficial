@extends('layouts.table')

@section('title', 'Cuentas')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Personalizando el fondo oscuro de las filas de la tabla a morado */
        .table-dark {
            background-color: #800080 !important;
            /* Color morado */
            color: white !important;
            /* Texto blanco para contraste */
        }

        /* Personalizando el badge bg-dark a morado */
        .badge.bg-dark {
            background-color: #800080 !important;
            /* Color morado */
            color: white !important;
            /* Texto blanco para el badge */
        }

        .badge.bg-dark:hover {
            background-color: #6a006a !important;
            /* Color morado más oscuro en hover */
        }

        .btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1;
            border-radius: 0.2rem;
        }
    </style>
@endsection
@section('h1', 'Cuentas')
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Revisa las cuentas activas del <strong>Negocio</strong>. Aquí podrás gestionar las cuentas de usuario
        asociadas a los servicios de streaming pertenecientes a Streamify HQ.
    </p>
@endsection

@section('btncrear')
    <a href="{{ route('cuentas.create') }}" class="btn btn-primary mb-3">Crear Cuenta</a>
    <a href="{{ route('valores.create') }}" class="btn btn-primary mb-3">Crear Valor</a>
@endsection

@section('tablename', 'Cuentas')

@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Servicio</th>
                <th>Usuario</th>
                <th>Clave</th>
                <th>Vence</th>
                <th>Clientes</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cuentas as $cuenta)
                @php
                    // Convertir la fecha de vencimiento a Carbon
                    $fechaVencimiento = \Carbon\Carbon::parse($cuenta->fechavencue);
                    $hoy = \Carbon\Carbon::today();
                    $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);

                    // Determinar la clase CSS para la fila
                    /*
                    if ($cuenta->caidacue) {
                        // Cuenta dañada (morado)
                        $estadoClase = 'table-dark'; // Clase personalizada para morado
                    } elseif ($diasRestantes < 0) {
                        // Cuenta vencida (rojo)
                        $estadoClase = 'table-danger';
                    } elseif ($diasRestantes <= 5) {
                        // Cuenta por vencer (amarillo)
                        $estadoClase = 'table-warning';
                    } else {
                        // Cuenta activa (verde)
                        $estadoClase = 'table-success';
                    }
                    */

                @endphp
                <tr> {{-- class="{{ $estadoClase }}" --}}
                    <td>{{ $cuenta->idcue }}</td>
                    <td>{{ $cuenta->valor->idser }}-{{ $cuenta->valor->proveedor->nombrepro }}</td>
                    <td>{{ $cuenta->usuariocue }}</td>
                    <td>{{ $cuenta->contrasenacue }}</td>
                    <td>{{ $cuenta->fechavencue->format('Y/m/d') }}</td>
                    <td>
                        @php
                            $users = $cuenta->usuarios_activos;
                        @endphp
                        @if ($cuenta->valor->pantmaxval < $users)
                            <span class="badge bg-dark">{{ $users }}</span>
                        @elseif ($cuenta->valor->pantminval > $users)
                            <span class="badge bg-danger">{{ $users }}</span>
                        @else
                            <span class="badge bg-success">{{ $users }}</span>
                        @endif
                    </td>
                    {{-- <td>
                        @php
                            $pantmaxval = $cuenta->valor->pantmaxval;
                            $usuarios_activos = $cuenta->usuarios_activos;
                            $resta = $pantmaxval - $usuarios_activos;
                        @endphp
                        {{ $resta }}
                    </td> --}}
                    <td>
                        @if ($cuenta->caidacue)
                            <span class="badge bg-dark">Dañada</span>
                        @elseif ($diasRestantes <= 0)
                            <span class="badge bg-danger">Vencida</span>
                        @elseif ($diasRestantes <= 5)
                            <span class="badge bg-warning">Ya vence</span>
                        @else
                            <span class="badge bg-success">Activa</span>
                        @endif
                        <!-- Botón para cambiar estado -->
                        <form action="{{ route('cuentas.status', $cuenta->idcue) }}" method="POST"
                            style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-dark btn-sm">
                                @if ($cuenta->caidacue)
                                    <i class="fas fa-toggle-on fa-xs"></i>
                                @else
                                    <i class="fas fa-toggle-off fa-xs"></i>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td>
                        <a href="{{ route('cuentas.edit', $cuenta->idcue) }}" class="btn btn-warning btn-xs"><i
                                class="fas fa-edit"></i></a>
                        <!-- Botón de renovación: Solo visible si la cuenta está por vencer o vencida -->
                        @if ($diasRestantes <= 5 || $diasRestantes < 0)
                            <a href="{{ route('cuentas.renew', $cuenta->idcue) }}" class="btn btn-success btn-xs">
                                {{--  --}}
                                <i class="fas fa-sync-alt"></i>
                            </a>
                        @endif
                        <form action="{{ route('cuentas.destroy', $cuenta->idcue) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs"
                                onclick="return confirm('¿Estás seguro?')"><i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
@section('table2')
    <div class="card mb-4">
        <div class="card-body">
            Busca los perfiles de una cuenta específica.
            <div class="form-group mb-3">
                <label for="idcue">Seleccionar Cuenta</label>

                <form method="GET" action="{{ route('cuentas') }}#tabla-perfiles">
                    <select name="idcue" id="idcue" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Selecciona una Cuenta --</option>
                        @foreach ($cuentas as $cuenta)
                            <option value="{{ $cuenta->idcue }}"
                                {{ request('idcue') == $cuenta->idcue ? 'selected' : '' }}>
                                {{ $cuenta->idcue }} - {{ $cuenta->usuariocue }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>


    <div id="tabla-perfiles" class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Perfiles de {{ $idcueSeleccionado }}
        </div>
        <div class="card-body">
            {{-- aqui empieza la tabla, se modifica, en cualquier tabla
        se debe poner con style id="datatablesSimple"
        example: <table id="datatablesSimple"> --}}
            <table id="datatablesSimple" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Número de Perfil</th>
                        <th>PIN del Perfil</th>
                        <th>Usuarios Activos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="Perfil">
                    @foreach ($perfiles as $perfil)
                        <tr>
                            <td>{{ $perfil->numeroper }}</td>
                            <td>{{ $perfil->pinper }}</td>
                            <td class="usuarios-activos">
                                <span
                                    class="
                                {{ $perfil->usuarios_activos == 0
                                    ? 'badge bg-danger'
                                    : ($perfil->usuarios_activos == 1
                                        ? 'badge bg-success'
                                        : 'badge bg-dark') }}">
                                    {{ $perfil->usuarios_activos }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#editProfileModal" data-id="{{ $perfil->idper }}"
                                    data-pin="{{ $perfil->pinper }}">
                                    <i class="fas fa-edit">Editar</i>
                                </button>
                                <!-- Botón para obtener o copiar el mensaje del perfil -->
                                <button class="btn btn-success btn-sm"
                                    onclick="copyMessage('{{ $perfil->cuenta->idcue }}', '{{ $perfil->cuenta->usuariocue }}', '{{ $perfil->cuenta->contrasenacue }}', '{{ $perfil->numeroper }}', '{{ $perfil->pinper }}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end"><strong>Total de Usuarios activos:</strong></td>
                        <td id="totalUsuariosActivos"><strong>0</strong></td> <!-- Aquí se mostrará la suma -->
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- Modal para editar el PIN del perfil -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Editar PIN del Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="perfilId" name="idper">
                        <div class="form-group">
                            <label for="pinper">Nuevo PIN</label>
                            <input type="text" class="form-control" id="pinper" name="pinper" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="editProfileForm" class="btn btn-primary">Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Aquí puedes agregar un área oculta donde se almacenará el mensaje para copiarlo -->
@endsection
@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (window.location.href.includes('#tabla-perfiles')) {
                document.getElementById('tabla-perfiles').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    </script>
    {{-- este script es para el modal de editar perfil que aun no funciona --}}
    <script>
        $('#editProfileModal').on('shown.bs.modal', function(event) {
            var button = $(event.relatedTarget); // El botón que activó el modal
            var perfilId = button.data('id'); // Obtener el ID del perfil
            var pinper = button.data('pin'); // Obtener el PIN del perfil

            var modal = $(this);
            modal.find('#perfilId').val(perfilId); // Asignar el ID al campo oculto
            modal.find('#pinper').val(pinper); // Asignar el PIN al campo de texto
            // Actualizar la URL del formulario para apuntar al perfil correcto
            var formAction = "{{ route('perfil.update', ':id') }}".replace(':id', perfilId);
            modal.find('#editProfileForm').attr('action', formAction); // Asignar la URL correcta al formulario
        });
    </script>

    {{-- script para sumar los usuarios activos de la tabla perfiles --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sumar los valores de la columna "Usuarios Activos"
            var totalUsuarios = 0;

            // Obtener todos los valores de la columna "Usuarios Activos"
            var usuariosActivos = document.querySelectorAll('.usuarios-activos');

            // Recorrer todos los valores y sumarlos
            usuariosActivos.forEach(function(item) {
                totalUsuarios += parseInt(item.textContent) || 0;
            });

            // Mostrar el total en el pie de la tabla
            document.getElementById('totalUsuariosActivos').textContent = totalUsuarios;
        });
    </script>
    {{-- script para copiar un mensaje al portapapeles --}}
    <script>
        function copyMessage(idcue, usuariocue, contrasenacue, numeroper, pinper) {
            // 1. Limpiar el `idcue` para que solo contenga letras
            var servicio = idcue.replace(/[^a-zA-Z]/g, ''); // Eliminar todo lo que no sea letra

            // 2. Crear el mensaje con saltos de línea explícitos
            var message = servicio + "\n"; // Usar el `idcue` limpio y un salto de línea extra
            message += "Usuario: " + usuariocue + "\n"; // Usar el `usuariocue`
            message += "Clave: " + contrasenacue + "\n"; // Usar el `contrasenacue`
            message += "PIN de perfil Nro " + numeroper + ": " + pinper; // Usar el `numeroper`

            // 3. Crear un área de texto temporal para copiar el mensaje
            var tempTextArea = document.createElement("textarea");
            tempTextArea.value = message; // Establecer el valor como el mensaje completo
            document.body.appendChild(tempTextArea);

            // 4. Seleccionar y copiar el texto al portapapeles
            tempTextArea.select();
            document.execCommand("copy");

            // 5. Eliminar el área de texto temporal
            document.body.removeChild(tempTextArea);

            // 6. Avisar al usuario que el mensaje se ha copiado
            alert("El mensaje se ha copiado al portapapeles.");
        }
    </script>
    @if (session('focus'))
        <script>
            document.getElementById("{{ session('focus') }}").focus();
        </script>
    @endif
    <script>
        // Inicializa Select2 en el select con el id 'idcue'
        $(document).ready(function() {
            $('#idcue').select2({
                placeholder: "Selecciona una Cuenta",
                allowClear: true // Permite borrar la selección
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
@endsection
