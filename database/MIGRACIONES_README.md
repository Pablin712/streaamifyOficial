# Migraciones de Laravel - Streamify

## ✅ Configuración Completada

Se han reemplazado y ordenado todas las migraciones de Laravel para reflejar exactamente la estructura de la base de datos de producción (`u557565149_streamify.sql`).

## 📋 Estructura de Migraciones Creadas

### Orden de Ejecución:
1. `0001_01_01_000000_create_users_table.php` - Tablas base de Laravel (users, sessions, etc.)
2. `0001_01_01_000001_create_cache_table.php` - Sistema de cache
3. `0001_01_01_000002_create_jobs_table.php` - Sistema de colas y trabajos
4. `2024_01_01_000001_create_base_tables.php` - Tablas principales (empleados, clientes, servicios, proveedores)
5. `2024_01_01_000002_create_valores_cuentas_perfiles.php` - Valores, cuentas y perfiles
6. `2024_01_01_000003_create_ventas_detalles.php` - Sistema de ventas y detalles
7. `2024_01_01_000004_create_costos_mantenimientos.php` - Costos y mantenimientos
8. `2024_01_01_000005_create_productos_categorias.php` - Productos, categorías y tipos
9. `2024_01_01_000006_create_bancos_recargas_pedidos.php` - Bancos, recargas y pedidos
10. `2024_01_01_000007_create_gastos_contabilidad_estadisticas.php` - Gastos, contabilidad y estadísticas
11. `2024_01_01_000008_create_tareas_asistencias_historial.php` - Tareas, asistencias e historial
12. `2024_01_01_000009_create_permissions_roles.php` - Sistema de permisos Spatie
13. `2024_01_01_000010_create_triggers.php` - Triggers principales
14. `2024_01_01_000011_create_perfiles_trigger.php` - Trigger para perfiles automáticos
15. `2024_01_01_000012_create_views.php` - Vistas de base de datos

## 🎯 Características Implementadas

### ✅ Tablas Principales
- ✅ empleados (con campos personalizados)
- ✅ clientes (con sistema de referidos)
- ✅ servicios (Netflix, Disney+, etc.)
- ✅ proveedores
- ✅ valores (planes y precios)
- ✅ cuentas (cuentas de streaming)
- ✅ perfiles (perfiles de cuentas)

### ✅ Sistema de Ventas
- ✅ ventas (con ID autogenerado)
- ✅ detalles_venta (detalles de cada venta)
- ✅ secuencia_factura (para numeración)

### ✅ Sistema de Productos
- ✅ categorias
- ✅ tipos_producto
- ✅ productos
- ✅ detalle_productos

### ✅ Sistema Financiero
- ✅ bancos
- ✅ recargas (con estados)
- ✅ estado_recargas
- ✅ costos
- ✅ gastos
- ✅ tipo_gasto
- ✅ contabilidad

### ✅ Gestión y Control
- ✅ tareas (con empleado responsable)
- ✅ asistencias (tracking de empleados)
- ✅ historial (log de acciones)
- ✅ daily_statistics (estadísticas diarias)
- ✅ ventas_diarias
- ✅ pedidos

### ✅ Sistema de Comunicación
- ✅ mails (configuración de correos)
- ✅ notifications (notificaciones Laravel)

### ✅ Sistema de Permisos
- ✅ permissions (Spatie Permission)
- ✅ roles
- ✅ model_has_permissions
- ✅ model_has_roles
- ✅ role_has_permissions
- ✅ rolesAntes (roles legacy)

### ✅ Triggers Implementados
1. **trg_insert_codigo_referidor** - Genera código de referido al crear cliente
2. **trg_update_codigo_referidor** - Actualiza código si cambia nombre
3. **trg_generar_idventa** - Genera ID de venta automático (001-001-000000001)
4. **trg_actualizar_total_venta_insert/update/delete** - Actualiza total de venta
5. **trigger_generar_idval** - Genera ID de valor automático
6. **insertar_perfiles** - Crea perfiles automáticamente según el tipo de cuenta

### ✅ Vistas Implementadas
1. **view_usuarios_activos** - Usuarios con cuentas activas
2. **view_clientes_usuarios** - Resumen de clientes y usuarios
3. **ventas_mensuales** - Estadísticas de ventas por mes
4. **usuarios_activos_mensuales** - Promedio de usuarios activos por mes

## 🔗 Relaciones Foreign Key
- ✅ Todas las relaciones de la BD de producción implementadas
- ✅ Restricciones CASCADE donde corresponde
- ✅ Índices en campos de búsqueda frecuente

## ⚡ Comandos Útiles

### Ejecutar migraciones desde cero:
```bash
php artisan migrate:fresh
```

### Ejecutar migraciones con seeders:
```bash
php artisan migrate:fresh --seed
```

### Ver estado de migraciones:
```bash
php artisan migrate:status
```

### Crear nueva migración:
```bash
php artisan make:migration nombre_de_migracion
```

## ✨ Funcionalidades Especiales

1. **Código de Referido Automático**: Al crear un cliente se genera automáticamente su código (ej: JUAN-001)

2. **ID de Venta Automático**: Las ventas tienen ID con formato 001-001-000000001

3. **Perfiles Automáticos**: Al crear una cuenta se generan perfiles según el servicio:
   - Netflix: 5 perfiles con PINs predefinidos
   - Disney+: 7 perfiles
   - Prime Video: 6 perfiles
   - Spotify: 6 perfiles (owner + invitados)
   - Etc.

4. **Cálculo Automático de Totales**: Los totales de venta se actualizan automáticamente

5. **Vistas Optimizadas**: Para consultas frecuentes como usuarios activos y estadísticas

## 🎉 Estado: COMPLETADO

✅ Todas las migraciones ejecutadas exitosamente
✅ Estructura de BD idéntica a producción
✅ Triggers funcionando correctamente
✅ Vistas creadas
✅ Relaciones establecidas
✅ Pruebas básicas realizadas

La base de datos está lista para usar con `php artisan migrate:fresh` en cualquier ambiente de desarrollo.