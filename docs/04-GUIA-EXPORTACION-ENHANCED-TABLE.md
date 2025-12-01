# 📤 Guía de Exportación - Enhanced Table v2.0

**Fecha:** 30 de noviembre de 2025  
**Versión:** 2.0  
**Objetivo:** Implementar botones de exportación en tablas con Enhanced Table v2

---

## 🎯 Formatos Disponibles

Enhanced Table v2.0 incluye 4 funciones de exportación integradas:

| Formato | Función | Librería Requerida | Uso Común |
|---------|---------|-------------------|-----------|
| **CSV** | `exportTableToCSV()` | ❌ Nativa | Importar a Excel, análisis de datos |
| **Excel** | `exportTableToExcel()` | ✅ SheetJS (XLSX) | Reportes profesionales, fórmulas |
| **JSON** | `exportTableToJSON()` | ❌ Nativa | APIs, desarrollo, backups |
| **PDF** | `exportTableToPDF()` | ✅ jsPDF + autotable | Documentos oficiales, imprimir |

---

## ⚙️ Configuración Inicial

### 1. Incluir Librerías CDN (Opcional)

Si quieres usar **Excel** y **PDF**, agrega estas librerías en tu vista:

```blade
@section('scripts')
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

<!-- Opcional: Librerías para exportación -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
@endsection
```

**Nota:** CSV y JSON funcionan sin librerías adicionales.

---

## 🔘 Implementación de Botones

### Opción 1: Botones Individuales

Agrega botones en tu vista con los IDs específicos:

```blade
<div class="mb-3">
    <button id="ventas-table-export-csv" class="btn btn-success btn-sm">
        <i class="fas fa-file-csv"></i> Exportar CSV
    </button>
    <button id="ventas-table-export-excel" class="btn btn-primary btn-sm">
        <i class="fas fa-file-excel"></i> Exportar Excel
    </button>
    <button id="ventas-table-export-json" class="btn btn-info btn-sm">
        <i class="fas fa-code"></i> Exportar JSON
    </button>
    <button id="ventas-table-export-pdf" class="btn btn-danger btn-sm">
        <i class="fas fa-file-pdf"></i> Exportar PDF
    </button>
</div>
```

**Patrón de IDs:** `{tableId}-export-{formato}`

Donde `{tableId}` es el valor de `data-table` en tu `<table>`.

**Ejemplo:** Si tu tabla es `<table data-table="clientes-table">`, los IDs serán:
- `clientes-table-export-csv`
- `clientes-table-export-excel`
- `clientes-table-export-json`
- `clientes-table-export-pdf`

---

### Opción 2: Dropdown Compacto (Recomendado)

Para ahorrar espacio, usa un dropdown:

```blade
<div class="btn-group mb-3" role="group">
    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
        <i class="fas fa-download"></i> Exportar
    </button>
    <ul class="dropdown-menu">
        <li>
            <button id="ventas-table-export-csv" class="dropdown-item">
                <i class="fas fa-file-csv text-success"></i> CSV
            </button>
        </li>
        <li>
            <button id="ventas-table-export-excel" class="dropdown-item">
                <i class="fas fa-file-excel text-primary"></i> Excel
            </button>
        </li>
        <li>
            <button id="ventas-table-export-json" class="dropdown-item">
                <i class="fas fa-code text-info"></i> JSON
            </button>
        </li>
        <li>
            <button id="ventas-table-export-pdf" class="dropdown-item">
                <i class="fas fa-file-pdf text-danger"></i> PDF
            </button>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <button id="ventas-table-print" class="dropdown-item">
                <i class="fas fa-print text-dark"></i> Imprimir
            </button>
        </li>
    </ul>
</div>
```

---

### Opción 3: Ubicación en el Encabezado de la Tabla

Integra los botones junto a la búsqueda:

```blade
<div class="card-body">
    <!-- Encabezado: Búsqueda, Registros y Exportación -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-5 col-md-5 col-12 mb-3 mb-md-0">
            <label for="ventas-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="ventas-table-search" type="text" class="form-control">
        </div>
        <div class="col-lg-3 col-md-3 col-12 mb-3 mb-md-0">
            <label for="ventas-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="ventas-table-rows-per-page" class="form-select">
                <option value="5" selected>5 registros</option>
                <option value="10">10 registros</option>
            </select>
        </div>
        <div class="col-lg-4 col-md-4 col-12">
            <label class="form-label fw-semibold d-block">
                <i class="fas fa-download text-primary"></i> Exportar:
            </label>
            <div class="btn-group" role="group">
                <button id="ventas-table-export-csv" class="btn btn-outline-success btn-sm" title="Exportar CSV">
                    <i class="fas fa-file-csv"></i>
                </button>
                <button id="ventas-table-export-excel" class="btn btn-outline-primary btn-sm" title="Exportar Excel">
                    <i class="fas fa-file-excel"></i>
                </button>
                <button id="ventas-table-export-json" class="btn btn-outline-info btn-sm" title="Exportar JSON">
                    <i class="fas fa-code"></i>
                </button>
                <button id="ventas-table-export-pdf" class="btn btn-outline-danger btn-sm" title="Exportar PDF">
                    <i class="fas fa-file-pdf"></i>
                </button>
            </div>
        </div>
    </div>
    <!-- ... resto de la tabla ... -->
</div>
```

---

## 🔧 Funcionamiento Automático

Una vez que agregas los botones con los IDs correctos, **Enhanced Table v2 los detecta automáticamente** y configura los listeners.

**No necesitas escribir JavaScript adicional.** 

El script `enhanced-table-v2.js` ya incluye:

```javascript
// Botones de exportación (líneas 383-393)
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
```

---

## 📋 Características de las Exportaciones

### CSV (`exportTableToCSV`)
- **Codificación:** UTF-8 con BOM
- **Delimitador:** Comas (`,`)
- **Manejo de comillas:** Escapa `"` como `""`
- **Columnas excluidas:** Las marcadas con `data-type="actions"`
- **Nombre por defecto:** `export.csv`

**Ejemplo de output:**
```csv
ID,Cliente,Teléfono,Correo
1,"José María","555-1234","jose@example.com"
2,"María José","555-5678","maria@example.com"
```

---

### Excel (`exportTableToExcel`)
- **Formato:** XLSX (Excel 2007+)
- **Hoja:** "Data"
- **Estilos:** Encabezados en negrita automáticamente
- **Columnas excluidas:** Las marcadas con `data-type="actions"`
- **Nombre por defecto:** `export.xlsx`

**Ventajas:**
- Soporta acentos y caracteres especiales
- Formato compatible con Google Sheets
- Permite aplicar fórmulas después de exportar

---

### JSON (`exportTableToJSON`)
- **Formato:** JSON estructurado con metadatos
- **Estructura:**
  ```json
  {
    "exported_at": "2025-11-30T10:30:00.000Z",
    "total": 100,
    "data": [
      { "ID": "1", "Cliente": "José María", "Teléfono": "555-1234" },
      { "ID": "2", "Cliente": "María José", "Teléfono": "555-5678" }
    ]
  }
  ```
- **Uso:** Ideal para APIs, backup de datos, desarrollo

---

### PDF (`exportTableToPDF`)
- **Orientación:** Automática (landscape si >6 columnas)
- **Tema:** Striped (filas alternadas)
- **Encabezado:** Título "Reporte de Datos" + fecha de generación
- **Estilos:** Encabezados con fondo azul (#4472C4)
- **Columnas excluidas:** Las marcadas con `data-type="actions"`
- **Nombre por defecto:** `export.pdf`

**Configuración personalizable:**
```javascript
// Si necesitas personalizar el PDF, puedes llamar la función directamente:
const config = window.enhancedTableConfig['ventas-table'];
exportTableToPDF(config, 'reporte-ventas-noviembre.pdf');
```

---

## ⚠️ Consideraciones Importantes

### 1. Columnas de Acciones
Las columnas marcadas con `data-type="actions"` **NO se exportan** en ningún formato:

```blade
<th data-type="actions">Acciones</th>
```

Esto previene que botones de editar/eliminar aparezcan en los reportes.

---

### 2. Datos Filtrados vs Completos
**Enhanced Table v2 exporta solo los datos visibles después de aplicar filtros.**

- Si buscaste "José", solo se exportarán las filas con "José"
- Si no hay filtros activos, se exportan todas las filas

**Ventaja:** Los reportes reflejan exactamente lo que el usuario ve en pantalla.

---

### 3. Validación de Librerías
Si falta una librería, el usuario verá un alert:

```javascript
// Excel sin SheetJS
alert('Librería Excel no disponible');

// PDF sin jsPDF
alert('Librería PDF no disponible');
```

**CSV y JSON siempre funcionan** porque son nativos del navegador.

---

## 🎨 Ejemplo Completo: Vista de Clientes

```blade
@extends('layouts.navigation')
@section('title', 'Clientes')

@section('main')
<div class="container-fluid px-4">
    <h1 class="mt-4">Clientes</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-users"></i> Lista de Clientes
            </h6>
            <!-- Botones de exportación alineados a la derecha -->
            <div class="btn-group" role="group">
                <button id="clientes-table-export-csv" class="btn btn-success btn-sm">
                    <i class="fas fa-file-csv"></i> CSV
                </button>
                <button id="clientes-table-export-excel" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button id="clientes-table-export-pdf" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Búsqueda y paginación -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="clientes-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="clientes-table-search" type="text" 
                           placeholder="Buscar por nombre, teléfono, correo..." 
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="clientes-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="clientes-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                    </select>
                </div>
            </div>

            <!-- Tabla -->
            <div class="table-responsive">
                <table id="clientes-table" 
                       data-table="clientes-table" 
                       class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="0">
                                ID
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="1">Nombre</th>
                            <th class="sortable" data-type="string" data-col="2">Teléfono</th>
                            <th data-type="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clientes as $cliente)
                            <tr>
                                <td>{{ $cliente->id }}</td>
                                <td>{{ $cliente->nombre }}</td>
                                <td>{{ $cliente->telefono }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="clientes-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="clientes-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
<!-- Opcional: Librerías para Excel y PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
@endsection
```

---

## 🚀 Personalización Avanzada

### Cambiar Nombres de Archivos

Puedes llamar las funciones directamente desde JavaScript:

```javascript
document.getElementById('mi-boton-custom').addEventListener('click', function() {
    const config = window.enhancedTableConfig['clientes-table'];
    exportTableToExcel(config, 'reporte-clientes-2025.xlsx');
});
```

### Exportar con Filtros Programáticos

```javascript
// Aplicar filtro antes de exportar
const searchInput = document.getElementById('clientes-table-search');
searchInput.value = 'activo';
searchInput.dispatchEvent(new Event('input'));

// Esperar a que se aplique el filtro
setTimeout(() => {
    const config = window.enhancedTableConfig['clientes-table'];
    exportTableToPDF(config, 'clientes-activos.pdf');
}, 300);
```

---

## ✅ Checklist de Implementación

- [ ] Agregar botones con IDs correctos (`{tableId}-export-{formato}`)
- [ ] Incluir `enhanced-table-v2.js` en `@section('scripts')`
- [ ] (Opcional) Agregar librerías CDN para Excel/PDF
- [ ] Marcar columnas de acciones con `data-type="actions"`
- [ ] Probar cada formato de exportación
- [ ] Verificar que datos filtrados se exportan correctamente
- [ ] Personalizar nombres de archivo si es necesario

---

## 📚 Recursos Adicionales

- **SheetJS Docs:** https://docs.sheetjs.com/
- **jsPDF Docs:** https://github.com/parallax/jsPDF
- **Enhanced Table v2 Docs:** `docs/02-GUIA-USO-ENHANCED-TABLE.md`

---

**¡Listo!** Ahora puedes agregar funcionalidad de exportación a cualquier tabla con Enhanced Table v2. 🎉
