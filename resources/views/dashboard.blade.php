@extends('layouts.static')
@section('title')
    Dashboard
@endsection
@section('styles')
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        tfoot {
            display: table-footer-group !important;
        }
    </style>
@endsection
@section('h1', 'Dashboard')
@section('introduccion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h1>{{ Auth::user()->nombreemp }}</h1>
    <form method="POST" action="{{ route('dashboard.store') }}">
        @csrf
        <!-- Otras entradas del formulario -->
        <input type="hidden" name="ventas" value="{{ $ventas }}">
        <input type="hidden" name="ingresos_mes" value="{{ $ingresos_mes }}">
        <input type="hidden" name="ingresos_ano" value="{{ $ingresos_ano }}">
        <input type="hidden" name="clientes_activos" value="{{ $clientes_activos }}">
        <input type="hidden" name="usuarios_activos" value="{{ $total_usuarios_activos }}">
        <input type="hidden" name="cuentas_caidas" value="{{ $cuentas_caidas }}">
        <input type="hidden" name="usuarios_acobrar" value="{{ $usuarios_acobrar }}">
        <input type="hidden" name="num_cuentas" value="{{ $num_cuentas }}">
        <input type="hidden" name="costos_mes" value="{{ $costos_mes }}">
        <input type="hidden" name="promedio_pagos_mes" value="{{ $promedio_pagos_mes }}">
        <input type="hidden" name="cliente_mas_facturado" value="{{ $cliente_mas_facturado }}">
        <input type="hidden" name="ventas_mes" value="{{ $ventas_mes }}">

        <!-- Variables de historial -->
        <input type="hidden" name="meses_historial" value="{{ $meses_historial }}">
        <input type="hidden" name="ingresos_historial" value="{{ $ingresos_historial }}">
        <input type="hidden" name="costos_historial" value="{{ $costos_historial }}">
        <input type="hidden" name="ganancias_historial" value="{{ $ganancias_historial }}">

        <!-- Variables de servicios específicos -->
        <input type="hidden" name="cuentas_netflix" value="{{ $cuentas_netflix }}">
        <input type="hidden" name="usuarios_netflix" value="{{ $usuarios_netflix }}">
        <input type="hidden" name="ingresos_netflix" value="{{ $ingresos_netflix }}">
        <input type="hidden" name="costos_netflix" value="{{ $costos_netflix }}">

        <input type="hidden" name="cuentas_disney" value="{{ $cuentas_disney }}">
        <input type="hidden" name="usuarios_disney" value="{{ $usuarios_disney }}">
        <input type="hidden" name="ingresos_disney" value="{{ $ingresos_disney }}">
        <input type="hidden" name="costos_disney" value="{{ $costos_disney }}">

        <input type="hidden" name="cuentas_prime" value="{{ $cuentas_prime }}">
        <input type="hidden" name="usuarios_prime" value="{{ $usuarios_prime }}">
        <input type="hidden" name="ingresos_prime" value="{{ $ingresos_prime }}">
        <input type="hidden" name="costos_prime" value="{{ $costos_prime }}">

        <input type="hidden" name="cuentas_max" value="{{ $cuentas_max }}">
        <input type="hidden" name="usuarios_max" value="{{ $usuarios_max }}">
        <input type="hidden" name="ingresos_max" value="{{ $ingresos_max }}">
        <input type="hidden" name="costos_max" value="{{ $costos_max }}">

        <input type="hidden" name="cuentas_magis" value="{{ $cuentas_magis }}">
        <input type="hidden" name="usuarios_magis" value="{{ $usuarios_magis }}">
        <input type="hidden" name="ingresos_magis" value="{{ $ingresos_magis }}">
        <input type="hidden" name="costos_magis" value="{{ $costos_magis }}">

        <input type="hidden" name="cuentas_crunchy" value="{{ $cuentas_crunchy }}">
        <input type="hidden" name="usuarios_crunchy" value="{{ $usuarios_crunchy }}">
        <input type="hidden" name="ingresos_crunchy" value="{{ $ingresos_crunchy }}">
        <input type="hidden" name="costos_crunchy" value="{{ $costos_crunchy }}">

        <input type="hidden" name="cuentas_paramount" value="{{ $cuentas_paramount }}">
        <input type="hidden" name="usuarios_paramount" value="{{ $usuarios_paramount }}">
        <input type="hidden" name="ingresos_paramount" value="{{ $ingresos_paramount }}">
        <input type="hidden" name="costos_paramount" value="{{ $costos_paramount }}">

        <input type="hidden" name="cuentas_spotify" value="{{ $cuentas_spotify }}">
        <input type="hidden" name="usuarios_spotify" value="{{ $usuarios_spotify }}">
        <input type="hidden" name="ingresos_spotify" value="{{ $ingresos_spotify }}">
        <input type="hidden" name="costos_spotify" value="{{ $costos_spotify }}">

        <input type="hidden" name="cuentas_otros" value="{{ $cuentas_otros }}">
        <input type="hidden" name="usuarios_otros" value="{{ $usuarios_otros }}">
        <input type="hidden" name="ingresos_otros" value="{{ $ingresos_otros }}">
        <input type="hidden" name="costos_otros" value="{{ $costos_otros }}">
        <!-- Agrega los demás campos como ocultos si es necesario -->
        <button type="submit" class="btn btn-primary mb-3">Guardar reporte de mes</button>
    </form>

@endsection
@section('content')
    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Resumen de Streamify HQ</h1>
                </div>


                <div class="row">

                    <!-- Earnings (Monthly) Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Earnings (Monthly)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">${{ $ingresos_mes }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings (Annual) Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Earnings (Annual)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">${{ $ingresos_ano }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Earnings (Annual) Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Ventas este mes</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $ventas_mes }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Ventas este Año</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $ventas_ano }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- otro card --}}

                </div>
                <div class="row">
                    <!-- Clientes activos (Monthly) Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Cuentas Activas</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $num_cuentas }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-crown fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Espacios disponibles</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $espacios }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Pending Requests Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Pending Payments</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $usuarios_acobrar }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Pending Requests Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Cuentas caidas</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $cuentas_caidas }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-tools fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">

                    <!-- Clientes activos (Monthly) Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Clientes Activos</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $clientes_activos }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings (Annual) Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Usuarios Activos</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_usuarios_activos }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-id-badge fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Media de pago por cliente</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ${{ number_format($promedio_pagos_mes, 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-coins fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- otro card --}}
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Cliente más facturado</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $cliente_mas_facturado->nombre_cliente ?? 'No encontrado' }}
                                            ${{ $cliente_mas_facturado->facturado ?? 0 }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->
    </div>
    <!-- End of Content Wrapper -->
    {{--  <h3>Mapa de erp</h3>
    <img src="{{ asset('images/BASE2.png') }}" alt="imagen de mapa">
    --}}

    {{-- tabla de resultados del mes --}}
    <div id="resultados" class="card mb-4">
        <div class="card-header">
            <i class="fas fa-money-bill-wave me-1"></i>
            Resultados del mes
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th>Cta</th>
                        <th>Usuarios</th>
                        <th>usu/cue</th>
                        <th>Ingresos</th>
                        <th>Costos</th>
                        <th>Ganancias</th>
                        <th>Renta</th>
                        <th>Ing/Cli</th>
                        <th>Cos/Cli</th>
                        <th>Gan/Cli</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Netflix</td>
                        <td>{{ $cuentas_netflix }}</td>
                        <td>{{ $usuarios_netflix }}</td>
                        <td>
                            @if ($cuentas_netflix != 0)
                                {{ number_format($usuarios_netflix / $cuentas_netflix, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ $ingresos_netflix }}</td>
                        <td>{{ $costos_netflix }}</td>
                        <td>{{ $ingresos_netflix - $costos_netflix }}</td>
                        <td>
                            @if ($costos_netflix != 0)
                                {{ number_format($ingresos_netflix / $costos_netflix, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_netflix != 0)
                                {{ number_format($ingresos_netflix / $usuarios_netflix, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_netflix != 0)
                                {{ number_format($costos_netflix / $usuarios_netflix, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_netflix != 0)
                                {{ number_format(($ingresos_netflix - $costos_netflix) / $usuarios_netflix, 2) }}
                            @else
                                0
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Disney</td>
                        <td>{{ $cuentas_disney }}</td>
                        <td>{{ $usuarios_disney }}</td>
                        <td>
                            @if ($cuentas_disney != 0)
                                {{ number_format($usuarios_disney / $cuentas_disney, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ $ingresos_disney }}</td>
                        <td>{{ $costos_disney }}</td>
                        <td>{{ $ingresos_disney - $costos_disney }}</td>
                        <td>
                            @if ($costos_netflix != 0)
                                {{ number_format($ingresos_disney / $costos_disney, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_disney != 0)
                                {{ number_format($ingresos_disney / $usuarios_disney, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_disney != 0)
                                {{ number_format($costos_disney / $usuarios_disney, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_disney != 0)
                                {{ number_format(($ingresos_disney - $costos_disney) / $usuarios_disney, 2) }}
                            @else
                                0
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Prime</td>
                        <td>{{ $cuentas_prime }}</td>
                        <td>{{ $usuarios_prime }}</td>
                        <td>
                            @if ($cuentas_prime != 0)
                                {{ number_format($usuarios_prime / $cuentas_prime, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ $ingresos_prime }}</td>
                        <td>{{ $costos_prime }}</td>
                        <td>{{ $ingresos_prime - $costos_prime }}</td>
                        <td>
                            @if ($costos_prime != 0)
                                {{ number_format($ingresos_prime / $costos_prime, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_prime != 0)
                                {{ number_format($ingresos_prime / $usuarios_prime, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_prime != 0)
                                {{ number_format($costos_prime / $usuarios_prime, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_prime != 0)
                                {{ number_format(($ingresos_prime - $costos_prime) / $usuarios_prime, 2) }}
                            @else
                                0
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Max</td>
                        <td>{{ $cuentas_max }}</td>
                        <td>{{ $usuarios_max }}</td>
                        <td>
                            @if ($cuentas_max != 0)
                                {{ number_format($usuarios_max / $cuentas_max, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ $ingresos_max }}</td>
                        <td>{{ $costos_max }}</td>
                        <td>{{ $ingresos_max - $costos_max }}</td>
                        <td>
                            @if ($costos_max != 0)
                                {{ number_format($ingresos_max / $costos_max, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_max != 0)
                                {{ number_format($ingresos_max / $usuarios_max, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_max != 0)
                                {{ number_format($costos_max / $usuarios_max, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_max != 0)
                                {{ number_format(($ingresos_max - $costos_max) / $usuarios_max, 2) }}
                            @else
                                0
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Magis</td>
                        <td>{{ $cuentas_magis }}</td>
                        <td>{{ $usuarios_magis }}</td>
                        <td>
                            @if ($cuentas_magis != 0)
                                {{ number_format($usuarios_magis / $cuentas_magis, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ $ingresos_magis }}</td>
                        <td>{{ $costos_magis }}</td>
                        <td>{{ $ingresos_magis - $costos_magis }}</td>
                        <td>
                            @if ($costos_magis != 0)
                                {{ number_format($ingresos_magis / $costos_magis, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_magis != 0)
                                {{ number_format($ingresos_magis / $usuarios_magis, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_magis != 0)
                                {{ number_format($costos_magis / $usuarios_magis, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_magis != 0)
                                {{ number_format(($ingresos_magis - $costos_magis) / $usuarios_magis, 2) }}
                            @else
                                0
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Crunchy</td>
                        <td>{{ $cuentas_crunchy }}</td>
                        <td>{{ $usuarios_crunchy }}</td>
                        <td>
                            @if ($cuentas_crunchy != 0)
                                {{ number_format($usuarios_crunchy / $cuentas_crunchy, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ $ingresos_crunchy }}</td>
                        <td>{{ $costos_crunchy }}</td>
                        <td>{{ $ingresos_crunchy - $costos_crunchy }}</td>
                        <td>
                            @if ($costos_crunchy != 0)
                                {{ number_format($ingresos_crunchy / $costos_crunchy, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_crunchy != 0)
                                {{ number_format($ingresos_crunchy / $usuarios_crunchy, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_crunchy != 0)
                                {{ number_format($costos_crunchy / $usuarios_crunchy, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_crunchy != 0)
                                {{ number_format(($ingresos_crunchy - $costos_crunchy) / $usuarios_crunchy, 2) }}
                            @else
                                0
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Paramount</td>
                        <td>{{ $cuentas_paramount }}</td>
                        <td>{{ $usuarios_paramount }}</td>
                        <td>
                            @if ($cuentas_paramount != 0)
                                {{ number_format($usuarios_paramount / $cuentas_paramount, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ $ingresos_paramount }}</td>
                        <td>{{ $costos_paramount }}</td>
                        <td>{{ $ingresos_paramount - $costos_paramount }}</td>
                        <td>
                            @if ($costos_paramount != 0)
                                {{ number_format($ingresos_paramount / $costos_paramount, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_paramount != 0)
                                {{ number_format($ingresos_paramount / $usuarios_paramount, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_paramount != 0)
                                {{ number_format($costos_paramount / $usuarios_paramount, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_paramount != 0)
                                {{ number_format(($ingresos_paramount - $costos_paramount) / $usuarios_paramount, 2) }}
                            @else
                                0
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Spotify</td>
                        <td>{{ $cuentas_spotify }}</td>
                        <td>{{ $usuarios_spotify }}</td>
                        <td>
                            @if ($cuentas_spotify != 0)
                                {{ number_format($usuarios_spotify / $cuentas_spotify, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ $ingresos_spotify }}</td>
                        <td>{{ $costos_spotify }}</td>
                        <td>{{ $ingresos_spotify - $costos_spotify }}</td>
                        <td>
                            @if ($costos_spotify != 0)
                                {{ number_format($ingresos_spotify / $costos_spotify, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_spotify != 0)
                                {{ number_format($ingresos_spotify / $usuarios_spotify, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_spotify != 0)
                                {{ number_format($costos_spotify / $usuarios_spotify, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_spotify != 0)
                                {{ number_format(($ingresos_spotify - $costos_spotify) / $usuarios_spotify, 2) }}
                            @else
                                0
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Otros</td>
                        <td>{{ $cuentas_otros }}</td>
                        <td>{{ $usuarios_otros }}</td>
                        <td>
                            @if ($cuentas_otros != 0)
                                {{ number_format($usuarios_otros / $cuentas_otros, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ $ingresos_otros }}</td>
                        <td>{{ $costos_otros }}</td>
                        <td>{{ $ingresos_otros - $costos_otros }}</td>
                        <td>
                            @if ($costos_otros != 0)
                                {{ number_format($ingresos_otros / $costos_otros, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_otros != 0)
                                {{ number_format($ingresos_otros / $usuarios_otros, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_otros != 0)
                                {{ number_format($costos_otros / $usuarios_otros, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($usuarios_otros != 0)
                                {{ number_format(($ingresos_otros - $costos_otros) / $usuarios_otros, 2) }}
                            @else
                                0
                            @endif
                        </td>
                    </tr>


                    <tr>
                        <td><strong>Totales</strong></td>
                        <td><strong>{{ $num_cuentas }}</strong></td>
                        <td><strong>{{ $total_usuarios_activos }}</strong></td>
                        <td>
                            @if ($num_cuentas != 0)
                                <strong>{{ number_format($total_usuarios_activos / $num_cuentas, 2) }}</strong>
                            @else
                                <strong>0</strong>
                            @endif
                        </td>
                        <td><strong>{{ $ingresos_mes }}</strong></td>
                        <td><strong>{{ $costos_mes }}</strong></td>
                        <td><strong>{{ $ingresos_mes - $costos_mes }}</strong></td>
                        <td>
                            @if ($costos_mes != 0)
                                <strong>{{ number_format($ingresos_mes / $costos_mes, 2) }}</strong>
                            @else
                                <strong>0</strong>
                            @endif
                        </td>
                        <td>
                            @if ($total_usuarios_activos != 0)
                                <strong>{{ number_format($ingresos_mes / $total_usuarios_activos, 2) }}</strong>
                            @else
                                <strong>0</strong>
                            @endif
                        </td>
                        <td>
                            @if ($total_usuarios_activos != 0)
                                <strong>{{ number_format($costos_mes / $total_usuarios_activos, 2) }}</strong>
                            @else
                                <strong>0</strong>
                            @endif
                        </td>
                        <td>
                            @if ($total_usuarios_activos != 0)
                                <strong>{{ number_format(($ingresos_mes - $costos_mes) / $total_usuarios_activos, 2) }}</strong>
                            @else
                                <strong>0</strong>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Resumen financiero:</h5>
            <div class="row mb-2">
                <div class="col-6"><strong>Ingresos:</strong></div>
                <div class="col-6 text-end">{{ $ingresos_mes }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Costos:</strong></div>
                <div class="col-6 text-end">{{ $costos_mes }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Gastos:</strong></div>
                <div class="col-6 text-end">{{ $gastos_mes }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Balance:</strong></div>
                <div class="col-6 text-end">
                    <strong>
                        <span class="{{ ($ingresos_mes - $costos_mes - $gastos_mes) >= 0 ? 'text-success' : 'text-danger' }}">
                            {{$ingresos_mes - $costos_mes - $gastos_mes}}
                        </span>
                    </strong>
                </div>
            </div>
            <p class="mt-3">Visualiza los gráficos de resultados del mes actual.</p>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-area me-1"></i>
            Progreso en los últimos 6 meses
        </div>
        <div class="card-body"><canvas id="myAreaChart" width="100%" height="30"></canvas></div>
        <div class="card-footer small text-muted">Updated yesterday at 11:59 PM</div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Finanzas de los servicios este mes
                </div>
                <div class="card-body">
                    <canvas id="myBarChart" width="100%" height="50"></canvas>
                </div>
                <div class="card-footer small text-muted">Updated yesterday at 11:59 PM</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Porcentaje de Ganancias
                </div>
                <div class="card-body"><canvas id="myPieChart" width="100%" height="50"></canvas></div>
                <div class="card-footer small text-muted">Updated yesterday at 11:59 PM</div>
            </div>
        </div>
    </div>
@endsection
@section('pie')
    Realiza las tareas
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('myAreaChart').getContext('2d');


        var mesesHistorial = @json($meses_historial).reverse(); // Etiquetas de los meses, invertidas
        var ingresosHistorial = @json($ingresos_historial).reverse(); // Ingresos invertidos
        var costosHistorial = @json($costos_historial).reverse(); // Costos invertidos
        var gananciasHistorial = @json($ganancias_historial).reverse();

        var myAreaChart = new Chart(ctx, {
            type: 'line', // Tipo de gráfico: línea (pero con área rellena)
            data: {
                labels: mesesHistorial, // Etiquetas del eje X, que son los meses pasados desde el controlador
                datasets: [{
                        label: 'Ingresos', // Nombre de la primera serie (Ingresos)
                        data: ingresosHistorial, // Los datos de la serie de ingresos
                        fill: true, // Rellenar el área debajo de la línea
                        backgroundColor: 'rgba(78, 115, 223, 0.2)', // Color del área (con transparencia)
                        borderColor: 'rgba(78, 115, 223, 1)', // Color de la línea
                        borderWidth: 1
                    },
                    {
                        label: 'Costos', // Nombre de la segunda serie (Costos)
                        data: costosHistorial, // Los datos de la serie de costos
                        fill: true, // Rellenar el área debajo de la línea
                        backgroundColor: 'rgba(255, 159, 64, 0.2)', // Color del área (con transparencia)
                        borderColor: 'rgba(255, 159, 64, 1)', // Color de la línea
                        borderWidth: 1
                    },
                    {
                        label: 'Ganancias', // Nombre de la tercera serie (Ganancias)
                        data: gananciasHistorial, // Los datos de la serie de ganancias
                        fill: true, // Rellenar el área debajo de la línea
                        backgroundColor: 'rgba(28, 200, 138, 0.2)', // Color del área (con transparencia)
                        borderColor: 'rgba(28, 200, 138, 1)', // Color de la línea
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true, // Hacer el gráfico responsivo
                scales: {
                    x: {
                        beginAtZero: true // Comenzar el eje X desde 0
                    },
                    y: {
                        beginAtZero: true // Comenzar el eje Y desde 0
                    }
                },
                plugins: {
                    legend: {
                        display: true, // Mostrar la leyenda
                        position: 'top' // Posición de la leyenda
                    },
                    tooltip: {
                        enabled: true // Habilitar los tooltips
                    }
                }
            }
        });
    </script>
    <script>
        var ctx = document.getElementById('myBarChart').getContext('2d');

        var ingNetflix = @json($ingresos_netflix); // Ingresos
        var cosNetflix = @json($costos_netflix); // Costos
        var ganNetflix = ingNetflix - cosNetflix;

        // Ingresos, costos y ganancias de Disney
        var ingDisney = @json($ingresos_disney); // Ingresos de Disney
        var cosDisney = @json($costos_disney); // Costos de Disney
        var ganDisney = ingDisney - cosDisney; // Ganancias de Disney
        // Ingresos, costos y ganancias de Prime
        var ingPrime = @json($ingresos_prime); // Ingresos de Prime
        var cosPrime = @json($costos_prime); // Costos de Prime
        var ganPrime = ingPrime - cosPrime; // Ganancias de Prime
        // Ingresos, costos y ganancias de Max
        var ingMax = @json($ingresos_max); // Ingresos de Max
        var cosMax = @json($costos_max); // Costos de Max
        var ganMax = ingMax - cosMax; // Ganancias de Max
        // Ingresos, costos y ganancias de Magis
        var ingMagis = @json($ingresos_magis); // Ingresos de Magis
        var cosMagis = @json($costos_magis); // Costos de Magis
        var ganMagis = ingMagis - cosMagis; // Ganancias de Magis
        // Ingresos, costos y ganancias de Crunchy
        var ingCrunchy = @json($ingresos_crunchy); // Ingresos de Crunchy
        var cosCrunchy = @json($costos_crunchy); // Costos de Crunchy
        var ganCrunchy = ingCrunchy - cosCrunchy; // Ganancias de Crunchy
        // Ingresos, costos y ganancias de Paramount
        var ingParamount = @json($ingresos_paramount); // Ingresos de Paramount
        var cosParamount = @json($costos_paramount); // Costos de Paramount
        var ganParamount = ingParamount - cosParamount; // Ganancias de Paramount
        // Ingresos, costos y ganancias de Spotify
        var ingSpotify = @json($ingresos_spotify); // Ingresos de Spotify
        var cosSpotify = @json($costos_spotify); // Costos de Spotify
        var ganSpotify = ingSpotify - cosSpotify; // Ganancias de Spotify
        // Ingresos, costos y ganancias de Otros
        var ingOtros = @json($ingresos_otros); // Ingresos de Otros
        var cosOtros = @json($costos_otros); // Costos de Otros
        var ganOtros = ingOtros - cosOtros; // Ganancias de Otros
        // Ingresos, costos y ganancias de Netflix
        var myBarChart = new Chart(ctx, {
            type: 'bar', // Tipo de gráfico: barra
            data: {
                labels: ['Netflix', 'Disney', 'Prime', 'Max', 'Magis', 'Crunchy', 'Paramount', 'Spotify',
                    'Otros'
                ], // Etiquetas de las categorías (eje X)
                datasets: [{
                        label: 'Ingresos', // Etiqueta para la primera barra
                        data: [ingNetflix, ingDisney, ingPrime, ingMax, ingMagis, ingCrunchy, ingParamount,
                            ingSpotify, ingOtros
                        ], // Datos para la serie 1
                        backgroundColor: '#3aff00', // Color de las barras de la primera serie
                        borderColor: '#3aff00', // Color del borde de las barras
                        borderWidth: 1
                    },
                    {
                        label: 'Costos', // Etiqueta para la segunda barra
                        data: [cosNetflix, cosDisney, cosPrime, cosMax, cosMagis, cosCrunchy, cosParamount,
                            cosSpotify, cosOtros
                        ], // Datos para la serie 2
                        backgroundColor: '#ff0000', // Color de las barras de la segunda serie
                        borderColor: '#ff0000', // Color del borde de las barras
                        borderWidth: 1
                    },
                    {
                        label: 'Ganancias', // Etiqueta para la tercera barra
                        data: [ganNetflix, ganDisney, ganPrime, ganMax, ganMagis, ganCrunchy, ganParamount,
                            ganSpotify, ganOtros
                        ], // Datos para la serie 3
                        backgroundColor: '#18af00', // Color de las barras de la tercera serie
                        borderColor: '#18af00', // Color del borde de las barras
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true, // Hacer el gráfico responsivo
                scales: {
                    x: {
                        stacked: false // No apilar las barras, es necesario para barras agrupadas
                    },
                    y: {
                        stacked: false // No apilar las barras en el eje Y
                    }
                },
                plugins: {
                    legend: {
                        display: true, // Mostrar la leyenda
                        position: 'top' // Posición de la leyenda
                    }
                }
            }
        });
    </script>

    <script>
        var ctx = document.getElementById('myPieChart').getContext('2d');
        var myPieChart = new Chart(ctx, {
            type: 'pie', // Tipo de gráfico: pastel
            data: {
                labels: ['Netflix', 'Disney', 'Prime', 'Max', 'Magis', 'Crunchy', 'Paramount', 'Spotify',
                    'Otros'
                ], // Etiquetas para cada porción del pastel
                datasets: [{
                    data: [ganNetflix, ganDisney, ganPrime, ganMax, ganMagis, ganCrunchy, ganParamount,
                        ganSpotify, ganOtros
                    ], // Datos para cada porción del pastel
                    backgroundColor: ['#ff0000', '#00babd', '#00f7ff', '#003aff', '#ff8f00',
                        '#ffcd00', '#009eff', '#1abd00', '#d100ff'
                    ], // Colores para las porciones
                    borderColor: ['#ffffff', '#ffffff', '#ffffff', '#ffffff', '#ffffff',
                        '#ffffff', '#ffffff', '#ffffff', '#ffffff'
                    ], // Color de los bordes
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true, // Hacer el gráfico responsivo
                plugins: {
                    legend: {
                        display: true, // Mostrar la leyenda
                        position: 'top' // Posición de la leyenda
                    },
                    tooltip: {
                        enabled: true, // Habilitar los tooltips
                    }
                }
            }
        });
    </script>

@endsection
