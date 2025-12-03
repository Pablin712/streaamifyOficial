@extends('layouts.static')

@section('title', 'Editar Perfil')

@section('h1')
    <i class="fas fa-user-edit text-primary me-2"></i> Mi Perfil
@endsection

@section('breadcrumb')
    Empleados
@endsection

@section('breadcrumb2')
    Editar Perfil
@endsection

@section('introduccion')
    Desde aquí puedes actualizar tu información personal, cambiar tu foto de perfil y modificar tu contraseña de acceso.
    Mantén tus datos actualizados para mejorar la comunicación dentro del equipo.
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <!-- Card de Perfil -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: var(--bg-light);">
                    <h5 class="mb-0" style="color: var(--text-primary);">
                        <i class="fas fa-info-circle me-2"></i>Información Personal
                    </h5>
                </div>
                <div class="card-body">
                    <form id="editProfileForm" action="{{ route('empleados.update', $empleado->idemp) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Foto de Perfil -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img id="profileImagePreview"
                                     src="{{ $empleado->foto_url ? asset('storage/' . $empleado->foto_url) : 'https://via.placeholder.com/150/007bff/ffffff?text=' . strtoupper(substr($empleado->nombreemp, 0, 1)) }}"
                                     class="rounded-circle shadow"
                                     style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--bs-primary);"
                                     alt="Foto de perfil">
                                <label for="foto_url" class="position-absolute bottom-0 end-0 btn btn-primary btn-sm rounded-circle"
                                       style="width: 40px; height: 40px; padding: 0; cursor: pointer;"
                                       title="Cambiar foto">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file"
                                       class="d-none"
                                       id="foto_url"
                                       name="foto_url"
                                       accept="image/*"
                                       onchange="previewProfileImage(event)">
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Haz clic en el icono de la cámara para cambiar tu foto</small>
                            </div>
                            @error('foto_url')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="nombreemp" class="form-label fw-semibold">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('nombreemp') is-invalid @enderror"
                                    id="nombreemp" name="nombreemp"
                                    value="{{ old('nombreemp', $empleado->nombreemp) }}"
                                    required maxlength="50">
                                @error('nombreemp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Usuario -->
                            <div class="col-md-6 mb-3">
                                <label for="usuarioemp" class="form-label fw-semibold">
                                    <i class="fas fa-at text-primary me-1"></i>
                                    Usuario <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('usuarioemp') is-invalid @enderror"
                                    id="usuarioemp" name="usuarioemp"
                                    value="{{ old('usuarioemp', $empleado->usuarioemp) }}"
                                    required maxlength="20">
                                @error('usuarioemp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Utilizado para iniciar sesión</small>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="fas fa-envelope text-primary me-1"></i>
                                    Correo Electrónico
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email"
                                    value="{{ old('email', $empleado->email) }}"
                                    maxlength="50">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefonoemp" class="form-label fw-semibold">
                                    <i class="fas fa-phone text-primary me-1"></i>
                                    Teléfono
                                </label>
                                <input type="tel" class="form-control @error('telefonoemp') is-invalid @enderror"
                                    id="telefonoemp" name="telefonoemp"
                                    value="{{ old('telefonoemp', $empleado->telefonoemp) }}"
                                    maxlength="15">
                                @error('telefonoemp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Divider -->
                        <hr class="my-4">

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('inicio') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card de Cambiar Contraseña -->
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: var(--bg-light);">
                    <h5 class="mb-0" style="color: var(--text-primary);">
                        <i class="fas fa-key me-2"></i>Cambiar Contraseña
                    </h5>
                </div>
                <div class="card-body">
                    <form id="changePasswordForm" action="{{ route('empleados.updatePassword', $empleado->idemp) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Por seguridad, necesitas ingresar tu contraseña actual para realizar el cambio.
                        </div>

                        <div class="row">
                            <!-- Contraseña Actual -->
                            <div class="col-md-6 mb-3">
                                <label for="current_password" class="form-label fw-semibold">
                                    <i class="fas fa-lock text-primary me-1"></i>
                                    Contraseña Actual <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="w-100"></div>

                            <!-- Nueva Contraseña -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-lock text-primary me-1"></i>
                                    Nueva Contraseña <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" minlength="6" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label fw-semibold">
                                    <i class="fas fa-lock text-primary me-1"></i>
                                    Confirmar Contraseña <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control"
                                    id="password_confirmation" name="password_confirmation" minlength="6" required>
                            </div>
                        </div>

                        <!-- Botón -->
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key me-1"></i> Cambiar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pie')
    <p class="mb-0">
        <i class="fas fa-shield-alt text-success me-2"></i>
        <strong>Nota:</strong> Todos los cambios realizados en tu perfil quedarán registrados en el historial del sistema por motivos de seguridad.
    </p>
@endsection

@section('scripts')
<script>
// Preview de imagen de perfil
function previewProfileImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileImagePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// Validación del formulario de perfil
document.getElementById('editProfileForm').addEventListener('submit', function(e) {
    const nombre = document.getElementById('nombreemp').value.trim();
    const usuario = document.getElementById('usuarioemp').value.trim();

    if (!nombre || !usuario) {
        e.preventDefault();
        alert('Por favor completa los campos obligatorios');
        return false;
    }
});

// Validación del formulario de contraseña
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirmation').value;

    if (!currentPassword || !newPassword || !confirmPassword) {
        e.preventDefault();
        alert('Por favor completa todos los campos de contraseña');
        return false;
    }

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
    }

    if (newPassword.length < 6) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 6 caracteres');
        return false;
    }
});
</script>
@endsection
