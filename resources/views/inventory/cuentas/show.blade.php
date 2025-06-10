@extends('layouts.static')
@section('h1', 'Perfiles de la Cuenta')
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    Perfiles de {{ $cuenta->usuariocue }}
@endsection
@section('introduccion')
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
    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif
    @if (session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <p>En esta sección puedes ver los perfiles asociados a la cuenta de suscripción
        <strong>{{ $cuenta->usuariocue }}</strong>.
        Puedes editar el PIN de cada perfil o ver los datos de acceso de la cuenta.
    </p>
    <div class="row">
        @if (Auth::user()->hasPermissionTo('usuarios.change'))
            <form action="{{ route('cuentas.moverClientes') }}" method="POST" class="mb-3"
                onsubmit="return confirm('¿Estás seguro de mover TODOS los clientes de esta cuenta a la Mesa de Trabajo? Esta acción no se puede deshacer.');">
                @csrf
                <input type="hidden" name="cuenta_origen" value="{{ $cuenta->idcue }}">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-random"></i> Mover todos los clientes a Mesa de Trabajo
                </button>
            </form>
        @endif
        @if (Auth::user()->hasPermissionTo('usuarios.change'))
            <form action="{{ route('cuentas.moverClientesDisperso') }}" method="POST" class="mb-3"
                onsubmit="return confirm('¿Estás seguro de mover TODOS los clientes de esta cuenta a otras cuentas disponibles? Esta acción no se puede deshacer.');">
                @csrf
                <input type="hidden" name="cuenta_origen" value="{{ $cuenta->idcue }}">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-random"></i> Mover todos los clientes a otro lugar
                </button>
            </form>
        @endif
    </div>
@endsection
@section('content')
    <div class="container">
        <table class="table">
            <thead>
                <tr>
                    <th>Número de Perfil</th>
                    <th>PIN del Perfil</th>
                    <th>Num Usuarios</th>
                    <th>Usuarios Activos</th>
                    @if (Auth::user()->hasAnyPermission(['cuentas.mensaje', 'perfil.update']))
                        <th>Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($perfiles as $perfil)
                    <tr>
                        <td>{{ $perfil->numeroper }}</td>
                        <td>{{ $perfil->pinper }}</td>
                        <td class="usuarios-activos">
                            <span
                                class="{{ $perfil->usuarios_activos == 0 ? 'badge bg-danger' : ($perfil->usuarios_activos == 1 ? 'badge bg-success' : 'badge bg-dark') }}">
                                {{ $perfil->usuarios_activos }}
                            </span>
                        </td>
                        <td>
                            @if ($perfil->usuarios_activos == 0)
                                <span class="badge-success">Libre</span>
                            @else
                                @foreach ($perfil->usuarios as $usuario)
                                    @php
                                        $fechaVencimiento = \Carbon\Carbon::parse($usuario->fecha_vencimiento);
                                        $hoy = \Carbon\Carbon::today();
                                        $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
                                    @endphp
                                    @if ($diasRestantes <= 0)
                                        <span class="badge bg-danger">{{ $usuario->nombre_cliente }} (Vencido)</span>
                                        @if (Auth::user()->hasPermissionTo('usuarios.change'))
                                            <form action="{{ route('usuarios.moverUsuario', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-dark btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario?')">
                                                    <i class="fas fa-exchange"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('usuarios.moverUsuarioMesa', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-info btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario a la mesa de trabajo?')">
                                                    <i class="fas fa-arrow-right-to-bracket"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if (Auth::user()->hasPermissionTo('ventas.renew'))
                                            <a href="{{ route('ventas.renew', ['idcli' => $usuario->idcli, 'idven' => $usuario->idven]) }}"
                                                class="btn btn-success btn-sm">
                                                <i class="fas fa-sync-alt"></i>
                                            </a>
                                        @endif
                                        @if (Auth::user()->hasPermissionTo('usuarios.destroy'))
                                            <form action="{{ route('usuarios.destroy', $usuario->iddet) }}" method="POST"
                                                style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-circle btn-sm"
                                                    onclick="return confirm('¿Eliminar este usuario?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <br>
                                    @elseif ($diasRestantes <= 3)
                                        <span class="badge bg-warning">{{ $usuario->nombre_cliente }}
                                            {{ $usuario->fecha_vencimiento }}</span>
                                        @if (Auth::user()->hasPermissionTo('usuarios.change'))
                                            <form action="{{ route('usuarios.moverUsuario', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-dark btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario?')">
                                                    <i class="fas fa-exchange"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('usuarios.moverUsuarioMesa', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-info btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario a la mesa de trabajo?')">
                                                    <i class="fas fa-arrow-right-to-bracket"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if (Auth::user()->hasPermissionTo('ventas.renew'))
                                            <a href="{{ route('ventas.renew', ['idcli' => $usuario->idcli, 'idven' => $usuario->idven]) }}"
                                                class="btn btn-success btn-sm">
                                                <i class="fas fa-sync-alt"></i>
                                            </a>
                                        @endif
                                        <br>
                                    @else
                                        <span class="badge bg-success">{{ $usuario->nombre_cliente }}
                                            {{ $usuario->fecha_vencimiento }}</span>
                                        @if (Auth::user()->hasPermissionTo('usuarios.change'))
                                            <form action="{{ route('usuarios.moverUsuario', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-dark btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario?')">
                                                    <i class="fas fa-random"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('usuarios.moverUsuarioMesa', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-info btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario a la mesa de trabajo?')">
                                                    <i class="fas fa-arrow-right-to-bracket"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <br>
                                    @endif
                                @endforeach
                            @endif
                        </td>
                        @if (Auth::user()->hasAnyPermission(['cuentas.mensaje', 'perfil.update']))
                            <td>
                                @if (Auth::user()->hasPermissionTo('perfil.update'))
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editProfileModal"
                                        data-action="{{ route('perfil.update', $perfil->idper) }}"
                                        data-id="{{ $perfil->idper }}" data-pin="{{ $perfil->pinper }}">
                                        <i class="fas fa-edit">Editar</i>
                                    </button>
                                @endif
                                @if (Auth::user()->hasPermissionTo('cuentas.mensaje'))
                                    <button class="btn btn-success btn-sm"
                                        onclick="copyMessage('{{ $perfil->cuenta->idcue }}', '{{ $perfil->cuenta->usuariocue }}', '{{ $perfil->cuenta->contrasenacue }}', '{{ $perfil->numeroper }}', '{{ $perfil->pinper }}', '{{ $perfil->cuenta->valor->bot }}')">
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
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Editar PIN del Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm" method="POST">
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
    <script src="{{ asset('js/cuentas.js') }}?v={{ time() }}"></script>
@endsection
