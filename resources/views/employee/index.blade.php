@extends('layouts.table')

@section('title', 'Empleados')
@section('h1', 'Empleados')
@section('breadcrumb', 'Empleados')

@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
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
    <h3>Información de Empleados</h3>
@endsection

@section('table1')
    <a href="{{ route('empleados.create') }}" class="btn btn-primary mb-3">Crear Empleado</a>
    <div class="row">
        @foreach ($empleados as $empleado)
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="{{ $empleado->foto_url ? asset('public/storage/' . $empleado->foto_url) : 'https://via.placeholder.com/100/007bff/ffffff?text=Usuario' }}"
                            alt="Foto de {{ $empleado->nombreemp }}" class="img-fluid rounded-circle mb-3"
                            style="width: 100px; height: 100px; object-fit: cover;">
                        <h5 class="card-title">{{ $empleado->nombreemp }}</h5>
                        <p class="card-text">
                            <strong>Teléfono:</strong> {{ $empleado->telefonoemp }}<br>
                            <strong>Usuario:</strong> {{ $empleado->usuarioemp }}<br>
                            <strong>Rol:</strong> {{ $empleado->idrol }}<br>
                            <strong>Ventas este mes:</strong> {{ $empleado->ventas_mes_actual }}<br>
                            <strong>Total de Ventas:</strong> {{ $empleado->ventas_count }}<br>
                            <strong>Correo:</strong> {{ $empleado->email }}<br>
                        </p>

                        @if (Auth::user()->idrol == 'administrador')
                            <button class="btn btn-warning" data-bs-toggle="modal"
                                data-bs-target="#editModal{{ $empleado->idemp }}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Modal de Edición -->
            <div class="modal fade" id="editModal{{ $empleado->idemp }}" tabindex="-1"
                aria-labelledby="editModalLabel{{ $empleado->idemp }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $empleado->idemp }}">Editar Empleado</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <form action="{{ route('empleados.updateRol', $empleado->idemp) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="idrol" class="form-label">Rol</label>
                                    <select name="idrol" id="idrol" class="form-select" required>
                                        @foreach ($roles as $rol)
                                            <option value="{{ $rol->idrol }}"
                                                {{ $empleado->idrol === $rol->idrol ? 'selected' : '' }}>
                                                {{ ucfirst($rol->idrol) }}: 
                                                {{ ucfirst($rol->detallerol) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
