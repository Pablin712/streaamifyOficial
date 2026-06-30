@extends('layouts.table')

@section('title', 'Donna — Configuración del agente')

@section('styles')
<style>
    :root { --donna-blue: #274698; --donna-gold: #E4B100; }
    .donna-section {
        background: #f4f6ff;
        border: 1px solid #c5cae9;
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    [data-dark-mode="true"] .donna-section {
        background: rgba(39, 70, 152, 0.12);
        border-color: rgba(39, 70, 152, 0.4);
    }
    .donna-section-title {
        color: var(--donna-blue);
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('h1', 'Donna — Configuración del agente')

@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex align-items-center gap-3 mb-2">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-sliders me-2" style="color: var(--donna-blue);"></i>
            Configuración de {{ $sub->cliente?->nombrecli ?? 'Cliente #' . $sub->client_id }}
        </h5>
        <span class="badge" style="background: {{ $sub->service_type === 'business' ? 'var(--donna-gold)' : 'var(--donna-blue)' }}; color: {{ $sub->service_type === 'business' ? '#1D1D1B' : '#fff' }};">
            {{ ucfirst($sub->service_type) }}
        </span>
        <span class="badge bg-{{ $sub->status_color }}">{{ $sub->status_label }}</span>
    </div>
    <p class="text-muted mb-0">
        Edita el prompt, el contexto del negocio y las reglas que sigue Donna para esta suscripción.
        Los cambios se aplican en el próximo mensaje, sin reiniciar nada.
    </p>
@endsection

@section('tablename')
    <i class="bi bi-robot me-1"></i> Agente — Suscripción #{{ $sub->id }}
@endsection

@section('table1')
<a href="{{ route('donna.suscripciones.index') }}" class="btn btn-link px-0 mb-3">
    <i class="bi bi-arrow-left me-1"></i>Volver a Suscripciones
</a>

<form method="POST" action="{{ route('donna.suscripciones.config.update', $sub->id) }}">
    @csrf

    {{-- Variables del agente --}}
    <div class="donna-section">
        <div class="donna-section-title"><i class="bi bi-robot me-1"></i>Variables del agente</div>
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <label class="form-label fw-semibold small mb-1">Nombre del agente</label>
                <input type="text" name="agent_name" class="form-control form-control-sm" maxlength="50"
                       placeholder="Donna" value="{{ old('agent_name', $config->agent_name) }}">
            </div>
            @if($sub->service_type === 'business')
            <div class="col-sm-6 col-lg-3">
                <label class="form-label fw-semibold small mb-1">Nombre del negocio</label>
                <input type="text" name="business_name" class="form-control form-control-sm" maxlength="150"
                       value="{{ old('business_name', $config->business_name) }}">
            </div>
            @endif
            <div class="col-sm-6 col-lg-3">
                <label class="form-label fw-semibold small mb-1">Tono</label>
                <input type="text" name="tone" class="form-control form-control-sm" maxlength="30"
                       placeholder="profesional, amable y directa"
                       value="{{ old('tone', $config->tone) }}">
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="form-label fw-semibold small mb-1">Idioma</label>
                @php $lang = old('language', $config->language ?? 'es'); @endphp
                <select name="language" class="form-select form-select-sm">
                    <option value="es" @selected($lang === 'es')>Español</option>
                    <option value="en" @selected($lang === 'en')>English</option>
                </select>
            </div>

            <div class="col-sm-6 col-lg-4">
                <label class="form-label fw-semibold small mb-1">Zona horaria</label>
                @php
                    $tz = old('timezone', $config->timezone ?? 'America/Guayaquil');
                    $tzOptions = [
                        'America/Guayaquil'   => 'Guayaquil / Lima (UTC-5)',
                        'America/Bogota'      => 'Bogotá (UTC-5)',
                        'America/Mexico_City' => 'Ciudad de México (UTC-6)',
                        'America/New_York'    => 'New York (UTC-5/-4)',
                        'America/Los_Angeles' => 'Los Ángeles (UTC-8/-7)',
                        'America/Santiago'    => 'Santiago (UTC-4/-3)',
                        'America/Argentina/Buenos_Aires' => 'Buenos Aires (UTC-3)',
                        'Europe/Madrid'       => 'Madrid (UTC+1/+2)',
                        'UTC'                 => 'UTC',
                    ];
                @endphp
                <select name="timezone" class="form-select form-select-sm">
                    @foreach($tzOptions as $tzVal => $tzLabel)
                        <option value="{{ $tzVal }}" @selected($tz === $tzVal)>{{ $tzLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Horario desde</label>
                <input type="time" name="wh_start" class="form-control form-control-sm" value="{{ old('wh_start', $wh['start'] ?? '09:00') }}">
            </div>
            <div class="col-sm-4 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Horario hasta</label>
                <input type="time" name="wh_end" class="form-control form-control-sm" value="{{ old('wh_end', $wh['end'] ?? '18:00') }}">
            </div>
            <div class="col-sm-4 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Almuerzo</label>
                <input type="time" name="wh_lunch" class="form-control form-control-sm" value="{{ old('wh_lunch', $wh['lunch'] ?? '') }}">
            </div>

            @if($sub->service_type === 'business')
            <div class="col-sm-6 col-lg-3">
                <label class="form-label fw-semibold small mb-1">Google Calendar ID</label>
                <input type="text" name="calendar_id" class="form-control form-control-sm" maxlength="150"
                       placeholder="primary" value="{{ old('calendar_id', $config->calendar_id ?? 'primary') }}">
            </div>
            @endif

            <div class="col-12">
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $config->is_active ?? true))>
                    <label class="form-check-label fw-semibold small" for="is_active">Agente activo</label>
                    <div class="form-text">Si se desactiva, Donna deja de responder en este servicio aunque la suscripción siga vigente.</div>
                </div>
            </div>
        </div>
    </div>

    @if($sub->service_type === 'personal')
        {{-- Contexto personal --}}
        <div class="donna-section">
            <div class="donna-section-title"><i class="bi bi-person-lines-fill me-1"></i>Contexto personal</div>
            <textarea name="personal_context" class="form-control" rows="5" maxlength="3000"
                      placeholder="Profesión, proyectos activos, preferencias de comunicación...">{{ old('personal_context', $config->personal_context) }}</textarea>
        </div>
    @else
        {{-- Contexto del negocio --}}
        <div class="donna-section">
            <div class="donna-section-title"><i class="bi bi-building me-1"></i>Contexto del negocio</div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold small mb-1">Descripción del negocio</label>
                    <textarea name="business_description" class="form-control" rows="2" maxlength="3000"
                              placeholder="Qué hace el negocio, qué vende...">{{ old('business_description', $config->business_description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small mb-1">Contexto del negocio (detallado)</label>
                    <textarea name="business_context" class="form-control" rows="4" maxlength="5000">{{ old('business_context', $config->business_context) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small mb-1">Lógica de negocio (reglas especiales)</label>
                    <textarea name="business_logic" class="form-control" rows="4" maxlength="5000">{{ old('business_logic', $config->business_logic) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small mb-1">Reglas de ventas</label>
                    <textarea name="sales_rules" class="form-control" rows="3" maxlength="5000">{{ old('sales_rules', $config->sales_rules) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small mb-1">Reglas de soporte</label>
                    <textarea name="support_rules" class="form-control" rows="3" maxlength="5000">{{ old('support_rules', $config->support_rules) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Prompts y mensajes --}}
        <div class="donna-section">
            <div class="donna-section-title"><i class="bi bi-chat-square-text me-1"></i>Prompt y mensajes</div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold small mb-1">Prompt principal</label>
                    <textarea name="main_prompt" class="form-control font-monospace" rows="8" maxlength="8000"
                              placeholder="Instrucción principal que define cómo debe comportarse Donna para este negocio.">{{ old('main_prompt', $config->main_prompt) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small mb-1">Mensaje de bienvenida</label>
                    <textarea name="welcome_message" class="form-control" rows="2" maxlength="1000">{{ old('welcome_message', $config->welcome_message) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small mb-1">Mensaje fallback (no sabe responder)</label>
                    <textarea name="fallback_prompt" class="form-control" rows="2" maxlength="2000">{{ old('fallback_prompt', $config->fallback_prompt) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small mb-1">Mensaje fuera de horario</label>
                    <textarea name="out_of_hours_message" class="form-control" rows="2" maxlength="1000">{{ old('out_of_hours_message', $config->out_of_hours_message) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small mb-1">Mensaje de escalado a humano</label>
                    <textarea name="human_handoff_msg" class="form-control" rows="2" maxlength="1000">{{ old('human_handoff_msg', $config->human_handoff_msg) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Herramientas y comportamiento --}}
        <div class="donna-section">
            <div class="donna-section-title"><i class="bi bi-toggles me-1"></i>Herramientas y comportamiento</div>
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="knowledge_enabled" name="knowledge_enabled" value="1"
                               @checked(old('knowledge_enabled', $config->knowledge_enabled ?? false))>
                        <label class="form-check-label small" for="knowledge_enabled">Base de conocimiento</label>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="calendar_enabled" name="calendar_enabled" value="1"
                               @checked(old('calendar_enabled', $config->calendar_enabled ?? false))>
                        <label class="form-check-label small" for="calendar_enabled">Google Calendar</label>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="sheets_enabled" name="sheets_enabled" value="1"
                               @checked(old('sheets_enabled', $config->sheets_enabled ?? false))>
                        <label class="form-check-label small" for="sheets_enabled">Google Sheets</label>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="human_takeover_enabled" name="human_takeover_enabled" value="1"
                               @checked(old('human_takeover_enabled', $config->human_takeover_enabled ?? true))>
                        <label class="form-check-label small" for="human_takeover_enabled">Permitir atención humana</label>
                    </div>
                </div>

                <div class="col-sm-4">
                    <label class="form-label fw-semibold small mb-1">Estilo de respuesta</label>
                    @php $rs = old('response_style', $config->response_style ?? 'concise'); @endphp
                    <select name="response_style" class="form-select form-select-sm">
                        <option value="concise" @selected($rs === 'concise')>Conciso</option>
                        <option value="moderate" @selected($rs === 'moderate')>Moderado</option>
                        <option value="detailed" @selected($rs === 'detailed')>Detallado</option>
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-semibold small mb-1">Espera antes de responder (seg)</label>
                    <input type="number" name="wait_seconds" class="form-control form-control-sm" min="3" max="60"
                           value="{{ old('wait_seconds', $config->wait_seconds ?? 35) }}">
                    <div class="form-text">Tiempo para agrupar varios mensajes seguidos del cliente.</div>
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-semibold small mb-1">Máx. llamadas a herramientas</label>
                    <input type="number" name="max_tool_calls" class="form-control form-control-sm" min="1" max="20"
                           value="{{ old('max_tool_calls', $config->max_tool_calls ?? 8) }}">
                </div>

                @if($config->spreadsheet_id)
                <div class="col-12">
                    <div class="text-muted small">
                        <i class="bi bi-grid me-1"></i>Spreadsheet conectado: <code>{{ $config->spreadsheet_name }}</code>
                        ({{ $config->spreadsheet_id }})
                    </div>
                </div>
                @endif
            </div>
        </div>
    @endif

    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
        <i class="bi bi-save me-1"></i>Guardar configuración
    </button>
</form>
@endsection
