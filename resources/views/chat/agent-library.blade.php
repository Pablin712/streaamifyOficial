@extends('layouts.navigation')

@section('title', 'Biblioteca del Agente IA - Streamify')

@section('main')
<div class="container-fluid px-4 py-3">

    <div class="d-flex align-items-center gap-2 mb-1">
        <h1 class="mt-2 mb-0" style="font-size:1.4rem;font-weight:700;">
            📚 Biblioteca del Agente IA
        </h1>
    </div>
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:.82rem;">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/chat/whatsapp') }}">Chat WhatsApp</a></li>
            <li class="breadcrumb-item active">Biblioteca del Agente</li>
        </ol>
    </nav>
    <p class="text-muted mb-4" style="font-size:.88rem;max-width:680px;">
        Contextos de conocimiento que el agente IA usa para responder con precisión.
        Incluye FAQs, servicios, políticas, campañas temporales y más.
        Las campañas activas se inyectan automáticamente según su rango de fechas.
    </p>

    @livewire('chat.agent-library')

</div>
@endsection
