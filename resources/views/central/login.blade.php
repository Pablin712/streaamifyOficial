<!doctype html>
<html lang="es" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Central — Streamify SaaS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: var(--bs-body-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-card {
            width: 100%;
            max-width: 380px;
            padding: 2rem;
            border-radius: 8px;
            background-color: var(--bs-body-bg);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-login {
            background-color: #274698;
            border: none;
            color: white;
            font-weight: bold;
        }

        .btn-login:hover {
            background-color: #1d3475;
            color: white;
        }
    </style>
</head>

<body>
    <div class="login-card">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="text-center mb-3">
            <h1 class="h4 fw-bold">Panel Central</h1>
            <p class="text-muted">Administracion de Tenants (Vendedores)</p>
        </div>

        <form action="{{ route('central.login.submit') }}" method="POST">
            @csrf

            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" placeholder="Email" name="email" value="{{ old('email') }}" required autofocus>
                <label for="email">Email</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" placeholder="Contraseña" name="password" required>
                <label for="password">Contraseña</label>
            </div>

            <button class="btn btn-login w-100 py-2" type="submit">Iniciar Sesión</button>
        </form>
    </div>
</body>

</html>
