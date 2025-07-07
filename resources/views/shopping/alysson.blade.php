@extends('layouts.cliente')

@section('title', 'Para Alysson 💖')
@section('styles')
    <style>
        body {
            background: #fff0f6;
        }
        .header-alysson {
            background: linear-gradient(90deg, #f8bbd0 0%, #f06292 100%);
            color: #fff;
            padding: 2.5rem 1rem 1.5rem 1rem;
            border-radius: 0 0 2rem 2rem;
            box-shadow: 0 4px 16px #f0629240;
            text-align: center;
        }
        .header-alysson h1 {
            font-size: 2.7rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }
        .header-alysson p {
            font-size: 1.2rem;
            margin-bottom: 0;
        }
        .corazon {
            position: fixed;
            width: 38px;
            height: 38px;
            pointer-events: none;
            z-index: 9999;
            animation: flotar 5s linear infinite;
        }
        @keyframes flotar {
            0% {
                transform: translateY(100vh) scale(1) rotate(0deg);
                opacity: 0.8;
            }
            100% {
                transform: translateY(-10vh) scale(1.2) rotate(30deg);
                opacity: 0;
            }
        }
        .section-audio {
            margin: 2.5rem auto 1.5rem auto;
            max-width: 600px;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 12px #f0629240;
            padding: 1.5rem 2rem;
            text-align: center;
        }
        .section-fotos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            max-width: 900px;
            margin: 2rem auto;
        }
        .foto-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 12px #f0629240;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
        }
        .foto-card img {
            width: 100%;
            max-width: 180px;
            border-radius: 0.7rem;
            margin-bottom: 0.7rem;
        }
        .sentimientos-card {
            background: linear-gradient(120deg, #f8bbd0 60%, #fff 100%);
            border-radius: 1.2rem;
            box-shadow: 0 2px 16px #f0629240;
            max-width: 700px;
            margin: 2.5rem auto 2rem auto;
            padding: 2rem 2.5rem;
            text-align: center;
        }
        .sentimientos-card h2 {
            color: #d72660;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .sentimientos-card p {
            font-size: 1.25rem;
            color: #333;
            line-height: 1.7;
        }
        @media (max-width: 600px) {
            .sentimientos-card, .section-audio { padding: 1rem; }
            .header-alysson { padding: 1.5rem 0.5rem 1rem 0.5rem; }
        }
    </style>
@endsection
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
@section('header')
    <div class="header-alysson">
        <h1>Para Alysson Julieth Pilataxi Valencia 💖</h1>
        <p>Esta página es solo para ti, porque eres única y especial en mi vida.</p>
    </div>
@endsection

@section('sections')
    {{-- Sección de audio --}}
    <div class="section-audio">
        <h3 style="color:#d72660;">🎵 Nuestra canción especial</h3>
        <audio controls>
            <source src="{{ asset('audios/tu-cancion.mp3') }}" type="audio/mpeg">
            Tu navegador no soporta el elemento de audio.
        </audio>
        {{-- Puedes agregar más audios aquí --}}
        {{-- <audio controls class="mt-2">
            <source src="{{ asset('audios/otra-cancion.mp3') }}" type="audio/mpeg">
        </audio> --}}
    </div>

    {{-- Grid de fotos o recuerdos (puedes agregar más cards o imágenes) --}}
    <div class="section-fotos">
        {{-- Ejemplo de card de foto --}}
        <div class="foto-card">
            <img src="{{ asset('images/alysson1.jpg') }}" alt="Recuerdo especial">
            <div>Un momento inolvidable juntos</div>
        </div>
        <div class="foto-card">
            <img src="{{ asset('images/alysson2.jpg') }}" alt="Recuerdo especial">
            <div>Tu sonrisa ilumina mi vida</div>
        </div>
        {{-- Agrega más fotos/cards según desees --}}
    </div>

    {{-- Card de sentimientos --}}
    <div class="sentimientos-card">
        <h2>Mis sentimientos para ti</h2>
        <p>
            Eres la persona más especial de mi vida.<br>
            Gracias por existir y por llenar mi mundo de amor.<br>
            <b>Te amo con todo mi corazón.</b><br>
            <br>
            Cada día a tu lado es un regalo, y quiero que sepas que siempre estaré para ti.<br>
            Eres mi inspiración, mi alegría y mi paz.<br>
            <span style="color:#d72660; font-weight:bold;">Siempre tuyo.</span>
        </p>
    </div>
@endsection
@section('scripts')
    {{-- Animación de corazones flotando --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            for (let i = 0; i < 22; i++) {
                let corazon = document.createElement('img');
                corazon.src = 'https://cdn-icons-png.flaticon.com/512/833/833472.png';
                corazon.className = 'corazon';
                corazon.style.left = (Math.random() * 95) + 'vw';
                corazon.style.animationDelay = (Math.random() * 5) + 's';
                document.body.appendChild(corazon);
            }
        });
    </script>
@endsection