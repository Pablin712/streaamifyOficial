# Comando de Limpieza de Base de Datos

## Descripción
Comando Artisan para limpiar registros inactivos de la base de datos y liberar espacio. Elimina de forma segura:

- ✅ **Ventas antiguas** (>1 año) donde **TODOS** los detalles sean inactivos (nunca elimina detalles de ventas con detalles activos)
- ✅ Ventas sin detalles asociados
- ✅ Perfiles asociados a cuentas inactivas
- ✅ Cuentas inactivas (`activocue = false/0`)
- ✅ Valores inactivos sin cuentas asociadas (`activoval = false/0`)
- ✅ Proveedores inactivos sin valores asociados (`activopro = false/0`)

## Ubicación
```
app/Console/Commands/LimpiarRegistrosInactivos.php
```

## Uso Básico

### 1. Modo Simulación (Recomendado primero)
```bash
php artisan db:limpiar-inactivos --dry-run
```
Muestra qué se eliminaría sin borrar nada realmente.

### 2. Limpieza Completa
```bash
php artisan db:limpiar-inactivos
```
Solicita confirmación antes de eliminar. Limpia todos los tipos de registros inactivos.

### 3. Limpieza sin Confirmación
```bash
php artisan db:limpiar-inactivos --force
```
⚠️ **PELIGRO**: Elimina directamente sin pedir confirmación.

## Opciones Específicas

### Limpiar Solo Ventas Antiguas con TODOS los Detalles Inactivos
```bash
php artisan db:limpiar-inactivos --ventas-antiguas
```

### Cambiar Años de Antigüedad (default: 1 año)
```bash
# Ventas con más de 2 años
php artisan db:limpiar-inactivos --ventas-antiguas --anos=2
```

### Limpiar Solo Ventas Vacías
```bash
php artisan db:limpiar-inactivos --ventas-vacias
```

### Limpiar Solo Cuentas Inactivas y sus Perfiles
```bash
php artisan db:limpiar-inactivos --cuentas
```

### Limpiar Solo Valores Inactivos
```bash
php artisan db:limpiar-inactivos --valores
```

### Limpiar Solo Proveedores Inactivos
```bash
php artisan db:limpiar-inactivos --proveedores
```

## Combinación de Opciones

### Simular limpieza solo de ventas antiguas y ventas vacías
```bash
php artisan db:limpiar-inactivos --ventas-antiguas --ventas-vacias --dry-run
```

### Limpiar cuentas y valores sin confirmación
```bash
php artisan db:limpiar-inactivos --cuentas --valores --force
```

## Todas las Opciones

| Opción | Descripción |
|--------|-------------|
| `--dry-run` | Simula la limpieza sin eliminar registros |
| `--force` | No solicita confirmación antes de eliminar |
| `--ventas-antiguas` | Solo limpia ventas antiguas con TODOS los detalles inactivos |
| `--ventas-vacias` | Solo limpia ventas sin detalles |
| `--cuentas` | Solo limpia cuentas inactivas y sus perfiles |
| `--valores` | Solo limpia valores inactivos sin cuentas |
| `--proveedores` | Solo limpia proveedores inactivos sin valores |
| `--anos=N` | Años de antigüedad mínima para ventas (default: 1) |

## Orden de Limpieza

El comando respeta las relaciones de la base de datos eliminando en este orden:

1. **Ventas antiguas** (>1 año) donde TODOS los detalles sean inactivos
   - Primero elimina los detalles
   - Luego elimina la venta
2. **Ventas** sin detalles asociados
3. **Perfiles** de cuentas inactivas
4. **Cuentas** inactivas
5. **Valores** inactivos sin cuentas
6. **Proveedores** inactivos sin valores

Esto evita errores de integridad referencial.

## 🚨 Lógica de Seguridad para Ventas

**IMPORTANTE**: El comando **NUNCA** eliminará detalles de venta de forma individual.

### Reglas de Eliminación de Ventas:

1. ✅ Solo se eliminan ventas **COMPLETAS** (con todos sus detalles)
2. ✅ Solo si **TODOS** los detalles de esa venta son inactivos (`activodet = 'NO'`)
3. ✅ Solo si la venta tiene más de 1 año de antigüedad (configurable con `--anos`)

### Ejemplo:

**Venta #001-001-000001** con 3 detalles:
- Detalle 1: `activodet = 'NO'` ❌
- Detalle 2: `activodet = 'SI'` ✅ 
- Detalle 3: `activodet = 'NO'` ❌

⚠️ **Esta venta NO se eliminará** porque tiene al menos un detalle activo.

**Venta #001-001-000002** con 2 detalles:
- Detalle 1: `activodet = 'NO'` ❌
- Detalle 2: `activodet = 'NO'` ❌
- Fecha: hace 2 años

✅ **Esta venta SÍ se eliminará** (completa con todos sus detalles).

## Ejemplo de Salida

```
⚠️  MODO SIMULACIÓN - No se eliminarán registros

🔍 Buscando detalles de venta inactivos...
   Encontrados: 67 detalles de venta inactivos
🔍 Buscando ventas sin detalles asociados...
   ✅ No hay ventas sin detalles
🔍 Buscando cuentas inactivas...
   ✅ No hay cuentas inactivas
🔍 Buscando valores inactivos sin cuentas...
   ✅ No hay valores inactivos sin cuentas
🔍 Buscando proveedores inactivos sin valores...
   ✅ No hay proveedores inactivos sin valores

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 RESUMEN DE LIMPIEZA
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
+-------------------+----------+
| Tipo              | Cantidad |
+-------------------+----------+
| Detalles de Venta | 67       |
| Ventas vacías     | 0        |
| Perfiles          | 0        |
| Cuentas           | 0        |
| Valores           | 0        |
| Proveedores       | 0        |
+-------------------+----------+
⚠️  MODO SIMULACIÓN: 67 registros serían eliminados
💡 Ejecute sin --dry-run para eliminar realmente
```

## Seguridad

### ✅ Características de Seguridad
- **Modo dry-run**: Simula antes de eliminar
- **Confirmación**: Pide confirmación excepto con `--force`
- **Integridad referencial**: Elimina en orden correcto
- **Validación**: Solo elimina registros verdaderamente inactivos
- **Protección**: No elimina valores/proveedores con relaciones activas

### ⚠️ Advertencias
- **Backup recomendado**: Haga backup antes de ejecutar sin `--dry-run`
- **Producción**: Use siempre `--dry-run` primero en producción
- **Irreversible**: Los registros eliminados no se pueden recuperar

## Automatización (Opcional)

### Ejecutar semanalmente (cron)
```bash
# En app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Cada domingo a las 3:00 AM
    $schedule->command('db:limpiar-inactivos --detalles-venta --ventas-vacias')
             ->weekly()
             ->sundays()
             ->at('03:00');
}
```

## Casos de Uso

### 1. Limpieza periódica ligera (semanal)
```bash
php artisan db:limpiar-inactivos --ventas-antiguas --ventas-vacias
```

### 2. Limpieza profunda (mensual)
```bash
# 1. Revisar qué se eliminaría
php artisan db:limpiar-inactivos --dry-run

# 2. Si todo está bien, ejecutar
php artisan db:limpiar-inactivos
```

### 3. Limpieza agresiva - Ventas con 6 meses de antigüedad
```bash
# Primero revisar
php artisan db:limpiar-inactivos --ventas-antiguas --anos=0.5 --dry-run

# Si está bien, ejecutar
php artisan db:limpiar-inactivos --ventas-antiguas --anos=0.5
```

## Troubleshooting

### Error: "SQLSTATE[23000]: Integrity constraint violation"
**Causa**: Intenta eliminar registros con relaciones activas.  
**Solución**: El comando ya previene esto, pero si ocurre:
- Use `--dry-run` para identificar el problema
- Reporte el bug con detalles

### Error: "Class 'App\Models\X' not found"
**Causa**: Algún modelo no está importado correctamente.  
**Solución**: Verifique los imports en el archivo del comando.

### No se eliminan registros esperados
**Causa**: Posibles relaciones ocultas o lógica de negocio.  
**Solución**: 
- Verifique que los registros realmente estén inactivos
- Use `--dry-run` para ver qué detecta
- Revise las condiciones en el código del comando

## Mantenimiento del Comando

### Añadir nuevo tipo de limpieza
1. Agregar opción en `$signature`
2. Crear método privado `limpiarNuevoTipo()`
3. Agregar en `handle()` con condicional
4. Actualizar `mostrarResumen()`

### Modificar criterios de limpieza
Edite los métodos privados correspondientes:
- `limpiarVentasAntiguasInactivas()` - Ventas antiguas con todos los detalles inactivos
- `limpiarVentasVacias()` - Ventas vacías
- `limpiarCuentasInactivas()` - Cuentas y perfiles
- `limpiarValoresInactivos()` - Valores
- `limpiarProveedoresInactivos()` - Proveedores

## Contribuciones

Si necesita agregar más tipos de limpieza:
1. Identifique el modelo y criterio de "inactivo"
2. Cree método de limpieza siguiendo el patrón existente
3. Agregue opciones y documentación
4. Pruebe exhaustivamente con `--dry-run`
