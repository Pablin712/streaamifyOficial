# Guía de mejoras frontend — Streamify HQ
**Fecha:** Mayo 2026 | **Stack:** Bootstrap 5.2.3 + Variables CSS + Sistema de Temas

---

## Índice

1. [Sistema de diseño](#1-sistema-de-diseño)
2. [Modo oscuro — reglas de oro](#2-modo-oscuro--reglas-de-oro)
3. [Vista de referencia: Configuración del sistema](#3-vista-de-referencia-configuración-del-sistema)
4. [Mapa de vistas y estado actual](#4-mapa-de-vistas-y-estado-actual)
5. [Problemas globales a corregir](#5-problemas-globales-a-corregir)
6. [Checklist por vista](#6-checklist-por-vista)
7. [Componentes y patrones reutilizables](#7-componentes-y-patrones-reutilizables)
8. [Convenciones de código](#8-convenciones-de-código)

---

## 1. Sistema de diseño

### Variables CSS — úsalas siempre, nunca colores hardcoded

El archivo `public/css/themes.css` define **todas las variables** del sistema. Estas variables cambian automáticamente cuando el usuario cambia de tema o activa el modo oscuro.

```css
/* FONDOS */
var(--bg-body)         /* Fondo general de la página */
var(--bg-card)         /* Fondo de tarjetas y paneles */
var(--bg-light)        /* Fondo de secciones secundarias, thead de tablas */
var(--bg-hover)        /* Hover de filas, items de lista */
var(--bg-table-odd)    /* Filas impares de tabla */
var(--bg-table-even)   /* Filas pares de tabla */

/* TEXTOS */
var(--text-primary)    /* Texto principal, headings */
var(--text-secondary)  /* Subtítulos, descripciones */
var(--text-muted)      /* Texto apagado, placeholders */
var(--text-on-primary) /* Texto sobre botones/badges de color primario */

/* BORDES Y SOMBRAS */
var(--border-color)    /* Bordes de cards, tablas, inputs */
var(--border-primary)  /* Borde de acento (color del tema) */
var(--shadow-sm)       /* Sombra sutil */
var(--shadow-md)       /* Sombra de cards */
var(--shadow-lg)       /* Sombra de modales, dropdowns */

/* COLOR DEL TEMA ACTIVO */
var(--primary-color)   /* Color principal del tema */
var(--primary-dark)    /* Versión oscura del primario (hover) */
var(--primary-light)   /* Versión clara del primario */
var(--primary-gradient)/* Gradiente con el color del tema */
var(--secondary-color) /* Color secundario del tema */
var(--accent-color)    /* Color de acento */
```

### Lo que NUNCA debes escribir

```html
<!-- ❌ MAL: colores hardcoded -->
<div style="background: #ffffff; color: #333333;">
<div class="bg-white text-dark">
<div style="background:#800080">  <!-- visto en cuentas/index.blade.php -->

<!-- ✅ BIEN: variables CSS -->
<div style="background: var(--bg-card); color: var(--text-primary);">
<div class="tema-card">  <!-- clase que ya usa variables -->
```

### Jerarquía de archivos CSS

```
themes.css          ← FUENTE DE VERDAD (variables + dark mode overlay)
styles.css          ← Bootstrap base + componentes globales
sidebar.css         ← Sidebar (ya usa variables correctamente)
navbar.css          ← Navbar (ya usa variables correctamente)
enhanced-table-global.css ← Tablas DataTables
modal-system.css    ← Sistema de modales Alpine.js
select2-dark-mode.css ← Select2 en modo oscuro
```

**Regla:** Si necesitas estilos propios para una vista, agrégalos en un `@section('styles')` en el blade, usando las variables CSS. **No crear archivos CSS nuevos por vista.**

---

## 2. Modo oscuro — reglas de oro

### Cómo funciona

El modo oscuro es un **overlay** que se activa con `data-dark-mode="true"` en el `<html>`. No cambia el tema activo, solo sobreescribe las variables de fondo, texto y borde.

```css
/* Así funciona en themes.css */
[data-dark-mode="true"] {
  --bg-body:  #121212 !important;
  --bg-card:  #1e1e1e !important;
  --bg-light: #2a2a2a !important;
  --text-primary: #ffffff !important;
  --border-color: #333333 !important;
  /* ... etc */
}
```

### Las 10 reglas del dark mode

1. **Usa `var(--bg-card)` en lugar de `bg-white` o `background:#fff`** — en dark mode se vuelve `#1e1e1e` automáticamente.

2. **Usa `var(--text-primary)` en lugar de `text-dark` o `color:#333`** — en dark mode se vuelve blanco automáticamente.

3. **Evita `bg-white`, `bg-light`, `text-dark`, `text-black`** — Bootstrap los define pero el override de dark mode los cubre vía `[data-dark-mode="true"] .bg-light` etc.

4. **Los inputs ya están cubiertos** en `themes.css` para `.form-control`, `.form-select`. Solo asegúrate de NO sobreescribir su background manualmente.

5. **Imágenes/iconos sobre fondo claro**: si tienes un logo o imagen que solo funciona en claro, usa `filter: invert(1)` condicionalmente o provee dos versiones.

6. **Badges de estado**: usa clases Bootstrap semánticas (`bg-success`, `bg-danger`, `bg-warning`) — ya están cubiertas en dark mode. Evita colores inline.

7. **Tablas con `table-striped`**: ya funcionan con variables. Asegúrate de no sobreescribir el `background` de `<tr>` directamente.

8. **Select2**: ya está cubierto por `select2-dark-mode.css`. Solo asegúrate de inicializar con la clase correcta.

9. **Gráficos (Chart.js, etc.)**: deben detectar el tema activo. Ver sección de componentes.

10. **Textos hardcoded sobre fondos coloridos**: revisar contraste. El amarillo `#ffe226` sobre blanco en dark mode puede desaparecer — usar `--text-on-primary`.

### Quick-fix para una vista con dark mode roto

```html
<!-- En el blade, añade esto al contenedor principal: -->
<style>
  .mi-card {
    background: var(--bg-card);
    color: var(--text-primary);
    border-color: var(--border-color);
  }
  .mi-header {
    background: var(--bg-light);
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
  }
</style>
```

---

## 3. Vista de referencia: Configuración del sistema

**Archivo:** `resources/views/settings/sistema/index.blade.php`

Esta vista es el **estándar de referencia** del proyecto. Observa y replica sus patrones:

### Patrón: `sistema-card`

```html
<div class="sistema-card mb-4">
  <div class="sistema-card-header">
    <span><i class="fas fa-icon me-2"></i>Título de la sección</span>
    <span class="badge ...">Estado opcional</span>
  </div>
  <div class="sistema-card-body">
    <!-- contenido -->
  </div>
</div>
```

**Por qué funciona:** usa variables CSS internamente, tiene transiciones suaves, shadow coherente, y el header tiene gradiente del tema activo.

### Patrón: Header de página

```html
<nav aria-label="breadcrumb" class="mt-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Nombre de la vista</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <div class="sistema-icon-wrap">
    <i class="fas fa-icono"></i>
  </div>
  <div>
    <h1 class="h3 mb-0 fw-bold">Título</h1>
    <p class="text-muted mb-0 small">Descripción breve</p>
  </div>
</div>
```

### Patrón: Toggle / Switch

```html
<label class="sistema-switch">
  <input type="checkbox" id="miToggle">
  <span class="sistema-switch-slider"></span>
</label>
```

### Lo que NO tiene la vista Sistema (y que otras vistas necesitan)

- No tiene tablas complejas → ver `enhanced-table.blade.php`
- No tiene formularios de creación/edición → ver patrón de modales
- No tiene estados de carga → ver spinner pattern

---

## 4. Mapa de vistas y estado actual

### Leyenda de estado

| Símbolo | Significado |
|---------|-------------|
| ✅ | Bien: usa variables CSS, dark mode funciona |
| ⚠️ | Parcial: algunos colores hardcoded, dark mode funciona con overrides |
| ❌ | Crítico: colores hardcoded, dark mode roto o inconsistente |
| 🔧 | Prioridad de mejora |

---

### Auth

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Login | `auth/login.blade.php` | ⚠️ | Usa layout diferente (`layouts/cliente.blade.php`), sin variables de tema |
| Registro | `auth/register.blade.php` | ⚠️ | Mismo layout cliente |

**Nota:** las vistas auth usan Bootstrap CDN externo. Menor prioridad pero conviene unificar.

---

### Settings

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Sistema | `settings/sistema/index.blade.php` | ✅ | **REFERENCIA** — ninguno |

---

### Inventory / Cuentas

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Listado cuentas | `inventory/cuentas/index.blade.php` | ❌ 🔧 | `style="background:#800080"` hardcoded; badges con colores inline; dark mode roto en headers de sección |
| Modal mensaje clientes | `inventory/cuentas/modals/mensaje-clientes.blade.php` | ⚠️ | Fondos parcialmente variables |
| Modal mensaje proveedor | `inventory/cuentas/modals/mensaje-proveedor.blade.php` | ⚠️ | Ídem |
| Otros modals (×10) | `inventory/cuentas/modals/*.blade.php` | ⚠️ | Revisar headers de modal |

**Acciones para cuentas/index.blade.php:**
- Reemplazar todos los `style="background:#..."` por clases con variables
- Usar `.badge.bg-{semántico}` en lugar de colores inline en badges de estado
- Headers de sección: cambiar `background: #f8f9fa` → `var(--bg-light)`

---

### Inventory / Usuarios

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Listado usuarios | `inventory/usuarios/index.blade.php` | ⚠️ 🔧 | Algunos badges con `background` inline; tabla con `bg-white` explícito |
| Modals | `inventory/usuarios/modals/*.blade.php` | ⚠️ | Headers de modal con color fijo |

---

### Inventory / Productos

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Listado | `inventory/productos/index.blade.php` | ⚠️ | Revisar tarjetas de producto |
| Modals (×10) | `inventory/productos/modals/*.blade.php` | ⚠️ | Inconsistencia en headers |

---

### Inventory / Proveedores, Servicios, Valores, Mantenimientos

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Modals varios | `inventory/*/modals/*.blade.php` | ⚠️ | Headers con `bg-primary` Bootstrap (no el tema) |

---

### Finance / Finanzas

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Bancos | `finance/bancos/modals/*.blade.php` | ⚠️ | Fondos de modal header |
| Costos | `finance/costos/modals/*.blade.php` | ⚠️ | Ídem |
| Gastos | `finance/gastos/modals/*.blade.php` | ⚠️ | Ídem |

---

### Sales / Clientes

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Clientes | `sales/clientes/modals/*.blade.php` | ⚠️ | Headers hardcoded |

---

### Employee

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Lista empleados | `employee/index.blade.php` | ⚠️ | Revisar dark mode en tabla |
| Editar empleado | `employee/edit.blade.php` | ⚠️ | Formulario con inputs sin override de tema |

---

### Roles

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Crear rol | `roles/create.blade.php` | ⚠️ | Checkboxes de permisos con estilos inline |
| Editar rol | `roles/edit.blade.php` | ⚠️ | Ídem |

---

### Donna

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Dashboard | `donna/dashboard.blade.php` | ⚠️ 🔧 | Gráficos sin adaptación dark mode |
| Conversaciones | `donna/conversaciones.blade.php` | ⚠️ | Panel de chat con estilos propios |
| Planes | `donna/planes.blade.php` | ⚠️ | Cards de precios con colores inline |
| Solicitudes | `donna/solicitudes.blade.php` | ⚠️ | Tabla con estados hardcoded |
| Suscripciones | `donna/suscripciones.blade.php` | ⚠️ | Ídem |

---

### Chat / Helpdesk

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| WhatsApp Helpdesk | `livewire/chat/whatsapp-helpdesk.blade.php` | ⚠️ 🔧 | Panel de chat con estilos muy específicos; burbujas de mensaje con colores fijos |
| Panel conversaciones | `livewire/chat/panel-conversaciones.blade.php` | ⚠️ | Similar al anterior |

---

### Administration

| Vista | Archivo | Estado | Problema |
|-------|---------|--------|---------|
| Calendario | `administration/calendar.blade.php` | ❌ 🔧 | FullCalendar con colores hardcoded; sin adaptación dark mode |

---

## 5. Problemas globales a corregir

### 5.1 Colores hardcoded — lista de patrones a eliminar

Busca y reemplaza estos patrones en todas las vistas:

```bash
# Buscar con grep (PowerShell):
Select-String -Path "resources\views\**\*.blade.php" -Pattern "background:\s*#|background-color:\s*#|color:\s*#" -Recurse
```

| Patrón incorrecto | Reemplazar por |
|-------------------|----------------|
| `style="background:#fff"` | `style="background:var(--bg-card)"` |
| `style="background:#f8f9fa"` | `style="background:var(--bg-light)"` |
| `style="background:#800080"` | clase semántica o `var(--primary-color)` |
| `class="bg-white"` | `style="background:var(--bg-card)"` o clase propia |
| `class="text-dark"` | `style="color:var(--text-primary)"` |
| `class="border border-gray-..."` | `style="border-color:var(--border-color)"` |

### 5.2 Headers de modales — patrón estandarizado

Actualmente hay 3 estilos diferentes de modal-header en el proyecto. **Estandarizar a:**

```html
<!-- Modal header CORRECTO -->
<div class="modal-header" style="
  background: var(--bg-light);
  border-bottom: 1px solid var(--border-color);
">
  <h5 class="modal-title" style="color: var(--text-primary);">
    <i class="fas fa-icono me-2" style="color: var(--primary-color);"></i>
    Título del modal
  </h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<!-- Modal body -->
<div class="modal-body" style="background: var(--bg-card); color: var(--text-primary);">
  <!-- contenido -->
</div>

<!-- Modal footer -->
<div class="modal-footer" style="
  background: var(--bg-light);
  border-top: 1px solid var(--border-color);
">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
  <button type="submit" class="btn" style="
    background: var(--primary-gradient);
    color: var(--text-on-primary);
    border: none;
  ">Guardar</button>
</div>
```

### 5.3 Badges de estado — usar semántica Bootstrap

```html
<!-- ❌ MAL -->
<span style="background:#28a745; color:#fff; padding:3px 8px; border-radius:4px;">Activo</span>

<!-- ✅ BIEN — Bootstrap semántico (ya cubierto en dark mode) -->
<span class="badge bg-success">Activo</span>
<span class="badge bg-danger">Inactivo</span>
<span class="badge bg-warning text-dark">Pendiente</span>
<span class="badge bg-info">En proceso</span>
<span class="badge bg-secondary">Sin asignar</span>
```

### 5.4 Tablas — estructura coherente

```html
<div class="card" style="
  background: var(--bg-card);
  border-color: var(--border-color);
  box-shadow: var(--shadow-md);
">
  <div class="card-header" style="
    background: var(--bg-light);
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
  ">
    <i class="fas fa-table me-2" style="color: var(--primary-color);"></i>
    Título de la tabla
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead style="background: var(--bg-light);">
        <tr>
          <th style="color: var(--text-primary); border-color: var(--border-color);">Columna</th>
        </tr>
      </thead>
      <tbody>
        <tr style="background: var(--bg-table-even);">
          <td style="color: var(--text-primary); border-color: var(--border-color);">Dato</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
```

### 5.5 Formularios — inputs y labels

```html
<div class="mb-3">
  <label class="form-label fw-semibold" style="color: var(--text-primary);">
    Nombre del campo
  </label>
  <input
    type="text"
    class="form-control"
    {{-- NO añadir style de background/color -- themes.css ya lo cubre --}}
    placeholder="Escribe aquí..."
  >
  <div class="form-text" style="color: var(--text-muted);">Texto de ayuda opcional.</div>
</div>
```

### 5.6 Botones de acción — consistencia

```html
<!-- Botón primario del tema -->
<button class="btn" style="
  background: var(--primary-gradient);
  color: var(--text-on-primary);
  border: none;
  font-weight: 600;
">
  <i class="fas fa-save me-1"></i> Guardar
</button>

<!-- Botón secundario -->
<button class="btn btn-outline-secondary">
  <i class="fas fa-times me-1"></i> Cancelar
</button>

<!-- Botón peligro -->
<button class="btn btn-danger">
  <i class="fas fa-trash me-1"></i> Eliminar
</button>

<!-- Botón de acción de tabla (compacto) -->
<button class="btn btn-sm btn-outline-primary" title="Editar">
  <i class="fas fa-edit"></i>
</button>
```

---

## 6. Checklist por vista

Usa este checklist al mejorar cualquier vista:

### Checklist de revisión

- [ ] **Fondos**: ningún `background:#xxx` hardcoded — todo usa `var(--bg-*)`.
- [ ] **Textos**: ningún `color:#xxx` hardcoded en elementos de texto — usa `var(--text-*)`.
- [ ] **Bordes**: usa `var(--border-color)` o clases Bootstrap estándar.
- [ ] **Shadows**: usa `var(--shadow-sm/md/lg)`.
- [ ] **Botón primario**: usa `var(--primary-gradient)` o `var(--primary-color)`.
- [ ] **Badges de estado**: usa clases Bootstrap semánticas (`bg-success`, `bg-danger`, etc.).
- [ ] **Headers de modal**: siguen el patrón estandarizado de la sección 5.2.
- [ ] **Inputs y selects**: no tienen `background` manual (lo cubre `themes.css`).
- [ ] **Dark mode verificado**: abrir la vista con dark mode activado y comprobar visualmente.
- [ ] **Tema verificado**: cambiar a tema Navidad o Mundial y comprobar que los colores del tema se aplican.
- [ ] **Sin archivos CSS nuevos**: estilos propios dentro de `@section('styles')` en el blade.

---

## 7. Componentes y patrones reutilizables

### 7.1 `x-enhanced-table` — tablas con exportación

```blade
<x-enhanced-table
  id="tabla-cuentas"
  :csv="true"
  :excel="true"
  :print="true"
  :headers="['ID', 'Nombre', 'Estado', 'Fecha', 'Acciones']"
>
  {{-- El contenido de <tbody> va aquí --}}
</x-enhanced-table>
```

### 7.2 `x-modal` — modales Alpine.js

```blade
{{-- Trigger --}}
<button @click="$dispatch('open-modal', 'nombre-modal')" class="btn">
  Abrir Modal
</button>

{{-- Modal --}}
<x-modal name="nombre-modal" maxWidth="lg">
  <div class="p-4" style="background: var(--bg-card); color: var(--text-primary);">
    <h3 class="mb-3">Título</h3>
    <!-- contenido -->
  </div>
</x-modal>
```

### 7.3 `x-searchable-select` — select con búsqueda

```blade
<x-searchable-select
  name="cliente_id"
  placeholder="Buscar cliente..."
  :options="$clientes"
  value="{{ old('cliente_id') }}"
/>
```

### 7.4 Patrón: página con tabla y filtros

```html
<!-- Estructura recomendada para vistas de listado -->
<div class="container-fluid px-4 pb-5">

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mt-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
      <li class="breadcrumb-item active">Vista</li>
    </ol>
  </nav>

  <!-- Header + botón de acción -->
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
      <div class="sistema-icon-wrap">
        <i class="fas fa-icon"></i>
      </div>
      <div>
        <h1 class="h3 mb-0 fw-bold">Título</h1>
        <p class="text-muted mb-0 small">Descripción</p>
      </div>
    </div>
    <button class="btn" style="
      background: var(--primary-gradient);
      color: var(--text-on-primary);
      border: none;
    ">
      <i class="fas fa-plus me-1"></i> Nueva entidad
    </button>
  </div>

  <!-- Filtros / búsqueda rápida -->
  <div class="sistema-card mb-4">
    <div class="sistema-card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <input type="text" class="form-control" placeholder="Buscar...">
        </div>
        <!-- más filtros -->
      </div>
    </div>
  </div>

  <!-- Tabla principal -->
  <div class="sistema-card">
    <div class="sistema-card-header">
      <span><i class="fas fa-list me-2"></i>Registros</span>
    </div>
    <div class="sistema-card-body p-0">
      <!-- tabla aquí -->
    </div>
  </div>

</div>
```

### 7.5 Spinner de carga (estado vacío / loading)

```html
<!-- Loading state -->
<div class="text-center py-5" id="loadingState">
  <div class="spinner-border" style="color: var(--primary-color);" role="status">
    <span class="visually-hidden">Cargando...</span>
  </div>
  <p class="mt-2" style="color: var(--text-muted);">Cargando datos...</p>
</div>

<!-- Empty state -->
<div class="text-center py-5" id="emptyState" style="display:none;">
  <i class="fas fa-inbox fa-3x mb-3" style="color: var(--text-muted);"></i>
  <p class="mb-0 fw-semibold" style="color: var(--text-primary);">Sin registros</p>
  <p class="small" style="color: var(--text-muted);">No hay datos que mostrar.</p>
</div>
```

---

## 8. Convenciones de código

### Orden de atributos en HTML

```html
<div
  id="..."
  class="..."
  style="..."
  data-*="..."
  wire:*="..."
  x-*="..."
  @*="..."
>
```

### Estructura de `@section('styles')` por vista

```blade
@section('styles')
<style>
  /* ── NombreVista ────────────────────────────────── */

  .mi-componente {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
  }

  .mi-componente-header {
    background: var(--bg-light);
    border-bottom: 1px solid var(--border-color);
    padding: 1rem 1.25rem;
    color: var(--text-primary);
    font-weight: 600;
  }

  /* NO necesitas media queries para dark mode — themes.css lo cubre */
</style>
@endsection
```

### Nombramiento de clases CSS propias

- Usa kebab-case: `.mi-clase-css`
- Prefija con el nombre del módulo para evitar colisiones: `.cuentas-badge`, `.usuarios-action-bar`
- Evita nombres genéricos como `.card2`, `.box-blue`, `.header-custom`

### Iconos

- Usa **FontAwesome** (`fas fa-*`, `fab fa-*`) — ya incluido
- Usa **Bootstrap Icons** (`bi bi-*`) — ya incluido via CDN
- No mezcles: elige uno por sección y mantenlo consistente
- Tamaño inline: `class="fa-sm"`, `class="fa-lg"`, `class="fa-xl"`

### Breakpoints para responsive

```css
/* Bootstrap 5 breakpoints (referencia) */
xs: < 576px   (móvil)
sm: ≥ 576px   (móvil grande)
md: ≥ 768px   (tablet)
lg: ≥ 992px   (desktop pequeño)
xl: ≥ 1200px  (desktop)
xxl: ≥ 1400px (desktop grande)

/* Usa clases Bootstrap: d-none d-md-block, col-12 col-lg-8, etc. */
```

---

## Orden de trabajo sugerido

Dado el estado actual del proyecto, se sugiere este orden de mejoras:

### Sprint 1 — Correcciones críticas (❌)
1. `inventory/cuentas/index.blade.php` — eliminar `background:#800080` y todos los colores hardcoded
2. `administration/calendar.blade.php` — adaptar FullCalendar al sistema de variables
3. Revisar el chat Helpdesk (`livewire/chat/whatsapp-helpdesk.blade.php`) — burbujas con variables

### Sprint 2 — Estandarización de modales (⚠️)
1. Aplicar el patrón de modal-header estandarizado a todos los modales en `inventory/*/modals/`
2. Aplicar a `finance/*/modals/` y `sales/*/modals/`
3. Actualizar botones de submit para usar `var(--primary-gradient)`

### Sprint 3 — Mejoras visuales
1. Donna dashboard — adaptar gráficos a dark mode
2. Roles — mejorar el listado de checkboxes de permisos
3. Employee — mejorar formulario de edición
4. Donna planes — mejorar cards de precios

### Sprint 4 — Pulido general
1. Auth (login/register) — valorar unificar con el sistema de temas
2. Revisión final de todas las vistas con modo oscuro activado
3. Revisión con cada tema (navidad, mundial, etc.)

---

*Guía generada en Mayo 2026. Actualizar esta guía cada vez que se agreguen nuevas vistas o se modifique el sistema de temas (`themes.css`).*
