@extends('layouts.static')

@section('title', 'Detalles del Producto')

@section('h1', 'Detalles del Producto')
@section('breadcrumb')
    <a href="{{ route('productos.index') }}">Productos</a>
@endsection
@section('breadcrumb2')
    Ver Producto / {{ $producto->codigopro }}
@endsection
@section('introduccion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p class="lead">Aquí puedes ver toda la información del producto seleccionado, incluyendo sus detalles asociados.</p>
    <a href="{{ route('productos.index') }}" class="btn btn-outline-primary mt-3">
        <i class="fas fa-arrow-left"></i> Volver a Productos
    </a>
@endsection

@section('content')
    <div class="container">
        <!-- Tarjeta de Información del Producto -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row">
                    <!-- Columna de información -->
                    <div class="col-md-8">
                        <h4 class="card-title text-primary">{{ $producto->nombrepro }}</h4>
                        <h6 class="card-subtitle text-muted">Código: {{ $producto->codigopro }}</h6>
                        <hr>
                        <p class="card-text"><strong>Precio:</strong> ${{ number_format($producto->preciopro, 2) }}</p>
                        <p class="card-text"><strong>Descripción:</strong> {{ $producto->descripcionpro }}</p>
                        <p class="card-text"><strong>Categoría:</strong> {{ $producto->categoria->nombre }}</p>
                        <p class="card-text"><strong>Tipo de Producto:</strong> {{ $producto->tipoProducto->nombre }}</p>
                        <p class="card-text"><strong>Estado:</strong> 
                            @if ($producto->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </p>
                    </div>
                    <!-- Columna de imagen -->
                    <div class="col-md-4 text-center">
                        @if ($producto->foto)
                            <img src="{{ asset('public/' . $producto->foto) }}" alt="Foto del Producto" class="img-fluid rounded shadow">
                        @else
                            <p class="text-muted">Sin imagen disponible</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Detalles del Producto -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="mb-3">Detalles del Producto</h4>
                <table class="table table-hover table-bordered text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>ID Servicio</th>
                            <th>Descripción</th>
                            <th>Meses</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($producto->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->idser }}</td>
                                <td>{{ $detalle->descripcion }}</td>
                                <td>{{ $detalle->meses }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection