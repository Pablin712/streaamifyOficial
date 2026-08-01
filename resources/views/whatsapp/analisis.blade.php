@extends('layouts.static')
@section('title', 'Analisis de WhatsApp (IA)')
@section('h1', 'Analisis de WhatsApp')
@section('breadcrumb')
    <a href="{{ route('inicio') }}">Inicio</a>
@endsection
@section('breadcrumb2', 'WhatsApp (IA)')
@section('introduccion')
    Calidad de atencion, motivos de contacto y demoras de respuesta, clasificados por IA a partir de las conversaciones
    de WhatsApp. Fase 1 (prototipo) — no reparte puntos todavia. Ejecutado manualmente o semanal vía
    <code>php artisan whatsapp:analizar-satisfaccion</code>.
@endsection

@section('content')

<div class="d-flex gap-2 mb-4 flex-wrap align-items-center">
    <span class="text-muted small me-1 fw-semibold">Periodo:</span>
    <a href="?periodo=hoy"    class="btn btn-sm {{ $periodo === 'hoy'    ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">Hoy</a>
    <a href="?periodo=semana" class="btn btn-sm {{ $periodo === 'semana' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">Esta semana</a>
    <a href="?periodo=mes"    class="btn btn-sm {{ $periodo === 'mes'    ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">Este mes</a>
    <a href="?periodo=todo"   class="btn btn-sm {{ $periodo === 'todo'   ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">Todo</a>
</div>

@if ($totalAnalizadas === 0)
    <div class="alert alert-info">
        No hay conversaciones analizadas en este periodo. Corre
        <code>php artisan whatsapp:analizar-satisfaccion</code> para generar datos, o probá con otro periodo arriba
        (ej. "Todo").
    </div>
@else

{{-- Resumen general --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted small fw-semibold text-uppercase">Analizadas</div>
                <div class="fs-2 fw-bold">{{ $totalAnalizadas }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted small fw-semibold text-uppercase">Satisfaccion (prom. / moda)</div>
                <div class="fs-2 fw-bold">{{ $satisfaccionGlobal ?? '-' }}<span class="fs-6 text-muted">/5</span></div>
                <div class="small text-muted">Moda: {{ $moda ?? '-' }}/5</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted small fw-semibold text-uppercase">Tiempo resp. promedio</div>
                <div class="fs-2 fw-bold">{{ $tiempoRespuestaPromedioMin ?? '-' }}<span class="fs-6 text-muted">min</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted small fw-semibold text-uppercase">Con señal de pérdida</div>
                <div class="fs-2 fw-bold text-danger">{{ $totalPerdidas }}</div>
                <div class="small text-muted">{{ round($totalPerdidas / $totalAnalizadas * 100, 1) }}%</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Distribucion de satisfaccion --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Distribución de satisfacción</div>
            <div class="card-body">
                <table class="table table-sm mb-2">
                    <thead><tr><th>Score</th><th class="text-end">Cant.</th><th class="text-end">%</th></tr></thead>
                    <tbody>
                        @foreach (range(1, 5) as $score)
                            <tr>
                                <td>{{ $score }}★</td>
                                <td class="text-end">{{ $distribucionSatisfaccion[$score] }}</td>
                                <td class="text-end">{{ round($distribucionSatisfaccion[$score] / $totalAnalizadas * 100, 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-between small">
                    <span class="text-success fw-semibold">Buena (4-5★): {{ $buenas }} ({{ round($buenas / $totalAnalizadas * 100, 1) }}%)</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-warning fw-semibold">Regular (3★): {{ $regulares }} ({{ round($regulares / $totalAnalizadas * 100, 1) }}%)</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-danger fw-semibold">Mala (1-2★): {{ $malas }} ({{ round($malas / $totalAnalizadas * 100, 1) }}%)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Motivo de contacto --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Motivo de contacto</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach ([
                            'compra' => 'Compra',
                            'soporte_tecnico' => 'Soporte técnico',
                            'renovacion' => 'Renovación',
                            'solicitar_codigo' => 'Código',
                            'consulta_general' => 'Consulta general',
                            'otro' => 'Otro',
                        ] as $key => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td class="text-end">{{ $motivoContactoGlobal[$key] }}</td>
                                <td class="text-end text-muted">{{ round($motivoContactoGlobal[$key] / $totalAnalizadas * 100, 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Motivo de perdida / cruce de empleados --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Señales de pérdida</div>
            <div class="card-body">
                @if ($motivoPerdidaGlobal->isEmpty())
                    <p class="text-muted small mb-2">Sin señales de pérdida detectadas en este período.</p>
                @else
                    <table class="table table-sm mb-2">
                        <tbody>
                            @foreach ($motivoPerdidaGlobal as $key => $cantidad)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                    <td class="text-end">{{ $cantidad }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
                <hr>
                <div class="small text-muted fw-semibold text-uppercase mb-1">Cruce de empleados</div>
                <div class="small">Ninguno: {{ $cruceEmpleados['ninguno'] }} · Tardado: {{ $cruceEmpleados['tardado'] }} · Dividido: {{ $cruceEmpleados['dividido'] }}</div>
                <div class="small mt-1">Sin respuesta: <span class="{{ $sinRespuesta > 0 ? 'text-danger fw-semibold' : '' }}">{{ $sinRespuesta }}</span></div>
            </div>
        </div>
    </div>
</div>

{{-- Por servicio --}}
@if ($porServicio->isEmpty())
    <div class="alert alert-info">No hay conversaciones con servicio identificado en este período.</div>
@else
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Servicio</th>
                <th class="text-end">Cuentas activas</th>
                <th class="text-end">Conversaciones</th>
                <th class="text-end">Tasa /100 cuentas</th>
                <th class="text-end">Satisfacción</th>
                <th class="text-end">Tiempo resp.</th>
                <th class="text-end">Soporte técnico</th>
                <th class="text-end">Código</th>
                <th class="text-end">Compra</th>
                <th class="text-end">Renovación</th>
                <th class="text-end">Consulta gral.</th>
                <th class="text-end">Otro</th>
                <th class="text-end">Con pérdida</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($porServicio as $fila)
                <tr>
                    <td class="fw-semibold">{{ $fila['servicio']->nombreser }}</td>
                    <td class="text-end">{{ $fila['cuentas_activas'] }}</td>
                    <td class="text-end">{{ $fila['total_conversaciones'] }}</td>
                    <td class="text-end">{{ $fila['tasa_por_100_cuentas'] ?? '-' }}</td>
                    <td class="text-end">
                        @if ($fila['satisfaccion_promedio'] !== null)
                            <span class="badge {{ $fila['satisfaccion_promedio'] >= 4 ? 'bg-success' : ($fila['satisfaccion_promedio'] >= 3 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                {{ $fila['satisfaccion_promedio'] }}/5
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-end">{{ $fila['tiempo_respuesta_promedio_min'] !== null ? $fila['tiempo_respuesta_promedio_min'] . 'min' : '-' }}</td>
                    <td class="text-end">{{ $fila['por_motivo']['soporte_tecnico'] }}</td>
                    <td class="text-end">{{ $fila['por_motivo']['solicitar_codigo'] }}</td>
                    <td class="text-end">{{ $fila['por_motivo']['compra'] }}</td>
                    <td class="text-end">{{ $fila['por_motivo']['renovacion'] }}</td>
                    <td class="text-end">{{ $fila['por_motivo']['consulta_general'] }}</td>
                    <td class="text-end">{{ $fila['por_motivo']['otro'] }}</td>
                    <td class="text-end">
                        @if ($fila['perdidas'] > 0)
                            <span class="badge bg-danger">{{ $fila['perdidas'] }}</span>
                        @else
                            0
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<p class="text-muted small mt-2">
    "Tasa /100 cuentas" = conversaciones analizadas por cada 100 cuentas activas de ese servicio — permite comparar
    servicios de distinto tamaño sin que el más grande "gane" solo por tener más cuentas. "Tiempo resp." excluye
    huecos de más de 6h entre mensaje del cliente y respuesta (conversaciones que retoman un tema días después).
</p>
@endif

@if ($sinServicio > 0)
    <p class="text-muted small">{{ $sinServicio }} conversación(es) analizadas sin servicio identificable (consultas generales, saludos, etc.) no aparecen en la tabla por servicio.</p>
@endif

@endif

@endsection
