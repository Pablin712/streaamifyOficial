@extends('layouts.static')

@section('title', 'Dashboard')

@section('h1')
    <i class="fas fa-chart-line text-primary me-2"></i> Dashboard Financiero
@endsection

@section('breadcrumb')
    Dashboard
@endsection

@section('introduccion')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">Bienvenido, <strong>{{ Auth::user()->nombreemp }}</strong></h4>
            <p class="mb-0 text-muted">Resumen completo del rendimiento financiero y operativo de Streamify HQ</p>
        </div>
        <div>
            <a href="{{ route('dashboard.pdf') }}" class="btn btn-danger">
                <i class="fas fa-file-pdf me-1"></i> Descargar PDF
            </a>
        </div>
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

                <!-- Cards de Resumen Financiero Total -->
                <div class="row mb-4">
                    <div class="col-12 mb-3">
                        <h5 class="font-weight-bold text-primary">
                            <i class="fas fa-money-bill-wave me-2"></i> Resumen Financiero del Negocio
                        </h5>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            <i class="fas fa-university"></i> Total Disponible en Bancos
                                        </div>
                                        <div class="h4 mb-0 font-weight-bold text-success">
                                            ${{ number_format($totalDisponible ?? 0, 2) }}
                                        </div>
                                        <small class="text-muted">Suma de todos los bancos registrados</small>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-coins fa-3x text-success" style="opacity: 0.3;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            <i class="fas fa-file-invoice-dollar"></i> Deudas Pendientes
                                        </div>
                                        <div class="h4 mb-0 font-weight-bold text-danger">
                                            ${{ number_format($totalDeudasPendientes ?? 0, 2) }}
                                        </div>
                                        <small class="text-muted">Total por pagar a proveedores</small>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-3x text-danger" style="opacity: 0.3;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2" style="border-left: 4px solid {{ ($totalDisponible - $totalDeudasPendientes) >= 0 ? '#28a745' : '#dc3545' }};">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: {{ ($totalDisponible - $totalDeudasPendientes) >= 0 ? '#28a745' : '#dc3545' }};">
                                            <i class="fas fa-balance-scale"></i> Balance Neto
                                        </div>
                                        <div class="h4 mb-0 font-weight-bold" style="color: {{ ($totalDisponible - $totalDeudasPendientes) >= 0 ? '#28a745' : '#dc3545' }};">
                                            ${{ number_format(($totalDisponible ?? 0) - ($totalDeudasPendientes ?? 0), 2) }}
                                        </div>
                                        <small class="text-muted">Disponible - Deudas</small>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-line fa-3x" style="color: {{ ($totalDisponible - $totalDeudasPendientes) >= 0 ? '#28a745' : '#dc3545' }}; opacity: 0.3;"></i>
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

    {{-- Tabla de Resultados del Mes --}}
    @include('partials.dashboard-statistics-table')

    {{-- Resumen Financiero --}}
    <div class="card mb-4">
        <div class="card-header" style="background-color: var(--bg-light);">
            <h5 class="mb-0" style="color: var(--text-primary);">
                <i class="fas fa-calculator me-2"></i>Resumen Financiero del Mes
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>Análisis de Resultados
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>📌 Concepto</th>
                                    <th class="text-end">💰 Monto</th>
                                    <th class="text-end">📈 Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>💵 Ingresos</strong></td>
                                    <td class="text-end">${{ number_format($ingresos_mes, 2) }}</td>
                                    <td class="text-end"><strong>100%</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>🏭 Costos</strong></td>
                                    <td class="text-end">${{ number_format($costos_mes, 2) }}</td>
                                    <td class="text-end">{{ number_format($costos_pct, 2) }}%</td>
                                </tr>
                                <tr>
                                    <td><strong>💸 Gastos</strong></td>
                                    <td class="text-end">${{ number_format($gastos_mes, 2) }}</td>
                                    <td class="text-end">{{ number_format($gastos_pct, 2) }}%</td>
                                </tr>
                                <tr class="table-{{ $balance >= 0 ? 'success' : 'danger' }}">
                                    <td><strong>📊 Balance</strong></td>
                                    <td class="text-end">
                                        <strong>${{ number_format($balance, 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($balance_pct, 2) }}%</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-receipt me-2 text-warning"></i>Desglose de Gastos
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>📍 Concepto</th>
                                    <th class="text-end">💲 Monto</th>
                                    <th class="text-end">📊 %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gastos as $gasto)
                                    <tr>
                                        <td>{{ $gasto['concepto'] }}</td>
                                        <td class="text-end">${{ number_format($gasto['total'], 2) }}</td>
                                        <td class="text-end">{{ $gasto['porcentaje'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Visualiza los gráficos de resultados del mes actual en la sección inferior.
            </p>
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

            function initChart(labels, ingresos, costos, gastos, ganancias, ventasChart, newCustomers, users, accounts, dangerAccounts, pendingPayments, affectedCustomers) {
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
                            {
                                label: "Cuentas",
                                data: accounts,
                                fill: false,
                                backgroundColor: "rgba(139, 69, 19, 0.2)", // 🟤 Marrón translúcido
                                borderColor: "rgba(139, 69, 19, 1)", // 🟤 Marrón fuerte
                                borderWidth: 2,
                                hidden: true
                            },
                            {
                                label: "Cuentas Peligrosas",
                                data: dangerAccounts,
                                fill: false,
                                backgroundColor: "rgba(255, 99, 132, 0.2)", // 🔴 Rojo translúcido
                                borderColor: "rgba(255, 99, 132, 1)", // 🔴 Rojo fuerte
                                borderWidth: 2,
                                hidden: true
                            },
                            {
                                label: "Pagos Pendientes",
                                data: pendingPayments,
                                fill: false,
                                backgroundColor: "rgba(54, 162, 235, 0.2)", // 🔵 Azul translúcido
                                borderColor: "rgba(54, 162, 235, 1)", // 🔵 Azul fuerte
                                borderWidth: 2,
                                hidden: true
                            },
                            {
                                label: "Clientes Afectados",
                                data: affectedCustomers,
                                fill: false,
                                backgroundColor: "rgba(255, 159, 64, 0.2)", // 🟠 Naranja translúcido
                                borderColor: "rgba(255, 159, 64, 1)", // 🟠 Naranja fuerte
                                borderWidth: 2,
                                hidden: true
                            }
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
                            Object.values(data.users),
                            Object.values(data.accounts),
                            Object.values(data.dangerAccounts),
                            Object.values(data.pendingPayments),
                            Object.values(data.affectedCustomers),
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

    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
@endsection
