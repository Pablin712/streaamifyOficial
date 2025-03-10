@extends('layouts.table')

@section('title', 'Cuentas')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Personalizando el fondo oscuro de las filas de la tabla a morado */
        .table-dark {
            background-color: #800080 !important;
            /* Color morado */
            color: white !important;
        }

        /* Personalizando el badge bg-dark a morado */
        .badge.bg-dark {
            background-color: #800080 !important;
            /* Color morado */
            color: white !important;
        }

        .badge.bg-dark:hover {
            background-color: #6a006a !important;
        }

        .btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1;
            border-radius: 0.2rem;
        }
    </style>
@endsection
@section('h1', 'Cuentas')
@section('breadcrumb')
    Cuentas
@endsection
@section('descripcion')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Revisa las cuentas activas del <strong>Negocio</strong>. Aquí podrás gestionar las cuentas de usuario
        asociadas a los servicios de streaming pertenecientes a Streamify HQ.
    </p>
    <div class="row">
        @php
            $serviciosConfig = [
                'NETFLIX' => ['color' => 'danger', 'icon' => 'logo_netflix.png'],
                'DISNEYP' => ['color' => 'primary', 'icon' => 'espn.jpg'],
                'DISNEYS' => ['color' => 'primary', 'icon' => 'disneyP.jpg'],
                'MAX' => ['color' => 'info', 'icon' => 'max.jpg'],
                'PRIME' => ['color' => 'success', 'icon' => 'fa-amazon'],
                'PARAMOUNT' => ['color' => 'primary', 'icon' => 'paramount.jpg'],
                'CRUNCHY' => ['color' => 'warning', 'icon' => 'crunchy.jpg'],
                'SPOTIFY' => ['color' => 'success', 'icon' => 'fa-spotify'],
                'MAGIS' => ['color' => 'dark', 'icon' => 'magis.jpg'],
            ];
        @endphp

        @foreach ($espacios_por_servicio as $servicio => $espacios)
            @if (isset($serviciosConfig[$servicio]))
                @php
                    $color = $serviciosConfig[$servicio]['color'];
                    $icono = $serviciosConfig[$servicio]['icon'];
                @endphp
                <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                    <div class="card border-left-{{ $color }} shadow h-100 py-1 small">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-{{ $color }} text-uppercase mb-1">
                                        {{ ucfirst($servicio) }}
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        {{ $espacios }} puestos
                                    </div>
                                </div>
                                <div class="col-auto">
                                    @if (Str::startsWith($icono, 'fa-'))
                                        <i class="fab {{ $icono }} fa-2x text-gray-300"></i>
                                    @else
                                        <img src="{{ asset('images/' . $icono) }}" width="40" height="40">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endsection
@section('btncrear')
    @if (Auth::user()->hasPermissionTo('cuentas.create'))
        <a href="{{ route('cuentas.create') }}" class="btn btn-primary mb-3">Crear Cuenta</a>
    @endif
    @if (Auth::user()->hasPermissionTo('valores.create'))
        <a href="{{ route('valores.create') }}" class="btn btn-primary mb-3">Crear Valor</a>
    @endif
@endsection

@section('tablename', 'Cuentas')

@section('table1')
    <ul class="nav nav-tabs" id="cuentasTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="todas-tab" data-bs-toggle="tab" data-bs-target="#todas"
                type="button" role="tab">Todas</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="disponibles-tab" data-bs-toggle="tab" data-bs-target="#disponibles"
                type="button" role="tab">Disponibles</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="colapsadas-tab" data-bs-toggle="tab" data-bs-target="#colapsadas" type="button"
                role="tab">Colapsadas</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sinocupar-tab" data-bs-toggle="tab" data-bs-target="#sinocupar" type="button"
                role="tab">Sin Ocupar</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="porvencer-tab" data-bs-toggle="tab" data-bs-target="#porvencer" type="button"
                role="tab">Por Vencer</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="caidas-tab" data-bs-toggle="tab" data-bs-target="#caidas" type="button"
                role="tab">Dañadas</button>
        </li>
    </ul>

    <!-- Contenido de las pestañas -->
    <div class="tab-content mt-3" id="cuentasTabContent">
        <!-- Pestaña de Cuentas -->
        <div class="tab-pane fade show active" id="todas" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentas])
        </div>

        <!-- Pestaña de Cuentas Disponibles -->
        <div class="tab-pane fade" id="disponibles" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasDisponibles])
        </div>

        <!-- Pestaña de Cuentas Colapsadas -->
        <div class="tab-pane fade" id="colapsadas" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasColapsadas])
        </div>

        <!-- Pestaña de Cuentas Sin Ocupar -->
        <div class="tab-pane fade" id="sinocupar" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasSinOcupar])
        </div>

        <!-- Pestaña de Cuentas Por Vencer -->
        <div class="tab-pane fade" id="porvencer" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasPorVencer])
        </div>

        <!-- Pestaña de Cuentas Dañadas -->
        <div class="tab-pane fade" id="caidas" role="tabpanel">
            @include('inventory.cuentas.tabla', ['cuentas' => $cuentasCaidas])
        </div>

    </div>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const tables = document.querySelectorAll(
                '.datatable'); // Selecciona todas las tablas con la clase 'datatable'

                tables.forEach((table) => {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0) {
                        new simpleDatatables.DataTable(table, {
                            searchable: true,
                            perPageSelect: [5, 10, 20],
                            labels: {
                                placeholder: "Buscar...",
                                perPage: "Registros por página",
                                noRows: "No se encontraron registros.",
                                info: "Mostrando {start} a {end} de {rows} registros",
                            },
                        });
                    } else {
                        console.warn('La tabla sigue sin filas después del tiempo de espera.');
                    }
                });
            }, 500);
        });
    </script>
@endsection