# Ejemplo de Uso: Bot Telegram - Mover Cliente a Otro Servicio

## 🤖 Contexto para el Agente IA

Cuando un usuario del bot de Telegram te informe que un servicio está caído (por ejemplo: "Se cayó Netflix") o necesite ayuda para cambiar de servicio, puedes usar este endpoint para mover clientes automáticamente.

## 📋 Flujo Conversacional

### Escenario 1: Servicio Caído (Emergencia)

**Usuario (Admin)**: "Se cayó Netflix, necesito mover a todos los clientes a otro servicio"

**Bot**: "Entendido. Veo que tienes 15 clientes activos en Netflix. ¿A qué servicio deseas moverlos?"

**Opciones disponibles**:
- 🎬 MAX (8 espacios disponibles)
- 🎭 Disney+ Premium (12 espacios disponibles)
- 📺 Prime Video (5 espacios disponibles)

**Usuario**: "MAX"

**Bot procede**:
1. Obtiene lista de clientes de Netflix
2. Por cada cliente, llama al endpoint
3. Recopila las nuevas credenciales
4. Envía mensaje a cada cliente con sus nuevas credenciales

```python
# Pseudocódigo del Bot

async def mover_clientes_masivo(servicio_origen: str, servicio_destino: str):
    # 1. Obtener clientes del servicio origen
    usuarios = await obtener_usuarios_por_servicio(servicio_origen)
    
    resultados = {
        'exitosos': [],
        'fallidos': []
    }
    
    # 2. Intentar mover cada usuario
    for usuario in usuarios:
        try:
            response = await http_client.post(
                f"{API_URL}/api/v2/tech-usuarios/mover-otro-servicio",
                headers={"Authorization": f"Bearer {BOT_TOKEN}"},
                json={
                    "iddet": usuario['iddet'],
                    "servicio_destino": servicio_destino
                }
            )
            
            if response.status_code == 200:
                data = response.json()['data']
                resultados['exitosos'].append({
                    'cliente': data['cliente'],
                    'iddet': data['iddet'],
                    'credenciales': data['cuenta_nueva']
                })
                
                # Notificar al cliente
                await notificar_cliente_telegram(
                    cliente_id=usuario['cliente_telegram_id'],
                    mensaje=generar_mensaje_cambio_servicio(data)
                )
            else:
                resultados['fallidos'].append({
                    'iddet': usuario['iddet'],
                    'error': response.json()['message']
                })
                
        except Exception as e:
            resultados['fallidos'].append({
                'iddet': usuario['iddet'],
                'error': str(e)
            })
            
        # Delay para no saturar el servidor
        await asyncio.sleep(0.5)
    
    # 3. Reportar resultados
    return resultados
```

### Escenario 2: Cambio Individual

**Usuario (Admin)**: "El cliente Juan Pérez del iddet 12345 quiere cambiar de Disney+ a Netflix"

**Bot**:
```python
async def cambiar_servicio_individual(iddet: int, servicio_destino: str):
    # 1. Obtener información actual del cliente
    usuario_actual = await obtener_usuario(iddet)
    
    # 2. Validar que haya espacio en el servicio destino
    espacios = await verificar_espacios_disponibles(servicio_destino)
    
    if espacios <= 0:
        return "❌ No hay espacios disponibles en {servicio_destino}"
    
    # 3. Ejecutar el cambio
    response = await http_client.post(
        f"{API_URL}/api/v2/tech-usuarios/mover-otro-servicio",
        headers={"Authorization": f"Bearer {BOT_TOKEN}"},
        json={
            "iddet": iddet,
            "servicio_destino": servicio_destino
        }
    )
    
    if response.status_code == 200:
        data = response.json()['data']
        mensaje = f"""
✅ Cliente movido exitosamente

👤 Cliente: {data['cliente']}
🔄 De: {data['servicio_origen']} → A: {data['servicio_destino']}

📋 Nuevas credenciales:
👤 Usuario: {data['cuenta_nueva']['usuario']}
🔑 Contraseña: {data['cuenta_nueva']['contrasena']}
📍 Perfil: {data['cuenta_nueva']['perfil']}
🔢 PIN: {data['cuenta_nueva']['pin']}

⚠️ Por favor, notifica al cliente con estas credenciales.
        """
        return mensaje
    else:
        return f"❌ Error: {response.json()['message']}"
```

## 📨 Plantilla de Mensaje para Clientes

```python
def generar_mensaje_cambio_servicio(data):
    servicio_destino = data['servicio_destino']
    cuenta = data['cuenta_nueva']
    
    # Mapeo de nombres amigables
    servicios_nombres = {
        'NETFLIX': 'Netflix',
        'DISNEYP': 'Disney+ Premium',
        'DISNEYS': 'Disney+ Estándar',
        'MAX': 'MAX (HBO Max)',
        'PRIME': 'Prime Video',
        'PARAMOUNT': 'Paramount+',
        'CRUNCHY': 'Crunchyroll',
        'SPOTIFY': 'Spotify Premium',
        'MAGIS': 'Magis TV'
    }
    
    nombre_servicio = servicios_nombres.get(servicio_destino, servicio_destino)
    
    mensaje = f"""
🎬 **CAMBIO DE SERVICIO**

Hola! Debido a problemas técnicos con tu servicio anterior, hemos migrado tu cuenta a **{nombre_servicio}** 🎉

📋 **Tus nuevas credenciales:**
━━━━━━━━━━━━━━━━━━━━
👤 Usuario: `{cuenta['usuario']}`
🔑 Contraseña: `{cuenta['contrasena']}`
📍 Perfil: **#{cuenta['perfil']}**
🔢 PIN: `{cuenta['pin']}`
━━━━━━━━━━━━━━━━━━━━

⚠️ **IMPORTANTE:**
• No cambies la contraseña
• No modifiques el perfil
• Usa únicamente el perfil #{cuenta['perfil']}

Tu fecha de vencimiento sigue siendo la misma 📅

¿Necesitas ayuda? Contáctanos 💬
    """
    
    return mensaje
```

## 🔄 Flujo Completo con Validaciones

```python
class ServicioMigracionBot:
    def __init__(self, api_url: str, bot_token: str):
        self.api_url = api_url
        self.bot_token = bot_token
    
    async def verificar_capacidad(self, servicio_destino: str) -> dict:
        """Verifica cuántos espacios hay disponibles en un servicio"""
        response = await http_client.get(
            f"{self.api_url}/api/v2/tech-accounts/espacios-disponibles",
            headers={"Authorization": f"Bearer {self.bot_token}"}
        )
        
        if response.status_code == 200:
            espacios = response.json()['data']
            return {
                'servicio': servicio_destino,
                'espacios': espacios.get(servicio_destino, 0)
            }
        return {'servicio': servicio_destino, 'espacios': 0}
    
    async def obtener_usuarios_servicio(self, servicio: str) -> list:
        """Obtiene todos los usuarios activos de un servicio"""
        response = await http_client.get(
            f"{self.api_url}/api/v2/tech-usuarios/estadisticas",
            headers={"Authorization": f"Bearer {self.bot_token}"}
        )
        
        if response.status_code == 200:
            # Filtrar por servicio específico
            # (Este endpoint necesitaría modificación para filtrar)
            return response.json()['data']['por_servicio']
        return []
    
    async def migrar_con_validacion(
        self, 
        iddet: int, 
        servicio_destino: str,
        notificar_telegram: bool = True
    ) -> dict:
        """
        Migra un usuario con todas las validaciones necesarias
        """
        try:
            # 1. Verificar capacidad del servicio destino
            capacidad = await self.verificar_capacidad(servicio_destino)
            
            if capacidad['espacios'] <= 0:
                return {
                    'success': False,
                    'error': f"No hay espacios en {servicio_destino}"
                }
            
            # 2. Obtener info del usuario actual
            usuario = await self.obtener_usuario(iddet)
            
            if not usuario:
                return {
                    'success': False,
                    'error': 'Usuario no encontrado'
                }
            
            # 3. Ejecutar la migración
            response = await http_client.post(
                f"{self.api_url}/api/v2/tech-usuarios/mover-otro-servicio",
                headers={"Authorization": f"Bearer {self.bot_token}"},
                json={
                    "iddet": iddet,
                    "servicio_destino": servicio_destino
                }
            )
            
            if response.status_code == 200:
                data = response.json()['data']
                
                # 4. Notificar al cliente si está habilitado
                if notificar_telegram and usuario.get('telegram_id'):
                    await self.enviar_notificacion_cliente(
                        telegram_id=usuario['telegram_id'],
                        data=data
                    )
                
                return {
                    'success': True,
                    'data': data
                }
            else:
                return {
                    'success': False,
                    'error': response.json()['message']
                }
                
        except Exception as e:
            return {
                'success': False,
                'error': str(e)
            }
    
    async def enviar_notificacion_cliente(self, telegram_id: int, data: dict):
        """Envía notificación al cliente con las nuevas credenciales"""
        mensaje = generar_mensaje_cambio_servicio(data)
        
        # Enviar por Telegram
        await bot.send_message(
            chat_id=telegram_id,
            text=mensaje,
            parse_mode='Markdown'
        )
```

## 📊 Dashboard de Monitoreo

```python
async def generar_reporte_migracion(servicio_origen: str, servicio_destino: str):
    """Genera un reporte antes de hacer la migración masiva"""
    
    # 1. Contar usuarios del servicio origen
    usuarios_origen = await contar_usuarios_servicio(servicio_origen)
    
    # 2. Verificar espacios en destino
    espacios_destino = await verificar_espacios_disponibles(servicio_destino)
    
    # 3. Generar reporte
    reporte = f"""
📊 **REPORTE DE MIGRACIÓN**

🔴 Servicio Origen: {servicio_origen}
👥 Usuarios a migrar: {usuarios_origen}

🟢 Servicio Destino: {servicio_destino}
📦 Espacios disponibles: {espacios_destino}

{'✅ Migración viable' if espacios_destino >= usuarios_origen else '⚠️ NO hay suficiente capacidad'}

{'Proceder con la migración?' if espacios_destino >= usuarios_origen else f'Faltan {usuarios_origen - espacios_destino} espacios'}
    """
    
    return reporte
```

## 🎯 Comandos del Bot

```python
# Comando: /migrar_servicio
@bot.command('migrar_servicio')
async def cmd_migrar_servicio(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """
    Uso: /migrar_servicio [iddet] [servicio_destino]
    Ejemplo: /migrar_servicio 12345 MAX
    """
    try:
        iddet = int(context.args[0])
        servicio_destino = context.args[1].upper()
        
        resultado = await migrar_con_validacion(iddet, servicio_destino)
        
        if resultado['success']:
            await update.message.reply_text(
                f"✅ Cliente migrado exitosamente a {servicio_destino}",
                parse_mode='Markdown'
            )
        else:
            await update.message.reply_text(
                f"❌ Error: {resultado['error']}"
            )
            
    except Exception as e:
        await update.message.reply_text(f"❌ Error: {str(e)}")

# Comando: /migrar_masivo
@bot.command('migrar_masivo')
async def cmd_migrar_masivo(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """
    Uso: /migrar_masivo [servicio_origen] [servicio_destino]
    Ejemplo: /migrar_masivo NETFLIX MAX
    """
    try:
        servicio_origen = context.args[0].upper()
        servicio_destino = context.args[1].upper()
        
        # Generar reporte primero
        reporte = await generar_reporte_migracion(servicio_origen, servicio_destino)
        await update.message.reply_text(reporte, parse_mode='Markdown')
        
        # Solicitar confirmación
        await update.message.reply_text(
            "¿Confirmas la migración? Responde SI para continuar"
        )
        
        # Aquí iría el manejador de confirmación
        
    except Exception as e:
        await update.message.reply_text(f"❌ Error: {str(e)}")
```

---

**Nota**: Este es un ejemplo conceptual. La implementación real dependerá de tu framework específico de Telegram Bot (python-telegram-bot, aiogram, etc.)
