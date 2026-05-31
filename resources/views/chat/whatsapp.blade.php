@extends('layouts.navigation')

@section('title', 'WhatsApp Helpdesk - Streamify')

@section('styles')
<style>
    /* Modo pantalla completa para el chat: el inline min-height:100vh sobredimensiona el contenedor */
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
</style>
@endsection

@section('main')
    @livewire(\App\Livewire\Chat\WhatsAppHelpdesk::class)
@endsection

