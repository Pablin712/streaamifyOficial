<!doctype html>
<html lang="es" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Streamify</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/color-model.js') }}"></script>
    <link rel="icon" href="{{ asset('images/Icono.png') }}" type="image/x-icon">
    
    <style>
        /* Fondo de la página */
        body {
            background-color: var(--bs-body-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        /* Tarjeta de login */
        .login-card {
            width: 100%;
            max-width: 380px;
            padding: 2rem;
            border-radius: 8px;
            background-color: var(--bs-body-bg);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Botón de inicio de sesión */
        .btn-login {
            background-color: #E4B100;
            border: none;
            color: white;
            font-weight: bold;
        }

        .btn-login:hover {
            background-color: #C49A00;
        }

        /* Enlace de recuperar contraseña */
        .forgot-password {
            color: #274698;
            font-weight: bold;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        /* Ajuste de color para modo oscuro */
        [data-bs-theme="dark"] .login-card {
            background-color: #1d1d1d;
            color: white;
        }

        [data-bs-theme="dark"] .forgot-password {
            color: #E4B100;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <!-- Mostrar mensajes de validación -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('test_error'))
            <div class="alert alert-info">
                {{ session('test_error') }}
            </div>
        @endif

        <!-- Logo e Introducción -->
        <div class="text-center">
            <img class="mb-3" src="{{ asset('images/Icono.png') }}" alt="Logo" width="72" height="72">
            <h1 class="h4 fw-bold">Iniciar Sesión</h1>
            <p class="text-muted">Accede con tu usuario y contraseña</p>
        </div>

        <!-- Formulario -->
        <form action="{{ route('login.submit') }}" method="POST">
            @csrf

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="floatingInput" placeholder="Usuario" name="usuarioemp" required>
                <label for="floatingInput">Usuario</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="floatingPassword" placeholder="Contraseña" name="passwordemp" required>
                <label for="floatingPassword">Contraseña</label>
            </div>

            <button class="btn btn-login w-100 py-2" type="submit">Iniciar Sesión</button>

            <div class="text-center mt-3">
                <a href="{{ route('recover') }}" class="forgot-password">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>
</body>
</html>