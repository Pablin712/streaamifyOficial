@extends('layouts.static')

@section('h1', 'Actividad de Empleados')
@section('breadcrumb')
    Actividad de Empleados
@endsection
@section('introduccion')
    Aquí puedes ver la actividad de los empleados en el sistema.
@endsection
@section('content')
    <div class="container">
        <h3 class="text-center mb-4">Estadísticas del Mes</h3>
        <div class="row">
            @foreach ($estadisticasOrdenadas as $idemp => $datosMes)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-center">{{ $datosMes['nombre'] }}</h5>
                            <canvas id="chart-{{ $idemp }}" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach ($estadisticasOrdenadas as $idemp => $datosMes)
                const labels{{ $idemp }} = [
                    @foreach ($datosMes as $fecha => $datosDia)
                        @if ($fecha === 'nombre')
                            @continue
                        @endif
                        "{{ \Carbon\Carbon::parse($fecha)->format('d M') }}",
                    @endforeach
                ];

                const data{{ $idemp }} = {
                    labels: labels{{ $idemp }},
                    datasets: [{
                            label: 'Asistencias',
                            data: [
                                @foreach ($datosMes as $fecha => $datosDia)
                                    @if ($fecha === 'nombre')
                                        @continue
                                    @endif
                                    {{ $datosDia['asistencias'] }},
                                @endforeach
                            ],
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            fill: true,
                            hidden: false,
                        },
                        {
                            label: 'Ventas',
                            data: [
                                @foreach ($datosMes as $fecha => $datosDia)
                                    @if ($fecha === 'nombre')
                                        @continue
                                    @endif
                                    {{ $datosDia['ventas'] }},
                                @endforeach
                            ],
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            fill: true,
                            hidden: false,
                        },
                        {
                            label: 'Recargas',
                            data: [
                                @foreach ($datosMes as $fecha => $datosDia)
                                    @if ($fecha === 'nombre')
                                        @continue
                                    @endif
                                    {{ $datosDia['recargas'] }},
                                @endforeach
                            ],
                            borderColor: 'rgba(255, 159, 64, 1)',
                            backgroundColor: 'rgba(255, 159, 64, 0.2)',
                            fill: true,
                            hidden: true,
                        },
                        {
                            label: 'Productos',
                            data: [
                                @foreach ($datosMes as $fecha => $datosDia)
                                    @if ($fecha === 'nombre')
                                        @continue
                                    @endif
                                    {{ $datosDia['productos'] }},
                                @endforeach
                            ],
                            borderColor: 'rgba(153, 102, 255, 1)',
                            backgroundColor: 'rgba(153, 102, 255, 0.2)',
                            fill: true,
                            hidden: true,
                        },
                        {
                            label: 'Inventario',
                            data: [
                                @foreach ($datosMes as $fecha => $datosDia)
                                    @if ($fecha === 'nombre')
                                        @continue
                                    @endif
                                    {{ $datosDia['inventario'] }},
                                @endforeach
                            ],
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            fill: true,
                            hidden: true,
                        },
                        {
                            label: 'Cuentas',
                            data: [
                                @foreach ($datosMes as $fecha => $datosDia)
                                    @if ($fecha === 'nombre')
                                        @continue
                                    @endif
                                    {{ $datosDia['cuentas'] }},
                                @endforeach
                            ],
                            borderColor: 'rgba(201, 203, 207, 1)',
                            backgroundColor: 'rgba(201, 203, 207, 0.2)',
                            fill: true,
                            hidden: true,
                        },
                        {
                            label: 'Tareas',
                            data: [
                                @foreach ($datosMes as $fecha => $datosDia)
                                    @if ($fecha === 'nombre')
                                        @continue
                                    @endif
                                    {{ $datosDia['tareas'] }},
                                @endforeach
                            ],
                            borderColor: 'rgba(0, 128, 128, 1)',
                            backgroundColor: 'rgba(0, 128, 128, 0.2)',
                            fill: true,
                            hidden: true,
                        },
                        {
                            label: 'Costos',
                            data: [
                                @foreach ($datosMes as $fecha => $datosDia)
                                    @if ($fecha === 'nombre')
                                        @continue
                                    @endif
                                    {{ $datosDia['costos'] }},
                                @endforeach
                            ],
                            borderColor: 'rgba(255, 0, 255, 1)',
                            backgroundColor: 'rgba(255, 0, 255, 0.2)',
                            fill: true,
                            hidden: true,
                        },
                        {
                            label: 'Clientes',
                            data: [
                                @foreach ($datosMes as $fecha => $datosDia)
                                    @if ($fecha === 'nombre')
                                        @continue
                                    @endif
                                    {{ $datosDia['clientes'] }},
                                @endforeach
                            ],
                            borderColor: 'rgba(0, 204, 102, 1)',
                            backgroundColor: 'rgba(0, 204, 102, 0.2)',
                            fill: true,
                            hidden: true,
                        },
                        {
                            label: 'Gastos',
                            data: [
                                @foreach ($datosMes as $fecha => $datosDia)
                                    @if ($fecha === 'nombre')
                                        @continue
                                    @endif
                                    {{ $datosDia['gastos'] }},
                                @endforeach
                            ],
                            borderColor: 'rgba(255, 0, 0, 1)',
                            backgroundColor: 'rgba(255, 0, 0, 0.2)',
                            fill: true,
                            hidden: true,
                        },
                    ],
                };

                const config{{ $idemp }} = {
                    type: 'line',
                    data: data{{ $idemp }},
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Estadísticas del Mes'
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Días del Mes'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Cantidad'
                                },
                                beginAtZero: true
                            }
                        }
                    }
                };

                new Chart(
                    document.getElementById('chart-{{ $idemp }}'),
                    config{{ $idemp }}
                );
            @endforeach
        });
    </script>
@endsection
