<!DOCTYPE html>
<html lang="es"></html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 10px 0;
        }
        .content {
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 10px 0;
            font-size: 12px;
            color: #888888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Recuperación de Contraseña</h1>
        </div>
        <div class="content">
            <p>Estimado/a {{ $user->nombreemp }}, </p>
            <p>Hemos recibido una solicitud para restablecer su contraseña del usuario {{ $user->usuarioemp }}. Su nueva contraseña es:</p>
            <p><strong>{{ $password }}</strong></p>
            <p>Por favor, utilice esta contraseña para iniciar sesión y asegúrese de cambiarla por una más segura una vez que tenga acceso a su cuenta.</p>
            <p>Si no solicitó un restablecimiento de contraseña,contacte de forma inmediata con el soporte.</p>
        </div>
        <div class="footer">
            <p>Gracias,</p>
            <p>El equipo de Streamify</p>
        </div>
    </div>
</body>
</html>