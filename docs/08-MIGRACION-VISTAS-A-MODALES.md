# 📋 Checklist: Migración de Vistas CRUD a Sistema de Modales

> **Fecha de inicio**: 1 de diciembre de 2025  
> **Sistema**: Streamify - Sistema de Gestión de Cuentas  
> **Objetivo**: Migrar todas las vistas separadas (create.blade.php, edit.blade.php) a modales integrados en la vista index

---

## 🎯 Beneficios de la Migración

- ✅ **UX mejorada**: Sin recargas de página, experiencia más fluida
- ✅ **Performance**: Menos peticiones HTTP, carga inicial única
- ✅ **Mantenibilidad**: Todo el CRUD en un solo archivo
- ✅ **Consistencia**: Mismo diseño y comportamiento en todas las vistas
- ✅ **Responsive**: Modales adaptables a móvil, tablet y desktop
- ✅ **Temas adaptables**: Funciona con Default, Christmas y Dark Mode

---

## 📊 Estado de Migración

### ✅ Completado

| Módulo | Index | Create | Edit | Delete | Fecha | Notas |
|--------|-------|--------|------|--------|-------|-------|
| **Mantenimientos** | ✅ | ✅ | ✅ | ✅ | 2025-12-01 | Primer módulo migrado, incluye validaciones AJAX |
| **Servicios** | ✅ | ✅ | ✅ | ✅ | 2025-12-01 | Segundo módulo, CRUD con 7 campos, 5 errores resueltos |
| **Proveedores** | ✅ | ✅ | ✅ | ✅ | 2025-12-01 | Tercer módulo, CRUD simple con 2 campos |
| **Valores** | ✅ | ✅ | ✅ | ✅ | 2025-12-02 | Cuarto módulo, 8 campos con selects, string PK, patrón cross-module |
| **Productos** | ✅ | ✅ | ✅ | ✅ | 2025-12-02 | Quinto módulo, detalles anidados, subida de archivos, 5 modales totales |

### 🔄 Pendientes de Migración

#### 📦 Inventory (resources/views/inventory)

| Módulo | Prioridad | Index | Create | Edit | Delete | Archivos Actuales | Notas |
|--------|-----------|-------|--------|------|--------|-------------------|-------|
| **Cuentas** | 🔴 Alta | ✅ | ❌ | ❌ | ❌ | `create.blade.php`, `edit.blade.php` | Módulo principal, tiene show.blade.php, pdf.blade.php, renew.blade.php, mails.blade.php, tabla.blade.php, spotify.blade.php |
| **Usuarios** | 🟡 Media | ✅ | ❌ | ❌ | ❌ | Solo `change.blade.php` | Gestión de usuarios, tiene botón "Nueva Venta" que comparte con Ventas |

#### 💰 Sales (resources/views/sales)

| Módulo | Prioridad | Index | Create | Edit | Delete | Archivos Actuales | Notas |
|--------|-----------|-------|--------|------|--------|-------------------|-------|
| **Ventas** | 🔴 Alta | ✅ | ❌ | ❌ | ❌ | `create.blade.php`, `edit.blade.php`, `renew.blade.php` | **CRÍTICO**: Botón compartido con Usuarios, tiene tabla con partials/table-rows.blade.php |
| **Clientes** | 🟡 Media | ✅ | ❌ | ❌ | ❌ | `create.blade.php`, `edit.blade.php` | CRUD estándar, tiene partials/table-rows.blade.php |

#### 👥 Employee (resources/views/employee)

| Módulo | Prioridad | Index | Create | Edit | Delete | Archivos Actuales | Notas |
|--------|-----------|-------|--------|------|--------|-------------------|-------|
| **Empleados** | 🟡 Media | ✅ | ❌ | ❌ | ❌ | `create.blade.php`, `edit.blade.php` | Gestión de empleados, tiene statistics.blade.php, tareas.blade.php, roles.blade.php |

#### 🔐 Roles (resources/views/roles)

| Módulo | Prioridad | Index | Create | Edit | Delete | Archivos Actuales | Notas |
|--------|-----------|-------|--------|------|--------|-------------------|-------|
| **Roles** | 🟢 Baja | ✅ | ❌ | ❌ | ❌ | `create.blade.php`, `edit.blade.php` | Gestión de roles y permisos |

---

## 🛠️ Pasos para Migrar un Módulo

### 1. Preparación (Análisis)

- [ ] Revisar `index.blade.php` actual
- [ ] Identificar campos del formulario en `create.blade.php`
- [ ] Identificar campos del formulario en `edit.blade.php`
- [ ] Revisar validaciones en el Controller
- [ ] Verificar permisos requeridos
- [ ] Identificar relaciones (selects, autocomplete)

### 2. Crear Estructura de Modales

**Carpeta**: `resources/views/inventory/{modulo}/modals/`

- [ ] Crear `modals/create.blade.php`
- [ ] Crear `modals/edit.blade.php`
- [ ] Crear `modals/delete.blade.php`
- [ ] (Opcional) Crear `modals/view.blade.php` para vista detallada

### 3. Implementar Modal Create

**Archivo**: `modals/create.blade.php`

```blade
<x-modal name="create-{modulo}" maxWidth="lg" :closeable="true">
    <div class="modal-header p-4">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-plus-circle"></i> Crear {Entidad}
        </h5>
        <button type="button" class="btn-close" x-on:click="$dispatch('close-modal', 'create-{modulo}')"></button>
    </div>
    <form id="create-form" onsubmit="submitCreate(event)">
        <div class="modal-body p-4">
            @csrf
            <!-- Campos del formulario -->
        </div>
        <div class="modal-footer p-4">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'create-{modulo}')">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </form>
</x-modal>
```

### 4. Implementar Modal Edit

**Archivo**: `modals/edit.blade.php`

```blade
<x-modal name="edit-{modulo}" maxWidth="lg" :closeable="true">
    <div class="modal-header p-4">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-edit"></i> Editar {Entidad}
        </h5>
        <button type="button" class="btn-close" x-on:click="$dispatch('close-modal', 'edit-{modulo}')"></button>
    </div>
    <form id="edit-form" onsubmit="submitEdit(event)">
        <div class="modal-body p-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id" name="id">
            <!-- Campos del formulario -->
        </div>
        <div class="modal-footer p-4">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'edit-{modulo}')">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Actualizar
            </button>
        </div>
    </form>
</x-modal>
```

### 5. Implementar Modal Delete

**Archivo**: `modals/delete.blade.php`

```blade
<x-modal name="delete-{modulo}" maxWidth="md" :closeable="true">
    <div class="modal-header p-4">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación
        </h5>
        <button type="button" class="btn-close" x-on:click="$dispatch('close-modal', 'delete-{modulo}')"></button>
    </div>
    <div class="modal-body p-4">
        <div class="text-center mb-3">
            <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
            <p class="mb-2 fw-semibold">¿Estás seguro de que deseas eliminar este registro?</p>
            <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
        </div>
        <div class="alert alert-warning mb-0" role="alert">
            <strong><i class="fas fa-info-circle"></i> Información del registro:</strong>
            <div class="mt-2" id="delete-info">
                <!-- Información dinámica -->
            </div>
        </div>
        <input type="hidden" id="delete-id" value="">
    </div>
    <div class="modal-footer p-4">
        <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'delete-{modulo}')">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
            <i class="fas fa-trash"></i> Eliminar
        </button>
    </div>
</x-modal>
```

### 6. Actualizar index.blade.php

#### A. Incluir modales (antes de `@endsection`)

```blade
<!-- Modales -->
@include('inventory.{modulo}.modals.create')
@include('inventory.{modulo}.modals.edit')
@include('inventory.{modulo}.modals.delete')
@endsection
```

#### B. Actualizar botones en tabla

```blade
<!-- Botón Crear -->
<button onclick="openCreateModal()" class="btn btn-primary">
    <i class="fas fa-plus"></i> Crear {Entidad}
</button>

<!-- Botón Editar (en acciones de tabla) -->
<button onclick="openEditModal({{ $item->id }})" class="btn btn-warning btn-sm" title="Editar">
    <i class="fas fa-edit"></i>
</button>

<!-- Botón Eliminar (en acciones de tabla) -->
<button onclick="openDeleteModal({{ $item->id }})" class="btn btn-danger btn-sm" title="Eliminar">
    <i class="fas fa-trash"></i>
</button>
```

#### C. Implementar funciones JavaScript (en sección `@section('scripts')`)

```javascript
// Crear
function openCreateModal() {
    console.log('🔷 Abriendo modal de crear...');
    const form = document.getElementById('create-form');
    if (form) form.reset();
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-{modulo}' }));
}

function closeCreateModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'create-{modulo}' }));
}

function submitCreate(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    fetch('{{ route("{modulo}.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeCreateModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.error || 'Error al crear', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error al procesar la solicitud', 'danger');
    });
}

// Editar
function openEditModal(id) {
    console.log('🔷 Abriendo modal de editar para ID:', id);
    
    const url = '{{ route("{modulo}.edit", "__ID__") }}'.replace('__ID__', id);
    
    fetch(url, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Rellenar campos del formulario
                document.getElementById('edit-id').value = data.item.id;
                // ... más campos
                
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-{modulo}' }));
            }
        });
}

function closeEditModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'edit-{modulo}' }));
}

function submitEdit(event) {
    event.preventDefault();
    const id = document.getElementById('edit-id').value;
    const formData = new FormData(event.target);
    
    const url = '{{ route("{modulo}.update", "__ID__") }}'.replace('__ID__', id);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeEditModal();
            setTimeout(() => location.reload(), 1500);
        }
    });
}

// Eliminar
function openDeleteModal(id) {
    const url = '{{ route("{modulo}.edit", "__ID__") }}'.replace('__ID__', id);
    
    fetch(url, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('delete-id').value = id;
                // Mostrar información en el modal
                document.getElementById('delete-info').innerHTML = `
                    <p><strong>Campo:</strong> ${data.item.campo}</p>
                `;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-{modulo}' }));
            }
        });
}

function closeDeleteModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'delete-{modulo}' }));
}

function confirmDelete() {
    const id = document.getElementById('delete-id').value;
    
    const url = '{{ route("{modulo}.destroy", "__ID__") }}'.replace('__ID__', id);
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeDeleteModal();
            setTimeout(() => location.reload(), 1500);
        }
    });
}

// Alertas
function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alert = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    alertContainer.innerHTML = alert;
    setTimeout(() => alertContainer.innerHTML = '', 5000);
}
```

### 7. Actualizar Controller

⚠️ **CRÍTICO**: Asegurar que los métodos detecten correctamente las peticiones AJAX y retornen JSON.

**Problema común**: `request()->wantsJson()` no siempre detecta peticiones AJAX correctamente.

**Solución**: Usar triple verificación: `ajax()`, `wantsJson()` y verificar header `Accept`.

```php
public function edit($id)
{
    $item = Model::with('relaciones')->findOrFail($id);
    
    // ✅ TRIPLE VERIFICACIÓN para detectar AJAX
    if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
        return response()->json([
            'success' => true,
            'item' => $item,
            // Campos adicionales si se necesitan
        ]);
    }
    
    // Fallback para peticiones normales (si aún existe la vista)
    return view('inventory.{modulo}.edit', compact('item'));
}

public function store(Request $request)
{
    // validaciones...
    $item = Model::create($request->all());
    
    // ✅ TRIPLE VERIFICACIÓN
    if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
        return response()->json([
            'success' => true,
            'message' => 'Registro creado exitosamente',
            'item' => $item
        ]);
    }
    
    return redirect()->route('{modulo}')->with('success', 'Registro creado exitosamente');
}

public function update(Request $request, $id)
{
    // validaciones...
    $item = Model::findOrFail($id);
    $item->update($request->all());
    
    // ✅ TRIPLE VERIFICACIÓN
    if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado exitosamente',
            'item' => $item
        ]);
    }
    
    return redirect()->route('{modulo}')->with('success', 'Registro actualizado exitosamente');
}

public function destroy($id)
{
    $item = Model::findOrFail($id);
    $item->delete();
    
    // ✅ TRIPLE VERIFICACIÓN
    if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado exitosamente'
        ]);
    }
    
    return redirect()->route('{modulo}')->with('success', 'Registro eliminado exitosamente');
}
```

**Notas importantes**:
- ✅ Siempre usar la **triple verificación** para evitar errores
- ✅ El header `Accept: application/json` debe estar en todas las peticiones fetch
- ✅ Si el modal no abre, revisar en consola del navegador si hay error 500 o HTML en lugar de JSON

### 8. Eliminar Vistas Antiguas

- [ ] Eliminar `create.blade.php`
- [ ] Eliminar `edit.blade.php`
- [ ] Verificar que no haya enlaces directos en rutas o navegación

### 9. Testing

- [ ] Probar crear registro
- [ ] Probar editar registro
- [ ] Probar eliminar registro
- [ ] Probar cerrar modal con X
- [ ] Probar cerrar modal con botón Cancelar
- [ ] Probar cerrar modal con tecla ESC
- [ ] Probar cerrar modal haciendo click fuera
- [ ] Verificar validaciones
- [ ] Verificar en móvil/tablet
- [ ] Verificar en los 3 temas (Default, Christmas, Dark Mode)

#### ✅ Checklist de Verificación Crítica

**Controller (verificar en DevTools → Network → XHR)**:
- [ ] `edit/{id}` retorna JSON (no HTML) ✅
- [ ] Response tiene `{"success": true, "item": {...}}` ✅
- [ ] `store` retorna JSON con `success` y `message` ✅
- [ ] `update` retorna JSON con `success` y `message` ✅
- [ ] `destroy` retorna JSON con `success` y `message` ✅

**JavaScript (verificar en consola del navegador)**:
- [ ] No hay errores de "Route not defined" ✅
- [ ] Logging muestra emojis correctos (🔷📤✅❌🗑️) ✅
- [ ] showAlert() muestra mensajes de éxito/error ✅

**Rutas (verificar en routes/web.php)**:
- [ ] Todas las rutas están definidas ✅
- [ ] Los nombres de ruta coinciden con los usados en JavaScript ✅
- [ ] Los paths coinciden con los fetch() del frontend ✅

### 10. Documentación

- [ ] Actualizar este checklist con el módulo completado
- [ ] Documentar cualquier peculiaridad del módulo
- [ ] Actualizar fecha de migración

---

## 📝 Plantilla de Commit

```
feat(modales): Migrar CRUD de {Módulo} a sistema de modales

- ✅ Crear modal create.blade.php
- ✅ Crear modal edit.blade.php
- ✅ Crear modal delete.blade.php
- ✅ Actualizar index.blade.php con modales integrados
- ✅ Actualizar Controller para respuestas JSON
- ✅ Eliminar vistas create.blade.php y edit.blade.php
- ✅ Testing completo en desktop y móvil
- ✅ Validado en 3 temas del sistema

Relacionado: #ISSUE_NUMBER
```

---

## 🎨 Componentes Requeridos

### CSS
- ✅ `public/css/modal-system.css` - Sistema de modales
- ✅ `public/css/enhanced-table-global.css` - Estilos de tablas

### JavaScript
- ✅ Alpine.js 3.x - Manejo de modales
- ✅ `public/js/enhanced-table-v2.js` - Sistema de tablas

### Blade Components
- ✅ `resources/views/components/modal.blade.php` - Componente modal

---

## 📊 Métricas de Progreso

| Métrica | Valor |
|---------|-------|
| **Módulos totales** | 10 |
| **Completados** | 2 (20%) |
| **Pendientes** | 8 (80%) |
| **Alta prioridad** | 3 (Cuentas, Productos, Ventas) |
| **Media prioridad** | 5 (Proveedores, Usuarios, Clientes, Empleados) |
| **Baja prioridad** | 2 (Valores, Roles) |
| **Vistas eliminadas** | 4 (Mantenimientos, Servicios) |
| **Modales creados** | 6 (Mantenimientos, Servicios) |

---

## ⚠️ Casos Especiales

### 🔴 Ventas - Botón Compartido
**Problema**: El botón "Nueva Venta" aparece en dos módulos:
- `resources/views/sales/ventas/index.blade.php`
- `resources/views/inventory/usuarios/index.blade.php`

**Solución Propuesta**:
1. Crear modal `sales/ventas/modals/create.blade.php`
2. Incluir el modal en ambas vistas: `ventas/index.blade.php` y `usuarios/index.blade.php`
3. Ambos botones llamarán a la misma función `openCreateVentaModal()`
4. Asegurar que el modal se cargue solo una vez usando un flag global

**Código sugerido**:
```blade
<!-- En ventas/index.blade.php -->
@include('sales.ventas.modals.create')

<!-- En usuarios/index.blade.php -->
@include('sales.ventas.modals.create')
```

**JavaScript global** (en ambas vistas):
```javascript
// Evitar duplicación si el modal ya existe
if (!window.ventasModalLoaded) {
    window.ventasModalLoaded = true;
    // Funciones de modal aquí
}
```

---

## 🔗 Referencias

- **Documentación Modal**: `docs/07-COMPONENTE-MODAL-SISTEMA.md`
- **Enhanced Table v2**: `docs/01-ENHANCED-TABLE-V2-GUIA-COMPLETA.md`
- **Ejemplo Completo**: `resources/views/inventory/mantenimientos/index.blade.php`

---

## 📌 Notas Importantes

1. **Siempre usar `maxWidth` en camelCase**, no `max-width`
2. **Incluir `:closeable="true"`** para habilitar cierre con ESC
3. **Usar `x-on:click="$dispatch('close-modal', 'nombre-modal')`** en botones de cerrar
4. **IDs únicos** para cada campo del formulario (prefijo create-, edit-, delete-)
5. **Validaciones** tanto en frontend (HTML5) como backend (Controller)
6. **CSRF Token** en todos los formularios
7. **JSON responses** en todos los métodos del Controller
8. **Logging con emojis** para facilitar debugging en consola
9. **showAlert()** para feedback visual al usuario
10. **setTimeout()** para recargar página después de 1.5s

---

## 🐛 Troubleshooting

### ❌ Error: "Modal edit/delete no abre - Error al cargar los datos"

**Síntomas**: 
- Modal create funciona ✅
- Modal edit/delete no abren ❌
- Consola muestra error al cargar datos del servicio

**Causa**: El Controller no detecta la petición AJAX y retorna HTML en lugar de JSON.

**Solución**:
```php
// ❌ MAL - Solo wantsJson() no es suficiente
if (request()->wantsJson()) {
    return response()->json([...]);
}

// ✅ BIEN - Triple verificación
if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
    return response()->json([...]);
}
```

**Verificación**:
1. Abrir DevTools → Network → XHR
2. Hacer click en "Editar" o "Eliminar"
3. Verificar que la respuesta sea JSON (no HTML)
4. Si es HTML, aplicar la triple verificación en el Controller

---

### ❌ Error: "Route [nombre.index] not defined"

**Síntomas**: Error al cargar la vista con mensaje de ruta no definida.

**Causa**: Usar `route('nombre.index')` cuando la ruta está definida como `route('nombre')`.

**Solución**:
1. Revisar `routes/web.php` para ver el nombre exacto de la ruta
2. Actualizar todas las referencias en JavaScript usando `route()` de Laravel:

```javascript
// ❌ MAL - Paths absolutos no funcionan en producción/subdirectorios
fetch(`/servicios/${id}/edit`, {...})

// ✅ BIEN - Usar route() de Laravel para generar URLs correctas
fetch(`{{ route('servicios.edit', '') }}/${idser}`, {...})

// Para POST/PUT/DELETE también:
fetch('{{ route("servicios.store") }}', {...})
fetch(`{{ route('servicios.update', '') }}/${idser}`, {...})
fetch(`{{ route('servicios.destroy', '') }}/${idser}`, {...})
```

**Por qué es importante**:
- ❌ `/servicios/...` solo funciona en local en raíz (http://localhost/servicios)
- ✅ `route()` funciona en local, desarrollo y producción
- ✅ `route()` maneja automáticamente subdirectorios y prefijos de ruta
- ✅ Si cambias el path en routes/web.php, no necesitas actualizar el JS

**Patrón correcto para rutas con parámetros**:
```javascript
// ✅ Patrón placeholder + replace (recomendado)
const url = '{{ route("modulo.edit", "__ID__") }}'.replace('__ID__', id);
fetch(url, {...})

// Para rutas sin parámetros:
fetch('{{ route("modulo.store") }}', {...})
```

**Por qué usar `__ID__` como placeholder**:
- ✅ Laravel genera la ruta completa con el placeholder
- ✅ JavaScript reemplaza `__ID__` con el valor real
- ✅ No genera URLs malformadas (dobles barras, etc.)
- ✅ Funciona con cualquier tipo de primary key (string, int)

---

### ❌ Error: "404 Not Found en /servicios/{id}/edit"

**Síntomas**: 
- Error 404 al abrir modal edit/delete
- Consola muestra: "Failed to load resource: 404"
- Mensaje: "❌ Error al cargar datos: undefined"

**Causa**: El parámetro de ruta no coincide con la primary key del modelo.

**Ejemplo del problema**:
```php
// routes/web.php
Route::get('/servicios/{id}/edit', 'edit');  // ❌ Usa {id}

// Model
protected $primaryKey = 'idser';  // ✅ Primary key es idser

// Controller
public function edit($idser) {  // ✅ Espera $idser
    $servicio = Servicio::findOrFail($idser);
}
```

**Solución**:
1. **Verificar el modelo** para identificar la primary key:
```php
// app/Models/Servicio.php
protected $primaryKey = 'idser';  // ← Este es el nombre correcto
```

2. **Actualizar routes/web.php** para usar el mismo nombre:
```php
// ❌ ANTES
Route::get('/servicios/{id}/edit', 'edit');
Route::put('/servicios/{id}', 'update');
Route::delete('/servicios/{id}', 'destroy');

// ✅ DESPUÉS
Route::get('/servicios/{idser}/edit', 'edit');
Route::put('/servicios/{idser}', 'update');
Route::delete('/servicios/{idser}', 'destroy');
```

3. **Actualizar JavaScript** para usar el nombre correcto:
```javascript
// ❌ ANTES
function openEditModal(id) {
    const id = document.getElementById('edit-idser').value;
    fetch(`/servicios/${id}/edit`, {...})
}

// ✅ DESPUÉS
function openEditModal(idser) {  // ← Nombre consistente
    const idser = document.getElementById('edit-idser').value;
    fetch(`/servicios/${idser}/edit`, {...})
}
```

4. **Actualizar botones en la tabla** para pasar el parámetro correcto:
```blade
<!-- ❌ ANTES -->
<button onclick="openEditModal({{ $servicio->id }})">

<!-- ✅ DESPUÉS -->
<button onclick="openEditModal('{{ $servicio->idser }}')">
```

5. **CRÍTICO: Actualizar Controller para buscar correctamente**:
```php
// ❌ MAL - findOrFail() no funciona bien con primary keys string
$servicio = Servicio::findOrFail($idser);

// ✅ BIEN - Usar where()->firstOrFail() para primary keys string
$servicio = Servicio::where('idser', $idser)->firstOrFail();
```

**Aplicar en todos los métodos del Controller**:
```php
public function edit($idser) {
    $servicio = Servicio::where('idser', $idser)->firstOrFail();
    // ...
}

public function update(Request $request, $idser) {
    $servicio = Servicio::where('idser', $idser)->firstOrFail();
    // ...
}

public function destroy($idser) {
    $servicio = Servicio::where('idser', $idser)->firstOrFail();
    // ...
}
```

**Verificación**:
1. DevTools → Network → XHR
2. Click en "Editar"
3. URL debe ser `/servicios/VALOR_CORRECTO/edit` (no `/servicios/undefined/edit`)
4. Response debe ser JSON 200, no 404

**Nota importante**: Este problema solo ocurre cuando:
- Primary key NO es autoincremental (`$incrementing = false`)
- Primary key es de tipo string (`$keyType = 'string'`)
- En estos casos, **siempre usar `where()->firstOrFail()`** en lugar de `findOrFail()`

---

### ❌ Error: "Rutas funcionan en local pero fallan en producción/desarrollo"

**Síntomas**: 
- Modales funcionan en local (http://localhost/servicios)
- En producción o con subdirectorio fallan con 404
- URLs generadas no incluyen el prefijo correcto

**Causa**: Usar paths absolutos (`/servicios/...`) en lugar de `route()` de Laravel.

**Problema en producción**:
```javascript
// ❌ MAL - Solo funciona en raíz del dominio
fetch(`/servicios/${id}/edit`, {...})
// En http://localhost/servicios → ✅ Funciona
// En http://example.com/app/servicios → ❌ Busca /servicios (404)
```

**Solución - Usar route() de Laravel**:
```javascript
// ✅ BIEN - Usar placeholder y replace para generar URLs correctas
const url = '{{ route("servicios.edit", "__ID__") }}'.replace('__ID__', idser);
fetch(url, {...})

// Resultado en local: http://localhost/admin/servicios/CANVA/edit
// Resultado en producción: http://example.com/app/admin/servicios/CANVA/edit
```

**❌ Errores comunes al construir rutas**:
```javascript
// ❌ MAL - Genera doble barra //edit/
fetch(`{{ route('servicios.edit', '') }}/${idser}`, {...})
// Genera: /admin/servicios//edit/CANVA (404)

// ❌ MAL - No funciona en subdirectorios
fetch(`/servicios/${idser}/edit`, {...})
// Solo funciona en raíz del dominio

// ✅ BIEN - Placeholder + replace
const url = '{{ route("servicios.edit", "__ID__") }}'.replace('__ID__', idser);
fetch(url, {...})
// Genera: /admin/servicios/CANVA/edit
```

**Actualizar todos los fetch en el JavaScript**:
```javascript
// CREATE - Sin parámetros en la ruta
fetch('{{ route("servicios.store") }}', {
    method: 'POST',
    body: formData
})

// EDIT - Cargar datos con parámetro
function openEditModal(idser) {
    const url = '{{ route("servicios.edit", "__ID__") }}'.replace('__ID__', idser);
    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
}

// UPDATE - Actualizar con parámetro
function submitEdit(event) {
    const idser = document.getElementById('edit-idser').value;
    const url = '{{ route("servicios.update", "__ID__") }}'.replace('__ID__', idser);
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ _method: 'PUT', ... })
    })
}

// DELETE - Cargar datos (reutiliza edit)
function openDeleteModal(idser) {
    const url = '{{ route("servicios.edit", "__ID__") }}'.replace('__ID__', idser);
    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
}

// DESTROY - Eliminar con parámetro
function confirmDelete() {
    const idser = document.getElementById('delete-idser').value;
    const url = '{{ route("servicios.destroy", "__ID__") }}'.replace('__ID__', idser);
    fetch(url, {
        method: 'DELETE'
    })
}
```

**Beneficios de usar `route()`**:
- ✅ Funciona en local, desarrollo y producción
- ✅ Maneja automáticamente subdirectorios
- ✅ Si cambias la ruta en web.php, el JS se actualiza automáticamente
- ✅ Respeta prefijos y grupos de rutas

---

### ❌ Error: "Paginación con colores invisibles"

**Síntomas**: Texto blanco sobre fondo blanco en botones de paginación.

**Solución**: Ya implementado en `enhanced-table-global.css`:
```css
[id$="-pagination"] .btn {
    background-color: #ffffff !important;
    color: #1a1a1a !important;
    border: 2px solid var(--border-color) !important;
}
```

---

**Última actualización**: 2 de diciembre de 2025  
**Módulos completados**: Mantenimientos ✅, Servicios ✅, Proveedores ✅, Valores ✅  
**Próximos módulos sugeridos**:
1. 🔴 **Productos** (Alta prioridad + tiene show.blade.php)
2. 🟡 **Clientes** (Media prioridad + tiene partials)
3. 🟡 **Empleados** (Media prioridad + statistics/tareas)
4. 🔴 **Cuentas** (Alta prioridad + módulo principal)
5. 🟡 **Usuarios** (Media prioridad + conectado con Ventas)
6. 🔴 **Ventas** (Alta prioridad + caso especial del botón compartido)
7. 🟢 **Roles** (Baja prioridad)

**Orden de migración recomendado**:
1. Productos (tiene show.blade.php)
2. Clientes (tiene partials)
3. Empleados (statistics/tareas)
4. Cuentas (módulo más importante)
5. Usuarios (conectado con Ventas)
6. Ventas (resolver caso especial último)
7. Roles (permisos)
