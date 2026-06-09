# API: Listar Usuarios con Filtros

## 📋 Descripción General

Este endpoint permite al **Agente IA** listar usuarios activos con filtros esenciales para facilitar la búsqueda. Es especialmente útil para:

- 🔍 Buscar clientes específicos por nombre
- 📺 Listar todos los usuarios de un servicio
- 📊 Obtener listados paginados

## 🔌 Endpoint

```
GET /api/v2/tech-usuarios/listar
```

**Autenticación**: Bearer Token (Bot Telegram)

## 🎯 Query Parameters (Todos opcionales)

| Parámetro | Tipo | Valores | Descripción |
|-----------|------|---------|-------------|
| `servicio` | string | NETFLIX, MAX, PRIME, etc. | Filtrar por servicio específico |
| `cliente` | string | - | Buscar por nombre de cliente (búsqueda parcial) |
| `limit` | integer | 1-200 | Límite de resultados (default: 50) |
| `offset` | integer | 0+ | Offset para paginación (default: 0) |

## 📤 Respuesta Exitosa (200 OK)

```json
{
  "success": true,
  "message": "Se encontraron 15 usuarios",
  "data": [
    {
      "iddet": 12345,
      "cliente": {
        "idcli": 789,
        "nombre": "Juan Pérez",
        "whatsapp": "573001234567",
        "telegram_id": 123456789
      },
      "servicio": "Netflix",
      "cuenta": {
        "idcue": "NETFLIX#001",
        "usuario": "netflix@email.com",
        "contrasena": "password123",
        "estado": "activa",
        "caida": false
      },
      "perfil": {
        "numero": 2,
        "pin": "1234"
      },
      "vencimiento": {
        "fecha": "2026-02-15",
        "dias_restantes": 11,
        "estado": "vigente"
      },
      "venta": {
        "idven": 456,
        "monto": 15000,
        "fecha_venta": "2026-01-15"
      },
      "estado_cobro": "COBRADO",
      "activo": true
    }
  ],
  "pagination": {
    "total": 150,
    "count": 15,
    "limit": 50,
    "offset": 0,
    "has_more": true
  },
  "filters_applied": {
    "servicio": "NETFLIX",
    "cliente": "Juan"
  }
}
```

## 📋 Ejemplos de Uso

### Ejemplo 1: Todos los usuarios de Netflix

```bash
GET /api/v2/tech-usuarios/listar?servicio=NETFLIX
```

### Ejemplo 2: Usuarios vencidos de MAX

```bash
GET /api/v2/tech-usuarios/listar?servicio=MAX&estado=vencido
```

### Ejemplo 3: Buscar cliente por nombre

```bash

### Ejemplo 1: Todos los usuarios de Netflix

```bash
GET /api/v2/tech-usuarios/listar?servicio=NETFLIX
```

### Ejemplo 2: Buscar cliente por nombre

```bash
GET /api/v2/tech-usuarios/listar?cliente=Juan
```

Encuentra: "Juan Pérez", "Juan Carlos", "María Juan", etc.

### Ejemplo 3: Usuarios de Netflix con nombre "Pablo"

```bash
GET /api/v2/tech-usuarios/listar?servicio=NETFLIX&cliente=Pablo
```

### Ejemplo 4: Paginación (20 resultados por página)

```bash
# Primera página
GET /api/v2/tech-usuarios/listar?limit=20&offset=0

# Segunda página
GET /api/v2/tech-usuarios/listar?limit=20&offset=20

# Tercera página
GET /api/v2/tech-usuarios/listar?limit=20&offset=40
```

### Ejemplo 5: Buscar en Crunchyroll

```bash
GET /api/v2/tech-usuarios/listar?servicio=CRUNCHY
```

## 🤖 Uso en el Bot de Telegram

### Caso 1: Buscar cliente antes de operación

```python
async def buscar_cliente_por_nombre(nombre: str):
    """Busca clientes por nombre para operaciones"""
    response = await http_client.get(
        f"{API_URL}/api/v2/tech-usuarios/listar",
        headers={"Authorization": f"Bearer {BOT_TOKEN}"},
        params={
            "cliente": nombre,
            "limit": 10
        }
    )

    if response.status_code == 200:
        data = response.json()
        usuarios = data['data']

        if len(usuarios) == 0:
            return f"❌ No se encontraron clientes con el nombre '{nombre}'"

        # Mostrar opciones
        mensaje = f"🔍 Encontrados {len(usuarios)} clientes:\n\n"
        for i, user in enumerate(usuarios, 1):
            mensaje += f"{i}. {user['cliente']['nombre']}\n"
            mensaje += f"   📺 {user['servicio']} - Perfil #{user['perfil']['numero']}\n"
            mensaje += f"   📅 Vence: {user['vencimiento']['fecha']} ({user['vencimiento']['dias_restantes']} días)\n"
            mensaje += f"   🆔 IdDet: {user['iddet']}\n\n"

        return mensaje
```

### Caso 2: Listar todos los usuarios de un servicio

```python
async def listar_usuarios_servicio(servicio: str):
    """Lista todos los usuarios de un servicio específico"""
    response = await http_client.get(
        f"{API_URL}/api/v2/tech-usuarios/listar",
        headers={"Authorization": f"Bearer {BOT_TOKEN}"},
        params={
            "servicio": servicio.upper(),
            "limit": 100
        }
    )

    if response.status_code == 200:
        data = response.json()
        total = data['pagination']['total']
        usuarios = data['data']

        mensaje = f"📺 Servicio: {servicio}\n"
        mensaje += f"👥 Total usuarios: {total}\n\n"

        # Mostrar primeros 10
        for user in usuarios[:10]:
            mensaje += f"👤 {user['cliente']['nombre']}\n"
            mensaje += f"📅 Vence: {user['vencimiento']['fecha']}\n"
            mensaje += f"✅ Estado: {user['vencimiento']['estado']}\n\n"

        return mensaje
```

### Caso 3: Búsqueda específica por servicio y cliente

```python
async def buscar_usuario_especifico(servicio: str, nombre_cliente: str):
    """Busca un usuario específico en un servicio"""
    response = await http_client.get(
        f"{API_URL}/api/v2/tech-usuarios/listar",
        headers={"Authorization": f"Bearer {BOT_TOKEN}"},
        params={
            "servicio": servicio.upper(),
            "cliente": nombre_cliente,
            "limit": 5
        }
    )

    if response.status_code == 200:
        data = response.json()
        usuarios = data['data']

        if len(usuarios) == 0:
            return f"❌ No se encontró '{nombre_cliente}' en {servicio}"

        mensaje = f"🔍 Resultados para '{nombre_cliente}' en {servicio}:\n\n"

Tu servicio de **{user['servicio']}** vence pronto:
📅 Fecha de vencimiento: {user['vencimiento']['fecha']}
⏳ Días restantes: {user['vencimiento']['dias_restantes']}

Renueva ahora para no perder el acceso 🎬
                """

                await bot.send_message(
                    chat_id=user['cliente']['telegram_id'],
                    text=mensaje,
                    parse_mode='Markdown'

        for user in usuarios:
            mensaje += f"👤 {user['cliente']['nombre']}\n"
            mensaje += f"📅 Vence: {user['vencimiento']['fecha']}\n"
            mensaje += f"🔢 Perfil: #{user['perfil']['numero']}\n"
            mensaje += f"🆔 IdDet: {user['iddet']}\n\n"

        return mensaje
```

### Caso 4: Paginación para listados grandes

```python
async def listar_todos_usuarios_servicio(servicio: str):
    """Lista TODOS los usuarios de un servicio usando paginación"""
    all_usuarios = []
    offset = 0
    limit = 100

    while True:
        response = await http_client.get(
            f"{API_URL}/api/v2/tech-usuarios/listar",
            headers={"Authorization": f"Bearer {BOT_TOKEN}"},
            params={
                "servicio": servicio.upper(),
                "limit": limit,
                "offset": offset
            }
        )

        if response.status_code == 200:
            data = response.json()
            usuarios = data['data']
            all_usuarios.extend(usuarios)

            # Si no hay más páginas, terminar
            if not data['pagination']['has_more']:
                break

            offset += limit
        else:
            break

    return all_usuarios
```

## 📋 Respuestas de Error
        await update.message.reply_text(
            f"🔍 Encontrados {len(usuarios)} usuarios:",
            reply_markup=reply_markup
        )
```

## 🔄 Combinación con otras APIs

### Flujo completo: Buscar y mover usuario

```python
async def buscar_y_mover_usuario(nombre_cliente: str, servicio_destino: str):
    """Busca un usuario y lo mueve a otro servicio"""

## 📋 Respuestas de Error

### 422 - Validación fallida

```json
{
  "success": false,
  "message": "Validación fallida",
  "errors": {
    "limit": ["The limit must not be greater than 200."]
  }
}
```

### 500 - Error del servidor

```json
{
  "success": false,
  "message": "Error al listar usuarios",
  "error": "Mensaje de error específico"
}
```

## 🔄 Integración con Mover Usuario

Ejemplo completo de buscar y mover un usuario:

```python
async def buscar_y_mover_usuario(nombre_cliente: str, servicio_destino: str):
    """Busca un usuario por nombre y lo mueve a otro servicio"""

    # 1. Buscar usuario
    response = await http_client.get(
        f"{API_URL}/api/v2/tech-usuarios/listar",
        headers={"Authorization": f"Bearer {BOT_TOKEN}"},
        params={"cliente": nombre_cliente, "limit": 1}
    )

    if response.status_code != 200:
        return "❌ Error al buscar usuario"

    data = response.json()
    if data['pagination']['total'] == 0:
        return f"❌ No se encontró el usuario '{nombre_cliente}'"

    usuario = data['data'][0]
    iddet = usuario['iddet']

    # 2. Mover a otro servicio
    response_move = await http_client.post(
        f"{API_URL}/api/v2/tech-usuarios/mover-otro-servicio",
        headers={"Authorization": f"Bearer {BOT_TOKEN}"},
        json={
            "iddet": iddet,
            "servicio_destino": servicio_destino
        }
    )

    if response_move.status_code == 200:
        data_move = response_move.json()['data']
        return f"""
✅ Usuario movido exitosamente!

👤 {data_move['cliente']}
🔄 {data_move['servicio_origen']} → {data_move['servicio_destino']}

📋 Nuevas credenciales:
Usuario: {data_move['cuenta_nueva']['usuario']}
Contraseña: {data_move['cuenta_nueva']['contrasena']}
Perfil: #{data_move['cuenta_nueva']['perfil']}
PIN: {data_move['cuenta_nueva']['pin']}
        """
    else:
        return f"❌ Error al mover usuario: {response_move.json()['message']}"
```

## ⚡ Ventajas de esta API

1. **Búsqueda simple**: Solo los filtros necesarios
2. **Paginación eficiente**: Manejo de grandes volúmenes
3. **Información completa**: Todos los datos en una respuesta
4. **Estado calculado**: Indica automáticamente vencimiento
5. **Fácil integración**: Compatible con otros endpoints

## 📊 Performance

- **Límite por defecto**: 50 usuarios
- **Máximo por request**: 200 usuarios
- **Recomendado para bot**: 10-50 usuarios por consulta
- **Soporte para paginación**: Sí (offset/limit)
- **Orden**: Siempre por fecha de vencimiento ascendente

## ⚠️ Consideraciones

1. **Sin filtros = Todos los usuarios**: Si no hay filtros, retorna todos (limitado por `limit`)
2. **Búsqueda parcial**: `cliente` hace búsqueda LIKE (case insensitive)
3. **Estado dinámico**: El estado se calcula en tiempo real
4. **Paginación**: Para más de 200 resultados, usar offset/limit múltiples veces

## 🔗 Endpoints Relacionados

- `POST /api/v2/tech-usuarios/mover-otro-servicio` - Mover usuario a otro servicio

---

**Fecha de creación**: 2026-02-04
**Última actualización**: 2026-02-04
**Versión**: 2.0 (Simplificada)
**Mantenedor**: Sistema de gestión de cuentas Streamify
