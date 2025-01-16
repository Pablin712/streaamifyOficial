@extends('layouts.cliente')
@section('title', 'Shop Streamify')
@section('styles')
    <style>
        .cart-float {
            position: fixed;
            bottom: 20px;
            /* Distancia del borde inferior */
            right: 20px;
            /* Distancia del borde derecho */
            z-index: 1000;
            /* Asegúrate de que esté sobre otros elementos */
        }

        .cart-float .btn {
            width: 60px;
            height: 60px;
            background-color: #274698;
            /* Color del fondo del botón */
            color: white;
            /* Color del icono */
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .cart-float .btn:hover {
            transform: scale(1.1);
            /* Efecto de zoom al pasar el mouse */
            background-color: #1d3a8c;
            /* Color de fondo al hacer hover */
            color: #f1f1f1;
            /* Cambiar color del icono */
        }

        .cart-float .btn i {
            font-size: 1.5rem;
            /* Tamaño del icono */
        }

        #cart-count {
            font-size: 0.75rem;
            padding: 5px 8px;
        }
    </style>
@endsection
@section('header')
    <!-- Mensaje de inicio de sesión exitoso -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessage">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (!Auth::guard('cliente')->check())
        <!-- Header-->
        <div class="container px-5">
            <div class="row gx-5 align-items-center">
                <div class="col-lg-6">
                    <div class="mb-5 mb-lg-0 text-center text-lg-start">
                        <h1 class="display-1 lh-1 mb-3">Descubre Nuestro Catálogo Exclusivo</h1>
                        <p class="lead fw-normal text-muted mb-5">Explora una selección exclusiva de productos diseñados para
                            mejorar tu experiencia de streaming.</p>
                    </div>
                </div>
                <div class="col-lg-6 d-flex align-items-center justify-content-center">
                    <!-- Reemplazamos el dispositivo con una imagen promocional -->
                    <div class="masthead-device-mockup">
                        <div class="text-center">
                            <!-- Aquí va la imagen promocional -->
                            <img {{-- src="{{ asset('images/tik tok miniatura.png') }}" --}} src="{{ asset('images/cuadrado/combos/33.png') }}"
                                alt="Promoción de Streaming" class="rounded shadow-lg"
                                style="width: 80%; max-width: 600px; height: auto; object-fit: cover;" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('sections')
    <!-- Catálogo Section -->
    <section id="catalog" class="py-5 bg-light">
        <div class="container px-4 px-lg-5 mt-5">
            <h2 class="text-center fw-bold mb-5">Catálogo de Productos</h2>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <div class="col mb-5">
                    <div class="card h-100">
                        <img class="card-img-top" src="{{ asset('images/productos/producto1.png') }}" alt="Producto 1" />
                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bolder">Producto 1</h5>
                            <p class="text-muted">$10.00</p>
                        </div>
                    </div>
                </div>
                <div class="col mb-5">
                    <div class="card h-100">
                        <img class="card-img-top" src="{{ asset('images/productos/producto2.png') }}" alt="Producto 2" />
                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bolder">Producto 2</h5>
                            <p class="text-muted">$15.00</p>
                        </div>
                    </div>
                </div>
                <div class="col mb-5">
                    <div class="card h-100">
                        <img class="card-img-top" src="{{ asset('images/productos/producto3.png') }}" alt="Producto 3" />
                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bolder">Producto 3</h5>
                            <p class="text-muted">$12.50</p>
                        </div>
                    </div>
                </div>
                <div class="col mb-5">
                    <div class="card h-100">
                        <img class="card-img-top" src="{{ asset('images/productos/producto4.png') }}" alt="Producto 4" />
                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bolder">Producto 4</h5>
                            <p class="text-muted">$8.00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="carrito">
        <div id="cart-float" class="cart-float">
            <a href="#" {{-- {{ route('cart') }} --}}
                class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center">
                <i class="bi bi-cart3 fs-4"></i>
                {{-- <span id="cart-count"
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ Cart::count() ?? 0 }}
                </span> --}}
            </a>
        </div>
    </section>
@endsection
@section('scripts')

@endsection
