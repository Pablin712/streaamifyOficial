{{--
    Resuelve el modo claro/oscuro ANTES del primer pintado.

    Debe ir en el <head> por encima de cualquier <link rel="stylesheet">: al
    ser un script en línea y síncrono, se ejecuta antes de que el navegador
    pinte nada, así que no hay parpadeo blanco.

    Reparto de responsabilidades:
      - data-color-scheme  → preferencia PERSONAL ('system' | 'light' | 'dark'),
                             la pinta el servidor desde la fila del empleado.
      - data-dark-mode     → resultado ya resuelto, que es lo que consume el CSS.

    'system' solo lo puede resolver el navegador, porque únicamente él sabe
    cómo tiene configurado el sistema operativo quien está mirando. Por eso
    esta parte no se puede hacer en PHP.
--}}
<meta name="color-scheme" content="light dark">
<script>
    (function () {
        var raiz = document.documentElement;
        var ESQUEMAS = ['system', 'light', 'dark'];
        var autenticado = {{ auth()->check() ? 'true' : 'false' }};

        // Un visitante anónimo no tiene fila en la base de datos: su navegador
        // recuerda la preferencia. Un empleado autenticado manda desde el
        // servidor, para que le siga entre dispositivos.
        if (!autenticado) {
            try {
                var local = localStorage.getItem('streamify_esquema');
                if (local && ESQUEMAS.indexOf(local) !== -1) {
                    raiz.setAttribute('data-color-scheme', local);
                }
            } catch (e) {}
        }

        function prefiereOscuroElSistema() {
            return !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
        }

        function aplicar() {
            var pref = raiz.getAttribute('data-color-scheme') || 'system';
            var oscuro = pref === 'dark' || (pref === 'system' && prefiereOscuroElSistema());

            if (oscuro) {
                raiz.setAttribute('data-dark-mode', 'true');
                raiz.setAttribute('data-bs-theme', 'dark');
            } else {
                raiz.removeAttribute('data-dark-mode');
                raiz.setAttribute('data-bs-theme', 'light');
            }
            raiz.style.colorScheme = oscuro ? 'dark' : 'light';
            return oscuro;
        }

        aplicar();

        // Si la persona sigue al sistema y cambia el modo del sistema operativo
        // con la pestaña abierta, se refleja al momento.
        if (window.matchMedia) {
            var consulta = window.matchMedia('(prefers-color-scheme: dark)');
            var alCambiar = function () {
                if ((raiz.getAttribute('data-color-scheme') || 'system') === 'system') {
                    aplicar();
                    window.dispatchEvent(new CustomEvent('esquemaCambiado', {
                        detail: { esquema: 'system', oscuro: raiz.hasAttribute('data-dark-mode') }
                    }));
                }
            };
            if (consulta.addEventListener) consulta.addEventListener('change', alCambiar);
            else if (consulta.addListener) consulta.addListener(alCambiar);
        }

        // theme-manager.js y navbar.js reutilizan esto tras cambiar la preferencia.
        window.__sfAplicarEsquema = aplicar;
        window.__sfAutenticado = autenticado;
    })();
</script>
