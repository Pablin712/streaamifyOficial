@extends('layouts.cliente')
@section('menu')
    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('principal') }}#features">Fortalezas</a></li>
    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('principal') }}#combos">Combos</a>
    </li>
    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('shop') }}#catalog">Catálogo</a>
    </li>
@endsection
@section('title', 'Historial de Cliente')
@section('header')
    <div class="container text-center my-4">
        <h1 class="fw-bold">Actividad con Streamify</h1>
        <p class="text-muted">
            Aquí puedes consultar todas tus <strong>compras</strong>, <strong>pedidos</strong>, <strong>suscripciones
                activas</strong> y <strong>recargas</strong> realizadas en Streamify.
            Mantente al tanto del estado de tus transacciones y accede a los detalles de cada servicio adquirido.
        </p>
    </div>
    @if (session('renovacion_exitosa'))
        <div class="modal fade show d-block" id="renovacionExitosaModal" tabindex="-1"
            aria-labelledby="renovacionExitosaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="renovacionExitosaLabel">¡Renovación Exitosa!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            onclick="cerrarRenovacionModal()"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="bi bi-arrow-repeat text-primary" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Tu suscripción a <b>{{ session('renovacion_exitosa')['nombre'] }}</b> ha sido
                            renovada</h5>
                        <p class="text-muted">Nueva fecha de vencimiento:
                            <b>{{ session('renovacion_exitosa')['fecha_vencimiento'] }}</b>
                        </p>
                        <p>¡Sigue disfrutando de tu contenido sin interrupciones!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="cerrarRenovacionModal()">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function cerrarRenovacionModal() {
                document.getElementById('renovacionExitosaModal').classList.remove('show');
                document.getElementById('renovacionExitosaModal').classList.add('d-none');
            }
        </script>
    @endif
    @if (session('error'))
        <div class="modal fade show d-block" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="errorModalLabel">¡Error!</h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"
                            onclick="cerrarErrorModal()"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Ocurrió un problema</h5>
                        <p class="text-muted">{{ session('error') }}</p>
                        <p>No te preocupes, no se te descontó saldo, puedes verificar más tarde si ya hay stock,
                            o contacta con soporte para que agreguen más cuentas.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="cerrarErrorModal()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function cerrarErrorModal() {
                document.getElementById('errorModal').classList.remove('show');
                document.getElementById('errorModal').classList.add('d-none');
            }
        </script>
    @endif
@endsection
@section('sections')
    <div class="container px-5 my-5">
        <ul class="nav nav-tabs" id="historialTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="suscripciones-tab" data-bs-toggle="tab" data-bs-target="#suscripciones"
                    type="button" role="tab" aria-controls="suscripciones" aria-selected="true">
                    📺 Mis Suscripciones
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ventas-tab" data-bs-toggle="tab" data-bs-target="#ventas" type="button"
                    role="tab" aria-controls="ventas" aria-selected="false">🛒 Historial de Compras</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pedidos-tab" data-bs-toggle="tab" data-bs-target="#pedidos" type="button"
                    role="tab" aria-controls="pedidos" aria-selected="false">
                    📦 Historial de Pedidos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="recargas-tab" data-bs-toggle="tab" data-bs-target="#recargas" type="button"
                    role="tab" aria-controls="recargas" aria-selected="false">💰 Historial de Recargas</button>
            </li>
        </ul>
        <div class="tab-content" id="historialTabsContent">
            <div class="tab-pane fade show active" id="ventas" role="tabpanel" aria-labelledby="ventas-tab">
                <h3 class="mt-4">Historial de Compras</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID Compra</th>
                            <th>Fecha de Compra</th>
                            <th>Total Pagado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aquí va el contenido del historial de ventas -->
                        @foreach ($ventas as $venta)
                            <tr>
                                <td>{{ $venta->idven }}</td>
                                <td>{{ $venta->fechaven->format('d/m/Y') }}</td>
                                <td>${{ number_format($venta->detalles_venta->sum('montodet'), 2) }}</td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#detalleVentaModal-{{ $venta->idven }}">
                                        Ver Compra
                                    </button>
                                </td>
                            </tr>
                            <!-- Modal de Detalles de Venta -->
                            <div class="modal fade" id="detalleVentaModal-{{ $venta->idven }}" tabindex="-1"
                                aria-labelledby="detalleVentaLabel-{{ $venta->idven }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detalles de la Compra #{{ $venta->idven }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Fecha de Compra:</strong> {{ $venta->fechaven->format('d/m/Y') }}
                                            </p>
                                            <p><strong>Total Pagado:</strong>
                                                ${{ number_format($venta->detalles_venta->sum('montodet'), 2) }}</p>

                                            <h6>Detalles de los Perfiles Adquiridos:</h6>
                                            <ul class="list-group">
                                                @foreach ($venta->detalles_venta as $detalle)
                                                    <li class="list-group-item">
                                                        <strong>{{ $detalle->perfil->cuenta->valor->servicio->nombreser }}</strong><br>
                                                        <strong>Fecha de Vencimiento:</strong>
                                                        {{ $detalle->fechavendet->format('d/m/Y') }}<br>
                                                        <strong>Monto:</strong> ${{ number_format($detalle->montodet, 2) }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="recargas" role="tabpanel" aria-labelledby="recargas-tab">
                <h3 class="mt-4">Historial de Recargas</h3>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recargas as $recarga)
                                <tr>
                                    <td>${{ number_format($recarga->valor, 2) }}</td>
                                    <td>
                                        <span
                                            class="badge 
                                            @if ($recarga->estado->nombre === 'Pendiente') bg-warning 
                                            @elseif ($recarga->estado->nombre === 'Rechazado') bg-danger 
                                            @elseif ($recarga->estado->nombre === 'Aprobado') bg-success @endif">
                                            {{ ucfirst($recarga->estado->nombre) }}
                                        </span>
                                    </td>
                                    <td>{{ $recarga->created_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $recargas->links() }}
                </div>
            </div>
            <!-- 📦 Historial de Pedidos -->
            <div class="tab-pane fade" id="pedidos" role="tabpanel" aria-labelledby="pedidos-tab">
                <h3 class="mt-4">Historial de Pedidos</h3>
                <p class="text-muted">Aquí puedes ver los productos que has solicitado y el estado en el que se encuentran.
                </p>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Fecha del Pedido</th>
                            <th>Respuesta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pedidos as $pedido)
                            <tr>
                                <td>{{ $pedido->id }}</td>
                                <td>{{ $pedido->producto->nombrepro }}</td>
                                <td>{{ $pedido->producto->descripcionpro }}</td>
                                <td>
                                    <span
                                        class="badge 
                                        @if ($pedido->estado->nombre === 'Pendiente') bg-warning 
                                        @elseif ($pedido->estado->nombre === 'Rechazado') bg-danger 
                                        @elseif ($pedido->estado->nombre === 'Aprobado') bg-success @endif">
                                        {{ ucfirst($pedido->estado->nombre) }}
                                    </span>
                                </td>
                                <td>{{ $pedido->fechapedido->format('d/m/Y H:i') }}</td>
                                <td>{{ $pedido->respuesta ?? 'Sin respuesta' }}</td>
                            </tr>
                            {{-- @include('cliente.partials.modal_pedido', ['pedido' => $pedido]) --}}
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- 📺 Mis Suscripciones -->
            <div class="tab-pane fade" id="suscripciones" role="tabpanel" aria-labelledby="suscripciones-tab">
                <h3 class="mt-4">Mis Suscripciones Activas</h3>
                <p class="text-muted">Aquí puedes ver todas las suscripciones que tienes activas en Streamify.</p>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>🆔 Compra</th>
                            <th>📺 Streaming</th>
                            <th>🔑 Cuenta</th>
                            <th>🔒 Contraseña</th>
                            <th>👤 Perfil</th>
                            <th>📅 Fecha de Vencimiento</th>
                            <th>🔄 Acción</th> <!-- Nueva columna para el botón de renovar -->
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios_activos as $usuario)
                            <tr>
                                <td>{{ $usuario->idven }}</td>
                                <td>{{ $usuario->cuenta->valor->servicio->nombreser }}</td>
                                <td>{{ $usuario->cuenta->usuariocue }}</td>
                                <td>{{ $usuario->cuenta->contrasenacue }}</td>
                                @php
                                    // Construir el ID del perfil
                                    $idper = $usuario->cuenta->idcue . '.' . $usuario->perfil;

                                    // Buscar el pin del perfil
                                    $perfil = \App\Models\Perfil::where('idper', $idper)->first();
                                    $pinper = $perfil ? $perfil->pinper : 'N/A';
                                @endphp
                                <td>{{ $usuario->perfil }}: {{ $pinper }}</td>
                                <td>{{ \Carbon\Carbon::parse($usuario->fecha_vencimiento)->format('d/m/Y') }}</td>
                                <td>
                                    <!-- Botón que abre el modal correspondiente -->
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#renovarModal{{ $usuario->idven }}">
                                        🔄 Renovar
                                    </button>
                                </td>
                            </tr>
                            <!-- Modal de Confirmación de Renovación -->
                            <div class="modal fade" id="renovarModal{{ $usuario->idven }}" tabindex="-1"
                                aria-labelledby="renovarModalLabel{{ $usuario->idven }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="renovarModalLabel{{ $usuario->idven }}">
                                                Confirmar Renovación
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('cliente.renovar', $usuario->idven) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <h5>Selecciona los perfiles a renovar</h5>
                                                <ul class="list-group">
                                                    <!-- Listar los perfiles disponibles -->
                                                    @foreach ($usuario->venta->detalles_venta as $detalle)
                                                        <li
                                                            class="list-group-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <input class="form-check-input me-2" type="checkbox"
                                                                    name="detalles[]" value="{{ $detalle->iddet }}"
                                                                    id="detalle-{{ $detalle->iddet }}">
                                                                <label class="form-check-label"
                                                                    for="detalle-{{ $detalle->iddet }}">
                                                                    <strong>{{ $detalle->perfil->cuenta->valor->servicio->nombreser }}</strong><br>
                                                                    Cuenta:
                                                                    <strong>{{ $detalle->perfil->cuenta->usuariocue }}</strong><br>
                                                                    Perfil:
                                                                    <strong>{{ $detalle->perfil->numeroper }}</strong><br>
                                                                    Fecha de Vencimiento:
                                                                    <strong>{{ \Carbon\Carbon::parse($detalle->fechavendet)->addMonth()->format('d/m/Y') }}</strong>
                                                                </label>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">Confirmar
                                                    Renovación</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var renovarModal = document.getElementById("renovarModal");
            renovarModal.addEventListener("show.bs.modal", function(event) {
                var button = event.relatedTarget;
                var id = button.getAttribute("data-id");

                var form = document.getElementById("renovarForm");
                form.setAttribute("formaction", "{{ route('cliente.renovar', '') }}" + id);
            });
        });
    </script>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener la pestaña activa del almacenamiento local
            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                var tab = new bootstrap.Tab(document.querySelector(activeTab));
                tab.show();
            }

            // Guardar la pestaña activa en el almacenamiento local al cambiar de pestaña
            var tabs = document.querySelectorAll('button[data-bs-toggle="tab"]');
            tabs.forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(event) {
                    localStorage.setItem('activeTab', event.target.getAttribute('data-bs-target'));
                });
            });
        });
    </script>
@endsection
