# API de Mensajes de Precios

## Descripción
Endpoint de la API v2 que genera mensajes de precios formateados, similar a las funciones de "copiar mensaje" de la vista de productos.

## Endpoint
```
GET /api/v2/precios
```

## Parámetros

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción | Valores |
|-----------|------|-----------|-------------|---------|
| `tipo` | string | No | Tipo de mensaje a generar | `general`, `productos`, `combos`, `servicio` |
| `servicio_id` | string | Condicional* | ID del servicio (solo para tipo=servicio) | Ej: `NETFLIX`, `MAX`, `SPOTIFY` |

*\* Requerido solo cuando `tipo=servicio`*

## Tipos de Mensajes

### 1. General (`tipo=general`)
Genera un mensaje con los precios de todos los servicios configurados (Netflix, Disney+, HBO Max, etc.)

**Ejemplo de uso:**
```bash
GET /api/v2/precios?tipo=general
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "tipo": "general",
    "mensaje": "*Precios 1 mes 1 disp*\nNetflix: $15.99\nDisney+ Premium: $12.99\nHBO Max: $14.99\n..."
  }
}
```

### 2. Productos Individuales (`tipo=productos`)
Genera un mensaje con productos de la categoría "Individual" con planes de 1 mes.

**Ejemplo de uso:**
```bash
GET /api/v2/precios?tipo=productos
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "tipo": "productos",
    "mensaje": "*Precios 1 mes 1 disp*\nNetflix Premium: $15.99\nSpotify Individual: $9.99\n..."
  }
}
```

### 3. Combos (`tipo=combos`)
Genera un mensaje con productos de la categoría "Combos" con planes de 1 mes.

**Ejemplo de uso:**
```bash
GET /api/v2/precios?tipo=combos
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "tipo": "combos",
    "mensaje": "*Combos 1 mes 1 disp*\nCombo Streaming: $29.99\nCombo Familiar: $39.99\n..."
  }
}
```

### 4. Planes de Servicio Específico (`tipo=servicio`)
Genera un mensaje con todos los planes disponibles para un servicio específico.

**Ejemplo de uso:**
```bash
GET /api/v2/precios?tipo=servicio&servicio_id=NETFLIX
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "tipo": "servicio",
    "mensaje": "*Planes de Netflix*\n\nNetflix Premium:\n  - 1 mes(es): $15.99\n  - 3 mes(es): $42.99\n..."
  }
}
```

## Respuestas de Error

### Error 400 - Bad Request
Cuando falta un parámetro requerido o el tipo no es válido:

```json
{
  "success": false,
  "message": "Tipo de mensaje no válido. Tipos disponibles: general, productos, combos, servicio"
}
```

```json
{
  "success": false,
  "message": "Se requiere el parámetro servicio_id para este tipo"
}
```

### Error 500 - Internal Server Error
Error en el servidor al procesar la solicitud:

```json
{
  "success": false,
  "message": "Error al generar el mensaje de precios",
  "error": "Detalles del error..."
}
```

## Ejemplos de Uso

### Con cURL

```bash
# Mensaje general
curl -X GET "http://tu-dominio.com/api/v2/precios?tipo=general"

# Productos individuales
curl -X GET "http://tu-dominio.com/api/v2/precios?tipo=productos"

# Combos
curl -X GET "http://tu-dominio.com/api/v2/precios?tipo=combos"

# Planes de Netflix
curl -X GET "http://tu-dominio.com/api/v2/precios?tipo=servicio&servicio_id=NETFLIX"
```

### Con JavaScript (Fetch API)

```javascript
// Mensaje general
fetch('/api/v2/precios?tipo=general')
  .then(response => response.json())
  .then(data => {
    console.log(data.data.mensaje);
  });

// Planes de un servicio
fetch('/api/v2/precios?tipo=servicio&servicio_id=SPOTIFY')
  .then(response => response.json())
  .then(data => {
    console.log(data.data.mensaje);
  });
```

### Con PHP (Laravel HTTP Client)

```php
use Illuminate\Support\Facades\Http;

// Mensaje de combos
$response = Http::get('http://tu-dominio.com/api/v2/precios', [
    'tipo' => 'combos'
]);

$mensaje = $response->json()['data']['mensaje'];
```

### Con Python (requests)

```python
import requests

# Planes de servicio
response = requests.get('http://tu-dominio.com/api/v2/precios', params={
    'tipo': 'servicio',
    'servicio_id': 'MAX'
})

data = response.json()
print(data['data']['mensaje'])
```

## Notas Importantes

1. **Sin Autenticación**: Este endpoint es público y no requiere API Key ni autenticación.

2. **Formato del Mensaje**: Los mensajes están formateados con markdown de WhatsApp (usando asteriscos para negrita).

3. **Servicios Configurados**: Los servicios disponibles son:
   - NETFLIX
   - DISNEYP (Disney+ Premium)
   - DISNEYS (Disney+ Standard)
   - MAX (HBO Max)
   - PRIME (Amazon Prime)
   - PARAMOUNT
   - CRUNCHY (Crunchyroll)
   - SPOTIFY
   - MAGIS (Magis TV)

4. **Categorías de Productos**: 
   - "Individual" para productos individuales
   - "Combos" para productos combinados

5. **Filtro por Meses**: Los mensajes de productos y combos filtran automáticamente por planes de 1 mes.

## Casos de Uso

### 1. Bot de WhatsApp/Telegram
Puedes usar este endpoint para que tu bot genere automáticamente mensajes de precios actualizados.

### 2. Integración con n8n
Crear workflows que obtengan precios y los envíen a clientes de forma automatizada.

### 3. Panel de Administración
Mostrar precios actualizados en tiempo real en tu panel administrativo.

### 4. Notificaciones Programadas
Enviar mensajes de precios a clientes de forma periódica.

## Changelog

- **v1.0** (2026-01-13): Implementación inicial con 4 tipos de mensajes.
