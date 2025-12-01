@extends('layouts.navigation')

@section('title', 'Valores')

@section('styles')
    <style>
        /* Estilos personalizados para la tabla de valores */
        #valores-table {
            border-radius: 8px;
            overflow: hidden;
        }

        #valores-table thead th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            color: white !important;
            text-align: center;
            padding: 14px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        #valores-table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        #valores-table tbody tr:nth-child(even) {
            background-color: white;
        }

        #valores-table tbody tr:hover {
            background-color: #e3f2fd !important;
            transform: scale(1.001);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
            transition: all 0.2s ease;
        }

        #valores-table td {
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
    <h1 class="mt-4">Valores de servicios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Valores</li>
    </ol>

    <!-- Descripción y alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <h3 class="text-primary">Gestión de Valores</h3>
        <p class="text-muted">Revisa el inventario y crea nuevos posibles contratos para luego agregarlas a stock.</p>
    </div>

    <!-- Formulario de pantallas por servicio -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-sliders-h"></i> Configuración de Pantallas por Servicio
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('valores.updatePantallas') }}" method="POST">
                @csrf
                <div class="row">
                    @foreach ($serviciosPrincipales as $servicio)
                        <div class="col-md-3 mb-3">
                            <div class="card border-{{ $servicio->color }} shadow-sm">
                                <div class="card-body text-center">
                                    @if (Str::startsWith($servicio->icon, 'fa-'))
                                        <i class="fab {{ $servicio->icon }} fa-2x text-gray-300"></i>
                                    @else
                                        <img src="{{ asset('images/' . $servicio->icon) }}" width="40" height="40"
                                            alt="{{ $servicio->nombreser }}">
                                    @endif

                                    <h6 class="card-title mt-2">{{ $servicio->nombreser }}</h6>

                                    <div class="row">
                                        <div class="col-6">
                                            <label for="pantmin_{{ $servicio->idser }}" class="form-label small">Pant Min</label>
                                            <input type="number" step="1" class="form-control form-control-sm"
                                                id="pantmin_{{ $servicio->idser }}"
                                                name="pantallas[{{ $servicio->idser }}][pantmin]" required min="1">
                                        </div>
                                        <div class="col-6">
                                            <label for="pantmax_{{ $servicio->idser }}" class="form-label small">Pant Max</label>
                                            <input type="number" step="1" class="form-control form-control-sm"
                                                id="pantmax_{{ $servicio->idser }}"
                                                name="pantallas[{{ $servicio->idser }}][pantmax]" required min="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
                <div class="text-center mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Botones de acción -->
    @if (Auth::user()->hasPermissionTo('valores.create'))
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            {{-- Crear Valor --}}
            <a href="{{ route('valores.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Crear Valor
            </a>

            {{-- Corregir idval --}}
            <form action="{{ route('valores.corregir') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-tools"></i> Corregir ID de Valores
                </button>
            </form>

            {{-- Borrar innecesarios --}}
            <form action="{{ route('valores.deletegroup') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Borrar Innecesarios
                </button>
            </form>

            {{-- Descargar PDF --}}
            <a href="{{ route('valores.pdf') }}" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF - {{ \Carbon\Carbon::now()->format('Y-m-d') }}
            </a>

            {{-- Nuevo Servicio --}}
            @if (Auth::user()->hasPermissionTo('servicios.create'))
                <a href="{{ route('servicios.create') }}" class="btn btn-info text-white">
                    <i class="fas fa-plus-circle"></i> Nuevo Servicio
                </a>
            @endif

            {{-- Nuevo Proveedor --}}
            @if (Auth::user()->hasPermissionTo('proveedores.create'))
                <a href="{{ route('proveedores.create') }}" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i> Nuevo Proveedor
                </a>
            @endif
        </div>
    @endif

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Lista de Valores
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="valores-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="valores-table-search"
                           type="text"
                           placeholder="Buscar por ID, servicio, proveedor, tipo..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="valores-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="valores-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="valores-table"
                       data-table="valores-table"
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
                                Servicio
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="2">
                                Proveedor
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="3">
                                Costo
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="4">
                                Tipo
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="5">
                                Min
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="6">
                                Max
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="number" data-col="7">
                                Meses
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th data-type="string">Bot de códigos</th>
                            <th class="sortable" data-type="number" data-col="9">
                                Num cuentas
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            @if (Auth::user()->hasAnyPermission(['valores.edit', 'valores.destroy']))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($valores as $valor)
                            <tr>
                                <td>{{ $valor->idval }}</td>
                                <td>{{ $valor->idser }}</td>
                                <td>{{ $valor->proveedor->nombrepro }}</td>
                                <td>${{ number_format($valor->costoval, 2) }}</td>
                                <td>{{ $valor->tipoval }}</td>
                                <td>{{ $valor->pantminval }}</td>
                                <td>{{ $valor->pantmaxval }}</td>
                                <td>{{ $valor->mesesval }}</td>
                                <td>
                                    @if (!empty($valor->bot))
                                        <a href="{{ $valor->bot }}" target="_blank" class="text-primary">Ver Bot</a>
                                    @else
                                        <span class="text-danger">No disponible</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-success">{{ $valor->num_cuentas }}</span></td>
                                @if (Auth::user()->hasAnyPermission(['valores.edit', 'valores.destroy']))
                                    <td>
                                        <div class="action-buttons">
                                            @if (Auth::user()->hasPermissionTo('valores.edit'))
                                                <a href="{{ route('valores.edit', $valor->idval) }}" class="btn btn-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if (Auth::user()->hasPermissionTo('valores.destroy'))
                                                <form action="{{ route('valores.destroy', $valor->idval) }}" method="POST"
                                                    style="display: inline;"
                                                    onsubmit="return confirm('¿Estás seguro?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer: Info y paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="valores-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="valores-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
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
    console.log('Vista de valores cargada con Enhanced Table v2.0');
    console.log('Total de valores en la tabla:', {{ $valores->count() }});
</script>
@endsection
