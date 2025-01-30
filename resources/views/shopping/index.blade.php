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
    @if (session('compra_exitosa'))
        <div class="modal fade show d-block" id="compraExitosaModal" tabindex="-1" aria-labelledby="compraExitosaLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="compraExitosaLabel">¡Compra Exitosa!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            onclick="cerrarModal()"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">{{ session('compra_exitosa')['nombre'] }}</h5>
                        <p class="text-muted">Precio: ${{ number_format(session('compra_exitosa')['precio'], 2) }}</p>
                        <p>Tu compra ha sido procesada con éxito.</p>

                        <!-- Mostrar los servicios adquiridos -->
                        <div class="mt-3">
                            <h6>Detalles de los servicios adquiridos:</h6>
                            <ul class="list-group">
                                @foreach (session('compra_exitosa')['servicios'] as $servicio)
                                    <li class="list-group-item">
                                        <pre>{{ $servicio }}</pre>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="cerrarModal()">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function cerrarModal() {
                document.getElementById('compraExitosaModal').classList.remove('show');
                document.getElementById('compraExitosaModal').classList.add('d-none');
            }
        </script>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('pedido_registrado'))
        <div class="modal fade show d-block" id="pedidoRegistradoModal" tabindex="-1"
            aria-labelledby="pedidoRegistradoLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pedidoRegistradoLabel">¡Pedido Registrado!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            onclick="cerrarPedidoModal()"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="bi bi-info-circle text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">{{ session('pedido_registrado')['nombre'] }}</h5>
                        <p class="text-muted">Precio estimado:
                            ${{ number_format(session('pedido_registrado')['precio'], 2) }}</p>
                        <p>Tu pedido ha sido registrado con éxito. Nos pondremos en contacto contigo pronto. 
                            Puedes revisar tu perfil de registro de pedidos y compras para estar al tanto.</p>
                        <p>Estado actual del pedido: <b>Pendiente</b></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="cerrarPedidoModal()">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function cerrarPedidoModal() {
                document.getElementById('pedidoRegistradoModal').classList.remove('show');
                document.getElementById('pedidoRegistradoModal').classList.add('d-none');
            }
        </script>
    @endif
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
                                <!-- Estado del producto -->
                                @if ($producto->activo)
                                    <span class="badge bg-success">En Stock</span>
                                @else
                                    <span class="badge bg-danger">Agotado</span>
                                @endif
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
                                    <!-- Modal de Información del Producto -->
                                    <div class="modal fade" id="infoModal{{ $producto->id }}" tabindex="-1"
                                        aria-labelledby="infoModalLabel{{ $producto->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="infoModalLabel{{ $producto->id }}">
                                                        {{ $producto->nombrepro }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <!-- Imagen del producto -->
                                                    <img src="{{ asset($producto->foto) }}"
                                                        alt="{{ $producto->nombrepro }}" class="img-fluid rounded mb-3"
                                                        style="max-width: 100%; height: auto;">

                                                    <!-- Descripción del producto -->
                                                    <p class="text-muted">{{ $producto->descripcionpro }}</p>

                                                    <!-- Precio -->
                                                    <h5 class="text-primary">Precio:
                                                        ${{ number_format($producto->preciopro, 2) }}</h5>

                                                    <!-- Estrellas (Valoración) -->
                                                    <div class="mb-3">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <i
                                                                class="bi bi-star{{ $i <= $producto->estrellaspro ? ' star' : '-gray gray-star' }}"></i>
                                                        @endfor
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botón Añadir al Carrito -->
                                    <form action="{{ route('cart.add', $producto->id) }}" method="POST">
                                        {{--  --}}
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                    </form>

                                    <!-- Comprar para Entrega Inmediata -->
                                    <form action="{{ route('comprar', $producto->id) }}" method="POST">
                                        {{-- --}}
                                        @csrf
                                        <!-- Botón Comprar -->
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#confirmCompraModal{{ $producto->id }}">
                                            Comprar
                                        </button>
                                    </form>
                                    <!-- Modal de Confirmación de Compra -->
                                    <div class="modal fade" id="confirmCompraModal{{ $producto->id }}" tabindex="-1"
                                        aria-labelledby="confirmCompraLabel{{ $producto->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="confirmCompraLabel{{ $producto->id }}">
                                                        Confirmar Compra</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img src="{{ asset($producto->foto) }}"
                                                        alt="{{ $producto->nombrepro }}" class="img-fluid rounded mb-3"
                                                        style="max-width: 100px;">
                                                    <h5>{{ $producto->nombrepro }}</h5>
                                                    <p class="text-muted">Precio:
                                                        ${{ number_format($producto->preciopro, 2) }}</p>
                                                    <p>¿Deseas confirmar la compra?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('comprar', $producto->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary">Confirmar
                                                            Compra</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
            <h2 class="text-center fw-bold mb-5">Entrega Inmediata - Combos</h2>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-5">
                @foreach ($productosCombos as $producto)
                    <div class="col mb-5">
                        <div class="card h-100">
                            <img class="card-img-top" src="{{ asset($producto->foto) }}"
                                alt="{{ $producto->nombrepro }}" />
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bolder">{{ $producto->nombrepro }}</h5>
                                <!-- Estado del producto -->
                                @if ($producto->activo)
                                    <span class="badge bg-success">En Stock</span>
                                @else
                                    <span class="badge bg-danger">Agotado</span>
                                @endif
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
                                <!-- Modal de Información del Producto -->
                                <div class="modal fade" id="infoModal{{ $producto->id }}" tabindex="-1"
                                    aria-labelledby="infoModalLabel{{ $producto->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="infoModalLabel{{ $producto->id }}">
                                                    {{ $producto->nombrepro }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <!-- Imagen del producto -->
                                                <img src="{{ asset($producto->foto) }}" alt="{{ $producto->nombrepro }}"
                                                    class="img-fluid rounded mb-3" style="max-width: 100%; height: auto;">

                                                <!-- Descripción del producto -->
                                                <p class="text-muted">{{ $producto->descripcionpro }}</p>

                                                <!-- Precio -->
                                                <h5 class="text-primary">Precio:
                                                    ${{ number_format($producto->preciopro, 2) }}</h5>

                                                <!-- Estrellas (Valoración) -->
                                                <div class="mb-3">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i
                                                            class="bi bi-star{{ $i <= $producto->estrellaspro ? ' star' : '-gray gray-star' }}"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón Añadir al Carrito -->
                                <form action="{{ route('cart.add', $producto->id) }}" method="POST">
                                    {{--  --}}
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </form>
                                <!-- Hacer Pedido para otros -->
                                <form action="{{ route('comprar', $producto->id) }}" method="POST">
                                    {{-- {{ route('pedido', $producto->id) }} --}}
                                    @csrf
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#confirmCompraModal{{ $producto->id }}">
                                        Comprar
                                    </button>
                                </form>
                                <!-- Modal de Confirmación de Compra -->
                                <div class="modal fade" id="confirmCompraModal{{ $producto->id }}" tabindex="-1"
                                    aria-labelledby="confirmCompraLabel{{ $producto->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="confirmCompraLabel{{ $producto->id }}">
                                                    Confirmar Compra</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="{{ asset($producto->foto) }}" alt="{{ $producto->nombrepro }}"
                                                    class="img-fluid rounded mb-3" style="max-width: 100px;">
                                                <h5>{{ $producto->nombrepro }}</h5>
                                                <p class="text-muted">Precio:
                                                    ${{ number_format($producto->preciopro, 2) }}</p>
                                                <p>¿Deseas confirmar la compra?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('comprar', $producto->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary">Confirmar
                                                        Compra</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                <!-- Modal de Información del Producto -->
                                <div class="modal fade" id="infoModal{{ $producto->id }}" tabindex="-1"
                                    aria-labelledby="infoModalLabel{{ $producto->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="infoModalLabel{{ $producto->id }}">
                                                    {{ $producto->nombrepro }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <!-- Imagen del producto -->
                                                <img src="{{ asset($producto->foto) }}" alt="{{ $producto->nombrepro }}"
                                                    class="img-fluid rounded mb-3" style="max-width: 100%; height: auto;">

                                                <!-- Descripción del producto -->
                                                <p class="text-muted">{{ $producto->descripcionpro }}</p>

                                                <!-- Precio -->
                                                <h5 class="text-primary">Precio:
                                                    ${{ number_format($producto->preciopro, 2) }}</h5>

                                                <!-- Estrellas (Valoración) -->
                                                <div class="mb-3">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i
                                                            class="bi bi-star{{ $i <= $producto->estrellaspro ? ' star' : '-gray gray-star' }}"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón Añadir al Carrito -->
                                <form action="#" method="POST"> {{-- {{ route('cart.add', $producto->id) }} --}}
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </form>
                                <!-- Hacer Pedido para otros -->
                                <form action="{{ route('comprar', $producto->id) }}" method="POST">
                                    {{-- {{ route('pedido', $producto->id) }} --}}
                                    @csrf
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#confirmCompraModal{{ $producto->id }}">
                                        Pedir
                                    </button>
                                </form>
                                <!-- Modal de Confirmación de Compra -->
                                <div class="modal fade" id="confirmCompraModal{{ $producto->id }}" tabindex="-1"
                                    aria-labelledby="confirmCompraLabel{{ $producto->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="confirmCompraLabel{{ $producto->id }}">
                                                    Confirmar Compra</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="{{ asset($producto->foto) }}" alt="{{ $producto->nombrepro }}"
                                                    class="img-fluid rounded mb-3" style="max-width: 100px;">
                                                <h5>{{ $producto->nombrepro }}</h5>
                                                <p class="text-muted">Precio:
                                                    ${{ number_format($producto->preciopro, 2) }}</p>
                                                <p>¿Deseas confirmar la compra?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('comprar', $producto->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary">Confirmar
                                                        Pedido</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                <!-- Modal de Información del Producto -->
                                <div class="modal fade" id="infoModal{{ $producto->id }}" tabindex="-1"
                                    aria-labelledby="infoModalLabel{{ $producto->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="infoModalLabel{{ $producto->id }}">
                                                    {{ $producto->nombrepro }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <!-- Imagen del producto -->
                                                <img src="{{ asset($producto->foto) }}" alt="{{ $producto->nombrepro }}"
                                                    class="img-fluid rounded mb-3" style="max-width: 100%; height: auto;">

                                                <!-- Descripción del producto -->
                                                <p class="text-muted">{{ $producto->descripcionpro }}</p>

                                                <!-- Precio -->
                                                <h5 class="text-primary">Precio:
                                                    ${{ number_format($producto->preciopro, 2) }}</h5>

                                                <!-- Estrellas (Valoración) -->
                                                <div class="mb-3">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i
                                                            class="bi bi-star{{ $i <= $producto->estrellaspro ? ' star' : '-gray gray-star' }}"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                <!-- Modal de Información del Producto -->
                                <div class="modal fade" id="infoModal{{ $producto->id }}" tabindex="-1"
                                    aria-labelledby="infoModalLabel{{ $producto->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="infoModalLabel{{ $producto->id }}">
                                                    {{ $producto->nombrepro }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <!-- Imagen del producto -->
                                                <img src="{{ asset($producto->foto) }}"
                                                    alt="{{ $producto->nombrepro }}" class="img-fluid rounded mb-3"
                                                    style="max-width: 100%; height: auto;">

                                                <!-- Descripción del producto -->
                                                <p class="text-muted">{{ $producto->descripcionpro }}</p>

                                                <!-- Precio -->
                                                <h5 class="text-primary">Precio:
                                                    ${{ number_format($producto->preciopro, 2) }}</h5>

                                                <!-- Estrellas (Valoración) -->
                                                <div class="mb-3">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i
                                                            class="bi bi-star{{ $i <= $producto->estrellaspro ? ' star' : '-gray gray-star' }}"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

    {{-- carrito flotante --}}
    <div class="cart-float">
        <button type="button" class="btn rounded-circle position-relative" id="cartButton" data-bs-toggle="modal"
            data-bs-target="#cartModal">
            <i class="bi bi-cart"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">
                0
            </span>
        </button>
    </div>

    {{-- modal de carrito --}}
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Tu Carrito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group" id="cart-items">
                        <!-- Aquí se llenarán los productos del carrito -->
                        <li class="list-group-item text-center text-muted">El carrito está vacío</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Seguir Comprando</button>
                    {{-- <a href="{{ route('cart.checkout') }}" class="btn btn-primary">Finalizar Compra</a> --}}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        let cart = @json($cart); // Inicializar el carrito con los datos de la sesión

        // Guardar el carrito en localStorage
        localStorage.setItem('cart', JSON.stringify(cart));

        // Añadir producto al carrito
        function addToCart(productId) {
            fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    cart = data.cart;
                    localStorage.setItem('cart', JSON.stringify(cart)); // Guardar en localStorage
                    updateCartUI();
                })
                .catch(error => console.error('Error:', error));
        }

        // Actualizar la interfaz del carrito
        function updateCartUI() {
            const cartItems = document.getElementById('cart-items');
            const cartCount = document.getElementById('cart-count');
            cartItems.innerHTML = '';

            let totalItems = 0;

            if (Object.keys(cart).length === 0) {
                cartItems.innerHTML = '<li class="list-group-item text-center text-muted">El carrito está vacío</li>';
                if (cartCount) {
                    cartCount.textContent = '0';
                }
                return;
            }

            Object.values(cart).forEach(item => {
                totalItems += item.cantidad;
                let listItem = document.createElement('li');
                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                listItem.innerHTML = `
            <img src="${item.foto}" alt="${item.nombre}" style="width: 50px;">
            <span>${item.nombre} (x${item.cantidad})</span>
            <span class="badge bg-primary rounded-pill">$${(item.precio * item.cantidad).toFixed(2)}</span>
            <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">🗑</button>
        `;
                cartItems.appendChild(listItem);
            });

            if (cartCount) {
                cartCount.textContent = totalItems;
            }
        }

        // Eliminar un producto del carrito
        function removeFromCart(productId) {
            delete cart[productId];
            localStorage.setItem('cart', JSON.stringify(cart)); // Actualizar en localStorage
            updateCartUI();

            // Enviar solicitud AJAX al servidor para actualizar la sesión
            fetch(`/cart/remove/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Asegúrate de incluir el token CSRF
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Producto eliminado de la sesión');
                    } else {
                        console.error('Error al eliminar el producto de la sesión');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Llamar a updateCartUI cuando la página se cargue
        document.addEventListener('DOMContentLoaded', updateCartUI);
    </script>
@endsection
