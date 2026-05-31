@extends('layouts.navigation')

@section('title', 'WhatsApp Helpdesk - Streamify')

@section('styles')
<style>
    /* Modo pantalla completa para el chat: eliminar espacio extra bajo el módulo */
    #layoutSidenav_content,
    #layoutSidenav_content > main {
        overflow: hidden !important;
        max-height: calc(100dvh - 56px) !important;
        background: transparent !important;
    }
</style>
@endsection

@section('main')
    @livewire(\App\Livewire\Chat\WhatsAppHelpdesk::class)
@endsection

