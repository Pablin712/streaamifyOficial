# API: Mover Cliente a Otro Servicio

## 📋 Descripción General

Este endpoint permite al **Técnico de Cuentas** (Agente IA) mover clientes de la mesa de trabajo de un servicio a otro servicio diferente. Es especialmente útil cuando:

- ❌ Un servicio se cae o deja de funcionar (ej: Netflix)
- 🔄 Necesitas redistribuir clientes por capacidad
- 🎯 Quieres ofrecer un upgrade o cambio de servicio

## 🔌 Endpoint

```
POST /api/v2/tech-usuarios/mover-otro-servicio
```

**Autenticación**: Bearer Token (Bot Telegram)

## 📥 Request Body

```json
{
  "iddet": 12345,
  "servicio_destino": "MAX"
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `iddet` | integer | ✅ Sí | ID del detalle de venta (usuario activo) |
| `servicio_destino` | string | ✅ Sí | Servicio destino: `NETFLIX`, `DISNEYP`, `DISNEYS`, `MAX`, `PRIME`, `PARAMOUNT`, `CRUNCHY`, `SPOTIFY`, `MAGIS` |

## 📤 Respuestas

### ✅ Éxito (200 OK)

```json
{
  "success": true,
  "message": "Cliente movido exitosamente a otro servicio",
  "data": {
    "cliente": "Juan Pérez",
    "iddet": 12345,
    "servicio_origen": "NETFLIX",
    "servicio_destino": "MAX",
    "cuenta_anterior": {
      "idcue": "NETFLIX#001",
      "usuario": "netflix@email.com"
    },
    "cuenta_nueva": {
      "idcue": "MAX#005",
      "usuario": "max@email.com",
      "contrasena": "password123",
      "perfil": 2,
      "pin": "1234"
    },
    "mensaje_completo": "Cliente Juan Pérez movido del servicio NETFLIX al servicio MAX - Cuenta: max@email.com - Clave: password123 - PIN de Perfil 2: 1234"
  }
}
```

### ❌ Error: Servicio sin disponibilidad (404)

```json
{
  "success": false,
  "message": "No hay cuentas disponibles en el servicio MAX",
  "detalle": "No se encontraron cuentas activas con espacios libres en el servicio destino"
}
```

### ❌ Error: Sin perfiles libres (404)

```json
{
  "success": false,
  "message": "No hay perfiles disponibles en las cuentas del servicio MAX",
  "detalle": "Se encontraron cuentas pero no tienen perfiles libres"
}
```

### ❌ Error: Mismo servicio (400)

```json
{
  "success": false,
  "message": "El cliente ya está en el servicio MAX. Use el endpoint de cambio de perfil si desea moverlo dentro del mismo servicio."
}
```

### ❌ Error: Usuario no encontrado (404)

```json
{
  "success": false,
  "message": "Usuario no encontrado o no está activo"
}
```

### ❌ Error: Validación (422)

```json
{
  "success": false,
  "message": "Validación fallida",
  "errors": {
    "iddet": ["El campo iddet es obligatorio."],
    "servicio_destino": ["El servicio seleccionado no es válido."]
  }
}
```

## 🎯 Casos de Uso

### Caso 1: Netflix se cayó, mover todos los clientes a MAX

**Paso 1:** Obtener todos los usuarios de Netflix en mesa de trabajo
```
GET /api/v2/tech-usuarios/estadisticas
```

**Paso 2:** Por cada usuario, moverlo a MAX
```bash
curl -X POST https://tu-dominio.com/api/v2/tech-usuarios/mover-otro-servicio \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "iddet": 12345,
    "servicio_destino": "MAX"
  }'
```

**Paso 3:** Notificar al cliente con las nuevas credenciales

### Caso 2: Upgrade de cliente de Disney+ a Netflix

```json
{
  "iddet": 67890,
  "servicio_destino": "NETFLIX"
}
```

### Caso 3: Redistribuir carga entre servicios

Si tienes Disney+ lleno y Prime con espacio:
```json
{
  "iddet": 11111,
  "servicio_destino": "PRIME"
}
```

## 🔄 Flujo Interno

1. **Validación**: Verifica que el `iddet` exista y el servicio sea válido
2. **Obtener usuario**: Busca el usuario activo en la vista
3. **Validar servicio origen**: Verifica que no sea el mismo servicio
4. **Buscar cuenta disponible**: Usa `CuentaService->buscarCuentaDisponible()`
5. **Buscar perfil libre**: Usa `CuentaService->buscarPerfilDisponible()`
6. **Actualizar registro**: Cambia el `idper` en `detalles_venta`
7. **Registrar en historial**: Crea entrada con acción `Mudacion-Usuario-Servicio`
8. **Retornar credenciales**: Devuelve las nuevas credenciales para notificar al cliente

## 📊 Lógica de Asignación

El sistema automáticamente busca:

1. **Cuenta disponible**: 
   - Activa (`activocue = true`)
   - No caída (`caidacue = false`)
   - Del servicio destino
   - Con espacio disponible (usuarios activos < pantallas máximas)

2. **Perfil disponible**:
   - Primero: Perfiles con 0 usuarios
   - Si no hay: Perfiles con 1 usuario (compartido)

## ⚠️ Consideraciones Importantes

1. **Notificación obligatoria**: Después de mover un cliente, SIEMPRE debes notificarle las nuevas credenciales
2. **Verificar disponibilidad**: Antes de hacer mudanzas masivas, verifica que el servicio destino tenga suficiente capacidad
3. **Historial**: Todas las mudanzas quedan registradas en la tabla `historial`
4. **Mismo servicio**: Si deseas mover dentro del mismo servicio, usa el endpoint `/cambiar-perfil`
5. **Usuario activo**: Solo funciona con usuarios que tengan `activodet = true`

## 🔗 Endpoints Relacionados

- `GET /api/v2/tech-usuarios/obtener/{iddet}` - Obtener detalles de un usuario
- `POST /api/v2/tech-usuarios/cambiar-perfil` - Cambiar perfil dentro del mismo servicio
- `GET /api/v2/tech-accounts/espacios-disponibles` - Ver espacios disponibles por servicio
- `GET /api/v2/tech-usuarios/estadisticas` - Estadísticas de usuarios por servicio

## 📝 Ejemplo Completo con cURL

```bash
# 1. Obtener información del usuario actual
curl -X GET https://tu-dominio.com/api/v2/tech-usuarios/obtener/12345 \
  -H "Authorization: Bearer TU_TOKEN"

# 2. Mover a otro servicio
curl -X POST https://tu-dominio.com/api/v2/tech-usuarios/mover-otro-servicio \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "iddet": 12345,
    "servicio_destino": "MAX"
  }'

# 3. Verificar el cambio
curl -X GET https://tu-dominio.com/api/v2/tech-usuarios/obtener/12345 \
  -H "Authorization: Bearer TU_TOKEN"
```

## 🔐 Permisos

Este endpoint requiere:
- ✅ Autenticación con Bearer Token
- ✅ Token válido del bot de Telegram
- ✅ El usuario debe existir y estar activo

## 📈 Rate Limiting

- Sin límite específico
- Se recomienda procesar mudanzas en lotes pequeños (máx 10 por minuto)
- Para mudanzas masivas, implementar delays entre requests

## 🐛 Troubleshooting

### Error: "No hay cuentas disponibles"
**Solución**: Verifica el estado de las cuentas del servicio destino o considera agregar más cuentas.

### Error: "No hay perfiles disponibles"
**Solución**: Las cuentas están llenas. Necesitas más cuentas del servicio destino.

### Error: "Usuario no encontrado"
**Solución**: Verifica que el `iddet` sea correcto y que el usuario esté activo.

---

**Fecha de creación**: 2026-02-04  
**Versión**: 1.0  
**Mantenedor**: Sistema de gestión de cuentas Streamify
