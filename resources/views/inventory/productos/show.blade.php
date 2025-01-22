@extends('layouts.static')

@section('title', 'Detalles del Producto')

@section('h1', 'Detalles del Producto')
@section('introduccion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Aquí puedes ver toda la información del producto seleccionado, incluyendo sus detalles asociados.</p>
    <a href="{{ route('productos.index') }}" class="btn btn-secondary mt-3">Volver a Productos</a>
@endsection

@section('content')
    <div class="container">
        <h2>Información del Producto</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Nombre: {{ $producto->nombrepro }}</h5>
                <h6 class="card-subtitle mb-2 text-muted">Código: {{ $producto->codigopro }}</h6>
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
                @if ($producto->foto)
                    <p><strong>Foto:</strong></p>
                    <img src="{{ asset('storage/' . $producto->foto) }}" alt="Foto del Producto" class="img-thumbnail" width="200">
                @endif
            </div>
        </div>

        <div class="mt-5">
            <h4>Detalles del Producto</h4>
            <table class="table table-bordered">
                <thead>
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
@endsection
