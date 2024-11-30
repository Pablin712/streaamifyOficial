@extends('layouts.table')

@section('title', 'Cuentas')
@section('styles')
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
    </style>

@endsection
@section('h1', 'Cuentas')
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h3>Revisa las cuentas activas del <strong>Negocio</strong></h3>
    <p>Aquí podrás gestionar las cuentas de usuario asociadas a los servicios de streaming pertenecientes a Streamify HQ.
    </p>
    <h4>Realizado por Pablo Jiménez, terminado por Andrés Rincón</h4>
    <br>
    <h5>Por completar:</h5>
    <p>
        <strong>1. Botón renovar (verde en columna acciones):</strong> Que habra una vista y permita renovar cuenta
        (extender fecha de vencimiento, registrar costo). <br>
        <strong>2. Botón editar perfil:</strong> que habra un modal que permita cambiar el pin del perfil de la cuenta (Solo
        el pin y nada más del perfil). <br>
        <strong>3. Columna Usuarios activos en perfiles LISTO:</strong> que calcule los clientes que están ocupando ese
        perfil
        (vista y función ya hecha en sql de postgres). <br>
        <strong>4. Botón cambiar estado cuenta LISTO:</strong> que cambie el caidacue al darle clic (botón azul) <br>
        <strong>5. Botón ver mensaje (Ver perfil):</strong> este permite copiar, o ver los detalles del perfil
        para vender a un cliente, debe indicar un mensaje con el formato establecido para enviar por WhatsApp.
        <br><br>
        <strong>Nombre de servicio</strong><br>
        <strong>Usuario:</strong> usuariocue<br>
        <strong>Clave:</strong> contrasenacue<br>
        <strong>PIN de perfil numeroper:</strong> pinper
    </p>
@endsection

@section('btncrear')
    <a href="{{ route('cuentas.create') }}" class="btn btn-primary mb-3">Crear Cuenta</a>
@endsection

@section('tablename', 'Cuentas')

@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Servicio</th>
                <th>Usuario</th>
                <th>Vencimiento</th>
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
                @endphp
                <tr class="{{ $estadoClase }}">
                    <td>{{ $cuenta->idcue }}</td>
                    <td>{{ $cuenta->valor->idval }} ({{ $cuenta->valor->proveedor->nombrepro }})</td>
                    <td>{{ $cuenta->usuariocue }}</td>
                    <td>{{ $cuenta->fechavencue->format('d/m/Y') }}</td>
                    <td>{{ $cuenta->usuarios_activos }}</td>
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
                        <form action="{{ route('cuentas.status', $cuenta->idcue) }}" method="POST" style="display:inline;">
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
                        <a href="{{ route('cuentas.edit', $cuenta->idcue) }}" class="btn btn-warning  "><i
                                class="fas fa-edit"></i></a>
                        <!-- Botón de renovación: Solo visible si la cuenta está por vencer o vencida -->
                        @if ($diasRestantes <= 5 || $diasRestantes < 0)
                            <a href="#" class="btn btn-success"> {{-- {{ route('cuentas.renovar', $cuenta->idcue) }} --}}
                                <i class="fas fa-sync-alt"></i>
                            </a>
                        @endif
                        <form action="{{ route('cuentas.destroy', $cuenta->idcue) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-circle"
                                onclick="return confirm('¿Estás seguro?')"><i class="fas fa-trash"></i></button>
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
                <tbody>
                    @foreach ($perfiles as $perfil)
                        <tr>
                            <td>{{ $perfil->numeroper }}</td>
                            <td>{{ $perfil->pinper }}</td>
                            <td class="usuarios-activos">{{ $perfil->usuarios_activos }}</td>
                            <!-- AQUI TIENES QUE AGREGAR EL CALCULO PARA VER LOS USUARIOS ACTIVOS -->
                            <td>
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editProfileModal"
                                    data-id="{{ $perfil->id }}" data-pin="{{ $perfil->pinper }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- Botón para obtener o copiar el mensaje del perfil -->
                                <button class="btn btn-success btn-sm" onclick="copyMessage('{{ $perfil->id }}')">
                                    <i class="fas fa-eye"></i> Ver mensaje
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
                    <form id="editProfileForm" method="POST" action="{{ route('perfil.update', ':id') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="perfilId" name="perfilId">
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
    <input type="text" id="mensajeParaCopiar" style="position: absolute; left: -9999px;">
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
        // Esto se ejecutará cuando se haga clic en el botón "Editar"
        $('#editProfileModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // El botón que activó el modal
            var perfilId = button.data('id'); // Obtener el ID del perfil
            var pinper = button.data('pin'); // Obtener el PIN del perfil

            var modal = $(this);
            modal.find('#perfilId').val(perfilId); // Asignar el ID al campo oculto
            modal.find('#pinper').val(pinper); // Asignar el PIN al campo de texto
            // Actualizar la URL del formulario para apuntar al perfil correcto
            var formAction = "{{ route('perfil.update', ':id') }}".replace(':id', perfilId);
            modal.find('#editProfileForm').attr('action', formAction);
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
    
@endsection
