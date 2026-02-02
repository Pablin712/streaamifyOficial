# API de Estadísticas Diarias - Configuración con n8n

## 📋 Descripción

Esta API permite guardar automáticamente las estadísticas diarias del dashboard mediante n8n u otra herramienta de automatización.

**IMPORTANTE**: El método `guardar()` solo debe ejecutarse una vez al día mediante n8n (23:59), no cuando los empleados se loguean.

## 🚀 Endpoints (Sin autenticación)

### 1. Guardar estadísticas del día actual
```http
GET/POST /api/daily-statistics/save
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Estadísticas guardadas correctamente",
  "date": "2026-02-01",
  "data": {
    "active_users": 327,
    "accounts": 31,
    "daily_revenue": 0,
    "daily_cost": 0,
    "daily_bill": 0,
    "daily_sales": 0,
    "balance": 0
  }
}
```

### 2. Guardar estadísticas de una fecha específica
```http
POST /api/daily-statistics/save?date=2026-01-15
```

### 3. Guardar estadísticas de un rango de fechas
```http
POST /api/daily-statistics/save-range
Content-Type: application/json

{
  "start_date": "2026-01-01",
  "end_date": "2026-01-31"
}
```

### 4. Consultar estadísticas de una fecha
```http
GET /api/daily-statistics/2026-02-01
```

## ⚡ Configuración con n8n (RECOMENDADO)

### Crear workflow en n8n:

**1. Nodo Schedule Trigger**
- Modo: `Custom`
- Cron Expression: `59 23 * * *` (ejecutar a las 23:59 todos los días)
- Timezone: `America/Guayaquil` (GMT-5)

**2. Nodo HTTP Request**
- Method: `POST`
- URL: `https://streamify.aaronsoft.es/api/daily-statistics/save`
- Authentication: `None`
- Response Format: `JSON`

**3. Nodo IF (opcional) - Verificar éxito**
- Condition: `{{ $json.success }}` es igual a `true`

**4. Nodo Send Email/Telegram (opcional)**
- Solo se ejecuta si falla (para recibir alertas)

### JSON del workflow completo:

```json
{
  "name": "Guardar Estadísticas Diarias Streamify",
  "nodes": [
    {
      "parameters": {
        "rule": {
          "interval": [
            {
              "cronExpression": "59 23 * * *"
            }
          ]
        },
        "timezone": "America/Guayaquil"
      },
      "name": "Schedule - 23:59 Diario",
      "type": "n8n-nodes-base.scheduleTrigger",
      "position": [250, 300]
    },
    {
      "parameters": {
        "method": "POST",
        "url": "https://streamify.aaronsoft.es/api/daily-statistics/save",
        "options": {}
      },
      "name": "Guardar Estadísticas API",
      "type": "n8n-nodes-base.httpRequest",
      "position": [450, 300]
    },
    {
      "parameters": {
        "conditions": {
          "boolean": [
            {
              "value1": "={{ $json.success }}",
              "value2": true
            }
          ]
        }
      },
      "name": "¿Guardado exitoso?",
      "type": "n8n-nodes-base.if",
      "position": [650, 300]
    }
  ],
  "connections": {
    "Schedule - 23:59 Diario": {
      "main": [
        [
          {
            "node": "Guardar Estadísticas API",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Guardar Estadísticas API": {
      "main": [
        [
          {
            "node": "¿Guardado exitoso?",
            "type": "main",
            "index": 0
          }
        ]
      ]
    }
  }
}
```

### Importar en n8n:
1. Copia el JSON anterior
2. En n8n: Menú → Import from File/URL
3. Pega el JSON
4. Activa el workflow

## ⏰ Alternativas de configuración

### Opción 1: cPanel (para hosting compartido)

1. Accede a tu cPanel
2. Busca "Cron Jobs" o "Tareas Cron"
3. Agrega un nuevo cron job:

**Configuración:**
- **Minuto:** 59
- **Hora:** 23
- **Día:** *
- **Mes:** *
- **Día de la semana:** *

**Comando:**
```bash
curl -X POST "https://streamify.aaronsoft.es/api/daily-statistics/save" -H "Content-Type: application/json"
```

### Opción 2: Crontab Linux/Ubuntu

Edita el crontab:
```bash
crontab -e
```

Agrega esta línea para ejecutar a las 23:59 todos los días:
```bash
59 23 * * * curl -X POST "https://streamify.aaronsoft.es/api/daily-statistics/save" >/dev/null 2>&1
```

### Opción 3: Servicio externo (EasyCron, cron-job.org)

1. Registrate en https://cron-job.org o https://www.easycron.com
2. Crea un nuevo job:
   - URL: `https://streamify.aaronsoft.es/api/daily-statistics/save`
   - Método: POST
   - Frecuencia: Diaria a las 23:59
   - Zona horaria: America/Guayaquil (GMT-5)

## 🧪 Pruebas

### Probar manualmente el endpoint:

**Con cURL:**
```bash
curl -X POST "https://streamify.aaronsoft.es/api/daily-statistics/save"
```

**Con Postman:**
1. Método: POST
2. URL: `https://streamify.aaronsoft.es/api/daily-statistics/save`

**Desde el navegador:**
```
https://streamify.aaronsoft.es/api/daily-statistics/save
```

## 📊 Rellenar datos históricos

Si necesitas rellenar estadísticas de días pasados:

```bash
curl -X POST "https://streamify.aaronsoft.es/api/daily-statistics/save-range" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2026-01-01",
    "end_date": "2026-01-31"
  }'
```

## 🔍 Verificación

### Ver logs del servidor:
```bash
tail -f storage/logs/laravel.log
```

### Consultar último registro guardado:
```bash
curl "https://streamify.aaronsoft.es/api/daily-statistics/2026-02-01"
```

## ⚠️ Cambios importantes realizados

### 1. Removido guardar() automático:
- ❌ Ya NO se ejecuta en cada login de empleado
- ❌ Ya NO se ejecuta al abrir el dashboard
- ❌ Ya NO se ejecuta al generar PDFs
- ✅ SOLO se ejecuta mediante n8n a las 23:59

### 2. API sin autenticación:
- ✅ No requiere token (simplificado)
- ✅ Puede ejecutarse desde n8n, cPanel, o cualquier herramienta
- ✅ Acepta GET y POST

### 3. Correcciones en guardar():
1. ✅ **Ventas**: Usa `whereDate('fechaven')` en lugar de `created_at`
2. ✅ **Gastos**: Usa `whereDate('fechagas')` en lugar de `created_at`
3. ✅ **Retorno**: Devuelve array con datos guardados para verificación

## 🎯 Resultado esperado

Con n8n configurado:
- ✅ Cada día a las 23:59 se guardarán las estadísticas en `daily_statistics`
- ✅ Los reportes PDF mostrarán datos precisos de meses anteriores
- ✅ Los datos del dashboard y PDF serán consistentes
- ✅ Histórico completo para análisis de tendencias
- ✅ Datos más reales porque se capturan al final del día

## 📝 Notas importantes

- La API NO requiere autenticación (para simplificar integración con n8n)
- Los datos se guardan con `updateOrCreate`, evitando duplicados
- El límite para `save-range` es 90 días para evitar timeouts
- Todas las operaciones se registran en el log de Laravel
- El método `guardar()` ya NO se ejecuta en login ni al abrir dashboard

## 🔧 Comando Artisan (alternativa)

También puedes usar el comando Artisan para ejecutar manualmente:

### Guardar estadísticas de hoy:
```bash
php artisan estadisticas:guardar
```

### Guardar estadísticas de una fecha específica:
```bash
php artisan estadisticas:guardar --date=2026-01-15
```

### Guardar estadísticas de un rango de fechas:
```bash
php artisan estadisticas:guardar --start=2026-01-01 --end=2026-01-31
```
