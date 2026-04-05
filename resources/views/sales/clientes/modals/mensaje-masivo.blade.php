<x-modal name="mensajeMasivoClientesModal" :show="false" maxWidth="2xl">
    <div class="modal-header border-bottom">
        <h5 class="modal-title">
            <i class="fab fa-whatsapp me-2 text-success"></i>
            <span id="mensaje_masivo_clientes_titulo">Mensaje Masivo a Clientes Activos</span>
        </h5>
        <button type="button" class="btn-close" onclick="closeMensajeMasivoClientesModal()"></button>
    </div>

    <form id="mensajeMasivoClientesForm" onsubmit="submitMensajeMasivoClientes(event)">
        @csrf
        <input type="hidden" id="mensaje_masivo_clientes_segmento" name="segmento" value="sin_web">
        <div class="modal-body bg-body">
            <div class="alert alert-info mb-3">
                <div><strong>Destino:</strong> <span id="mensaje_masivo_clientes_destino">Clientes activos</span>.</div>
                <div><strong>Total actual:</strong> <span id="mensaje_masivo_clientes_total">0</span></div>
            </div>

            <div class="mb-3">
                <label for="mensaje_masivo_clientes_texto" class="form-label fw-semibold">Plantilla del mensaje</label>
                <textarea
                    id="mensaje_masivo_clientes_texto"
                    name="mensaje"
                    class="form-control font-monospace"
                    rows="11"
                    maxlength="4000"
                    required
                ></textarea>
                <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                    <span>Puedes editar esta plantilla antes de enviarla.</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="restaurarPlantillaMensajeMasivo()">
                        <i class="fas fa-rotate-left me-1"></i>Restaurar plantilla
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-footer border-top">
            <button type="button" class="btn btn-secondary" onclick="closeMensajeMasivoClientesModal()">
                Cerrar
            </button>
            <button type="submit" id="mensaje_masivo_clientes_submit" class="btn btn-success">
                <i class="fab fa-whatsapp me-1"></i>Enviar mensaje masivo
            </button>
        </div>
    </form>
</x-modal>
