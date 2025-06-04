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
    <table id="datatablesSimpl" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Total de cuentas</th>
                <th>Netflix</th>
                <th>Disney Premium</th>
                <th>Disney</th>
                <th>MAX</th>
                <th>Prime</th>
                <th>Spotify</th>
                <th>Otros</th>
                <th>Se debe</th>
                <th>Pagar en el mes</th>
                @if (Auth::user()->hasAnyPermission(['proveedores.edit', 'proveedores.destroy']))
                    <th>Acciones</th>
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
            }
        });
    </script>
@endsection
