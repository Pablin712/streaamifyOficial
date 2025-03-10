@extends('layouts.static')

@section('title', 'Crear Mantenimiento')

@section('h1', 'Crear Mantenimiento')
@section('breadcrumb')
    <a href="{{ route('mantenimientos') }}">Mantenimientos</a>
@endsection
@section('breadcrumb2')
    Registrar Mantenimiento
@endsection
@section('introduccion')
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    En esta vista puedes agregar un nuevo mantenimiento para una cuenta existente. Asegúrate de ingresar todos los campos correctamente.
@endsection

@section('content')
    <form action="{{ route('mantenimientos.store') }}" method="POST">
        @csrf

        <!-- Selección del ID de Cuenta (idcue) -->
        <div class="form-group mb-3">
            <label for="idcue">ID de Cuenta</label>
            <select name="idcue" id="idcue" class="form-control" required>
                <option value="">Seleccione una cuenta</option>
                @foreach ($cuentas as $cuenta)
                    <option value="{{ $cuenta->idcue }}">{{ $cuenta->idcue }} - {{ $cuenta->usuariocue }}</option>
                @endforeach
            </select>
        </div>

        <!-- Campo para la fecha de mantenimiento (fechaman) -->
        <div class="form-group mb-3">
            <label for="fechaman">Fecha de Mantenimiento</label>
            <input type="date" name="fechaman" id="fechaman" class="form-control" required>
        </div>

        <!-- Campo para la descripción del mantenimiento -->
        <div class="form-group mb-3">
            <label for="descripcionman">Descripción del Mantenimiento</label>
            <textarea name="descripcionman" id="descripcionman" class="form-control" required></textarea>
        </div>

        <!-- Botón para guardar el mantenimiento -->
        <button type="submit" class="btn btn-success">Guardar Mantenimiento</button>
    </form>
@endsection

@section('pie')
    <p>¿No deseas agregar un mantenimiento? Vuelve a la página de listado de mantenimientos:</p>
    <a href="{{ route('mantenimientos') }}" class="btn btn-secondary">Volver a Mantenimientos</a>
@endsection
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializar Select2 en el select de cuentas
        $('#idcue').select2({
            placeholder: 'Seleccione una cuenta',
            allowClear: true
        });
    });
</script>
@endsection