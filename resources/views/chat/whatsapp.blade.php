@extends('layouts.navigation')

@section('title', 'WhatsApp Helpdesk - Streamify')

@section('main')
    @livewire(\App\Livewire\Chat\WhatsAppHelpdesk::class)
@endsection

