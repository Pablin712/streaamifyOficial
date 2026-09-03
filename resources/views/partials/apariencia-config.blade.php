{{--
    Puente entre AparienciaService (PHP) y theme-manager.js.

    El catalogo de temas vive UNA sola vez, en PHP. Antes estaba duplicado en
    theme-manager.js y en la vista de sistema, y se desincronizaban.

    El <html> ya trae data-theme y data-dark-mode aplicados por el servidor;
    esto solo le dice a JS cual es el estado vigente y a donde escribir.
--}}
<script>
    window.StreamifyApariencia = {
        // Global: lo fija el administrador, lo ve todo el mundo
        tema: @json($apariencia['tema']),
        temaBase: @json($apariencia['temaBase']),
        autoTemporada: @json($apariencia['autoTemporada']),
        temaTemporada: @json($apariencia['temaTemporada']),
        decoracion: @json($apariencia['decoracion']),
        catalogo: @json($apariencia['catalogo']),
        // Personal: preferencia de quien está mirando
        esquema: @json($apariencia['esquema']),
        rutas: {
            leer: @json(route('apariencia.actual')),
            esquema: @json(route('apariencia.esquema')),
            @auth
                guardar: @json(route('sistema.apariencia.guardar')),
            @else
                guardar: null,
            @endauth
        },
        puedeEditar: @json(auth()->check() && auth()->user()->hasRole('Admin')),
        csrf: @json(csrf_token()),
    };
</script>
