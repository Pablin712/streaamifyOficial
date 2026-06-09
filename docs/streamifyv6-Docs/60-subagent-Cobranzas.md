# Subagent: Cobranzas y Pago (cobranzas_pago)

**Tipo:** `cobranzas`  
**Prioridad:** 40  
**Estado:** Operativo ligero (solo metodos de pago + bancos)  
**Responsable:** Router general → Detecta intención de pago/comprobante

---

## 1. Definición y rol

El subagente **cobranzas_pago** gestiona todo lo relacionado con métodos de pago, validación de comprobantes y confirmación de transacciones.

### Intenciones que disparan este subagent

- Cliente dice: "cómo pago?", "métodos de pago?", "qué bancos?", "transferencia?", "tarjeta?"
- Cliente envía comprobante de pago
- Cliente pregunta estado de su pago/recarga
- Cliente solicita datos bancarios para transferencia

### Diferencia con vendedor_cierre

- **vendedor_cierre** (prioridad 20): cierra la venta, sugiere plan, ofrece CTA de compra
- **cobranzas_pago** (prioridad 40): el cliente YA DECIDIÓ comprar; ahora necesita instrucciones de pago y validación

---

## 2. Modelo de operación

### Flujo típico

1. **Cliente expresa intención de pago** → Router lo detecta → Envía a cobranzas_pago
2. **Subagent consulta métodos disponibles** → GET `/api/v2/chat/assistant/cobranzas/metodos-pago`
3. **Subagent enruta según método**:
   - **Transferencia bancaria** → Solicita comprobante, valida después
   - **Tarjeta de crédito** → Link de pago directo (si existe)
   - **Billetera virtual** → Link directo o instrucciones
   - **Efectivo en agencia** → Dirección y códigos de verificación
4. **Cliente envía comprobante** (si es transferencia) → Subagent deriva al flujo existente de subidor/verificador
5. **Empleado valida** en panel (payments/n8n) → Confirma acreditación al cliente
6. **Cierre de flujo** → Mensaje de éxito + siguiente paso (acceso a servicio, entrega de credenciales, etc.)

### Escenarios comunes

| Escenario | Acción del Subagent |
|-----------|-------------------|
| Cliente pide métodos | Consulta `/metodos-pago` y presenta opciones claras |
| Cliente pide datos bancarios | Consulta `/bancos` (nombre, número, titular, email) |
| Cliente envía foto de comprobante | Deriva al subidor/verificador de pagos existente |
| Subagent sospecha comprobante falso | No valida; deriva al flujo verificador |
| Cliente pregunta "ya se acreditó?" | Consulta estado de pago (si existe endpoint) |
| Cliente dice "no sé transferir" | Handoff a humano o sugerencia de tarjeta/billetera |

---

## 3. APIs requeridas

### Métodos de pago disponibles

```
GET /api/v2/chat/assistant/cobranzas/metodos-pago
Response:
{
  "metodos": [
    {
      "id": "1",
      "nombre": "Transferencia Bancaria",
      "descripcion": "Transferencia directa a nuestras cuentas",
      "bancos_soportados": [ "Banco de Crédito", "Interbank", "BCP" ],
      "tiempo_acreditacion": "1-2 minutos",
      "visible_para_nuevo_cliente": true
    },
    {
      "id": "2",
      "nombre": "Tarjeta de Crédito",
      "descripcion": "Pago instantáneo con tarjeta",
      "link_pago": "https://payment.streamify.com/checkout",
      "tiempo_acreditacion": "inmediato",
      "visible_para_nuevo_cliente": true
    },
    {
      "id": "3",
      "nombre": "Billetera Virtual",
      "descripcion": "Yape, Plin, etc.",
      "link_pago": "https://payment.streamify.com/wallet",
      "tiempo_acreditacion": "inmediato",
      "visible_para_nuevo_cliente": false
    }
  ],
  "mensaje_general": "Selecciona el método que prefieras",
  "tiempo_respuesta_media": "30 minutos (transferencia)"
}
```

### Bancos y datos de transferencia

```
GET /api/v2/chat/assistant/cobranzas/bancos?filtro=nombre
Response:
{
  "bancos": [
    {
      "id": "1",
      "nombre": "Banco de Crédito",
      "numero_cuenta": "19234567890",
      "tipo_cuenta": "Corriente",
      "titular": "Streamify SAC",
      "cci": "002391234567890123",
      "email_notificacion": "pagos@streamify.com",
      "instrucciones": "Referencia: Tu teléfono o ID cliente"
    }
  ]
}
```

### Comprobantes (flujo existente)

La recepcion y validacion de comprobantes NO se maneja desde este subagente.

Se reutiliza el flujo existente de pagos:

- Subidor: `POST /api/v2/payments/n8n/receipt-intake`
- Verificador: `POST /api/v2/payments/n8n/recargas/{idrec}/aprobar` o `/rechazar`

### Validar comprobante (empleado)

```
POST /api/v2/payments/n8n/recargas/{idrec}/aprobar (flujo empleado)
Body:
{
  "referencia_externa": "string (opcional)",
  "notas_validacion": "string"
}

Response:
{
  "success": true,
  "notificacion_enviada": true,
  "datos": {
    "idcli": "integer",
    "idrec": "integer",
    "estado": "aprobado|rechazado",
    "proximos_pasos": "string"
  }
}
```

---

## 4. System message y criterios

### System message para DeepSeek

```
Eres un especialista en cobranzas amable pero profesional.

Tu rol es:
1. Presentar métodos de pago de forma clara y sin presión
2. Guiar al cliente a través del método que elige
3. Derivar comprobantes al flujo ya existente de subidor/verificador
4. Evitar duplicar procesos de validación

Estilo:
- Mensajes cortos (máx 3-4 líneas)
- Tono seguro y competente
- Claridad absoluta en instrucciones
- Máximo 1 emoji por mensaje
- Nunca expongas detalles internos (como "validando en base de datos")

Flujo de pensamiento:
1. ¿Cliente pregunta método? → Consulta GET /metodos-pago y presenta opciones
2. ¿Cliente elige transferencia? → Consulta GET /bancos y muestra datos (número, titular, CCI)
3. ¿Cliente elige tarjeta? → Envía link de pago directo
4. ¿Cliente envía comprobante? → Deriva al flujo /payments/n8n/receipt-intake
5. ¿Cliente inseguro? → Ofrece tarjeta como alternativa; si rechaza → handoff a humano

Comunicación al gestionar comprobante:
- Confirma: "Te ayudo a enviarlo por el canal de verificación ✓"
- Informa: "Luego el equipo lo valida"
- Nunca digas: "Base de datos", "sistema", "procesamiento", detalles técnicos
- Siempre cierra con: "¿Hay algo más en lo que te pueda ayudar?"

Escalado a humano:
- Si cliente dice: "no sé cómo pagar", "no tengo acceso", "problema con banco"
- Ofrece: "Te conecto con un asesor que te puede guiar mejor"
- NO dejes al cliente confundido; handoff inmediato

Validación de comprobante:
- Tu rol es DERIVAR, no validar
- El flujo payments/n8n recibe y el empleado valida

SALIDA OBLIGATORIA:
- Devuelve SIEMPRE solo JSON valido.
- No devuelvas markdown, explicaciones ni texto fuera del JSON.
- Usa exactamente la estructura minima definida abajo.

Estructura JSON minima obligatoria:
{
  "subagente_codigo": "cobranzas_pago",
  "reply_text": "texto final para cliente",
  "accion_tipo": "ninguna|mostrar_metodos|mostrar_bancos|enviar_link_pago|derivar_comprobante|handoff",
  "accion_requerida": false,
  "accion_payload": null,
  "escalar_humano": false,
  "motivo_humano": null,
  "confianza": 0.9
}

Reglas para el JSON:
- reply_text: siempre obligatorio y listo para enviar al cliente.
- accion_requerida: true cuando n8n debe ejecutar una accion.
- accion_payload: objeto cuando accion_requerida=true, null en caso contrario.
- escalar_humano: true solo cuando accion_tipo=handoff.
- confianza: numero entre 0 y 1.
```

### Criterios de activación

```yaml
intenciones:
  - "pagar"
  - "comprobante"
  - "transferencia"
  - "banco"
  - "tarjeta"
  - "billetera"
  - "método pago"
  - "cómo pago"

estado_cliente:
  - "cliente_activo"
  - "cliente_reciente"
  - "nuevo_cliente_con_intención_compra"

no_activar_si:
  - "cliente_pregunta_soporte" (→ soporte_cliente)
  - "cliente_pregunta_precio" (→ vendedor_cierre)
  - "cliente_pide_humano" (→ espera_humano)
```

---

## 5. Tools y acciones

### Tools disponibles (Modelo 1: Sin servidor externo)

```json
{
  "tools": [
    {
      "name": "consultar_metodos_pago",
      "description": "Obtiene la lista de métodos de pago disponibles",
      "endpoint": "GET /api/v2/chat/assistant/cobranzas/metodos-pago"
    },
    {
      "name": "consultar_bancos",
      "description": "Obtiene datos bancarios para transferencia (CCI, número, titular)",
      "endpoint": "GET /api/v2/chat/assistant/cobranzas/bancos",
      "params": {
        "filtro": "string (opcional: nombre del banco)"
      }
    },
    {
      "name": "handoff_humano",
      "description": "Escala la conversación a un empleado de cobranzas o atención al cliente",
      "endpoint": "POST /api/v2/chat/router/handoff",
      "params": {
        "razon": "string (ej: cliente_confundido, metodo_no_disponible)",
        "notas": "string (contexto para el empleado)"
      }
    }
  ]
}
```

### Acciones del subagent

| Acción | Trigger | Payload |
|--------|---------|---------|
| **mostrar_metodos** | Cliente pregunta "cómo pago?" | Resultado de GET /metodos-pago + opciones claras |
| **mostrar_bancos** | Cliente elige "transferencia" | Datos de CCI, número, titular, instrucciones |
| **enviar_link_pago** | Cliente elige "tarjeta" | Link directo a checkout (si existe) |
| **derivar_comprobante** | Cliente envía foto/número comprobante | Redirigir a flujo existente /payments/n8n/receipt-intake |
| **confirmar_derivacion** | Derivación exitosa | Mensaje: "Perfecto, ya te guié por el canal de verificación." |
| **handoff_humano** | Cliente confundido o método no disponible | Escala a empleado de cobranzas |

---

## 6. Integración con n8n

### Nodo cobranzas_pago

En n8n, crear nodo con:

**Entrada:**
```json
{
  "mensaje_agrupado": "string (último mensaje del cliente)",
  "historial": ["msg1", "msg2", ...],
  "contexto": {
    "idcli": "integer",
    "telefono": "string",
    "intencion": "pagar|comprobante|etc",
    "monto_esperado": "decimal (si viene de venta anterior)"
  },
  "memoria_negocio": "playbooks de cobranzas (desde chat_memoria_negocio)"
}
```

**System message:**
```
[Sistema base de cobranzas_pago (ver 4. System message)]
```

### Prompt recomendado (listo para copiar en n8n)

```text
Atiende este caso de cobranzas y devuelve SOLO JSON valido.

mensaje_agrupado: {{ $('get context').item.json.data.mensaje_agrupado }}
historial: {{ JSON.stringify($('get context').item.json.data.historial_reciente) }}
contacto: {{ JSON.stringify($('get context').item.json.data.contacto) }}
conversacion: {{ JSON.stringify($('get context').item.json.data.conversacion) }}

Contexto operativo:
- Este subagente SOLO cubre metodos de pago y consulta de bancos.
- No registra ni valida comprobantes dentro de chat assistant.
- Si cliente envia comprobante, orientar y derivar al flujo existente: /api/v2/payments/n8n/receipt-intake.

Devuelve exclusivamente este JSON minimo:
{
  "subagente_codigo": "cobranzas_pago",
  "reply_text": "texto final para cliente",
  "accion_tipo": "ninguna|mostrar_metodos|mostrar_bancos|enviar_link_pago|derivar_comprobante|handoff",
  "accion_requerida": false,
  "accion_payload": null,
  "escalar_humano": false,
  "motivo_humano": null,
  "confianza": 0.9
}
```

**Paso 1: Detectar intención específica**
```
Pregunta al modelo:
"¿Es el cliente preguntando por métodos, enviando comprobante, o pidiendo ayuda con acceso?"
→ Clasifica en: metodos | comprobante | estado_pago | otro
```

**Paso 2: Ejecutar acción según intención**
- Si `metodos` → GET /api/v2/chat/assistant/cobranzas/metodos-pago → Presentar
- Si `comprobante` → Derivar a flujo existente POST /api/v2/payments/n8n/receipt-intake
- Si `estado_pago` → Handoff (no hay endpoint de estado en v2 aún)
- Si `otro` → Consultar memoria de negocio → Responder o handoff

**Paso 3: Responder**
- Mensaje natural basado en resultado de API
- Si error → Handoff a humano

**Paso 3.1: Formato de salida (obligatorio)**
- El modelo debe responder solo con el JSON minimo.
- n8n parsea reply_text y lo envia al cliente.
- Nunca enviar texto libre fuera del JSON.

**Paso 4: Registrar en base de datos**
```
POST /api/v2/chat/save-respond
{
  "idconv": "...",
  "subagente": "cobranzas_pago",
  "accion": "mostrar_metodos | derivar_comprobante | handoff",
  "datos_guardados": {...}
}
```

---

## 7. Playbooks de memoria (chat_memoria_negocio)

Se poblarán en seeder posterior: `ChatCobranzasPlaybooksSeeder.php`

Ejemplos de entradas:

### cobranzas_mensaje_bienvenida

```
Cuando el cliente entra al flujo de pago, abre con un mensaje amable:
"Aquí te muestro cómo pagamos en Streamify 💳"
+ lista de 3 métodos más comunes
```

### cobranzas_problema_transferencia_no_llega

```
Si cliente dice "hice transferencia pero no aparece":
1. Verifica: ¿número de cuenta correcto?
2. ¿Referencia correcta? (ej: teléfono o ID)
3. ¿Banco de salida? (algunos bancos demoran)
4. Si pasó 1h sin solución → handoff a empleado
```

### cobranzas_que_es_cci

```
Si cliente pregunta "qué es CCI":
"Es el código único de tu banco. En el caso de [Banco X], es: [CCI]"
Nunca hagas explicación técnica larga.
```

### cobranzas_comprobante_rechazado

```
Si empleado rechaza comprobante:
Modelo recibe notificación → Informa al cliente:
"El comprobante no coincide con nuestros registros. 
¿Verificamos los datos? Te paso con un asesor 👉"
→ Handoff a humano
```

---

## 8. Diferenciación con otros subagentes

| Subagent | Rol | Intención cliente |
|----------|-----|------------------|
| **vendedor_cierre** (P20) | Convencer de comprar | "Cuál es el precio?", "Qué plan me recomiendas?" |
| **cobranzas_pago** (P40) | Recibir y validar pago | "Ya quiero pagar", "Cómo envío el comprobante?" |
| **postventa_reciente** (P50) | Confirmación post-venta | "Confirmé mi pago, ¿ahora qué?", "Cuándo accedo?" |
| **soporte_cliente** (P30) | Resolver incidencias | "No me funciona", "Olvidé contraseña" |

---

## 9. Matriz de decisión del Router

El `router_general` (prioridad 1) debe reconocer:

```
Si cliente dice:
- "pagar" / "transferencia" / "banco" / "tarjeta" / "comprobante" 
  → **cobranzas_pago** (P40)

- "cuánto cuesta" / "qué plan" / "combo" 
  → **vendedor_cierre** (P20)

- "no me funciona" / "no entra" / "olvidé contraseña" 
  → **soporte_cliente** (P30)

- "ya pagué, ¿qué sigue?" / "confirmé mi pago" 
  → **postventa_reciente** (P50)

Default: **asistente_no_registrado** (P10)
```

---

## 10. Seeder de playbooks

### ChatCobranzasPlaybooksSeeder.php

Crear archivo: `database/seeders/ChatCobranzasPlaybooksSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\ChatMemoriaNegocio;
use Illuminate\Database\Seeder;

class ChatCobranzasPlaybooksSeeder extends Seeder
{
    public function run(): void
    {
        $playbooks = [
            [
                'codigo' => 'cobranzas_metodos_disponibles',
                'nombre' => 'Métodos de Pago Disponibles',
                'tipo' => 'playbook',
                'descripcion' => 'Presentación clara de opciones de pago con diferencias y ventajas',
                'prompt_base' => 'Presentamos 3 métodos principales sin presión. Cada uno con su tiempo de acreditación.',
                'criterios' => ['intencion' => 'cliente_pregunta_metodos_pago'],
                'contenido' => 'Transf. bancaria (1-2 min), Tarjeta (inmediato), Billetera (inmediato)',
            ],
            [
                'codigo' => 'cobranzas_transferencia_paso_a_paso',
                'nombre' => 'Guía Transferencia Bancaria',
                'tipo' => 'playbook',
                'descripcion' => 'Instrucciones paso a paso para transferencia a cuentas de Streamify',
                'prompt_base' => 'Guía clara, sin tecnicismos. Datos bancarios precisos. Referencia obligatoria.',
                'criterios' => ['metodo_elegido' => 'transferencia'],
                'contenido' => 'Banco: X, Número: Y, CCI: Z, Titular: Streamify SAC, Referencia: Tu teléfono',
            ],
            [
                'codigo' => 'cobranzas_comprobante_que_mostrar',
                'nombre' => 'Qué Comprobante Mostrar',
                'tipo' => 'playbook',
                'descripcion' => 'Indicaciones sobre qué foto/screenshot enviar como comprobante',
                'prompt_base' => 'Solicita comprobante específico según banco. Pantalla de confirmación es lo importante.',
                'criterios' => ['cliente_pregunta' => 'qué_comprobante_enviar'],
                'contenido' => 'Foto de confirmación del banco (operación exitosa) + monto + fecha/hora visibles',
            ],
            [
                'codigo' => 'cobranzas_demora_acreditacion',
                'nombre' => 'Demora en Acreditación',
                'tipo' => 'playbook',
                'descripcion' => 'Qué hacer si cliente dice "hice transferencia pero no me acreditó"',
                'prompt_base' => 'Tranquilizamos, verificamos datos, esperamos, escalamos si es necesario.',
                'criterios' => ['problema' => 'transferencia_no_acreditada'],
                'contenido' => '1. Verifica número y CCI. 2. Referencia correcta? 3. Banco tarda a veces 2-3 hrs. 4. Si pasan 2hrs → empleado',
            ],
            [
                'codigo' => 'cobranzas_link_pago_directo',
                'nombre' => 'Link de Pago Directo',
                'tipo' => 'playbook',
                'descripcion' => 'Enviar link de tarjeta/billetera para pago instantáneo',
                'prompt_base' => 'Link directo, sin explicación técnica. "Haz clic y terminas en segundos."',
                'criterios' => ['metodo_elegido' => 'tarjeta_o_billetera'],
                'contenido' => 'https://payment.streamify.com/checkout (o wallet) - Pago inmediato, seguro',
            ],
            [
                'codigo' => 'cobranzas_falta_datos_bancarios',
                'nombre' => 'Cliente no Proporciona Datos de Banco',
                'tipo' => 'playbook',
                'descripcion' => 'Qué hacer si cliente no quiere/puede usar transferencia',
                'prompt_base' => 'Ofrecer alternativa (tarjeta), nunca obligar. Escalación profesional.',
                'criterios' => ['cliente_rechaza' => 'transferencia'],
                'contenido' => 'Ofrece: Tarjeta de crédito es más fácil. Si sigue negando → empleado de cobranzas',
            ],
            [
                'codigo' => 'cobranzas_comprobante_rechazado_mensaje',
                'nombre' => 'Comprobante Rechazado por Empleado',
                'tipo' => 'playbook',
                'descripcion' => 'Mensaje al cliente cuando su comprobante fue rechazado',
                'prompt_base' => 'Profesional, sin culpar al cliente. Propone solución inmediata.',
                'criterios' => ['comprobante_validado' => 'rechazado'],
                'contenido' => 'No coincidió con registros. Verificamos números. Si hay dudas → conectamos con asesor.',
            ],
            [
                'codigo' => 'cobranzas_pago_acreditado_mensaje',
                'nombre' => 'Pago Acreditado - Mensaje de Cierre',
                'tipo' => 'playbook',
                'descripcion' => 'Confirmar acreditación y siguiente paso (acceso, entrega credenciales, etc)',
                'prompt_base' => 'Alegre, seguro. Claro en el siguiente paso. "Tu acceso está listo en segundos."',
                'criterios' => ['comprobante_validado' => 'acreditado'],
                'contenido' => 'Confirma recepción ✓. Acceso inmediato. Si no accedes → soporte.',
            ],
        ];

        foreach ($playbooks as $playbook) {
            ChatMemoriaNegocio::updateOrCreate(
                ['codigo' => $playbook['codigo']],
                $playbook
            );
        }
    }
}
```

Actualizar `DatabaseSeeder.php`:
```php
$this->call([
    // ... otros seeders
    ChatCobranzasPlaybooksSeeder::class,
]);
```

---

## 11. Checklist de implementación

- [x] Endpoints de cobranzas en `ChatAssistantController` (alcance ligero):
  - [x] `cobranzasMetodosPago()` → GET /api/v2/chat/assistant/cobranzas/metodos-pago
  - [x] `cobranzasBancos()` → GET /api/v2/chat/assistant/cobranzas/bancos
- [x] Comprobantes delegados al flujo existente:
  - [x] `POST /api/v2/payments/n8n/receipt-intake`
  - [x] `POST /api/v2/payments/n8n/recargas/{idrec}/aprobar|rechazar`
- [x] No crear endpoints duplicados de registro/validación en subagente cobranzas
- [ ] Crear `ChatCobranzasPlaybooksSeeder.php` con 8 playbooks
- [ ] Ejecutar seeder: `php artisan db:seed --class=ChatCobranzasPlaybooksSeeder`
- [ ] Crear nodo n8n `cobranzas_pago` con system message del documento
- [ ] Validar en router: intención "pagar" → cobranzas_pago (P40)
- [ ] Test end-to-end: cliente pregunta método → respuesta con opciones → pago exitoso

---

## Próximos subagentes documentados

1. ✅ asistente_no_registrado (P10, doc 57)
2. ✅ vendedor_cierre (P20, doc 58)
3. ✅ soporte_cliente (P30, doc 59)
4. 📄 **cobranzas_pago (P40, doc 60)** ← Estás aquí
5. ⏳ postventa_reciente (P50)

---

## Referencias

- [49. Guía n8n: router, subagentes y memoria](49-GUIA-N8N-ROUTER-Y-SUBAGENTES.md)
- [57. Subagent Asistente](57-subagent-Asistente.md)
- [58. Subagent Vendedor](58-subagent-Vendedor.md)
- [59. Subagent Soporte](59-subagent-Soporte.md)
- [ChatSubagenteSeeder.php](../database/seeders/ChatSubagenteSeeder.php) - Definiciones de prioridad

