@extends('layouts.cliente')

@section('title', 'Para Alysson 💖')
@section('styles')
    <style>
        body {
            background: #fff0f6;
        }

        .corazon {
            position: fixed;
            width: 40px;
            height: 40px;
            background: url('https://cdn-icons-png.flaticon.com/512/833/833472.png') no-repeat center/contain;
            animation: flotar 4s linear infinite;
            opacity: 0.7;
            z-index: 9999;
        }

        @keyframes flotar {
            0% {
                transform: translateY(100vh) scale(1);
                opacity: 0.7;
            }

            100% {
                transform: translateY(-10vh) scale(1.2);
                opacity: 0;
            }
        }
    </style>
@endsection
@section('header')
    
@endsection
@section('sections')
    <div style="position:relative; min-height: 80vh;">
        <h1 class="text-center mt-5" style="color:#d72660; font-size:2.5rem;">
            Para Alysson Julieth Pilataxi Valencia 💖
        </h1>
        <p class="text-center mt-4" style="font-size:1.3rem;">
            Eres la persona más especial de mi vida.<br>
            Gracias por existir y por llenar mi mundo de amor.<br>
            <b>Te amo con todo mi corazón.</b>
        </p>
    </div>
    <script>
        // Animación de corazones flotando
        for (let i = 0; i < 20; i++) {
            let corazon = document.createElement('div');
            corazon.className = 'corazon';
            corazon.style.left = (Math.random() * 100) + 'vw';
            corazon.style.animationDelay = (Math.random() * 4) + 's';
            document.body.appendChild(corazon);
        }
    </script>
@endsection
