<x-modal name="deleteCuentaModal" :show="false" maxWidth="md">
    <div class="modal-header modal-header-danger">
        <h5 class="modal-title">
            <i class="fas fa-exclamation-triangle me-2"></i>Eliminar Cuenta
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteCuentaModal' }))"></button>
    </div>
    <form id="deleteCuentaForm" onsubmit="submitDelete(event)">
        @csrf
        @method('DELETE')
        <input type="hidden" id="delete_idcue" name="idcue">

        <div class="modal-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>¿Está seguro que desea eliminar esta cuenta?</strong>
                <p class="mb-0 mt-2">Esta acción no se puede deshacer.</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="card-title fw-bold text-danger mb-3">Información de la Cuenta</h6>

                    <div class="row mb-2">
                        <div class="col-5 fw-semibold">ID Cuenta:</div>
                        <div class="col-7" id="delete_cuenta_id"></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-5 fw-semibold">Servicio:</div>
                        <div class="col-7" id="delete_cuenta_servicio"></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-5 fw-semibold">Usuario:</div>
                        <div class="col-7" id="delete_cuenta_usuario"></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-5 fw-semibold">Vencimiento:</div>
                        <div class="col-7" id="delete_cuenta_vencimiento"></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-5 fw-semibold">Usuarios Activos:</div>
                        <div class="col-7">
                            <span id="delete_usuarios_count" class="badge bg-info"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-5 fw-semibold">Estado:</div>
                        <div class="col-7">
                            <span id="delete_cuenta_estado"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mostrar advertencia solo si hay usuarios activos (bloquea eliminación) -->
            <div id="delete_warning_usuarios" class="alert alert-danger mt-3" style="display: none;">
                <i class="fas fa-ban me-2"></i>
                <strong>⛔ NO SE PUEDE ELIMINAR:</strong> Esta cuenta tiene <strong><span id="delete_usuarios_count_text"></span> usuario(s) activo(s)</strong>.
                <p class="mb-0 mt-2">Primero debe mover los usuarios a otra cuenta o esperar a que expiren.</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-danger" id="delete_submit_btn">
                <i class="fas fa-trash me-1"></i> Sí, Eliminar Cuenta
            </button>
        </div>
    </form>
</x-modal>
