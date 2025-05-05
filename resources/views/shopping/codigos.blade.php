@extends('layouts.cliente')

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

@section('title', 'Códigos de Hogar')

@section('header')
    <div class="container text-center my-4">
        <h1 class="fw-bold">Acceso a Códigos de Hogar</h1>
        <p class="text-muted">
            Aquí puedes acceder a los <strong>códigos de hogar</strong> que necesitas para disfrutar de nuestros servicios.
            Si no tienes códigos disponibles, sigue las instrucciones para solicitarlos.
        </p>
        <div class="d-flex justify-content-center">
            @if (!empty($codigos) && count($codigos) > 0)
                @foreach (collect($codigos)->unique('bot') as $codigo)
                    <a href="{{ $codigo['bot'] }}" class="btn btn-primary me-2" target="_blank">
                        Solicitar Códigos de {{ $codigo['servicio'] }}
                    </a>
                @endforeach
            @else
                <p class="text-danger">No tienes cuentas que cuenten con bot de códigos.</p>
            @endif
        </div>
    </div>
@endsection

@section('sections')
    <div class="container px-5 my-5">
        <h3 class="text-center">¿Cómo Solicitar Códigos?</h3>
        <p class="text-muted text-center">
            Si necesitas códigos de hogar, sigue estos pasos:
        </p>
        <ol class="list-group list-group-numbered">
            <li class="list-group-item">
                Accede al botón <strong>"Solicitar Códigos"</strong> en la parte superior de esta página.
            </li>
            <li class="list-group-item">
                Completa el formulario con la información requerida para procesar tu solicitud.
            </li>
            <li class="list-group-item">
                Una vez aprobada tu solicitud, podrás ver tus códigos en la sección <strong>"Ver Mis Códigos"</strong>.
            </li>
        </ol>
        <p class="text-center mt-4">
            Si tienes alguna duda, no dudes en contactarnos a través de nuestras <a
                href="{{ route('principal') }}#redes">redes sociales</a>.
        </p>
    </div>
@endsection

@section('scripts')
    <!-- Puedes agregar scripts adicionales aquí si es necesario -->
@endsection
