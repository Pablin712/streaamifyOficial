# 🤖 API de Automatización - Técnico de Cuentas

## 📋 Descripción General
Sistema de endpoints para automatizar tareas repetitivas del técnico de cuentas, ahorrando tiempo y reduciendo errores manuales.

---

## ⚡ ENDPOINTS IMPLEMENTADOS

### 1. Mover Servicio Completo a Mesa de Trabajo
**Endpoint:** `POST /api/v2/tech-accounts/acciones/mover-servicio-a-mesa`

**Caso de Uso:** Tienes 20 cuentas de Netflix que vas a borrar. Primero necesitas trasladar TODOS los clientes a la mesa de trabajo para luego poder eliminar las cuentas vacías.

**Body:**
```json
{
  "servicio_id": "NETFLIX"
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "servicio": "NETFLIX",
    "mesa_trabajo": "NETFLIXAtencion",
    "cuentas_procesadas": 20,
    "usuarios_movidos": 156,
    "errores": []
  }
}
```

**Lo que hace:**
- ✅ Busca la mesa de trabajo del servicio (ej: `NETFLIXAtencion`)
- ✅ Obtiene todas las cuentas activas del servicio
- ✅ Por cada cuenta, mueve TODOS los usuarios activos a la mesa
- ✅ Actualiza `idper` en `detalles_venta` para cambiar el perfil
- ✅ Reporta errores si los hay

---

### 2. Desactivar Usuarios Masivamente
**Endpoint:** `POST /api/v2/tech-accounts/acciones/desactivar-usuarios`

**Caso de Uso:** Ya moviste los usuarios y ahora quieres borrar las cuentas. Primero desactiva los usuarios restantes.

**Body:**
```json
{
  "cuenta_ids": ["NET001", "NET002", "NET003"],
  "razon": "Cuentas viejas a eliminar"
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "total_cuentas_procesadas": 3,
    "total_usuarios_desactivados": 45,
    "razon": "Cuentas viejas a eliminar",
    "detalles": [
      {
        "cuenta": "NET001",
        "servicio": "Netflix",
        "usuarios_desactivados": 15,
        "status": "completado"
      }
    ]
  }
}
```

**Lo que hace:**
- ✅ Por cada cuenta, busca usuarios en `view_usuarios_activos`
- ✅ Actualiza `activodet = false` en `detalles_venta`
- ✅ Cuenta cuántos usuarios desactivó por cuenta
- ✅ Reporta cuentas no encontradas

---

### 3. Limpiar y Preparar Cuentas para Eliminación
**Endpoint:** `POST /api/v2/tech-accounts/acciones/limpiar-cuentas`

**Caso de Uso:** Verificar cuáles cuentas de un servicio están vacías y listas para borrar, y cuáles aún tienen usuarios.

**Body:**
```json
{
  "servicio_id": "NETFLIX",
  "solo_vacias": true
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "servicio": "NETFLIX",
    "total_cuentas_evaluadas": 25,
    "cuentas_limpias": 20,
    "cuentas_con_usuarios": 5,
    "listas_para_borrar": [
      {
        "id": "NET001",
        "servicio": "Netflix",
        "tipo": "premium",
        "fecha_vencimiento": "2026-02-15",
        "lista_para_borrar": true
      }
    ],
    "requieren_atencion": [
      {
        "id": "NET010",
        "usuarios_activos": 3,
        "requiere_limpieza": true
      }
    ]
  }
}
```

**Lo que hace:**
- ✅ Evalúa todas las cuentas del servicio
- ✅ Separa cuentas vacías de cuentas con usuarios
- ✅ Lista las cuentas que puedes borrar directamente
- ✅ Alerta sobre cuentas que necesitan limpieza primero

---

### 4. Ver Usuarios Listos para Mover
**Endpoint:** `GET /api/v2/tech-accounts/acciones/usuarios-por-mover?servicio_id=NETFLIX`

**Caso de Uso:** Antes de mover, ver cuántos usuarios hay en cada cuenta y planificar la operación.

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "servicio": "NETFLIX",
    "total_cuentas": 20,
    "total_usuarios": 156,
    "cuentas": [
      {
        "cuenta": "NET001",
        "total_usuarios": 8,
        "usuarios": [
          {
            "iddet": 123,
            "cliente": "Juan Perez",
            "perfil": 1,
            "vencimiento": "2026-03-01"
          }
        ]
      }
    ]
  }
}
```

---

## 💡 MÁS IDEAS DE AUTOMATIZACIÓN

### 🔄 Operaciones Masivas

#### 5. **Renovar Cuentas Masivamente**
```
POST /api/v2/tech-accounts/acciones/renovar-cuentas
Body: {
  "servicio_id": "SPOTIFY",
  "cuentas_ids": ["SPO001", "SPO002"],
  "nueva_fecha_vencimiento": "2026-12-31",
  "auto_cobrar": false
}
```
**Beneficio:** Renueva 50 cuentas de golpe en lugar de una por una.

---

#### 6. **Arreglar Cuentas Caídas Automáticamente**
```
POST /api/v2/tech-accounts/acciones/arreglar-caidas
Body: {
  "servicio_id": "NETFLIX",
  "accion": "cambiar_contraseña" | "mover_usuarios" | "notificar_proveedor"
}
```
**Beneficio:** Detecta las 15 cuentas caídas y ejecuta la acción correctiva.

---

#### 7. **Balancear Usuarios Entre Cuentas**
```
POST /api/v2/tech-accounts/acciones/balancear-usuarios
Body: {
  "servicio_id": "MAX",
  "estrategia": "llenar_minimo_primero" | "distribuir_equitativamente"
}
```
**Beneficio:** Reorganiza automáticamente usuarios para maximizar rentabilidad. Si una cuenta tiene 2 usuarios y otra 8, balancea a 5-5.

---

#### 8. **Limpiar Perfiles Sin Usar**
```
POST /api/v2/tech-accounts/acciones/limpiar-perfiles-huerfanos
Body: {
  "servicio_id": "DISNEY",
  "dias_sin_uso": 30
}
```
**Beneficio:** Elimina perfiles que no tienen usuarios asignados hace X días, liberando espacio.

---

#### 9. **Alertas Proactivas de Vencimientos**
```
GET /api/v2/tech-accounts/acciones/alertas-vencimientos?dias=7
Response: Lista de cuentas que vencen en 7 días con usuarios activos
```
**Beneficio:** El agente IA puede notificar automáticamente antes de que una cuenta expire.

---

#### 10. **Análisis de Rentabilidad por Proveedor**
```
GET /api/v2/tech-accounts/analisis/rentabilidad-proveedores
Response: {
  "proveedores": [
    {
      "proveedor": "Proveedor A",
      "cuentas": 10,
      "costo_promedio": 5.50,
      "ingreso_promedio": 12.00,
      "margen": 54.2%,
      "recomendacion": "Incrementar compras"
    }
  ]
}
```
**Beneficio:** Decide de qué proveedores comprar más cuentas basándote en datos reales.

---

#### 11. **Consolidación de Cuentas**
```
POST /api/v2/tech-accounts/acciones/consolidar-cuentas
Body: {
  "cuentas_origen": ["NET001", "NET002", "NET003"],
  "cuenta_destino": "NET010"
}
```
**Beneficio:** Mueve todos los usuarios de 3 cuentas a una sola para reducir costos.

---

#### 12. **Auditoría de Duplicados**
```
GET /api/v2/tech-accounts/auditoria/clientes-duplicados
Response: Lista de clientes con múltiples perfiles activos del mismo servicio
```
**Beneficio:** Detecta si un cliente tiene Netflix en 2 cuentas diferentes (posible error).

---

#### 13. **Generación de Reportes Automáticos**
```
POST /api/v2/tech-accounts/reportes/generar
Body: {
  "tipo": "mensual" | "semanal",
  "servicios": ["NETFLIX", "SPOTIFY"],
  "email_destino": "admin@streamify.com"
}
```
**Beneficio:** Recibe reportes automáticos sin tener que generarlos manualmente.

---

#### 14. **Migración de Usuarios por Calidad**
```
POST /api/v2/tech-accounts/acciones/migrar-por-calidad
Body: {
  "servicio_id": "HBO",
  "desde_tipo": "basico",
  "hacia_tipo": "premium",
  "filtro_clientes": "vip"
}
```
**Beneficio:** Migra automáticamente clientes VIP de planes básicos a premium.

---

#### 15. **Detección de Cuentas Saturadas**
```
GET /api/v2/tech-accounts/alertas/cuentas-saturadas
Response: Cuentas que están al 100% de capacidad
```
**Beneficio:** El agente IA puede sugerir comprar más cuentas antes de que se rechacen ventas.

---

#### 16. **Limpieza de Usuarios Vencidos en Mesa**
```
POST /api/v2/tech-accounts/acciones/limpiar-mesa-vencidos
Body: {
  "servicio_id": "SPOTIFY",
  "dias_vencidos": 30
}
```
**Beneficio:** Usuarios que están en la mesa de trabajo hace más de 30 días y ya vencieron, se marcan como inactivos.

---

#### 17. **Optimización de Costos**
```
GET /api/v2/tech-accounts/optimizacion/sugerencias
Response: {
  "eliminar_cuentas": ["NET001", "NET002"], // Cuentas no rentables
  "renovar_cuentas": ["SPO005"], // Cuentas muy rentables
  "balancear_cuentas": ["MAX010", "MAX011"] // Mejor distribución
}
```
**Beneficio:** El agente IA te dice exactamente qué hacer para optimizar costos.

---

#### 18. **Asignación Inteligente de Nuevos Usuarios**
```
POST /api/v2/tech-accounts/acciones/asignar-inteligente
Body: {
  "servicio_id": "NETFLIX",
  "cantidad_usuarios": 10,
  "preferencia": "balancear" | "llenar_primero" | "rentabilidad"
}
```
**Beneficio:** Cuando entran 10 nuevos clientes, la IA decide a qué cuentas asignarlos para máxima eficiencia.

---

## 🔧 FLUJO DE TRABAJO TÍPICO

### Escenario: Quiero borrar 20 cuentas viejas de Netflix

**Paso 1:** Ver usuarios a mover
```bash
GET /api/v2/tech-accounts/acciones/usuarios-por-mover?servicio_id=NETFLIX
```

**Paso 2:** Mover todos los usuarios a la mesa
```bash
POST /api/v2/tech-accounts/acciones/mover-servicio-a-mesa
Body: {"servicio_id": "NETFLIX"}
```

**Paso 3:** Verificar cuentas limpias
```bash
POST /api/v2/tech-accounts/acciones/limpiar-cuentas
Body: {"servicio_id": "NETFLIX", "solo_vacias": true}
```

**Paso 4:** (Si hay usuarios restantes) Desactivar usuarios
```bash
POST /api/v2/tech-accounts/acciones/desactivar-usuarios
Body: {
  "cuenta_ids": ["NET001", "NET002"],
  "razon": "Preparando para eliminación"
}
```

**Paso 5:** (Manual) Borrar cuentas desde el panel de control

---

## 🎯 BENEFICIOS

| Tarea Manual | Tiempo Manual | Con API | Ahorro |
|--------------|---------------|---------|--------|
| Mover 156 usuarios uno por uno | ~2 horas | 10 segundos | 99.9% |
| Desactivar usuarios de 20 cuentas | ~45 minutos | 5 segundos | 99.8% |
| Verificar cuentas vacías | ~30 minutos | 3 segundos | 99.8% |
| Revisar cuántos usuarios tiene cada cuenta | ~20 minutos | 2 segundos | 99.8% |

---

## 🔐 CONSIDERACIONES DE SEGURIDAD

1. **Validación:** Todos los endpoints validan datos de entrada
2. **Rollback:** Considera implementar logs de auditoría
3. **Confirmación:** Para acciones destructivas, podría agregarse confirmación doble
4. **Límites:** Implementar rate limiting para evitar operaciones masivas accidentales
5. **Permisos:** Solo técnicos autorizados deberían acceder a estos endpoints

---

## 📊 MÉTRICAS A TRACKEAR

- Tiempo ahorrado por operación
- Errores evitados vs proceso manual
- Cantidad de operaciones masivas ejecutadas
- Usuarios movidos/desactivados por día
- Cuentas optimizadas/consolidadas

---

## 🚀 PRÓXIMOS PASOS

1. ✅ Implementar endpoints básicos de automatización
2. ⏳ Crear interfaz visual para ejecutar acciones
3. ⏳ Implementar sistema de confirmación para acciones destructivas
4. ⏳ Agregar logs de auditoría detallados
5. ⏳ Crear dashboard de métricas de automatización
6. ⏳ Implementar notificaciones automáticas (Telegram/WhatsApp)
7. ⏳ Sistema de sugerencias de optimización inteligente

---

## 💬 INTEGRACIÓN CON AGENTE IA

El agente IA puede:
- Sugerir cuándo ejecutar estas acciones
- Ejecutarlas automáticamente con confirmación del técnico
- Alertar sobre oportunidades de optimización
- Generar reportes post-operación

**Ejemplo de conversación:**
```
Técnico: "Tengo 20 cuentas de Netflix viejas que quiero borrar"
Agente IA: "Detecté 156 usuarios activos en esas cuentas. ¿Quieres que los mueva 
            automáticamente a la mesa de trabajo?"
Técnico: "Sí"
Agente IA: ✅ Movidos 156 usuarios. ✅ 20 cuentas ahora vacías. 
           ¿Desactivo los usuarios restantes y preparo para borrado?
```

---

## 📝 NOTAS TÉCNICAS

- Las operaciones usan transacciones donde es necesario
- Se reportan errores individuales sin detener el proceso completo
- Todas las respuestas incluyen detalles granulares para auditoría
- Compatible con el sistema de mesa de trabajo existente (cuentas terminadas en "Atencion")

---

**Última actualización:** Enero 2026
**Versión API:** v2
**Autor:** Sistema Streamify
