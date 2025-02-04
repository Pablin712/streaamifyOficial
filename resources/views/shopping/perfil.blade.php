@extends('layouts.cliente')
@section('title', 'Mi Perfil')
@section('menu')
    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('principal') }}#features">Fortalezas</a></li>
    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('principal') }}#combos">Combos</a>
    </li>
    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('shop') }}#catalog">Catálogo</a>
    </li>
@endsection
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
                <!-- Mensajes de éxito y error -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <strong>Éxito:</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <strong>Error:</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <strong>Error:</strong>
                        <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
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
                            <input type="text" class="form-control" value="${{ number_format($cliente->saldo, 2) }}"
                                disabled>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                            data-bs-target="#changePasswordModal">
                            <i class="fas fa-key"></i> Cambiar Contraseña
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

    <!-- Modal de cambio de contraseña -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">🔑 Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('cliente.cambiar.contrasena') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Contraseña actual -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Contraseña Actual</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                        </div>

                        <!-- Nueva contraseña -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required
                                minlength="6" pattern="^(?=.*[0-9])(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$"
                                title="Debe tener al menos 6 caracteres, un número y un símbolo especial (@$!%*?&).">
                        </div>

                        <!-- Confirmar nueva contraseña -->
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Repetir Nueva Contraseña</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation"
                                required>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar
                                Cambios</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
