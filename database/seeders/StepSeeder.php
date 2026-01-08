<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Llena la tabla steps con todos los pasos del flujo de autenticación de Telegram
     */
    public function run(): void
    {
        $steps = [
            [
                'name' => 'inicio',
                'description' => 'PASO INICIO: Este es el primer contacto con el usuario.

ACCIÓN: Pregunta al usuario si ya tiene una cuenta en Streamify.

MENSAJE A ENVIAR:
"👋 ¡Hola! Para usar Streamify Bot necesito vincular tu cuenta.

¿Ya tienes una cuenta en https://streamify.aaronsoft.es?

Por favor responde:
✅ SI (si ya tienes cuenta)
❌ NO (si necesitas crear cuenta)"

FORMATO DE ACTUALIZACIÓN DE MEMORIA:
- step: siguiente paso (login_email o registro_nombre)
- proceso: tipo de proceso (login o registro)
- ultimo_mensaje_bot: guardar el mensaje enviado por el bot
- ultimo_mensaje_usuario: guardar la respuesta del usuario (variable $(Telegram Trigger).item.json.message.text la cual contiene el texto del mensaje recibido del trigger y no se debe modificar)
- datos: {} (inicializar como objeto vacío)

DATOS A GUARDAR: ninguno (inicializar datos como objeto vacío)',
                'next_step' => 'si o no', // Depende de la respuesta: login_email o registro_nombre
            ],
            [
                'name' => 'si o no',
                'description' => 'PASO SI O NO: Decidir si el usuario quiere hacer login o registro. ACCIÓN: Basado en la respuesta del usuario en el paso "inicio", redirigir al paso correspondiente.
                VALIDAR RESPUESTA: revisar el texto del mensaje recibido y ultimo_mensaje_usuario, para decidir el siguiente paso.
- Si usuario escribió "SI", "SÍ", "S", "YES", "Y" → Actualizar memoria: step=login_email, proceso=login
- Si usuario escribió "NO", "N" → Actualizar memoria: step=registro_nombre, proceso=registro
- Si respuesta no válida → Repetir pregunta y decir: "Por favor responde SI o NO"
FORMATO DE ACTUALIZACIÓN DE MEMORIA:
- step: login_email o registro_nombre
- proceso: login o registro
- ultimo_mensaje_bot: guardar el mensaje enviado por el bot
- ultimo_mensaje_usuario: guardar la respuesta del usuario (SI, NO) (variable $(Telegram Trigger).item.json.message.text la cual contiene el texto del mensaje recibido del trigger y no se debe modificar)
- datos: {} (inicializar como objeto vacío)
DATOS A GUARDAR: ninguno (inicializar datos como objeto vacío).
                Guardar la respuesta en la memoria correspondiente. ',
                'next_step' => null, // Este paso es solo de decisión, no se guarda

            ],

            [
                'name' => 'login_email',
                'description' => 'PASO LOGIN EMAIL: Usuario ya tiene cuenta y debe proporcionar su email.

ACCIÓN: Pedir el email de registro al usuario.

MENSAJE A ENVIAR:
"📧 Perfecto. Por favor ingresa tu email de registro:"

VALIDAR RESPUESTA:
1. Verificar que contiene @ y dominio
2. Si formato inválido → Repetir: "❌ Formato de email inválido. Por favor ingresa un email válido:"
3. Si formato válido → Actualizar memoria: step=login_password, proceso=login, datos={email: valor_ingresado}

DATOS A GUARDAR: {email: "email_del_usuario"}',
                'next_step' => 'login_password',
            ],

            [
                'name' => 'login_password',
                'description' => 'PASO LOGIN PASSWORD: Usuario debe proporcionar su contraseña.

PRE-REQUISITO: Debe existir datos.email en memoria (si no existe, volver a login_email)

ACCIÓN: Pedir contraseña y validar credenciales contra la base de datos.

MENSAJE A ENVIAR:
"🔐 Ahora ingresa tu contraseña:"

VALIDAR RESPUESTA:
1. Verificar que datos.email existe en memoria (si no, regresar a login_email)
2. Obtener el password ingresado por el usuario
3. Usar tool "validar_credenciales" con parámetros:
   - email: datos.email (obtenido de memoria)
   - password: password ingresado por usuario
4. El tool devuelve el cliente si credenciales son válidas, o null si son inválidas

SI CREDENCIALES VÁLIDAS (tool devuelve cliente con id, nombre, etc):
   a. Obtener chat_id de la sesión actual
   b. Usar tool "Update_telegram_chat_id" con parámetros:
      - cliente_id: id del cliente devuelto
      - telegram_chat_id: chat_id de la sesión
   c. Actualizar memoria: step=completado, proceso=login
   d. Responder: "✅ ¡Perfecto! Tu cuenta ha sido vinculada exitosamente.\n\nBienvenido/a {nombre_cliente} 🎉"
   e. Usar tool "delete_memory" para eliminar la sesión (ya está completo)

SI CREDENCIALES INVÁLIDAS (tool devuelve null o error):
   a. Obtener intentos actuales de memoria (si no existe, inicializar en 0)
   b. Incrementar intentos: intentos_nuevo = intentos_actual + 1
   c. Si intentos_nuevo >= 3:
      - Actualizar memoria: step=inicio, proceso=null, datos={}, intentos=0
      - Responder: "❌ Demasiados intentos fallidos. Por seguridad, debes comenzar de nuevo.\n\nEscribe \"hola\" para iniciar nuevamente."
   d. Si intentos_nuevo < 3:
      - Actualizar memoria: step=login_password, proceso=login, intentos=intentos_nuevo, datos={email: mantener}
      - Responder: "❌ Contraseña incorrecta. Intento {intentos_nuevo}/3.\n\n🔐 Por favor ingresa tu contraseña nuevamente:"

FORMATO DE ACTUALIZACIÓN DE MEMORIA:
- step: completado (si válido) o login_password (si inválido y < 3 intentos) o inicio (si >= 3 intentos)
- proceso: login (mantener) o null (si reinicia)
- ultimo_mensaje_bot: guardar el mensaje enviado por el bot según el resultado
- ultimo_mensaje_usuario: guardar el password ingresado (variable $(Telegram Trigger).item.json.message.text la cual contiene el texto del mensaje recibido del trigger y no se debe modificar) - IMPORTANTE: no guardar el password real en memoria por seguridad, solo guardar "***" o el número de intentos
- intentos: incrementar en caso de fallo, resetear a 0 si éxito o si >= 3
- datos: mantener {email: valor} si fallo < 3 intentos, vaciar {} si reinicia

DATOS A GUARDAR:
- Si credenciales válidas: mantener email temporalmente hasta delete_memory
- Si credenciales inválidas: mantener email para siguiente intento
- Si >= 3 intentos: limpiar todo (datos={})
- NUNCA guardar el password en memoria por seguridad',
                'next_step' => 'completado',
            ],

            [
                'name' => 'registro_nombre',
                'description' => 'PASO REGISTRO NOMBRE: Usuario nuevo debe proporcionar su nombre completo.

ACCIÓN: Pedir nombre completo al usuario para iniciar el proceso de registro.

MENSAJE A ENVIAR:
"📝 ¡Perfecto! Vamos a crear tu cuenta.

¿Cuál es tu nombre completo?"

VALIDAR RESPUESTA:
1. Obtener el texto ingresado por el usuario
2. Limpiar espacios adicionales: trim(texto)
3. Verificar longitud: debe tener al menos 3 caracteres
4. Si es muy corto (< 3 caracteres):
   - Mantener step=registro_nombre
   - Responder: "❌ El nombre debe tener al menos 3 caracteres.\n\n📝 Por favor ingresa tu nombre completo:"
5. Si es válido (>= 3 caracteres):
   - Actualizar memoria: step=registro_email, proceso=registro
   - Guardar en datos: {nombre: valor_ingresado_limpio}
   - Continuar al siguiente paso automáticamente (el bot enviará el mensaje del siguiente paso)

FORMATO DE ACTUALIZACIÓN DE MEMORIA:
- step: registro_email (si válido) o registro_nombre (si inválido)
- proceso: registro (mantener)
- ultimo_mensaje_bot: guardar el mensaje enviado por el bot ("¿Cuál es tu nombre completo?" o mensaje de error)
- ultimo_mensaje_usuario: guardar el nombre ingresado (variable $(Telegram Trigger).item.json.message.text la cual contiene el texto del mensaje recibido del trigger y no se debe modificar)
- datos: {nombre: "valor_ingresado"} si válido, o {} si inválido

DATOS A GUARDAR:
{
  "nombre": "nombre_completo_del_usuario"
}

IMPORTANTE: El nombre se guardará tal como el usuario lo escribió (respetando mayúsculas/minúsculas), solo quitando espacios extras al inicio/final.',
                'next_step' => 'registro_email',
            ],

            [
                'name' => 'registro_email',
                'description' => 'PASO REGISTRO EMAIL: Usuario debe proporcionar un email único.

PRE-REQUISITO: Debe existir datos.nombre en memoria (si no existe, volver a registro_nombre)

ACCIÓN: Pedir email y verificar que no exista en la base de datos.

MENSAJE A ENVIAR:
"📧 Gracias {datos.nombre}. Ahora, ¿cuál es tu email?"

VALIDAR RESPUESTA:
1. Verificar que datos.nombre existe en memoria (si no, regresar a registro_nombre)
2. Obtener el email ingresado por el usuario
3. Limpiar espacios y convertir a minúsculas: trim(toLowerCase(email))
4. Validar formato básico:
   - Debe contener exactamente un símbolo @
   - Debe tener texto antes del @
   - Debe tener dominio después del @ (mínimo: algo.xx)
   - Expresión sugerida: contiene @ y un punto después del @
5. Si formato inválido:
   - Mantener step=registro_email, proceso=registro
   - Responder: "❌ Formato de email inválido.\n\n📧 Por favor ingresa un email válido (ejemplo: usuario@ejemplo.com):"
6. Si formato válido:
   a. Usar tool "check_email_exists" con parámetro: email=email_ingresado
   b. El tool devuelve true si existe, false si no existe

SI EMAIL YA EXISTE (tool devuelve true):
   - Mantener step=registro_email, proceso=registro (por si dice NO)
   - Responder: "❌ Este email ya está registrado en Streamify.\n\n¿Quieres hacer login en su lugar?\n\n✅ Responde SI para iniciar sesión\n❌ Responde NO para usar otro email"
   - Esperar respuesta del usuario:
     * Si responde "SI", "SÍ", "S", "YES", "Y":
       - Actualizar memoria: step=login_email, proceso=login, datos={email: email_ingresado}
       - El siguiente paso será pedir contraseña
     * Si responde "NO", "N":
       - Mantener memoria: step=registro_email, proceso=registro, datos={nombre: mantener}
       - Responder: "📧 Entendido. Por favor ingresa otro email:"
     * Si respuesta no válida:
       - Mantener step=registro_email
       - Responder: "Por favor responde SI o NO"

SI EMAIL NO EXISTE (tool devuelve false):
   - Actualizar memoria: step=registro_telefono, proceso=registro
   - Guardar en datos: {nombre: mantener, email: email_validado}
   - Continuar al siguiente paso automáticamente

FORMATO DE ACTUALIZACIÓN DE MEMORIA:
- step: registro_telefono (si email nuevo) o login_email (si existe y usuario elige SI) o registro_email (si existe y usuario elige NO, o formato inválido)
- proceso: registro (mantener) o login (si usuario elige cambiar a login)
- ultimo_mensaje_bot: guardar el mensaje enviado por el bot según el resultado (solicitud de email, error de formato, o pregunta sobre email existente)
- ultimo_mensaje_usuario: guardar el email o respuesta ingresada (variable $(Telegram Trigger).item.json.message.text la cual contiene el texto del mensaje recibido del trigger y no se debe modificar)
- datos:
  * Si email nuevo: {nombre: mantener, email: "email_validado"}
  * Si email existe y dice SI: {email: "email_existente"}
  * Si email existe y dice NO: {nombre: mantener} (sin email)
  * Si formato inválido: {nombre: mantener} (sin email)

DATOS A GUARDAR:
Si email válido y no existe:
{
  "nombre": "mantener_valor_anterior",
  "email": "email_validado@ejemplo.com"
}

IMPORTANTE:
- El email debe guardarse en minúsculas y sin espacios
- Si el email ya existe, ofrecer cambiar a login es más conveniente que forzar otro email
- Validar formato ANTES de consultar la base de datos para evitar consultas innecesarias',
                'next_step' => 'registro_telefono',
            ],

            [
                'name' => 'registro_telefono',
                'description' => 'PASO REGISTRO TELÉFONO: Usuario debe proporcionar su número de teléfono.

PRE-REQUISITO: Deben existir datos.nombre y datos.email en memoria (si no existen, regresar a pasos anteriores)

ACCIÓN: Pedir número de teléfono y validar formato básico.

MENSAJE A ENVIAR:
"📱 Perfecto. ¿Cuál es tu número de teléfono?

Puedes incluir código de país si lo deseas (ejemplo: +34 612345678 o 612345678)"

VALIDAR RESPUESTA:
1. Verificar que datos.nombre y datos.email existen en memoria (si no, regresar a registro_nombre)
2. Obtener el teléfono ingresado por el usuario
3. Limpiar el formato: permitir números, espacios, y símbolos: + - ( )
4. Remover todos los espacios para contar solo caracteres válidos
5. Validar:
   - Mínimo 7 caracteres (teléfonos locales cortos)
   - Máximo 20 caracteres (con código de país y formato)
   - Solo puede contener: dígitos (0-9), +, -, (, ), espacios
6. Si formato inválido:
   - Mantener step=registro_telefono, proceso=registro
   - Responder: "❌ Formato de teléfono inválido.\n\n📱 Por favor ingresa un número válido (7-20 caracteres, puede incluir +, -, paréntesis):\n\nEjemplos:\n• +34 612 345 678\n• (55) 1234-5678\n• 612345678"
7. Si formato válido:
   - Actualizar memoria: step=registro_password, proceso=registro
   - Guardar en datos: {nombre: mantener, email: mantener, telefono: valor_ingresado}
   - Continuar al siguiente paso automáticamente

FORMATO DE ACTUALIZACIÓN DE MEMORIA:
- step: registro_password (si válido) o registro_telefono (si inválido)
- proceso: registro (mantener)
- ultimo_mensaje_bot: guardar el mensaje enviado por el bot (solicitud de teléfono o mensaje de error)
- ultimo_mensaje_usuario: guardar el teléfono ingresado (variable $(Telegram Trigger).item.json.message.text la cual contiene el texto del mensaje recibido del trigger y no se debe modificar)
- datos:
  * Si válido: {nombre: mantener, email: mantener, telefono: "valor_ingresado"}
  * Si inválido: {nombre: mantener, email: mantener} (sin teléfono)

DATOS A GUARDAR:
Si teléfono válido:
{
  "nombre": "mantener_valor_anterior",
  "email": "mantener_valor_anterior",
  "telefono": "numero_telefono"
}

IMPORTANTE:
- El teléfono se guarda tal como el usuario lo escribió (con espacios y símbolos si los incluyó)
- No es necesario validar que el número exista o sea real, solo validar formato básico
- Aceptar formatos internacionales con código de país (+)
- Permitir formatos comunes: con guiones, paréntesis, espacios',
                'next_step' => 'registro_password',
            ],

            [
                'name' => 'registro_password',
                'description' => 'PASO REGISTRO PASSWORD: Usuario debe crear una contraseña segura.

PRE-REQUISITO: Deben existir datos.nombre, datos.email, datos.telefono en memoria (si no existen, regresar a pasos anteriores)

ACCIÓN: Pedir que el usuario cree una contraseña para su nueva cuenta.

MENSAJE A ENVIAR:
"🔐 Ahora crea una contraseña segura para tu cuenta.

Debe tener al menos 6 caracteres.

Por favor ingresa tu contraseña:"

VALIDAR RESPUESTA:
1. Verificar que datos.nombre, datos.email y datos.telefono existen en memoria (si no, regresar a pasos anteriores)
2. Obtener el password ingresado por el usuario
3. Validar longitud: debe tener al menos 6 caracteres
4. Si es muy corta (< 6 caracteres):
   - Mantener step=registro_password, proceso=registro
   - Responder: "❌ La contraseña debe tener al menos 6 caracteres.\n\n🔐 Por favor ingresa una contraseña válida (mínimo 6 caracteres):"
5. Si es válida (>= 6 caracteres):
   - Actualizar memoria: step=registro_confirmar, proceso=registro
   - Guardar en datos: {nombre: mantener, email: mantener, telefono: mantener, password: valor_ingresado}
   - Continuar al siguiente paso automáticamente

FORMATO DE ACTUALIZACIÓN DE MEMORIA:
- step: registro_confirmar (si válido) o registro_password (si inválido)
- proceso: registro (mantener)
- ultimo_mensaje_bot: guardar el mensaje enviado por el bot (solicitud de contraseña o mensaje de error)
- ultimo_mensaje_usuario: NO guardar el password por seguridad, guardar solo "***" o "(contraseña oculta)" - NO usar la variable de Telegram Trigger aquí
- datos:
  * Si válido: {nombre: mantener, email: mantener, telefono: mantener, password: "valor_temporal"}
  * Si inválido: {nombre: mantener, email: mantener, telefono: mantener} (sin password)

DATOS A GUARDAR:
Si password válido:
{
  "nombre": "mantener_valor_anterior",
  "email": "mantener_valor_anterior",
  "telefono": "mantener_valor_anterior",
  "password": "contraseña_temporal_en_texto_plano"
}

IMPORTANTE - SEGURIDAD:
- El password se guarda TEMPORALMENTE en memoria en texto plano para poder encriptarlo después
- En ultimo_mensaje_usuario NO guardes el password real, usa "***" o "(oculta)"
- El password será encriptado con bcrypt al crear el cliente en el siguiente paso
- La sesión en memoria se eliminará después de crear el cliente
- NUNCA mostrar o logear el password en texto plano
- El password debe guardarse en datos.password (no en ultimo_mensaje_usuario)

VALIDACIÓN DE SEGURIDAD RECOMENDADA (opcional):
- Puedes sugerir al usuario usar mayúsculas, números o símbolos
- Pero el requisito mínimo es 6 caracteres solamente',
                'next_step' => 'registro_confirmar',
            ],

            [
                'name' => 'registro_confirmar',
                'description' => 'PASO REGISTRO CONFIRMAR: Usuario debe revisar y confirmar todos sus datos antes de crear la cuenta.

PRE-REQUISITO: Deben existir todos los datos en memoria (nombre, email, telefono, password)

ACCIÓN: Mostrar resumen completo de los datos y pedir confirmación explícita.

MENSAJE A ENVIAR:
"📋 Por favor revisa y confirma tus datos:

👤 Nombre: {datos.nombre}
📧 Email: {datos.email}
📱 Teléfono: {datos.telefono}
🔐 Contraseña: ••••••• (oculta por seguridad)

¿Todo está correcto?

✅ Responde SI para crear tu cuenta
❌ Responde NO para corregir datos"

VALIDAR RESPUESTA:
1. Verificar que todos los datos existen en memoria (nombre, email, telefono, password)
2. Si falta algún dato, regresar al paso correspondiente
3. Obtener la respuesta del usuario

SI USUARIO CONFIRMA (responde "SI", "SÍ", "S", "YES", "Y"):
   a. Preparar los datos para crear el cliente:
      - nombre: datos.nombre
      - email: datos.email
      - telefono: datos.telefono
      - password: datos.password (se encriptará con bcrypt en el tool)

   b. Usar tool "Registrar_cliente" con parámetros:
      - nombre: datos.nombre
      - email: datos.email
      - telefono: datos.telefono
      - password: datos.password

      IMPORTANTE: El tool "Registrar_cliente" debe ejecutar:
      ```sql
      INSERT INTO clientes (nombre, email, telefono, password, created_at, updated_at)
      VALUES (:nombre, :email, :telefono, BCRYPT(:password), NOW(), NOW())
      ```
      Nota: Verificar si MySQL soporta BCRYPT() nativamente. Si no, el password debe encriptarse ANTES de llamar al tool usando bcrypt/hash function de N8N o pre-procesamiento.

   c. Si el registro es EXITOSO (tool devuelve el ID del cliente creado):
      - Obtener el cliente_id devuelto por el tool
      - Obtener el chat_id de la sesión actual
      - Usar tool "Update_telegram_chat_id" con parámetros:
        * cliente_id: id devuelto por Registrar_cliente
        * telegram_chat_id: chat_id de la sesión
      - Actualizar memoria: step=completado, proceso=registro
      - Responder: "🎉 ¡Felicidades! Tu cuenta ha sido creada y vinculada exitosamente.\n\n¡Bienvenido/a {datos.nombre} a Streamify! 🎉\n\nYa puedes usar todas las funciones del bot."
      - Usar tool "delete_memory" para eliminar la sesión (autenticación completa)

   d. Si el registro FALLA (tool devuelve error):
      - Analizar el tipo de error:
        * Si es "email duplicado": Ofrecer hacer login
        * Si es otro error: Mostrar mensaje genérico
      - Actualizar memoria: step=inicio, proceso=null, datos={}, intentos=0
      - Responder: "❌ Error al crear la cuenta: {mensaje_error}\n\nPor favor comienza de nuevo escribiendo \"hola\" o contacta con soporte si el problema persiste."

SI USUARIO NO CONFIRMA (responde "NO", "N"):
   - Actualizar memoria: step=registro_nombre, proceso=registro, datos={} (limpiar todos los datos)
   - Responder: "📝 Entendido. Vamos a comenzar de nuevo con el registro.\n\n¿Cuál es tu nombre completo?"

SI RESPUESTA NO VÁLIDA:
   - Mantener step=registro_confirmar, proceso=registro, datos=mantener_todos
   - Responder: "Por favor responde SI para confirmar o NO para corregir datos."

FORMATO DE ACTUALIZACIÓN DE MEMORIA:
- step: completado (si confirma y registro exitoso) o inicio (si confirma pero falla registro) o registro_nombre (si no confirma) o registro_confirmar (si respuesta inválida)
- proceso: registro (mantener) o null (si falla o completa)
- ultimo_mensaje_bot: guardar el mensaje enviado por el bot según el resultado
- ultimo_mensaje_usuario: guardar la respuesta del usuario (variable $(Telegram Trigger).item.json.message.text la cual contiene el texto del mensaje recibido del trigger y no se debe modificar)
- datos:
  * Si confirma y éxito: se usa para crear cliente, luego se elimina con delete_memory
  * Si confirma y falla: {} (limpiar todo)
  * Si no confirma: {} (limpiar todo para empezar de nuevo)
  * Si respuesta inválida: mantener todos los datos

DATOS A GUARDAR:
Antes de confirmar:
{
  "nombre": "mantener",
  "email": "mantener",
  "telefono": "mantener",
  "password": "mantener_temporal"
}

Después de confirmar y crear cliente exitosamente:
- La sesión completa se elimina con delete_memory
- No quedan datos en memoria

IMPORTANTE - FLUJO CRÍTICO:
1. Validar que TODOS los datos existen antes de mostrar resumen
2. Mostrar datos claramente para que usuario pueda revisarlos
3. Ocultar password con ••••••• por seguridad
4. Si confirma:
   - Encriptar password con bcrypt (el tool debe hacer esto)
   - Crear cliente en BD
   - Si éxito: Vincular telegram y eliminar sesión
   - Si falla: Reiniciar proceso y mostrar error
5. Si no confirma: Limpiar datos y empezar registro desde cero
6. NUNCA dejar datos sensibles en memoria después de completar

CONSIDERACIONES DE SEGURIDAD:
- El password se encripta con bcrypt al crear el cliente
- Después de crear el cliente, la sesión se elimina inmediatamente
- Si hay error, limpiar todos los datos (especialmente password)
- No logear ni mostrar passwords en ningún momento',
                'next_step' => 'completado',
            ],

            [
                'name' => 'completado',
                'description' => 'PASO COMPLETADO: La autenticación se completó exitosamente.

ESTE ES UN PASO TERMINAL: No hay siguiente paso, la sesión debe haber sido eliminada.

CONTEXTO:
Este paso se establece justo ANTES de eliminar la sesión, como un marcador de que el proceso terminó correctamente. Sin embargo, inmediatamente después de establecerlo, se debe llamar al tool "delete_memory" para eliminar toda la sesión.

FLUJOS QUE LLEGAN AQUÍ:
1. LOGIN EXITOSO:
   - Credenciales validadas correctamente
   - telegram_chat_id vinculado al cliente
   - Mensaje enviado: "✅ ¡Perfecto! Tu cuenta ha sido vinculada exitosamente. Bienvenido/a {nombre} 🎉"
   - Sesión eliminada con delete_memory

2. REGISTRO EXITOSO:
   - Cliente creado en base de datos
   - telegram_chat_id vinculado al cliente
   - Mensaje enviado: "🎉 ¡Felicidades! Tu cuenta ha sido creada y vinculada exitosamente. ¡Bienvenido/a {nombre} a Streamify! 🎉"
   - Sesión eliminada con delete_memory

ACCIONES PREVIAS (ya completadas al llegar aquí):
✅ Usuario autenticado (login) o registrado (registro)
✅ telegram_chat_id vinculado al cliente en BD
✅ Mensaje de bienvenida enviado al usuario
✅ Sesión eliminada de telegram_auth_sessions con delete_memory

ESTADO ESPERADO:
- NO debe existir registro en telegram_auth_sessions para este chat_id
- El cliente debe tener telegram_chat_id poblado en la tabla clientes
- El usuario puede empezar a usar el bot normalmente

SI LLEGAS A ESTE PASO Y LA SESIÓN AÚN EXISTE:
Esto indica un error en el flujo anterior. La sesión debió eliminarse antes.
ACCIÓN DE RECUPERACIÓN:
1. Verificar si el cliente tiene telegram_chat_id vinculado
2. Si SÍ está vinculado:
   - Usar tool "delete_memory" para limpiar sesión huérfana
   - Responder: "✅ Ya estás autenticado. Puedes usar el bot normalmente."
3. Si NO está vinculado:
   - Algo salió mal en el proceso de autenticación
   - Usar tool "delete_memory" para limpiar sesión
   - Actualizar memoria (si aún existe): step=inicio, proceso=null, datos={}
   - Responder: "❌ Hubo un error en el proceso de autenticación. Por favor comienza de nuevo escribiendo \"hola\"."

IMPORTANTE - ESTE PASO NO DEBE PROCESARSE NORMALMENTE:
- Si el flujo funciona correctamente, nunca se consulta este paso
- La sesión se elimina ANTES de que pueda ser consultada nuevamente
- Este paso existe solo como documentación y para manejo de errores

NO HAY FORMATO DE ACTUALIZACIÓN DE MEMORIA:
Este paso no actualiza memoria porque la sesión ya fue eliminada.

NO HAY DATOS A GUARDAR:
No hay datos que guardar porque la sesión no existe más.

PRÓXIMOS PASOS PARA EL USUARIO:
El usuario ya está autenticado y puede:
- Usar comandos del bot
- Consultar información de su cuenta
- Realizar operaciones según los permisos de su cuenta
- El sistema reconocerá al usuario por su telegram_chat_id sin necesidad de autenticarse nuevamente',
                'next_step' => null,
            ],
        ];

        // Insertar todos los pasos
        foreach ($steps as $step) {
            DB::table('steps')->updateOrInsert(
                ['name' => $step['name']],
                $step
            );
        }

        $this->command->info('✅ Steps seeder completado: ' . count($steps) . ' pasos insertados.');
    }
}

