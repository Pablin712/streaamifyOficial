@extends('layouts.table')

@section('title', 'Cuentas')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Personalizando el fondo oscuro de las filas de la tabla a morado */
        .table-dark {
            background-color: #800080 !important; /* Color morado */
            color: white !important;
        }
        /* Personalizando el badge bg-dark a morado */
        .badge.bg-dark {
            background-color: #800080 !important; /* Color morado */
            color: white !important;
        }
        .badge.bg-dark:hover {
            background-color: #6a006a !important;
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
@section('breadcrumb')
    Cuentas
@endsection
@section('descripcion')
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
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
    @if (Auth::user()->hasPermissionTo('cuentas.create'))
        <a href="{{ route('cuentas.create') }}" class="btn btn-primary mb-3">Crear Cuenta</a>
    @endif
    @if (Auth::user()->hasPermissionTo('valores.create'))
        <a href="{{ route('valores.create') }}" class="btn btn-primary mb-3">Crear Valor</a>
    @endif
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
                @if (Auth::user()->hasAnyPermission(['cuentas.edit', 'cuentas.renew', 'cuentas.destroy']))
                    <th>Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($cuentas as $cuenta)
                @php
                    // Convertir la fecha de vencimiento a Carbon
                    $fechaVencimiento = \Carbon\Carbon::parse($cuenta->fechavencue);
                    $hoy = \Carbon\Carbon::today();
                    $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
                @endphp
                <tr>
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
                        @if (Auth::user()->hasPermissionTo('cuentas.status'))
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
                        @endif
                    </td>
                    @if (Auth::user()->hasAnyPermission(['cuentas.edit', 'cuentas.renew', 'cuentas.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('cuentas.edit'))
                                <a href="{{ route('cuentas.edit', $cuenta->idcue) }}" class="btn btn-warning btn-xs">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            <!-- Botón de renovación: Solo visible si la cuenta está por vencer o vencida -->
                            @if ($diasRestantes <= 5 || $diasRestantes < 0)
                                @if (Auth::user()->hasPermissionTo('cuentas.renew'))
                                    <a href="{{ route('cuentas.renew', $cuenta->idcue) }}" class="btn btn-success btn-xs">
                                        <i class="fas fa-sync-alt"></i>
                                    </a>
                                @endif
                            @endif
                            @if (Auth::user()->hasPermissionTo('cuentas.destroy'))
                                <form action="{{ route('cuentas.destroy', $cuenta->idcue) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs"
                                        onclick="return confirm('¿Estás seguro?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    @endif
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
            <table id="datatablesSimple" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Número de Perfil</th>
                        <th>PIN del Perfil</th>
                        <th>Usuarios Activos</th>
                        @if (Auth::user()->hasAnyPermission(['cuentas.mensaje', 'perfil.update']))
                            <th>Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="Perfil">
                    @foreach ($perfiles as $perfil)
                        <tr>
                            <td>{{ $perfil->numeroper }}</td>
                            <td>{{ $perfil->pinper }}</td>
                            <td class="usuarios-activos">
                                <span class="{{ $perfil->usuarios_activos == 0 ? 'badge bg-danger' : ($perfil->usuarios_activos == 1 ? 'badge bg-success' : 'badge bg-dark') }}">
                                    {{ $perfil->usuarios_activos }}
                                </span>
                            </td>
                            @if (Auth::user()->hasAnyPermission(['cuentas.mensaje', 'perfil.update']))
                                <td>
                                    @if (Auth::user()->hasPermissionTo('perfil.update'))
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editProfileModal" data-id="{{ $perfil->idper }}"
                                            data-pin="{{ $perfil->pinper }}">
                                            <i class="fas fa-edit">Editar</i>
                                        </button>
                                    @endif
                                    @if (Auth::user()->hasPermissionTo('cuentas.mensaje'))
                                        <button class="btn btn-success btn-sm"
                                            onclick="copyMessage('{{ $perfil->cuenta->idcue }}', '{{ $perfil->cuenta->usuariocue }}', '{{ $perfil->cuenta->contrasenacue }}', '{{ $perfil->numeroper }}', '{{ $perfil->pinper }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end"><strong>Total de Usuarios activos:</strong></td>
                        <td id="totalUsuariosActivos"><strong>0</strong></td>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#editProfileModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var perfilId = button.data('id');
                var pinper = button.data('pin');
                var modal = $(this);
                modal.find('#perfilId').val(perfilId);
                modal.find('#pinper').val(pinper);
                var formAction = "{{ url('admin/perfil') }}/" + perfilId;
                modal.find('#editProfileForm').attr('action', formAction);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var totalUsuarios = 0;
            var usuariosActivos = document.querySelectorAll('.usuarios-activos');
            usuariosActivos.forEach(function(item) {
                totalUsuarios += parseInt(item.textContent) || 0;
            });
            document.getElementById('totalUsuariosActivos').textContent = totalUsuarios;
        });
    </script>
    <script>
        function copyMessage(idcue, usuariocue, contrasenacue, numeroper, pinper) {
            var servicio = idcue.replace(/[^a-zA-Z]/g, '');
            var message = servicio + "\n";
            message += "Usuario: " + usuariocue + "\n";
            message += "Clave: " + contrasenacue + "\n";
            message += "PIN de perfil Nro " + numeroper + ": " + pinper;
            var tempTextArea = document.createElement("textarea");
            tempTextArea.value = message;
            document.body.appendChild(tempTextArea);
            tempTextArea.select();
            document.execCommand("copy");
            document.body.removeChild(tempTextArea);
            alert("El mensaje se ha copiado al portapapeles.");
        }
    </script>
    @if (session('focus'))
        <script>
            document.getElementById("{{ session('focus') }}").focus();
        </script>
    @endif
    <script>
        $(document).ready(function() {
            $('#idcue').select2({
                placeholder: "Selecciona una Cuenta",
                allowClear: true
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
@endsection