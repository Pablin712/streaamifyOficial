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
                'description' => 'PASO INICIO: Este es el primer contacto con el usuario. Este paso maneja DOS casos:

                CASO 1 - PRIMERA INTERACCIÓN (usuario escribe "hola" u otro mensaje inicial, cualquiera):
                ========================================================================
                ACCIÓN: Pregunta al usuario si ya tiene una cuenta en Streamify.

                MENSAJE A ENVIAR:
                "👋 ¡Hola! Para usar Streamify Bot necesito vincular tu cuenta.

                ¿Ya tienes una cuenta en https://streamify.aaronsoft.es?

                Por favor responde:
                ✅ SI (si ya tienes cuenta)
                ❌ NO (si necesitas crear cuenta)"

                ⚠️ IMPORTANTE - FORMATO DE ACTUALIZACIÓN DE MEMORIA (ejecutar ANTES de enviar mensaje):

                update_memory debe recibir EXACTAMENTE estos valores:

                - chat_id: (número del chat de Telegram)
                - step: "inicio" (string - SE MANTIENE en inicio esperando respuesta)
                - proceso: null (null, no string "null")
                - ultimo_mensaje_bot: "👋 ¡Hola! Para usar Streamify Bot necesito vincular tu cuenta.\n\n¿Ya tienes una cuenta en https://streamify.aaronsoft.es?\n\nPor favor responde:\n✅ SI (si ya tienes cuenta)\n❌ NO (si necesitas crear cuenta)" (string - el mensaje EXACTO que se enviará)
                - ultimo_mensaje_usuario: obtener el TEXTO del mensaje de Telegram usando: item.message.text o $json.message.text (string - ejemplo: "hola", "Hola pibe", "iniciar")
                - datos: JSON.stringify({}) (string JSON vacío: "{}")
                - intentos: 0 (número)

                ❌ NO HACER:
                - NO guardar ultimo_mensaje_usuario como objeto: {}
                - NO guardar ultimo_mensaje_usuario como JSON string: "{}"
                - NO guardar ultimo_mensaje_usuario como undefined o null

                ✅ SÍ HACER:
                - Guardar ultimo_mensaje_usuario como STRING con el texto del mensaje: "hola", "Hola pibe", etc.
                - Obtener el texto desde: $json.message.text o item.message.text
                - Ejemplo correcto: ultimo_mensaje_usuario: "Hola pibe"

                CASO 2 - RESPUESTA DEL USUARIO (usuario responde "SI" o "NO"):
                ================================================================
                ACCIÓN: Evaluar la respuesta del usuario y redirigir al flujo correspondiente.

                VALIDAR RESPUESTA: revisar el texto del mensaje recibido:
                - Obtener texto desde: $json.message.text o item.message.text
                - Si usuario escribió "SI", "SÍ", "S", "YES", "Y" → Cambiar a: step=login_email, proceso=login
                - Si usuario escribió "NO", "N" → Cambiar a: step=registro_nombre, proceso=registro
                - Si respuesta no válida → Mantener step=inicio y repetir pregunta

                ⚠️ IMPORTANTE - FORMATO DE ACTUALIZACIÓN DE MEMORIA (ejecutar ANTES de enviar mensaje):

                Si respuesta es "SI":
                update_memory debe recibir:
                - chat_id: (número del chat)
                - step: "login_email" (string - CAMBIA a login)
                - proceso: "login" (string)
                - ultimo_mensaje_bot: "" (string vacío, el siguiente paso enviará su mensaje)
                - ultimo_mensaje_usuario: obtener TEXTO del mensaje desde $json.message.text (ejemplo: "SI", "si", "SÍ")
                - datos: JSON.stringify({}) (string JSON vacío)
                - intentos: 0

                Si respuesta es "NO":
                update_memory debe recibir:
                - chat_id: (número del chat)
                - step: "registro_nombre" (string - CAMBIA a registro)
                - proceso: "registro" (string)
                - ultimo_mensaje_bot: "" (string vacío, el siguiente paso enviará su mensaje)
                - ultimo_mensaje_usuario: obtener TEXTO del mensaje desde $json.message.text (ejemplo: "NO", "no", "N")
                - datos: JSON.stringify({}) (string JSON vacío)
                - intentos: 0

                Si respuesta inválida:
                update_memory debe recibir:
                - chat_id: (número del chat)
                - step: "inicio" (string - MANTENER en inicio)
                - proceso: null (null, no string)
                - ultimo_mensaje_bot: "Por favor responde SI o NO" (string)
                - ultimo_mensaje_usuario: obtener TEXTO del mensaje desde $json.message.text (la respuesta inválida)
                - datos: JSON.stringify({}) (string JSON vacío)
                - intentos: 0

                DATOS A GUARDAR: JSON.stringify({}) (string JSON vacío en todos los casos)',
                'next_step' => 'login_email', // Depende de la respuesta: login_email o registro_nombre
            ],

            [
                'name' => 'login_email',
                'description' => 'PASO LOGIN EMAIL: Usuario ya tiene cuenta y debe proporcionar su email.

                ACCIÓN: Pedir el email de registro al usuario.

                MENSAJE A ENVIAR:
                "📧 Perfecto. Por favor ingresa tu email de registro:"

                VALIDAR RESPUESTA:
                1. Obtener el email del mensaje: $(Telegram Trigger).item.json.message.text
                2. Verificar que contiene @ y dominio
                3. Si formato inválido → Mantener en este paso y solicitar nuevamente
                4. Si formato válido → Avanzar al siguiente paso con el email guardado

                FORMATO DE ACTUALIZACIÓN DE MEMORIA:
                Si formato válido:
                - step: "login_password"
                - proceso: "login"
                - ultimo_mensaje_bot: "📧 Perfecto. Por favor ingresa tu email de registro:"
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text (el email ingresado por el usuario)
                - datos: {"email": "email_del_usuario"}

                Si formato inválido:
                - step: "login_email" (mantener)
                - proceso: "login"
                - ultimo_mensaje_bot: "❌ Formato de email inválido. Por favor ingresa un email válido:"
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text (el email inválido ingresado)
                - datos: {}

                DATOS A GUARDAR: {"email": "email_del_usuario"}',
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
                2. Obtener el password del mensaje: $(Telegram Trigger).item.json.message.text
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
                Si credenciales válidas:
                - step: "completado"
                - proceso: "login"
                - ultimo_mensaje_bot: "✅ ¡Perfecto! Tu cuenta ha sido vinculada exitosamente..." (mensaje completo)
                - ultimo_mensaje_usuario: "***" (NO guardar el password real por seguridad, usar asteriscos)
                - intentos: 0
                - datos: {"email": "mantener_valor"} (se eliminará con delete_memory)

                Si credenciales inválidas (intentos < 3):
                - step: "login_password" (mantener)
                - proceso: "login"
                - ultimo_mensaje_bot: "❌ Contraseña incorrecta. Intento X/3..." (mensaje de error con número de intento)
                - ultimo_mensaje_usuario: "***" (NO guardar el password real, usar asteriscos)
                - intentos: incrementar +1
                - datos: {"email": "mantener_valor"}

                Si credenciales inválidas (intentos >= 3):
                - step: "inicio"
                - proceso: null
                - ultimo_mensaje_bot: "❌ Demasiados intentos fallidos..."
                - ultimo_mensaje_usuario: "***" (NO guardar el password real)
                - intentos: 0
                - datos: {}

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
                1. Obtener el texto ingresado: $(Telegram Trigger).item.json.message.text
                2. Limpiar espacios adicionales: trim(texto)
                3. Verificar longitud: debe tener al menos 3 caracteres
                4. Si es muy corto (< 3 caracteres): mantener en este paso
                5. Si es válido (>= 3 caracteres): avanzar al siguiente paso

                FORMATO DE ACTUALIZACIÓN DE MEMORIA:
                Si nombre válido (>= 3 caracteres):
                - step: "registro_email"
                - proceso: "registro"
                - ultimo_mensaje_bot: "📝 ¡Perfecto! Vamos a crear tu cuenta..." (mensaje completo)
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text (el nombre ingresado por el usuario)
                - datos: {"nombre": "valor_ingresado_limpio"}

                Si nombre inválido (< 3 caracteres):
                - step: "registro_nombre" (mantener)
                - proceso: "registro"
                - ultimo_mensaje_bot: "❌ El nombre debe tener al menos 3 caracteres..." (mensaje de error)
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text (el nombre inválido ingresado)
                - datos: {}

                DATOS A GUARDAR: {"nombre": "nombre_completo_del_usuario"}',
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
                2. Obtener el email: $(Telegram Trigger).item.json.message.text
                3. Limpiar espacios y convertir a minúsculas: trim(toLowerCase(email))
                4. Validar formato básico (debe contener @ y dominio)
                5. Si formato inválido: mantener en este paso
                6. Si formato válido: usar tool "check_email_exists"

                SI EMAIL YA EXISTE (tool devuelve true):
                - Ofrecer cambiar a login o usar otro email
                - Esperar respuesta del usuario (SI para login, NO para otro email)

                SI EMAIL NO EXISTE (tool devuelve false):
                - Avanzar al siguiente paso con email guardado

                FORMATO DE ACTUALIZACIÓN DE MEMORIA:
                Si email válido y NO existe:
                - step: "registro_telefono"
                - proceso: "registro"
                - ultimo_mensaje_bot: "📧 Gracias {datos.nombre}. Ahora, ¿cuál es tu email?"
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text (el email ingresado)
                - datos: {"nombre": "mantener", "email": "email_validado@ejemplo.com"}

                Si email existe y usuario dice SI (cambiar a login):
                - step: "login_email"
                - proceso: "login"
                - ultimo_mensaje_bot: "❌ Este email ya está registrado... ¿Quieres hacer login?"
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text ("SI")
                - datos: {"email": "email_existente"}

                Si email existe y usuario dice NO (usar otro email):
                - step: "registro_email" (mantener)
                - proceso: "registro"
                - ultimo_mensaje_bot: "📧 Entendido. Por favor ingresa otro email:"
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text ("NO")
                - datos: {"nombre": "mantener"}

                Si formato inválido:
                - step: "registro_email" (mantener)
                - proceso: "registro"
                - ultimo_mensaje_bot: "❌ Formato de email inválido..."
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text (email inválido)
                - datos: {"nombre": "mantener"}

                DATOS A GUARDAR: {"nombre": "mantener", "email": "email_validado@ejemplo.com"}',
                'next_step' => 'registro_telefono',
            ],

            [
                'name' => 'registro_telefono',
                'description' => 'PASO REGISTRO TELÉFONO: Usuario debe proporcionar su número de teléfono.

                PRE-REQUISITO: Deben existir datos.nombre y datos.email en memoria

                ACCIÓN: Pedir número de teléfono y validar formato básico.

                MENSAJE A ENVIAR:
                "📱 Perfecto. ¿Cuál es tu número de teléfono?

                Puedes incluir código de país si lo deseas (ejemplo: +34 612345678 o 612345678)"

                VALIDAR RESPUESTA:
                1. Verificar que datos.nombre y datos.email existen (si no, regresar)
                2. Obtener el teléfono: $(Telegram Trigger).item.json.message.text
                3. Validar: mínimo 7 caracteres, máximo 20, solo dígitos y símbolos permitidos
                4. Si formato inválido: mantener en este paso
                5. Si formato válido: avanzar al siguiente paso

                FORMATO DE ACTUALIZACIÓN DE MEMORIA:
                Si teléfono válido:
                - step: "registro_password"
                - proceso: "registro"
                - ultimo_mensaje_bot: "📱 Perfecto. ¿Cuál es tu número de teléfono?..."
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text (el teléfono ingresado)
                - datos: {"nombre": "mantener", "email": "mantener", "telefono": "numero_telefono"}

                Si teléfono inválido:
                - step: "registro_telefono" (mantener)
                - proceso: "registro"
                - ultimo_mensaje_bot: "❌ Formato de teléfono inválido..."
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text (teléfono inválido)
                - datos: {"nombre": "mantener", "email": "mantener"}

                DATOS A GUARDAR: {"nombre": "mantener", "email": "mantener", "telefono": "numero_telefono"}',
                'next_step' => 'registro_password',
            ],

            [
                'name' => 'registro_password',
                'description' => 'PASO REGISTRO PASSWORD: Usuario debe crear una contraseña segura.

                PRE-REQUISITO: Deben existir datos.nombre, datos.email, datos.telefono en memoria

                ACCIÓN: Pedir que el usuario cree una contraseña para su nueva cuenta.

                MENSAJE A ENVIAR:
                "🔐 Ahora crea una contraseña segura para tu cuenta.

                Debe tener al menos 6 caracteres.

                Por favor ingresa tu contraseña:"

                VALIDAR RESPUESTA:
                1. Verificar que datos.nombre, datos.email y datos.telefono existen en memoria
                2. Obtener el password: $(Telegram Trigger).item.json.message.text
                3. Validar longitud: debe tener al menos 6 caracteres
                4. Si es muy corta (< 6 caracteres): mantener en este paso
                5. Si es válida (>= 6 caracteres): avanzar al siguiente paso

                FORMATO DE ACTUALIZACIÓN DE MEMORIA:
                Si password válido (>= 6 caracteres):
                - step: "registro_confirmar"
                - proceso: "registro"
                - ultimo_mensaje_bot: "🔐 Ahora crea una contraseña segura..."
                - ultimo_mensaje_usuario: "***" (NO guardar el password real por seguridad, usar asteriscos)
                - datos: {"nombre": "mantener", "email": "mantener", "telefono": "mantener", "password": "contraseña_temporal_en_texto_plano"}

                Si password inválido (< 6 caracteres):
                - step: "registro_password" (mantener)
                - proceso: "registro"
                - ultimo_mensaje_bot: "❌ La contraseña debe tener al menos 6 caracteres..."
                - ultimo_mensaje_usuario: "***" (NO guardar el password real)
                - datos: {"nombre": "mantener", "email": "mantener", "telefono": "mantener"}

                DATOS A GUARDAR:
                {"nombre": "mantener", "email": "mantener", "telefono": "mantener", "password": "contraseña_temporal"}

                IMPORTANTE - SEGURIDAD:
                - El password se guarda TEMPORALMENTE en datos.password (no en ultimo_mensaje_usuario)
                - En ultimo_mensaje_usuario SIEMPRE guardar "***" por seguridad
                - El password será encriptado con bcrypt al crear el cliente en el siguiente paso
                - NUNCA guardar el password en ultimo_mensaje_usuario',
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
                2. Obtener la respuesta: $(Telegram Trigger).item.json.message.text
                3. Evaluar la respuesta y ejecutar acción correspondiente

                SI USUARIO CONFIRMA ("SI", "SÍ", "S", "YES", "Y"):
                a. Usar tool "Registrar_cliente" con datos de memoria
                b. Si registro EXITOSO:
                   - Usar tool "Update_telegram_chat_id" para vincular
                   - Actualizar memoria: step=completado
                   - Responder: "🎉 ¡Felicidades! Tu cuenta ha sido creada..."
                   - Usar tool "delete_memory"
                c. Si registro FALLA:
                   - Actualizar memoria: step=inicio, limpiar datos
                   - Responder: "❌ Error al crear la cuenta..."

                SI USUARIO NO CONFIRMA ("NO", "N"):
                - Actualizar memoria: step=registro_nombre, limpiar datos
                - Responder: "📝 Entendido. Vamos a comenzar de nuevo..."

                SI RESPUESTA INVÁLIDA:
                - Mantener step=registro_confirmar, mantener datos
                - Responder: "Por favor responde SI para confirmar o NO para corregir datos."

                FORMATO DE ACTUALIZACIÓN DE MEMORIA:
                Si confirma y registro exitoso:
                - step: "completado"
                - proceso: "registro"
                - ultimo_mensaje_bot: "🎉 ¡Felicidades! Tu cuenta ha sido creada y vinculada exitosamente..."
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text ("SI")
                - datos: se eliminarán con delete_memory

                Si confirma pero registro falla:
                - step: "inicio"
                - proceso: null
                - ultimo_mensaje_bot: "❌ Error al crear la cuenta..."
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text ("SI")
                - datos: {}

                Si NO confirma:
                - step: "registro_nombre"
                - proceso: "registro"
                - ultimo_mensaje_bot: "📝 Entendido. Vamos a comenzar de nuevo con el registro..."
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text ("NO")
                - datos: {}

                Si respuesta inválida:
                - step: "registro_confirmar" (mantener)
                - proceso: "registro"
                - ultimo_mensaje_bot: "Por favor responde SI para confirmar o NO para corregir datos."
                - ultimo_mensaje_usuario: $(Telegram Trigger).item.json.message.text (respuesta inválida)
                - datos: mantener todos

                DATOS A GUARDAR: {"nombre": "mantener", "email": "mantener", "telefono": "mantener", "password": "mantener_temporal"}',
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
