@extends('layouts.navigation')
@section('main')
    <div class="container-fluid px-4">
        <h1 class="mt-4">@yield('h1')</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
            <li class="breadcrumb-item active">@yield('breadcrumb')</li>
            <li class="breadcrumb-item active">@yield('breadcrumb2')</li>
            @yield('breadcrumb3')
        </ol>
        <div class="card mb-4">
            <div class="card-body">
                <p class="mb-0">
                    @yield('introduccion')
                    
                </p>
            </div>
        </div>
        <div>@yield('content')</div>
        <br>
        <div class="card mt-auto mb-4">
            <div class="card-body">@yield('pie')</div>
        </div>
    </div>
@endsection
