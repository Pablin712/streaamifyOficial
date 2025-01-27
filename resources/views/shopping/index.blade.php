@extends('layouts.cliente')
@section('title', 'Shop Streamify')
@section('styles')
    <style>
        .section-link {
            cursor: pointer;
            color: #007bff;
            text-decoration: none;
            margin-right: 15px;
        }

        .section-link:hover {
            text-decoration: underline;
        }

        .star {
            color: gold;
        }

        .gray-star {
            color: lightgray;
        }

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
    <div class="container px-5">
        <div class="row gx-5 align-items-center">
            <div class="col-lg-6">
                <div class="mb-5 mb-lg-0 text-center text-lg-start">
                    <h1 class="display-1 lh-1 mb-3">Descubre Nuestro Catálogo Exclusivo</h1>
                    <p class="lead fw-normal text-muted mb-5">Explora una selección exclusiva de productos diseñados para
                        mejorar tu experiencia de streaming.</p>

                    <!-- Links para navegación -->
                    <div>
                        <a class="section-link" href="#inmediata-individual">Entrega Inmediata - Individual</a>
                        <a class="section-link" href="#combos">Combos</a>
                        <a class="section-link" href="#pedidos">Pedidos</a>
                        <a class="section-link" href="#personalizadas">Personalizadas</a>
                        <a class="section-link" href="#completos">Completos</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="text-center">
                    <img src="{{ asset('images/cuadrado/combos/35.png') }}" alt="Promoción de Streaming"
                        class="rounded shadow-lg" style="width: 80%; max-width: 600px; height: auto; object-fit: cover;" />
                </div>
            </div>
        </div>
    </div>
@endsection
@section('sections')

    <!-- Sección Entrega Inmediata - Individual -->
    <section id="inmediata-individual" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Entrega Inmediata - Individual</h2>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-5">
                @foreach ($productosInmediataIndividual as $producto)
                    <div class="col mb-5">
                        <div class="card h-100">
                            <!-- Imagen del producto -->
                            <img class="card-img-top" src="{{ asset($producto->foto) }}" alt="{{ $producto->nombrepro }}" />

                            <!-- Detalles del producto -->
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bolder">{{ $producto->nombrepro }}</h5>

                                <!-- Estrellas -->
                                <div>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="bi bi-star{{ $i <= $producto->estrellaspro ? ' star' : '-gray gray-star' }}"></i>
                                    @endfor
                                </div>

                                <!-- Precio -->
                                <p class="text-muted">${{ number_format($producto->preciopro, 2) }}</p>

                                <!-- Botones -->
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- Botón Info -->
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#infoModal{{ $producto->id }}">
                                        <i class="bi bi-info-circle"></i>
                                    </button>

                                    <!-- Botón Añadir al Carrito -->
                                    <form action="#" method="POST"> {{-- {{ route('cart.add', $producto->id) }} --}}
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                    </form>

                                    <!-- Comprar para Entrega Inmediata -->
                                    <form action="#" method="POST"> {{-- {{ route('comprar', $producto->id) }} --}}
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Comprar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Sección Combos -->
    <section id="combos" class="py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Combos</h2>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-5">
                @foreach ($productosCombos as $producto)
                    <div class="col mb-5">
                        <div class="card h-100">
                            <img class="card-img-top" src="{{ asset($producto->foto) }}" alt="{{ $producto->nombrepro }}" />
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bolder">{{ $producto->nombrepro }}</h5>
                                <div>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="bi bi-star{{ $i <= $producto->estrellaspro ? ' star' : '-gray gray-star' }}"></i>
                                    @endfor
                                </div>
                                <p class="text-muted">${{ number_format($producto->preciopro, 2) }}</p>
                                <!-- Botón Info -->
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#infoModal{{ $producto->id }}">
                                    <i class="bi bi-info-circle"></i>
                                </button>

                                <!-- Botón Añadir al Carrito -->
                                <form action="#" method="POST"> {{-- {{ route('cart.add', $producto->id) }} --}}
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </form>
                                <!-- Hacer Pedido para otros -->
                                <form action="#" method="POST"> {{-- {{ route('pedido', $producto->id) }} --}}
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm">Pedir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Sección Pedidos -->
    <section id="pedidos" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Pedidos</h2>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-5">
                @foreach ($productosPedidos as $producto)
                    <div class="col mb-5">
                        <div class="card h-100">
                            <img class="card-img-top" src="{{ asset($producto->foto) }}"
                                alt="{{ $producto->nombrepro }}" />
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bolder">{{ $producto->nombrepro }}</h5>
                                <div>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="bi bi-star{{ $i <= $producto->estrellaspro ? ' star' : '-gray gray-star' }}"></i>
                                    @endfor
                                </div>
                                <p class="text-muted">${{ number_format($producto->preciopro, 2) }}</p>
                                <!-- Botón Info -->
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#infoModal{{ $producto->id }}">
                                    <i class="bi bi-info-circle"></i>
                                </button>

                                <!-- Botón Añadir al Carrito -->
                                <form action="#" method="POST"> {{-- {{ route('cart.add', $producto->id) }} --}}
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </form>
                                <!-- Hacer Pedido para otros -->
                                <form action="#" method="POST"> {{-- {{ route('pedido', $producto->id) }} --}}
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm">Pedir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Sección Personalizadas -->
    <section id="personalizadas" class="py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Personalizadas</h2>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-5">
                @foreach ($productosPersonalizados as $producto)
                    <div class="col mb-5">
                        <div class="card h-100">
                            <img class="card-img-top" src="{{ asset($producto->foto) }}"
                                alt="{{ $producto->nombrepro }}" />
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bolder">{{ $producto->nombrepro }}</h5>
                                <div>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="bi bi-star{{ $i <= $producto->estrellaspro ? ' star' : '-gray gray-star' }}"></i>
                                    @endfor
                                </div>
                                <p class="text-muted">${{ number_format($producto->preciopro, 2) }}</p>
                                <!-- Botón Info -->
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#infoModal{{ $producto->id }}">
                                    <i class="bi bi-info-circle"></i>
                                </button>

                                <!-- Botón Añadir al Carrito -->
                                <form action="#" method="POST"> {{-- {{ route('cart.add', $producto->id) }} --}}
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </form>
                                <!-- Hacer Pedido para otros -->
                                <form action="#" method="POST"> {{-- {{ route('pedido', $producto->id) }} --}}
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm">Pedir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Sección Completos -->
    <section id="completos" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Completos</h2>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-5">
                @foreach ($productosCompletos as $producto)
                    <div class="col mb-5">
                        <div class="card h-100">
                            <img class="card-img-top" src="{{ asset($producto->foto) }}"
                                alt="{{ $producto->nombrepro }}" />
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bolder">{{ $producto->nombrepro }}</h5>
                                <div>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="bi bi-star{{ $i <= $producto->estrellaspro ? ' star' : '-gray gray-star' }}"></i>
                                    @endfor
                                </div>
                                <p class="text-muted">${{ number_format($producto->preciopro, 2) }}</p>
                                <!-- Botón Info -->
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#infoModal{{ $producto->id }}">
                                    <i class="bi bi-info-circle"></i>
                                </button>

                                <!-- Botón Añadir al Carrito -->
                                <form action="#" method="POST"> {{-- {{ route('cart.add', $producto->id) }} --}}
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </form>
                                <!-- Hacer Pedido para otros -->
                                <form action="#" method="POST"> {{-- {{ route('pedido', $producto->id) }} --}}
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm">Pedir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
@section('scripts')

@endsection
