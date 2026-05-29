# Donna Business — Payloads de Ingest por tipo de mensaje

Todos los casos llaman `POST /api/donna/business/ingest` DESPUÉS de procesar media.
El flujo n8n es: **Webhook → Normalizar → Switch tipo → [Procesar media] → Ingest → Context → AI Agent**

---

## Caso 1: Texto

n8n extrae directamente del webhook y llama ingest.

```json
{
  "instance_name": "bot-pagos",
  "remote_jid": "593987183479@s.whatsapp.net",
  "from_me": false,
  "provider_message_id": "AC629EAE2F47F2B6BA630762EC2CD2C0",
  "sender_identifier": "593987183479",
  "sender_name": "Siani M",
  "message_type": "text",
  "content_text": "buenos días cuando se cabe la cuenta me ayudo con la otra cuenta gracias",
  "message_timestamp": 1780065998,
  "provider": "evolution_api"
}
```

`effective_text` devuelto = `content_text`

---

## Caso 2: Audio (después de transcribir con Whisper)

n8n descarga el audio de Evo API → transcribe con OpenAI → llama ingest con ambos.

```json
{
  "instance_name": "bot-pagos",
  "remote_jid": "593987183479@s.whatsapp.net",
  "from_me": false,
  "provider_message_id": "3EB001234ABCD",
  "sender_identifier": "593987183479",
  "sender_name": "Siani M",
  "message_type": "audio",
  "content_text": null,
  "transcription_text": "Hola quiero saber cuánto cuesta Spotify premium individual",
  "media_url": "https://evoapi.abigailsoft.com/download/audio/...",
  "media_mime_type": "audio/ogg; codecs=opus",
  "message_timestamp": 1780065998,
  "provider": "evolution_api"
}
```

`effective_text` devuelto = `transcription_text`

---

## Caso 3: Imagen con caption (y OCR opcional)

n8n recibe imagen → extrae caption del webhook → (opcional: OCR/visión) → llama ingest.

```json
{
  "instance_name": "bot-pagos",
  "remote_jid": "593987183479@s.whatsapp.net",
  "from_me": false,
  "provider_message_id": "3EB005678EFGH",
  "sender_identifier": "593987183479",
  "sender_name": "Siani M",
  "message_type": "image",
  "content_text": null,
  "caption_text": "esto es lo que me apareció en pantalla",
  "ocr_text": "Error 404 - Cuenta no encontrada. Código: ABC123",
  "media_url": "https://evoapi.abigailsoft.com/download/image/...",
  "media_mime_type": "image/jpeg",
  "message_timestamp": 1780065998,
  "provider": "evolution_api"
}
```

`effective_text` devuelto = `ocr_text` (si existe), si no → `caption_text`

**Nota:** Si no hay OCR, simplemente no enviar `ocr_text`. El `effective_text` será el caption.

---

## Respuesta de ingest

En todos los casos, ingest devuelve `message.effective_text` listo para el AI Agent:

```json
{
  "stored": true,
  "allowed": true,
  "message": {
    "id": 1200,
    "type": "audio",
    "content_text": null,
    "transcription_text": "Hola quiero saber cuánto cuesta Spotify premium individual",
    "ocr_text": null,
    "effective_text": "Hola quiero saber cuánto cuesta Spotify premium individual"
  },
  "conversation": { "id": 900, ... },
  "channel": { ... }
}
```

El nodo "Guardar contexto Business" en n8n debe mapear:

```js
input_text = $('POST Donna Business Ingest').item.json.message.effective_text
```

---

## Prioridad de effective_text

```
transcription_text  (audio)
    ↓ si null
ocr_text            (imagen con análisis)
    ↓ si null
content_text        (texto directo o caption de imagen sin OCR)
    ↓ si null
""                  (vacío, caso edge)
```
