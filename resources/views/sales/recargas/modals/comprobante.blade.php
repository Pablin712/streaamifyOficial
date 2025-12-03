<x-modal name="ver-comprobante" maxWidth="lg">
    <div class="modal-header">
        <h5 class="modal-title">Comprobante de Recarga</h5>
        <button type="button" class="btn-close" x-on:click="show = false" aria-label="Cerrar"></button>
    </div>

    <div class="modal-body text-center">
        <img id="comprobanteImg" src="" alt="Comprobante" class="img-fluid" style="max-width: 300px; height: auto;">
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" x-on:click="show = false">Cerrar</button>
    </div>
</x-modal>
