@extends('layouts.navigation')

@section('title', 'Solicitudes de Recarga')

@section('styles')
    <style>
        /* Estilos personalizados para la tabla de recargas */
        #recargas-table {
            border-radius: 8px;
            overflow: hidden;
        }

        #recargas-table thead th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            color: white !important;
            text-align: center;
            padding: 14px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        #recargas-table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        #recargas-table tbody tr:nth-child(even) {
            background-color: white;
        }

        #recargas-table tbody tr:hover {
            background-color: #e3f2fd !important;
            transform: scale(1.001);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
            transition: all 0.2s ease;
        }

        #recargas-table td {
            text-align: center;
            padding: 12px 10px;
            vertical-align: middle;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            margin: 2px;
        }
    </style>
@endsection

@section('main')
<div class="container-fluid px-4">
    <!-- Título y breadcrumb -->
    <h1 class="mt-4">Solicitudes de Recarga</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Recargas</li>
    </ol>

    <!-- Descripción y alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <h3 class="text-primary">Gestión de Recargas</h3>
        <p class="text-muted">Visualiza todas las solicitudes de recarga enviadas por los clientes y realiza las acciones necesarias para aprobar o
        rechazar.</p>
    </div>

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Solicitudes de Recarga
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="recargas-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="recargas-table-search"
                           type="text"
                           placeholder="Buscar por cliente, banco, valor..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="recargas-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="recargas-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="recargas-table"
                       data-table="recargas-table"
                       class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="0">
                                ID
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="1">
                                Cliente
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="2">
                                Banco
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th data-type="string">Comprobante</th>
                            <th class="sortable" data-type="number" data-col="4">
                                Valor
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="5">
                                Fecha
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="6">
                                Estado
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recargas as $recarga)
                            <tr>
                                <td>{{ $recarga->idrec }}</td>
                                <td>{{ $recarga->cliente->nombrecli }}</td>
                                <td>{{ $recarga->banco->nombreban }}</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="me-2 badge bg-secondary">{{ $recarga->numcomprobante }}</span>
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalComprobante" data-id="{{ $recarga->idrec }}"
                                            data-img="{{ asset('storage/' . $recarga->foto) }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>${{ number_format($recarga->valor, 2) }}</td>
                                <td>{{$recarga->created_at}}</td>
                                <td>
                                    <span
                                        class="badge
                                        @if ($recarga->estado->nombre === 'Pendiente') bg-warning
                                        @elseif ($recarga->estado->nombre === 'Rechazado') bg-danger
                                        @elseif ($recarga->estado->nombre === 'Aprobado') bg-success @endif">
                                        {{ ucfirst($recarga->estado->nombre) }}
                                    </span>
                                    @if ($recarga->estado->nombre === 'Pendiente')
                                        @if (Auth::user()->hasPermissionTo('empleado.recargas.updateEstado'))
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
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer: Info y paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="recargas-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="recargas-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Único -->
    <div class="modal fade" id="modalComprobante" tabindex="-1" aria-labelledby="modalComprobanteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalComprobanteLabel">Comprobante de Recarga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="comprobanteImg" src="" alt="Comprobante" class="img-fluid"
                        style="max-width: 300px; height: auto;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

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
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modalComprobante = document.getElementById('modalComprobante');

        modalComprobante.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Botón que activó el modal
            var imageUrl = button.getAttribute('data-img'); // Obtener URL de la imagen

            var imgElement = document.getElementById('comprobanteImg');
            imgElement.src = imageUrl; // Actualizar la imagen en el modal
        });
    });
</script>

<script>
    console.log('Vista de recargas cargada con Enhanced Table v2.0');
    console.log('Total de recargas en la tabla:', {{ $recargas->count() }});
</script>
@endsection
