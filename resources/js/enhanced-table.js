/**
 * Enhanced Table Component - Híbrido Client/Server-Side
 * Mejora del componente table original con paginación inteligente
 *
 * Funcionalidades:
 * - Paginación híbrida (client-side para datasets pequeños, server-side para grandes)
 * - Ordenación de columnas (asc/desc)
 * - Búsqueda/filtrado inteligente
 * - Exportación (CSV, JSON, Print) excluyendo columnas de acciones
 * - Control de visibilidad de columnas
 * - Responsive design con indicadores de scroll
 * - Selección de filas
 */

document.addEventListener("DOMContentLoaded", () => {
    // Agregar estilos CSS para responsive
    addTableStyles();

    // Inicializar todas las tablas
    document.querySelectorAll("table[data-table]").forEach(initEnhancedTable);
});

// Función para agregar estilos CSS dinámicamente (sin cambios)
function addTableStyles() {
    if (document.getElementById('table-responsive-styles')) return;

    const style = document.createElement('style');
    style.id = 'table-responsive-styles';
    style.textContent = `
        /* Estilos personalizados para tabla responsive */
        .overflow-x-auto {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
            scroll-behavior: smooth;
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Responsividad para móvil */
        @media (max-width: 767px) {
            .overflow-x-auto {
                border-left: 3px solid #3b82f6;
                border-right: 3px solid #3b82f6;
            }

            .overflow-x-auto::-webkit-scrollbar-thumb {
                background: #3b82f6;
            }

            .overflow-x-auto::-webkit-scrollbar-track {
                background: #dbeafe;
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
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
}

function initEnhancedTable(table) {
    // Detectar el tipo de paginación basado en el número de filas
    const tbody = table.tBodies[0];
    if (!tbody) {
        console.warn('[Enhanced Table] No se encontró tbody en la tabla', table);
        return;
    }

    const allRows = Array.from(tbody.querySelectorAll("tr"));

    // IMPORTANTE: Respetar explícitamente el atributo data-server-side
    // Solo activar auto-detección si no está explícitamente definido
    const explicitServerSide = table.dataset.serverSide;
    const isServerSide = explicitServerSide === 'true' ||
                        (explicitServerSide !== 'false' && allRows.length >= 500);

    // Configuración inicial
    const config = {
        table: table,
        tableId: table.dataset.table,
        isServerSide: isServerSide,
        searchUrl: table.dataset.searchUrl || window.location.href,
        currentPage: 1,
        rowsPerPage: 10,
        sortBy: null,
        sortOrder: 'asc',
        searchTerm: '',
        totalRecords: parseInt(table.dataset.totalRecords) || allRows.length,

        // Para client-side
        allRows: allRows,
        filteredRows: allRows.slice(),

        // Estado UI
        loading: false,
        sortOrder: {},

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

    // Guardar configuración en la tabla
    table._config = config;

    // Inicializar funcionalidades
    initTableEvents(config);
    initResponsiveFeatures(config);

    // Cargar datos inicial
    if (config.isServerSide) {
        loadServerData(config);
    } else {
        renderClientPage(config);
    }

    console.log(`[Enhanced Table] ${config.tableId} inicializada:`, {
        modo: config.isServerSide ? 'Server-side' : 'Client-side',
        totalRegistros: config.totalRecords,
        filasPorPagina: config.rowsPerPage,
        columnas: config.table.querySelectorAll('th').length
    });
}

function initTableEvents(config) {
    const { table, tableId } = config;

    // Ordenación de headers
    const headers = table.querySelectorAll("th.sortable");
    headers.forEach((th, index) => {
        th.addEventListener("click", () => {
            const type = th.dataset.type || "string";
            config.sortOrder[index] = config.sortOrder[index] === "asc" ? "desc" : "asc";

            if (config.isServerSide) {
                config.sortBy = index;
                config.sortOrder = config.sortOrder[index];
                loadServerData(config);
            } else {
                sortTableByColumn(config, index, type, config.sortOrder[index]);
                renderClientPage(config);
            }

            updateSortArrows(config, index, config.sortOrder[index]);
        });
    });

    // Búsqueda
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
                    filterClientTable(config);
                    renderClientPage(config);
                }
            }, 300); // Debounce de 300ms
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

    // Control de columnas
    config.columnToggles.forEach((toggle, idx) => {
        toggle.addEventListener("change", () => {
            toggleColumn(config, idx, toggle.checked);
        });
    });

    // Selección de filas
    config.allRows.forEach((row) => {
        row.addEventListener("click", () => {
            row.classList.toggle("selected");
        });
    });
}

// ============================================================================
// FUNCIONES SERVER-SIDE
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

        // Verificar que la respuesta sea JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Respuesta no es JSON:', text.substring(0, 200));
            throw new Error('El servidor no devolvió JSON. Verifica la configuración de la ruta.');
        }

        const data = await response.json();

        // Actualizar tabla con nuevos datos
        updateTableBody(config, data.html);
        config.totalRecords = data.total_records || data.total || 0;

        // Actualizar paginación y info
        renderServerPagination(config, data);
        updateRowInfo(config);

    } catch (error) {
        console.error('Error cargando datos del servidor:', error);
        showError(config, 'Error al cargar los datos. Por favor, recarga la página.');
    } finally {
        setLoading(config, false);
    }
}

function updateTableBody(config, html) {
    const tbody = config.table.tBodies[0];
    tbody.innerHTML = html;

    // Reattach click events para selección de filas
    const newRows = Array.from(tbody.querySelectorAll("tr"));
    newRows.forEach((row) => {
        row.addEventListener("click", () => {
            row.classList.toggle("selected");
        });
    });
}

function renderServerPagination(config, data) {
    if (!config.paginationContainer) return;

    const totalRecords = data.total_records || data.total || 0;
    const totalPages = Math.ceil(totalRecords / config.rowsPerPage);
    config.paginationContainer.innerHTML = "";

    // Helper para crear botón
    function createBtn(label, page, disabled = false, active = false) {
        const btn = document.createElement("button");
        btn.className =
            "px-2 py-1 mx-1 rounded transition-colors duration-200 " +
            (active
                ? "bg-brand text-white font-bold shadow"
                : "bg-gray-200 text-gray-700 hover:bg-blue-100") +
            (disabled ? " opacity-50 cursor-not-allowed" : "");
        btn.textContent = label;
        if (!disabled && !active) {
            btn.addEventListener("click", () => {
                config.currentPage = page;
                loadServerData(config);
            });
        }
        return btn;
    }

    // Lógica de paginación igual que antes
    config.paginationContainer.appendChild(createBtn("<<", 1, config.currentPage === 1));
    config.paginationContainer.appendChild(createBtn("<", Math.max(1, config.currentPage - 1), config.currentPage === 1));

    let startPage = 1;
    let endPage = totalPages;

    if (totalPages > 10) {
        if (config.currentPage <= 6) {
            startPage = 1;
            endPage = 10;
        } else if (config.currentPage + 4 >= totalPages) {
            startPage = totalPages - 9;
            endPage = totalPages;
        } else {
            startPage = config.currentPage - 5;
            endPage = config.currentPage + 4;
        }
    }

    if (startPage > 1) {
        const dots = document.createElement("span");
        dots.textContent = "...";
        dots.className = "mx-1 text-gray-500";
        config.paginationContainer.appendChild(dots);
    }

    for (let i = startPage; i <= endPage; i++) {
        config.paginationContainer.appendChild(createBtn(i, i, false, i === config.currentPage));
    }

    if (endPage < totalPages) {
        const dots = document.createElement("span");
        dots.textContent = "...";
        dots.className = "mx-1 text-gray-500";
        config.paginationContainer.appendChild(dots);
    }

    config.paginationContainer.appendChild(createBtn(">", Math.min(totalPages, config.currentPage + 1), config.currentPage === totalPages));
    config.paginationContainer.appendChild(createBtn(">>", totalPages, config.currentPage === totalPages));
}

// ============================================================================
// FUNCIONES CLIENT-SIDE (sin cambios del original)
// ============================================================================

function sortTableByColumn(config, columnIndex, type = "string", order = "asc") {
    const rows = config.filteredRows;
    rows.sort((rowA, rowB) => {
        let cellA = rowA.cells[columnIndex].innerText.trim();
        let cellB = rowB.cells[columnIndex].innerText.trim();
        if (type === "number") {
            cellA = parseFloat(cellA) || 0;
            cellB = parseFloat(cellB) || 0;
        }
        if (order === "asc") {
            return type === "number"
                ? cellA - cellB
                : cellA.localeCompare(cellB);
        } else {
            return type === "number"
                ? cellB - cellA
                : cellB.localeCompare(cellA);
        }
    });
    rows.forEach((row) => config.table.tBodies[0].appendChild(row));
}

function filterClientTable(config) {
    const term = config.searchTerm.toLowerCase();
    config.filteredRows = config.allRows.filter((row) => {
        const text = row.innerText.toLowerCase();
        return text.includes(term);
    });
}

function renderClientPage(config) {
    const rows = config.filteredRows;
    const start = (config.currentPage - 1) * config.rowsPerPage;
    const end = config.currentPage * config.rowsPerPage;

    rows.forEach((row, idx) => {
        row.style.display = idx >= start && idx < end ? "" : "none";
    });

    config.allRows.forEach((row) => {
        if (!rows.includes(row)) row.style.display = "none";
    });

    renderClientPagination(config, rows.length);
    updateRowInfo(config);
}

function renderClientPagination(config, totalRows) {
    if (!config.paginationContainer) return;

    const totalPages = Math.ceil(totalRows / config.rowsPerPage);
    config.paginationContainer.innerHTML = "";

    function createBtn(label, page, disabled = false, active = false) {
        const btn = document.createElement("button");
        btn.className =
            "px-2 py-1 mx-1 rounded transition-colors duration-200 " +
            (active
                ? "bg-brand text-white font-bold shadow"
                : "bg-gray-200 text-gray-700 hover:bg-blue-100") +
            (disabled ? " opacity-50 cursor-not-allowed" : "");
        btn.textContent = label;
        if (!disabled && !active) {
            btn.addEventListener("click", () => {
                config.currentPage = page;
                renderClientPage(config);
            });
        }
        return btn;
    }

    // Misma lógica de paginación
    config.paginationContainer.appendChild(createBtn("<<", 1, config.currentPage === 1));
    config.paginationContainer.appendChild(createBtn("<", Math.max(1, config.currentPage - 1), config.currentPage === 1));

    let startPage = 1;
    let endPage = totalPages;

    if (totalPages > 10) {
        if (config.currentPage <= 6) {
            startPage = 1;
            endPage = 10;
        } else if (config.currentPage + 4 >= totalPages) {
            startPage = totalPages - 9;
            endPage = totalPages;
        } else {
            startPage = config.currentPage - 5;
            endPage = config.currentPage + 4;
        }
    }

    if (startPage > 1) {
        const dots = document.createElement("span");
        dots.textContent = "...";
        dots.className = "mx-1 text-gray-500";
        config.paginationContainer.appendChild(dots);
    }

    for (let i = startPage; i <= endPage; i++) {
        config.paginationContainer.appendChild(createBtn(i, i, false, i === config.currentPage));
    }

    if (endPage < totalPages) {
        const dots = document.createElement("span");
        dots.textContent = "...";
        dots.className = "mx-1 text-gray-500";
        config.paginationContainer.appendChild(dots);
    }

    config.paginationContainer.appendChild(createBtn(">", Math.min(totalPages, config.currentPage + 1), config.currentPage === totalPages));
    config.paginationContainer.appendChild(createBtn(">>", totalPages, config.currentPage === totalPages));
}

// ============================================================================
// FUNCIONES UTILITARIAS Y UI
// ============================================================================

function setLoading(config, loading) {
    config.loading = loading;
    const tableContainer = config.table.closest('.overflow-x-auto');

    if (loading) {
        tableContainer.classList.add('table-loading');
        const spinner = document.createElement('div');
        spinner.className = 'spinner';
        spinner.style.position = 'absolute';
        spinner.style.top = '50%';
        spinner.style.left = '50%';
        spinner.style.transform = 'translate(-50%, -50%)';
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
                    <svg class="w-12 h-12 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-lg font-medium">${message}</p>
                </div>
            </td>
        </tr>
    `;
}

// Resto de funciones sin cambios (updateSortArrows, updateRowInfo, toggleColumn, etc.)
function updateSortArrows(config, columnIndex, order) {
    const headers = config.table.querySelectorAll("th.sortable");
    headers.forEach((th, idx) => {
        const arrow = th.querySelector(".sort-arrow");
        if (!arrow) return;
        if (idx === columnIndex) {
            // Columna activa - mostrar flecha según dirección
            arrow.innerHTML =
                order === "asc"
                    ? '<svg width="14" height="14" style="display:inline-block;vertical-align:middle;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 15l7-7 7 7"/></svg>'
                    : '<svg width="14" height="14" style="display:inline-block;vertical-align:middle;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>';
            arrow.style.opacity = "1";
        } else {
            // Columnas inactivas - mostrar flechas verticales sutiles
            arrow.innerHTML = '<svg width="14" height="14" style="display:inline-block;vertical-align:middle;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg>';
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

    config.rowInfoContainer.textContent = `${start} a ${end} de ${totalRows}`;
}

function toggleColumn(config, colIndex, show) {
    config.table.querySelectorAll("tr").forEach((row) => {
        if (row.cells[colIndex]) {
            row.cells[colIndex].style.display = show ? "" : "none";
        }
    });

    const tableContainer = document.getElementById(`${config.tableId}-table-container`);
    if (tableContainer) {
        setTimeout(() => {
            const needsScroll = tableContainer.scrollWidth > tableContainer.clientWidth;
            if (needsScroll) {
                tableContainer.setAttribute('data-scrollable', 'true');
            } else {
                tableContainer.removeAttribute('data-scrollable');
            }
        }, 100);
    }
}

// Funciones de exportación adaptadas
async function exportTableToCSV(config, filename = "export.csv") {
    if (config.isServerSide) {
        // Para server-side, descargar CSV del servidor
        const params = new URLSearchParams({
            export: 'csv',
            search: config.searchTerm,
            sort_by: config.sortBy || '',
            sort_order: config.sortOrder || 'asc'
        });
        window.location.href = `${config.searchUrl}?${params.toString()}`;
    } else {
        // Para client-side, generar CSV local
        exportClientCSV(config, filename);
    }
}

function exportClientCSV(config, filename) {
    const headers = Array.from(config.table.querySelectorAll("th"));
    const actionColumnIndexes = [];
    headers.forEach((th, index) => {
        if (th.dataset.type === 'actions') {
            actionColumnIndexes.push(index);
        }
    });

    const headerRow = Array.from(config.table.querySelectorAll("tr"))[0];
    const dataRows = config.filteredRows;
    const rowsToExport = [headerRow, ...dataRows];

    const csv = rowsToExport
        .map((row) => {
            const cols = Array.from(row.querySelectorAll("th, td"));
            return cols
                .filter((col, index) => !actionColumnIndexes.includes(index))
                .map((c) => `"${cleanTextForJSON(c.innerText).replace(/"/g, '""')}"`)
                .join(",");
        })
        .join("\n");

    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

async function exportTableToJSON(config, filename = "export.json") {
    if (config.isServerSide) {
        const params = new URLSearchParams({
            export: 'json',
            search: config.searchTerm,
            sort_by: config.sortBy || '',
            sort_order: config.sortOrder || 'asc'
        });
        window.location.href = `${config.searchUrl}?${params.toString()}`;
    } else {
        exportClientJSON(config, filename);
    }
}

async function exportTableToExcel(config, filename = "export.xlsx") {
    if (config.isServerSide) {
        // Para server-side, descargar Excel del servidor
        const params = new URLSearchParams({
            export: 'excel',
            search: config.searchTerm,
            sort_by: config.sortBy || '',
            sort_order: config.sortOrder || 'asc'
        });
        window.location.href = `${config.searchUrl}?${params.toString()}`;
    } else {
        // Para client-side, generar Excel local
        exportClientExcel(config, filename);
    }
}

function exportClientExcel(config, filename) {
    // Verificar si SheetJS está disponible
    if (typeof XLSX !== 'undefined') {
        exportExcelWithSheetJS(config, filename);
    } else {
        // Fallback: crear archivo HTML que Excel puede abrir
        exportExcelAsHTML(config, filename);
    }
}

function exportExcelWithSheetJS(config, filename) {
    const headers = Array.from(config.table.querySelectorAll("th"));
    const actionColumnIndexes = [];
    const headerLabels = [];

    headers.forEach((th, index) => {
        if (th.dataset.type === 'actions') {
            actionColumnIndexes.push(index);
        } else {
            const clone = th.cloneNode(true);
            const svgElements = clone.querySelectorAll('svg, .sort-arrow');
            svgElements.forEach(svg => svg.remove());
            headerLabels.push(cleanTextForJSON(clone.innerText));
        }
    });

    const dataRows = config.filteredRows.map(row => {
        const allCells = Array.from(row.querySelectorAll("td"));
        return allCells
            .filter((cell, index) => !actionColumnIndexes.includes(index))
            .map(cell => {
                const clone = cell.cloneNode(true);
                const svgElements = clone.querySelectorAll('svg');
                svgElements.forEach(svg => svg.remove());
                return cleanTextForJSON(clone.innerText);
            });
    });

    // Crear workbook y worksheet
    const wb = XLSX.utils.book_new();
    const wsData = [headerLabels, ...dataRows];
    const ws = XLSX.utils.aoa_to_sheet(wsData);

    // Auto-width para columnas
    const colWidths = headerLabels.map((header, i) => {
        const maxLength = Math.max(
            header.length,
            ...dataRows.map(row => (row[i] || '').toString().length)
        );
        return { wch: Math.min(maxLength + 2, 50) };
    });
    ws['!cols'] = colWidths;

    // Agregar worksheet al workbook
    XLSX.utils.book_append_sheet(wb, ws, "Data");

    // Descargar archivo
    XLSX.writeFile(wb, filename);
}

function exportExcelAsHTML(config, filename) {
    const headers = Array.from(config.table.querySelectorAll("th"));
    const actionColumnIndexes = [];
    const headerLabels = [];

    headers.forEach((th, index) => {
        if (th.dataset.type === 'actions') {
            actionColumnIndexes.push(index);
        } else {
            const clone = th.cloneNode(true);
            const svgElements = clone.querySelectorAll('svg, .sort-arrow');
            svgElements.forEach(svg => svg.remove());
            headerLabels.push(cleanTextForJSON(clone.innerText));
        }
    });

    const dataRows = config.filteredRows.map(row => {
        const allCells = Array.from(row.querySelectorAll("td"));
        return allCells
            .filter((cell, index) => !actionColumnIndexes.includes(index))
            .map(cell => {
                const clone = cell.cloneNode(true);
                const svgElements = clone.querySelectorAll('svg');
                svgElements.forEach(svg => svg.remove());
                return cleanTextForJSON(clone.innerText);
            });
    });

    // Crear estructura HTML completa para Excel
    let excelData = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        </head>
        <body>
            <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">
                <thead><tr>
    `;

    // Agregar encabezados
    headerLabels.forEach(header => {
        excelData += `<th style="background-color: #4472C4; color: white; font-weight: bold; border: 1px solid #000; padding: 8px;">${header}</th>`;
    });
    excelData += '</tr></thead><tbody>';

    // Agregar datos
    dataRows.forEach((row, rowIndex) => {
        const bgColor = rowIndex % 2 === 0 ? '#ffffff' : '#f2f2f2';
        excelData += `<tr style="background-color: ${bgColor};">`;
        row.forEach(cell => {
            excelData += `<td style="border: 1px solid #000; padding: 8px;">${cell}</td>`;
        });
        excelData += '</tr>';
    });

    excelData += `
            </tbody>
        </table>
        </body>
        </html>
    `;

    // Crear blob y descargar
    const blob = new Blob([excelData], {
        type: "application/vnd.ms-excel;charset=utf-8;"
    });

    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportClientJSON(config, filename) {
    const allHeaders = Array.from(config.table.querySelectorAll("th"));
    const actionColumnIndexes = [];
    const headers = [];

    allHeaders.forEach((th, index) => {
        if (th.dataset.type === 'actions') {
            actionColumnIndexes.push(index);
        } else {
            headers.push(cleanTextForJSON(th.innerText));
        }
    });

    const data = config.filteredRows.map(row => {
        const allCells = Array.from(row.querySelectorAll("td"));
        const cells = allCells.filter((cell, index) => !actionColumnIndexes.includes(index));
        const rowData = {};

        cells.forEach((cell, index) => {
            if (headers[index]) {
                let cellText = cleanTextForJSON(cell.innerText);
                const numValue = parseFloat(cellText);
                if (!isNaN(numValue) && isFinite(numValue) && cellText === numValue.toString()) {
                    rowData[headers[index]] = numValue;
                } else if (cellText.toLowerCase() === 'true' || cellText.toLowerCase() === 'false') {
                    rowData[headers[index]] = cellText.toLowerCase() === 'true';
                } else {
                    rowData[headers[index]] = cellText;
                }
            }
        });

        return rowData;
    });

    const jsonData = {
        exported_at: new Date().toISOString(),
        total_records: data.length,
        data: data
    };

    const jsonString = JSON.stringify(jsonData, null, 2);
    const blob = new Blob([jsonString], { type: "application/json;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function printTable(config) {
    const headers = Array.from(config.table.querySelectorAll("th"));
    const actionColumnIndexes = [];
    headers.forEach((th, index) => {
        if (th.dataset.type === 'actions') {
            actionColumnIndexes.push(index);
        }
    });

    const tableClone = config.table.cloneNode(true);
    const allRows = Array.from(tableClone.querySelectorAll("tr"));

    allRows.forEach(row => {
        const cells = Array.from(row.querySelectorAll("th, td"));
        actionColumnIndexes.sort((a, b) => b - a).forEach(index => {
            if (cells[index]) {
                cells[index].remove();
            }
        });

        const remainingCells = Array.from(row.querySelectorAll("th, td"));
        remainingCells.forEach(cell => {
            cell.textContent = cleanTextForJSON(cell.textContent);
        });
    });

    const printHTML = `
        <html>
        <head>
            <title>Reporte de Tabla</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: white; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; font-size: 12px; }
                th { background-color: #4a5568; color: white; font-weight: bold; text-transform: uppercase; }
                tr:nth-child(even) { background-color: #f8f9fa; }
                tr:hover { background-color: #e9ecef; }
                .print-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4a5568; padding-bottom: 10px; }
                .print-date { text-align: right; font-size: 10px; color: #666; margin-bottom: 10px; }
                @media print { body { margin: 0; } .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class="print-date">Generado el: ${new Date().toLocaleString('es-ES')}</div>
            <div class="print-header"><h2>Reporte de Datos</h2></div>
            ${tableClone.outerHTML}
        </body>
        </html>
    `;

    const win = window.open("", "", "width=900,height=700");
    win.document.write(printHTML);
    win.document.close();
    win.print();
}

// Funciones responsive (sin cambios)
function initResponsiveFeatures(config) {
    const toggleBtn = document.getElementById(`${config.tableId}-toggle-columns`);
    const columnsContainer = document.getElementById(`${config.tableId}-columns-container`);

    if (toggleBtn && columnsContainer) {
        toggleBtn.addEventListener('click', function() {
            const isVisible = !columnsContainer.classList.contains('hidden');

            if (isVisible) {
                columnsContainer.classList.add('hidden');
                this.setAttribute('aria-expanded', 'false');
            } else {
                columnsContainer.classList.remove('hidden');
                this.setAttribute('aria-expanded', 'true');
            }
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
        this.style.borderColor = '#3b82f6';

        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            this.style.borderColor = '';
        }, 1000);
    });

    function checkScrollNeed() {
        const needsScroll = container.scrollWidth > container.clientWidth;
        if (needsScroll) {
            container.setAttribute('data-scrollable', 'true');
        } else {
            container.removeAttribute('data-scrollable');
        }
    }

    checkScrollNeed();
    window.addEventListener('resize', checkScrollNeed);
}

function cleanTextForJSON(text) {
    return text
        .replace(/[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/gu, '')
        .replace(/[\u{FE00}-\u{FE0F}]|[\u{200D}]|[\u{20E3}]/gu, '')
        .replace(/\s+/g, ' ')
        .trim();
}

// ============================================================================
// FUNCIONES DE EXPORTACIÓN PDF
// ============================================================================

async function exportTableToPDF(config, filename = "export.pdf") {
    if (config.isServerSide) {
        // Para server-side, descargar PDF del servidor
        const params = new URLSearchParams({
            export: 'pdf',
            search: config.searchTerm,
            sort_by: config.sortBy || '',
            sort_order: config.sortOrder || 'asc'
        });
        window.location.href = `${config.searchUrl}?${params.toString()}`;
    } else {
        // Para client-side, generar PDF local
        exportClientPDF(config, filename);
    }
}

function exportClientPDF(config, filename) {
    // Verificar si jsPDF está disponible
    if (typeof window.jspdf === 'undefined' && typeof jsPDF === 'undefined') {
        console.warn('[Enhanced Table] jsPDF no está disponible. Cargando desde CDN...');
        loadJsPDFAndExport(config, filename);
        return;
    }

    generatePDFWithJsPDF(config, filename);
}

function loadJsPDFAndExport(config, filename) {
    // Cargar jsPDF y autoTable desde CDN
    const script1 = document.createElement('script');
    script1.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';

    const script2 = document.createElement('script');
    script2.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js';

    script1.onload = () => {
        script2.onload = () => {
            console.log('[Enhanced Table] jsPDF cargado exitosamente');
            generatePDFWithJsPDF(config, filename);
        };
        document.head.appendChild(script2);
    };

    document.head.appendChild(script1);
}

function generatePDFWithJsPDF(config, filename) {
    // Obtener jsPDF del window object
    const { jsPDF } = window.jspdf || window;

    if (!jsPDF) {
        console.error('[Enhanced Table] No se pudo cargar jsPDF');
        alert('Error al cargar la biblioteca PDF. Por favor, intenta de nuevo.');
        return;
    }

    // Preparar datos de la tabla
    const headers = Array.from(config.table.querySelectorAll("th"));
    const actionColumnIndexes = [];
    const headerLabels = [];

    headers.forEach((th, index) => {
        if (th.dataset.type === 'actions') {
            actionColumnIndexes.push(index);
        } else {
            const clone = th.cloneNode(true);
            const svgElements = clone.querySelectorAll('svg, .sort-arrow');
            svgElements.forEach(svg => svg.remove());
            headerLabels.push(cleanTextForJSON(clone.innerText));
        }
    });

    const dataRows = config.filteredRows.map(row => {
        const allCells = Array.from(row.querySelectorAll("td"));
        return allCells
            .filter((cell, index) => !actionColumnIndexes.includes(index))
            .map(cell => {
                const clone = cell.cloneNode(true);
                const svgElements = clone.querySelectorAll('svg, button, a');
                svgElements.forEach(element => element.remove());
                return cleanTextForJSON(clone.innerText);
            });
    });

    // Crear documento PDF
    const doc = new jsPDF({
        orientation: headerLabels.length > 6 ? 'landscape' : 'portrait',
        unit: 'mm',
        format: 'a4'
    });

    // Título del documento
    doc.setFontSize(16);
    doc.setTextColor(40, 40, 40);
    doc.text('Reporte de Datos', 14, 15);

    // Fecha de generación
    doc.setFontSize(9);
    doc.setTextColor(100, 100, 100);
    doc.text(`Generado: ${new Date().toLocaleString('es-ES')}`, 14, 22);

    // Información de registros
    doc.text(`Total de registros: ${dataRows.length}`, 14, 27);

    // Generar tabla con autoTable
    doc.autoTable({
        head: [headerLabels],
        body: dataRows,
        startY: 32,
        theme: 'striped',
        headStyles: {
            fillColor: [68, 114, 196],
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            fontSize: 10,
            halign: 'left'
        },
        bodyStyles: {
            fontSize: 9,
            cellPadding: 3
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245]
        },
        margin: { top: 32, right: 14, bottom: 14, left: 14 },
        styles: {
            overflow: 'linebreak',
            cellWidth: 'wrap',
            minCellHeight: 8
        },
        columnStyles: generateColumnStyles(headerLabels.length),
        didDrawPage: function(data) {
            // Footer con número de página
            const pageCount = doc.internal.getNumberOfPages();
            doc.setFontSize(8);
            doc.setTextColor(128, 128, 128);
            doc.text(
                `Página ${data.pageNumber} de ${pageCount}`,
                doc.internal.pageSize.width / 2,
                doc.internal.pageSize.height - 10,
                { align: 'center' }
            );
        }
    });

    // Descargar PDF
    doc.save(filename);
    console.log(`[Enhanced Table] PDF exportado: ${filename}`);
}

function generateColumnStyles(columnCount) {
    // Ajustar anchos de columna automáticamente
    const styles = {};
    const equalWidth = 'auto';

    for (let i = 0; i < columnCount; i++) {
        styles[i] = {
            cellWidth: equalWidth,
            halign: 'left'
        };
    }

    return styles;
}
