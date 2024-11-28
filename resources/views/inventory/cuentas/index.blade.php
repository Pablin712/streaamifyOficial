@extends('layouts.table')

@section('title', 'Cuentas')

@section('h1', 'Cuentas')

@section('descripcion')
    <h3>Revisa las cuentas activas del <strong>Negocio</strong></h3>
    <p>Aquí podrás gestionar las cuentas de usuario asociadas a los servicios de streaming pertenecientes a Streamify HQ.
    </p>
    <h4>Realizado por Pablo Jiménez, terminado por Andrés Rincón</h4>
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
                        <a href="{{ route('cuentas.edit', $cuenta->idcue) }}" class="btn btn-warning  " ><i class="fas fa-edit"></i></a>
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
                        <option value="{{ $cuenta->idcue }}" {{ request('idcue') == $cuenta->idcue ? 'selected' : '' }}>
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
        example: <table id="datatablesSimple">--}}
        <table id="datatablesSimple" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Número de Perfil</th>
                    <th>PIN del Perfil</th>
                    <th>Número de Personas Ocupando</th>
                    <th>Editar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($perfiles as $perfil)
                    <tr>
                        <td>{{ $perfil->numeroper }}</td>
                        <td>{{ $perfil->pinper }}</td>
                        <td>1</td> <!-- Cada perfil cuenta como 1 ocupante -->
                        <td>
                            {{-- comment <a href="{{ route('perfil.edit', $perfil->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                        --}} editar
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-end"><strong>Total de Personas:</strong></td>
                    <td><strong>{{ $perfiles->count() }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (window.location.href.includes('#tabla-perfiles')) {
                document.getElementById('tabla-perfiles').scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>
@endsection