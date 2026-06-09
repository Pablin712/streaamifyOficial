# Mejoras en ContadorService - ViewUsuarioActivo

**Fecha:** 15 de enero de 2026  
**Módulo:** API Contador v2

## 📋 Resumen de Cambios

Se han mejorado las consultas del `ContadorService` para usar correctamente la vista `ViewUsuarioActivo` y los campos de los modelos, siguiendo estas aclaraciones importantes:

### ✅ Aclaraciones Clave

1. **Campo `estado` en DetalleVenta**: Solo sirve para mensajes de WhatsApp, se debe IGNORAR para consultas financieras
2. **Campo `activodet`**: Valores 'v'/'f' o 1/0 - indica si un cliente usa o no la cuenta todavía
3. **Vista `ViewUsuarioActivo`**: Vista especializada que filtra automáticamente detalles de venta activos (`activodet = 1`)
4. **Facturas Pendientes**: Obtener de `ViewUsuarioActivo` quienes tienen `fecha_vencimiento` próxima a 3 días, es hoy, o ya está atrasada

---

## 🔧 Cambios Implementados

### 1. Método `facturasPendientes()`

**Antes:**
```php
// ❌ Usaba Venta con whereHas() en detalles_venta
// ❌ Filtraba por estado (campo incorrecto para finanzas)
// ❌ No usaba ViewUsuarioActivo
```

**Ahora:**
```php
// ✅ Usa ViewUsuarioActivo directamente
// ✅ Filtra por fecha_vencimiento (próxima 3 días, hoy, o atrasada)
// ✅ Ignora campo estado
// ✅ Solo incluye cuentas activas (activodet = 1)
```

**Estructura de Respuesta:**
```json
{
  "total_pendientes": 15,
  "monto_total": 450.00,
  "promedio_dias_atraso": 7,
  "resumen": {
    "atrasados": 8,
    "vencen_hoy": 3,
    "proximos": 4
  },
  "facturas": [
    {
      "id_detalle": 456,
      "id_venta": 123,
      "id_cliente": 89,
      "cliente": "Juan Pérez",
      "perfil": "user123@netflix",
      "servicio": "Netflix",
      "monto": 30.00,
      "fecha_vencimiento": "2026-01-10",
      "dias_atraso": 5,
      "dias_restantes": 0,
      "estado_pago": "ATRASADO"  // ATRASADO, VENCE_HOY, PROXIMO
    }
  ]
}
```

**Parámetros Actualizados:**
- ✅ `dias_proximos` (default: 3) - Reemplaza `dias_atrasados`
- ✅ `servicio` (ID numérico del servicio)

---

### 2. Método `clientesMorosos()`

**Mejoras:**
```php
// ✅ Usa ViewUsuarioActivo para obtener solo cuentas activas vencidas
// ✅ Agrupa por cliente correctamente
// ✅ Incluye lista de servicios del cliente
// ✅ Cuenta "cuentas_vencidas" en lugar de "ventas_vencidas"
```

**Estructura de Respuesta:**
```json
{
  "total_morosos": 5,
  "monto_total_deuda": 1250.00,
  "promedio_deuda": 250.00,
  "clientes": [
    {
      "id": 89,
      "nombre": "Juan Pérez",
      "cuentas_vencidas": 3,
      "deuda_total": 450.00,
      "dias_max_atraso": 15,
      "servicios": ["Netflix", "Spotify", "Disney+"]
    }
  ]
}
```

---

### 3. Método `proyeccionIngresos()`

**Mejoras:**
```php
// ✅ Usa ViewUsuarioActivo para obtener cuentas activas que vencen
// ✅ Más eficiente y preciso
// ✅ No usa campo estado (ignorado)
```

---

### 4. Método `estadisticasClientes()`

**Mejoras:**
```php
// ✅ Usa ViewUsuarioActivo para contar clientes activos
// ✅ Cuenta cuentas_activas_totales
// ✅ Top clientes calculado correctamente desde detalles_venta
```

**Estructura de Respuesta:**
```json
{
  "total_clientes": 150,
  "clientes_activos": 120,
  "clientes_inactivos": 30,
  "porcentaje_activos": 80.00,
  "cuentas_activas_totales": 345,
  "clientes_con_saldo": 45,
  "saldo_total_clientes": 5670.50,
  "top_clientes": [...]
}
```

---

### 5. Método `calcularTasaRenovacion()`

**Mejoras:**
```php
// ✅ Compara detalles vencidos vs los que siguen activos (activodet = 1)
// ✅ No usa campo estado
// ✅ Cálculo más preciso de renovación
```

---

## 📊 Estructura de ViewUsuarioActivo

```sql
CREATE OR REPLACE VIEW view_usuarios_activos AS
SELECT 
    v.idcli,
    cl.nombrecli AS nombre_cliente,
    dv.idven,
    dv.iddet,
    p.idcue,
    p.numeroper AS perfil,
    dv.fechavendet AS fecha_vencimiento
FROM detalles_venta dv
JOIN ventas v ON dv.idven = v.idven
JOIN clientes cl ON v.idcli = cl.idcli
JOIN perfiles p ON dv.idper = p.idper
JOIN cuentas c ON p.idcue = c.idcue
WHERE dv.activodet = 1  -- ✅ Solo cuentas activas
```

**Modelo Laravel:**
```php
class ViewUsuarioActivo extends Model
{
    protected $table = 'view_usuarios_activos';
    protected $primaryKey = null;
    public $timestamps = false;
    
    // Campos disponibles
    protected $fillable = [
        'idcli',
        'nombre_cliente',
        'idven',
        'iddet',
        'idcue',
        'perfil',
        'fecha_vencimiento'
    ];
    
    // Relaciones
    public function cuenta() { ... }
    public function cliente() { ... }
    public function venta() { ... }
    public function detalle_venta() { ... }
}
```

---

## 🔍 Campos Importantes

### DetalleVenta
- `activodet`: **v/f o 1/0** - Indica si cliente usa la cuenta (usar para filtrar activos)
- `estado`: **IGNORAR** - Solo para mensajes de WhatsApp
- `fechavendet`: Fecha de vencimiento de la cuenta
- `montodet`: Monto de la venta/cuenta

### ViewUsuarioActivo
- `fecha_vencimiento`: Fecha cuando vence la cuenta
- `perfil`: Número de perfil (usuario@servicio)
- Solo incluye registros con `activodet = 1`

---

## 🧪 Pruebas Recomendadas

### 1. Facturas Pendientes
```bash
# Próximas 3 días (default)
GET /api/v2/accountant/ventas/facturas-pendientes

# Próximas 7 días
GET /api/v2/accountant/ventas/facturas-pendientes?dias_proximos=7

# Filtrar por servicio (ID = 1)
GET /api/v2/accountant/ventas/facturas-pendientes?servicio=1
```

### 2. Clientes Morosos
```bash
# Morosos con 3+ días de atraso (default)
GET /api/v2/accountant/ventas/clientes-morosos

# Morosos con 7+ días
GET /api/v2/accountant/ventas/clientes-morosos?dias_minimos=7
```

### 3. Estadísticas de Clientes
```bash
GET /api/v2/accountant/estadisticas/clientes
```

---

## ✅ Verificación de Cambios

**Archivos Modificados:**
- ✅ `app/Services/ContadorService.php` - 6 métodos mejorados
- ✅ `app/Http/Controllers/ContadorController.php` - Parámetros actualizados
- ✅ `docs/30-API-CONTADOR-GUIA-PRUEBAS.md` - Documentación actualizada
- ✅ Sin errores de sintaxis

---

## 🎯 Ventajas de los Cambios

1. **Rendimiento**: Uso de vista especializada `ViewUsuarioActivo`
2. **Precisión**: Solo cuentas realmente activas (`activodet = 1`)
3. **Claridad**: Ignorar campo `estado` (solo para WhatsApp)
4. **Información**: Estados claros (ATRASADO, VENCE_HOY, PROXIMO)
5. **Flexibilidad**: Parámetro `dias_proximos` para controlar ventana de tiempo
6. **Datos correctos**: Respuestas JSON con estructura coherente

---

## 📝 Notas Adicionales

- El campo `activodet` es el indicador correcto de si una cuenta está en uso
- `ViewUsuarioActivo` ya filtra automáticamente por `activodet = 1`
- El campo `estado` en `DetalleVenta` NO debe usarse para lógica financiera
- `fecha_vencimiento` es el campo correcto para determinar vencimientos
- Las respuestas JSON ahora incluyen más detalles útiles (perfil, servicios, etc.)

---

## 🔄 Próximos Pasos

1. ✅ Probar endpoints con datos reales
2. ⏳ Implementar rol Técnico
3. ⏳ Implementar rol Secretaria
4. ⏳ Sistema de autenticación para empleados
