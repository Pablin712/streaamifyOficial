# 📡 API REST COMPLETA - CHECKLIST DE IMPLEMENTACIÓN

**Fecha**: Diciembre 4, 2025  
**Versión**: 6.1  
**Branch**: version-6.1.chat  

---

## 📋 ÍNDICE

1. [Modelos del Sistema](#-modelos-del-sistema)
2. [Estructura de Permisos](#-estructura-de-permisos)
3. [Endpoints por Módulo](#-endpoints-por-módulo)
4. [Plan de Implementación](#-plan-de-implementación)
5. [Controladores a Crear](#-controladores-a-crear)
6. [Testing y Validación](#-testing-y-validación)

---

## 🗂️ MODELOS DEL SISTEMA

### Modelos Existentes Identificados

**Sales (Ventas)**
- [ ] `Cliente` - Clientes del sistema
- [ ] `Venta` - Ventas realizadas
- [ ] `Pedido` - Pedidos de clientes
- [ ] `Recarga` - Recargas de servicios
- [ ] `DetalleVenta` - Detalles de ventas

**Inventory (Inventario)**
- [ ] `Producto` - Productos/servicios disponibles
- [ ] `Cuenta` - Cuentas de streaming
- [ ] `Valor` - Valores/perfiles de cuentas
- [ ] `Servicio` - Servicios (Netflix, Spotify, etc.)
- [ ] `Proveedor` - Proveedores de cuentas
- [ ] `Categoria` - Categorías de productos
- [ ] `TipoProducto` - Tipos de productos
- [ ] `Mantenimiento` - Mantenimientos de cuentas

**Finance (Finanzas)**
- [ ] `Gasto` - Gastos del sistema
- [ ] `TipoGasto` - Tipos de gastos
- [ ] `Costo` - Costos por cuenta
- [ ] `Contabilidad` - Registros contables
- [ ] `Banco` - Bancos para transacciones

**Employee (Empleados)**
- [ ] `Empleado` - Empleados del sistema
- [ ] `Asistencia` - Asistencias de empleados
- [ ] `Tarea` - Tareas asignadas

**System (Sistema)**
- [ ] `User` - Usuarios del sistema
- [ ] `Rol` - Roles (usando Spatie)
- [ ] `Permiso` - Permisos (usando Spatie)
- [ ] `Perfil` - Perfiles de usuario
- [ ] `Historial` - Historial de actividades
- [ ] `Mail` - Correos del sistema

**Chat (Mensajería)** ✅ YA IMPLEMENTADO
- [x] `Conversacion` - Conversaciones de chat
- [x] `Mensaje` - Mensajes de chat

---

## 🔐 ESTRUCTURA DE PERMISOS

### Permisos por Módulo (Spatie Permissions)

#### 1. Clientes
```php
'clientes.ver'      // GET /api/v1/clientes
'clientes.crear'    // POST /api/v1/clientes
'clientes.editar'   // PUT /api/v1/clientes/{id}
'clientes.eliminar' // DELETE /api/v1/clientes/{id}
```

#### 2. Ventas
```php
'ventas.ver'        // GET /api/v1/ventas
'ventas.crear'      // POST /api/v1/ventas
'ventas.editar'     // PUT /api/v1/ventas/{id}
'ventas.eliminar'   // DELETE /api/v1/ventas/{id}
'ventas.renovar'    // POST /api/v1/ventas/{id}/renovar
```

#### 3. Cuentas
```php
'cuentas.ver'       // GET /api/v1/cuentas
'cuentas.crear'     // POST /api/v1/cuentas
'cuentas.editar'    // PUT /api/v1/cuentas/{id}
'cuentas.eliminar'  // DELETE /api/v1/cuentas/{id}
'cuentas.asignar'   // POST /api/v1/cuentas/{id}/asignar
```

#### 4. Productos
```php
'productos.ver'
'productos.crear'
'productos.editar'
'productos.eliminar'
```

#### 5. Empleados
```php
'empleados.ver'
'empleados.crear'
'empleados.editar'
'empleados.eliminar'
'empleados.asistencias'
```

#### 6. Gastos
```php
'gastos.ver'
'gastos.crear'
'gastos.editar'
'gastos.eliminar'
```

#### 7. Usuarios
```php
'usuarios.ver'
'usuarios.crear'
'usuarios.editar'
'usuarios.eliminar'
'usuarios.roles'
'usuarios.permisos'
```

#### 8. Servicios
```php
'servicios.ver'
'servicios.estadisticas'
```

#### 9. Proveedores
```php
'proveedores.ver'
'proveedores.crear'
'proveedores.editar'
'proveedores.eliminar'
```

#### 10. Dashboard/Estadísticas
```php
'dashboard.ver'
'dashboard.estadisticas'
'dashboard.reportes'
```

---

## 🌐 ENDPOINTS POR MÓDULO

### 📊 1. CLIENTES

**Estado**: ⚠️ Parcialmente implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/clientes` | Listar clientes | `clientes.ver` | ✅ Hecho |
| GET | `/api/v1/clientes/{id}` | Ver cliente | `clientes.ver` | ✅ Hecho |
| POST | `/api/v1/clientes` | Crear cliente | `clientes.crear` | ✅ Hecho |
| PUT | `/api/v1/clientes/{id}` | Actualizar cliente | `clientes.editar` | ✅ Hecho |
| DELETE | `/api/v1/clientes/{id}` | Eliminar cliente | `clientes.eliminar` | ✅ Hecho |
| GET | `/api/v1/clientes/{id}/ventas` | Ventas del cliente | `clientes.ver` | ✅ Hecho |
| GET | `/api/v1/clientes/{id}/pedidos` | Pedidos del cliente | `clientes.ver` | ❌ Pendiente |
| GET | `/api/v1/clientes/{id}/estadisticas` | Estadísticas cliente | `clientes.ver` | ❌ Pendiente |

**Controlador**: `app/Http/Controllers/Api/V1/ClienteApiController.php` ✅ Existe

**Tareas**:
- [ ] Agregar método `pedidos()` en ClienteApiController
- [ ] Agregar método `estadisticas()` en ClienteApiController
- [ ] Implementar validación con permisos Spatie
- [ ] Agregar paginación configurable
- [ ] Documentar endpoints

---

### 💰 2. VENTAS

**Estado**: ✅ **IMPLEMENTADO**

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/ventas` | Listar ventas | `ventas.ver` | ✅ **Hecho** |
| GET | `/api/v1/ventas/{id}` | Ver venta | `ventas.ver` | ✅ **Hecho** |
| POST | `/api/v1/ventas` | Crear venta | `ventas.crear` | ✅ **Hecho** |
| PUT | `/api/v1/ventas/{id}` | Actualizar venta | `ventas.editar` | ✅ **Hecho** |
| DELETE | `/api/v1/ventas/{id}` | Eliminar venta | `ventas.eliminar` | ✅ **Hecho** |
| POST | `/api/v1/ventas/{id}/renovar` | Renovar venta | `ventas.renovar` | ✅ **Hecho** |
| GET | `/api/v1/ventas/{id}/detalles` | Detalles de venta | `ventas.ver` | ✅ **Hecho** |
| GET | `/api/v1/ventas-estadisticas` | Estadísticas ventas | `ventas.ver` | ✅ **Hecho** |

**Controlador**: `app/Http/Controllers/Api/V1/VentaApiController.php` ✅ **Creado**

**Relaciones incluidas**:
```php
// GET /api/v1/ventas/{id}
return Venta::with([
    'cliente',
    'empleado',
    'detalles_venta.perfil.cuenta.valor.servicio',
    'usuarios'
])->find($id);
```

**Tareas**:
- [x] Crear VentaApiController
- [x] Implementar método `index()` con filtros (fecha, cliente, empleado, búsqueda)
- [x] Implementar método `show()` con relaciones completas
- [x] Implementar método `store()` con validación de detalles
- [x] Implementar método `update()`
- [x] Implementar método `destroy()` con validación de detalles activos
- [x] Implementar método `renovar()` - crear nueva venta basada en anterior
- [x] Implementar método `detalles()` - obtener detalles con estado y días restantes
- [x] Implementar método `estadisticas()` - totales, promedios, top clientes, ventas por día
- [x] Agregar rutas en `routes/api.php`
- [ ] Agregar middleware de permisos Spatie (comentado, listo para activar)
- [ ] Crear tests en `tests/Feature/Api/VentaApiTest.php`
- [ ] Agregar a colección Postman

**Características implementadas**:
- ✅ Creación de ventas con múltiples detalles (transaccional)
- ✅ Validación de clientes, empleados y perfiles
- ✅ Renovación automática de ventas con cálculo de fechas
- ✅ Estadísticas completas: total ventas, ingresos, promedios, top clientes, ventas por empleado
- ✅ Detalles con estados dinámicos (Activo/Inactivo/Vencido)
- ✅ Cálculo de días restantes hasta vencimiento
- ✅ Filtros avanzados: cliente, empleado, rango de fechas, búsqueda
- ✅ Paginación y ordenamiento configurable
- ✅ Manejo de transacciones DB con rollback
- ✅ Validación antes de eliminar (no elimina si tiene detalles activos)

---

### 📦 3. CUENTAS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/cuentas` | Listar cuentas | `cuentas.ver` | ❌ Pendiente |
| GET | `/api/v1/cuentas/{id}` | Ver cuenta | `cuentas.ver` | ❌ Pendiente |
| POST | `/api/v1/cuentas` | Crear cuenta | `cuentas.crear` | ❌ Pendiente |
| PUT | `/api/v1/cuentas/{id}` | Actualizar cuenta | `cuentas.editar` | ❌ Pendiente |
| DELETE | `/api/v1/cuentas/{id}` | Eliminar cuenta | `cuentas.eliminar` | ❌ Pendiente |
| GET | `/api/v1/cuentas/disponibles` | Cuentas disponibles | `cuentas.ver` | ❌ Pendiente |
| GET | `/api/v1/cuentas/{id}/valores` | Valores de cuenta | `cuentas.ver` | ❌ Pendiente |
| POST | `/api/v1/cuentas/{id}/asignar` | Asignar cuenta | `cuentas.asignar` | ❌ Pendiente |
| GET | `/api/v1/cuentas/estados` | Cuentas por estado | `cuentas.ver` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/CuentaApiController.php`

**Relaciones a incluir**:
```php
return Cuenta::with([
    'valor.servicio',
    'valor.proveedor',
    'usuarios',
    'mantenimientos'
])->find($id);
```

**Tareas**:
- [ ] Crear CuentaApiController
- [ ] Método `index()` con filtros (servicio, estado, proveedor)
- [ ] Método `show()` con relaciones completas
- [ ] Método `store()` con validación de unicidad
- [ ] Método `update()` con cambio de estado
- [ ] Método `destroy()` con validación (no eliminar si tiene usuarios activos)
- [ ] Método `disponibles()` - solo cuentas sin asignar
- [ ] Método `valores()` - obtener todos los valores de una cuenta
- [ ] Método `asignar()` - asignar cuenta a cliente
- [ ] Método `estadosCuentas()` - agrupar por estado
- [ ] Agregar middleware de permisos

---

### 🛍️ 4. PRODUCTOS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/productos` | Listar productos | `productos.ver` | ❌ Pendiente |
| GET | `/api/v1/productos/{id}` | Ver producto | `productos.ver` | ❌ Pendiente |
| POST | `/api/v1/productos` | Crear producto | `productos.crear` | ❌ Pendiente |
| PUT | `/api/v1/productos/{id}` | Actualizar producto | `productos.editar` | ❌ Pendiente |
| DELETE | `/api/v1/productos/{id}` | Eliminar producto | `productos.eliminar` | ❌ Pendiente |
| GET | `/api/v1/productos/categorias` | Listar categorías | `productos.ver` | ❌ Pendiente |
| GET | `/api/v1/productos/tipos` | Listar tipos | `productos.ver` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/ProductoApiController.php`

**Tareas**:
- [ ] Crear ProductoApiController
- [ ] CRUD completo con validación
- [ ] Método `categorias()` - listar categorías
- [ ] Método `tipos()` - listar tipos de productos
- [ ] Filtros por categoría, tipo, servicio, activo/inactivo
- [ ] Agregar middleware de permisos

---

### 👥 5. EMPLEADOS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/empleados` | Listar empleados | `empleados.ver` | ❌ Pendiente |
| GET | `/api/v1/empleados/{id}` | Ver empleado | `empleados.ver` | ❌ Pendiente |
| POST | `/api/v1/empleados` | Crear empleado | `empleados.crear` | ❌ Pendiente |
| PUT | `/api/v1/empleados/{id}` | Actualizar empleado | `empleados.editar` | ❌ Pendiente |
| DELETE | `/api/v1/empleados/{id}` | Eliminar empleado | `empleados.eliminar` | ❌ Pendiente |
| GET | `/api/v1/empleados/{id}/asistencias` | Asistencias | `empleados.asistencias` | ❌ Pendiente |
| POST | `/api/v1/empleados/{id}/asistencias` | Registrar asistencia | `empleados.asistencias` | ❌ Pendiente |
| GET | `/api/v1/empleados/{id}/tareas` | Tareas asignadas | `empleados.ver` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/EmpleadoApiController.php`

**Tareas**:
- [ ] Crear EmpleadoApiController
- [ ] CRUD completo
- [ ] Método `asistencias()` - listar asistencias del empleado
- [ ] Método `registrarAsistencia()` - crear asistencia
- [ ] Método `tareas()` - tareas asignadas
- [ ] Relaciones con usuario y perfil
- [ ] Agregar middleware de permisos

---

### 💸 6. GASTOS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/gastos` | Listar gastos | `gastos.ver` | ❌ Pendiente |
| GET | `/api/v1/gastos/{id}` | Ver gasto | `gastos.ver` | ❌ Pendiente |
| POST | `/api/v1/gastos` | Crear gasto | `gastos.crear` | ❌ Pendiente |
| PUT | `/api/v1/gastos/{id}` | Actualizar gasto | `gastos.editar` | ❌ Pendiente |
| DELETE | `/api/v1/gastos/{id}` | Eliminar gasto | `gastos.eliminar` | ❌ Pendiente |
| GET | `/api/v1/gastos/tipos` | Tipos de gastos | `gastos.ver` | ❌ Pendiente |
| GET | `/api/v1/gastos/estadisticas` | Estadísticas gastos | `gastos.ver` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/GastoApiController.php`

**Tareas**:
- [ ] Crear GastoApiController
- [ ] CRUD completo
- [ ] Método `tipos()` - listar tipos de gastos
- [ ] Método `estadisticas()` - totales por tipo, mes, etc.
- [ ] Filtros por tipo, fecha, rango de montos
- [ ] Agregar middleware de permisos

---

### 👤 7. USUARIOS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/usuarios` | Listar usuarios | `usuarios.ver` | ❌ Pendiente |
| GET | `/api/v1/usuarios/{id}` | Ver usuario | `usuarios.ver` | ❌ Pendiente |
| POST | `/api/v1/usuarios` | Crear usuario | `usuarios.crear` | ❌ Pendiente |
| PUT | `/api/v1/usuarios/{id}` | Actualizar usuario | `usuarios.editar` | ❌ Pendiente |
| DELETE | `/api/v1/usuarios/{id}` | Eliminar usuario | `usuarios.eliminar` | ❌ Pendiente |
| GET | `/api/v1/usuarios/{id}/roles` | Roles de usuario | `usuarios.roles` | ❌ Pendiente |
| POST | `/api/v1/usuarios/{id}/roles` | Asignar rol | `usuarios.roles` | ❌ Pendiente |
| GET | `/api/v1/usuarios/{id}/permisos` | Permisos | `usuarios.permisos` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/UserApiController.php`

**Tareas**:
- [ ] Crear UserApiController
- [ ] CRUD completo con hash de password
- [ ] Método `roles()` - obtener roles del usuario (Spatie)
- [ ] Método `asignarRol()` - asignar/remover rol
- [ ] Método `permisos()` - listar permisos efectivos
- [ ] Relación con empleado/perfil
- [ ] Agregar middleware de permisos

---

### 🏢 8. PROVEEDORES

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/proveedores` | Listar proveedores | `proveedores.ver` | ❌ Pendiente |
| GET | `/api/v1/proveedores/{id}` | Ver proveedor | `proveedores.ver` | ❌ Pendiente |
| POST | `/api/v1/proveedores` | Crear proveedor | `proveedores.crear` | ❌ Pendiente |
| PUT | `/api/v1/proveedores/{id}` | Actualizar proveedor | `proveedores.editar` | ❌ Pendiente |
| DELETE | `/api/v1/proveedores/{id}` | Eliminar proveedor | `proveedores.eliminar` | ❌ Pendiente |
| GET | `/api/v1/proveedores/{id}/cuentas` | Cuentas proveedor | `proveedores.ver` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/ProveedorApiController.php`

**Tareas**:
- [ ] Crear ProveedorApiController
- [ ] CRUD completo
- [ ] Método `cuentas()` - cuentas del proveedor
- [ ] Agregar middleware de permisos

---

### 🎯 9. SERVICIOS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/servicios` | Listar servicios | `servicios.ver` | ❌ Pendiente |
| GET | `/api/v1/servicios/{id}` | Ver servicio | `servicios.ver` | ❌ Pendiente |
| GET | `/api/v1/servicios/{id}/estadisticas` | Estadísticas servicio | `servicios.ver` | ❌ Pendiente |
| GET | `/api/v1/servicios/{id}/cuentas` | Cuentas del servicio | `servicios.ver` | ❌ Pendiente |
| GET | `/api/v1/servicios/{id}/productos` | Productos servicio | `servicios.ver` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/ServicioApiController.php`

**Tareas**:
- [ ] Crear ServicioApiController
- [ ] Método `index()` - listar servicios
- [ ] Método `show()` - ver servicio
- [ ] Método `estadisticas()` - cuentas, usuarios, ingresos, etc.
- [ ] Método `cuentas()` - cuentas del servicio
- [ ] Método `productos()` - productos del servicio
- [ ] Agregar middleware de permisos

---

### 📊 10. DASHBOARD/ESTADÍSTICAS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/dashboard` | Dashboard general | `dashboard.ver` | ❌ Pendiente |
| GET | `/api/v1/dashboard/ventas` | Estadísticas ventas | `dashboard.estadisticas` | ❌ Pendiente |
| GET | `/api/v1/dashboard/cuentas` | Estadísticas cuentas | `dashboard.estadisticas` | ❌ Pendiente |
| GET | `/api/v1/dashboard/finanzas` | Estadísticas finanzas | `dashboard.estadisticas` | ❌ Pendiente |
| GET | `/api/v1/dashboard/reporte` | Generar reporte | `dashboard.reportes` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/DashboardApiController.php`

**Tareas**:
- [ ] Crear DashboardApiController
- [ ] Método `index()` - resumen general (tarjetas del dashboard)
- [ ] Método `estadisticasVentas()` - totales, gráficos
- [ ] Método `estadisticasCuentas()` - por estado, servicio
- [ ] Método `estadisticasFinanzas()` - ingresos, gastos, ganancias
- [ ] Método `generarReporte()` - exportar datos en JSON/CSV
- [ ] Agregar middleware de permisos

---

### 📝 11. PEDIDOS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/pedidos` | Listar pedidos | `pedidos.ver` | ❌ Pendiente |
| GET | `/api/v1/pedidos/{id}` | Ver pedido | `pedidos.ver` | ❌ Pendiente |
| POST | `/api/v1/pedidos` | Crear pedido | `pedidos.crear` | ❌ Pendiente |
| PUT | `/api/v1/pedidos/{id}` | Actualizar pedido | `pedidos.editar` | ❌ Pendiente |
| DELETE | `/api/v1/pedidos/{id}` | Eliminar pedido | `pedidos.eliminar` | ❌ Pendiente |
| PUT | `/api/v1/pedidos/{id}/estado` | Cambiar estado | `pedidos.editar` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/PedidoApiController.php`

---

### 🔄 12. RECARGAS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/recargas` | Listar recargas | `recargas.ver` | ❌ Pendiente |
| GET | `/api/v1/recargas/{id}` | Ver recarga | `recargas.ver` | ❌ Pendiente |
| POST | `/api/v1/recargas` | Crear recarga | `recargas.crear` | ❌ Pendiente |
| PUT | `/api/v1/recargas/{id}/estado` | Cambiar estado | `recargas.editar` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/RecargaApiController.php`

---

### 🔨 13. MANTENIMIENTOS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/mantenimientos` | Listar mantenimientos | `mantenimientos.ver` | ❌ Pendiente |
| GET | `/api/v1/mantenimientos/{id}` | Ver mantenimiento | `mantenimientos.ver` | ❌ Pendiente |
| POST | `/api/v1/mantenimientos` | Crear mantenimiento | `mantenimientos.crear` | ❌ Pendiente |
| PUT | `/api/v1/mantenimientos/{id}` | Actualizar mantenimiento | `mantenimientos.editar` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/MantenimientoApiController.php`

---

### 📋 14. TAREAS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/tareas` | Listar tareas | `tareas.ver` | ❌ Pendiente |
| GET | `/api/v1/tareas/{id}` | Ver tarea | `tareas.ver` | ❌ Pendiente |
| POST | `/api/v1/tareas` | Crear tarea | `tareas.crear` | ❌ Pendiente |
| PUT | `/api/v1/tareas/{id}` | Actualizar tarea | `tareas.editar` | ❌ Pendiente |
| PUT | `/api/v1/tareas/{id}/completar` | Completar tarea | `tareas.editar` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/TareaApiController.php`

---

### 📅 15. ASISTENCIAS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/asistencias` | Listar asistencias | `asistencias.ver` | ❌ Pendiente |
| POST | `/api/v1/asistencias` | Registrar asistencia | `asistencias.crear` | ❌ Pendiente |
| GET | `/api/v1/asistencias/reporte` | Reporte mensual | `asistencias.reportes` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/AsistenciaApiController.php`

---

### 📊 16. VALORES (Perfiles de Cuentas)

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/valores` | Listar valores | `valores.ver` | ❌ Pendiente |
| GET | `/api/v1/valores/{id}` | Ver valor | `valores.ver` | ❌ Pendiente |
| POST | `/api/v1/valores` | Crear valor | `valores.crear` | ❌ Pendiente |
| PUT | `/api/v1/valores/{id}` | Actualizar valor | `valores.editar` | ❌ Pendiente |
| DELETE | `/api/v1/valores/{id}` | Eliminar valor | `valores.eliminar` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/ValorApiController.php`

---

### 🏦 17. CONTABILIDAD

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/contabilidad` | Listar registros | `contabilidad.ver` | ❌ Pendiente |
| GET | `/api/v1/contabilidad/balance` | Balance general | `contabilidad.ver` | ❌ Pendiente |
| GET | `/api/v1/contabilidad/flujo` | Flujo de caja | `contabilidad.ver` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/ContabilidadApiController.php`

---

### 🏛️ 18. ROLES Y PERMISOS

**Estado**: ❌ No implementado

| Método | Endpoint | Descripción | Permiso | Estado |
|--------|----------|-------------|---------|--------|
| GET | `/api/v1/roles` | Listar roles | `roles.ver` | ❌ Pendiente |
| GET | `/api/v1/roles/{id}` | Ver rol | `roles.ver` | ❌ Pendiente |
| POST | `/api/v1/roles` | Crear rol | `roles.crear` | ❌ Pendiente |
| PUT | `/api/v1/roles/{id}` | Actualizar rol | `roles.editar` | ❌ Pendiente |
| DELETE | `/api/v1/roles/{id}` | Eliminar rol | `roles.eliminar` | ❌ Pendiente |
| GET | `/api/v1/roles/{id}/permisos` | Permisos del rol | `roles.ver` | ❌ Pendiente |
| POST | `/api/v1/roles/{id}/permisos` | Asignar permisos | `roles.editar` | ❌ Pendiente |
| GET | `/api/v1/permisos` | Listar permisos | `permisos.ver` | ❌ Pendiente |

**Controlador a crear**: `app/Http/Controllers/Api/V1/RolApiController.php`

---

## 🚀 PLAN DE IMPLEMENTACIÓN

### Fase 1: Core Business (Semana 1)
**Prioridad: CRÍTICA**

#### Día 1-2: Ventas
- [ ] Crear `VentaApiController`
- [ ] Implementar CRUD completo
- [ ] Método `renovar()`
- [ ] Método `estadisticas()`
- [ ] Agregar tests con Postman

#### Día 3: Productos
- [ ] Crear `ProductoApiController`
- [ ] Implementar CRUD completo
- [ ] Métodos `categorias()` y `tipos()`
- [ ] Tests

#### Día 4-5: Cuentas
- [ ] Crear `CuentaApiController`
- [ ] Implementar CRUD completo
- [ ] Método `disponibles()`
- [ ] Método `asignar()`
- [ ] Método `estadosCuentas()`
- [ ] Tests completos

---

### Fase 2: Finanzas y Empleados (Semana 2)
**Prioridad: ALTA**

#### Día 1: Gastos
- [ ] Crear `GastoApiController`
- [ ] CRUD + método `tipos()`
- [ ] Método `estadisticas()`
- [ ] Tests

#### Día 2-3: Empleados
- [ ] Crear `EmpleadoApiController`
- [ ] CRUD completo
- [ ] Método `asistencias()`
- [ ] Método `tareas()`
- [ ] Tests

#### Día 4: Pedidos y Recargas
- [ ] Crear `PedidoApiController`
- [ ] Crear `RecargaApiController`
- [ ] CRUD básico
- [ ] Tests

#### Día 5: Usuarios y Autenticación
- [ ] Crear `UserApiController`
- [ ] Métodos de roles y permisos
- [ ] Integración con Spatie
- [ ] Tests de autenticación

---

### Fase 3: Administración y Estadísticas (Semana 3)
**Prioridad: MEDIA**

#### Día 1: Proveedores y Servicios
- [ ] Crear `ProveedorApiController`
- [ ] Crear `ServicioApiController`
- [ ] CRUD completo
- [ ] Tests

#### Día 2: Dashboard/Estadísticas
- [ ] Crear `DashboardApiController`
- [ ] Método `index()` - resumen general
- [ ] Métodos de estadísticas por módulo
- [ ] Tests

#### Día 3: Valores y Mantenimientos
- [ ] Crear `ValorApiController`
- [ ] Crear `MantenimientoApiController`
- [ ] CRUD completo
- [ ] Tests

#### Día 4: Tareas y Asistencias
- [ ] Crear `TareaApiController`
- [ ] Crear `AsistenciaApiController`
- [ ] CRUD + métodos especiales
- [ ] Tests

#### Día 5: Roles, Permisos y Contabilidad
- [ ] Crear `RolApiController`
- [ ] Crear `ContabilidadApiController`
- [ ] Integración Spatie
- [ ] Tests finales

---

## 📝 CONTROLADORES A CREAR

### Template Base para Controladores

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{Modelo};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class {Modelo}ApiController extends Controller
{
    /**
     * Constructor - Aplicar middleware de permisos
     */
    public function __construct()
    {
        $this->middleware('permission:{modulo}.ver')->only(['index', 'show']);
        $this->middleware('permission:{modulo}.crear')->only(['store']);
        $this->middleware('permission:{modulo}.editar')->only(['update']);
        $this->middleware('permission:{modulo}.eliminar')->only(['destroy']);
    }

    /**
     * GET /api/v1/{modelo}
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $query = {Modelo}::query();

        // Filtros opcionales
        if ($request->has('campo')) {
            $query->where('campo', $request->campo);
        }

        // Búsqueda
        if ($request->has('search')) {
            $query->where('nombre', 'LIKE', '%' . $request->search . '%');
        }

        // Ordenamiento
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $data = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ]
        ]);
    }

    /**
     * GET /api/v1/{modelo}/{id}
     */
    public function show($id)
    {
        $item = {Modelo}::with(['relacion1', 'relacion2'])->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'error' => '{Modelo} no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $item
        ]);
    }

    /**
     * POST /api/v1/{modelo}
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Reglas de validación
            'campo1' => 'required|string|max:255',
            'campo2' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $item = {Modelo}::create($request->all());

        return response()->json([
            'success' => true,
            'message' => '{Modelo} creado exitosamente',
            'data' => $item
        ], 201);
    }

    /**
     * PUT /api/v1/{modelo}/{id}
     */
    public function update(Request $request, $id)
    {
        $item = {Modelo}::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'error' => '{Modelo} no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            // Reglas de validación
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $item->update($request->all());

        return response()->json([
            'success' => true,
            'message' => '{Modelo} actualizado exitosamente',
            'data' => $item
        ]);
    }

    /**
     * DELETE /api/v1/{modelo}/{id}
     */
    public function destroy($id)
    {
        $item = {Modelo}::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'error' => '{Modelo} no encontrado'
            ], 404);
        }

        // Validar si se puede eliminar
        // if ($item->tiene_dependencias()) {
        //     return response()->json([
        //         'success' => false,
        //         'error' => 'No se puede eliminar porque tiene registros asociados'
        //     ], 400);
        // }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => '{Modelo} eliminado exitosamente'
        ]);
    }
}
```

---

## 🧪 TESTING Y VALIDACIÓN

### Colección de Postman

**Crear colección**: `Streamify API v1 - Completa`

#### Carpetas por Módulo:
1. 📁 Auth
   - Login
   - Logout
   - Refresh Token

2. 📁 Clientes
   - Listar clientes
   - Ver cliente
   - Crear cliente
   - Actualizar cliente
   - Eliminar cliente
   - Ventas del cliente
   - Pedidos del cliente
   - Estadísticas cliente

3. 📁 Ventas
   - Listar ventas
   - Ver venta
   - Crear venta
   - Actualizar venta
   - Eliminar venta
   - Renovar venta
   - Detalles de venta
   - Estadísticas ventas

4. 📁 Cuentas
   - ... (todos los endpoints)

... *(replicar para cada módulo)*

---

### Tests Automatizados con PHPUnit

**Crear tests**:
```bash
php artisan make:test Api/VentaApiTest
php artisan make:test Api/CuentaApiTest
php artisan make:test Api/ClienteApiTest
# ... etc
```

**Ejemplo de test**:
```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{User, ApiKey, Venta};
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaApiTest extends TestCase
{
    use RefreshDatabase;

    protected $apiKey;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->apiKey = ApiKey::generate('Test Key', $this->user->id);
        $this->user->givePermissionTo('ventas.ver', 'ventas.crear');
    }

    /** @test */
    public function puede_listar_ventas()
    {
        Venta::factory()->count(5)->create();

        $response = $this->withHeader('X-API-Key', $this->apiKey->key)
                         ->getJson('/api/v1/ventas');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data',
                     'pagination'
                 ]);
    }

    /** @test */
    public function puede_crear_venta()
    {
        $data = [
            'idcli' => 1,
            'idprod' => 1,
            // ... más campos
        ];

        $response = $this->withHeader('X-API-Key', $this->apiKey->key)
                         ->postJson('/api/v1/ventas', $data);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('ventas', ['idcli' => 1]);
    }

    /** @test */
    public function requiere_permiso_para_crear_venta()
    {
        $this->user->revokePermissionTo('ventas.crear');

        $response = $this->withHeader('X-API-Key', $this->apiKey->key)
                         ->postJson('/api/v1/ventas', []);

        $response->assertStatus(403);
    }
}
```

---

## 📚 DOCUMENTACIÓN

### Generar Documentación con Scribe

```bash
composer require --dev knuckleswtf/scribe
php artisan vendor:publish --tag=scribe-config
php artisan scribe:generate
```

### Swagger/OpenAPI

```bash
composer require darkaonline/l5-swagger
php artisan l5-swagger:generate
```

---

## ✅ CHECKLIST GENERAL

### Por Cada Controlador:

- [ ] Crear controlador en `app/Http/Controllers/Api/V1/`
- [ ] Implementar constructor con middleware de permisos
- [ ] Implementar método `index()` con paginación y filtros
- [ ] Implementar método `show()` con relaciones
- [ ] Implementar método `store()` con validación completa
- [ ] Implementar método `update()` con validación
- [ ] Implementar método `destroy()` con validaciones
- [ ] Agregar métodos personalizados según módulo
- [ ] Crear rutas en `routes/api.php`
- [ ] Crear tests en `tests/Feature/Api/`
- [ ] Agregar endpoints a colección Postman
- [ ] Documentar con PHPDoc
- [ ] Validar respuestas de error (404, 403, 422, 500)
- [ ] Probar con diferentes roles/permisos

---

### Validación Final:

- [ ] Todos los endpoints retornan JSON con estructura consistente
- [ ] Todas las rutas tienen middleware `api.key`
- [ ] Todos los métodos validan permisos con Spatie
- [ ] Paginación funciona correctamente
- [ ] Filtros y búsqueda funcionan
- [ ] Ordenamiento funciona
- [ ] Relaciones se cargan correctamente (evitar N+1)
- [ ] Validaciones retornan errores claros (422)
- [ ] Errores 404 cuando recurso no existe
- [ ] Errores 403 cuando falta permiso
- [ ] Tests pasan correctamente
- [ ] Postman collection actualizada
- [ ] Documentación generada

---

## 📊 RESUMEN DE PROGRESO

### Módulos Completados: 2/18 (11%)

- ✅ **Clientes**: Parcialmente (falta `pedidos()` y `estadisticas()`)
- ✅ **Chat**: Completo (ya existía)
- ✅ **Ventas**: ✅ **COMPLETO** - 8 endpoints implementados

### Módulos Pendientes: 16/18 (89%)

**Alta Prioridad** (Semana 1):
- ✅ ~~Ventas~~ **COMPLETADO** 🎉
- ❌ Productos (siguiente)
- ❌ Cuentas

**Media Prioridad** (Semana 2):
- ❌ Gastos
- ❌ Empleados
- ❌ Pedidos
- ❌ Recargas
- ❌ Usuarios

**Baja Prioridad** (Semana 3):
- ❌ Proveedores
- ❌ Servicios
- ❌ Dashboard
- ❌ Valores
- ❌ Mantenimientos
- ❌ Tareas
- ❌ Asistencias
- ❌ Roles/Permisos
- ❌ Contabilidad

---

## 🎯 PRÓXIMOS PASOS INMEDIATOS

1. **✅ ~~Crear VentaApiController~~** ✅ **COMPLETADO**
   - ✅ CRUD completo
   - ✅ Método `renovar()`
   - ✅ Método `detalles()`
   - ✅ Método `estadisticas()`

2. **Crear ProductoApiController** (Día 3 - SIGUIENTE)
   - CRUD completo
   - Métodos `categorias()` y `tipos()`

3. **Completar ClienteApiController**
   - Agregar método `pedidos()`
   - Agregar método `estadisticas()`

3. **Crear ProductoApiController** (Día 3)
   - CRUD completo
   - Métodos `categorias()` y `tipos()`

4. **Crear CuentaApiController** (Día 4-5)
   - CRUD completo
   - Métodos especiales (disponibles, asignar, etc.)

---

**Última actualización**: Diciembre 4, 2025  
**Versión**: 6.1  
**Responsable**: Equipo de Desarrollo  
**Estado**: 📝 En planificación
