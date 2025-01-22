<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Streamify HQ Recovery System">
    <meta name="author" content="Streamify HQ">
    <title>Recuperar Contraseña - Streamify HQ</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <link rel="icon" href="{{ asset('images/Icono.png') }}" type="image/x-icon">

    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f8f9fa;
        }

        .form-recover {
            max-width: 380px;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .form-recover img {
            max-width: 100px;
            margin-bottom: 15px;
        }

        .form-recover h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #333;
        }

        .btn-recover {
            background-color: #E4B100;
            color: #fff;
            font-weight: bold;
        }

        .btn-recover:hover {
            background-color: #274698;
        }

        .alert {
            text-align: left;
        }

        .form-text {
            color: #6c757d;
        }
    </style>
</head>

<body>
    <main class="form-recover">
        <img src="{{ asset('images/Icono.png') }}" alt="Streamify HQ">
        <h1 class="h3 mb-3 fw-normal">Recuperar Contraseña</h1>

        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('recoverCliente.email') }}" method="POST">
            @csrf
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                <label for="email">Correo electrónico</label>
            </div>
            <button class="btn btn-recover w-100 py-2" type="submit">Solicitar Cambio</button>
        </form>
        <p class="mt-3 form-text">Ingresa el correo asociado a tu cuenta para recibir instrucciones de recuperación.</p>
        <a href="{{ route('cliente.login') }}" class="text-decoration-none">Volver al inicio de sesión</a>
    </main>
</body>

</html>
