@extends('layouts.table')
@section('title')
    Servicios
@endsection
@section('h1')
    Servicios
@endsection
@section('breadcrumb')
    Servicios
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Muestra tabla de todos los servicios y sus precios para la venta.</p>
@endsection
@section('tablename', 'Servicios')
@section('btncrear')
    @if (Auth::user()->hasPermissionTo('servicios.create'))
        <a href="{{ route('servicios.create') }}" class="btn btn-primary">Crear Servicio</a>
    @endif
@endsection
@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>PVP completo</th>
                <th>PVP individual</th>
                <th>PVP combo</th>
                <th>Reventa 1 pant</th>
                <th>Reventa 1 cuent</th>
                @if (Auth::user()->hasAnyPermission(['servicios.edit', 'servicios.destroy']))
                    <th>Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($servicios as $servicio)
                <tr>
                    <td>{{ $servicio->idser }}</td>
                    <td>{{ $servicio->nombreser }}</td>
                    <td>${{ number_format($servicio->completoser, 2) }}</td>
                    <td>${{ number_format($servicio->precioser, 2) }}</td>
                    <td>${{ number_format($servicio->comboser, 2) }}</td>
                    <td>${{ number_format($servicio->reventaser, 2) }}</td>
                    <td>${{ number_format($servicio->revcompser, 2) }}</td>
                    @if (Auth::user()->hasAnyPermission(['servicios.edit', 'servicios.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('servicios.edit'))
                                <a href="{{ route('servicios.edit', $servicio->idser) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if (Auth::user()->hasPermissionTo('servicios.destroy'))
                                <form action="{{ route('servicios.destroy', $servicio->idser) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle"
                                        onclick="return confirm('¿Eliminar este servicio?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No hay servicios disponibles.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializa DataTables
            $('#datatablesSimple').DataTable();
        });
    </script>
@endsection