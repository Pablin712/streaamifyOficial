@extends('layouts.static') 
@section('title')
    Inicio
@endsection
@section('introduccion') 
<h1>Bienvenido, {{ Auth::user()->nombreemp }}</h1>

@endsection
@section('content')


<div class="container">
    <h1 class="text-center my-4"> Que haras hoy?</h1>

    <div class="container mt-4">
    <h1 class="text-center my-4">Acciones Frecuentes</h1>
    <div class="row text-center">
        <!-- Ver Ventas -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <i class="fas fa-shopping-cart fa-5x text-primary mb-3"></i>
                    <h5 class="card-title">Ventas</h5>
                    <a href="{{ route('ventas') }}" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Ver Ventas
                    </a>
                </div>
            </div>
        </div>

        <!-- Administrar Usuarios -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <i class="fas fa-users fa-5x text-success mb-3"></i>
                    <h5 class="card-title">Usuarios</h5>
                    <a href="{{ route('usuarios') }}" class="btn btn-success">
                        <i class="fas fa-cogs"></i> Administrar Usuarios
                    </a>
                </div>
            </div>
        </div>

        <!-- Gestionar Contabilidad -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <i class="fas fa-wallet fa-5x text-warning mb-3"></i>
                    <h5 class="card-title">Contabilidad</h5>
                    <a href="{{ route('dashboard') }}" class="btn btn-warning">
                        <i class="fas fa-chart-line"></i> Gestionar Contabilidad
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <!--<h1 class="text-center my-4">Acciones Frecuentes</h1>-->
    <div class="row">
        <!-- Registrar Venta (Azul) -->
        <div class="col-md-4 mb-4">
            <div class="card shadow text-center">
                <div class="card-body">
                    <i class="fas fa-shopping-cart fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Registrar venta</h5>
                    <a href="{{ route('ventas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                </div>
            </div>
        </div>

        <!-- Registrar Cuenta (Negro) -->
        <div class="col-md-4 mb-4">
            <div class="card shadow text-center">
                <div class="card-body">
                    <i class="fas fa-tv fa-3x text-dark mb-3"></i>
                    <h5 class="card-title">Registrar cuenta</h5>
                    <a href="{{ route('cuentas.create') }}" class="btn btn-dark">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                </div>
            </div>
        </div>

        <!-- Registrar Gasto (Amarillo - Business Logic) -->
        <div class="col-md-4 mb-4">
            <div class="card shadow text-center">
                <div class="card-body">
                    <i class="fas fa-money-bill-wave fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Registrar gasto</h5>
                    <a href="{{ route('gastos') }}" class="btn btn-warning">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                </div>
            </div>
        </div>

        <!-- Registrar Cliente (Verde - Usuarios) -->
        <div class="col-md-4 mb-4">
            <div class="card shadow text-center">
                <div class="card-body">
                    <i class="fas fa-user fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Registrar cliente</h5>
                    <a href="{{ route('clientes.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                </div>
            </div>
        </div>

        <!-- Registrar Servicio (Rojo) -->
        <div class="col-md-4 mb-4">
            <div class="card shadow text-center">
                <div class="card-body">
                    <i class="fas fa-concierge-bell fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Registrar servicio</h5>
                    <a href="{{ route('servicios.create') }}" class="btn btn-danger">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                </div>
            </div>
        </div>

        <!-- Registrar Proveedor (Rojo) -->
        <div class="col-md-4 mb-4">
            <div class="card shadow text-center">
                <div class="card-body">
                    <i class="fas fa-truck fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Registrar proveedor</h5>
                    <a href="{{ route('proveedores.create') }}" class="btn btn-danger">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                </div>
            </div>
        </div>

        <!-- Registrar Valor (Amarillo - Business Logic) -->
        <div class="col-md-4 mb-4">
            <div class="card shadow text-center">
                <div class="card-body">
                    <i class="fas fa-coins fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Registrar valor</h5>
                    <a href="{{ route('valores.create') }}" class="btn btn-warning">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>



<h3>Mapa de erp</h3>
<img src="{{asset('images/BASE2.png')}}" alt="imagen de mapa">

@endsection


@section('pie')
    Realiza las tareas
@endsection