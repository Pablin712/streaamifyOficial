@extends('layouts.cliente')

@section('title', 'Mi Perfil')

@section('header')
    <div class="container text-center my-4">
        <h1 class="fw-bold">Mi Perfil</h1>
        <p class="text-muted">Aquí puedes consultar y actualizar tu información personal en Streamify.</p>
    </div>
@endsection

@section('sections')
    <div class="container px-5 my-5">
        <div class="card shadow-lg border-0 rounded">
            <div class="card-body p-5">
                <h2 class="text-center fw-bold mb-4">Información del Cliente</h2>

                <!-- Mensajes de éxito/error -->
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('cliente.perfil.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Nombre -->
                        <div class="col-md-6 mb-3">
                            <label for="nombrecli" class="form-label"><strong>📌 Nombre Completo:</strong></label>
                            <input type="text" class="form-control" id="nombrecli" name="nombrecli" 
                                value="{{ $cliente->nombrecli }}" required 
                                pattern="^[A-Za-zÁÉÍÓÚáéíóúñÑ]+(?: [A-Za-zÁÉÍÓÚáéíóúñÑ]+){3,}$"
                                title="Debe ingresar sus dos nombres y apellidos correctamente.">
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-6 mb-3">
                            <label for="telefonocli" class="form-label"><strong>📞 Teléfono:</strong></label>
                            <input type="tel" class="form-control" id="telefonocli" name="telefonocli" 
                                value="{{ $cliente->telefonocli }}" required pattern="^[0-9]{10}$" 
                                title="Ingrese un número de teléfono válido de 10 dígitos.">
                        </div>

                        <!-- Correo Electrónico -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label"><strong>📧 Correo Electrónico:</strong></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                value="{{ $cliente->email }}" required>
                        </div>

                        <!-- Saldo (Solo Lectura) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>💰 Saldo Disponible:</strong></label>
                            <input type="text" class="form-control" value="${{ number_format($cliente->saldo, 2) }}" disabled>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="{{ route('historial.cliente') }}" class="btn btn-secondary">
                            <i class="fas fa-history"></i> Ver Historial
                        </a>
                        <a href="{{ route('recargar.index') }}" class="btn btn-success">
                            <i class="fas fa-wallet"></i> Recargar Saldo
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
