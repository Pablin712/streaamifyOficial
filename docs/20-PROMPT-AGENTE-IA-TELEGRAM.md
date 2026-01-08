# PROMPT PARA AGENTE IA DE TELEGRAM AUTH - STREAMIFY

## 🤖 System Message para N8N AI Agent

```
Eres el asistente de AUTENTICACIÓN de Streamify Bot.

Tienes MEMORIA PERSISTENTE en MySQL (telegram_auth_sessions) y GUÍA DE PASOS (steps).

=== TOOLS DISPONIBLES ===

0. get_step_instructions - Lee instrucciones del paso (¡USA SIEMPRE!)
1. get_memory - Lee estado: step, proceso, datos, intentos
2. update_memory - Guarda estado (OBLIGATORIO antes de responder)
3. delete_memory - Elimina sesión cuando completa
4. validar_credenciales - Login
5. check_email_exists - Verifica email duplicado
6. Registrar_cliente - Crea cliente
7. Update_telegram_chat_id - Vincula telegram

=== FLUJO POR CADA MENSAJE ===

1. get_memory(chat_id) → obtén step actual
2. get_step_instructions(step) → lee QUÉ HACER (¡CLAVE!)
3. Ejecuta validaciones según instrucciones
4. update_memory(chat_id, nuevo_step, proceso, datos, intentos) → ANTES de responder
5. Responde al usuario con mensaje de las instrucciones
6. delete_memory si completado

=== PASOS DEL FLUJO ===

LOGIN: inicio → login_email → login_password → completado
REGISTRO: inicio → registro_nombre → registro_email → registro_telefono → registro_password → registro_confirmar → completado

=== REGLAS ===

✅ SIEMPRE usa get_step_instructions - tiene TODO
✅ SIEMPRE usa update_memory ANTES de responder
✅ Sigue instrucciones del paso al pie de la letra
✅ Máximo 3 intentos de login
✅ Emojis en respuestas

❌ NO inventes - consulta get_step_instructions
❌ NO olvides update_memory
❌ NO saltes pasos
```

## 📋 Tools MySQL a Configurar en N8N

### Tool 0: get_step_instructions
```sql
SELECT name, description, next_step 
FROM steps 
WHERE name = :step_name 
LIMIT 1
```
**Descripción:** "Obtiene instrucciones detalladas del paso. El campo 'description' es tu subprompt completo."
**Parámetros:** step_name (string)

### Tool 1: get_memory
```sql
SELECT chat_id, step, proceso, datos, intentos, last_activity,
CASE WHEN last_activity < NOW() - INTERVAL 10 MINUTE THEN true ELSE false END as expirada
FROM telegram_auth_sessions 
WHERE chat_id = :chat_id 
LIMIT 1
```
**Descripción:** "Lee el estado de la conversación del usuario."
**Parámetros:** chat_id (number)

### Tool 2: update_memory
```sql
INSERT INTO telegram_auth_sessions 
(chat_id, step, proceso, datos, intentos, last_activity, created_at, updated_at)
VALUES (:chat_id, :step, :proceso, :datos, :intentos, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE
step = VALUES(step), proceso = VALUES(proceso), datos = VALUES(datos),
intentos = VALUES(intentos), last_activity = VALUES(last_activity), updated_at = VALUES(updated_at)
```
**Descripción:** "Guarda/actualiza memoria. datos debe ser JSON string. Usa JSON.stringify()."
**Parámetros:** 
- chat_id (number)
- step (string)
- proceso (string) - 'login' o 'registro' o NULL
- datos (string) - JSON como string: '{"email":"test@test.com"}'
- intentos (number)

**Ejemplo de datos:** `{{ JSON.stringify({email: "test@test.com", nombre: "Test"}) }}`

### Tool 3: delete_memory
```sql
DELETE FROM telegram_auth_sessions WHERE chat_id = :chat_id
```
**Descripción:** "Elimina sesión. SOLO cuando auth completa."
**Parámetros:** chat_id (number)

### Tool 4: validar_credenciales
```sql
SELECT idcli, nombrecli, email, password 
FROM clientes 
WHERE email = :email 
LIMIT 1
```
**Descripción:** "Obtiene cliente por email. Valida password con bcrypt después."
**Parámetros:** email (string)

### Tool 5: check_email_exists
```sql
SELECT COUNT(*) as existe 
FROM clientes 
WHERE email = :email 
LIMIT 1
```
**Descripción:** "Verifica si email existe. Retorna existe: 1 (sí) o 0 (no)."
**Parámetros:** email (string)

### Tool 6: Registrar_cliente
```sql
INSERT INTO clientes 
(nombrecli, email, password, telefonocli, telegram_chat_id, pais, saldo, created_at, updated_at)
VALUES (:nombre, :email, :password_hash, :telefono, :chat_id, 'Ecuador', 0.00, NOW(), NOW());
SELECT LAST_INSERT_ID() as idcli
```
**Descripción:** "Crea cliente. password_hash debe estar encriptado con bcrypt ANTES."
**Parámetros:**
- nombre (string)
- email (string)
- password_hash (string) - YA ENCRIPTADO
- telefono (string)
- chat_id (number)

### Tool 7: Update_telegram_chat_id
```sql
UPDATE clientes 
SET telegram_chat_id = :chat_id, updated_at = NOW()
WHERE idcli = :cliente_id 
AND telegram_chat_id IS NULL
```
**Descripción:** "Vincula telegram a cliente después de login exitoso."
**Parámetros:**
- cliente_id (number)
- chat_id (number)

## 🚀 Orden de Ejecución en N8N

1. **Telegram Webhook** → Captura mensaje
2. **AI Agent** con tools configurados arriba
3. **IF Node**: ¿Completado?
   - SI → Flujo principal
   - NO → Enviar respuesta y END

## 💡 Tips Importantes

- **get_step_instructions ES LA CLAVE**: Contiene mensajes, validaciones y lógica completa
- **update_memory SIEMPRE**: Antes de cada respuesta al usuario
- **datos como string**: Usa `JSON.stringify()` para el campo datos
- **password bcrypt**: Encripta con bcrypt antes de Registrar_cliente

## 📝 Ejemplo de Flujo Completo

```
Usuario: "hola"
1. get_memory(12345) → null
2. get_step_instructions('inicio') → "Pregunta si tiene cuenta..."
3. update_memory(12345, 'inicio', null, '{}', 0)
4. Respuesta: "👋 ¡Hola! ¿Ya tienes cuenta? SI/NO"

Usuario: "SI"
1. get_memory(12345) → {step: 'inicio'}
2. get_step_instructions('inicio') → "Si SI → login_email"
3. update_memory(12345, 'login_email', 'login', '{}', 0)
4. Respuesta: "📧 Ingresa tu email:"

Usuario: "juan@test.com"
1. get_memory(12345) → {step: 'login_email'}
2. get_step_instructions('login_email') → "Validar email..."
3. update_memory(12345, 'login_password', 'login', '{"email":"juan@test.com"}', 0)
4. Respuesta: "🔐 Ingresa contraseña:"

Usuario: "pass123"
1. get_memory(12345) → {step: 'login_password', datos: {email: "juan@test.com"}}
2. get_step_instructions('login_password') → "Validar credenciales..."
3. validar_credenciales("juan@test.com") → {idcli: 1, password: "$2y$..."}
4. [Validar password bcrypt]
5. Update_telegram_chat_id(1, 12345)
6. delete_memory(12345)
7. Respuesta: "✅ ¡Bienvenido!"
```

## 🎯 Pasos para Implementar

1. ✅ Ejecutar migraciones: `php artisan migrate`
2. ✅ Ejecutar seeder: `php artisan db:seed --class=StepSeeder`
3. ✅ Configurar AI Agent en N8N con el System Message
4. ✅ Agregar los 8 tools MySQL
5. ✅ Conectar a base de datos MySQL de Streamify
6. ✅ Probar flujo completo

¡Listo para usar! 🚀
