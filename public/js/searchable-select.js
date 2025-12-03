/**
 * Inicializador de Searchable Selects (Select2)
 * Este archivo maneja la inicialización automática de todos los selects con clase 'searchable-select'
 * Incluye soporte para modo oscuro dinámico según el tema activo del sistema
 */

// Función para detectar si el sistema está en modo oscuro
function isDarkMode() {
    // Verificar si existe ThemeManager y tiene dark mode activo
    if (typeof ThemeManager !== 'undefined' && ThemeManager.isDarkMode()) {
        return true;
    }

    // Verificar atributo data-dark-mode en el documento
    if (document.documentElement.hasAttribute('data-dark-mode')) {
        return true;
    }

    // Verificar clases dark en html o body
    if (document.documentElement.classList.contains('dark') || document.body.classList.contains('dark')) {
        return true;
    }

    // Por defecto, usar preferencia del sistema
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
}

// Función para aplicar estilos de modo oscuro O claro
function applyThemeStyles($container) {
    const darkMode = isDarkMode();

    if (darkMode) {
        // Estilos para modo oscuro
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
    } else {
        // Estilos para modo claro
        $container.find('.select2-selection').css({
            'background-color': '#ffffff',
            'border-color': '#ced4da',
            'color': '#212529'
        });

        $container.find('.select2-selection__rendered').css({
            'color': '#212529'
        });

        $container.find('.select2-selection__placeholder').css({
            'color': '#6c757d'
        });
    }
}

// Función para aplicar estilos al dropdown (modo oscuro O claro)
function applyThemeToDropdown() {
    const darkMode = isDarkMode();

    if (darkMode) {
        // Estilos para modo oscuro
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

        // Estilos para hover
        $('.select2-results__option').off('mouseenter mouseleave').on('mouseenter', function() {
            $(this).css({
                'background-color': '#4a5568',
                'color': '#ffffff'
            });
        }).on('mouseleave', function() {
            if (!$(this).hasClass('select2-results__option--selected')) {
                $(this).css({
                    'background-color': '#2d3748',
                    'color': '#e2e8f0'
                });
            }
        });
    } else {
        // Estilos para modo claro
        $('.select2-dropdown').css({
            'background-color': '#ffffff',
            'border-color': '#ced4da'
        });

        $('.select2-results__option').css({
            'background-color': '#ffffff',
            'color': '#212529'
        });

        $('.select2-search__field').css({
            'background-color': '#ffffff',
            'border-color': '#ced4da',
            'color': '#212529'
        });

        // Estilos para hover en modo claro
        $('.select2-results__option').off('mouseenter mouseleave').on('mouseenter', function() {
            $(this).css({
                'background-color': '#e9ecef',
                'color': '#000000'
            });
        }).on('mouseleave', function() {
            if (!$(this).hasClass('select2-results__option--selected')) {
                $(this).css({
                    'background-color': '#ffffff',
                    'color': '#212529'
                });
            }
        });
    }
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
            applyThemeStyles($select2Container);
        } else {
            $select2Container.removeClass('select2-dark-mode');
            console.log('🎨 Aplicando modo claro a:', $select.attr('id') || $select.attr('name'));
            applyThemeStyles($select2Container);
        }

        // Aplicar estilos al abrir el dropdown
        $select.on('select2:open', function() {
            setTimeout(function() {
                applyThemeToDropdown();
            }, 50);
        });
    });
}

// Inicializar cuando el DOM esté listo
$(document).ready(function() {
    initializeSearchableSelects();
});

// Reinicializar cuando se abre un modal Alpine.js
window.addEventListener('open-modal', function(event) {
    const modalName = event.detail;
    console.log('📢 Evento open-modal detectado:', modalName);

    // Esperar a que Alpine.js complete la transición del modal (200ms + margen)
    setTimeout(function() {
        console.log('🔍 Buscando selects en modales visibles...');

        // Buscar todos los modal-overlay que estén visibles
        let initialized = false;
        $('.modal-overlay').each(function() {
            const $modalOverlay = $(this);

            // Verificar si el modal está visible
            if ($modalOverlay.is(':visible') && $modalOverlay.css('display') !== 'none') {
                const $selects = $modalOverlay.find('.searchable-select');
                console.log('🔍 Modal visible encontrado con', $selects.length, 'selects');

                if ($selects.length > 0) {
                    console.log('✅ Inicializando Select2 en modal...');
                    initializeSearchableSelects($modalOverlay[0]);
                    initialized = true;
                }
            }
        });

        if (!initialized) {
            console.log('⚠️ No se encontraron modales visibles con selects');
        }
    }, 350); // 200ms de transición + 150ms de margen
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

// Escuchar cambios de tema desde ThemeManager
window.addEventListener('darkModeChanged', function(event) {
    const darkMode = event.detail.darkMode;
    console.log('🌓 Dark mode cambiado desde ThemeManager:', darkMode ? 'oscuro' : 'claro');

    // Reinicializar todos los selects para aplicar el nuevo tema
    initializeSearchableSelects();
});

// Observar cambios en el atributo data-dark-mode del documento
if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'data-dark-mode' || mutation.attributeName === 'class') {
                const darkMode = isDarkMode();
                console.log('🎨 Atributos del documento cambiaron, aplicando tema:', darkMode ? 'oscuro' : 'claro');

                // Actualizar todas las instancias de Select2
                $('.select2-container').each(function() {
                    const $container = $(this);
                    if (darkMode) {
                        $container.addClass('select2-dark-mode');
                    } else {
                        $container.removeClass('select2-dark-mode');
                    }
                    applyThemeStyles($container);
                });
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class', 'data-dark-mode', 'data-theme']
    });

    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });
}
