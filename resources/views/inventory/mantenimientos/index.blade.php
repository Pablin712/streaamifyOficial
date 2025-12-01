@extends('layouts.navigation')

@section('title', 'Mantenimientos')

@section('styles')
    <style>
        /* Estilos personalizados para la tabla de mantenimientos */
        #mantenimientos-table {
            border-radius: 8px;
            overflow: hidden;
        }

        #mantenimientos-table thead th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            color: white !important;
            text-align: center;
            padding: 14px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        #mantenimientos-table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        #mantenimientos-table tbody tr:nth-child(even) {
            background-color: white;
        }

        #mantenimientos-table tbody tr:hover {
            background-color: #e3f2fd !important;
            transform: scale(1.001);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
            transition: all 0.2s ease;
        }

        #mantenimientos-table td {
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
    <h1 class="mt-4">Mantenimientos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Mantenimientos</li>
    </ol>

    <!-- Descripción y alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <h3 class="text-primary">Gestión de Mantenimientos</h3>
        <p class="text-muted">Visualiza todos los mantenimientos registrados y realiza las acciones necesarias como editar o eliminar.</p>
    </div>

    <!-- Botón crear mantenimiento -->
    @if (Auth::user()->hasPermissionTo('mantenimientos.create'))
        <div class="mb-3">
            <a href="{{ route('mantenimientos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear Mantenimiento
            </a>
        </div>
    @endif

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Lista de Mantenimientos
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="mantenimientos-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="mantenimientos-table-search"
                           type="text"
                           placeholder="Buscar por cuenta, usuario, fecha..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="mantenimientos-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="mantenimientos-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="mantenimientos-table"
                       data-table="mantenimientos-table"
                       class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="0">
                                ID Cuenta
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="1">
                                Usuario
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="2">
                                Contraseña
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="3">
                                Fecha de Mantenimiento
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="4">
                                Descripción
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            @if (Auth::user()->hasAnyPermission(['mantenimientos.edit', 'mantenimientos.destroy']))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mantenimientos as $mantenimiento)
                            <tr>
                                <td>{{ $mantenimiento->idcue }}</td>
                                <td>{{ $mantenimiento->cuenta->usuariocue }}</td>
                                <td>{{ $mantenimiento->cuenta->contrasenacue }}</td>
                                <td>{{ $mantenimiento->fechaman }}</td>
                                <td>{{ $mantenimiento->descripcionman }}</td>
                                @if (Auth::user()->hasAnyPermission(['mantenimientos.edit', 'mantenimientos.destroy']))
                                    <td>
                                        <div class="action-buttons">
                                            @if (Auth::user()->hasPermissionTo('mantenimientos.edit'))
                                                <a href="{{ route('mantenimientos.edit', $mantenimiento->idman) }}" class="btn btn-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if (Auth::user()->hasPermissionTo('mantenimientos.destroy'))
                                                <form action="{{ route('mantenimientos.destroy', $mantenimiento->idman) }}" method="POST"
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
                    <div id="mantenimientos-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="mantenimientos-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
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
    console.log('Vista de mantenimientos cargada con Enhanced Table v2.0');
    console.log('Total de mantenimientos en la tabla:', {{ $mantenimientos->count() }});
</script>
@endsection
