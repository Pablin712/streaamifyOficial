@props(['eval'])

{{--
    Tarjeta de meta con semaforo. El color no dice si la cifra es alta o baja:
    dice si se va a cumplir el objetivo al ritmo actual.
--}}
<div class="meta-card meta-card--{{ $eval['color'] }}">
    <div class="meta-card-head">
        <span class="meta-card-icon"><i class="fas {{ $eval['definicion']['icono'] }}"></i></span>
        <span class="meta-card-label">{{ $eval['definicion']['label'] }}</span>
        <span class="meta-card-pill">{{ $eval['etiqueta'] }}</span>
    </div>

    <div class="meta-card-cifras">
        <span class="meta-card-valor sf-num">{{ $eval['f_actual'] }}</span>
        <span class="meta-card-objetivo sf-num">
            {{ $eval['definicion']['direccion'] === 'subir' ? 'de' : 'limite' }} {{ $eval['f_objetivo'] }}
        </span>
    </div>

    <div class="meta-card-barra">
        <div class="meta-card-track">
            <div class="meta-card-fill" style="width: {{ round($eval['avance_barra'], 1) }}%"></div>
            @if (!is_null($eval['marca_ritmo']) && $eval['en_curso'])
                {{-- Donde deberia ir hoy si el objetivo se repartiera por dias --}}
                <span class="meta-card-marca" style="left: {{ round($eval['marca_ritmo'], 1) }}%"
                      title="Ritmo esperado a dia de hoy: {{ $eval['f_esperado'] }}"></span>
            @endif
        </div>
        <span class="meta-card-pct sf-num">{{ number_format($eval['avance'], 0, ',', '.') }}%</span>
    </div>

    <p class="meta-card-mensaje">{{ $eval['mensaje'] }}</p>

    @if (!empty($eval['aviso']))
        <p class="meta-card-aviso"><i class="fas fa-circle-info"></i> {{ $eval['aviso'] }}</p>
    @endif

    <div class="meta-card-pie">
        @if ($eval['en_curso'])
            <span><i class="fas fa-calendar-day"></i> Día <span class="sf-num">{{ $eval['dias_pasados'] }}</span>/<span class="sf-num">{{ $eval['dias_totales'] }}</span></span>
        @elseif ($eval['cerrado'])
            <span><i class="fas fa-lock"></i> Periodo cerrado</span>
        @else
            <span><i class="fas fa-clock"></i> Aún no empieza</span>
        @endif

        @if ($eval['acumulativo'] && $eval['f_ritmo_actual'])
            <span><i class="fas fa-gauge-high"></i> Ritmo actual <span class="sf-num">{{ $eval['f_ritmo_actual'] }}</span>/día</span>
        @endif

        @if ($eval['meta']->nota)
            <span class="meta-card-nota"><i class="fas fa-note-sticky"></i> {{ $eval['meta']->nota }}</span>
        @endif
    </div>
</div>
