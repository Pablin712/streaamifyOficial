@extends('layouts.static')
@section('title', 'Tareas')
@section('breadcrumb') Tareas @endsection
@section('introduccion')
    <p>
        Toma las tareas del pool que quieras trabajar, o espera a que te asignen.
        Cuando termines, márcalas como completadas. Puedes devolver una tarea al pool
        si no puedes hacerla.
    </p>
@endsection
@section('content')
    <div class="container-fluid px-2 px-md-3">
        @livewire('tareas-board')
    </div>
@endsection

@section('modals')
<x-modal name="wsp-cobro-modal" :show="false" maxWidth="md">
    <div class="modal-header" style="background:#075e54;color:#fff;">
        <h5 class="modal-title" style="font-size:.97rem;">
            <i class="fab fa-whatsapp me-2"></i>Enviar WhatsApp — Cobro
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="wspCerrar()"></button>
    </div>
    <div class="modal-body">
        <div class="row mb-3 g-2">
            <div class="col-6">
                <div class="p-2 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <small class="d-block fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;color:#6b7280;">Cliente</small>
                    <span class="fw-semibold" id="wsp-modal-cliente">—</span>
                </div>
            </div>
            <div class="col-6">
                <div class="p-2 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <small class="d-block fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;color:#6b7280;">Teléfono</small>
                    <span id="wsp-modal-telefono">—</span>
                </div>
            </div>
        </div>
        <div>
            <label class="form-label fw-semibold mb-1" style="font-size:.83rem;">Mensaje</label>
            <textarea id="wsp-modal-mensaje" class="form-control" rows="4"
                      placeholder="Escribe el mensaje para el cliente..."></textarea>
            <small class="text-muted" style="font-size:.72rem;">
                Variables: <code>{nombre}</code> nombre del cliente · <code>{fecha}</code> fecha de vencimiento.
            </small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" onclick="wspCerrar()">Cancelar</button>
        <button type="button" id="wsp-modal-send" class="btn btn-success btn-sm" onclick="wspConfirmar()">
            <i class="fab fa-whatsapp me-1"></i>Enviar por WhatsApp
        </button>
    </div>
</x-modal>
@endsection

@section('scripts')
<style>
    #wsp-toast {
        display:none; position:fixed; bottom:1.5rem; right:1.5rem; z-index:99999;
        padding:.65rem 1rem; border-radius:.5rem; font-size:.87rem; font-weight:600;
        box-shadow:0 4px 15px rgba(0,0,0,.18); max-width:320px; color:#fff;
        transition:opacity .4s;
    }
</style>
<div id="wsp-toast"></div>
<script>
(function() {
    const wspSelected = new Map(); // tareaId (int) → {nombre, telefono, idcli, mensaje}
    let wspSingleData = null;

    // ── Abrir modal individual ────────────────────────────────────────────────
    window.wspAbrirDesdeBtn = function(btn) {
        const d = btn.dataset;
        wspSingleData = {nombre: d.nombre || '', telefono: d.tel || '', idcli: parseInt(d.idcli || 0), mensaje: d.msg || ''};
        if (!wspSingleData.telefono) { wspToast('⚠️ Este cliente no tiene teléfono registrado.', 'warning'); return; }
        document.getElementById('wsp-modal-cliente').textContent  = wspSingleData.nombre   || 'Cliente';
        document.getElementById('wsp-modal-telefono').textContent = wspSingleData.telefono || '—';
        const ta  = document.getElementById('wsp-modal-mensaje');
        const btn2 = document.getElementById('wsp-modal-send');
        if (ta)   { ta.value = wspSingleData.mensaje || ''; }
        if (btn2) { btn2.disabled = false; btn2.innerHTML = '<i class="fab fa-whatsapp me-1"></i>Enviar por WhatsApp'; }
        window.dispatchEvent(new CustomEvent('open-modal', {detail: 'wsp-cobro-modal'}));
        setTimeout(() => document.getElementById('wsp-modal-mensaje')?.focus(), 150);
    };

    window.wspCerrar = function() {
        window.dispatchEvent(new CustomEvent('close-modal', {detail: 'wsp-cobro-modal'}));
    };

    // ── Confirmar envío individual ────────────────────────────────────────────
    window.wspConfirmar = async function() {
        const ta     = document.getElementById('wsp-modal-mensaje');
        const btn    = document.getElementById('wsp-modal-send');
        const mensaje = ta?.value.trim() || '';
        if (mensaje.length < 3) { wspToast('⚠️ El mensaje debe tener al menos 3 caracteres.', 'warning'); return; }
        if (!wspSingleData?.telefono) { wspToast('⚠️ Sin teléfono registrado.', 'warning'); return; }
        try {
            if (btn) { btn.disabled = true; btn.textContent = 'Enviando...'; }
            const r = await wspFetch({
                id_cliente: wspSingleData.idcli,
                cliente: wspSingleData.nombre,
                telefono: wspSingleData.telefono,
                mensaje,
                channel_preference: 'verde'
            });
            if (!r.ok || !r.data.success) throw new Error(r.data.message || 'Error al enviar.');
            wspCerrar();
            wspToast('✅ ' + (r.data.message || 'Mensaje enviado.'), 'success');
        } catch(e) {
            wspToast('❌ ' + (e.message || 'No se pudo enviar.'), 'danger');
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fab fa-whatsapp me-1"></i>Enviar por WhatsApp'; }
        }
    };

    // ── Enviar a todos (batch desde data-tareas del botón) ────────────────────
    window.wspEnviarTodos = async function(btnEl) {
        const btn = btnEl.closest('button') || btnEl;
        let todos = [];
        try { todos = JSON.parse(btn.dataset.tareas || '[]'); } catch(e) {
            wspToast('⚠️ Error al leer datos. Recarga la página.', 'warning'); return;
        }
        if (!todos.length) {
            wspToast('⚠️ Sin datos de cobro. Recarga la página.', 'warning'); return;
        }
        const tareas = todos.filter(t => t.telefono);
        const sinTel = todos.length - tareas.length;
        if (!tareas.length) {
            wspToast(`⚠️ Ningún cliente tiene teléfono registrado (${todos.length} tareas sin teléfono). Agrega teléfonos en los perfiles de cliente.`, 'warning');
            return;
        }
        const confirmMsg = `¿Enviar mensaje a ${tareas.length} cliente${tareas.length !== 1 ? 's' : ''}?`
            + (sinTel > 0 ? ` (${sinTel} sin teléfono serán omitidos)` : '');
        if (!confirm(confirmMsg)) return;
        await wspEnviarBatch(tareas);
    };

    // ── Enviar seleccionados ──────────────────────────────────────────────────
    window.wspEnviarSeleccionados = async function() {
        if (!wspSelected.size) { wspToast('⚠️ No hay tareas seleccionadas.', 'warning'); return; }
        const tareas = [...wspSelected.values()].filter(t => t.telefono);
        const sinTel = wspSelected.size - tareas.length;
        if (!tareas.length) { wspToast('⚠️ Ningún cliente tiene teléfono registrado.', 'warning'); return; }
        const confirmMsg = `¿Enviar mensaje a ${tareas.length} cliente${tareas.length !== 1 ? 's' : ''}?`
            + (sinTel > 0 ? ` (${sinTel} sin teléfono serán omitidos)` : '');
        if (!confirm(confirmMsg)) return;
        await wspEnviarBatch(tareas);
        wspSelected.clear();
        document.querySelectorAll('.task-select-cb').forEach(cb => cb.checked = false);
        wspUpdateSelUI();
    };

    // ── Batch send — vía n8n webhook para evitar bloqueo de cuenta ───────────
    async function wspEnviarBatch(tareas) {
        wspToast('⏳ Enviando a n8n...', 'success');
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const resp = await fetch('{{ route("tareas.enviarCobrosWspMasivo") }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify({
                    destinatarios: tareas.map(t => ({
                        nombre:   t.nombre   || '',
                        telefono: t.telefono || '',
                        idcli:    t.idcli    || 0,
                        mensaje:  t.mensaje  || '',
                    }))
                })
            });
            const data = await resp.json();
            if (resp.ok && data.success) {
                wspToast('✅ ' + (data.message || 'Enviado correctamente.'), 'success');
            } else {
                wspToast('❌ ' + (data.message || 'Error al enviar.'), 'danger');
            }
        } catch(e) {
            wspToast('❌ Error de conexión al enviar a n8n.', 'danger');
        }
    }

    // ── Checkbox toggle ───────────────────────────────────────────────────────
    window.wspToggle = function(cb) {
        const d = cb.dataset;
        if (cb.checked) {
            wspSelected.set(parseInt(d.tid), {nombre: d.nombre || '', telefono: d.tel || '', idcli: parseInt(d.idcli || 0), mensaje: d.msg || ''});
        } else {
            wspSelected.delete(parseInt(d.tid));
        }
        wspUpdateSelUI();
    };

    window.wspSelectAll = function() {
        const cbs = document.querySelectorAll('.task-select-cb');
        const allChecked = wspSelected.size === cbs.length && cbs.length > 0;
        wspSelected.clear();
        cbs.forEach(cb => {
            cb.checked = !allChecked;
            if (!allChecked) {
                const d = cb.dataset;
                wspSelected.set(parseInt(d.tid), {nombre: d.nombre || '', telefono: d.tel || '', idcli: parseInt(d.idcli || 0), mensaje: d.msg || ''});
            }
        });
        wspUpdateSelUI();
    };

    function wspUpdateSelUI() {
        const count   = wspSelected.size;
        const allCbs  = document.querySelectorAll('.task-select-cb').length;
        const selBtn  = document.getElementById('wsp-btn-sel');
        const selCnt  = document.getElementById('wsp-sel-count');
        const allLbl  = document.getElementById('wsp-all-sel-label');
        if (selBtn)  selBtn.style.display = count > 0 ? '' : 'none';
        if (selCnt)  selCnt.textContent   = count;
        if (allLbl)  allLbl.textContent   = (count === allCbs && allCbs > 0) ? 'Deseleccionar todos' : 'Seleccionar todos';
    }

    // ── Fetch helper ──────────────────────────────────────────────────────────
    async function wspFetch(body) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const resp = await fetch('{{ route("usuarios.enviarMensajeCliente") }}', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'},
            body: JSON.stringify(body)
        });
        return {ok: resp.ok, data: await resp.json()};
    }

    // ── Toast ─────────────────────────────────────────────────────────────────
    window.wspToast = function(msg, type) {
        const el = document.getElementById('wsp-toast');
        if (!el) return;
        el.style.background = {success:'#16a34a', warning:'#d97706', danger:'#dc2626'}[type] || '#16a34a';
        el.textContent = msg;
        el.style.opacity = '1';
        el.style.display = 'block';
        clearTimeout(el._t);
        el._t = setTimeout(() => {
            el.style.opacity = '0';
            setTimeout(() => { el.style.display = 'none'; }, 400);
        }, 3500);
    };

    // ── Sync Map con DOM tras updates de Livewire ─────────────────────────────
    document.addEventListener('livewire:update', () => {
        const domIds = new Set([...document.querySelectorAll('.task-select-cb')].map(cb => parseInt(cb.dataset.tid)));
        for (const id of wspSelected.keys()) {
            if (!domIds.has(id)) wspSelected.delete(id);
        }
        wspUpdateSelUI();
    });

    // ── Notificaciones desde Livewire dispatch('notify', ...) ─────────────────
    window.addEventListener('notify', function(e) {
        const d = e.detail;
        if (typeof wspToast === 'function') wspToast(d.msg ?? '', d.type ?? 'success');
    });
})();
</script>
@endsection
