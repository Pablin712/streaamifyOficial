@extends('layouts.table')

@section('title')
    Solicitudes de Recarga
@endsection

@section('h1', 'Solicitudes de Recarga')
@section('breadcrumb')
    Recargas
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Visualiza todas las solicitudes de recarga enviadas por los clientes y realiza las acciones necesarias para aprobar o
        rechazar.</p>
@endsection

@section('tablename', 'Solicitudes de Recarga')

@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Banco</th>
                <th>Comprobante</th>
                <th>Valor</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($recargas as $recarga)
                <tr>
                    <td>{{ $recarga->idrec }}</td>
                    <td>{{ $recarga->cliente->nombrecli }}</td>
                    <td>{{ $recarga->banco->nombreban }}</td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalComprobante-{{ $recarga->idrec }}">
                            Ver Comprobante
                        </button>
                        <!-- Modal -->
                        <div class="modal fade" id="modalComprobante-{{ $recarga->idrec }}" tabindex="-1"
                            aria-labelledby="modalComprobanteLabel-{{ $recarga->idrec }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalComprobanteLabel-{{ $recarga->idrec }}">Comprobante
                                            de Recarga</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img src="{{ asset('public/storage/' . $recarga->foto) }}" alt="Comprobante"
                                            class="img-fluid">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>${{ number_format($recarga->valor, 2) }}</td>
                    <td>
                        <span
                            class="badge 
                            @if ($recarga->estado->nombre === 'Pendiente') bg-warning 
                            @elseif ($recarga->estado->nombre === 'Rechazado') bg-danger 
                            @elseif ($recarga->estado->nombre === 'Aprobado') bg-success @endif">
                            {{ ucfirst($recarga->estado->nombre) }}
                        </span>
                        @if ($recarga->estado->nombre === 'Pendiente')
                            <form action="{{ route('empleado.recargas.updateEstado', $recarga->idrec) }}" method="POST"
                                style="display: inline;">
                                @csrf
                                <input type="hidden" name="idestado" id="idestado">

                                <!-- Botón Aprobar -->
                                <button type="submit" class="btn btn-success btn-sm"
                                    onclick="return confirmarAccion('¿Estás seguro de que quieres aprobar esta recarga?', 'aprobado');">
                                    Aprobar
                                </button>

                                <!-- Botón Rechazar -->
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirmarAccion('¿Estás seguro de que quieres rechazar esta recarga?', 'rechazado');">
                                    Rechazar
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
@section('scripts')
    <script>
        function confirmarAccion(mensaje, estado) {
            const confirmacion = confirm(mensaje);
            if (confirmacion) {
                // Asigna el estado seleccionado al input oculto
                document.getElementById('idestado').value = estado;
                return true; // Permite enviar el formulario
            }
            return false; // Cancela el envío del formulario
        }
    </script>
@endsection
