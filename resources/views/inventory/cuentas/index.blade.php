@extends('layouts.table')

@section('title', 'Cuentas')

@section('h1', 'Cuentas')

@section('descripcion')
    <h3>Revisa las cuentas activas del <strong>Negocio</strong></h3>
    <p>Aquí podrás gestionar las cuentas de usuario asociadas a los servicios de streaming pertenecientes a Streamify HQ.
    </p>
    <h3>Tabla de Cuentas</h3>
    <p>En esta tabla puedes ver las cuentas, editar o eliminarlas según sea necesario. Cada cuenta está asociada a un valor
        (plan de servicio) específico.</p>
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
                <th>Fecha Vencimiento</th>
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
                    $diasRestantes = $fechaVencimiento->diffInDays($hoy, false);

                    // Determinar la clase CSS para la fila
                    if ($cuenta->caidacue) {
                        // Cuenta dañada (morado)
                        $estadoClase = 'table-dark'; // Clase personalizada para morado
                    } elseif ($diasRestantes < 0) {
                        // Cuenta vencida (rojo)
                        $estadoClase = 'table-danger';
                    } elseif ($diasRestantes <= 3) {
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
                    <td>
                        @if ($cuenta->caidacue)
                            <span style="badge bg-dark">Dañada</span>
                        @elseif ($diasRestantes < 0)
                            <span class="badge bg-danger">Vencida</span>
                        @elseif ($diasRestantes <= 5)
                            <span class="badge bg-warning">Ya vence</span>
                        @else
                            <span class="badge bg-success">Activa</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('cuentas.edit', $cuenta->idcue) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('cuentas.destroy', $cuenta->idcue) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
