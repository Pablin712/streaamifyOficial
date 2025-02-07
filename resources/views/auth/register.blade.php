<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Streamify - Register</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link rel="icon" href="{{ asset('images/Icono.png') }}" type="image/x-icon">
    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- CSS de Intl-Tel-Input -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background: #E4B100;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-family: Arial, sans-serif;
        }

        .form-container {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
            color: #333;
        }

        .form-container h1 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #274698;
            font-weight: bold;
            text-align: center;
        }

        .form-container .btn-primary {
            background-color: #274698;
            border: none;
            font-weight: bold;
            transition: all 0.3s ease-in-out;
        }

        .form-container .btn-primary:hover {
            background-color: #D41216;
        }

        .form-container .form-floating label {
            color: #555;
        }

        .form-container .form-control {
            border-radius: 10px;
            border: 1px solid #ccc;
        }

        .form-container .extra-links {
            text-align: center;
            margin-top: 15px;
        }

        .form-container .extra-links a {
            color: #274698;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .form-container .extra-links a:hover {
            text-decoration: underline;
        }

        .form-container img {
            width: 70px;
            height: 70px;
            margin-bottom: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>

<body class="bg-gradient-primary">
    <div class="form-container">
        @if (session('autenticate'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Atención:</strong> {{ session('autenticate') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <form method="POST" action="{{ route('cliente.register') }}">
            @csrf
            <img src="{{ asset('images/Icono.png') }}" alt="Logo">
            <h1>Crear una cuenta</h1>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
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
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="first_name" id="firstName"
                            placeholder="Nombres" required>
                        <label for="firstName">Nombres</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="last_name" id="lastName"
                            placeholder="Apellidos" required>
                        <label for="lastName">Apellidos</label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-floating">
                    <input type="email" class="form-control" name="email" id="email" placeholder="Email"
                        required>
                    <label for="email">Email</label>
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group">
                    <!-- Select para código de país -->
                    <select id="countryCode" class="form-select" style="max-width: 120px;">
                        <option value="+593" data-country="Ecuador">🇪🇨 +593</option>
                        <option value="+54" data-country="Argentina">🇦🇷 +54</option>
                        <option value="+591" data-country="Bolivia">🇧🇴 +591</option>
                        <option value="+55" data-country="Brasil">🇧🇷 +55</option>
                        <option value="+56" data-country="Chile">🇨🇱 +56</option>
                        <option value="+57" data-country="Colombia">🇨🇴 +57</option>
                        <option value="+506" data-country="Costa Rica">🇨🇷 +506</option>
                        <option value="+53" data-country="Cuba">🇨🇺 +53</option>
                        <option value="+34" data-country="España">🇪🇸 +34</option>
                        <option value="+503" data-country="El Salvador">🇸🇻 +503</option>
                        <option value="+502" data-country="Guatemala">🇬🇹 +502</option>
                        <option value="+504" data-country="Honduras">🇭🇳 +504</option>
                        <option value="+52" data-country="México">🇲🇽 +52</option>
                        <option value="+505" data-country="Nicaragua">🇳🇮 +505</option>
                        <option value="+507" data-country="Panamá">🇵🇦 +507</option>
                        <option value="+595" data-country="Paraguay">🇵🇾 +595</option>
                        <option value="+51" data-country="Perú">🇵🇪 +51</option>
                        <option value="+1" data-country="República Dominicana">🇩🇴 +1</option>
                        <option value="+598" data-country="Uruguay">🇺🇾 +598</option>
                        <option value="+1" data-country="USA">🇺🇸 +1</option>
                        <option value="+58">🇻🇪 +58</option>
                    </select>
                    <!-- Input para número de teléfono -->
                    <input type="text" class="form-control" name="telefonocli" id="phone"
                        placeholder="Teléfono">
                </div>
            </div>
            <!-- Input oculto para almacenar el país -->
            <input type="hidden" name="pais" id="pais">
            <div class="row mb-3">
                <small class="text-muted d-block mb-1">
                    La contraseña debe tener al menos un número y un símbolo.
                </small>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" name="password" id="password"
                            placeholder="Agrega número y símbolo" required>
                        <label for="password">Contraseña</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" name="password_confirmation"
                            id="confirmPassword" placeholder="Repetir Contraseña" required>
                        <label for="confirmPassword">Repetir Contraseña</label>
                    </div>
                </div>
            </div>            
            <button type="submit" class="btn btn-primary w-100">Registrar Cuenta</button>
        </form>
        <div class="extra-links">
            <a href="{{ route('cliente.login') }}">¿Ya tienes cuenta? Inicia sesión</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-oENmA6qH0YKe1HK8zSbOvIZSmO2Mwkl1H2eDhSWpXXpg4YY2Et3OWSJE6yLyERq2" crossorigin="anonymous">
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function validateTwoWords(input) {
                return input.value.trim().split(/\s+/).length >= 2;
            }

            function validateLastName(value) {
                return /^\S+(?:\s+\S+)*$/.test(value.trim());
            }

            function validatePassword(password) {
                const minLength = password.length >= 6;
                const hasNumber = /[0-9]/.test(password);
                const hasSpecialChar = /[@$!%*?&]/.test(password);
                return minLength && hasNumber && hasSpecialChar;
            }

            document.getElementById("firstName").addEventListener("input", function() {
                if (!validateTwoWords(this)) {
                    this.setCustomValidity("Debe ingresar al menos dos nombres.");
                } else {
                    this.setCustomValidity("");
                }
            });

            document.getElementById("lastName").addEventListener("input", function() {
                if (!validateLastName(this)) {
                    this.setCustomValidity("Debe ingresar al menos un apellido.");
                } else {
                    this.setCustomValidity("");
                }
            });

            document.getElementById("password").addEventListener("input", function() {
                if (!validatePassword(this.value)) {
                    this.setCustomValidity(
                        "La contraseña debe tener al menos 6 caracteres, un número y un símbolo especial (@$!%*?&)."
                    );
                } else {
                    this.setCustomValidity("");
                }
            });

            document.getElementById("password_confirmation").addEventListener("input", function() {
                let password = document.getElementById("password").value;
                if (this.value !== password) {
                    this.setCustomValidity("Las contraseñas no coinciden.");
                } else {
                    this.setCustomValidity("");
                }
            });
        });
    </script>
    <!-- JS de Intl-Tel-Input -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const phoneInput = document.querySelector("#phone");
            const countryCodeSelect = document.querySelector("#countryCode");
            const countryInput = document.querySelector("#pais");

            // Función para actualizar el campo oculto con el país seleccionado
            function updateCountry() {
                let selectedOption = countryCodeSelect.options[countryCodeSelect.selectedIndex];
                countryInput.value = selectedOption.getAttribute("data-country");
            }

            // Evento para actualizar el país cuando se cambia el código de país
            countryCodeSelect.addEventListener("change", updateCountry);

            // Establecer país inicial según la selección por defecto
            updateCountry();

            // Formatear número mientras el usuario escribe
            phoneInput.addEventListener("input", function() {
                let selectedCode = countryCodeSelect.value; // Obtener código seleccionado
                let rawNumber = phoneInput.value.replace(/\D/g, ""); // Solo números

                if (selectedCode === "+593") { // Validación especial para Ecuador
                    if (rawNumber.startsWith("0")) {
                        rawNumber = rawNumber.substring(1); // Eliminar "0" inicial
                    }
                    if (rawNumber.length > 9) {
                        rawNumber = rawNumber.substring(0, 9); // Solo 9 dígitos permitidos
                    }
                }
                phoneInput.value = formatPhoneNumber(rawNumber, selectedCode);
            });

            function formatPhoneNumber(number, countryCode) {
                number = number.replace(/\D/g, ""); // Eliminar caracteres no numéricos

                if (countryCode === "+593") { // Ecuador
                    if (number.startsWith("0")) {
                        number = number.substring(1); // Eliminar el "0" inicial si lo tiene
                    }
                    if (number.length > 9) {
                        number = number.substring(0, 9); // Asegurar que solo tenga 9 dígitos
                    }
                    return `${number.slice(0, 2)} ${number.slice(2, 5)} ${number.slice(5, 9)}`;
                } else if (number.length === 10) { // Números de 10 dígitos (Ej: México, Argentina)
                    return `${number.slice(0, 2)} ${number.slice(2, 6)} ${number.slice(6, 10)}`;
                } else if (number.length === 9) { // Números de 9 dígitos (Ej: Colombia, Chile)
                    return `${number.slice(0, 3)} ${number.slice(3, 6)} ${number.slice(6, 9)}`;
                } else if (number.length === 8) { // Números de 8 dígitos (Ej: Bolivia, El Salvador)
                    return `${number.slice(0, 4)} ${number.slice(4, 8)}`;
                }
                return number; // Si no coincide con ningún formato, se deja igual
            }

            // Antes de enviar el formulario, asegurarse de que el campo país tenga un valor
            document.querySelector("form").addEventListener("submit", function() {
                updateCountry(); // Asegurar que el país esté actualizado
                let fullPhoneNumber = countryCodeSelect.value + " " + phoneInput.value;
                phoneInput.value = fullPhoneNumber; // Guardar en formato "+593 96 177 8319"
            });
        });
    </script>
</body>

</html>
