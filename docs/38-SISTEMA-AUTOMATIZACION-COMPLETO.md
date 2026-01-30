# 🤖 SISTEMA DE AUTOMATIZACIÓN COMPLETO - AGENTE IA STREAMIFY

**Fecha:** $(Get-Date -Format "yyyy-MM-dd")  
**Versión:** 2.0  
**Sistema:** Laravel API v2 + N8N + DeepSeek AI

---

## 📋 ÍNDICE

1. [Rol: Técnico de Soporte](#rol-técnico-de-soporte)
2. [Rol: Contador/Finanzas](#rol-contadorfinanzas)
3. [Rol: Administrador](#rol-administrador)
4. [Rol: Vendedor](#rol-vendedor)
5. [Módulos Transversales](#módulos-transversales)
6. [Mapeo Completo de Entidades](#mapeo-completo-de-entidades)

---

## 🔧 ROL: TÉCNICO DE SOPORTE

**Responsabilidad:** Gestión técnica diaria de cuentas, usuarios, productos y configuraciones.

### 📁 **1. GESTIÓN DE CUENTAS**

**Base de Datos:**
- Tabla: `cuentas`
- Modelo: `Cuenta`
- Campos: `idcue`, `idval`, `fechavencue`, `usuariocue`, `contrasenacue`, `caidacue`, `activocue`

**Acciones Automatizables:**

#### 1.1 Pasar usuarios a mesa de trabajo
```json
POST /api/v2/tech-accounts/mover-mesa-trabajo
{
  "servicio": "NETFLIX",        // Mover todas las cuentas de Netflix
  "cuenta_id": "CUE-001",       // O mover usuarios de cuenta específica
  "motivo": "mantenimiento"
}
```

#### 1.2 Registrar nueva cuenta
```json
POST /api/v2/tech-accounts/registrar
{
  "idval": "NETFLIX-PREMIUM-1MES",
  "fechavencue": "2026-02-25",
  "usuariocue": "usuario@gmail.com",
  "contrasenacue": "password123",
  "idpro": 1,
  "perfiles": [
    { "numeroper": 1, "pinper": "1234" },
    { "numeroper": 2, "pinper": "5678" }
  ]
}
```

#### 1.3 Editar cuenta existente
```json
PUT /api/v2/tech-accounts/editar/{idcue}
{
  "contrasenacue": "nuevaPassword",
  "fechavencue": "2026-03-25",
  "activocue": true
}
```

#### 1.4 Obtener información de cuenta específica
```json
GET /api/v2/tech-accounts/detalle/{idcue}
```

#### 1.5 Marcar cuenta como caída
```json
POST /api/v2/tech-accounts/marcar-caida
{
  "idcue": "CUE-001",
  "caidacue": true
}
```

#### 1.6 Estadísticas de cuentas
```json
GET /api/v2/tech-accounts/resumen
GET /api/v2/tech-accounts/por-servicio
GET /api/v2/tech-accounts/vencen-pronto?dias=7
```

---

### 👥 **2. GESTIÓN DE USUARIOS (Suscripciones Activas)**

**Base de Datos:**
- Tabla: `detalles_venta`
- Modelo: `DetalleVenta`
- Vista: `view_usuarios_activos`
- Campos: `iddet`, `idven`, `idcli`, `idper`, `activodet`, `fechavendet`, `montodet`

**Acciones Automatizables:**

#### 2.1 Desactivar usuarios vencidos
```json
POST /api/v2/tech-usuarios/desactivar-vencidos
{
  "servicio": "NETFLIX",    // Opcional
  "dry_run": false          // false = aplicar, true = simular
}
```

#### 2.2 Listar usuarios vencidos hoy (para notificaciones)
```json
GET /api/v2/tech-usuarios/vencidos-hoy?servicio=NETFLIX
```

**Respuesta:**
```json
{
  "success": true,
  "count": 5,
  "usuarios": [
    {
      "iddet": "DET-001",
      "cliente": {
        "nombre": "Juan Pérez",
        "email": "juan@email.com",
        "telefono": "+593999999999",
        "telegram": "@juanperez"
      },
      "cuenta": "CUE-NETFLIX-001",
      "servicio": "Netflix Premium",
      "fecha_vencimiento": "2026-01-25"
    }
  ]
}
```

#### 2.3 Cambiar usuario de cuenta/perfil
```json
POST /api/v2/tech-usuarios/cambiar-perfil
{
  "iddet": "DET-001",
  "nuevo_idper": "PER-CUE-002-3",
  "motivo": "Cuenta original caída"
}
```

#### 2.4 Obtener historial de usuario por cliente
```json
GET /api/v2/tech-usuarios/por-cliente/{idcli}
```

#### 2.5 Obtener usuario específico
```json
GET /api/v2/tech-usuarios/obtener/{iddet}
```

**Respuesta completa:**
```json
{
  "success": true,
  "data": {
    "iddet": "DET-001",
    "activo": true,
    "cliente": {
      "idcli": 1,
      "nombre": "Juan Pérez",
      "email": "juan@email.com",
      "telefono": "+593999999999"
    },
    "cuenta": {
      "idcue": "CUE-001",
      "usuario": "cuenta@gmail.com",
      "activa": true
    },
    "perfil": {
      "idper": "PER-001",
      "numero": 2,
      "pin": "1234"
    },
    "servicio": {
      "idser": "NETFLIX",
      "nombre": "Netflix Premium"
    },
    "suscripcion": {
      "fecha_vencimiento": "2026-02-25",
      "dias_restantes": 31,
      "estado": "vigente"
    }
  }
}
```

#### 2.6 Estadísticas de usuarios activos
```json
GET /api/v2/tech-usuarios/estadisticas
```

---

### 📦 **3. GESTIÓN DE PRODUCTOS**

**Base de Datos:**
- Tabla: `productos`
- Modelo: `Producto`
- Tabla relacionada: `detalle_productos` (DetalleProducto)
- Campos: `id`, `codigopro`, `nombrepro`, `preciopro`, `activo`, `tipo_producto_id`, `categoria_id`

**Estructura:**
```
Producto (Netflix Premium Individual)
  └── DetalleProducto (servicio_id: NETFLIX, meses: 1)

Producto (Combo Premium)
  ├── DetalleProducto (servicio_id: NETFLIX, meses: 1)
  ├── DetalleProducto (servicio_id: MAX, meses: 1)
  └── DetalleProducto (servicio_id: DISNEY, meses: 1)
```

**Acciones Automatizables:**

#### 3.1 Activar/desactivar producto individual
```json
POST /api/v2/tech-productos/cambiar-estado
{
  "idprod": 1,
  "activo": false
}
```

#### 3.2 Activar/desactivar productos masivamente
```json
POST /api/v2/tech-productos/cambiar-estado-masivo
{
  "servicio": "NETFLIX",
  "tipo": "individual",     // o "combo"
  "activo": true
}
```

#### 3.3 Cambiar precio de producto
```json
POST /api/v2/tech-productos/cambiar-precio
{
  "idprod": 1,
  "preciopro": 5.99
}
```

#### 3.4 Cambiar precios masivamente
```json
POST /api/v2/tech-productos/cambiar-precio-masivo
{
  "servicio": "NETFLIX",
  "tipo": "individual",
  "nuevo_precio": 5.99          // Opción 1: precio fijo
}
```

O con incremento porcentual:
```json
POST /api/v2/tech-productos/cambiar-precio-masivo
{
  "servicio": "NETFLIX",
  "tipo": "combo",
  "incremento_porcentaje": 15   // Opción 2: incremento del 15%
}
```

#### 3.5 Crear nuevo producto individual
```json
POST /api/v2/tech-productos/crear
{
  "codigopro": "NETFLIX-PREM",
  "nombrepro": "Netflix Premium Individual",
  "preciopro": 5.99,
  "activo": true,
  "tipo_producto_id": 1,
  "categoria_id": 1,
  "servicios": [
    {
      "idser": 1,           // ID del servicio Netflix
      "meses": 1,
      "descripcion": "Plan Premium 4K"
    }
  ]
}
```

#### 3.6 Crear producto combo
```json
POST /api/v2/tech-productos/crear
{
  "codigopro": "COMBO-PREMIUM",
  "nombrepro": "Combo Premium (Netflix + Max + Disney+)",
  "preciopro": 12.99,
  "activo": true,
  "tipo_producto_id": 1,
  "categoria_id": 1,
  "servicios": [
    { "idser": 1, "meses": 1, "descripcion": "Netflix Premium" },
    { "idser": 2, "meses": 1, "descripcion": "Max Premium" },
    { "idser": 3, "meses": 1, "descripcion": "Disney+ Premium" }
  ]
}
```

#### 3.7 Editar producto
```json
PUT /api/v2/tech-productos/editar/{id}
{
  "nombrepro": "Netflix Premium Individual Actualizado",
  "preciopro": 6.99,
  "activo": true
}
```

#### 3.8 Obtener producto específico
```json
GET /api/v2/tech-productos/obtener/{id}
```

#### 3.9 Listar productos con filtros
```json
GET /api/v2/tech-productos/listar?servicio=NETFLIX&activo=true&tipo=individual
```

---

### ⚙️ **4. CONFIGURACIÓN DEL SISTEMA**

#### 4.1 VALORES (Configuraciones de Servicios)

**Base de Datos:**
- Tabla: `valores`
- Modelo: `Valor`
- Campos: `idval`, `idser`, `idpro`, `tipoval`, `mesesval`, `min_pantallas`, `max_pantallas`

**Acciones:**

##### Definir pantallas mínimas y máximas
```json
POST /api/v2/tech-config/valores/pantallas
{
  "servicio": "NETFLIX",
  "min_pantallas": 1,
  "max_pantallas": 5
}
```

##### Crear nuevo valor
```json
POST /api/v2/tech-config/valores/crear
{
  "idval": "NETFLIX-PREMIUM-1MES",
  "idser": "NETFLIX",
  "idpro": 1,
  "tipoval": "completo",
  "mesesval": 1,
  "min_pantallas": 1,
  "max_pantallas": 5
}
```

##### Editar valor
```json
PUT /api/v2/tech-config/valores/editar/{idval}
{
  "min_pantallas": 2,
  "max_pantallas": 6
}
```

##### Obtener valor específico
```json
GET /api/v2/tech-config/valores/obtener/{idval}
```

##### Listar valores
```json
GET /api/v2/tech-config/valores/listar?servicio=NETFLIX&proveedor=1
```

---

#### 4.2 SERVICIOS

**Base de Datos:**
- Tabla: `servicios`
- Modelo: `Servicio`
- Campos: `idser`, `nombreser`, `imagenser`

**Acciones:**

##### Crear servicio
```json
POST /api/v2/tech-config/servicios/crear
{
  "idser": "NETFLIX",
  "nombreser": "Netflix Premium",
  "imagenser": "https://cdn.example.com/netflix.png"
}
```

##### Editar servicio
```json
PUT /api/v2/tech-config/servicios/editar/{idser}
{
  "nombreser": "Netflix Premium 4K",
  "imagenser": "https://cdn.example.com/netflix-new.png"
}
```

##### Obtener servicio específico
```json
GET /api/v2/tech-config/servicios/obtener/{idser}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "idser": "NETFLIX",
    "nombre": "Netflix Premium",
    "imagen": "https://...",
    "estadisticas": {
      "valores_asociados": 5,
      "cuentas_asociadas": 120
    }
  }
}
```

##### Listar servicios
```json
GET /api/v2/tech-config/servicios/listar
```

---

#### 4.3 PROVEEDORES

**Base de Datos:**
- Tabla: `proveedores`
- Modelo: `Proveedor`
- Campos: `idpro`, `nombrepro`, `telefonopro`, `direccionpro`

**Acciones:**

##### Crear proveedor
```json
POST /api/v2/tech-config/proveedores/crear
{
  "nombrepro": "Proveedor Premium S.A.",
  "telefonopro": "+593999999999",
  "direccionpro": "Av. Principal 123, Quito"
}
```

##### Editar proveedor
```json
PUT /api/v2/tech-config/proveedores/editar/{idpro}
{
  "nombrepro": "Proveedor Premium Actualizado",
  "telefonopro": "+593988888888"
}
```

##### Obtener proveedor específico
```json
GET /api/v2/tech-config/proveedores/obtener/{idpro}
```

##### Listar proveedores
```json
GET /api/v2/tech-config/proveedores/listar
```

---

## 💰 ROL: CONTADOR/FINANZAS

**Responsabilidad:** Gestión financiera, ventas, transacciones, gastos y reportes contables.

### 💳 **5. GESTIÓN DE VENTAS**

**Base de Datos:**
- Tabla: `ventas`
- Modelo: `Venta`
- Tabla relacionada: `detalles_venta`
- Campos: `idven`, `idcli`, `fechaven`, `totven`, `estadoven`

**Acciones Automatizables:**

#### 5.1 Crear nueva venta completa
```json
POST /api/v2/tech-ventas/crear
{
  "idcli": 1,
  "fechaven": "2026-01-25",
  "estadoven": "completada",
  "detalles": [
    {
      "idprod": 1,
      "cantidad": 1,
      "preciounitario": 5.99,
      "idper": "PER-CUE-001-2",
      "fechadet": "2026-01-25",
      "fechavendet": "2026-02-25",
      "montodet": 5.99
    }
  ],
  "transaccion": {
    "idbanco": 1,
    "monto": 5.99,
    "tipo": "ingreso",
    "descripcion": "Venta Netflix Premium"
  }
}
```

#### 5.2 Editar venta existente
```json
PUT /api/v2/tech-ventas/editar/{idven}
{
  "estadoven": "anulada",
  "detalles": [
    {
      "iddet": "DET-001",
      "fechavendet": "2026-03-25"
    }
  ]
}
```

#### 5.3 Obtener detalle completo de venta
```json
GET /api/v2/tech-ventas/detalle/{idven}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "idven": 1,
    "cliente": {
      "idcli": 1,
      "nombre": "Juan Pérez",
      "email": "juan@email.com"
    },
    "fecha_venta": "2026-01-25",
    "total": 5.99,
    "estado": "completada",
    "detalles": [
      {
        "iddet": "DET-001",
        "producto": "Netflix Premium",
        "cantidad": 1,
        "precio": 5.99,
        "perfil": {
          "cuenta": "CUE-001",
          "numero": 2
        },
        "fecha_vencimiento": "2026-02-25",
        "activo": true
      }
    ],
    "transacciones": [
      {
        "idtrans": 1,
        "banco": "Banco Pichincha",
        "monto": 5.99,
        "tipo": "ingreso",
        "fecha": "2026-01-25"
      }
    ]
  }
}
```

#### 5.4 Listar ventas con filtros
```json
GET /api/v2/tech-ventas/listar?fecha_inicio=2026-01-01&fecha_fin=2026-01-31&estado=completada&cliente=1
```

#### 5.5 Estadísticas de ventas
```json
GET /api/v2/tech-ventas/estadisticas?periodo=mes&fecha_inicio=2026-01-01&fecha_fin=2026-01-31
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "total_ventas": 150,
    "monto_total": 899.50,
    "ventas_completadas": 145,
    "ventas_pendientes": 3,
    "ventas_anuladas": 2,
    "por_producto": [
      {
        "producto": "Netflix Premium",
        "cantidad": 80,
        "monto": 479.20
      }
    ],
    "promedio_venta": 5.99
  }
}
```

---

### 💵 **6. GESTIÓN DE TRANSACCIONES**

**Base de Datos:**
- Tabla: `transacciones`
- Modelo: `Transaccion`
- Campos: `idtrans`, `idbanco`, `monto`, `tipo`, `descripcion`, `fecha`

**Acciones:**

#### 6.1 Registrar transacción
```json
POST /api/v2/finanzas/transacciones/crear
{
  "idbanco": 1,
  "monto": 100.00,
  "tipo": "ingreso",
  "descripcion": "Pago cliente Juan Pérez",
  "fecha": "2026-01-25",
  "categoria": "ventas",
  "referencia": "VEN-001"
}
```

#### 6.2 Listar transacciones
```json
GET /api/v2/finanzas/transacciones/listar?tipo=ingreso&fecha_inicio=2026-01-01&fecha_fin=2026-01-31&banco=1
```

#### 6.3 Reporte de flujo de caja
```json
GET /api/v2/finanzas/flujo-caja?periodo=mes&fecha=2026-01
```

---

### 📊 **7. GESTIÓN DE GASTOS**

**Base de Datos:**
- Tabla: `gastos`
- Modelo: `Gasto`
- Campos: `idgas`, `conceptogas`, `montogas`, `fechagas`, `tipo_gasto_id`

**Acciones:**

#### 7.1 Registrar gasto
```json
POST /api/v2/finanzas/gastos/crear
{
  "conceptogas": "Renovación cuenta Netflix Proveedor A",
  "montogas": 15.00,
  "fechagas": "2026-01-25",
  "tipo_gasto_id": 1,
  "idbanco": 1
}
```

#### 7.2 Listar gastos
```json
GET /api/v2/finanzas/gastos/listar?tipo=1&fecha_inicio=2026-01-01&fecha_fin=2026-01-31
```

#### 7.3 Estadísticas de gastos
```json
GET /api/v2/finanzas/gastos/estadisticas?periodo=mes
```

---

### 🏦 **8. GESTIÓN DE BANCOS**

**Base de Datos:**
- Tabla: `bancos`
- Modelo: `Banco`
- Campos: `idbanco`, `nombreban`, `saldo_actual`

**Acciones:**

#### 8.1 Obtener saldo de banco
```json
GET /api/v2/finanzas/bancos/{idbanco}/saldo
```

#### 8.2 Listar bancos con saldos
```json
GET /api/v2/finanzas/bancos/listar
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "idbanco": 1,
      "nombre": "Banco Pichincha",
      "saldo_actual": 1500.50,
      "ultima_transaccion": "2026-01-25 10:30:00"
    },
    {
      "idbanco": 2,
      "nombre": "Banco Guayaquil",
      "saldo_actual": 2300.75
    }
  ]
}
```

---

### 💳 **9. GESTIÓN DE DEUDAS**

**Base de Datos:**
- Tabla: `deudas`
- Modelo: `Deuda`
- Campos: `iddeuda`, `idcli`, `monto`, `estado`, `fecha_limite`

**Acciones:**

#### 9.1 Registrar deuda
```json
POST /api/v2/finanzas/deudas/crear
{
  "idcli": 1,
  "monto": 10.00,
  "descripcion": "Saldo pendiente venta VEN-001",
  "fecha_limite": "2026-02-25"
}
```

#### 9.2 Registrar pago de deuda
```json
POST /api/v2/finanzas/deudas/pagar
{
  "iddeuda": 1,
  "monto_pago": 10.00,
  "idbanco": 1
}
```

#### 9.3 Listar deudas pendientes
```json
GET /api/v2/finanzas/deudas/pendientes?cliente=1
```

---

## 👤 ROL: ADMINISTRADOR

**Responsabilidad:** Gestión de empleados, clientes, permisos, estadísticas globales y configuración del sistema.

### 👨‍💼 **10. GESTIÓN DE EMPLEADOS**

**Base de Datos:**
- Tabla: `empleados`
- Modelo: `Empleado`
- Campos: `idemp`, `nombreemp`, `usuarioemp`, `passwordemp`, `telefonoemp`

**Acciones:**

#### 10.1 Crear empleado
```json
POST /api/v2/admin/empleados/crear
{
  "nombreemp": "Carlos Técnico",
  "usuarioemp": "carlos.tecnico",
  "passwordemp": "password123",
  "telefonoemp": "+593999999999",
  "email": "carlos@empresa.com",
  "roles": ["tecnico", "soporte"]
}
```

#### 10.2 Editar empleado
```json
PUT /api/v2/admin/empleados/editar/{idemp}
{
  "nombreemp": "Carlos Técnico Senior",
  "telefonoemp": "+593988888888"
}
```

#### 10.3 Asignar permisos
```json
POST /api/v2/admin/empleados/{idemp}/permisos
{
  "permisos": ["ver_cuentas", "editar_cuentas", "crear_ventas"]
}
```

#### 10.4 Listar empleados
```json
GET /api/v2/admin/empleados/listar?activo=true&rol=tecnico
```

---

### 👥 **11. GESTIÓN DE CLIENTES**

**Base de Datos:**
- Tabla: `clientes`
- Modelo: `Cliente`
- Campos: `idcli`, `nombrecli`, `email`, `telefonocli`, `saldo`, `pais`

**Acciones:**

#### 11.1 Crear cliente
```json
POST /api/v2/admin/clientes/crear
{
  "nombrecli": "Juan Pérez",
  "email": "juan@email.com",
  "telefonocli": "+593999999999",
  "pais": "Ecuador",
  "codigo_referidor": "REF-001"
}
```

#### 11.2 Editar cliente
```json
PUT /api/v2/admin/clientes/editar/{idcli}
{
  "nombrecli": "Juan Pérez Actualizado",
  "saldo": 10.00
}
```

#### 11.3 Obtener cliente específico
```json
GET /api/v2/admin/clientes/obtener/{idcli}
```

#### 11.4 Listar clientes con filtros
```json
GET /api/v2/admin/clientes/listar?pais=Ecuador&ya_compro=true&saldo_mayor_a=0
```

#### 11.5 Historial completo del cliente
```json
GET /api/v2/admin/clientes/{idcli}/historial
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "cliente": {
      "idcli": 1,
      "nombre": "Juan Pérez",
      "email": "juan@email.com"
    },
    "estadisticas": {
      "total_compras": 25,
      "monto_total_gastado": 149.75,
      "servicios_activos": 3,
      "ultima_compra": "2026-01-25"
    },
    "ventas": [...],
    "usuarios_activos": [...],
    "deudas_pendientes": [...]
  }
}
```

---

### 📊 **12. ESTADÍSTICAS GLOBALES**

**Base de Datos:**
- Tabla: `daily_statistics`
- Modelo: `DailyStatistic`

**Acciones:**

#### 12.1 Dashboard general
```json
GET /api/v2/admin/dashboard
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "ventas_hoy": {
      "cantidad": 15,
      "monto": 89.85
    },
    "usuarios_activos": 320,
    "cuentas_activas": 85,
    "saldo_total_bancos": 3801.25,
    "gastos_mes": 450.00,
    "ingresos_mes": 2700.00,
    "utilidad_mes": 2250.00,
    "clientes_nuevos_mes": 42
  }
}
```

#### 12.2 Reporte de utilidades
```json
GET /api/v2/admin/reportes/utilidades?periodo=mes&fecha=2026-01
```

#### 12.3 Servicios más vendidos
```json
GET /api/v2/admin/reportes/top-servicios?limite=10&periodo=mes
```

---

## 🛒 ROL: VENDEDOR

**Responsabilidad:** Gestión de ventas, pedidos, recargas y atención al cliente.

### 🛍️ **13. GESTIÓN DE PEDIDOS**

**Base de Datos:**
- Tabla: `pedidos`
- Modelo: `Pedido`
- Campos: `idpedido`, `idcli`, `estado`, `fecha_pedido`

**Acciones:**

#### 13.1 Crear pedido
```json
POST /api/v2/ventas/pedidos/crear
{
  "idcli": 1,
  "productos": [
    {
      "idprod": 1,
      "cantidad": 1
    }
  ],
  "estado": "pendiente"
}
```

#### 13.2 Cambiar estado de pedido
```json
PUT /api/v2/ventas/pedidos/{idpedido}/estado
{
  "estado": "procesando"
}
```

#### 13.3 Listar pedidos
```json
GET /api/v2/ventas/pedidos/listar?estado=pendiente&cliente=1
```

---

### 💰 **14. GESTIÓN DE RECARGAS DE SALDO**

**Base de Datos:**
- Tabla: `recargas`
- Modelo: `Recarga`
- Campos: `idrec`, `idcli`, `montorec`, `estadorec`

**Acciones:**

#### 14.1 Registrar recarga
```json
POST /api/v2/ventas/recargas/crear
{
  "idcli": 1,
  "montorec": 20.00,
  "idbanco": 1,
  "estadorec": "pendiente"
}
```

#### 14.2 Aprobar recarga
```json
POST /api/v2/ventas/recargas/{idrec}/aprobar
{
  "nota": "Comprobante verificado"
}
```

#### 14.3 Listar recargas pendientes
```json
GET /api/v2/ventas/recargas/pendientes
```

---

## 🔄 MÓDULOS TRANSVERSALES

### 📧 **15. NOTIFICACIONES AUTOMÁTICAS**

**Acciones:**

#### 15.1 Enviar notificación de vencimiento
```json
POST /api/v2/notificaciones/vencimiento
{
  "tipo": "email",      // o "telegram", "sms"
  "destinatario": "juan@email.com",
  "servicio": "Netflix Premium",
  "fecha_vencimiento": "2026-01-26",
  "dias_restantes": 1
}
```

#### 15.2 Notificación de pago recibido
```json
POST /api/v2/notificaciones/pago-recibido
{
  "idcli": 1,
  "monto": 5.99,
  "metodo": "transferencia"
}
```

#### 15.3 Alerta de cuenta caída
```json
POST /api/v2/notificaciones/cuenta-caida
{
  "idcue": "CUE-001",
  "servicio": "Netflix Premium",
  "usuarios_afectados": 5
}
```

---

### 🤖 **16. INTEGRACIONES EXTERNAS**

#### 16.1 Telegram Bot
```json
POST /api/v2/integraciones/telegram/enviar
{
  "chat_id": "123456789",
  "mensaje": "Tu suscripción vence mañana",
  "botones": [
    { "text": "Renovar", "callback_data": "renovar_netflix" }
  ]
}
```

#### 16.2 N8N Webhook
```json
POST https://n8n.tudominio.com/webhook/streamify
{
  "evento": "usuario_vencido",
  "data": {
    "iddet": "DET-001",
    "cliente": "Juan Pérez",
    "servicio": "Netflix Premium"
  }
}
```

---

## 📊 MAPEO COMPLETO DE ENTIDADES

### Entidades Principales (57 Tablas)

| # | Tabla | Modelo | Descripción |
|---|-------|--------|-------------|
| 1 | `empleados` | `Empleado` | Personal del sistema |
| 2 | `clientes` | `Cliente` | Clientes finales |
| 3 | `servicios` | `Servicio` | Servicios de streaming (Netflix, etc.) |
| 4 | `proveedores` | `Proveedor` | Proveedores de cuentas |
| 5 | `valores` | `Valor` | Configuración de servicios |
| 6 | `cuentas` | `Cuenta` | Cuentas de streaming |
| 7 | `perfiles` | `Perfil` | Perfiles dentro de cuentas |
| 8 | `productos` | `Producto` | Productos a vender |
| 9 | `detalle_productos` | `DetalleProducto` | Servicios de cada producto |
| 10 | `tipo_productos` | `TipoProducto` | Clasificación de productos |
| 11 | `categorias` | `Categoria` | Categorías de productos |
| 12 | `ventas` | `Venta` | Ventas realizadas |
| 13 | `detalles_venta` | `DetalleVenta` | Detalles/usuarios de ventas |
| 14 | `bancos` | `Banco` | Cuentas bancarias |
| 15 | `transacciones` | `Transaccion` | Movimientos financieros |
| 16 | `gastos` | `Gasto` | Gastos del negocio |
| 17 | `tipo_gastos` | `TipoGasto` | Categorías de gastos |
| 18 | `deudas` | `Deuda` | Cuentas por cobrar |
| 19 | `costos` | `Costo` | Costos de servicios |
| 20 | `mantenimientos` | `Mantenimiento` | Mantenimiento de cuentas |
| 21 | `recargas` | `Recarga` | Recargas de saldo |
| 22 | `pedidos` | `Pedido` | Pedidos de clientes |
| 23 | `estados_recarga` | `EstadoRecarga` | Estados de recargas |
| 24 | `tareas` | `Tarea` | Tareas del sistema |
| 25 | `asistencias` | `Asistencia` | Control de asistencias |
| 26 | `historial` | `Historial` | Historial de acciones |
| 27 | `contabilidad` | `Contabilidad` | Registros contables |
| 28 | `daily_statistics` | `DailyStatistic` | Estadísticas diarias |
| 29 | `conversaciones` | `Conversacion` | Chats con clientes |
| 30 | `mensajes` | `Mensaje` | Mensajes de chat |
| 31 | `quick_responses` | `QuickResponse` | Respuestas rápidas |
| 32 | `empleados_online` | - | Empleados en línea |
| 33 | `telegram_auth_sessions` | `TelegramAuthSession` | Sesiones Telegram |
| 34 | `permissions` | `Permiso` | Permisos del sistema |
| 35 | `roles` | `Rol` | Roles de usuarios |
| 36 | `api_keys` | `ApiKey` | Claves API |
| 37 | `personal_access_tokens` | - | Tokens de acceso |
| 38 | `users` | `User` | Usuarios del sistema |

### Vistas del Sistema

| Vista | Propósito |
|-------|-----------|
| `view_usuarios_activos` | Lista de suscripciones activas |
| `view_clientes_usuarios` | Clientes con sus usuarios |

---

## 🎯 WORKFLOWS RECOMENDADOS PARA N8N

### Workflow 1: Desactivación Automática Diaria
```
Trigger: Cron (Diario 00:00)
  ↓
API: GET /api/v2/tech-usuarios/vencidos-hoy
  ↓
API: POST /api/v2/tech-usuarios/desactivar-vencidos
  ↓
Loop por cada usuario:
  ↓
  API: POST /api/v2/notificaciones/vencimiento (Email)
  ↓
  API: POST /api/v2/integraciones/telegram/enviar
```

### Workflow 2: Renovación de Cuentas con Proveedores
```
Trigger: Cron (Semanal)
  ↓
API: GET /api/v2/tech-accounts/vencen-pronto?dias=3
  ↓
Loop por cada cuenta:
  ↓
  Verificar con proveedor
  ↓
  API: PUT /api/v2/tech-accounts/editar/{idcue} (Renovar fecha)
  ↓
  API: POST /api/v2/finanzas/gastos/crear (Registrar costo)
```

### Workflow 3: Procesamiento de Ventas
```
Trigger: Webhook (Nueva venta)
  ↓
API: POST /api/v2/tech-ventas/crear
  ↓
API: POST /api/v2/notificaciones/pago-recibido
  ↓
API: POST /api/v2/integraciones/telegram/enviar (Confirmación)
```

### Workflow 4: Monitoreo de Cuentas Caídas
```
Trigger: Cron (Cada hora)
  ↓
API: GET /api/v2/tech-accounts/resumen
  ↓
Filtrar cuentas con caidacue = true
  ↓
Loop por cada cuenta:
  ↓
  API: GET /api/v2/tech-usuarios/obtener (usuarios afectados)
  ↓
  API: POST /api/v2/notificaciones/cuenta-caida
```

---

## 🔐 AUTENTICACIÓN

Todos los endpoints requieren API Key en header:

```bash
Authorization: Bearer YOUR_API_KEY_HERE
```

Generar API Key:
```json
POST /api/v2/auth/generate-api-key
{
  "name": "N8N Automation",
  "expires_in": 365
}
```

---

## 📝 RESUMEN DE ENDPOINTS

| Módulo | GET | POST | PUT | DELETE | Total |
|--------|-----|------|-----|--------|-------|
| Cuentas | 6 | 4 | 2 | 0 | 12 |
| Usuarios | 5 | 2 | 0 | 0 | 7 |
| Ventas | 3 | 1 | 1 | 0 | 5 |
| Productos | 2 | 4 | 1 | 0 | 7 |
| Configuración | 9 | 6 | 6 | 0 | 21 |
| Finanzas | 8 | 6 | 2 | 0 | 16 |
| Administración | 10 | 5 | 3 | 1 | 19 |
| **TOTAL** | **43** | **28** | **15** | **1** | **87** |

---

## 🚀 PRÓXIMOS PASOS PARA AUTOMATIZACIÓN COMPLETA

1. ✅ Completar endpoints faltantes (Finanzas, Administración)
2. ✅ Implementar sistema de permisos por rol
3. ✅ Crear webhooks para eventos críticos
4. ✅ Desarrollar workflows en N8N
5. ✅ Integrar con Telegram Bot
6. ✅ Sistema de notificaciones push
7. ✅ Dashboard en tiempo real
8. ✅ Reportes automáticos por email
9. ✅ Backup automático diario
10. ✅ Monitoreo de uptime y salud del sistema

---

**Última actualización:** 2026-01-25  
**Documento generado por:** GitHub Copilot AI  
**Versión del Sistema:** Streamify API v2.0
