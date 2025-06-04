@extends('layouts.table')
@section('title')
    Valores
@endsection
@section('h1')
    Valores de servicios
@endsection
@section('breadcrumb')
    Valores
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Revisa el inventario y crea nuevos posibles contratos para luego agregarlas a stock.</p>
    <form action="{{ route('valores.updatePantallas') }}" method="POST">
        @csrf
        <div class="row">
            @foreach ($serviciosPrincipales as $servicio)
                <div class="col-md-3 mb-3">
                    <div class="card border-{{ $servicio->color }} shadow-sm">
                        <div class="card-body text-center">
                            @if (Str::startsWith($servicio->icon, 'fa-'))
                                <i class="fab {{ $servicio->icon }} fa-2x text-gray-300"></i>
                            @else
                                <img src="{{ asset('images/' . $servicio->icon) }}" width="40" height="40"
                                    alt="{{ $servicio->nombreser }}">
                            @endif

                            <h6 class="card-title mt-2">{{ $servicio->nombreser }}</h6>

                            <div class="row">
                                <div class="col-6">
                                    <label for="pantmin_{{ $servicio->idser }}" class="form-label small">Pant Min</label>
                                    <input type="number" step="1" class="form-control form-control-sm"
                                        id="pantmin_{{ $servicio->idser }}"
                                        name="pantallas[{{ $servicio->idser }}][pantmin]" required min="1">
                                </div>
                                <div class="col-6">
                                    <label for="pantmax_{{ $servicio->idser }}" class="form-label small">Pant Max</label>
                                    <input type="number" step="1" class="form-control form-control-sm"
                                        id="pantmax_{{ $servicio->idser }}"
                                        name="pantallas[{{ $servicio->idser }}][pantmax]" required min="1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mb-3">
            <button type="submit" class="btn btn-primary btn-sm">Guardar Cambios</button>
        </div>
    </form>
@endsection
@section('btncrear')
    @if (Auth::user()->hasPermissionTo('valores.create'))
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            {{-- Crear Valor --}}
            <a href="{{ route('valores.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Crear Valor
            </a>

            {{-- Corregir idval --}}
            <form action="{{ route('valores.corregir') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-tools"></i> Corregir ID de Valores
                </button>
            </form>

            {{-- Borrar innecesarios --}}
            <form action="{{ route('valores.deletegroup') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Borrar Innecesarios
                </button>
            </form>

            {{-- Descargar PDF --}}
            <a href="{{ route('valores.pdf') }}" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF - {{ \Carbon\Carbon::now()->format('Y-m-d') }}
            </a>

            {{-- Nuevo Servicio --}}
            @if (Auth::user()->hasPermissionTo('servicios.create'))
                <a href="{{ route('servicios.create') }}" class="btn btn-info text-white">
                    <i class="fas fa-plus-circle"></i> Nuevo Servicio
                </a>
            @endif

            {{-- Nuevo Proveedor --}}
            @if (Auth::user()->hasPermissionTo('proveedores.create'))
                <a href="{{ route('proveedores.create') }}" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i> Nuevo Proveedor
                </a>
            @endif
        </div>
    @endif
@endsection
@section('tablename', 'Valores')
@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Servicio</th>
                <th>Proveedor</th>
                <th>Costo</th>
                <th>Tipo</th>
                <th>Min</th>
                <th>Max</th>
                <th>Meses</th>
                <th>Bot de códigos</th>
                <th>Num cuentas</th>
                @if (Auth::user()->hasAnyPermission(['valores.edit', 'valores.destroy']))
                    <th>Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($valores as $valor)
                <tr>
                    <td>{{ $valor->idval }}</td>
                    <td>{{ $valor->idser }}</td>
                    <td>{{ $valor->proveedor->nombrepro }}</td>
                    <td>${{ number_format($valor->costoval, 2) }}</td>
                    <td>{{ $valor->tipoval }}</td>
                    <td>{{ $valor->pantminval }}</td>
                    <td>{{ $valor->pantmaxval }}</td>
                    <td>{{ $valor->mesesval }}</td>
                    <td>
                        @if (!empty($valor->bot))
                            <a href="{{ $valor->bot }}" target="_blank" class="text-primary">Ver Bot</a>
                        @else
                            <span class="text-danger">No disponible</span>
                        @endif
                    </td>
                    <td><span class="badge bg-success">{{ $valor->num_cuentas }}</span></td>
                    @if (Auth::user()->hasAnyPermission(['valores.edit', 'valores.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('valores.edit'))
                                <a href="{{ route('valores.edit', $valor->idval) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if (Auth::user()->hasPermissionTo('valores.destroy'))
                                <form action="{{ route('valores.destroy', $valor->idval) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle"
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
