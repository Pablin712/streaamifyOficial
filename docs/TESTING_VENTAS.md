# Testing de Mejoras en Módulo de Ventas

## ✅ Checklist de Pruebas

### 1. Verificación de Dependencias

**Abrir cualquier vista de ventas y verificar consola del navegador:**

```javascript
// Abrir DevTools (F12) → Console y ejecutar:
typeof jQuery !== 'undefined'           // debe retornar true
typeof jQuery.fn.select2 !== 'undefined' // debe retornar true
```

**Si alguno es `false`**:
- ❌ Verificar que los scripts están en el orden correcto en `@section('scripts')`
- ❌ Verificar conectividad a CDNs de jQuery y Select2

---

### 2. Testing de Create (Crear Venta)

**URL**: `/ventas/create`

#### Test 1: Alerta Informativa
- [ ] Se muestra alerta azul con mensaje "¡Importante! Los cambios NO se guardarán hasta..."
- [ ] Alerta tiene botón de cerrar (X)
- [ ] Al cerrar desaparece correctamente

#### Test 2: Select de Clientes con Búsqueda
- [ ] El select de clientes tiene buscador activo
- [ ] Al hacer clic aparece dropdown con lista de clientes
- [ ] Al escribir en el buscador filtra resultados en tiempo real
- [ ] Tema Bootstrap 5 aplicado correctamente (colores azules)
- [ ] Placeholder "Buscar cliente..." visible
- [ ] Se puede limpiar selección con botón X

#### Test 3: Modal de Crear Cliente
- [ ] Botón "Crear Cliente" abre modal correctamente
- [ ] Modal usa Alpine.js (`open-modal` event)
- [ ] Al crear cliente exitosamente:
  - [ ] Modal se cierra automáticamente
  - [ ] Nuevo cliente aparece en el select de clientes
  - [ ] Select2 se actualiza sin necesidad de refrescar
  - [ ] Se muestra notificación toast de éxito

#### Test 4: Modal Agregar Detalle
- [ ] Botón "Agregar Detalle" abre modal verde
- [ ] Select de cuentas tiene búsqueda activa
- [ ] Al abrir modal, Select2 se inicializa automáticamente
- [ ] Dropdown de Select2 aparece DENTRO del modal (no detrás)
- [ ] Al buscar cuenta funciona correctamente
- [ ] Al guardar detalle:
  - [ ] Fila se agrega a la tabla
  - [ ] Total de venta se actualiza
  - [ ] Modal se cierra
  - [ ] Campos del modal se limpian

#### Test 5: Modal Editar Detalle
- [ ] Botón "Editar" abre modal amarillo
- [ ] Select de cuentas carga con valor actual
- [ ] Select2 funciona en modal de edición
- [ ] Al guardar cambios:
  - [ ] Fila se actualiza en tabla
  - [ ] Total recalcula correctamente
  - [ ] Modal se cierra

#### Test 6: Registro de Venta
- [ ] Al presionar "Registrar Venta" se envían todos los detalles
- [ ] Venta se crea correctamente en base de datos
- [ ] Redirecciona a listado de ventas

---

### 3. Testing de Edit (Editar Venta)

**URL**: `/ventas/{id}/edit`

#### Test 1: Alerta Informativa
- [ ] Se muestra alerta amarilla con mensaje "¡Atención! Recuerda presionar..."
- [ ] Alerta tiene ícono de exclamación
- [ ] Botón de cerrar funciona

#### Test 2: Carga de Datos
- [ ] Detalles existentes se muestran en tabla
- [ ] Total de venta se calcula correctamente
- [ ] Cliente aparece en campo readonly

#### Test 3: Modales
- [ ] Modal agregar detalle funciona igual que en create
- [ ] Modal editar detalle carga datos existentes
- [ ] Select2 funciona en ambos modales
- [ ] Estados (Activa/Vencida) se pueden cambiar con botón toggle

#### Test 4: Actualización
- [ ] Al presionar "Actualizar Venta" se guardan cambios
- [ ] Nuevos detalles se guardan
- [ ] Detalles editados se actualizan
- [ ] Total se recalcula

---

### 4. Testing de Renew (Renovar Venta)

**URL**: `/ventas/{id}/renew`

#### Test 1: Alerta Informativa
- [ ] Se muestra alerta azul con mensaje "Recordatorio: Esta renovación..."
- [ ] Estilo info (azul) aplicado correctamente

#### Test 2: Detalles Pre-cargados
- [ ] Detalles de venta anterior se cargan automáticamente
- [ ] Fechas de vencimiento se calculan correctamente (+1 mes)
- [ ] Total se muestra correctamente

#### Test 3: Funcionalidad de Modales
- [ ] Agregar detalle funciona
- [ ] Editar detalle funciona
- [ ] Select2 funciona en todos los selects
- [ ] NO hay inicialización manual de Select2 (eliminada)

#### Test 4: Registro de Renovación
- [ ] Al presionar "Registrar Venta" crea nueva venta
- [ ] Venta anterior se marca como renovada (si aplica)
- [ ] Detalles se copian con nuevas fechas

---

### 5. Testing de Consola del Navegador

**Verificar que NO aparezcan errores**:

#### Errores RESUELTOS (NO deberían aparecer):
- ❌ `Uncaught TypeError: $(...).select2 is not a function`
  - **Causa**: Select2 no cargado antes del inicializador
  - **Solución**: Orden correcto de scripts
  
- ❌ `jQuery is not defined`
  - **Causa**: jQuery no cargado
  - **Solución**: Incluir jQuery ANTES de Select2

#### Warnings Esperados (OK):
- ⚠️ Warnings de deprecation de Bootstrap (si los hay)
- ⚠️ Warnings de Alpine.js (normales)

---

### 6. Testing de Compatibilidad

#### Navegadores a probar:
- [ ] Google Chrome (última versión)
- [ ] Mozilla Firefox (última versión)
- [ ] Microsoft Edge (última versión)
- [ ] Safari (si está disponible)

#### Dispositivos:
- [ ] Desktop/Laptop (1920x1080)
- [ ] Tablet (768px ancho)
- [ ] Mobile (375px ancho)

**Nota**: Select2 debe ser responsive y funcionar en todos los tamaños de pantalla.

---

## 🔧 Troubleshooting

### Problema 1: Select2 no se inicializa

**Síntomas**:
- Select aparece como select normal de HTML
- No hay buscador
- No hay tema Bootstrap 5

**Solución**:
1. Abrir consola del navegador (F12)
2. Verificar errores JavaScript
3. Verificar orden de scripts:
   ```blade
   jQuery → Select2 → searchable-select.js → otros scripts
   ```
4. Limpiar caché del navegador (Ctrl + Shift + R)

### Problema 2: Dropdown de Select2 aparece detrás del modal

**Síntomas**:
- Al abrir select en modal, dropdown no se ve
- Dropdown aparece en posición incorrecta

**Solución**:
El archivo `searchable-select.js` ya incluye auto-detección de modales:
```javascript
const modal = $select.closest('.modal');
if (modal.length > 0) {
    config.dropdownParent = modal;
}
```
**Verificar que el modal tenga clase `modal`**.

### Problema 3: Nuevo cliente no aparece en select

**Síntomas**:
- Cliente se crea correctamente
- Pero no aparece en el select automáticamente

**Solución**:
Verificar que `ventasClienteHelper.js` esté incluido en la vista:
```blade
<script src="{{ asset('js/ventasClienteHelper.js') }}"></script>
```

Y que la función use `.trigger('change')`:
```javascript
$(select).trigger('change');
```

### Problema 4: Total de venta no se actualiza

**Síntomas**:
- Al agregar/editar/eliminar detalles, total no cambia

**Solución**:
Verificar que `actualizarTotalVenta()` esté definida en el script inline de la vista (edit.blade.php, renew.blade.php) o que se llame correctamente después de cada operación.

---

## 📊 Criterios de Aceptación

### ✅ TODAS las siguientes condiciones deben cumplirse:

1. **Modales Compartidos**:
   - [x] NO hay duplicación de código
   - [x] Modales funcionan en create, edit, renew
   - [x] Alpine.js maneja apertura/cierre

2. **Select2 Funcional**:
   - [x] Búsqueda funciona en todos los selects
   - [x] Tema Bootstrap 5 aplicado
   - [x] Textos en español
   - [x] NO hay errores en consola
   - [x] Funciona en modales

3. **Alertas Informativas**:
   - [x] Alertas visibles en create, edit, renew
   - [x] Colores correctos (info azul, warning amarillo)
   - [x] Mensajes claros y concisos
   - [x] Botón de cerrar funciona

4. **Funcionalidad Intacta**:
   - [x] Crear venta funciona igual que antes
   - [x] Editar venta funciona igual que antes
   - [x] Renovar venta funciona igual que antes
   - [x] Crear cliente desde ventas funciona
   - [x] Agregar/editar/eliminar detalles funciona
   - [x] Cálculo de totales es correcto

5. **Código Limpio**:
   - [x] NO hay código duplicado
   - [x] NO hay inicialización manual de Select2 en vistas
   - [x] Scripts en orden correcto
   - [x] Documentación actualizada

---

## 📝 Reporte de Bugs

Si encuentras algún bug durante el testing, documéntalo con el siguiente formato:

```markdown
### Bug #X: [Título descriptivo]

**Ubicación**: [URL o vista afectada]

**Pasos para reproducir**:
1. Ir a...
2. Hacer clic en...
3. Ver error...

**Comportamiento esperado**:
[Qué debería pasar]

**Comportamiento actual**:
[Qué pasa actualmente]

**Consola del navegador**:
```
[Pegar errores de consola aquí]
```

**Capturas de pantalla**:
[Adjuntar si es necesario]

**Prioridad**: Alta / Media / Baja
```

---

## 🎯 Testing Completado

Una vez completado el testing, marcar como verificado:

- [ ] Testing de Create completado sin errores
- [ ] Testing de Edit completado sin errores
- [ ] Testing de Renew completado sin errores
- [ ] Testing de consola sin errores JavaScript
- [ ] Testing en múltiples navegadores exitoso
- [ ] Testing en dispositivos móviles exitoso
- [ ] Todos los criterios de aceptación cumplidos
- [ ] Documentación revisada y actualizada

**Fecha de testing**: _________________

**Testeado por**: _________________

**Resultado**: ✅ APROBADO / ❌ REQUIERE CORRECCIONES
