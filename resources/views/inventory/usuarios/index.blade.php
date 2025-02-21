@extends('layouts.table')
@section('title')
    Usuarios
@endsection
@section('h1')
    Usuarios
@endsection
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    Usuarios Activos
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Muestra vista de todos los usuarios y sus fecha de caducidad para controlar actividad.</p>
@endsection
@section('tablename', 'Usuarios')
@section('btncrear')
    @if (Auth::user()->hasPermissionTo('ventas.create'))
        <a href="{{ route('ventas.create') }}" class="btn btn-primary">Nueva Venta</a>
    @endif
@endsection
@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>ID Cuenta</th>
                <th>Usuario Cuenta</th>
                <th>Perfil</th>
                <th>Vencimiento</th>
                <th>Estado</th>
                @if (Auth::user()->hasAnyPermission(['usuarios.change', 'ventas.renew', 'usuarios.destroy']))
                    <th>Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($usuarios as $usuario)
                @php
                    $fechaVencimiento = \Carbon\Carbon::parse($usuario->fecha_vencimiento);
                    $hoy = \Carbon\Carbon::today();
                    $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
                @endphp
                <tr>
                    <td>{{ $usuario->nombre_cliente }}</td>
                    <td>{{ $usuario->idcue }}</td>
                    <td>{{ $usuario->cuenta->usuariocue }}</td>
                    <td>{{ $usuario->perfil }}</td>
                    <td>{{ $usuario->fecha_vencimiento }}</td>
                    <td>
                        @if ($diasRestantes <= 0)
                            <span class="badge bg-danger">Vencida</span>
                        @elseif ($diasRestantes <= 3)
                            <span class="badge bg-warning">Ya vence</span>
                        @else
                            <span class="badge bg-success">Activo</span>
                        @endif
                    </td>
                    @if (Auth::user()->hasAnyPermission(['usuarios.change', 'ventas.renew', 'usuarios.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('usuarios.change'))
                                <a href="{{ route('usuarios.change', $usuario->iddet) }}" class="btn btn-warning">
                                    <i class="fas fa-exchange-alt"></i>
                                </a>
                            @endif
                            @if ($diasRestantes <= 3)
                                @if (Auth::user()->hasPermissionTo('ventas.renew'))
                                    <a href="{{ route('ventas.renew', ['idcli' => $usuario->idcli, 'idven' => $usuario->idven]) }}"
                                        class="btn btn-success">
                                        <i class="fas fa-sync-alt"></i>
                                    </a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('usuarios.destroy'))
                                    <form action="{{ route('usuarios.destroy', $usuario->iddet) }}" method="POST"
                                        style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-circle"
                                            onclick="return confirm('¿Eliminar este usuario?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No hay usuarios activos disponibles.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection