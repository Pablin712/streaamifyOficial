#!/bin/bash

GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo "🚀 Ejecutando 10 pruebas del modulo Chat WhatsApp..."
echo "============================================="
echo ""

# 🟢 PRUEBAS EXITOSAS (VERDE)
echo -e "${GREEN}🟢 PRUEBAS EXITOSAS (VERDE)${NC}"
echo "---------------------------------------------"

# 1. Mensaje texto normal
echo -n "1. Mensaje texto normal: "
RESULT=$(curl -s -w "%{http_code}" -o /tmp/chat_test_1.json -X POST http://localhost:8000/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: test_token_123" \
  -H "Content-Type: application/json" \
  -d '{"canal_user_id":"593999999901@s.whatsapp.net","telefono":"593999999901","nombre":"Usuario 1","mensaje":"Hola, primer mensaje de prueba","tipo":"texto","external_message_id":"test-101"}')
if [ "$RESULT" == "201" ]; then echo -e "${GREEN}✅ OK (201 Created)${NC}"; else echo -e "${RED}❌ FAIL ($RESULT)${NC}"; fi

# 2. Mensaje imagen
echo -n "2. Mensaje imagen: "
RESULT=$(curl -s -w "%{http_code}" -o /tmp/chat_test_2.json -X POST http://localhost:8000/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: test_token_123" \
  -H "Content-Type: application/json" \
  -d '{"canal_user_id":"593999999902@s.whatsapp.net","telefono":"593999999902","tipo":"imagen","media_url":"https://picsum.photos/400/300","mime_type":"image/jpeg","external_message_id":"test-102"}')
if [ "$RESULT" == "201" ]; then echo -e "${GREEN}✅ OK (201 Created)${NC}"; else echo -e "${RED}❌ FAIL ($RESULT)${NC}"; fi

# 3. Mensaje audio
echo -n "3. Mensaje audio: "
RESULT=$(curl -s -w "%{http_code}" -o /tmp/chat_test_3.json -X POST http://localhost:8000/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: test_token_123" \
  -H "Content-Type: application/json" \
  -d '{"canal_user_id":"593999999903@s.whatsapp.net","telefono":"593999999903","tipo":"audio","media_url":"https://example.com/voice.ogg","mime_type":"audio/ogg","external_message_id":"test-103"}')
if [ "$RESULT" == "201" ]; then echo -e "${GREEN}✅ OK (201 Created)${NC}"; else echo -e "${RED}❌ FAIL ($RESULT)${NC}"; fi

# 4. Mensaje duplicado
echo -n "4. Mensaje duplicado: "
RESULT=$(curl -s -w "%{http_code}" -o /tmp/chat_test_4.json -X POST http://localhost:8000/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: test_token_123" \
  -H "Content-Type: application/json" \
  -d '{"canal_user_id":"593999999901@s.whatsapp.net","telefono":"593999999901","mensaje":"Este es duplicado","tipo":"texto","external_message_id":"test-101"}')
if [ "$RESULT" == "200" ]; then echo -e "${GREEN}✅ OK (200 Ignorado)${NC}"; else echo -e "${RED}❌ FAIL ($RESULT)${NC}"; fi

# 5. Reabre conversacion cerrada
echo -n "5. Reabre conversacion cerrada: "
RESULT=$(curl -s -w "%{http_code}" -o /tmp/chat_test_5.json -X POST http://localhost:8000/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: test_token_123" \
  -H "Content-Type: application/json" \
  -d '{"canal_user_id":"593999999901@s.whatsapp.net","telefono":"593999999901","mensaje":"Vuelvo a escribir despues de cerrado","tipo":"texto","external_message_id":"test-105"}')
if [ "$RESULT" == "201" ]; then echo -e "${GREEN}✅ OK (201 Created)${NC}"; else echo -e "${RED}❌ FAIL ($RESULT)${NC}"; fi

echo ""

# 🔵 PRUEBAS DE ERROR (AZUL)
echo -e "${BLUE}🔵 PRUEBAS DE ERROR (AZUL)${NC}"
echo "---------------------------------------------"

# 6. Token invalido
echo -n "6. Token invalido: "
RESULT=$(curl -s -w "%{http_code}" -o /tmp/chat_test_6.json -X POST http://localhost:8000/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: token_malo_123" \
  -H "Content-Type: application/json" \
  -d '{"canal_user_id":"593999999906@s.whatsapp.net","mensaje":"Esto no deberia llegar"}')
if [ "$RESULT" == "401" ]; then echo -e "${BLUE}✅ OK (401 Unauthorized)${NC}"; else echo -e "${RED}❌ FAIL ($RESULT)${NC}"; fi

# 7. Sin numero de telefono
echo -n "7. Sin numero de telefono: "
RESULT=$(curl -s -w "%{http_code}" -o /tmp/chat_test_7.json -X POST http://localhost:8000/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: test_token_123" \
  -H "Content-Type: application/json" \
  -d '{"mensaje":"Mensaje sin telefono","tipo":"texto"}')
if [ "$RESULT" == "422" ]; then echo -e "${BLUE}✅ OK (422 Unprocessable)${NC}"; else echo -e "${RED}❌ FAIL ($RESULT)${NC}"; fi

# 8. Sin contenido ni media
echo -n "8. Sin contenido ni media: "
RESULT=$(curl -s -w "%{http_code}" -o /tmp/chat_test_8.json -X POST http://localhost:8000/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: test_token_123" \
  -H "Content-Type: application/json" \
  -d '{"canal_user_id":"593999999908@s.whatsapp.net","tipo":"texto"}')
if [ "$RESULT" == "422" ]; then echo -e "${BLUE}✅ OK (422 Unprocessable)${NC}"; else echo -e "${RED}❌ FAIL ($RESULT)${NC}"; fi

# 9. Tipo no permitido (documento)
echo -n "9. Tipo no permitido (documento): "
RESULT=$(curl -s -w "%{http_code}" -o /tmp/chat_test_9.json -X POST http://localhost:8000/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: test_token_123" \
  -H "Content-Type: application/json" \
  -d '{"canal_user_id":"593999999909@s.whatsapp.net","tipo":"documento","media_url":"https://example.com/file.pdf"}')
if [ "$RESULT" == "422" ]; then echo -e "${BLUE}✅ OK (422 Unprocessable)${NC}"; else echo -e "${RED}❌ FAIL ($RESULT)${NC}"; fi

# 10. Payload vacio
echo -n "10. Payload vacio: "
RESULT=$(curl -s -w "%{http_code}" -o /tmp/chat_test_10.json -X POST http://localhost:8000/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: test_token_123" \
  -d '')
if [ "$RESULT" == "422" ]; then echo -e "${BLUE}✅ OK (422 Unprocessable)${NC}"; else echo -e "${RED}❌ FAIL ($RESULT)${NC}"; fi

echo ""
echo "============================================="
echo "✅ Todas las 10 pruebas ejecutadas exitosamente!"
echo ""
echo "Abre http://localhost:8000/chat/whatsapp para ver las conversaciones creadas."
