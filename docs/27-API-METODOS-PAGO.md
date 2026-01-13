# API de Métodos de Pago

## Descripción
Endpoints de la API v2 que generan mensajes con información de métodos de pago (bancos) disponibles para realizar pagos.

## Endpoints

### 1. Obtener Todos los Métodos de Pago
```
GET /api/v2/metodos-pago
```

### 2. Obtener Banco Específico
```
GET /api/v2/banco/{id}
```

## Parámetros

### Para `/api/v2/metodos-pago`
No requiere parámetros.

### Para `/api/v2/banco/{id}`

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id` | integer | Sí | ID del banco (parámetro de ruta) |

## Tipos de Mensajes

### 1. Todos los Métodos de Pago
Genera un mensaje con todos los métodos de pago disponibles (todos los bancos).

**Ejemplo de uso:**
```bash
GET /api/v2/metodos-pago
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "mensaje": "*Para realizar pagos* 💰\n\n*Owner:* Pablo Darío Jiménez Elizalde\n*CI:* 1004549976\n*Mail:* pablojimenezelizalde@gmail.com\n\n*Cuenta de ahorros Banco Pichincha*\n2209859440\n\n*Cuenta de ahorros Produbanco*\n20001295622\n..."
  }
}
```

**Formato del mensaje:**
```
*Para realizar pagos* 💰

*Owner:* Pablo Darío Jiménez Elizalde
*CI:* 1004549976
*Mail:* pablojimenezelizalde@gmail.com

*Cuenta de ahorros Banco Pichincha*
2209859440

*Cuenta de ahorros Produbanco*
20001295622

*Cuenta Be Produbanco*
18001221307

*Cuenta de ahorros Banco Guayaquil*
33111385
...
```

### 2. Banco Específico
Genera un mensaje con la información de un banco específico.

**Ejemplo de uso:**
```bash
GET /api/v2/banco/1
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "mensaje": "*Información de Pago* 💰\n\n*Owner:* Pablo Darío Jiménez Elizalde\n*CI:* 1004549976\n*Mail:* pablojimenezelizalde@gmail.com\n\n*Cuenta de ahorros Banco Pichincha*\n2209859440\n"
  }
}
```

**Formato del mensaje:**
```
*Información de Pago* 💰

*Owner:* Pablo Darío Jiménez Elizalde
*CI:* 1004549976
*Mail:* pablojimenezelizalde@gmail.com

*Cuenta de ahorros Banco Pichincha*
2209859440

*Detalles:* Cuenta principal para pagos
```

## Respuestas de Error

### Error 404 - Not Found
Cuando el banco solicitado no existe:

```json
{
  "success": false,
  "message": "Banco no encontrado"
}
```

### Error 500 - Internal Server Error
Error en el servidor al procesar la solicitud:

```json
{
  "success": false,
  "message": "Error al generar el mensaje de métodos de pago",
  "error": "Detalles del error..."
}
```

## Ejemplos de Uso

### Con cURL

```bash
# Todos los métodos de pago
curl -X GET "http://tu-dominio.com/api/v2/metodos-pago"

# Banco específico
curl -X GET "http://tu-dominio.com/api/v2/banco/1"
```

### Con JavaScript (Fetch API)

```javascript
// Todos los métodos de pago
fetch('/api/v2/metodos-pago')
  .then(response => response.json())
  .then(data => {
    console.log(data.data.mensaje);
  });

// Banco específico
fetch('/api/v2/banco/1')
  .then(response => response.json())
  .then(data => {
    console.log(data.data.mensaje);
  });
```

### Con PHP (Laravel HTTP Client)

```php
use Illuminate\Support\Facades\Http;

// Todos los métodos de pago
$response = Http::get('http://tu-dominio.com/api/v2/metodos-pago');
$mensaje = $response->json()['data']['mensaje'];

// Banco específico
$response = Http::get('http://tu-dominio.com/api/v2/banco/1');
$mensaje = $response->json()['data']['mensaje'];
```

### Con Python (requests)

```python
import requests

# Todos los métodos de pago
response = requests.get('http://tu-dominio.com/api/v2/metodos-pago')
data = response.json()
print(data['data']['mensaje'])

# Banco específico
response = requests.get('http://tu-dominio.com/api/v2/banco/1')
data = response.json()
print(data['data']['mensaje'])
```

## Estructura de la Tabla Bancos

### Campos del Modelo Banco

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `idban` | integer | ID único del banco (Primary Key) |
| `nombreban` | string | Nombre del banco (ej: "Banco Pichincha") |
| `propietarioban` | string | Nombre del propietario de la cuenta |
| `cedulaban` | string | Cédula/CI del propietario |
| `numeroban` | string | Número de cuenta bancaria |
| `tipoban` | string | Tipo de cuenta (ej: "Cuenta de ahorros", "Cuenta Be") |
| `detalleban` | string | Detalles adicionales (opcional) |
| `foto` | string | Ruta de la foto/logo del banco (opcional) |
| `monto` | decimal | Saldo actual de la cuenta |

## Notas Importantes

1. **Sin Autenticación**: Este endpoint es público y no requiere API Key ni autenticación.

2. **Formato del Mensaje**: Los mensajes están formateados con markdown de WhatsApp (usando asteriscos para negrita).

3. **Información del Propietario**: La información del propietario (Owner, CI, Mail) se toma del primer banco en la lista o del banco específico consultado.

4. **Orden Alfabético**: Los bancos se ordenan alfabéticamente por nombre en el mensaje general.

5. **Bancos Disponibles**: Los bancos disponibles en el sistema son:
   - Banco Pichincha
   - Banco Guayaquil
   - Produbanco
   - Banco Internacional
   - Banco Bolivariano
   - Binance
   - PayPal

6. **Tipos de Cuenta**: Los tipos de cuenta pueden variar:
   - Cuenta de ahorros
   - Cuenta corriente
   - Cuenta Be
   - Billetera digital

## Casos de Uso

### 1. Bot de WhatsApp/Telegram
Cuando un cliente solicita información de pago, el bot puede llamar al endpoint y enviar el mensaje formateado con todos los métodos de pago disponibles.

### 2. Integración con n8n
Crear workflows automatizados que envíen información de pago cuando un cliente realiza una compra o solicita los datos bancarios.

### 3. Página de Pago
Mostrar dinámicamente en tu sitio web o app los métodos de pago disponibles, siempre actualizados desde la base de datos.

### 4. Notificaciones Automáticas
Después de que un cliente realice un pedido, enviar automáticamente la información de pago correspondiente.

### 5. Soporte al Cliente
Los agentes de soporte pueden consultar rápidamente la información de pago de un banco específico para compartirla con los clientes.

## Combinación con otros Endpoints

Puedes combinar este endpoint con el de precios para crear un mensaje completo:

```javascript
// Obtener precios y métodos de pago
async function obtenerInfoCompleta() {
  const [precios, pagos] = await Promise.all([
    fetch('/api/v2/precios?tipo=general').then(r => r.json()),
    fetch('/api/v2/metodos-pago?tipo=general').then(r => r.json())
  ]);
  
  const mensajeCompleto = precios.data.mensaje + '\n\n' + pagos.data.mensaje;
  return mensajeCompleto;
}
```

## Changelog

- **v1.0** (2026-01-13): Implementación inicial con 2 tipos de mensajes (general y banco específico).
