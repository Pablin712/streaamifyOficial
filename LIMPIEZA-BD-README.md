# 🧹 Limpieza Rápida de Base de Datos

## Comandos Más Usados

### 1. Ver qué se eliminaría (SIN BORRAR NADA)
```bash
php artisan db:limpiar-inactivos --dry-run
```

### 2. Limpieza completa segura (con confirmación)
```bash
php artisan db:limpiar-inactivos
```

### 3. Limpiar solo ventas antiguas con TODOS los detalles inactivos
```bash
php artisan db:limpiar-inactivos --ventas-antiguas
```

### 4. Limpiar ventas antiguas y ventas vacías
```bash
php artisan db:limpiar-inactivos --ventas-antiguas --ventas-vacias
```

### 5. Cambiar antigüedad requerida (ej: 2 años)
```bash
php artisan db:limpiar-inactivos --ventas-antiguas --anos=2
```

## 🎯 Recomendación de Uso

**Limpieza semanal automática:**
1. Solo ventas antiguas con TODOS los detalles inactivos
2. Ventas sin detalles

```bash
php artisan db:limpiar-inactivos --ventas-antiguas --ventas-vacias
```

**Limpieza mensual profunda:**
1. Primero revisar con `--dry-run`
2. Luego ejecutar completa

```bash
# Paso 1: Revisar
php artisan db:limpiar-inactivos --dry-run

# Paso 2: Ejecutar si todo está bien
php artisan db:limpiar-inactivos
```

## 📋 Qué Limpia

| Tipo | Criterio |
|------|----------|
| Ventas Antiguas | >1 año + TODOS los detalles `activodet = 'NO'` |
| Ventas Vacías | Sin detalles asociados |
| Perfiles | De cuentas inactivas |
| Cuentas | `activocue = false` |
| Valores | `activoval = false` sin cuentas |
| Proveedores | `activopro = false` sin valores |

## 🚨 Importante: Lógica de Ventas

**NUNCA se eliminan detalles de venta individuales.**

Solo se eliminan ventas **COMPLETAS** donde:
- ✅ **TODOS** los detalles sean inactivos (`activodet = 'NO'`)
- ✅ La venta tenga más de 1 año (configurable con `--anos`)

Esto evita eliminar detalles de ventas que aún tienen otros detalles activos.

## ⚠️ Importante

- **SIEMPRE** ejecute con `--dry-run` primero
- Haga backup antes de limpiezas profundas
- En producción, use `--dry-run` y revise los resultados
- Los registros eliminados NO se pueden recuperar

## 📚 Documentación Completa

Ver [docs/35-COMANDO-LIMPIEZA-BD.md](docs/35-COMANDO-LIMPIEZA-BD.md)
