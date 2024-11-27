@extends('layouts.navigation') 
@section('title')
Tabla de ejemplo
@endsection 
@section('main')

    <div class="container-fluid px-4">
        <h1 class="mt-4">@yield('h1')</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">@yield('h1')</li>
        </ol>
        <div class="card mb-4">
            <div class="card-body">
                @yield('descripcion')
                
                @yield('btncrear')
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                @yield('tablename')
            </div>
            <div class="card-body">
                {{-- aqui empieza la tabla, se modifica, en cualquier tabla
                se debe poner con style id="datatablesSimple"
                example: <table id="datatablesSimple">--}}
                @yield('table1')
            </div>
        </div>
        @yield('table2')
        @yield('table3')
    </div>

@endsection