@extends('layouts.navigation')
@section('title', 'Historial')

@section('main')
<div class="container-fluid px-4">
    <h1 class="mt-4">Historial</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Historial</li>
    </ol>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (Auth::user()->hasPermissionTo('historial.clear'))
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Borrar Historial por Rango de Fechas</h5>
                <form method="POST" action="{{ route('historial.clear') }}">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Fecha Inicio:</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Fecha Fin:</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Está seguro de borrar el historial en este rango de fechas?');">
                                <i class="fas fa-trash"></i> Borrar Historial
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-history"></i> Historial de Acciones
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="historial-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="historial-table-search"
                           type="text"
                           placeholder="Buscar por acción, descripción, empleado..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="historial-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="historial-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="historial-table"
                       data-table="historial-table"
                       data-server-side="true"
                       data-search-url="{{ route('historial') }}"
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
                                Acción
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="2">
                                Descripción
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="3">
                                Realizado Por
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="date" data-col="4">
                                Fecha
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center p-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer: Info y paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="historial-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="historial-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
<script>
    console.log('Vista de historial cargada con Enhanced Table v2.0 Server-side');
</script>
@endsection
