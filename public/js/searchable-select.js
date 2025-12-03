/**
 * Inicializador de Searchable Selects (Select2)
 * Este archivo maneja la inicialización automática de todos los selects con clase 'searchable-select'
 * Incluye soporte para modo oscuro automático según el tema del sistema
 */

// Función para detectar si el sistema está en modo oscuro
function isDarkMode() {
    // SIEMPRE aplicar modo oscuro por defecto
    // TODO: Implementar toggle manual de tema claro/oscuro en el futuro
    return true;

    /* Lógica original comentada para referencia futura:
    // Primero verificar si hay una clase dark en el html o body
    if (document.documentElement.classList.contains('dark') || document.body.classList.contains('dark')) {
        return true;
    }
    // Si no, usar la preferencia del sistema
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    */
}

// Función para aplicar estilos de modo oscuro directamente
function applyDarkModeStyles($container) {
    // Estilos para el campo de selección
    $container.find('.select2-selection').css({
        'background-color': '#2d3748',
        'border-color': '#4a5568',
        'color': '#e2e8f0'
    });

    $container.find('.select2-selection__rendered').css({
        'color': '#e2e8f0'
    });

    $container.find('.select2-selection__placeholder').css({
        'color': '#a0aec0'
    });
}

// Función para aplicar estilos de modo oscuro al dropdown
function applyDarkModeToDropdown() {
    $('.select2-dropdown').css({
        'background-color': '#2d3748',
        'border-color': '#4a5568'
    });

    $('.select2-results__option').css({
        'background-color': '#2d3748',
        'color': '#e2e8f0'
    });

    $('.select2-search__field').css({
        'background-color': '#1a202c',
        'border-color': '#4a5568',
        'color': '#e2e8f0'
    });

    // Estilos para hover (delegado a CSS pero reforzado aquí)
    $('.select2-results__option').on('mouseenter', function() {
        if (isDarkMode()) {
            $(this).css({
                'background-color': '#4a5568',
                'color': '#ffffff'
            });
        }
    }).on('mouseleave', function() {
        if (isDarkMode() && !$(this).hasClass('select2-results__option--selected')) {
            $(this).css({
                'background-color': '#2d3748',
                'color': '#e2e8f0'
            });
        }
    });
}

// Función global para inicializar selects
function initializeSearchableSelects(container = document) {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
        console.error('⚠️ jQuery o Select2 no están cargados. Asegúrate de incluir las librerías antes de este script.');
        return;
    }

    const darkMode = isDarkMode();
    console.log('🌙 Modo oscuro detectado:', darkMode);

    $(container).find('.searchable-select').each(function() {
        const $select = $(this);

        // Si ya está inicializado, destruir primero
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        const config = {
            theme: 'bootstrap-5',
            placeholder: $select.data('placeholder') || 'Seleccione una opción',
            allowClear: $select.data('allow-clear') !== false,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                },
                inputTooShort: function() {
                    return "Escribe para buscar...";
                },
                loadingMore: function() {
                    return "Cargando más resultados...";
                }
            }
        };

        // Agregar dropdownParent si está en un modal
        const dropdownParent = $select.data('dropdown-parent');
        if (dropdownParent) {
            config.dropdownParent = $(dropdownParent);
        } else {
            // Buscar el contenedor del modal (modal-content es donde va el contenido)
            const $modalContent = $select.closest('.modal-content');
            if ($modalContent.length) {
                config.dropdownParent = $modalContent;
            }
        }

        $select.select2(config);

        // Agregar clase de modo oscuro al contenedor Select2 Y aplicar estilos directamente
        const $select2Container = $select.next('.select2-container');
        if (darkMode) {
            $select2Container.addClass('select2-dark-mode');
            console.log('🎨 Aplicando modo oscuro a:', $select.attr('id') || $select.attr('name'));
            applyDarkModeStyles($select2Container);
        } else {
            $select2Container.removeClass('select2-dark-mode');
        }

        // Aplicar estilos al abrir el dropdown
        $select.on('select2:open', function() {
            if (isDarkMode()) {
                setTimeout(function() {
                    applyDarkModeToDropdown();
                }, 50);
            }
        });
    });
}

// Inicializar cuando el DOM esté listo
$(document).ready(function() {
    initializeSearchableSelects();
});

// Reinicializar cuando se abre un modal Alpine.js
window.addEventListener('open-modal', function(event) {
    // Esperar a que Alpine.js muestre el modal completamente
    setTimeout(function() {
        const modalName = event.detail;
        console.log('📢 Evento open-modal detectado:', modalName);

        // Buscar todos los modales visibles
        $('.modal-overlay').each(function() {
            const $modalOverlay = $(this);

            // Verificar si el modal está visible (Alpine.js lo muestra con x-show)
            if ($modalOverlay.is(':visible')) {
                console.log('✅ Modal visible encontrado, inicializando Select2...');

                // Buscar selects dentro del modal
                const $selects = $modalOverlay.find('.searchable-select');
                console.log('🔍 Selects encontrados:', $selects.length);

                if ($selects.length > 0) {
                    initializeSearchableSelects($modalOverlay[0]);
                }
            }
        });
    }, 300);
});

// Evento personalizado para reinicializar manualmente
window.addEventListener('reinitialize-selects', function() {
    initializeSearchableSelects();
});

// Observar cambios en el tema del sistema
if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        console.log('🌓 Tema del sistema cambió a:', e.matches ? 'oscuro' : 'claro');
        // Reinicializar todos los selects para aplicar el nuevo tema
        initializeSearchableSelects();
    });
}

// Observar cambios en la clase 'dark' del documento (para temas manuales)
if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                const darkMode = isDarkMode();
                console.log('🎨 Clase del documento cambió, aplicando tema:', darkMode ? 'oscuro' : 'claro');

                // Actualizar todas las instancias de Select2
                $('.select2-container').each(function() {
                    if (darkMode) {
                        $(this).addClass('select2-dark-mode');
                    } else {
                        $(this).removeClass('select2-dark-mode');
                    }
                });
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });

    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });
}
