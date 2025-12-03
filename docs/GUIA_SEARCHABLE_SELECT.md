# 📘 Guía de Uso: Componente `searchable-select`

## 🎯 Resumen Rápido

El componente `searchable-select` es un wrapper de **Select2** con soporte automático para **modo oscuro/claro** y configuración simplificada.

---

## 📦 Paso 1: Incluir Dependencias en tu Vista

### En la sección `@section('styles')`:

```blade
@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Select2 Dark Mode -->
    <link href="{{ asset('css/select2-dark-mode.css') }}" rel="stylesheet" />
@endsection
```

### En la sección `@section('scripts')`:

```blade
@section('scripts')
    <!-- jQuery (debe cargarse primero) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 (debe cargarse después de jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Inicializador de searchable-selects -->
    <script src="{{ asset('js/searchable-select.js') }}"></script>
    
    <!-- Tus scripts específicos van después -->
@endsection
```

⚠️ **IMPORTANTE**: El orden es crucial:
1. jQuery primero
2. Select2 segundo
3. searchable-select.js tercero
4. Tus scripts al final

---

## 🔧 Paso 2: Usar el Componente

### **Opción A: Usando el Componente Blade** (Recomendado)

```blade
<x-searchable-select
    id="idcli"
    name="idcli"
    :options="$clientes"
    :selected="old('idcli')"
    placeholder="Buscar cliente por nombre o teléfono..."
    valueField="idcli"
    labelField="nombrecli"
    :required="true"
/>
```

### **Opción B: HTML Directo con Clase**

```blade
<select name="idcli" id="idcli" 
        class="form-control searchable-select" 
        required
        data-placeholder="Buscar cliente por nombre o teléfono...">
    <option value="">-- Selecciona un Cliente --</option>
    @foreach ($clientes as $cliente)
        <option value="{{ $cliente->idcli }}">
            {{ $cliente->nombrecli }} - {{ $cliente->telefonocli }}
        </option>
    @endforeach
</select>
```

---

## 📋 Propiedades del Componente

| Propiedad | Tipo | Obligatorio | Default | Descripción |
|-----------|------|-------------|---------|-------------|
| `id` | string | ✅ Sí | - | ID del select |
| `name` | string | ✅ Sí | - | Nombre del campo |
| `options` | array/collection | ✅ Sí | `[]` | Array de opciones |
| `selected` | mixed | ❌ No | `null` | Valor preseleccionado |
| `placeholder` | string | ❌ No | 'Seleccione...' | Texto del placeholder |
| `required` | bool | ❌ No | `false` | Campo obligatorio |
| `valueField` | string | ❌ No | `'id'` | Nombre del campo para el value |
| `labelField` | string | ❌ No | `'name'` | Nombre del campo para el label |
| `allowClear` | bool | ❌ No | `true` | Permitir limpiar selección |
| `dropdownParent` | string | ❌ No | `null` | Selector del contenedor (para modales) |

---

## 💡 Ejemplos Prácticos

### **Ejemplo 1: Select Simple de Clientes**

```blade
{{-- En el Controller --}}
$clientes = Cliente::all();

{{-- En la Vista --}}
<x-searchable-select
    id="cliente_id"
    name="cliente_id"
    :options="$clientes"
    :selected="old('cliente_id', $venta->cliente_id ?? null)"
    placeholder="Seleccionar cliente..."
    valueField="idcli"
    labelField="nombrecli"
    :required="true"
/>
```

### **Ejemplo 2: Select con Campos Personalizados**

```blade
{{-- Si tu modelo tiene campos diferentes --}}
<x-searchable-select
    id="producto_id"
    name="producto_id"
    :options="$productos"
    placeholder="Buscar producto..."
    valueField="idprod"
    labelField="nombreprod"
/>
```

### **Ejemplo 3: Select en Modal**

```blade
{{-- IMPORTANTE: Agregar dropdownParent para modales --}}
<x-searchable-select
    id="empleado_id"
    name="empleado_id"
    :options="$empleados"
    placeholder="Seleccionar empleado..."
    valueField="idemp"
    labelField="nombreemp"
    dropdownParent="#miModal"
/>
```

### **Ejemplo 4: Select Multiple**

```blade
<select name="categorias[]" 
        id="categorias" 
        class="form-control searchable-select" 
        multiple
        data-placeholder="Seleccionar categorías...">
    @foreach ($categorias as $categoria)
        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
    @endforeach
</select>
```

### **Ejemplo 5: Select con Datos Concatenados**

```blade
{{-- Opción 1: En el controlador --}}
$clientes = Cliente::all()->map(function($cliente) {
    return [
        'id' => $cliente->idcli,
        'nombre_completo' => "{$cliente->nombrecli} - {$cliente->telefonocli}"
    ];
});

{{-- En la vista --}}
<x-searchable-select
    id="cliente_id"
    name="cliente_id"
    :options="$clientes"
    valueField="id"
    labelField="nombre_completo"
/>

{{-- Opción 2: Directo en el HTML --}}
<select name="cliente_id" class="form-control searchable-select">
    @foreach ($clientes as $cliente)
        <option value="{{ $cliente->idcli }}">
            {{ $cliente->nombrecli }} - {{ $cliente->telefonocli }}
        </option>
    @endforeach
</select>
```

---

## 🎨 Modo Oscuro

El modo oscuro se aplica **automáticamente** según:

1. ✅ **ThemeManager** (si existe y está activo)
2. ✅ Atributo `data-dark-mode` en `<html>`
3. ✅ Clases `.dark` en `<html>` o `<body>`
4. ✅ Preferencia del sistema `prefers-color-scheme: dark`

**NO necesitas hacer nada adicional**. El componente detecta y aplica los estilos automáticamente.

---

## 🔍 Funcionalidades Incluidas

✅ **Búsqueda en tiempo real**
✅ **Textos en español** (No se encontraron resultados, Buscando...)
✅ **Modo oscuro automático**
✅ **Compatibilidad con modales** (Alpine.js, Bootstrap)
✅ **Botón para limpiar selección** (X)
✅ **Soporte para select múltiple**
✅ **Reinicialización automática** al cambiar tema
✅ **Estilos Bootstrap 5**

---

## 🚨 Troubleshooting

### Problema: "Select2 no se muestra correctamente"
✅ **Solución**: Verifica que jQuery y Select2 estén cargados **antes** de `searchable-select.js`

### Problema: "El dropdown se corta dentro del modal"
✅ **Solución**: Agrega `dropdownParent="#tuModal"` al componente

### Problema: "No funciona en un modal que se abre dinámicamente"
✅ **Solución**: El script ya escucha el evento `open-modal`. Dispara el evento:
```javascript
window.dispatchEvent(new CustomEvent('open-modal', { detail: 'nombreModal' }));
```

### Problema: "El modo oscuro no se aplica"
✅ **Solución**: Verifica que `select2-dark-mode.css` esté cargado y que el script `searchable-select.js` esté incluido

---

## ✅ Checklist de Implementación

Para aplicar en **cualquier vista**:

```
□ Agregar enlaces CSS en @section('styles')
□ Agregar scripts en @section('scripts') (en orden correcto)
□ Cambiar <select> tradicional por <x-searchable-select> o agregar clase 'searchable-select'
□ Configurar valueField y labelField según tu modelo
□ Si está en modal: agregar dropdownParent
□ Probar en modo claro y oscuro
```

---

## 📂 Módulos que ya tienen Select2 implementado correctamente

### ✅ Sales (Ventas)
- `sales/ventas/create.blade.php` - Select de clientes
- `sales/ventas/edit.blade.php` - Select de clientes
- `sales/ventas/renew.blade.php` - Select de clientes

---

## 🎯 Módulos Pendientes de Implementación

### ⚠️ Employees (Empleados)
- `employees/empleados/create.blade.php` - Necesita Select2 para perfiles/roles
- `employees/empleados/edit.blade.php` - Necesita Select2 para perfiles/roles

### ⚠️ Sales (Clientes)
- `sales/clientes/create.blade.php` - Posibles selects de país, ciudad, etc.
- `sales/clientes/edit.blade.php` - Posibles selects de país, ciudad, etc.

### ⚠️ Inventory (Productos)
- `inventory/productos/create.blade.php` - Selects de categorías, proveedores, tipos
- `inventory/productos/edit.blade.php` - Selects de categorías, proveedores, tipos

### ⚠️ Inventory (Cuentas)
- `inventory/cuentas/create.blade.php` - Selects de servicios, perfiles
- `inventory/cuentas/edit.blade.php` - Selects de servicios, perfiles

### ⚠️ Otros Módulos
- Revisar cualquier formulario que contenga `<select>` y aplicar la clase `searchable-select`

---

## 📝 Notas Importantes

1. **No mezclar implementaciones**: Si ya tienes Select2 implementado de otra forma, elimina la implementación anterior antes de aplicar este componente.

2. **jQuery es obligatorio**: Select2 depende de jQuery. No funcionará sin jQuery.

3. **Orden de carga**: Respetar siempre el orden: jQuery → Select2 → searchable-select.js → tus scripts.

4. **Modales Alpine.js**: El componente ya está configurado para trabajar con modales Alpine.js mediante el evento `open-modal`.

5. **Cache de navegador**: Si no ves los cambios, limpia el cache del navegador (Ctrl + F5).

---

## 🔄 Actualización: 3 de Diciembre, 2025

- ✅ Modo oscuro totalmente funcional en Ventas
- ✅ Componente Blade creado y probado
- ✅ JavaScript con detección automática de tema
- ✅ CSS con soporte completo para modo oscuro
- ⚠️ Pendiente aplicar en otros módulos del sistema
