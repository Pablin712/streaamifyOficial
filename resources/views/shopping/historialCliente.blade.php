@extends('layouts.cliente')
@section('sections')
    <div class="container px-5 my-5">
        <ul class="nav nav-tabs" id="historialTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ventas-tab" data-bs-toggle="tab" data-bs-target="#ventas" type="button" role="tab" aria-controls="ventas" aria-selected="true">Historial de Ventas</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="recargas-tab" data-bs-toggle="tab" data-bs-target="#recargas" type="button" role="tab" aria-controls="recargas" aria-selected="false">Historial de Recargas</button>
            </li>
        </ul>
        <div class="tab-content" id="historialTabsContent">
            <div class="tab-pane fade show active" id="ventas" role="tabpanel" aria-labelledby="ventas-tab">
                <h3 class="mt-4">Historial de Ventas</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aquí va el contenido del historial de ventas -->
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
                                        <span class="badge 
                                            @if ($recarga->estado->nombre === 'Pendiente') bg-warning 
                                            @elseif ($recarga->estado->nombre === 'Rechazado') bg-danger 
                                            @elseif ($recarga->estado->nombre === 'Aprobado') bg-success @endif">
                                            {{ ucfirst($recarga->estado->nombre) }}
                                        </span>
                                    </td>
                                    <td>{{ $recarga->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $recargas->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Obtener la pestaña activa del almacenamiento local
        var activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            var tab = new bootstrap.Tab(document.querySelector(activeTab));
            tab.show();
        }

        // Guardar la pestaña activa en el almacenamiento local al cambiar de pestaña
        var tabs = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabs.forEach(function (tab) {
            tab.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('activeTab', event.target.getAttribute('data-bs-target'));
            });
        });
    });
</script>
@endsection