# Componente Modal - Sistema Streamify

## 📋 Descripción
Componente modal reutilizable que se adapta automáticamente al sistema de temas (Default, Christmas) y al Dark Mode.

## 🎨 Características
- ✅ Compatible con todos los temas del sistema
- ✅ Soporte completo para Dark Mode
- ✅ Animaciones suaves con Alpine.js
- ✅ Diseño responsive
- ✅ Accesibilidad (teclado, focus trap)
- ✅ Estilos consistentes con Bootstrap 5

## 🚀 Uso Básico

### 1. Incluir el componente en la vista

```blade
<x-modal name="mi-modal" max-width="lg" :closeable="true">
    <div class="modal-header p-4">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-icon"></i> Título del Modal
        </h5>
        <button type="button" class="btn-close" 
                x-on:click="$dispatch('close-modal', 'mi-modal')">
        </button>
    </div>
    
    <div class="modal-body p-4">
        <!-- Contenido del modal -->
    </div>
    
    <div class="modal-footer p-4">
        <button type="button" class="btn btn-secondary" 
                x-on:click="$dispatch('close-modal', 'mi-modal')">
            Cancelar
        </button>
        <button type="submit" class="btn btn-primary">
            Guardar
        </button>
    </div>
</x-modal>
```

### 2. Abrir el modal desde JavaScript

```javascript
function openModal() {
    window.dispatchEvent(new CustomEvent('open-modal', { 
        detail: 'mi-modal' 
    }));
}
```

### 3. Cerrar el modal desde JavaScript

```javascript
function closeModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { 
        detail: 'mi-modal' 
    }));
}
```

## ⚙️ Parámetros del Componente

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `name` | string | requerido | Identificador único del modal |
| `show` | boolean | false | Si debe mostrarse al cargar |
| `max-width` | string | '2xl' | Tamaño máximo (sm, md, lg, xl, 2xl-7xl) |
| `closeable` | boolean | true | Si se puede cerrar con ESC o click fuera |

## 📐 Tamaños Disponibles

- `sm`: 384px
- `md`: 448px
- `lg`: 512px
- `xl`: 576px
- `2xl`: 672px (default)
- `3xl`: 768px
- `4xl`: 896px
- `5xl`: 1024px
- `6xl`: 1152px
- `7xl`: 1280px

## 🎯 Ejemplo Completo: CRUD con Modal

```blade
<!-- Botón para abrir modal de crear -->
<button onclick="openCreateModal()" class="btn btn-primary">
    <i class="fas fa-plus"></i> Crear
</button>

<!-- Modal Crear -->
<x-modal name="create-item" max-width="lg">
    <div class="modal-header p-4">
        <h5 class="modal-title fw-bold">Crear Nuevo Item</h5>
        <button type="button" class="btn-close" 
                x-on:click="$dispatch('close-modal', 'create-item')">
        </button>
    </div>
    <form id="create-form" onsubmit="submitCreate(event)">
        <div class="modal-body p-4">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">Nombre</label>
                <input type="text" name="name" id="name" 
                       class="form-control" required>
            </div>
        </div>
        <div class="modal-footer p-4">
            <button type="button" class="btn btn-secondary" 
                    x-on:click="$dispatch('close-modal', 'create-item')">
                Cancelar
            </button>
            <button type="submit" class="btn btn-success">
                Guardar
            </button>
        </div>
    </form>
</x-modal>

<script>
function openCreateModal() {
    document.getElementById('create-form').reset();
    window.dispatchEvent(new CustomEvent('open-modal', { 
        detail: 'create-item' 
    }));
}

function submitCreate(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    fetch('/api/items', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            name: formData.get('name')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.dispatchEvent(new CustomEvent('close-modal', { 
                detail: 'create-item' 
            }));
            location.reload();
        }
    });
}
</script>
```

## 🎨 Personalización de Estilos

El modal hereda automáticamente las variables CSS del tema activo:

```css
/* Personalizar colores del modal en un tema específico */
[data-theme="mi-tema"] .modal-header {
    background: var(--primary-gradient);
    color: #ffffff;
}
```

## 🌙 Dark Mode

El modal se adapta automáticamente cuando Dark Mode está activo:

```css
[data-dark-mode="true"] .modal-content {
    background-color: var(--bg-card);
    color: var(--text-primary);
}
```

## 📱 Responsive

El modal es completamente responsive y se adapta a dispositivos móviles:

- Desktop: Ancho máximo según parámetro
- Tablet: Ancho ajustado automáticamente
- Mobile: Ancho completo con márgenes reducidos

## ♿ Accesibilidad

- Navegación por teclado (Tab/Shift+Tab)
- Cierre con tecla ESC
- Focus trap (el foco permanece dentro del modal)
- ARIA labels para lectores de pantalla
- Orden lógico de tabulación

## 🔧 Integración con Controladores

### Controlador AJAX

```php
public function store(Request $request)
{
    try {
        $item = Item::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Item creado exitosamente.',
            'item' => $item
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 422);
    }
}
```

## 📦 Archivos Incluidos

1. **Componente**: `resources/views/components/modal.blade.php`
2. **Estilos**: `public/css/modal-system.css`
3. **Dependencia**: Alpine.js (incluido en layout principal)

## 🎓 Ejemplo de Implementación: Mantenimientos

Ver `resources/views/inventory/mantenimientos/index.blade.php` para una implementación completa con:
- Modal de crear
- Modal de editar
- Eliminación con confirmación
- Manejo de errores
- Alertas dinámicas

## 🐛 Troubleshooting

### El modal no se abre
- Verificar que Alpine.js esté cargado
- Verificar que el `name` del modal coincida en `open-modal`
- Revisar consola del navegador

### Los estilos no se aplican
- Verificar que `modal-system.css` esté incluido en el layout
- Verificar que `themes.css` esté cargado antes
- Limpiar caché del navegador

### El formulario no envía
- Verificar la ruta del controlador
- Verificar el token CSRF
- Revisar validaciones en el controlador
