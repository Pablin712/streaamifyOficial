# 📋 Ayuda Técnica - Sistema Chat WhatsApp
## Última actualización: 22/04/2026

---

## 🚀 Estado actual del sistema
✅ **Funcionando correctamente**:
- ✅ WhatsAppHelpdeskService completamente refactorizado
- ✅ Webhook inbound funcionando
- ✅ Panel de chat `/chat/panel` listo
- ✅ Botón en sidebar agregado
- ✅ Configuración ngrok y proxies solucionada
- ✅ Workflows n8n listos para importar

---

## 📌 Comandos para ejecutar hoy

### 1. Iniciar servidor Laravel
```bash
php artisan serve
```
✅ El servidor corre en: **http://localhost:8001**

### 2. Iniciar ngrok
```bash
ngrok http 8001
```
✅ Tu URL ngrok actual:
```
https://47c4-2800-bf0-2c0c-fe8-e5e6-ace7-b290-1381.ngrok-free.app
```

---

## 🔗 URLs importantes

| Recurso | URL Local | URL Ngrok |
|---|---|---|
| Panel de Chat | `http://localhost:8001/chat/panel` | `https://47c4-2800-bf0-2c0c-fe8-e5e6-ace7-b290-1381.ngrok-free.app/chat/panel` |
| WhatsApp Helpdesk | `http://localhost:8001/chat/whatsapp` | `https://47c4-2800-bf0-2c0c-fe8-e5e6-ace7-b290-1381.ngrok-free.app/chat/whatsapp` |
| ✅ Webhook Inbound | `POST http://localhost:8001/api/chat/whatsapp/inbound` | `POST https://47c4-2800-bf0-2c0c-fe8-e5e6-ace7-b290-1381.ngrok-free.app/api/chat/whatsapp/inbound` |
| Test Ping | `http://localhost:8001/api/ping` | `https://47c4-2800-bf0-2c0c-fe8-e5e6-ace7-b290-1381.ngrok-free.app/api/ping` |

### 🔐 Token de seguridad:
```
test_token_123
```

---

## 🧪 Prueba manual del webhook
```bash
curl -X POST https://47c4-2800-bf0-2c0c-fe8-e5e6-ace7-b290-1381.ngrok-free.app/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: test_token_123" \
  -H "Content-Type: application/json" \
  -d '{
    "numero": "5215512345678",
    "nombre": "Usuario Prueba",
    "mensaje": "Hola, funciona!",
    "external_message_id": "test_msg_001"
  }'
```

---

## 🚀 Pasos para conectar n8n y Meta

### 1. Configurar n8n:
1.  Importa ambos workflows que están en `/docs/`
2.  En **WhatsApp Meta Inbound** actualiza la URL de Laravel
3.  En **WhatsApp Meta Outbound** agrega tu token de Meta y Phone Number ID
4.  Activa AMBOS workflows

### 2. Configurar Meta Developers:
| Campo | Valor |
|---|---|
| Callback URL | `https://TU-URL-N8N.com/webhook/whatsapp-meta` |
| Verify Token | El que tu elijas |
| Campo a suscribir | `messages` |

### 3. Configurar .env en Laravel:
```env
N8N_CLIENT_MESSAGE_WEBHOOK=https://TU-URL-N8N.com/webhook/whatsapp-outbound
CHAT_WEBHOOK_TOKEN=test_token_123
```

---

## 🔍 Flujo completo funcional
```
WhatsApp Usuario → Meta API → n8n VPS → ngrok → Laravel → Panel Chat /chat/panel
                                                              ↓
WhatsApp Usuario ← Meta API ← n8n VPS ← Laravel ← Respuesta operador
```

---

## 📂 Archivos modificados últimamente
1.  `app/Http/Controllers/Chat/WhatsAppWebhookController.php`
2.  `app/Services/Chat/WhatsAppHelpdeskService.php`
3.  `app/Services/Chat/WhatsAppOutboundService.php`
4.  `app/Providers/AppServiceProvider.php` (proxies ngrok)
5.  `bootstrap/app.php` (trust proxies)
6.  `resources/views/partials/sidebar.blade.php` (botón panel)
7.  `config/app.php` (chat_webhook_token)

---

## ✅ Checklist
- [x] Servidor Laravel ejecutandose en 8001
- [x] Ngrok ejecutandose y apuntando a 8001
- [x] Workflows importados en n8n
- [x] Webhook configurado en Meta
- [x] .env actualizado con URL de n8n
- [x] Prueba del webhook funcionando
- [x] Panel /chat/panel accesible
