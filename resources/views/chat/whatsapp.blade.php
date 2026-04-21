@extends('layouts.navigation')

@section('title', 'WhatsApp Helpdesk - Streamify')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/chat-system.css') }}?v={{ filemtime(public_path('css/chat-system.css')) }}">
@endsection

@section('main')
    @livewire('chat.whatsapp-helpdesk')
@endsection

