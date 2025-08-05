@extends('layouts.table')
@section('title', 'Spotify')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('h1', 'Cuentas de Spotify')
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    Cuentas de Spotify
@endsection
@section('descripcion')
    <p>Revisa las cuentas de Spotify activas del <strong>Negocio</strong>. Aquí podrás gestionar las cuentas de usuario
        asociadas a los servicios de streaming pertenecientes a Streamify HQ.</p>
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
@endsection
@section('tablename', 'Cuentas de Spotify')
@section('table1')
    <table id="datatablesSimple" class="datatable table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Perfil</th>
                <th>Datos</th>
                <th>Vence Plan</th>
                <th>Usuario</th>
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
                @foreach ($cuenta->perfiles as $perfil)
                    <tr>
                        <td>{{ $perfil->idcue }}</td>
                        <td>
                            @if ($perfil->numeroper == 1)
                                <span class="badge bg-success">Admin</span>
                            @else
                                {{ $perfil->numeroper }}
                            @endif
                        </td>
                        <td>
                            @if ($perfil->numeroper == 1)
                                {{ $cuenta->usuariocue }} {{ $cuenta->contrasenacue }}
                            @else
                                {{ $perfil->pinper }}
                            @endif
                        </td>
                        <td>{{ $cuenta->fechavencue }}</td>
                        <td>
                            @if ($perfil->num_usuarios == 0)
                                Libre
                            @else
                                @foreach ($perfil->usuarios as $usuario)
                                    @php
                                        $fechaVencimientoPerfil = \Carbon\Carbon::parse($usuario->fecha_vencimiento);
                                        $hoy = \Carbon\Carbon::today();
                                        $diasRestantes = $hoy->diffInDays($fechaVencimientoPerfil, false);
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
            @endforeach
        </tbody>
    </table>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const tables = document.querySelectorAll(
                    '.datatable'); // Selecciona todas las tablas con la clase 'datatable'

                tables.forEach((table) => {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0) {
                        new simpleDatatables.DataTable(table, {
                            searchable: true,
                            perPageSelect: [5, 10, 20],
                            labels: {
                                placeholder: "Buscar...",
                                perPage: "Registros por página",
                                noRows: "No se encontraron registros.",
                                info: "Mostrando {start} a {end} de {rows} registros",
                            },
                        });
                    } else {
                        console.warn('La tabla sigue sin filas después del tiempo de espera.');
                    }
                });
            }, 500);
        });
    </script>
    <script src="{{ asset('js/cuentas.js') }}?v={{ time() }}"></script>
@endsection
