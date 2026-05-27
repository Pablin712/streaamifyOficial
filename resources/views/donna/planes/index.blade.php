@extends('layouts.table')

@section('title', 'Donna — Planes')

@section('styles')
<style>
    .badge-personal { background-color: #274698; color: #fff; }
    .badge-business { background-color: #E4B100; color: #1D1D1B; }
</style>
@endsection

@section('h1', 'Donna — Planes')

@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <h5 class="mb-1 fw-bold">
        <i class="bi bi-robot me-2" style="color:#274698;"></i>Planes de Donna
    </h5>
    <p class="text-muted mb-0">
        Define los planes que los clientes pueden contratar. El precio se refleja automáticamente en
        <a href="{{ route('donna') }}" target="_blank">/donna</a>.
    </p>
@endsection

@section('btncrear')
    <div class="d-flex flex-wrap gap-2 mb-3">
        @if (Auth::user()->hasPermissionTo('donna.planes.store'))
            <button type="button" class="btn btn-primary" onclick="openCreatePlanModal()">
                <i class="fas fa-plus me-1"></i> Nuevo Plan
            </button>
        @endif
        <a href="{{ route('donna') }}" target="_blank" class="btn btn-outline-secondary">
            <i class="fas fa-eye me-1"></i> Ver página pública
        </a>
    </div>
@endsection

@section('tablename')
    <i class="bi bi-robot me-1"></i> Planes Donna
@endsection

@section('table1')
    <!-- Controles de búsqueda -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="donna-planes-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="donna-planes-table-search" type="text"
                   placeholder="Buscar plan..." class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="donna-planes-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="donna-planes-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="25">25 registros</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="donna-planes-table" data-table="donna-planes-table"
               class="table table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th class="sortable" data-type="number" data-col="0">
                        ID <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="1">
                        Plan <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="2">
                        Tipo <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="number" data-col="3">
                        Precio <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th>Ciclo</th>
                    <th>Características</th>
                    <th class="sortable" data-type="string" data-col="6">
                        Estado <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    @if (Auth::user()->hasAnyPermission(['donna.planes.store', 'donna.planes.destroy']))
                        <th data-type="actions">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($planes as $plan)
                    <tr>
                        <td>{{ $plan->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $plan->name }}</div>
                            <div class="text-muted small font-monospace">{{ $plan->code }}</div>
                        </td>
                        <td>
                            @if ($plan->service_type === 'personal')
                                <span class="badge badge-personal"><i class="fas fa-user me-1"></i>Personal</span>
                            @else
                                <span class="badge badge-business"><i class="fas fa-building me-1"></i>Business</span>
                            @endif
                        </td>
                        <td class="fw-bold">
                            ${{ number_format($plan->price, 2) }}
                            <span class="text-muted fw-normal small">{{ $plan->currency }}</span>
                        </td>
                        <td class="small">
                            @match($plan->billing_cycle)
                                'monthly'  => 'Mensual',
                                'yearly'   => 'Anual',
                                'one_time' => 'Único',
                                default    => $plan->billing_cycle
                            @endmatch
                        </td>
                        <td>
                            @if (!empty($plan->features_json))
                                @foreach (array_slice($plan->features_json, 0, 2) as $f)
                                    <span class="badge bg-light text-dark border small me-1 mb-1">{{ $f }}</span>
                                @endforeach
                                @if (count($plan->features_json) > 2)
                                    <span class="text-muted small">+{{ count($plan->features_json) - 2 }} más</span>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($plan->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                        @if (Auth::user()->hasAnyPermission(['donna.planes.store', 'donna.planes.destroy']))
                            <td>
                                @if (Auth::user()->hasPermissionTo('donna.planes.store'))
                                    <button type="button" class="btn btn-warning btn-sm"
                                        onclick="openEditPlanModal({{ $plan->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                                @if (Auth::user()->hasPermissionTo('donna.planes.destroy'))
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="openDeletePlanModal({{ $plan->id }}, '{{ addslashes($plan->name) }}', '{{ number_format($plan->price, 2) }}', '{{ $plan->service_type }}', {{ $plan->is_active ? 'true' : 'false' }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-robot fs-1 d-block mb-3" style="color:#274698;opacity:0.3;"></i>
                            No hay planes creados aún.<br>
                            <small>Usa el botón <strong>Nuevo Plan</strong> para empezar.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="row mt-3 align-items-center">
        <div class="col-md-6 col-12 mb-2 mb-md-0">
            <div id="donna-planes-table-row-info" class="text-muted"></div>
        </div>
        <div class="col-md-6 col-12">
            <div id="donna-planes-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
        </div>
    </div>

    {{-- Modales --}}
    @include('donna.planes.modals.create')
    @include('donna.planes.modals.edit')
    @include('donna.planes.modals.delete')
@endsection

@section('scripts')
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
<script>
// ── Helpers de características ─────────────────────────────────────────────
function addFeatureRowCreate() {
    const list = document.getElementById('create_features_list');
    const row = document.createElement('div');
    row.className = 'd-flex gap-2 mb-2';
    row.innerHTML = `
        <input type="text" name="features[]" class="form-control form-control-sm"
               placeholder="Ej: Agenda citas en Google Calendar" maxlength="200">
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>`;
    list.appendChild(row);
}

function addFeatureRowEdit(value = '') {
    const list = document.getElementById('edit_features_list');
    const row = document.createElement('div');
    row.className = 'd-flex gap-2 mb-2';
    row.innerHTML = `
        <input type="text" name="features[]" class="form-control form-control-sm"
               placeholder="Característica..." maxlength="200" value="${value.replace(/"/g, '&quot;')}">
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>`;
    list.appendChild(row);
}

// ── Modal CREAR ────────────────────────────────────────────────────────────
function openCreatePlanModal() {
    document.getElementById('createDonnaPlanForm').reset();
    document.getElementById('create_features_list').innerHTML = '';
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'createDonnaPlanModal' }));
}

async function submitCreatePlan(event) {
    event.preventDefault();
    const form = document.getElementById('createDonnaPlanForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('{{ route("donna.planes.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createDonnaPlanModal' }));
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Error al crear el plan.');
        }
    } catch (e) {
        alert('Error de conexión. Intenta nuevamente.');
    }
}

// ── Modal EDITAR ───────────────────────────────────────────────────────────
function openEditPlanModal(id) {
    fetch(`/admin/donna/planes/${id}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(plan => {
        document.getElementById('edit_plan_id').value          = plan.id;
        document.getElementById('edit_code').value             = plan.code;
        document.getElementById('edit_name').value             = plan.name;
        document.getElementById('edit_service_type').value     = plan.service_type;
        document.getElementById('edit_description').value      = plan.description ?? '';
        document.getElementById('edit_price').value            = plan.price;
        document.getElementById('edit_billing_cycle').value    = plan.billing_cycle;
        document.getElementById('edit_is_active').value        = plan.is_active ? '1' : '0';
        document.getElementById('edit_sort_order').value       = plan.sort_order;

        // Cargar características
        const list = document.getElementById('edit_features_list');
        list.innerHTML = '';
        const features = plan.features_json ?? [];
        features.forEach(f => addFeatureRowEdit(f));

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editDonnaPlanModal' }));
    })
    .catch(() => alert('Error al cargar los datos del plan.'));
}

async function submitEditPlan(event) {
    event.preventDefault();
    const id = document.getElementById('edit_plan_id').value;
    const form = document.getElementById('editDonnaPlanForm');
    const formData = new FormData(form);
    // Laravel necesita _method=PUT en FormData para spoofing
    formData.set('_method', 'PUT');

    try {
        const response = await fetch(`/admin/donna/planes/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editDonnaPlanModal' }));
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Error al actualizar el plan.');
        }
    } catch (e) {
        alert('Error de conexión. Intenta nuevamente.');
    }
}

// ── Modal ELIMINAR ─────────────────────────────────────────────────────────
function openDeletePlanModal(id, nombre, precio, tipo, activo) {
    document.getElementById('delete_plan_id').value     = id;
    document.getElementById('delete_plan_nombre').textContent = nombre;
    document.getElementById('delete_plan_precio').textContent = '$' + precio;
    document.getElementById('delete_plan_tipo').textContent   = tipo === 'personal' ? 'Personal' : 'Business';
    document.getElementById('delete_plan_estado').innerHTML   = activo
        ? '<span class="badge bg-success">Activo</span>'
        : '<span class="badge bg-danger">Inactivo</span>';
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'deleteDonnaPlanModal' }));
}

async function submitDeletePlan(event) {
    event.preventDefault();
    const id = document.getElementById('delete_plan_id').value;

    try {
        const response = await fetch(`/admin/donna/planes/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: new URLSearchParams({ '_method': 'DELETE', '_token': '{{ csrf_token() }}' })
        });
        const data = await response.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteDonnaPlanModal' }));
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Error al eliminar el plan.');
        }
    } catch (e) {
        alert('Error de conexión. Intenta nuevamente.');
    }
}
</script>
@endsection
