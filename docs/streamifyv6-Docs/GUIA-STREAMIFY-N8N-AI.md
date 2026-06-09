# 🤖 Guía Completa: AI Assistant para Streamify con n8n + DeepSeek

## 📋 Descripción del Sistema

Sistema de chat AI automático que:
- ✅ Monitorea mensajes nuevos de clientes cada 30 segundos
- ✅ Responde automáticamente usando DeepSeek AI
- ✅ Obtiene contexto del cliente (ventas, servicios, precios)
- ✅ Deriva a humano cuando detecta solicitudes complejas
- ✅ Guarda todas las respuestas en la base de datos

---

## 🏗️ Arquitectura del Flujo

```
[Trigger: Cada 30s] 
    ↓
[GET /api/v1/chat/n8n/mensajes-pendientes] → ¿Hay mensajes?
    ↓ (Sí)
[Dividir por mensaje] 
    ↓
[Obtener Contexto] ← [Knowledge Base] [Servicios] [Ventas del Cliente]
    ↓
[DeepSeek AI] → Generar respuesta inteligente
    ↓
[POST /api/v1/chat/n8n/responder] → Guardar en BD
    ↓
[Detectar derivación] → ¿Requiere humano? 
    ↓ (Sí)
[POST /api/v1/chat/n8n/marcar-requiere-humano] → Notificar empleados
```

---

## 🔧 PASO 1: Configurar Credenciales en n8n

### 1.1 Credencial: Streamify API Key

1. En n8n → **Credentials** → **Create New**
2. Tipo: **HTTP Header Auth**
3. Configurar:
   ```
   Name: Streamify API Key
   Header Name: X-API-Key
   Header Value: sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X
   ```
4. **Save**

### 1.2 Credencial: DeepSeek API

1. **Credentials** → **Create New**
2. Tipo: **DeepSeek API** (o **HTTP Header Auth** si no existe)
3. Configurar:
   ```
   Name: DeepSeek Account
   API Key: TU_API_KEY_DE_DEEPSEEK
   ```
   
   **¿Dónde obtener la API Key?**
   - Ve a https://platform.deepseek.com
   - Inicia sesión / Regístrate
   - Ve a **API Keys** → **Create API Key**
   - Copia la key (ej: `sk-1234567890abcdef...`)

4. **Save**

---

## 📥 PASO 2: Importar el Flujo

### Opción A: Importar JSON

1. En n8n → **Workflows** → **Import from File**
2. Selecciona: `docs/FLUJO-N8N-STREAMIFY-AI.json`
3. El flujo se importa con todos los nodos configurados

### Opción B: Crear Manualmente (si prefieres entender cada paso)

#### Nodo 1: Trigger - Ejecutar cada 30 segundos
```
Tipo: Schedule Trigger
Interval: Every 30 seconds
```

#### Nodo 2: Obtener Mensajes Pendientes
```
Tipo: HTTP Request
Method: GET
URL: http://localhost:8000/api/v1/chat/n8n/mensajes-pendientes?limit=5
Authentication: Streamify API Key
Headers:
  Accept: application/json
```

#### Nodo 3: ¿Hay mensajes?
```
Tipo: IF
Condition: {{ $json.count }} > 0
```

#### Nodo 4: Dividir por Mensaje
```
Tipo: Split In Batches
Mode: Each item
Item Property: data
```

#### Nodo 5: Obtener Knowledge Base
```
Tipo: HTTP Request
Method: GET
URL: http://localhost:8000/api/v1/public/ai/knowledge-base
```

#### Nodo 6: Obtener Servicios
```
Tipo: HTTP Request
Method: GET
URL: http://localhost:8000/api/v1/public/ai/servicios
```

#### Nodo 7: Obtener Ventas del Cliente
```
Tipo: HTTP Request
Method: GET
URL: http://localhost:8000/api/v1/ai/cliente/{{ $('Dividir por Mensaje').item.json.cliente.idcli }}/ventas
Authentication: Streamify API Key
```

#### Nodo 8: Preparar Contexto
```
Tipo: Set (Edit Fields)
Fields:
  - knowledgeBase: {{ JSON.stringify($('Obtener Knowledge Base').item.json.data) }}
  - servicios: {{ JSON.stringify($('Obtener Servicios').item.json.data) }}
  - ventasCliente: {{ JSON.stringify($('Obtener Ventas del Cliente').item.json.data) }}
  - mensajeCliente: {{ $('Dividir por Mensaje').item.json.mensaje.contenido }}
  - nombreCliente: {{ $('Dividir por Mensaje').item.json.cliente.nombre }}
  - idconv: {{ $('Dividir por Mensaje').item.json.idconv }}
  - idmsg: {{ $('Dividir por Mensaje').item.json.idmsg }}
```

#### Nodo 9: DeepSeek AI
```
Tipo: HTTP Request
Method: POST
URL: https://api.deepseek.com/v1/chat/completions
Authentication: DeepSeek API

Body (JSON):
{
  "model": "deepseek-chat",
  "messages": [
    {
      "role": "system",
      "content": "Eres el asistente virtual de Streamify, empresa ecuatoriana de servicios de streaming compartidos.

INFORMACIÓN DE LA EMPRESA:
{{ $json.knowledgeBase }}

SERVICIOS DISPONIBLES:
{{ $json.servicios }}

VENTAS DEL CLIENTE {{ $json.nombreCliente }}:
{{ $json.ventasCliente }}

INSTRUCCIONES IMPORTANTES:
1. Sé amable, profesional y conciso (máximo 3 párrafos cortos)
2. Llama al cliente por su nombre: {{ $json.nombreCliente }}
3. Si preguntan por PRECIOS, menciona los 5 tipos disponibles claramente
4. Si preguntan por RENOVACIÓN y el cliente tiene ventas activas, menciona sus servicios actuales
5. Si preguntan por disponibilidad de perfiles, indica que deben consultar disponibilidad actual
6. Para PAGOS, indica los métodos: transferencia bancaria, PayPal, Binance
7. Usa emojis apropiadamente: 🎬📺💳✅
8. Si detectas solicitudes de: renovación, pago, problema técnico grave → deriva a humano
9. NUNCA inventes precios, servicios o información que no esté en el contexto
10. Si no sabes algo, di: 'Déjame derivarte con un asesor para ayudarte mejor 👨‍💼'

FRASES PARA DERIVAR A HUMANO:
- 'quiero hablar con una persona'
- 'necesito ayuda urgente'
- 'tengo un problema'
- 'no funciona'
- 'quiero renovar'
- 'quiero pagar'

Si detectas alguna de estas frases, responde educadamente y SIEMPRE termina con:
'🔔 Te estoy derivando con un asesor humano que te atenderá enseguida.'"
    },
    {
      "role": "user",
      "content": "{{ $json.mensajeCliente }}"
    }
  ],
  "temperature": 0.7,
  "max_tokens": 400,
  "top_p": 0.9
}
```

#### Nodo 10: Enviar Respuesta a Streamify
```
Tipo: HTTP Request
Method: POST
URL: http://localhost:8000/api/v1/chat/n8n/responder
Authentication: Streamify API Key

Body (JSON):
{
  "idconv": "{{ $('Preparar Contexto').item.json.idconv }}",
  "idmsg": "{{ $('Preparar Contexto').item.json.idmsg }}",
  "contenido": "{{ $('DeepSeek AI').item.json.choices[0].message.content }}",
  "metadata": {
    "model": "deepseek-chat",
    "tokens": {{ $('DeepSeek AI').item.json.usage.total_tokens }},
    "finish_reason": "{{ $('DeepSeek AI').item.json.choices[0].finish_reason }}"
  }
}
```

#### Nodo 11: ¿Requiere Humano?
```
Tipo: IF
Condition: {{ $('DeepSeek AI').item.json.choices[0].message.content.toLowerCase() }} contains 'asesor humano'
```

#### Nodo 12: Marcar para Humano
```
Tipo: HTTP Request
Method: POST
URL: http://localhost:8000/api/v1/chat/n8n/marcar-requiere-humano
Authentication: Streamify API Key

Body (JSON):
{
  "idconv": "{{ $('Preparar Contexto').item.json.idconv }}",
  "razon": "Cliente derivado por AI - requiere atención personalizada"
}
```

---

## 🔗 PASO 3: Conectar los Nodos

Conexiones:
```
1. Trigger → 2. Obtener Mensajes
2. Obtener Mensajes → 3. ¿Hay mensajes?
3. ¿Hay mensajes? (TRUE) → 4. Dividir por Mensaje
4. Dividir → 5. Knowledge Base (paralelo)
4. Dividir → 6. Servicios (paralelo)
4. Dividir → 7. Ventas Cliente (paralelo)
5, 6, 7 → 8. Preparar Contexto (merge)
8. Preparar → 9. DeepSeek AI
9. DeepSeek → 10. Enviar Respuesta (paralelo)
9. DeepSeek → 11. ¿Requiere Humano? (paralelo)
11. ¿Requiere Humano? (TRUE) → 12. Marcar para Humano
```

---

## ✅ PASO 4: Probar el Flujo

### 4.1 Crear Mensaje de Prueba

Desde tu frontend de Streamify (o Postman):

```bash
# PowerShell
$headers = @{
    "Content-Type" = "application/json"
}

$body = @{
    idcli = "001"  # Reemplaza con ID de cliente real
    contenido = "Hola, cuánto cuesta Netflix?"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/v1/chat/cliente/enviar" `
                  -Method POST `
                  -Headers $headers `
                  -Body $body
```

### 4.2 Verificar que el Flujo se Ejecute

1. En n8n, **activa** el workflow (toggle arriba a la derecha)
2. Espera 30 segundos máximo
3. Ve a **Executions** (panel lateral)
4. Verás una nueva ejecución verde ✅

### 4.3 Ver la Respuesta en tu Base de Datos

```sql
-- Ver la conversación
SELECT * FROM conversaciones WHERE idcli = '001' ORDER BY created_at DESC LIMIT 1;

-- Ver los mensajes
SELECT * FROM mensajes WHERE idconv = 'XXX' ORDER BY created_at DESC;
```

Deberías ver:
- **Mensaje 1** (cliente): "Hola, cuánto cuesta Netflix?"
- **Mensaje 2** (bot): "¡Hola [Nombre]! 😊 Netflix tiene varios planes..."

---

## 🎯 PASO 5: Integrar con tu Frontend

### Ejemplo en JavaScript (Vue/React)

```javascript
// Enviar mensaje del cliente
async function enviarMensaje(clienteId, mensaje) {
  const response = await fetch('http://localhost:8000/api/v1/chat/cliente/enviar', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      idcli: clienteId,
      contenido: mensaje
    })
  });
  
  return await response.json();
}

// Obtener conversación del cliente (incluye respuesta del bot)
async function obtenerConversacion(clienteId) {
  const response = await fetch(`http://localhost:8000/api/v1/chat/cliente/${clienteId}/conversacion`);
  return await response.json();
}

// Polling para nuevos mensajes cada 3 segundos
setInterval(async () => {
  const conversacion = await obtenerConversacion(clienteId);
  // Actualizar UI con nuevos mensajes
  actualizarChat(conversacion.data.mensajes);
}, 3000);
```

### Ejemplo Livewire (si usas Livewire en tu frontend)

```php
// app/Livewire/ChatCliente.php
class ChatCliente extends Component
{
    public $mensajes = [];
    public $nuevoMensaje = '';
    
    public function mount()
    {
        $this->cargarMensajes();
    }
    
    public function cargarMensajes()
    {
        $cliente = auth()->user(); // Asumiendo que el cliente está autenticado
        $conversacion = Conversacion::where('idcli', $cliente->idcli)
            ->with('mensajes')
            ->first();
        
        $this->mensajes = $conversacion ? $conversacion->mensajes : [];
    }
    
    public function enviarMensaje()
    {
        $cliente = auth()->user();
        
        // Llamar al endpoint
        Http::post('http://localhost:8000/api/v1/chat/cliente/enviar', [
            'idcli' => $cliente->idcli,
            'contenido' => $this->nuevoMensaje
        ]);
        
        $this->nuevoMensaje = '';
        
        // Recargar después de 2 segundos para ver respuesta del bot
        $this->dispatch('$refresh')->delay(2000);
    }
    
    // Livewire polling cada 5 segundos
    public function render()
    {
        $this->cargarMensajes();
        return view('livewire.chat-cliente');
    }
}
```

```blade
{{-- resources/views/livewire/chat-cliente.blade.php --}}
<div wire:poll.5s>
    <div class="mensajes-container">
        @foreach($mensajes as $mensaje)
            <div class="mensaje {{ $mensaje->tipo_remitente }}">
                <strong>
                    @if($mensaje->tipo_remitente === 'cliente')
                        Tú
                    @elseif($mensaje->tipo_remitente === 'bot')
                        🤖 Asistente Streamify
                    @else
                        👨‍💼 {{ $mensaje->empleado->nombreemp }}
                    @endif
                </strong>
                <p>{{ $mensaje->contenido }}</p>
                <small>{{ $mensaje->created_at->diffForHumans() }}</small>
            </div>
        @endforeach
    </div>
    
    <form wire:submit.prevent="enviarMensaje">
        <input type="text" wire:model="nuevoMensaje" placeholder="Escribe tu mensaje...">
        <button type="submit">Enviar</button>
    </form>
</div>
```

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### ❌ Error: "No mensajes pendientes"

**Causa**: No hay conversaciones abiertas sin responder

**Solución**: Crea un mensaje de prueba con el script del paso 4.1

### ❌ Error: "401 Unauthorized"

**Causa**: API Key incorrecta o no configurada

**Solución**:
1. Verifica la credencial en n8n
2. Asegúrate que sea: `sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X`
3. Verifica que el header sea `X-API-Key`

### ❌ Error: "DeepSeek API error 429"

**Causa**: Límite de rate excedido

**Solución**:
1. Aumenta el intervalo del trigger a 60 segundos
2. Reduce el `limit` de mensajes pendientes a 3
3. Verifica tu límite en https://platform.deepseek.com/usage

### ❌ Error: "Connection refused localhost:8000"

**Causa**: Laravel no está corriendo o n8n no puede acceder

**Solución**:

```powershell
# Opción 1: Verificar Laravel
php artisan serve

# Opción 2: Si n8n está en Docker/Cloud, usa ngrok
ngrok http 8000

# Luego actualiza las URLs en n8n:
# De: http://localhost:8000
# A: https://abc123.ngrok.io
```

### ❌ Bot responde infinitamente

**Causa**: El bot responde sus propios mensajes

**Solución**: Ya está controlado en el endpoint `mensajesPendientesParaAI()` que filtra:
- Solo mensajes con `tipo_remitente = 'cliente'`
- Solo mensajes con `respondido_por_ai = false`

Si aún pasa, verifica que el código en ChatController tenga este filtro.

---

## 📊 MONITOREO

### Ver Estadísticas del Chat

```bash
# PowerShell
$headers = @{
    "X-API-Key" = "sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X"
}

Invoke-RestMethod -Uri "http://localhost:8000/api/v1/chat/estadisticas" `
                  -Headers $headers
```

Retorna:
```json
{
  "conversaciones_abiertas": 12,
  "conversaciones_bot_activo": 8,
  "requieren_atencion_humana": 4,
  "mensajes_hoy": 156,
  "respuestas_ai_hoy": 98,
  "tiempo_respuesta_promedio": "2.3s"
}
```

### Logs de n8n

1. Ve a **Executions** en n8n
2. Clic en cada ejecución para ver detalles
3. Puedes ver:
   - Cuántos mensajes se procesaron
   - Qué contexto se obtuvo
   - La respuesta generada por DeepSeek
   - Si se derivó a humano

---

## 🎉 MEJORAS OPCIONALES

### 1. Agregar Indicador de "Escribiendo..."

Cuando el bot está generando respuesta:

```javascript
// En el nodo "Enviar Respuesta", antes de DeepSeek, agrega:
// HTTP Request a Streamify para marcar "bot está escribiendo"

POST http://localhost:8000/api/v1/chat/conversaciones/{idconv}/escribiendo
Body: { "escribiendo": true, "quien": "bot" }

// Luego después de enviar respuesta:
POST http://localhost:8000/api/v1/chat/conversaciones/{idconv}/escribiendo
Body: { "escribiendo": false }
```

### 2. Agregar Análisis de Sentimiento

Agrega un nodo adicional después de "Preparar Contexto":

```javascript
// Nodo: Code
const mensaje = $('Preparar Contexto').item.json.mensajeCliente.toLowerCase();

// Detectar urgencia
const esUrgente = /urgente|ayuda|problema|no funciona/.test(mensaje);

// Detectar satisfacción
const esPositivo = /gracias|excelente|perfecto|bien/.test(mensaje);
const esNegativo = /malo|terrible|pésimo|horrible/.test(mensaje);

return [{
  json: {
    ...($input.item.json),
    analisis: {
      urgente: esUrgente,
      sentimiento: esPositivo ? 'positivo' : esNegativo ? 'negativo' : 'neutral'
    }
  }
}];
```

Luego en el prompt de DeepSeek agrega:
```
ANÁLISIS DEL MENSAJE:
Urgencia: {{ $json.analisis.urgente ? 'ALTA' : 'Normal' }}
Sentimiento: {{ $json.analisis.sentimiento }}

Si la urgencia es ALTA, prioriza derivar a humano.
```

### 3. Agregar Respuestas Rápidas (Quick Responses)

Si el cliente escribe exactamente `/precios`, `/servicios`, `/bancos`:

```javascript
// Nodo: Code - antes de DeepSeek
const mensaje = $('Preparar Contexto').item.json.mensajeCliente.trim();

// Si comienza con /, es un comando
if (mensaje.startsWith('/')) {
  const comando = mensaje.substring(1).toLowerCase();
  
  // Llamar a quick responses
  const response = await this.helpers.httpRequest({
    method: 'GET',
    url: `http://localhost:8000/api/v1/public/quick-responses/comando/${comando}`,
  });
  
  if (response.success) {
    // Usar respuesta rápida directamente (sin AI)
    return [{
      json: {
        respuestaRapida: response.data.contenido,
        saltarAI: true
      }
    }];
  }
}

// Si no es comando, continuar normal
return $input.all();
```

---

## 🚀 ¡LISTO!

Tu sistema AI está configurado. Ahora:

1. ✅ Los clientes envían mensajes desde tu frontend
2. ✅ n8n revisa cada 30 segundos si hay mensajes nuevos
3. ✅ DeepSeek genera respuestas inteligentes con contexto del cliente
4. ✅ Las respuestas se guardan automáticamente en tu BD
5. ✅ Si se requiere humano, la conversación se marca para empleados

**Próximos pasos**:
- Personaliza el prompt de DeepSeek según tus necesidades
- Ajusta el intervalo del trigger (30s, 60s, etc.)
- Agrega notificaciones push cuando se derive a humano
- Implementa el frontend del chat para clientes

---

## 📞 SOPORTE

Si tienes dudas:
1. Revisa los logs de n8n (Executions)
2. Verifica que Laravel esté corriendo
3. Prueba los endpoints manualmente con PowerShell:
   ```powershell
   .\test-ai-endpoints.ps1
   ```

**¡Sistema AI listo para Streamify! 🎬🤖**
