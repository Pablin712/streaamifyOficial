@extends('layouts.static')
@section('title')
    Dashboard
@endsection
@section('breadcrumb')
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
    <div class="d-flex justify-content mb-3">
        <a href="{{ route('dashboard.pdf') }}" class="btn btn-danger">
            <i class="fas fa-file-pdf"></i> Descargar PDF
        </a>
    </div>
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
                                            Ingresos (Mensual)</div>
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
                                            Ingresos (Anual)</div>
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
                            @if ($costos_disney != 0)
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
            <h3>📊 Resumen financiero</h5>
                <div class="row mb-2">
                    <div class="col-4"><strong>📌 Concepto</strong></div>
                    <div class="col-4 text-end"><strong>💰 Monto</strong></div>
                    <div class="col-4 text-end"><strong>📈 Porcentaje</strong></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>💵 Ingresos:</strong></div>
                    <div class="col-4 text-end">{{ number_format($ingresos_mes, 2) }}</div>
                    <div class="col-4 text-end"><strong>100%</strong></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>🏭 Costos:</strong></div>
                    <div class="col-4 text-end">{{ number_format($costos_mes, 2) }}</div>
                    <div class="col-4 text-end">{{ number_format($costos_pct, 2) }}%</div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>💸 Gastos:</strong></div>
                    <div class="col-4 text-end">{{ number_format($gastos_mes, 2) }}</div>
                    <div class="col-4 text-end">{{ number_format($gastos_pct, 2) }}%</div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>📉 Balance:</strong></div>
                    <div class="col-4 text-end">
                        <strong>
                            <span class="{{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($balance, 2) }}
                            </span>
                        </strong>
                    </div>
                    <div class="col-4 text-end">
                        <strong>
                            <span class="{{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($balance_pct, 2) }}%
                            </span>
                        </strong>
                    </div>
                </div>
                <hr> <!-- Línea de separación -->
                <h4 class="mt-3">📌 Resumen de Gastos</h6>
                <div class="row fw-bold">
                    <div class="col-4">📍 Concepto</div>
                    <div class="col-4 text-end">💲 Monto</div>
                    <div class="col-4 text-end">📊 Porcentaje</div>
                </div>
                @foreach ($gastos as $gasto)
                    <div class="row mb-2">
                        <div class="col-4">{{ $gasto['concepto'] }}</div>
                        <div class="col-4 text-end">{{ number_format($gasto['total'], 2) }}</div>
                        <div class="col-4 text-end">{{ $gasto['porcentaje'] }}%</div>
                    </div>
                @endforeach
                <p class="mt-3">Visualiza los gráficos de resultados del mes actual.</p>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-chart-area me-1"></i>
                Progreso de Streamify
            </span>
            <div>
                <button class="btn btn-sm btn-primary filter-btn" data-interval="1d">1D</button>
                <button class="btn btn-sm btn-primary filter-btn" data-interval="1w">1W</button>
                <button class="btn btn-sm btn-primary filter-btn" data-interval="1m">1M</button>
                <button class="btn btn-sm btn-primary filter-btn" data-interval="3m">3M</button>
                <button class="btn btn-sm btn-primary filter-btn" data-interval="1y">1Y</button>
            </div>
        </div>
        <div class="card-body">
            <canvas id="myAreaChart" width="100%" height="30"></canvas>
        </div>
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
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById("myAreaChart").getContext("2d");
            var myAreaChart;

            function initChart(labels, ingresos, costos, gastos, ganancias, ventasChart, newCustomers, users) {
                if (myAreaChart) {
                    myAreaChart.destroy(); // Destruir el gráfico si ya existe
                }

                myAreaChart = new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: labels,
                        datasets: [{
                                label: "Ingresos",
                                data: ingresos,
                                fill: true,
                                backgroundColor: "rgba(78, 115, 223, 0.2)",
                                borderColor: "rgba(78, 115, 223, 1)",
                                borderWidth: 2,
                            },
                            {
                                label: "Costos",
                                data: costos,
                                fill: true,
                                backgroundColor: "rgba(255, 159, 64, 0.2)", // 🟠 Naranja estándar
                                borderColor: "rgba(255, 159, 64, 1)",
                                borderWidth: 2,
                            },
                            {
                                label: "Gastos",
                                data: gastos,
                                fill: true,
                                backgroundColor: "rgba(220, 53, 69, 0.2)", // 🔴 Rojo translúcido
                                borderColor: "rgba(220, 53, 69, 1)", // 🔴 Rojo fuerte
                                borderWidth: 2,
                            },
                            {
                                label: "Ganancias",
                                data: ganancias,
                                fill: true,
                                backgroundColor: "rgba(28, 200, 138, 0.2)",
                                borderColor: "rgba(28, 200, 138, 1)",
                                borderWidth: 2,
                                hidden: true
                            },
                            {
                                label: "Ventas",
                                data: ventasChart,
                                fill: false,
                                backgroundColor: "rgba(255, 205, 86, 0.2)", // 🟡 Amarillo translúcido
                                borderColor: "rgba(255, 205, 86, 1)", // 🟡 Amarillo fuerte
                                borderWidth: 2,
                                hidden: true
                            },
                            {
                                label: "Clientes New",
                                data: newCustomers,
                                fill: false,
                                backgroundColor: "rgba(255, 20, 147, 0.2)", // 🌸 Rosa más vibrante
                                borderColor: "rgba(255, 20, 147, 1)", // 🌸 Rosa fuerte
                                borderWidth: 2,
                                hidden: true
                            },
                            {
                                label: "Suscripciones",
                                data: users,
                                fill: false,
                                backgroundColor: "rgba(153, 102, 255, 0.2)", // 🟣 Morado translúcido
                                borderColor: "rgba(153, 102, 255, 1)", // 🟣 Morado fuerte
                                borderWidth: 2,
                                hidden: true
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                beginAtZero: true
                            },
                            y: {
                                beginAtZero: true
                            },
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: "top"
                            },
                            tooltip: {
                                enabled: true
                            },
                        },
                    },
                });
            }

            function loadChartData(interval) {
                fetch("{{ route('dashboard.filter') }}?range=" + interval)
                    .then((response) => response.json())
                    .then((data) => {
                        initChart(
                            data.labels,
                            Object.values(data.ingresos),
                            Object.values(data.costos),
                            Object.values(data.gastos),
                            Object.values(data.ganancias),
                            Object.values(data.ventasChart),
                            Object.values(data.newCustomers),
                            Object.values(data.users)
                        );
                    })
                    .catch((error) => console.error("Error al cargar datos:", error));
            }

            document.querySelectorAll(".filter-btn").forEach((button) => {
                button.addEventListener("click", function() {
                    document.querySelectorAll(".filter-btn").forEach((btn) =>
                        btn.classList.remove("btn-primary")
                    );
                    this.classList.add("btn-primary");

                    var interval = this.getAttribute("data-interval");
                    loadChartData(interval);
                });
            });

            // Cargar datos iniciales
            loadChartData("1d");
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
