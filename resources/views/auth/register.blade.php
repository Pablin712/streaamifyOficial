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

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

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
                <div class="form-floating">
                    <input type="text" class="form-control" name="telefonocli" id="phone" placeholder="Teléfono">
                    <label for="phone">Teléfono</label>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" name="password" id="password"
                            placeholder="Agrega número y simbolo" required>
                        <label for="password">Contraseña</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" name="password_confirmation" id="confirmPassword"
                            placeholder="Repetir Contraseña" required>
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
</body>

</html>
