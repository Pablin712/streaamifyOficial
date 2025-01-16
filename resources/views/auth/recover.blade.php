<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamify HQ - Recovery System</title>
    <link rel="stylesheet" href="{{ asset('css/login_styles.css') }}">
</head>
<style>
    /* Estilización del contenedor principal */
    .main-container {
        display: flex;
        width: 80%;
        margin: 50px auto;
        border: 2px solid #ccc;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    /* Sección izquierda en blanco */
    .left-section {
        flex: 1;
        background-color: #FFFFFF;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .logo-img {
        max-width: 90%;
        height: auto;
    }

    /* Sección derecha con fondo degradado */
    .right-section {
        flex: 1;
        background: linear-gradient(to bottom, #E4B100, #F2D06B);
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 30px;
    }

    .form-container {
        width: 100%;
        max-width: 400px;
    }

    .form-title {
        font-size: 1.5rem;
        font-weight: bold;
    }

    .btn-danger-custom {
        background-color: #D41216;
        color: #FFFFFF;
        border: none;
        width: 100px;
        height: 40px;
        margin-bottom: 10px;
    }

    .btn-primary-custom {
        background-color: #274698;
        color: #FFFFFF;
        border: none;
        width: 100px;
        height: 40px;
        text-decoration: none;
    }

    .input-field {
        width: 100%;
        margin-bottom: 10px;
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
</style>
<body>
<div class="main-container">
    <!-- Sección izquierda con la imagen -->
    <div class="left-section">
        <img src="{{ asset('images/Icono.png') }}" alt="Streamify" class="logo-img">
    </div>
    
    <!-- Sección derecha con fondo degradado y el formulario Solicitud -->
    <div class="right-section">
        <form action="{{ route('recover.email') }}" method="POST" class="form-container">
            @csrf
            <label>correo</label>
            <input type="email" name="email" id="email" class="input-field" required>
            <br>
            <button type="submit" class="btn btn-primary">Solicitar Cambio de Contraseña</button>
            <br>
        </form>
    </div>
</div>
</body>
</html>
