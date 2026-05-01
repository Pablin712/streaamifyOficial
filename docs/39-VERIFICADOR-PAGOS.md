# 39 - Verificador de pagos con n8n (Webhook por recarga)

## Objetivo real
Procesar cada recarga de forma individual y automática:
1. El cliente envía su recarga y comprobante.
2. La recarga se guarda de inmediato como pendiente.
3. En ese mismo momento se dispara un webhook a n8n.
4. n8n analiza solo esa imagen con reglas definidas.
5. n8n aprueba o rechaza esa recarga por API.

No se usará cron job ni procesamiento por lotes.

## Requisito clave de rendimiento
El flujo del cliente debe terminar en milisegundos:
1. El controller de recarga no debe esperar a que termine n8n.
2. El disparo a n8n debe ser asíncrono (job/cola o fire-and-forget controlado).
3. La recarga queda temporalmente en estado pendiente mientras n8n valida (normalmente < 1 minuto).

## Estado actual del sistema (base real)
1. La recarga ya se guarda como pendiente (idestado = 1).
2. El comprobante se guarda en public/storage/comprobantes.
3. Ya existe lógica de aprobar/rechazar con transacción y lock (en flujo web).

Estados:
1. 1 = Pendiente
2. 2 = Rechazado
3. 3 = Aprobado

## Arquitectura objetivo (sin cron)

### Paso 1: creación de recarga
1. Cliente envía formulario de recarga.
2. Backend valida y guarda recarga + imagen.
3. Backend responde éxito al cliente inmediatamente.

### Paso 2: disparo de webhook
1. Justo después de persistir la recarga, backend dispara webhook a n8n con datos mínimos.
2. El disparo se ejecuta en segundo plano para no bloquear la respuesta HTTP del cliente.

### Paso 3: verificación en n8n
1. n8n recibe el payload de una sola recarga.
2. Descarga/lee el comprobante de esa recarga.
3. Aplica reglas de verificación.
4. Llama endpoint de aprobar o rechazar para ese idrec.

## Diseño de integración webhook

### Endpoint de entrada n8n
POST https://autobot.aaronsoft.es/webhook/94d871f4-6485-4118-93ec-471171de71c7

Nota:
No es obligatorio usar nodo Respond to Webhook. Puede configurarse el Webhook node para responder inmediatamente y continuar el flujo en background.

### Payload sugerido (1 recarga)
```json
{
  "event": "recarga.created",
  "idrec": 154,
  "idcli": 21,
  "idban": 3,
  "banco_nombre": "Pichincha",
  "numcomprobante": "TRX-984343",
  "valor": 10.50,
  "recarga_url": "https://tu-dominio.com/api/v2/payments/n8n/recargas/154",
  "foto_url": "https://tu-dominio.com/api/v2/payments/n8n/recargas/154/comprobante",
  "created_at": "2026-03-24T14:32:00Z",
  "trace_id": "recarga-154-20260324-143200"
}
```

## API mínima para n8n (por recarga específica)

### Endpoint recomendado de entrada para comprobantes desde n8n/chat

`POST /api/v2/payments/n8n/receipt-intake`

Este endpoint ya aplica la logica operativa para intake de comprobantes:

1. busca cliente primero por telefono;
2. si no existe, lo crea;
3. si viene titular (`cliente_nombre`) lo usa como nombre;
4. si no viene titular, genera nombre secuencial (`Cliente WhatsApp N`);
5. crea la recarga pendiente;
6. dispara verificacion automaticamente por defecto (`disparar_verificacion = true`).

Con esto puedes resolver de forma directa la rama "si es comprobante" en n8n sin depender de autenticacion del cliente.

### 0) Obtener detalle completo de recarga
GET /api/v2/payments/n8n/recargas/{idrec}

Esta respuesta devuelve todo lo necesario para análisis:
1. Foto (URL y metadatos del archivo).
2. Número de comprobante.
3. Si el comprobante está repetido en BD y en qué recargas.
4. Fecha de creación/actualización.
5. Cliente, banco, estado y monto.

Respuesta ejemplo:
```json
{
  "success": true,
  "data": {
    "idrec": 154,
    "idcli": 21,
    "cliente": {
      "nombre": "Juan Perez",
      "telefono": "+593961234567",
      "email": "juan@email.com"
    },
    "idban": 3,
    "banco": "Pichincha",
    "numcomprobante": "TRX-984343",
    "valor": 10.5,
    "idestado": 1,
    "estado": "Pendiente",
    "foto": {
      "path": "comprobantes/1742830011_trx.jpg",
      "url": "https://tu-dominio.com/api/v2/payments/n8n/recargas/154/comprobante",
      "download_url": "https://tu-dominio.com/api/v2/payments/n8n/recargas/154/comprobante/download",
      "exists": true,
      "mime": "image/jpeg",
      "size_bytes": 183452
    },
    "comprobante_repetido": true,
    "comprobante_repetido_count": 1,
    "comprobante_repetido_en": [
      {
        "idrec": 120,
        "idestado": 2,
        "created_at": "2026-03-20T10:15:00Z"
      }
    ],
    "created_at": "2026-03-24T14:32:00Z",
    "updated_at": "2026-03-24T14:32:10Z"
  }
}
```

### 1) Obtener imagen de comprobante
GET /api/v2/payments/n8n/recargas/{idrec}/comprobante

Uso:
1. n8n consume esta URL para analizar la imagen.
2. Debe validar API key o token firmado.

### 2) Aprobar recarga
POST /api/v2/payments/n8n/recargas/{idrec}/aprobar

Body opcional:
```json
{
  "observacion": "Aprobado por reglas IA",
  "trace_id": "recarga-154-20260324-143200"
}
```

### 3) Rechazar recarga
POST /api/v2/payments/n8n/recargas/{idrec}/rechazar

Body sugerido:
```json
{
  "motivo": "Comprobante no legible",
  "trace_id": "recarga-154-20260324-143200"
}
```

## Reglas de negocio obligatorias
1. Aprobar/rechazar solo si idestado = 1 (pendiente).
2. Si la recarga ya fue procesada, responder 409 (conflicto).
3. Mantener lock transaccional para evitar doble procesamiento.
4. Registrar historial con referencia trace_id para auditoría.

## Reglas sugeridas para el analizador
1. Legibilidad mínima de la imagen (OCR válido).
2. Coincidencia de monto detectado con valor de recarga (con tolerancia definida).
3. Detección de banco/emisor esperado.
4. Validación de número de comprobante cuando sea extraíble.
5. Detección básica de imagen inválida (vacía, recortada, repetida, borrosa extrema).

## Ajuste recomendado en n8n para alias de banco y multiples comprobantes

Si el nodo `Analyze image` devuelve `Banco del Barrio`, pero en Streamify el banco registrado es `Banco Guayaquil`, no debes comparar solo contra `banco.nombreban` como string fijo.

El webhook de verificacion ya envia estos campos:

```json
{
  "banco_nombre": "Banco Guayaquil",
  "banco_aliases": ["banco guayaquil", "guayaquil", "banco del barrio", "del barrio"],
  "verification_hints": {
    "allow_bank_alias_match": true,
    "allow_multiple_receipts_total_match": true,
    "expected_total_amount": 4.5,
    "bank_match_terms": ["banco guayaquil", "guayaquil", "banco del barrio", "del barrio"]
  }
}
```

### Prompt sugerido para el nodo Analyze image

Reemplaza el prompt del verificador por una version que soporte mas de un comprobante visible en la misma foto.

```text
Analiza este comprobante o conjunto de comprobantes de pago visibles en una sola imagen.

1. Detecta si hay uno o varios comprobantes.
2. Extrae cada comprobante por separado si existen multiples.
3. Para cada comprobante extrae:
- numero de comprobante
- monto
- banco
- fecha
- titular o beneficiario
- emisor si aparece

4. Devuelve tambien un resumen general:
- total_comprobantes
- monto_total_detectado
- banco_principal
- fechas_detectadas

5. Evalua calidad y antifraude:
- legible
- borroso
- editado
- realista
- confianza

Reglas:
- no inventes datos
- si algo no se ve devuelve null
- si hay multiples comprobantes suma sus montos en monto_total_detectado
- devuelve SOLO JSON valido

Formato de salida:
{
  "total_comprobantes": 1,
  "monto_total_detectado": number|null,
  "banco_principal": "string|null",
  "fechas_detectadas": ["YYYY-MM-DD"],
  "comprobantes": [
    {
      "comprobante": "string|null",
      "monto": number|null,
      "banco": "string|null",
      "fecha": "YYYY-MM-DD|null",
      "titular": "string|null",
      "emisor": "string|null"
    }
  ],
  "legible": true/false,
  "borroso": true/false,
  "editado": true/false,
  "realista": true/false,
  "confianza": 0-100
}
```

### Regla sugerida en el nodo Parsear

1. Para banco:
   compara `banco_principal` y los bancos de `comprobantes[]` contra `body.banco_aliases`.

2. Para monto:
   si `verification_hints.allow_multiple_receipts_total_match = true`, compara contra `monto_total_detectado`.

3. Para comprobante:
   acepta coincidencia si el `numcomprobante` del webhook aparece en cualquiera de `comprobantes[]`.

### Logica minima de banco en Parsear

```javascript
const normalize = (str) =>
  str?.toLowerCase()
    ?.normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim() || null;

const aliases = (webhook.banco_aliases || webhook.verification_hints?.bank_match_terms || [])
  .map(normalize)
  .filter(Boolean);

const bancosDetectados = [
  parsed.banco_principal,
  ...(parsed.comprobantes || []).map(item => item.banco)
]
  .map(normalize)
  .filter(Boolean);

const bankMatches = bancosDetectados.some(bancoDetectado =>
  aliases.some(alias =>
    bancoDetectado === alias || bancoDetectado.includes(alias) || alias.includes(bancoDetectado)
  )
);

if (!bankMatches) {
  aprobado = false;
  errores.push('Banco no coincide');
}
```

### Logica minima de monto total en Parsear

```javascript
const expectedAmount = Number(webhook.verification_hints?.expected_total_amount ?? webhook.valor ?? 0);
const detectedTotal = Number(parsed.monto_total_detectado ?? 0);

if (!detectedTotal || Math.abs(detectedTotal - expectedAmount) > 0.01) {
  aprobado = false;
  errores.push('Monto total no coincide');
}
```

### Caso de ejemplo

Si una foto trae dos depositos de `1.00` y `3.50`, el OCR debe devolver:

```json
{
  "total_comprobantes": 2,
  "monto_total_detectado": 4.5,
  "banco_principal": "Banco del Barrio",
  "comprobantes": [
    { "monto": 1.0, "comprobante": "00642453" },
    { "monto": 3.5, "comprobante": "00642453" }
  ]
}
```

Y el parser debe aprobar si el total coincide con `webhook.valor = 4.5` y el alias de banco coincide con `Banco Guayaquil`.

## Implementación recomendada en Laravel

### 1) Disparo asíncrono al crear recarga
En el flujo de procesar recarga:
1. Guardar recarga pendiente.
2. Despachar un Job con dispatchAfterResponse para invocar webhook n8n.
3. Retornar respuesta al cliente sin esperar análisis.

### 2) Job sugerido
Nombre sugerido:
- TriggerRecargaVerificationJob

Responsabilidad:
1. Enviar POST al webhook n8n con payload de la recarga.
2. Timeouts cortos y reintentos controlados.
3. Log de éxito/fallo del disparo.

### 3) Endpoints API para n8n
Crear controlador dedicado:
- App\Http\Controllers\Api\V2\PaymentVerificationController

Métodos mínimos:
1. detalleRecarga($idrec)
1. verComprobante($idrec)
2. aprobar($idrec)
3. rechazar($idrec)

## Flujo final esperado
1. Cliente registra recarga (rápido).
2. Estado inicial: pendiente.
3. Se dispara webhook de forma asíncrona.
4. n8n analiza solo ese comprobante.
5. n8n decide aprobar/rechazar.
6. En menos de 1 minuto, la recarga ya no está pendiente.

## Criterios de aceptación
1. No se usa cron para validación de recargas.
2. Cada recarga nueva dispara un webhook individual.
3. La respuesta al cliente no se bloquea por n8n.
4. n8n puede leer la imagen del comprobante de esa recarga.
5. n8n puede aprobar/rechazar de forma segura e idempotente.
