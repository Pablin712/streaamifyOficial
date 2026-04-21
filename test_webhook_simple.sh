#!/bin/bash

echo "=== PRUEBA WEBHOOK CHAT WHATSAPP ==="
echo ""

# Configuración
URL="https://autobot.aaronsoft.es/webhook/asistente-pablin"
TOKEN="test_token_123"

# Crear payload de prueba CON TOKEN INCLUIDO
PAYLOAD='{
  "canal_user_id": "593999999999@s.whatsapp.net",
  "telefono": "593999999999",
  "nombre": "Cliente de Prueba",
  "mensaje": "Hola, estoy probando el sistema de chat",
  "tipo": "texto",
  "external_message_id": "test_'$(date +%s)'",
  "instance": "Streamify Azul",
  "origen": "prueba",
  "token": "'$TOKEN'",
  "payload": {
    "source": "test"
  }
}'

echo "Enviando mensaje a: $URL"
echo "Payload:"
echo "$PAYLOAD" | python -m json.tool
echo ""

# Enviar la solicitud SIN HEADER DE TOKEN
curl -X POST "$URL" \
  -H "Content-Type: application/json" \
  -d "$PAYLOAD"

echo ""
echo ""
echo "=== INSTRUCCIONES ==="
echo "1. Revisa el panel de chat: http://localhost:8000/chat/whatsapp"
echo "2. Deberías ver una nueva conversación con 'Cliente de Prueba'"
echo "3. Haz clic en la conversación y responde"
echo "4. Verifica que el mensaje se guarda correctamente"
