@extends('layouts.navigation')
@section('title')
    Tabla de ejemplo
@endsection
@section('main')
    <div class="container-fluid px-4">
        <h1 class="mt-4">@yield('h1')</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item active">@yield('h1')</li>
        </ol>
        <div class="card mb-4">
            <div class="card-body">
                <p class="mb-0">
                    @yield('introduccion')
                </p>
            </div>
        </div>
        <div style="height: 100vh">@yield('content')</div>
        <div class="card mb-4">
            <div class="card-body">@yield('pie')</div>
        </div>
    </div>
@endsection
