# API Contador - Guía de Pruebas

**Fecha:** 15 de Enero de 2026  
**Controlador:** `ContadorController`  
**Service:** `ContadorService`  
**Base URL:** `http://localhost/api/v2/accountant`

---

## 🎯 Resumen de Implementación

### ✅ Completado

1. **ContadorService** creado en `app/Services/ContadorService.php`
   - Optimizado para usar `DailyStatistics` (reducir carga en DB)
   - 9 métodos principales implementados
   - Helpers para cálculo de fechas y tasas

2. **ContadorController** implementado en `app/Http/Controllers/ContadorController.php`
   - 9 endpoints públicos (temporal, sin autenticación)
   - Validación completa de parámetros
   - Manejo de errores robusto

3. **Rutas API** agregadas en `routes/api.php`
   - Estructura preparada para middleware futuro
   - Nombres de rutas consistentes

---

## 📋 Endpoints Disponibles

### 1. Resumen de Ventas
**GET** `/api/v2/accountant/ventas/resumen`

**Parámetros Query:**
- `periodo` (opcional): `daily`, `weekly`, `monthly`, `yearly`, `custom`
- `fecha_inicio` (requerido si periodo=custom): `YYYY-MM-DD`
- `fecha_fin` (requerido si periodo=custom): `YYYY-MM-DD`

**Ejemplo Requests:**
```bash
# Ventas del día
GET http://localhost/api/v2/accountant/ventas/resumen?periodo=daily

# Ventas del mes
GET http://localhost/api/v2/accountant/ventas/resumen?periodo=monthly

# Ventas personalizadas
GET http://localhost/api/v2/accountant/ventas/resumen?periodo=custom&fecha_inicio=2026-01-01&fecha_fin=2026-01-15
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "data": {
    "periodo": "monthly",
    "fecha_inicio": "2026-01-01",
    "fecha_fin": "2026-01-31",
    "total_ventas": 3500.00,
    "cantidad_ventas": 120,
    "promedio_venta": 29.17,
    "total_costos": 800.00,
    "total_gastos": 200.00,
    "ganancia_neta": 2500.00,
    "clientes_nuevos": 15,
    "servicios": [
      {
        "servicio": "Netflix",
        "cantidad": 50,
        "total": 1500.00
      }
    ],
    "metodos_pago": [
      {
        "metodo": "Transferencia Bancaria",
        "cantidad": 80,
        "total": 2400.00
      }
    ]
  }
}
```

---

### 2. Facturas Pendientes
**GET** `/api/v2/accountant/ventas/facturas-pendientes`

**Descripción:** Obtiene cuentas activas cuya fecha de vencimiento esté próxima (3 días), sea hoy, o ya esté atrasada. Usa la vista optimizada `ViewUsuarioActivo`.

**Parámetros Query:**
- `dias_proximos` (opcional, default: 3): Días hacia adelante para considerar próximos
- `servicio` (opcional): ID del servicio para filtrar (ej: `1`)

**Ejemplo Requests:**
```bash
# Facturas que vencen en los próximos 3 días (default)
GET http://localhost/api/v2/accountant/ventas/facturas-pendientes

# Facturas que vencen en los próximos 7 días
GET http://localhost/api/v2/accountant/ventas/facturas-pendientes?dias_proximos=7

# Facturas pendientes de Netflix (ID servicio = 1)
GET http://localhost/api/v2/accountant/ventas/facturas-pendientes?servicio=1
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "data": {
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
        "email": "juan@example.com",
        "telefono": "+593 96 123 4567",
        "perfil": "user123@netflix",
        "servicio": "Netflix",
        "monto": 30.00,
        "fecha_venta": "2026-01-05",
        "fecha_vencimiento": "2026-01-10",
        "dias_atraso": 5,
        "dias_restantes": 0,
        "estado_pago": "ATRASADO"
      }
    ]
  }
}
```

**Notas:**
- ✅ Solo cuentas activas (`activodet = 1`)
- ✅ Estados: `ATRASADO`, `VENCE_HOY`, `PROXIMO`

---

### 3. Ingresos por Servicio
**GET** `/api/v2/accountant/ventas/ingresos-por-servicio`

**Parámetros Query:**
- `periodo` (opcional): `daily`, `weekly`, `monthly`, `yearly`
- `fecha` (opcional): `YYYY-MM-DD`

**Ejemplo Requests:**
```bash
# Ingresos por servicio del mes actual
GET http://localhost/api/v2/accountant/ventas/ingresos-por-servicio?periodo=monthly

# Ingresos por servicio de la semana
GET http://localhost/api/v2/accountant/ventas/ingresos-por-servicio?periodo=weekly
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "data": {
    "periodo": "monthly",
    "fecha_inicio": "2026-01-01",
    "fecha_fin": "2026-01-31",
    "total_general": 3500.00,
    "cantidad_servicios": 5,
    "servicios": [
      {
        "servicio": "Netflix",
        "cantidad_ventas": 50,
        "ingresos": 1500.00,
        "clientes_unicos": 45,
        "porcentaje": 42.86,
        "promedio_por_venta": 30.00
      },
      {
        "servicio": "Disney+",
        "cantidad_ventas": 30,
        "ingresos": 900.00,
        "clientes_unicos": 28,
        "porcentaje": 25.71,
        "promedio_por_venta": 30.00
      }
    ]
  }
}
```

---

### 4. Clientes Morosos
**GET** `/api/v2/accountant/clientes/morosos`

**Parámetros Query:**
- `dias_minimos` (opcional, default: 3): días mínimos de atraso

**Ejemplo Requests:**
```bash
# Morosos con más de 3 días
GET http://localhost/api/v2/accountant/clientes/morosos

# Morosos con más de 7 días
GET http://localhost/api/v2/accountant/clientes/morosos?dias_minimos=7
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "data": {
    "total_morosos": 8,
    "monto_total_deuda": 240.00,
    "promedio_deuda": 30.00,
    "clientes": [
      {
        "id": 45,
        "nombre": "María López",
        "email": "maria@example.com",
        "telefono": "+593 96 123 4567",
        "ventas_vencidas": 2,
        "deuda_total": 60.00,
        "dias_max_atraso": 15,
        "ultima_venta": "2025-12-20"
      }
    ]
  }
}
```

---

### 5. Proyección de Ingresos
**GET** `/api/v2/accountant/ventas/proyeccion`

**Parámetros Query:**
- `periodo` (opcional): `next_week`, `next_month`

**Ejemplo Requests:**
```bash
# Proyección próxima semana
GET http://localhost/api/v2/accountant/ventas/proyeccion?periodo=next_week

# Proyección próximo mes
GET http://localhost/api/v2/accountant/ventas/proyeccion?periodo=next_month
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "data": {
    "periodo": "next_month",
    "fecha_inicio": "2026-01-16",
    "fecha_fin": "2026-02-16",
    "renovaciones_esperadas": 120,
    "ingreso_estimado": 3600.00,
    "tasa_renovacion_historica": 78.50,
    "ingreso_proyectado_real": 2826.00,
    "desglose": [
      {
        "servicio": "Netflix",
        "cantidad": 50,
        "monto_estimado": 1500.00
      }
    ]
  }
}
```

---

### 6. Estadísticas de Clientes
**GET** `/api/v2/accountant/clientes/estadisticas`

**Parámetros:** Ninguno

**Ejemplo Request:**
```bash
GET http://localhost/api/v2/accountant/clientes/estadisticas
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "data": {
    "total_clientes": 250,
    "clientes_activos": 180,
    "clientes_inactivos": 70,
    "porcentaje_activos": 72.00,
    "clientes_con_saldo": 45,
    "saldo_total_clientes": 1250.50,
    "top_clientes": [
      {
        "id": 12,
        "nombre": "Pedro Sánchez",
        "email": "pedro@example.com",
        "total_facturado": 450.00,
        "saldo": 15.00
      }
    ]
  }
}
```

---

### 7. Estadísticas de Métodos de Pago
**GET** `/api/v2/accountant/metodos-pago/estadisticas`

**Parámetros Query:**
- `periodo` (opcional): `daily`, `weekly`, `monthly`, `yearly`

**Ejemplo Request:**
```bash
GET http://localhost/api/v2/accountant/metodos-pago/estadisticas?periodo=monthly
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "data": {
    "periodo": "monthly",
    "fecha_inicio": "2026-01-01",
    "fecha_fin": "2026-01-31",
    "total_transacciones": 120,
    "monto_total": 3600.00,
    "metodos_pago": [
      {
        "metodo": "Transferencia Bancaria",
        "tipo": "Cuenta Corriente",
        "cantidad_transacciones": 80,
        "monto_total": 2400.00,
        "monto_promedio": 30.00,
        "porcentaje_transacciones": 66.67,
        "porcentaje_monto": 66.67
      }
    ]
  }
}
```

---

### 8. Reporte General Completo
**GET** `/api/v2/accountant/reportes/general`

**Parámetros Query:**
- `periodo` (opcional): `daily`, `weekly`, `monthly`, `yearly`

**Ejemplo Request:**
```bash
GET http://localhost/api/v2/accountant/reportes/general?periodo=monthly
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "data": {
    "periodo": "monthly",
    "fecha_inicio": "2026-01-01",
    "fecha_fin": "2026-01-31",
    "generado_en": "2026-01-15 10:30:00",
    "resumen_financiero": {
      "ingresos_totales": 3500.00,
      "costos_totales": 800.00,
      "gastos_totales": 200.00,
      "ganancia_neta": 2500.00,
      "margen_ganancia": 71.43
    },
    "ventas": {
      "cantidad_total": 120,
      "promedio_por_venta": 29.17,
      "clientes_nuevos": 15
    },
    "servicios": [...],
    "metodos_pago": [...],
    "clientes_morosos": {
      "total": 8,
      "deuda_total": 240.00
    },
    "proyeccion_proximo_mes": {
      "renovaciones_esperadas": 120,
      "ingreso_estimado": 3600.00,
      "ingreso_proyectado_real": 2826.00
    },
    "metricas_operativas": {
      "promedio_usuarios_activos": 450,
      "max_usuarios_activos": 520,
      "promedio_cuentas": 85
    }
  },
  "message": "Reporte generado exitosamente"
}
```

---

### 9. Exportar Reporte (En Desarrollo)
**POST** `/api/v2/accountant/reportes/exportar`

**Body JSON:**
```json
{
  "tipo_reporte": "general",
  "formato": "pdf",
  "periodo": "monthly"
}
```

**Parámetros:**
- `tipo_reporte` (requerido): `ventas`, `clientes`, `servicios`, `general`
- `formato` (opcional): `pdf`, `excel`, `csv`
- `periodo` (opcional): `daily`, `weekly`, `monthly`, `yearly`

**Ejemplo Request:**
```bash
POST http://localhost/api/v2/accountant/reportes/exportar
Content-Type: application/json

{
  "tipo_reporte": "general",
  "formato": "pdf",
  "periodo": "monthly"
}
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "data": {...},
  "message": "Datos preparados para exportación (función en desarrollo)"
}
```

---

## 🧪 Pruebas Rápidas con cURL

### Resumen de Ventas del Día
```bash
curl -X GET "http://localhost/api/v2/accountant/ventas/resumen?periodo=daily" \
  -H "Accept: application/json"
```

### Facturas Pendientes
```bash
curl -X GET "http://localhost/api/v2/accountant/ventas/facturas-pendientes" \
  -H "Accept: application/json"
```

### Clientes Morosos
```bash
curl -X GET "http://localhost/api/v2/accountant/clientes/morosos?dias_minimos=3" \
  -H "Accept: application/json"
```

### Reporte General
```bash
curl -X GET "http://localhost/api/v2/accountant/reportes/general?periodo=monthly" \
  -H "Accept: application/json"
```

---

## 🔧 Optimizaciones Implementadas

### 1. Uso de DailyStatistics
El servicio prioriza el uso de la tabla `daily_statistics` para:
- Resúmenes de ventas
- Estadísticas agregadas
- Proyecciones

**Ventaja:** Reduce significativamente la carga en la BD principal.

### 2. Queries Optimizadas
- Uso de `JOIN` en lugar de relaciones Eloquent cuando es más eficiente
- `GROUP BY` para agregaciones
- `SELECT` específicos para evitar cargar datos innecesarios

### 3. Caché Potencial (Futuro)
Los métodos están diseñados para ser fácilmente cacheables:
```php
Cache::remember("resumen_ventas_{$periodo}", 3600, function() {
    return $this->contadorService->resumenVentas($periodo);
});
```

---

## 📝 Notas Importantes

### Tabla DailyStatistics
**Campos Utilizados:**
- `date` - Fecha del registro
- `daily_revenue` - Ingresos diarios
- `daily_cost` - Costos diarios
- `daily_bill` - Gastos/facturas diarias
- `daily_sales` - Cantidad de ventas
- `new_customers` - Clientes nuevos
- `active_users` - Usuarios activos
- `accounts` - Cuentas totales

### Relaciones de Modelos
- `Venta` → `Cliente` (belongsTo)
- `Venta` → `DetalleVenta` (hasMany)
- `DetalleVenta` → `Servicio` (belongsTo)
- `Venta` → `Banco` (belongsTo)
- `Cliente` → `Ventas` (hasMany)

---

## 🚀 Próximos Pasos

1. **Implementar Autenticación de Empleados**
   - Crear `EmployeeAuthController`
   - Implementar middleware `auth:employee`
   - Implementar middleware `role:contador`

2. **Integración con Telegram Bot**
   - Comandos para cada endpoint
   - Formateo de respuestas para Telegram
   - Flujo conversacional

3. **Exportación de Reportes**
   - Implementar generación de PDF
   - Implementar exportación a Excel
   - Sistema de envío por email

4. **Cache y Performance**
   - Implementar caché de Redis
   - Optimizar queries pesadas
   - Background jobs para reportes grandes

---

**Implementación Completa** ✅  
**Fecha:** 15 de Enero de 2026  
**Versión:** 1.0
