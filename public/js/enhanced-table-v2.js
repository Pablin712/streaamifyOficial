/**
 * Enhanced Table Component v2.0 - Sistema de Tablas Mejorado
 * ============================================================
 *
 * Mejoras principales:
 * - ✅ Búsqueda inteligente con normalización de texto
 * - ✅ Soporte para múltiples términos separados por espacios
 * - ✅ Insensible a acentos, mayúsculas y caracteres especiales
 * - ✅ Búsqueda parcial mejorada (fuzzy search)
 * - ✅ Highlighting de resultados (opcional)
 * - ✅ Performance optimizado para grandes datasets
 *
 * @author Streamify Team
 * @version 2.0.0
 * @date 2025-11-30
 */

// ============================================================================
// UTILIDADES DE NORMALIZACIÓN Y TEMAS
// ============================================================================

/**
 * Obtiene el color primario del tema actual en formato RGB para jsPDF
 * @returns {Array} Array RGB [r, g, b]
 */
function getPrimaryColorRGB() {
    const primaryColor = getComputedStyle(document.documentElement)
        .getPropertyValue('--primary-color')
        .trim();

    // Convertir hex a RGB
    if (primaryColor.startsWith('#')) {
        const hex = primaryColor.replace('#', '');
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);
        return [r, g, b];
    }

    // Si es rgb() o rgba(), extraer valores
    const rgbMatch = primaryColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (rgbMatch) {
        return [parseInt(rgbMatch[1]), parseInt(rgbMatch[2]), parseInt(rgbMatch[3])];
    }

    // Fallback al color amarillo del usuario por defecto
    return [255, 226, 38]; // #ffe226
}

/**
 * Normaliza texto para búsqueda: elimina acentos, convierte a minúsculas,
 * elimina caracteres especiales y normaliza espacios
 */
function normalizeText(text) {
    if (!text) return '';

    return text
        .toString()
        .toLowerCase()
        // Eliminar acentos y diacríticos
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        // Eliminar caracteres especiales excepto espacios y números
        .replace(/[^\w\s\d]/gi, ' ')
        // Normalizar espacios múltiples a uno solo
        .replace(/\s+/g, ' ')
        .trim();
}

/**
 * Tokeniza un término de búsqueda en palabras individuales
 * Ignora palabras muy cortas (< 2 caracteres)
 */
function tokenize(searchTerm) {
    return normalizeText(searchTerm)
        .split(' ')
        .filter(token => token.length >= 2);
}

/**
 * Limpia texto para exportación (elimina emojis, caracteres especiales)
 */
function cleanTextForExport(text) {
    return text
        .replace(/[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/gu, '')
        .replace(/[\u{FE00}-\u{FE0F}]|[\u{200D}]|[\u{20E3}]/gu, '')
        .replace(/\s+/g, ' ')
        .trim();
}

/**
 * Calcula score de similitud entre dos textos (Levenshtein simplificado)
 * Retorna un valor entre 0 y 1 (1 = coincidencia exacta)
 */
function calculateSimilarity(text, query) {
    const normalizedText = normalizeText(text);
    const normalizedQuery = normalizeText(query);

    // Coincidencia exacta
    if (normalizedText === normalizedQuery) return 1.0;

    // Contiene el término completo
    if (normalizedText.includes(normalizedQuery)) return 0.9;

    // Buscar tokens individuales
    const tokens = tokenize(query);
    const matchedTokens = tokens.filter(token => normalizedText.includes(token));

    if (matchedTokens.length === 0) return 0;

    // Score basado en % de tokens encontrados
    return (matchedTokens.length / tokens.length) * 0.8;
}

// ============================================================================
// INICIALIZACIÓN
// ============================================================================

document.addEventListener("DOMContentLoaded", () => {
    // Agregar estilos CSS
    addTableStyles();

    // Inicializar todas las tablas con data-table attribute
    document.querySelectorAll("table[data-table]").forEach(initEnhancedTable);

    console.log('[Enhanced Table v2.0] Sistema inicializado');
});

// ============================================================================
// ESTILOS CSS DINÁMICOS
// ============================================================================

function addTableStyles() {
    if (document.getElementById('table-responsive-styles')) return;

    const style = document.createElement('style');
    style.id = 'table-responsive-styles';
    style.textContent = `
        /* Estilos mejorados para tabla responsive */
        .overflow-x-auto {
            scrollbar-width: thin;
            scrollbar-color: var(--border-color) var(--bg-light);
            scroll-behavior: smooth;
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: var(--bg-light);
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Highlighting de búsqueda */
        .search-highlight {
            background-color: var(--warning-color, #fef08a);
            font-weight: 500;
            padding: 1px 2px;
            border-radius: 2px;
        }

        /* Responsividad para móvil */
        @media (max-width: 767px) {
            .overflow-x-auto {
                border-left: 3px solid var(--primary-color);
                border-right: 3px solid var(--primary-color);
            }

            .overflow-x-auto::-webkit-scrollbar-thumb {
                background: var(--primary-color);
            }

            .overflow-x-auto::-webkit-scrollbar-track {
                background: var(--bg-hover);
            }

            table th, table td {
                padding: 0.75rem 1rem !important;
            }
        }

        /* Animaciones */
        .sort-arrow {
            transition: all 0.3s ease;
            display: inline-block;
            margin-left: 4px;
        }

        .sortable:hover .sort-arrow {
            opacity: 1 !important;
            transform: scale(1.15);
        }

        .sortable {
            cursor: pointer;
            user-select: none;
        }

        .sortable:active {
            transform: scale(0.98);
        }

        /* Toggle de columnas */
        [id$="-toggle-columns"] svg {
            transition: transform 0.2s ease;
        }

        [id$="-toggle-columns"][aria-expanded="true"] svg {
            transform: rotate(180deg);
        }

        /* Loading indicator */
        .table-loading {
            position: relative;
        }

        .table-loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--bg-overlay, rgba(255, 255, 255, 0.8));
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .spinner {
            border: 3px solid var(--border-color);
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Fila seleccionada */
        tbody tr.selected {
            background-color: var(--bg-hover) !important;
            border-left: 3px solid var(--primary-color);
        }
    `;
    document.head.appendChild(style);
}

// ============================================================================
// INICIALIZACIÓN DE TABLA
// ============================================================================

function initEnhancedTable(table) {
    const tbody = table.tBodies[0];
    if (!tbody) {
        console.warn('[Enhanced Table] No se encontró tbody', table);
        return;
    }

    const allRows = Array.from(tbody.querySelectorAll("tr"));

    // Detectar modo server-side
    const explicitServerSide = table.dataset.serverSide;
    const isServerSide = explicitServerSide === 'true' ||
                        (explicitServerSide !== 'false' && allRows.length >= 500);

    // Configuración de la tabla
    const config = {
        table: table,
        tableId: table.dataset.table,
        isServerSide: isServerSide,
        searchUrl: table.dataset.searchUrl || window.location.href,
        currentPage: 1,
        rowsPerPage: 5,
        sortBy: null,
        sortOrder: 'asc',
        searchTerm: '',
        totalRecords: parseInt(table.dataset.totalRecords) || allRows.length,

        // Client-side data
        allRows: allRows,
        filteredRows: allRows.slice(),

        // Cache de textos normalizados para mejor performance
        normalizedCache: new Map(),

        // Estado UI
        loading: false,
        sortOrderMap: {},

        // Elementos DOM
        searchInput: document.querySelector(`#${table.dataset.table}-search`),
        paginationSelect: document.querySelector(`#${table.dataset.table}-rows-per-page`),
        exportCsvBtn: document.querySelector(`#${table.dataset.table}-export-csv`),
        exportExcelBtn: document.querySelector(`#${table.dataset.table}-export-excel`),
        exportJsonBtn: document.querySelector(`#${table.dataset.table}-export-json`),
        exportPdfBtn: document.querySelector(`#${table.dataset.table}-export-pdf`),
        printBtn: document.querySelector(`#${table.dataset.table}-print`),
        columnToggles: document.querySelectorAll(`[data-toggle-col-${table.dataset.table}]`),
        paginationContainer: document.querySelector(`#${table.dataset.table}-pagination`),
        rowInfoContainer: document.querySelector(`#${table.dataset.table}-row-info`)
    };

    // Precachear textos normalizados para búsqueda rápida
    if (!isServerSide) {
        allRows.forEach((row, idx) => {
            const text = row.innerText;
            config.normalizedCache.set(row, normalizeText(text));
        });
    }

    // Guardar configuración
    table._config = config;

    // Inicializar eventos y features
    initTableEvents(config);
    initResponsiveFeatures(config);

    // Renderizar inicial
    if (config.isServerSide) {
        loadServerData(config);
    } else {
        renderClientPage(config);
    }

    console.log(`[Enhanced Table] ${config.tableId} inicializada`, {
        modo: config.isServerSide ? 'Server-side' : 'Client-side',
        registros: config.totalRecords,
        filasCacheadas: config.normalizedCache.size
    });
}

// ============================================================================
// EVENTOS DE TABLA
// ============================================================================

function initTableEvents(config) {
    const { table, tableId } = config;

    // Ordenación por columnas
    const headers = table.querySelectorAll("th.sortable");
    headers.forEach((th, index) => {
        th.addEventListener("click", () => {
            const type = th.dataset.type || "string";
            config.sortOrderMap[index] = config.sortOrderMap[index] === "asc" ? "desc" : "asc";

            if (config.isServerSide) {
                config.sortBy = index;
                config.sortOrder = config.sortOrderMap[index];
                loadServerData(config);
            } else {
                sortTableByColumn(config, index, type, config.sortOrderMap[index]);
                renderClientPage(config);
            }

            updateSortArrows(config, index, config.sortOrderMap[index]);
        });
    });

    // Búsqueda mejorada con debounce
    if (config.searchInput) {
        let searchTimeout;
        config.searchInput.addEventListener("input", (e) => {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {
                config.searchTerm = e.target.value;
                config.currentPage = 1;

                if (config.isServerSide) {
                    loadServerData(config);
                } else {
                    filterClientTableImproved(config);
                    renderClientPage(config);
                }
            }, 300);
        });
    }

    // Selector de filas por página
    if (config.paginationSelect) {
        config.paginationSelect.addEventListener("change", (e) => {
            config.rowsPerPage = parseInt(e.target.value, 10);
            config.currentPage = 1;

            if (config.isServerSide) {
                loadServerData(config);
            } else {
                renderClientPage(config);
            }
        });
    }

    // Botones de exportación
    if (config.exportCsvBtn) {
        config.exportCsvBtn.addEventListener("click", () => exportTableToCSV(config));
    }
    if (config.exportExcelBtn) {
        config.exportExcelBtn.addEventListener("click", () => exportTableToExcel(config));
    }
    if (config.exportJsonBtn) {
        config.exportJsonBtn.addEventListener("click", () => exportTableToJSON(config));
    }
    if (config.exportPdfBtn) {
        config.exportPdfBtn.addEventListener("click", () => exportTableToPDF(config));
    }
    if (config.printBtn) {
        config.printBtn.addEventListener("click", () => printTable(config));
    }

    // Control de visibilidad de columnas
    config.columnToggles.forEach((toggle, idx) => {
        toggle.addEventListener("change", () => {
            toggleColumn(config, idx, toggle.checked);
        });
    });

    // Selección de filas
    config.allRows.forEach((row) => {
        row.addEventListener("click", (e) => {
            // No seleccionar si se clickeó en un botón o enlace
            if (e.target.closest('button, a')) return;
            row.classList.toggle("selected");
        });
    });
}

// ============================================================================
// BÚSQUEDA MEJORADA (CLIENT-SIDE)
// ============================================================================

/**
 * Filtrado mejorado con soporte para múltiples términos y normalización
 */
function filterClientTableImproved(config) {
    const searchTerm = config.searchTerm.trim();

    // Si no hay término de búsqueda, mostrar todo
    if (!searchTerm) {
        config.filteredRows = config.allRows.slice();
        removeHighlights(config);
        return;
    }

    // Tokenizar términos de búsqueda
    const tokens = tokenize(searchTerm);

    if (tokens.length === 0) {
        config.filteredRows = config.allRows.slice();
        removeHighlights(config);
        return;
    }

    // Filtrar filas que coincidan con TODOS los tokens (AND lógico)
    config.filteredRows = config.allRows.filter((row) => {
        const normalizedText = config.normalizedCache.get(row);

        // Verificar que todos los tokens estén presentes
        return tokens.every(token => normalizedText.includes(token));
    });

    // Opcional: Aplicar highlighting
    // highlightSearchTerms(config, tokens);

    console.log(`[Enhanced Table] Búsqueda: "${searchTerm}" → ${config.filteredRows.length} resultados`);
}

/**
 * Resalta los términos de búsqueda en las celdas visibles
 */
function highlightSearchTerms(config, tokens) {
    removeHighlights(config);

    if (tokens.length === 0) return;

    config.filteredRows.forEach(row => {
        Array.from(row.cells).forEach(cell => {
            const originalHTML = cell.innerHTML;
            let highlightedHTML = originalHTML;

            tokens.forEach(token => {
                const regex = new RegExp(`(${escapeRegex(token)})`, 'gi');
                highlightedHTML = highlightedHTML.replace(regex, '<span class="search-highlight">$1</span>');
            });

            if (highlightedHTML !== originalHTML) {
                cell.innerHTML = highlightedHTML;
            }
        });
    });
}

/**
 * Elimina highlighting de búsqueda
 */
function removeHighlights(config) {
    config.table.querySelectorAll('.search-highlight').forEach(span => {
        const parent = span.parentNode;
        parent.replaceChild(document.createTextNode(span.textContent), span);
        parent.normalize();
    });
}

/**
 * Escapa caracteres especiales para regex
 */
function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// ============================================================================
// ORDENACIÓN
// ============================================================================

function sortTableByColumn(config, columnIndex, type = "string", order = "asc") {
    const rows = config.filteredRows;

    rows.sort((rowA, rowB) => {
        let cellA = rowA.cells[columnIndex]?.innerText.trim() || '';
        let cellB = rowB.cells[columnIndex]?.innerText.trim() || '';

        if (type === "number") {
            cellA = parseFloat(cellA.replace(/[^0-9.-]/g, '')) || 0;
            cellB = parseFloat(cellB.replace(/[^0-9.-]/g, '')) || 0;
            return order === "asc" ? cellA - cellB : cellB - cellA;
        }

        // Ordenación alfabética con normalización
        cellA = normalizeText(cellA);
        cellB = normalizeText(cellB);

        return order === "asc"
            ? cellA.localeCompare(cellB)
            : cellB.localeCompare(cellA);
    });

    rows.forEach((row) => config.table.tBodies[0].appendChild(row));
}

// ============================================================================
// RENDERIZADO CLIENT-SIDE
// ============================================================================

function renderClientPage(config) {
    const rows = config.filteredRows;
    const start = (config.currentPage - 1) * config.rowsPerPage;
    const end = config.currentPage * config.rowsPerPage;

    // Mostrar solo las filas de la página actual
    rows.forEach((row, idx) => {
        row.style.display = idx >= start && idx < end ? "" : "none";
    });

    // Ocultar filas que no están en filteredRows
    config.allRows.forEach((row) => {
        if (!rows.includes(row)) row.style.display = "none";
    });

    renderClientPagination(config, rows.length);
    updateRowInfo(config);
}

function renderClientPagination(config, totalRows) {
    if (!config.paginationContainer) return;

    const totalPages = Math.ceil(totalRows / config.rowsPerPage) || 1;
    config.paginationContainer.innerHTML = "";

    function createBtn(label, page, disabled = false, active = false) {
        const btn = document.createElement("button");
        btn.className =
            "px-3 py-2 mx-1 rounded transition-colors duration-200 text-sm font-medium " +
            (active
                ? "bg-blue-600 text-white shadow-md"
                : "bg-gray-200 text-gray-700 hover:bg-blue-100") +
            (disabled ? " opacity-50 cursor-not-allowed" : "");
        btn.textContent = label;
        btn.disabled = disabled;

        if (!disabled && !active) {
            btn.addEventListener("click", () => {
                config.currentPage = page;
                renderClientPage(config);
            });
        }
        return btn;
    }

    // Primera página
    config.paginationContainer.appendChild(createBtn("<<", 1, config.currentPage === 1));

    // Página anterior
    config.paginationContainer.appendChild(
        createBtn("<", Math.max(1, config.currentPage - 1), config.currentPage === 1)
    );

    // Calcular rango de páginas a mostrar
    let startPage = Math.max(1, config.currentPage - 5);
    let endPage = Math.min(totalPages, config.currentPage + 4);

    // Ajustar si estamos cerca del inicio o final
    if (config.currentPage <= 6) {
        endPage = Math.min(10, totalPages);
    } else if (config.currentPage + 4 >= totalPages) {
        startPage = Math.max(1, totalPages - 9);
    }

    // Ellipsis inicial
    if (startPage > 1) {
        const dots = document.createElement("span");
        dots.textContent = "...";
        dots.className = "mx-2 text-gray-500";
        config.paginationContainer.appendChild(dots);
    }

    // Botones de páginas
    for (let i = startPage; i <= endPage; i++) {
        config.paginationContainer.appendChild(
            createBtn(i.toString(), i, false, i === config.currentPage)
        );
    }

    // Ellipsis final
    if (endPage < totalPages) {
        const dots = document.createElement("span");
        dots.textContent = "...";
        dots.className = "mx-2 text-gray-500";
        config.paginationContainer.appendChild(dots);
    }

    // Página siguiente
    config.paginationContainer.appendChild(
        createBtn(">", Math.min(totalPages, config.currentPage + 1), config.currentPage === totalPages)
    );

    // Última página
    config.paginationContainer.appendChild(
        createBtn(">>", totalPages, config.currentPage === totalPages)
    );
}

// ============================================================================
// SERVER-SIDE (sin cambios significativos del original)
// ============================================================================

async function loadServerData(config) {
    try {
        setLoading(config, true);

        const params = new URLSearchParams({
            page: config.currentPage,
            per_page: config.rowsPerPage,
            search: config.searchTerm,
            sort_by: config.sortBy || '',
            sort_order: config.sortOrder || 'asc',
            ajax: 'true'
        });

        const response = await fetch(`${config.searchUrl}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('El servidor no devolvió JSON');
        }

        const data = await response.json();

        updateTableBody(config, data.html);
        config.totalRecords = data.total_records || data.total || 0;

        renderServerPagination(config, data);
        updateRowInfo(config);

    } catch (error) {
        console.error('Error cargando datos:', error);
        showError(config, 'Error al cargar los datos. Recarga la página.');
    } finally {
        setLoading(config, false);
    }
}

function updateTableBody(config, html) {
    const tbody = config.table.tBodies[0];
    tbody.innerHTML = html;

    const newRows = Array.from(tbody.querySelectorAll("tr"));
    newRows.forEach((row) => {
        row.addEventListener("click", (e) => {
            if (e.target.closest('button, a')) return;
            row.classList.toggle("selected");
        });
    });
}

function renderServerPagination(config, data) {
    if (!config.paginationContainer) return;

    const totalRecords = data.total_records || data.total || 0;
    const totalPages = Math.ceil(totalRecords / config.rowsPerPage);
    config.paginationContainer.innerHTML = "";

    function createBtn(label, page, disabled = false, active = false) {
        const btn = document.createElement("button");
        btn.className =
            "px-3 py-2 mx-1 rounded transition-colors duration-200 text-sm font-medium " +
            (active
                ? "bg-blue-600 text-white shadow-md"
                : "bg-gray-200 text-gray-700 hover:bg-blue-100") +
            (disabled ? " opacity-50 cursor-not-allowed" : "");
        btn.textContent = label;
        btn.disabled = disabled;

        if (!disabled && !active) {
            btn.addEventListener("click", () => {
                config.currentPage = page;
                loadServerData(config);
            });
        }
        return btn;
    }

    config.paginationContainer.appendChild(createBtn("<<", 1, config.currentPage === 1));
    config.paginationContainer.appendChild(createBtn("<", Math.max(1, config.currentPage - 1), config.currentPage === 1));

    let startPage = Math.max(1, config.currentPage - 5);
    let endPage = Math.min(totalPages, config.currentPage + 4);

    if (config.currentPage <= 6) {
        endPage = Math.min(10, totalPages);
    } else if (config.currentPage + 4 >= totalPages) {
        startPage = Math.max(1, totalPages - 9);
    }

    if (startPage > 1) {
        const dots = document.createElement("span");
        dots.textContent = "...";
        dots.className = "mx-2 text-gray-500";
        config.paginationContainer.appendChild(dots);
    }

    for (let i = startPage; i <= endPage; i++) {
        config.paginationContainer.appendChild(createBtn(i.toString(), i, false, i === config.currentPage));
    }

    if (endPage < totalPages) {
        const dots = document.createElement("span");
        dots.textContent = "...";
        dots.className = "mx-2 text-gray-500";
        config.paginationContainer.appendChild(dots);
    }

    config.paginationContainer.appendChild(createBtn(">", Math.min(totalPages, config.currentPage + 1), config.currentPage === totalPages));
    config.paginationContainer.appendChild(createBtn(">>", totalPages, config.currentPage === totalPages));
}

// ============================================================================
// UI UTILITIES
// ============================================================================

function setLoading(config, loading) {
    config.loading = loading;
    const tableContainer = config.table.closest('.overflow-x-auto');

    if (loading) {
        tableContainer.classList.add('table-loading');
        const spinner = document.createElement('div');
        spinner.className = 'spinner';
        spinner.style.cssText = 'position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:20;';
        tableContainer.appendChild(spinner);
    } else {
        tableContainer.classList.remove('table-loading');
        const spinner = tableContainer.querySelector('.spinner');
        if (spinner) spinner.remove();
    }
}

function showError(config, message) {
    const tbody = config.table.tBodies[0];
    const colCount = config.table.querySelectorAll('th').length;
    tbody.innerHTML = `
        <tr>
            <td colspan="${colCount}" class="text-center p-8 text-red-500">
                <div class="flex flex-col items-center gap-3">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-lg font-medium">${message}</p>
                </div>
            </td>
        </tr>
    `;
}

function updateSortArrows(config, columnIndex, order) {
    const headers = config.table.querySelectorAll("th.sortable");
    headers.forEach((th, idx) => {
        const arrow = th.querySelector(".sort-arrow");
        if (!arrow) return;

        if (idx === columnIndex) {
            arrow.innerHTML = order === "asc"
                ? '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 15l7-7 7 7"/></svg>'
                : '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>';
            arrow.style.opacity = "1";
        } else {
            arrow.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg>';
            arrow.style.opacity = "0.3";
        }
    });
}

function updateRowInfo(config) {
    if (!config.rowInfoContainer) return;

    let totalRows, start, end;

    if (config.isServerSide) {
        totalRows = config.totalRecords;
        start = totalRows === 0 ? 0 : (config.currentPage - 1) * config.rowsPerPage + 1;
        end = Math.min(config.currentPage * config.rowsPerPage, totalRows);
    } else {
        totalRows = config.filteredRows.length;
        start = totalRows === 0 ? 0 : (config.currentPage - 1) * config.rowsPerPage + 1;
        end = Math.min(config.currentPage * config.rowsPerPage, totalRows);
    }

    config.rowInfoContainer.textContent = `Mostrando ${start} a ${end} de ${totalRows} registros`;
}

function toggleColumn(config, colIndex, show) {
    config.table.querySelectorAll("tr").forEach((row) => {
        if (row.cells[colIndex]) {
            row.cells[colIndex].style.display = show ? "" : "none";
        }
    });
}

function initResponsiveFeatures(config) {
    const toggleBtn = document.getElementById(`${config.tableId}-toggle-columns`);
    const columnsContainer = document.getElementById(`${config.tableId}-columns-container`);

    if (toggleBtn && columnsContainer) {
        toggleBtn.addEventListener('click', function() {
            const isVisible = !columnsContainer.classList.contains('hidden');
            columnsContainer.classList.toggle('hidden');
            this.setAttribute('aria-expanded', !isVisible);
        });
        toggleBtn.setAttribute('aria-expanded', 'false');
    }

    const tableContainer = document.getElementById(`${config.tableId}-table-container`);
    if (tableContainer) {
        addScrollIndicators(tableContainer);
    }
}

function addScrollIndicators(container) {
    let scrollTimeout;
    container.addEventListener('scroll', function() {
        this.style.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            this.style.borderColor = '';
        }, 1000);
    });
}

// ============================================================================
// EXPORTACIÓN (preservadas del original con mejoras menores)
// ============================================================================

function exportTableToCSV(config, filename = "export.csv") {
    const headers = Array.from(config.table.querySelectorAll("th"));
    const actionIndexes = headers.map((th, i) => th.dataset.type === 'actions' ? i : -1).filter(i => i >= 0);

    const headerRow = headers.filter((_, i) => !actionIndexes.includes(i)).map(th => cleanTextForExport(th.innerText));
    const dataRows = config.filteredRows.map(row => {
        return Array.from(row.cells)
            .filter((_, i) => !actionIndexes.includes(i))
            .map(cell => `"${cleanTextForExport(cell.innerText).replace(/"/g, '""')}"`);
    });

    const csv = [headerRow.join(','), ...dataRows.map(r => r.join(','))].join('\n');
    downloadBlob(csv, filename, 'text/csv;charset=utf-8;');
}

function exportTableToJSON(config, filename = "export.json") {
    const headers = Array.from(config.table.querySelectorAll("th"));
    const actionIndexes = headers.map((th, i) => th.dataset.type === 'actions' ? i : -1).filter(i => i >= 0);
    const headerLabels = headers.filter((_, i) => !actionIndexes.includes(i)).map(th => cleanTextForExport(th.innerText));

    const data = config.filteredRows.map(row => {
        const rowData = {};
        Array.from(row.cells)
            .filter((_, i) => !actionIndexes.includes(i))
            .forEach((cell, idx) => {
                rowData[headerLabels[idx]] = cleanTextForExport(cell.innerText);
            });
        return rowData;
    });

    const json = JSON.stringify({ exported_at: new Date().toISOString(), total: data.length, data }, null, 2);
    downloadBlob(json, filename, 'application/json;charset=utf-8;');
}

function exportTableToExcel(config, filename = "export.xlsx") {
    if (typeof XLSX === 'undefined') {
        alert('Librería Excel no disponible');
        return;
    }

    const headers = Array.from(config.table.querySelectorAll("th"));
    const actionIndexes = headers.map((th, i) => th.dataset.type === 'actions' ? i : -1).filter(i => i >= 0);
    const headerLabels = headers.filter((_, i) => !actionIndexes.includes(i)).map(th => cleanTextForExport(th.innerText));

    const dataRows = config.filteredRows.map(row =>
        Array.from(row.cells)
            .filter((_, i) => !actionIndexes.includes(i))
            .map(cell => cleanTextForExport(cell.innerText))
    );

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet([headerLabels, ...dataRows]);
    XLSX.utils.book_append_sheet(wb, ws, "Data");
    XLSX.writeFile(wb, filename);
}

function exportTableToPDF(config, filename = "export.pdf") {
    const { jsPDF } = window.jspdf || window;
    if (!jsPDF) {
        alert('Librería PDF no disponible');
        return;
    }

    const headers = Array.from(config.table.querySelectorAll("th"));
    const actionIndexes = headers.map((th, i) => th.dataset.type === 'actions' ? i : -1).filter(i => i >= 0);
    const headerLabels = headers.filter((_, i) => !actionIndexes.includes(i)).map(th => cleanTextForExport(th.innerText));

    const dataRows = config.filteredRows.map(row =>
        Array.from(row.cells)
            .filter((_, i) => !actionIndexes.includes(i))
            .map(cell => cleanTextForExport(cell.innerText))
    );

    const doc = new jsPDF({ orientation: headerLabels.length > 6 ? 'landscape' : 'portrait' });
    doc.setFontSize(16);
    doc.text('Reporte de Datos', 14, 15);
    doc.setFontSize(9);
    doc.text(`Generado: ${new Date().toLocaleString()}`, 14, 22);

    doc.autoTable({
        head: [headerLabels],
        body: dataRows,
        startY: 28,
        theme: 'striped',
        headStyles: {
            fillColor: getPrimaryColorRGB(),
            fontStyle: 'bold',
            textColor: [255, 255, 255]
        }
    });

    doc.save(filename);
}

function printTable(config) {
    const headers = Array.from(config.table.querySelectorAll("th"));
    const actionIndexes = headers.map((th, i) => th.dataset.type === 'actions' ? i : -1).filter(i => i >= 0);

    const tableClone = config.table.cloneNode(true);
    tableClone.querySelectorAll("tr").forEach(row => {
        actionIndexes.reverse().forEach(idx => {
            if (row.cells[idx]) row.cells[idx].remove();
        });
    });

    const printHTML = `
        <html>
        <head>
            <title>Reporte</title>
            <style>
                body { font-family: Arial; margin: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid var(--border-color); padding: 8px; text-align: left; }
                th { background: var(--primary-gradient); color: var(--text-on-primary); }
            </style>
        </head>
        <body>
            <h2>Reporte - ${new Date().toLocaleDateString()}</h2>
            ${tableClone.outerHTML}
        </body>
        </html>
    `;

    const win = window.open("", "", "width=900,height=700");
    win.document.write(printHTML);
    win.document.close();
    win.print();
}

function downloadBlob(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
