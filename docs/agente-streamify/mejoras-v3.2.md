# Mejoras V3.2
> Análisis realizado: 2026-06-11
> Estado previo: v3 en producción con falencias en vendedor, handoff y datos de pago
> Archivo workflow: `docs/agente-streamify/flujo-n8n.json`

---

## Inconsistencias detectadas (inputs del negocio)

1. El agente cobra incluso cuando no hay stock — pide pago y luego no puede generar la venta porque la API dice "sin stock".
2. El agente no tiene verificación de stock antes de solicitar pago.
3. El agente adivina o lee historial de cuentas bancarias → entrega cuentas incorrectas, de otras personas o inexistentes.
4. El agente no entrega todos los métodos de pago — acorta la lista por la regla de mensajes cortos (ej: cliente con Banco del Pacífico no recibe su opción).
5. Cuando el cliente paga, el agente confirma el saldo pero no ejecuta la venta/renovación — informa "tu cuenta llegará en minutos" en vez de hacerla de inmediato.

---

## Diagnóstico técnico (análisis del workflow JSON)

### BUG-1: `renovar venta` tool sin body params — CRÍTICO
- La tool enviaba POST vacío a `/api/v2/chat/assistant/venta/renovar`. La API nunca recibía `idven` ni `meses`.
- Todas las renovaciones fallaban silenciosamente → el agente devolvía `accion_tipo: renovar_venta` pero nada ocurría.

### BUG-2: Tools críticas sin `toolDescription`
- `crear venta`, `renovar venta`, `get recargas del cliente` (del Vendedor) no tenían descripción.
- Sin descripción, DeepSeek no sabe cuándo ni cómo llamarlas → las ignora.

### BUG-3: `handoff1/2/3` sin body params
- Las tools de handoff enviaban POST vacío. La API necesita `idconv` para identificar la conversación.
- El handoff nunca llegaba al sistema aunque el agente llamara la tool.

### BUG-4: Sin nodo de seguridad post-subagente para handoff — CRÍTICO
- Cuando un agente devolvía `escalar_humano: true`, Parsear3 lo extraía pero el flujo iba directo a `save respond` y terminaba.
- No había ningún nodo que verificara `escalar_humano` y disparara el handoff como safety net.

### BUG-5: `get metodos-pago1` con descripción incorrecta
- El tool del Vendedor tenía descripción "Consulta precios de productos..." — texto copiado de otro tool.
- DeepSeek no sabía que era para métodos de pago → no lo llamaba → leía datos del historial.

### BUG-6: Parsear3 referenciaba `Parsear4` (nodo inexistente)
- `$('Parsear4').first()` lanzaba error silencioso. No causa fallos visibles pero es código muerto.

---

## Fixes aplicados al workflow

| Fix | Nodo afectado | Cambio |
|-----|--------------|--------|
| FIX-1 | `renovar venta` | Body params: `idven`, `meses` + toolDescription |
| FIX-2 | `crear venta` | toolDescription explicando parámetros requeridos |
| FIX-3 | `get recargas del cliente` (Vendedor) | toolDescription con instrucción de URL |
| FIX-4 | `handoff1`, `handoff2`, `handoff3` | Body params: `idconv` (de `get context`) + `razon` |
| FIX-5 | `Parsear3` | Eliminada referencia dead code a `Parsear4` |
| FIX-6 | Nuevo: `If Handoff Post` + `Handoff Post` | Safety net: si `escalar_humano===true` después de `save respond` → dispara handoff |
| FIX-7 | `get metodos-pago1` (Vendedor) | Descripción corregida (ya no dice "precios") |
| FIX-8 | `get metodos-pago` (Asistente) | Descripción corregida |
| FIX-9 | `get banco1` (Vendedor) | Descripción explícita: nunca del historial, siempre llamar tool |
| FIX-10 | Prompt Vendedor cierre | Añadidas: REGLA DE STOCK + REGLA DATOS BANCARIOS + EXCEPCION BREVEDAD (métodos de pago) |
| FIX-11 | Prompt Cobranzas | Añadida: REGLA DATOS BANCARIOS + mostrar todos los métodos sin truncar |

---

## Flujo corregido post-subagente

```
[Vendedor / Soporte / Cobranzas / Postventa / Asistente]
  → Parsear3
  → save respond (guarda respuesta + envía WA)
  → If Handoff Post
      TRUE:  Handoff Post → POST /api/v2/chat/router/handoff
      FALSE: fin
```

---

## Pendientes

- [ ] BUG-2 (anterior): Mover API keys hardcodeadas en Normalizar a variables de entorno n8n
- [ ] Habilitar nodo `Load` (verificar endpoint `/payments/n8n/receipt-intake`)
- [ ] MEJORA-4: Vista Laravel + endpoint configurable del árbol de decisiones
- [ ] MEJORA-5: Migrar DeepSeek → Claude cuando Docker esté actualizado

