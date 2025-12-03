<x-modal name="cambiar-usuario" maxWidth="2xl">
    <div class="modal-header">
        <h5 class="modal-title" id="changeUsuarioModalTitle">
            <i class="fas fa-exchange-alt me-2"></i>Cambiar Usuario de Cuenta
        </h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <form id="changeUsuarioForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <!-- Información Actual del Usuario -->
            <div class="card mb-4 border-info">
                <div class="card-header border-info" style="background-color: var(--bs-light, #f8f9fa);">
                    <h6 class="mb-0 text-info fw-bold">
                        <i class="fas fa-info-circle me-2"></i>Información Actual
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="change_nombrecli" class="form-label fw-semibold">
                                <i class="fas fa-user me-1"></i>Cliente
                            </label>
                            <input type="text" id="change_nombrecli" class="form-control" style="background-color: var(--bs-light, #f8f9fa);" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="change_idven" class="form-label fw-semibold">
                                <i class="fas fa-receipt me-1"></i>ID de Venta
                            </label>
                            <input type="text" id="change_idven" class="form-control" style="background-color: var(--bs-light, #f8f9fa);" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nueva Asignación -->
            <div class="card mb-3 border-primary">
                <div class="card-header border-primary" style="background-color: var(--bs-light, #f8f9fa);">
                    <h6 class="mb-0 text-primary fw-bold">
                        <i class="fas fa-edit me-2"></i>Nueva Asignación
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Selector de Cuenta con información detallada -->
                    <div class="form-group mb-4">
                        <label for="change_idcue" class="form-label fw-semibold">
                            <i class="fas fa-list-alt me-1"></i>Seleccionar Nueva Cuenta *
                        </label>
                        <select name="idcue" id="change_idcue" class="form-control searchable-select" required
                                data-placeholder="Seleccione una cuenta...">
                            <option value="">-- Selecciona una Cuenta --</option>
                            @foreach ($cuentas as $cuenta)
                                <option value="{{ $cuenta->idcue }}"
                                        data-ocupados="{{ $cuenta->usuarios_activos ?? 0 }}"
                                        data-perfiles='@json($cuenta->perfiles ?? [])'>
                                    {{ $cuenta->idcue }} - {{ $cuenta->usuariocue }} | Ocupados: {{ $cuenta->usuarios_activos ?? 0 }} @if(isset($cuenta->perfiles) && count($cuenta->perfiles) > 0) | Perfiles: @foreach ($cuenta->perfiles as $perfil)P{{ $perfil->numeroper }}:{{ $perfil->usuarios_activos ?? 0 }}{{ !$loop->last ? ', ' : '' }}@endforeach @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Formato: <strong>ID Cuenta - Usuario | Ocupados: Total | Perfiles: P1:ocupados, P2:ocupados...</strong>
                        </small>
                    </div>

                    <div class="row">
                        <!-- Número de Perfil -->
                        <div class="col-md-6 mb-3">
                            <label for="change_perfil" class="form-label fw-semibold">
                                <i class="fas fa-user-circle me-1"></i>Número de Perfil *
                            </label>
                            <input type="number"
                                   name="perfil"
                                   id="change_perfil"
                                   class="form-control"
                                   min="1"
                                   max="7"
                                   required
                                   placeholder="Ej: 1, 2, 3...">
                            <small class="text-muted">Entre 1 y 7</small>
                        </div>

                        <!-- Fecha de Vencimiento -->
                        <div class="col-md-6 mb-3">
                            <label for="change_fecha_vencimiento" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha de Vencimiento *
                            </label>
                            <input type="date"
                                   name="fecha_vencimiento"
                                   id="change_fecha_vencimiento"
                                   class="form-control"
                                   required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de ayuda -->
            <div class="alert alert-info mb-0">
                <i class="fas fa-lightbulb me-2"></i>
                <strong>Tip:</strong> Selecciona una cuenta con perfiles disponibles y asigna el número de perfil correspondiente.
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" x-on:click="show = false">
                <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check me-1"></i>Cambiar Usuario
            </button>
        </div>
    </form>
</x-modal>
