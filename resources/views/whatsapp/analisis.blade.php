@extends('layouts.static')
@section('title', 'Analisis de WhatsApp (IA)')
@section('h1', 'Analisis de WhatsApp')
@section('breadcrumb')
    <a href="{{ route('inicio') }}">Inicio</a>
@endsection
@section('breadcrumb2', 'WhatsApp (IA)')
@section('introduccion')
    Calidad de atencion y motivos de contacto por servicio, clasificados por IA a partir de las conversaciones
    de WhatsApp. Fase 1 (prototipo) — no reparte puntos todavia. Ejecutado manualmente vía
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

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted small fw-semibold text-uppercase">Conversaciones analizadas</div>
                <div class="fs-2 fw-bold">{{ $totalAnalizadas }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted small fw-semibold text-uppercase">Satisfaccion promedio</div>
                <div class="fs-2 fw-bold">{{ $satisfaccionGlobal ?? '-' }}<span class="fs-6 text-muted">/5</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted small fw-semibold text-uppercase">Sin servicio identificado</div>
                <div class="fs-2 fw-bold">{{ $sinServicio }}</div>
            </div>
        </div>
    </div>
</div>

@if ($porServicio->isEmpty())
    <div class="alert alert-info">
        No hay conversaciones analizadas en este periodo todavia. Corre
        <code>php artisan whatsapp:analizar-satisfaccion</code> para generar datos.
    </div>
@else
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Servicio</th>
                <th class="text-end">Cuentas activas</th>
                <th class="text-end">Conversaciones</th>
                <th class="text-end">Tasa /100 cuentas</th>
                <th class="text-end">Satisfaccion</th>
                <th class="text-end">Soporte tecnico</th>
                <th class="text-end">Codigo</th>
                <th class="text-end">Compra</th>
                <th class="text-end">Renovacion</th>
                <th class="text-end">Consulta gral.</th>
                <th class="text-end">Otro</th>
                <th class="text-end">Con perdida</th>
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
    servicios de distinto tamano sin que el mas grande "gane" solo por tener mas cuentas.
</p>
@endif

@endsection
