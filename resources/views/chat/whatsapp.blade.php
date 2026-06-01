@extends('layouts.navigation')

@section('title', 'WhatsApp Helpdesk - Streamify')

@section('styles')
<style>
    /* Modo pantalla completa para el chat */
    #layoutSidenav_content {
        min-height: calc(100dvh - 56px) !important;
        max-height: calc(100dvh - 56px) !important;
        overflow: hidden !important;
        background: transparent !important;
    }
    #layoutSidenav_content > main {
        overflow: hidden !important;
        flex: 1 1 0 !important;
        min-height: 0 !important;
    }
    /* Ocultar widget IA flotante (tapa el botón Enviar en móvil) */
    #chat-ai-widget-mount,
    #chat-widget-mount,
    [id*="ai-widget"],
    [id*="chat-widget"],
    .donna-widget-fab {
        display: none !important;
    }
    /* Prevenir scroll del body en móvil mientras se usa el chat */
    @media (max-width: 768px) {
        body {
            overflow: hidden !important;
        }
    }
</style>
@endsection

@section('main')
    @livewire(\App\Livewire\Chat\WhatsAppHelpdesk::class)
@endsection

