@extends('layouts.table')
@section('title')
    Proveedores
@endsection
@section('h1', 'Proveedores')
@section('breadcrumb')
    Proveedores
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Agrega un nuevo proveedor al negocio, para poder contactarlo y adquirir sus cuentas.</p>
@endsection
@section('tablename', 'Proveedores')
@section('table1')
    <h1>Proveedores</h1>
    @if (Auth::user()->hasPermissionTo('proveedores.create'))
        <a href="{{ route('proveedores.create') }}" class="btn btn-primary mb-3">Crear Proveedor</a>
    @endif
    <!-- Filtros con Checkboxes -->
    <div class="mb-3">
        <label><input type="checkbox" class="column-toggle" data-column="3" checked> Total de cuentas</label>
        <label><input type="checkbox" class="column-toggle" data-column="4"> Netflix</label>
        <label><input type="checkbox" class="column-toggle" data-column="5"> Disney Premium</label>
        <label><input type="checkbox" class="column-toggle" data-column="6"> Disney</label>
        <label><input type="checkbox" class="column-toggle" data-column="7"> MAX</label>
        <label><input type="checkbox" class="column-toggle" data-column="8"> Prime</label>
        <label><input type="checkbox" class="column-toggle" data-column="9"> Spotify</label>
        <label><input type="checkbox" class="column-toggle" data-column="10"> Otros</label>
        <label><input type="checkbox" class="column-toggle" data-column="11"> Se debe</label>
        <label><input type="checkbox" class="column-toggle" data-column="12"> Pagar en el mes</label>
    </div>

    <!-- Controles de búsqueda y registros -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="proveedores-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="proveedores-table-search"
                   type="text"
                   placeholder="Buscar proveedor..."
                   class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="proveedores-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="proveedores-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="20">20 registros</option>
                <option value="50">50 registros</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="proveedores-table" data-table="proveedores-table" class="table table-striped table-bordered">
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
                    Nombre
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="2">
                    Teléfono
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="3">
                    Total de cuentas
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="4">
                    Netflix
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="5">
                    Disney Premium
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="6">
                    Disney
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="7">
                    MAX
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="8">
                    Prime
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="9">
                    Spotify
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="10">
                    Otros
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="11">
                    Se debe
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="12">
                    Pagar en el mes
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                @if (Auth::user()->hasAnyPermission(['proveedores.edit', 'proveedores.destroy']))
                    <th data-type="actions">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($proveedores as $proveedor)
                <tr>
                    <td>{{ $proveedor->idpro }}</td>
                    <td>{{ $proveedor->nombrepro }}</td>
                    <td>{{ $proveedor->telefonopro }}</td>
                    <td>{{ $proveedor->total_cuentas }}</td>
                    <td>{{ $proveedor->cuentas_netflix }}</td>
                    <td>{{ $proveedor->cuentas_disney_p }}</td>
                    <td>{{ $proveedor->cuentas_disney_s }}</td>
                    <td>{{ $proveedor->cuentas_max }}</td>
                    <td>{{ $proveedor->cuentas_prime_v }}</td>
                    <td>{{ $proveedor->cuentas_spotify }}</td>
                    <td>{{ $proveedor->otras_cuentas }}</td>
                    <td>
                        @if ($proveedor->se_debe > 0)
                            <span class="badge bg-danger">
                                ${{ $proveedor->se_debe }}
                            </span>
                        @else
                            <span class="badge bg-success">$0</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-warning">
                            ${{ $proveedor->se_debe_mes }}
                        </span>
                    </td>
                    @if (Auth::user()->hasAnyPermission(['proveedores.edit', 'proveedores.destroy']))
                        <td>
                            @if (Auth::user()->hasPermissionTo('proveedores.edit'))
                                <a href="{{ route('proveedores.edit', $proveedor->idpro) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if (Auth::user()->hasPermissionTo('proveedores.destroy'))
                                <form action="{{ route('proveedores.destroy', $proveedor->idpro) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle"
                                        onclick="return confirm('¿Estás seguro?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <!-- Información de paginación y controles -->
    <div class="row mt-3 align-items-center">
        <div class="col-md-6 col-12 mb-2 mb-md-0">
            <div id="proveedores-table-row-info" class="text-muted"></div>
        </div>
        <div class="col-md-6 col-12">
            <div id="proveedores-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ocultar columnas por defecto (excepto Total de cuentas y Acciones)
            const table = document.querySelector('#datatablesSimpl');
            const checkboxes = document.querySelectorAll('.column-toggle');

            checkboxes.forEach(checkbox => {
                const column = checkbox.dataset.column;
                const isChecked = checkbox.checked;

                // Mostrar solo la columna "Total de cuentas" (data-column="3") al cargar la página
                if (column !== "3") {
                    checkbox.checked = false; // Desmarcar los checkboxes excepto el de Total de cuentas
                }

                toggleColumn(table, column, checkbox.checked);

                // Agregar evento para mostrar/ocultar columnas
                checkbox.addEventListener('change', function() {
                    toggleColumn(table, column, this.checked);
                });
            });

            function toggleColumn(table, columnIndex, show) {
                const rows = table.querySelectorAll('tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('th, td');
                    if (cells[columnIndex]) {
                        cells[columnIndex].style.display = show ? '' : 'none';
                    }
                });
            });
        });
    </script>

    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
@endsection
