@extends('layouts.navigation')

@section('title', 'Panel de Chat - Streamify')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/chat-system.css') }}?v={{ filemtime(public_path('css/chat-system.css')) }}">
    <style>
        @media (max-width: 768px) {
            .container-fluid {
                padding: 0 !important;
            }

            .breadcrumb-container {
                padding: 0.5rem 1rem;
                margin: 0;
            }

            h1.mt-4 {
                font-size: 1.25rem;
                margin-top: 0.5rem !important;
                margin-bottom: 0.5rem !important;
            }

            .breadcrumb {
                margin-bottom: 0.5rem !important;
                font-size: 0.875rem;
            }
        }
    </style>
@endsection

@section('main')
    <div class="container-fluid px-4">
        <div class="breadcrumb-container">
            <h1 class="mt-4">Chat</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('tareas.index') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Chat</li>
            </ol>
        </div>

        @livewire('chat.panel-conversaciones')
    </div>
@endsection
