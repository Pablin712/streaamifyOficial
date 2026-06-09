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

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4" id="libraryTabs" role="tablist" style="border-bottom:2px solid #e5e7eb;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-conocimiento" data-bs-toggle="tab" data-bs-target="#panel-conocimiento"
                    type="button" role="tab" style="font-size:.88rem;font-weight:600;">
                📖 Conocimiento
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-imagenes" data-bs-toggle="tab" data-bs-target="#panel-imagenes"
                    type="button" role="tab" style="font-size:.88rem;font-weight:600;">
                🖼 Imágenes
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- Tab: Conocimiento --}}
        <div class="tab-pane fade show active" id="panel-conocimiento" role="tabpanel">
            <p class="text-muted mb-4" style="font-size:.88rem;max-width:680px;">
                Contextos de conocimiento que el agente IA usa para responder con precisión.
                Incluye FAQs, servicios, políticas, campañas temporales y más.
                Las campañas activas se inyectan automáticamente según su rango de fechas.
            </p>
            @livewire('chat.agent-library')
        </div>

        {{-- Tab: Imágenes --}}
        <div class="tab-pane fade" id="panel-imagenes" role="tabpanel">
            <p class="text-muted mb-4" style="font-size:.88rem;max-width:680px;">
                Imágenes que el agente puede enviar a los clientes por WhatsApp.
                El agente consulta la lista y elige cuál imagen enviar según la conversación.
            </p>
            @livewire('chat.agent-library-images')
        </div>
    </div>

</div>
@endsection
