<x-modal name="donnaConvModal" maxWidth="lg">
    <div class="modal-header" style="background:#f8f9fc;border-bottom:1px solid #e9ecef;">
        <h5 class="modal-title fw-bold" id="donnaConvTitle">
            <i class="bi bi-chat-dots me-2" style="color:#274698;"></i>Conversación
        </h5>
        <button type="button" class="btn-close"
            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'donnaConvModal' }))">
        </button>
    </div>
    <div class="modal-body p-3" id="donnaConvBody" style="max-height:70vh;overflow-y:auto;">
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    </div>
</x-modal>
