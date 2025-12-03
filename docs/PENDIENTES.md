# 📋 Pendientes del Sistema - Streamify

## 🎨 Mejoras de UI/UX

### 1. Paginación de Tablas
- **Estado**: ❌ Pendiente
- **Prioridad**: Alta
- **Descripción**: La paginación de las tablas está bien aplicada solo en el módulo de **Ventas**. Se necesita estandarizar el diseño y funcionalidad en todas las vistas del sistema.
- **Afectado**:
  - Módulo de Clientes
  - Módulo de Empleados
  - Módulo de Cuentas
  - Módulo de Productos
  - Módulo de Proveedores
  - Módulo de Servicios
  - Otros módulos con tablas
- **Solución propuesta**:
  - Crear componente Blade reutilizable para paginación
  - Aplicar estilos consistentes (Bootstrap 5)
  - Verificar responsive design
  - Agregar indicadores de página actual
  - Implementar "ir a página X"

---

### 2. Modo Oscuro - Elementos Faltantes
- **Estado**: ⚠️ En progreso
- **Prioridad**: Media
- **Descripción**: Varios elementos del sistema aún no tienen implementado el modo oscuro correctamente.

#### 2.1. Selects (Select2)
- **Estado**: ✅ Completado en Ventas
- **Pendiente**: Aplicar en otros módulos
- **Elementos afectados**:
  - Selects en formularios de Clientes
  - Selects en formularios de Empleados
  - Selects en formularios de Productos
  - Selects en filtros de búsqueda
  - Selects en modales de otros módulos

#### 2.2. Paginación de Tablas
- **Estado**: ❌ Pendiente
- **Descripción**: Los controles de paginación (botones anterior/siguiente, números de página) necesitan estilos para modo oscuro
- **Archivos a modificar**:
  - `public/css/select2-dark-mode.css` (agregar sección de paginación)
  - Componente de paginación de Laravel

#### 2.3. Otros Elementos
- **Pendiente**:
  - Tablas en modo oscuro (headers, filas, hover)
  - Modales (fondos, bordes, texto)
  - Botones (estados hover, active, disabled)
  - Formularios (inputs, textareas, checkboxes, radios)
  - Alertas y notificaciones
  - Cards/paneles
  - Sidebar y Navbar (ver punto 5)

---

### 3. Componente Select2 Global
- **Estado**: ✅ Completado en Ventas
- **Prioridad**: Alta
- **Descripción**: El componente `searchable-select` con Select2 está funcionando en el módulo de Ventas. Necesita aplicarse en todo el sistema de empleados.

#### 3.1. Módulo de Empleados - Aplicar Select2
- **Vistas a actualizar**:
  - `resources/views/employees/empleados/create.blade.php`
  - `resources/views/employees/empleados/edit.blade.php`
  - Cualquier formulario con selects en módulo de empleados

#### 3.2. Otros Módulos
- **Pendiente**:
  - Módulo de Clientes (selects de país, ciudad, etc.)
  - Módulo de Productos (categorías, proveedores)
  - Módulo de Cuentas (servicios, perfiles)
  - Módulo de Servicios
  - Filtros de búsqueda en listados

#### 3.3. Pasos para Implementar
1. Incluir CSS de Select2 en `@section('styles')`
2. Incluir JS de Select2 en `@section('scripts')`
3. Agregar clase `searchable-select` a los `<select>`
4. O usar componente `<x-searchable-select>`
5. Verificar funcionamiento en modales

---

### 4. Dashboard - Tabla de Estadísticas
- **Estado**: ⚠️ Incompleto
- **Prioridad**: Media
- **Descripción**: La tabla de estadísticas en el Dashboard solo muestra datos de algunos servicios. Faltan estadísticas de otros servicios del sistema.

#### 4.1. Servicios Faltantes
- **Pendiente**:
  - Estadísticas de servicio X
  - Estadísticas de servicio Y
  - Estadísticas de servicio Z
  - *(Definir cuáles servicios específicos)*

#### 4.2. Datos a Mostrar
- [ ] Ventas por servicio
- [ ] Clientes activos por servicio
- [ ] Ingresos mensuales por servicio
- [ ] Cuentas/Perfiles activos por servicio
- [ ] Renovaciones pendientes
- [ ] Vencimientos próximos

#### 4.3. Mejoras Adicionales
- [ ] Gráficos visuales (Chart.js o similar)
- [ ] Exportar a Excel/PDF
- [ ] Filtros por fecha
- [ ] Comparativa mes actual vs mes anterior

#### 4.4. Archivos a Modificar
- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard.blade.php`
- Modelos relacionados (agregar métodos para estadísticas)

---

### 5. Sidebar y Navbar - Diseño Responsive
- **Estado**: ❌ Incorrecto
- **Prioridad**: Alta
- **Descripción**: El diseño responsive del Sidebar y Navbar está incorrecto. Problemas en dispositivos móviles y tablets.

#### 5.1. Problemas Identificados
- **Sidebar**:
  - No se colapsa correctamente en móviles
  - Menú hamburguesa no funciona bien
  - Overflow horizontal en pantallas pequeñas
  - Items de menú se solapan
  - Z-index incorrecto (se superpone con otros elementos)

- **Navbar**:
  - Elementos de usuario/perfil no se adaptan
  - Dropdown no funciona en móvil
  - Logo se deforma
  - Búsqueda se sale del contenedor

#### 5.2. Breakpoints a Verificar
- [ ] **XS (< 576px)**: Móviles pequeños
- [ ] **SM (576px - 767px)**: Móviles grandes
- [ ] **MD (768px - 991px)**: Tablets
- [ ] **LG (992px - 1199px)**: Laptops
- [ ] **XL (≥ 1200px)**: Desktops

#### 5.3. Soluciones Propuestas
- Implementar sidebar colapsable con animación suave
- Usar offcanvas de Bootstrap 5 para menú móvil
- Ajustar espaciados y tamaños de fuente
- Implementar media queries específicas
- Mejorar UX del toggle (botón hamburguesa más visible)

#### 5.4. Archivos a Modificar
- `resources/views/layouts/navigation.blade.php`
- `resources/views/layouts/static.blade.php`
- `public/css/sidebar.css` (si existe)
- `public/js/sidebar.js` (si existe)

---

## 🚀 Nuevas Funcionalidades

### 6. Toggle de Tema Claro/Oscuro Manual
- **Estado**: ❌ No implementado
- **Prioridad**: Baja
- **Descripción**: Actualmente el modo oscuro está forzado. Implementar toggle para que el usuario elija.
- **Elementos necesarios**:
  - Botón en navbar para cambiar tema
  - Guardar preferencia en localStorage
  - Ícono de sol/luna
  - Transición suave entre temas
  - Persistencia entre sesiones

---

## 🔧 Tareas Técnicas

### 7. Optimización de Performance
- **Estado**: ❌ Pendiente
- **Prioridad**: Baja
- **Tareas**:
  - Minificar CSS y JS
  - Implementar lazy loading en imágenes
  - Optimizar queries de base de datos (N+1)
  - Implementar caché de vistas
  - CDN para assets estáticos

### 8. Documentación
- **Estado**: ⚠️ Parcial
- **Prioridad**: Media
- **Pendiente**:
  - Documentar API endpoints (si existen)
  - Guía de instalación completa
  - Manual de usuario
  - Documentación de componentes Blade
  - Diagramas de base de datos

---

## 📊 Resumen por Prioridad

### 🔴 Prioridad Alta (3 items)
1. Paginación de tablas en todas las vistas
2. Aplicar Select2 en módulo de empleados
3. Arreglar diseño responsive de Sidebar y Navbar

### 🟡 Prioridad Media (3 items)
1. Modo oscuro en elementos faltantes
2. Completar estadísticas de Dashboard
3. Documentación del sistema

### 🟢 Prioridad Baja (2 items)
1. Toggle manual de tema claro/oscuro
2. Optimización de performance

---

## 📅 Plan de Trabajo Sugerido

### Sprint 1 (1-2 semanas)
- [ ] Arreglar Sidebar y Navbar responsive
- [ ] Estandarizar paginación en todas las vistas
- [ ] Aplicar Select2 en módulo de empleados

### Sprint 2 (1 semana)
- [ ] Implementar modo oscuro en paginación
- [ ] Implementar modo oscuro en tablas y formularios
- [ ] Completar estadísticas de Dashboard

### Sprint 3 (1 semana)
- [ ] Aplicar Select2 en módulos restantes
- [ ] Implementar toggle de tema claro/oscuro
- [ ] Documentación básica

---

## 📝 Notas Adicionales

### Archivos Importantes Creados
- `public/js/searchable-select.js` - Inicializador de Select2 con modo oscuro
- `public/css/select2-dark-mode.css` - Estilos de modo oscuro para Select2
- `resources/views/components/searchable-select.blade.php` - Componente reutilizable
- `resources/views/shared/modals/` - Modales compartidos de ventas

### Comandos Útiles
```bash
# Limpiar caché de vistas
php artisan view:clear

# Limpiar caché de configuración
php artisan config:clear

# Compilar assets (si usa Laravel Mix/Vite)
npm run dev
npm run build

# Actualizar composer
composer update
```

### Recursos
- Select2 Docs: https://select2.org/
- Bootstrap 5 Docs: https://getbootstrap.com/docs/5.3/
- Alpine.js Docs: https://alpinejs.dev/
- Laravel Docs: https://laravel.com/docs

---

**Última actualización**: 2 de Diciembre, 2025  
**Mantenido por**: Equipo de Desarrollo
