@php
    // $tableId, $rows, $integraciones, $placeholder deben venir definidos por quien incluya este partial
@endphp
<div class="row mb-3 align-items-end">
    <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
        <label for="{{ $tableId }}-search" class="form-label fw-semibold">
            <i class="fas fa-search text-primary"></i> Buscar:
        </label>
        <input id="{{ $tableId }}-search" type="text"
               placeholder="Buscar cliente, plan, estado..." class="form-control">
    </div>
    <div class="col-lg-4 col-md-5 col-12">
        <label for="{{ $tableId }}-rows-per-page" class="form-label fw-semibold">
            <i class="fas fa-list text-primary"></i> Mostrar:
        </label>
        <select id="{{ $tableId }}-rows-per-page" class="form-select">
            <option value="5">5 registros</option>
            <option value="10" selected>10 registros</option>
            <option value="25">25 registros</option>
        </select>
    </div>
</div>

<div class="table-responsive">
    <table id="{{ $tableId }}" data-table="{{ $tableId }}"
           class="table table-striped table-bordered align-middle">
        <thead>
            <tr>
                <th class="sortable" data-type="number" data-col="0">ID <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                <th class="sortable" data-type="string" data-col="1">Cliente <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                <th class="sortable" data-type="string" data-col="2">Plan <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                <th>Tipo</th>
                <th>Google</th>
                <th class="sortable" data-type="string" data-col="5">Estado <span class="sort-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg></span></th>
                <th>Vencimiento</th>
                <th>Días restantes</th>
                @if (Auth::user()->hasPermissionTo('donna.suscripciones.store'))
                    <th data-type="actions">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $sub)
                @php
                    $dias = $sub->daysRemaining();
                    $badgeColor = $sub->status_color;
                    if ($sub->status === 'active' && $dias !== null && $dias <= 7) $badgeColor = 'warning';
                    $integ = $integraciones->get($sub->client_id);
                    $avatar = $integ?->metadata_json['avatar'] ?? null;
                @endphp
                <tr>
                    <td>{{ $sub->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($avatar)
                                <img src="{{ $avatar }}" alt=""
                                     class="rounded-circle flex-shrink-0"
                                     style="width:36px;height:36px;object-fit:cover;border:2px solid #dee2e6;"
                                     onerror="this.replaceWith(this.nextElementSibling)">
                                <span class="rounded-circle bg-light border d-none flex-shrink-0 align-items-center justify-content-center" style="width:36px;height:36px;">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                            @else
                                <span class="rounded-circle bg-light border d-flex flex-shrink-0 align-items-center justify-content-center" style="width:36px;height:36px;">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $sub->cliente?->nombrecli ?? 'ID '.$sub->client_id }}</div>
                                <div class="text-muted small">{{ $sub->cliente?->telefonocli }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $sub->plan?->name ?? 'ID '.$sub->plan_id }}</div>
                        <div class="text-muted small">${{ number_format($sub->price_paid, 2) }} {{ $sub->currency }}</div>
                    </td>
                    <td>
                        @if ($sub->service_type === 'personal')
                            <span class="badge badge-donna-personal">Personal</span>
                        @else
                            <span class="badge badge-donna-business">Business</span>
                        @endif
                    </td>
                    <td>
                        @if($integ && $integ->isActive())
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Conectado</span>
                            </div>
                            <div class="text-muted small mt-1" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                title="{{ $integ->metadata_json['email'] ?? '' }}">
                                {{ $integ->metadata_json['email'] ?? '' }}
                            </div>
                            @if($integ->isTokenExpired())
                                <span class="badge bg-warning text-dark mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Token expirado</span>
                            @endif
                        @elseif($integ && $integ->status === 'revoked')
                            <span class="badge bg-secondary">Revocado</span>
                        @else
                            <span class="badge bg-light text-muted border">Sin conectar</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $badgeColor }}">{{ $sub->status_label }}</span>
                        @if (!$sub->is_enabled && $sub->status === 'suspended')
                            <div class="text-muted small mt-1">{{ Str::limit($sub->suspended_reason, 40) }}</div>
                        @endif
                    </td>
                    <td class="small">
                        {{ $sub->expires_at?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="text-center">
                        @if ($dias === null)
                            <span class="text-muted small">Sin límite</span>
                        @elseif ($dias < 0)
                            <span class="badge bg-danger">Vencida</span>
                        @elseif ($dias <= 7)
                            <span class="badge bg-warning text-dark">{{ $dias }} días</span>
                        @else
                            <span class="text-success fw-semibold">{{ $dias }} días</span>
                        @endif
                    </td>
                    @if (Auth::user()->hasPermissionTo('donna.suscripciones.store'))
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('donna.suscripciones.config', $sub->id) }}" class="btn btn-outline-primary btn-sm" title="Configurar agente">
                                    <i class="bi bi-sliders"></i>
                                </a>
                                @if ($sub->service_type === 'business')
                                    <button type="button" class="btn btn-sm text-white" style="background-color:#25D366;"
                                        onclick="openChannelModal({{ $sub->id }}, '{{ addslashes($sub->cliente?->nombrecli ?? '') }}')"
                                        title="Configurar canal WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="deleteChannel({{ $sub->id }}, '{{ addslashes($sub->cliente?->nombrecli ?? '') }}')"
                                        title="Eliminar canal WhatsApp">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-success btn-sm"
                                    onclick="openRenewModal({{ $sub->id }}, '{{ addslashes($sub->cliente?->nombrecli ?? '') }}', '{{ $sub->expires_at?->format('Y-m-d') }}')">
                                    <i class="fas fa-redo" title="Renovar"></i>
                                </button>
                                @if ($sub->status === 'active')
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        onclick="openSuspendModal({{ $sub->id }}, '{{ addslashes($sub->cliente?->nombrecli ?? '') }}')">
                                        <i class="fas fa-ban" title="Suspender"></i>
                                    </button>
                                @endif
                                @if($integ && $integ->isActive())
                                    <form method="POST" action="{{ route('donna.integraciones.revoke', $integ->id) }}"
                                        onsubmit="return confirm('¿Revocar Google de {{ addslashes($sub->cliente?->nombrecli ?? '') }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Revocar Google">
                                            <i class="bi bi-google"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-robot fs-1 d-block mb-3" style="color:#274698;opacity:0.3;"></i>
                        {{ $placeholder ?? 'No hay suscripciones registradas.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="row mt-3 align-items-center">
    <div class="col-md-6 col-12 mb-2 mb-md-0">
        <div id="{{ $tableId }}-row-info" class="text-muted"></div>
    </div>
    <div class="col-md-6 col-12">
        <div id="{{ $tableId }}-pagination" class="d-flex justify-content-end flex-wrap"></div>
    </div>
</div>
