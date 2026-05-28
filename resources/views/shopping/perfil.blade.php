@extends('layouts.cliente')
@section('title', 'Mi Perfil')
@section('menu')
    <!-- Menú Desplegable Acerca de -->
    <div class="dropdown me-lg-3">
        <button class="btn btn-light border rounded-pill dropdown-toggle fw-bold" type="button" id="dropdownAcerca"
            data-bs-toggle="dropdown" aria-expanded="false">
            Acerca de
        </button>
        <ul class="dropdown-menu shadow" aria-labelledby="dropdownAcerca">
            <li><a class="dropdown-item" href="{{ route('principal') }}#registro">Registro</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#features">Fortalezas</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#combos">Promociones</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#servicios">Otros Servicios</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#redes">Redes Sociales</a></li>
            <li><a class="dropdown-item" href="{{ route('principal') }}#faq">Preguntas Frecuentes</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item fw-bold" href="{{ route('donna') }}" style="color: #274698;">
                <i class="bi bi-robot me-1"></i> Donna AI
            </a></li>
        </ul>
    </div>

    <!-- Menú Desplegable Catálogo -->
    <div class="dropdown me-lg-3">
        <button class="btn btn-light border rounded-pill dropdown-toggle fw-bold" type="button" id="dropdownCatalogo"
            data-bs-toggle="dropdown" aria-expanded="false">
            Catálogo
        </button>
        <ul class="dropdown-menu shadow" aria-labelledby="dropdownCatalogo">
            <li><a class="dropdown-item" href="{{ route('shop') }}#inmediata-individual">Entrega Inmediata - Individual</a>
            </li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#combos">Entrega Inmediata - Combos</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#pedidos">Pedidos</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#personalizadas">Personalizadas</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#completos">Cuentas completas</a></li>
            <li><a class="dropdown-item" href="{{ route('shop') }}#juegos">Juegos</a></li>
        </ul>
    </div>
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
                            <div class="input-group">
                                <!-- Select para código de país -->
                                <select id="countryCode" class="form-select" style="max-width: 120px;">
                                    <option value="+593" data-country="Ecuador"
                                        {{ Str::startsWith($cliente->telefonocli, '+593') ? 'selected' : '' }}>🇪🇨 +593
                                    </option>
                                    <option value="+54" data-country="Argentina"
                                        {{ Str::startsWith($cliente->telefonocli, '+54') ? 'selected' : '' }}>🇦🇷 +54
                                    </option>
                                    <option value="+591" data-country="Bolivia"
                                        {{ Str::startsWith($cliente->telefonocli, '+591') ? 'selected' : '' }}>🇧🇴 +591
                                    </option>
                                    <option value="+55" data-country="Brasil"
                                        {{ Str::startsWith($cliente->telefonocli, '+55') ? 'selected' : '' }}>🇧🇷 +55
                                    </option>
                                    <option value="+56" data-country="Chile"
                                        {{ Str::startsWith($cliente->telefonocli, '+56') ? 'selected' : '' }}>🇨🇱 +56
                                    </option>
                                    <option value="+57" data-country="Colombia"
                                        {{ Str::startsWith($cliente->telefonocli, '+57') ? 'selected' : '' }}>🇨🇴 +57
                                    </option>
                                    <option value="+52" data-country="México"
                                        {{ Str::startsWith($cliente->telefonocli, '+52') ? 'selected' : '' }}>🇲🇽 +52
                                    </option>
                                    <option value="+1" data-country="USA"
                                        {{ Str::startsWith($cliente->telefonocli, '+1') ? 'selected' : '' }}>🇺🇸 +1
                                    </option>
                                    <option value="+58" data-country="Venezuela"
                                        {{ Str::startsWith($cliente->telefonocli, '+58') ? 'selected' : '' }}>🇻🇪 +58
                                    </option>
                                </select>
                                <!-- Input para el número -->
                                <input type="text" class="form-control" id="phone" name="telefonocli"
                                    value="{{ preg_replace('/^\+\d+\s/', '', $cliente->telefonocli) }}"
                                    placeholder="Teléfono">
                            </div>
                        </div>
                        <!-- Input oculto para almacenar el país -->
                        <input type="hidden" name="pais" id="pais">

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

                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>💰 Link de referido:</strong></label>
                            <div class="input-group">
                                <input type="text" class="form-control"
                                       value="{{ route('register', ['codigo_referidor' => $cliente->codigo_referidor]) }}"
                                       id="referralLink" readonly>
                                <button class="btn btn-secondary" type="button" onclick="copyReferralLink()">Copiar</button>
                            </div>
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
@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const phoneInput = document.querySelector("#phone");
            const countryCodeSelect = document.querySelector("#countryCode");
            const countryInput = document.querySelector("#pais");

            // Función para actualizar el país seleccionado
            function updateCountry() {
                let selectedOption = countryCodeSelect.options[countryCodeSelect.selectedIndex];
                countryInput.value = selectedOption.getAttribute("data-country");
            }

            // Evento al cambiar el código de país
            countryCodeSelect.addEventListener("change", updateCountry);

            // Establecer país inicial
            updateCountry();

            // Formatear número mientras el usuario escribe
            phoneInput.addEventListener("input", function() {
                let selectedCode = countryCodeSelect.value;
                let rawNumber = phoneInput.value.replace(/\D/g, ""); // Eliminar caracteres no numéricos

                if (selectedCode === "+593") { // Ecuador
                    if (rawNumber.startsWith("0")) {
                        rawNumber = rawNumber.substring(1);
                    }
                    if (rawNumber.length > 9) {
                        rawNumber = rawNumber.substring(0, 9);
                    }
                }
                phoneInput.value = formatPhoneNumber(rawNumber, selectedCode);
            });

            function formatPhoneNumber(number, countryCode) {
                number = number.replace(/\D/g, "");

                if (countryCode === "+593") { // Ecuador
                    if (number.startsWith("0")) {
                        number = number.substring(1);
                    }
                    if (number.length > 9) {
                        number = number.substring(0, 9);
                    }
                    return `${number.slice(0, 2)} ${number.slice(2, 5)} ${number.slice(5, 9)}`;
                } else if (number.length === 10) { // México, Argentina
                    return `${number.slice(0, 2)} ${number.slice(2, 6)} ${number.slice(6, 10)}`;
                } else if (number.length === 9) { // Colombia, Chile
                    return `${number.slice(0, 3)} ${number.slice(3, 6)} ${number.slice(6, 9)}`;
                } else if (number.length === 8) { // Bolivia, El Salvador
                    return `${number.slice(0, 4)} ${number.slice(4, 8)}`;
                }
                return number;
            }

            // Antes de enviar el formulario, asegurarse de que el país esté actualizado
            document.querySelector("form").addEventListener("submit", function() {
                updateCountry();
                let fullPhoneNumber = countryCodeSelect.value + " " + phoneInput.value;
                phoneInput.value = fullPhoneNumber;
            });
        });
    </script>
    <script>
        function copyReferralLink() {
            const referralLink = document.getElementById('referralLink');
            referralLink.select();
            referralLink.setSelectionRange(0, 99999); // Para dispositivos móviles
            document.execCommand('copy');
            alert('¡Enlace copiado al portapapeles!');
        }
    </script>
@endsection
