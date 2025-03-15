@extends('layouts.static')

@section('title', 'Actualizar Usuario')

@section('h1', 'Actualizar Usuario')
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    <a href="{{ route('usuarios') }}">Usuarios Activos</a>
@endsection
@section('breadcrumb3')
    <li class="breadcrumb-item active">Actualizar Usuario</li>
@endsection
@section('introduccion')
    Actualiza los datos de este usuario. Por favor, revisa cuidadosamente todos los campos antes de guardar los cambios.
@endsection

@section('content')
    <form action="{{ route('usuarios.update', $usuario->iddet) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Campo para el Nombre del Cliente (solo lectura) -->
        <div class="form-group mb-3">
            <label for="nombre_cliente">Nombre del Cliente</label>
            <input type="text" name="nombrecli" id="nombrecli" class="form-control" value="{{ $usuario->nombre_cliente }}"
                readonly>
        </div>

        <!-- Campo para el ID de Venta (solo lectura) -->
        <div class="form-group mb-3">
            <label for="idven">ID de Venta</label>
            <input type="text" name="idven" id="idven" class="form-control" value="{{ $usuario->idven }}" readonly>
        </div>
        {{-- Campo para el ID de detalle 
    <input type="hidden" name="iddet" id="iddet" value="{{ $usuario->iddet}}"> --}}

        <!-- Campo para seleccionar una Cuenta -->
        <div class="form-group mb-3">
            <label for="idcue">Cuenta</label>
            <select name="idcue" id="idcuek" class=" idcue form-control" required>
                @foreach ($cuentas as $cuenta)
                    <option value="{{ $cuenta->idcue }}" {{ $usuario->idcue == $cuenta->idcue ? 'selected' : '' }}>
                        {{ $cuenta->idcue }}: Oc: {{ $cuenta->usuarios_activos }} ::
                        @foreach ($cuenta->perfiles as $perfil)
                            P{{ $perfil->numeroper }}: {{ $perfil->usuarios_activos }}&nbsp;&nbsp;
                        @endforeach
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Campo para el Número de Perfil -->
        <div class="form-group mb-3">
            <label for="perfil">Número de Perfil</label>
            <input type="number" name="perfil" id="perfil" class="form-control" value="{{ $usuario->perfil }}" min=1
                max=7 required>
        </div>

        <!-- Campo para la Fecha de Vencimiento -->
        <div class="form-group mb-3">
            <label for="fecha_vencimiento">Fecha de Vencimiento</label>
            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control"
                value="{{ $usuario->fecha_vencimiento }}" required>
        </div>

        <!-- Botón para cambiar el usuario -->
        <button type="submit" class="btn btn-primary">Cambiar Usuario</button>
    </form>
@endsection

@section('pie')
    <p>¿No deseas realizar cambios? Regresa al listado de usuarios:</p>
    <a href="{{ route('usuarios') }}" class="btn btn-secondary">Volver a Usuarios</a>
@endsection
