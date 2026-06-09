# Sistema de Asistencias Mejorado

## 📋 Problema Resuelto

Cuando un empleado tiene múltiples pestañas del navegador abiertas, el sistema registraba múltiples asistencias en el mismo momento, causando:
- Cálculos incorrectos del tiempo de conexión
- Estadísticas infladas artificialmente
- Base de datos con registros duplicados

## ✅ Solución Implementada

### 1. Prevención de Duplicados al Registrar
**Archivo**: `app/Http/Controllers/AsistenciaController.php`

El método `ping()` ahora:
- ✅ Verifica si ya existe una asistencia en los últimos **30 segundos**
- ✅ Solo registra si:
  - No hay asistencia reciente, O
  - La ruta cambió (navegó a otra página)
- ✅ Previene múltiples registros por múltiples pestañas

### 2. Deduplicación en el Cálculo
**Archivo**: `app/Services/EmpleadoService.php`

El método `obtenerLapsosDeAsistenciasPorDia()` ahora:
- ✅ Deduplica asistencias antes de calcular lapsos
- ✅ Agrupa registros que estén en un intervalo de **30 segundos**
- ✅ Calcula correctamente el tiempo de conexión
- ✅ Elimina distorsiones por pestañas múltiples

### 3. Comando de Limpieza
**Archivo**: `app/Console/Commands/LimpiarAsistenciasDuplicadas.php`

Nuevo comando Artisan para limpiar registros duplicados existentes.

## 🚀 Uso del Sistema

### Funcionamiento Automático
El sistema ahora funciona automáticamente. No requiere configuración adicional.

### Limpiar Asistencias Duplicadas Existentes

#### Ver qué se limpiaría (simulación):
```bash
php artisan asistencias:limpiar-duplicadas --dry-run
```

#### Limpiar últimos 7 días (por defecto):
```bash
php artisan asistencias:limpiar-duplicadas
```

#### Limpiar últimos N días:
```bash
php artisan asistencias:limpiar-duplicadas --dias=30
```

#### Limpiar fecha específica:
```bash
php artisan asistencias:limpiar-duplicadas --fecha=2026-01-30
```

#### Ver detalles (verbose):
```bash
php artisan asistencias:limpiar-duplicadas --dry-run -v
```

## 📊 Mejoras en el Cálculo

### Antes
```
03:23:36 - Empleado 12 en /admin/bancos
03:23:36 - Empleado 12 en /admin/cuentas  ← Duplicado
03:25:36 - Empleado 12 en /admin/usuarios
03:26:36 - Empleado 12 en /admin/costos

Resultado: 3 minutos de conexión (INCORRECTO)
```

### Después
```
03:23:36 - Empleado 12 en /admin/bancos
[03:23:36 ignorado - duplicado]
03:25:36 - Empleado 12 en /admin/usuarios
03:26:36 - Empleado 12 en /admin/costos

Resultado: 3 minutos de conexión (CORRECTO)
```

## 🔧 Parámetros Ajustables

### Intervalo de Deduplicación
En `AsistenciaController.php` línea ~69:
```php
->where('created_at', '>=', Carbon::now()->subSeconds(30))
```
Cambiar `30` a otro valor si necesitas más/menos tiempo.

### Ventana de Conexión Activa
En `EmpleadoService.php` línea ~63:
```php
if ($actual->diffInMinutes($anterior) <= 5) {
```
Cambiar `5` minutos si necesitas ajustar cuándo se considera una sesión continua.

## 📈 Verificación

### Antes de implementar:
```sql
-- Ver duplicados existentes
SELECT empleado_id, DATE(created_at) as fecha, 
       COUNT(*) as total,
       COUNT(DISTINCT DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s')) as unicos
FROM asistencias
GROUP BY empleado_id, DATE(created_at)
HAVING total > unicos
ORDER BY fecha DESC;
```

### Después de limpiar:
```sql
-- Verificar que no hay duplicados
SELECT empleado_id, created_at, ruta_actual
FROM asistencias
WHERE DATE(created_at) = CURDATE()
ORDER BY empleado_id, created_at;
```

## ⚠️ Notas Importantes

1. **Backup**: Siempre haz backup de la base de datos antes de limpiar datos
2. **Dry-run**: Usa `--dry-run` primero para ver qué se eliminará
3. **Pestañas**: Los usuarios pueden seguir usando múltiples pestañas sin problema
4. **Histórico**: El comando de limpieza NO afecta cálculos futuros, solo limpia registros antiguos

## 🎯 Resultado Final

- ✅ Tiempo de conexión calculado correctamente
- ✅ Estadísticas precisas
- ✅ Base de datos limpia
- ✅ Sistema más eficiente
- ✅ Sin impacto en la experiencia del usuario
