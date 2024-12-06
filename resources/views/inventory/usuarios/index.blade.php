@extends('layouts.table')
@section('title')
    Usuarios
@endsection
@section('h1')
    Usuarios
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h3>Revisa la actividad de todos los clientes y su fecha de vencimiento</h3>
    <p>A esta pantalla acceden ciertos usuarios</p>
    <p>Muestra vista de todos los usuarios y sus fecha de caducidad para controlar actividad, un crud</p>
    <h4>Realizado por Pablo Jiménez</h4>
@endsection
@section('tablename', 'Usuarios')
@section('btncrear')
    <a href="{{ route('ventas.create') }}" class="btn btn-primary">Nueva Venta</a>
@endsection
@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>ID Cuenta</th>
                <th>Perfil</th>
                <th>Fecha de vencimiento</th>
                <th>Estado</th>
                <th>Acciones</th>
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
                    <td>
                        <a href="{{ route('usuarios.change', $usuario->iddet) }}" class="btn btn-warning  "><i
                                class="fas fa-exchange-alt"></i></a>
                        @if ($diasRestantes <= 3)
                            <a href="{{ route('ventas.renew', $usuario->idven) }}" class="btn btn-success">
                                {{--  --}}
                                <i class="fas fa-sync-alt"></i>
                            </a>
                            <form action="{{ route('usuarios.destroy', $usuario->iddet) }}" method="POST"
                                style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-circle"
                                    onclick="return confirm('¿Eliminar este usuario?')"><i
                                        class="fas fa-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No hay usuarios activos disponibles.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
