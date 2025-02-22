@extends('layouts.static')

@section('h1', 'Perfil')
@section('breadcrumb')
    Perfil
@endsection
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="container">
        <h1>Actualizar Datos Personales</h1>
        <form action="{{ route('empleados.update', Auth::user()->idemp) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3 text-center">
                <label for="foto_url" class="form-label d-block">Foto de Perfil</label>
                <input type="file" name="foto_url" id="foto_url" class="form-control">
                @if (Auth::user()->foto_url)
                    <div class="mt-2">
                        <img src="{{ asset('public/storage/' . Auth::user()->foto_url) }}" 
                            alt="Foto de {{ Auth::user()->nombreemp }}" 
                            class="img-fluid rounded-circle"
                            style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label for="nombreemp" class="form-label">Nombre</label>
                <input type="text" name="nombreemp" id="nombreemp" class="form-control" 
                    value="{{ Auth::user()->nombreemp }}" required>
            </div>

            <div class="mb-3">
                <label for="telefonoemp" class="form-label">Teléfono</label>
                <input type="text" name="telefonoemp" id="telefonoemp" class="form-control" 
                    value="{{ Auth::user()->telefonoemp }}" required>
            </div>

            <div class="mb-3">
                <label for="usuarioemp" class="form-label">Usuario</label>
                <input type="text" name="usuarioemp" id="usuarioemp" class="form-control" 
                    value="{{ Auth::user()->usuarioemp }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" 
                    value="{{ Auth::user()->email }}" required>
            </div>

            <div class="mb-3">
                <label for="passwordemp" class="form-label">Nueva Contraseña (opcional)</label>
                <input type="password" name="passwordemp" id="passwordemp" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
    </div>
@endsection
