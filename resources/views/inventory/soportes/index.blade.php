@extends('layouts.navigation')

@section('title', 'Soportes')

@section('main')
    <div class="container-fluid px-4">
        <h1 class="mt-4">Soportes</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item">Cuentas</li>
            <li class="breadcrumb-item active">Soportes</li>
        </ol>

        <div id="alert-container"></div>

        @php
            $pendientes = $soportes->where('estado', 'pendiente')->count();
            $atendidos = $soportes->where('estado', 'atendido')->count();
        @endphp

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Pendientes</div>
                        <div class="display-6 fw-bold text-warning">{{ $pendientes }}</div>
                        <div class="small text-muted">Soportes que requieren atención técnica.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Atendidos</div>
                        <div class="display-6 fw-bold text-success">{{ $atendidos }}</div>
                        <div class="small text-muted">Casos con solución registrada.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Total</div>
                        <div class="display-6 fw-bold text-primary">{{ $soportes->count() }}</div>
                        <div class="small text-muted">Vista consolidada para el rol técnico.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-life-ring me-2"></i>Lista de Soportes
                </h6>
                <span class="text-muted small">Marca cuentas dañadas o registra la solución sin salir de la tabla.</span>
            </div>
            <div class="card-body">
                <div class="row mb-3 align-items-end">
                    <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                        <label for="soportes-table-search" class="form-label fw-semibold">
                            <i class="fas fa-search"></i> Buscar
                        </label>
                        <input id="soportes-table-search" type="text" class="form-control"
                            placeholder="Buscar por cliente, servicio, usuario, estado...">
                    </div>
                    <div class="col-lg-4 col-md-5 col-12">
                        <label for="soportes-table-rows-per-page" class="form-label fw-semibold">
                            <i class="fas fa-list"></i> Mostrar
                        </label>
                        <select id="soportes-table-rows-per-page" class="form-select">
                            <option value="5">5 registros</option>
                            <option value="10" selected>10 registros</option>
                            <option value="20">20 registros</option>
                            <option value="50">50 registros</option>
                            <option value="100">100 registros</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="soportes-table" data-table="soportes-table" class="table table-striped table-bordered align-middle">
                        <thead>
                            <tr>
                                <th class="sortable" data-type="number" data-col="0">#</th>
                                <th class="sortable" data-type="string" data-col="1">Servicio</th>
                                <th class="sortable" data-type="string" data-col="2">Usuario</th>
                                <th class="sortable" data-type="string" data-col="3">Contraseña</th>
                                <th class="sortable" data-type="string" data-col="4">Perfil</th>
                                <th class="sortable" data-type="string" data-col="5">Tipo</th>
                                <th data-type="actions">Descripción</th>
                                <th data-type="actions">Solución</th>
                                <th class="sortable" data-type="string" data-col="8">Estado</th>
                                <th data-type="actions">WhatsApp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($soportes as $soporte)
                                @php
                                    $cuenta = $soporte->cuenta;
                                    $servicio = optional(optional(optional($cuenta)->valor)->servicio)->nombreser ?? 'Servicio';
                                    $usuarioActivo = $soporte->usuarioActivoSoporte;
                                    $perfilNumero = $usuarioActivo?->perfil;
                                    $pinPerfil = optional($usuarioActivo?->profile)->pinper;
                                @endphp
                                <tr data-idsop="{{ $soporte->idsop }}" data-idcue="{{ $soporte->idcue }}">
                                    <td>{{ $soporte->idsop }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $servicio }}</div>
                                        <div class="small text-muted">Cliente: {{ $soporte->cliente?->nombrecli ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            <div>
                                                <span class="fw-semibold">{{ $cuenta?->usuariocue ?? 'Cuenta no disponible' }}</span>
                                                @if ($cuenta?->caidacue)
                                                    <span class="badge bg-danger ms-2 js-account-badge">Dañada</span>
                                                @else
                                                    <span class="badge bg-success ms-2 js-account-badge">Activa</span>
                                                @endif
                                            </div>
                                            @can('cuentas.status')
                                                <button type="button"
                                                    class="btn {{ $cuenta?->caidacue ? 'btn-danger' : 'btn-outline-danger' }} btn-sm align-self-start js-toggle-account"
                                                    onclick="toggleCuentaDanada('{{ $soporte->idcue }}', {{ $cuenta?->caidacue ? 'true' : 'false' }})">
                                                    <i class="fas {{ $cuenta?->caidacue ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                                    {{ $cuenta?->caidacue ? 'Desmarcar dañada' : 'Marcar dañada' }}
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                    <td>{{ $cuenta?->contrasenacue ?? 'N/A' }}</td>
                                    <td>
                                        @if ($perfilNumero)
                                            <span class="fw-semibold">Perfil {{ $perfilNumero }}</span>
                                            <div class="small text-muted">PIN: {{ $pinPerfil ?: 'Sin PIN' }}</div>
                                        @else
                                            <span class="text-muted">Sin perfil activo</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($soporte->tipo) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="openTextModal('Descripción del soporte #{{ $soporte->idsop }}', {{ json_encode($soporte->descripcion) }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                    <td>
                                        @if ($soporte->solucion)
                                            <button type="button" class="btn btn-outline-success btn-sm"
                                                onclick="openTextModal('Solución del soporte #{{ $soporte->idsop }}', {{ json_encode($soporte->solucion) }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @else
                                            <span class="text-muted small">Sin solución</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($soporte->estado === 'pendiente')
                                            <div class="d-flex flex-column gap-2">
                                                <span class="badge bg-warning text-dark align-self-start">Pendiente</span>
                                                @can('soportes.update')
                                                    <button type="button" class="btn btn-warning btn-sm align-self-start"
                                                        onclick="openAttendModal({{ $soporte->idsop }}, {{ json_encode($servicio) }}, {{ json_encode($cuenta?->usuariocue) }})">
                                                        <i class="fas fa-screwdriver-wrench me-1"></i>Atender
                                                    </button>
                                                @endcan
                                            </div>
                                        @else
                                            <span class="badge bg-success">Atendido</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($soporte->estado === 'atendido')
                                            @if ($soporte->whatsapp_enviado_at)
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="badge bg-success">
                                                        <i class="fab fa-whatsapp me-1"></i>Enviado
                                                    </span>
                                                    <span class="text-muted" style="font-size:0.7rem;">
                                                        {{ $soporte->whatsapp_enviado_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                            @else
                                                @can('soportes.update')
                                                    <button type="button"
                                                        class="btn btn-outline-success btn-sm"
                                                        onclick="enviarWhatsappSoporte({{ $soporte->idsop }}, this)">
                                                        <i class="fab fa-whatsapp me-1"></i>Enviar
                                                    </button>
                                                @endcan
                                            @endif
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div id="soportes-table-row-info" class="text-muted"></div>
                    </div>
                    <div class="col-md-6">
                        <div id="soportes-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="soporte-texto" :show="false" maxWidth="2xl">
        <div class="modal-header border-bottom">
            <h5 class="modal-title" id="soporte-texto-titulo">Detalle</h5>
            <button type="button" class="btn-close"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'soporte-texto' }))"></button>
        </div>
        <div class="modal-body bg-body">
            <p class="mb-0" id="soporte-texto-contenido"></p>
        </div>
        <div class="modal-footer border-top">
            <button type="button" class="btn btn-secondary"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'soporte-texto' }))">
                Cerrar
            </button>
        </div>
    </x-modal>

    <x-modal name="atender-soporte" :show="false" maxWidth="xl">
        <div class="modal-header border-bottom">
            <h5 class="modal-title">
                <i class="fas fa-screwdriver-wrench me-2"></i>Atender soporte
            </h5>
            <button type="button" class="btn-close"
                onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'atender-soporte' }))"></button>
        </div>
        <form id="attend-support-form" onsubmit="submitAttendSupport(event)">
            <div class="modal-body bg-body">
                <div class="alert alert-info">
                    Registra la solución aplicada para cerrar el soporte y dejar trazabilidad al cliente y al equipo.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Servicio</label>
                    <input type="text" id="attend-support-service" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cuenta</label>
                    <input type="text" id="attend-support-account" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label for="attend-support-solution" class="form-label fw-semibold">Solución</label>
                    <textarea id="attend-support-solution" class="form-control" rows="5" required
                        placeholder="Describe la solución aplicada."></textarea>
                </div>
                <div class="mb-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                            id="attend-support-whatsapp" checked>
                        <label class="form-check-label" for="attend-support-whatsapp">
                            <i class="fab fa-whatsapp text-success me-1"></i>
                            Enviar solución por WhatsApp al cliente
                        </label>
                    </div>
                    <div class="text-muted small mt-1" style="padding-left:2.5rem;">
                        Se enviará por el último canal de WhatsApp que usó el cliente.
                    </div>
                </div>
                <input type="hidden" id="attend-support-id" value="">
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary"
                    onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'atender-soporte' }))">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-1"></i>Guardar solución
                </button>
            </div>
        </form>
    </x-modal>
@endsection

@section('scripts')
    <script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>
    <script>
        function showAlert(message, type = 'success') {
            const container = document.getElementById('alert-container');
            if (!container) {
                return;
            }

            container.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }

        function openTextModal(title, content) {
            document.getElementById('soporte-texto-titulo').textContent = title;
            document.getElementById('soporte-texto-contenido').textContent = content;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'soporte-texto' }));
        }

        function openAttendModal(idsop, servicio, cuenta) {
            document.getElementById('attend-support-id').value = idsop;
            document.getElementById('attend-support-service').value = servicio;
            document.getElementById('attend-support-account').value = cuenta || 'Cuenta no disponible';
            document.getElementById('attend-support-solution').value = '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'atender-soporte' }));
        }

        function submitAttendSupport(event) {
            event.preventDefault();

            const idsop = document.getElementById('attend-support-id').value;
            const solucion = document.getElementById('attend-support-solution').value.trim();
            const enviarWhatsapp = document.getElementById('attend-support-whatsapp').checked;
            const url = "{{ route('soportes.atender', ':id') }}".replace(':id', idsop);

            const submitBtn = event.target.querySelector('[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ solucion, enviar_whatsapp: enviarWhatsapp })
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || data.error || 'No se pudo atender el soporte.');
                    }
                    return data;
                })
                .then(data => {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'atender-soporte' }));
                    let msg = data.message || 'Soporte atendido correctamente.';
                    if (enviarWhatsapp && !data.whatsapp_enviado && data.whatsapp_error) {
                        msg += ' (WhatsApp: ' + data.whatsapp_error + ')';
                        showAlert(msg, 'warning');
                    } else {
                        showAlert(msg, 'success');
                    }
                    setTimeout(() => window.location.reload(), 900);
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check me-1"></i>Guardar solución';
                    showAlert(error.message || 'No se pudo atender el soporte.', 'danger');
                });
        }

        function enviarWhatsappSoporte(idsop, btn) {
            if (!confirm('¿Enviar la solución por WhatsApp al cliente?')) return;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            const url = "{{ route('soportes.whatsapp', ':id') }}".replace(':id', idsop);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'No se pudo enviar el mensaje.');
                    }
                    return data;
                })
                .then(() => {
                    showAlert('Mensaje de solución enviado por WhatsApp.', 'success');
                    setTimeout(() => window.location.reload(), 700);
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fab fa-whatsapp me-1"></i>Enviar';
                    showAlert(error.message || 'No se pudo enviar el mensaje.', 'danger');
                });
        }

        function toggleCuentaDanada(idcue, estadoActual) {
            const url = "{{ route('cuentas.status', ':id') }}".replace(':id', idcue);

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'No se pudo actualizar el estado de la cuenta.');
                    }
                    return data;
                })
                .then(data => {
                    showAlert(data.message || 'Estado de la cuenta actualizado.', 'success');
                    setTimeout(() => window.location.reload(), 500);
                })
                .catch(error => {
                    showAlert(error.message || 'No se pudo actualizar el estado de la cuenta.', 'danger');
                });
        }
    </script>
@endsection
