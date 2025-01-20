<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Login Cliente</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Custom CSS -->
    <style>
        body {
            background: #E4B100; /* linear-gradient(135deg, #E4B100, #274698); */
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
            max-width: 400px;
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
            background-color: #E4B100;
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

        .form-container img {
            width: 70px;
            height: 70px;
            margin-bottom: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
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
    </style>
</head>

<body>
    <div class="form-container">
        <form action="{{ route('cliente.login') }}" method="POST">
            @csrf
            <img src="{{ asset('images/Icono.png') }}" alt="Logo">
            <h1>Inicia Sesión</h1>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="floatingInput" name="email" placeholder="Usuario">
                <label for="floatingInput">Usuario</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="floatingPassword" name="password"
                    placeholder="Contraseña">
                <label for="floatingPassword">Contraseña</label>
            </div>

            <button class="btn btn-primary w-100 py-2" type="submit">Iniciar Sesión</button>

            <div class="extra-links mt-3">
                <a href="#">¿Olvidaste tu contraseña?</a> <br>
                <a href="{{ route('cliente.register')}}">¿No tienes cuenta? Regístrate</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</body>

</html>
