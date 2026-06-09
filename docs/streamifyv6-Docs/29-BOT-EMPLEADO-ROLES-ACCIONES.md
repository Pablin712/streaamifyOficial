# Bot Streamify - Análisis de Roles y Acciones del Empleado

**Fecha:** 15 de Enero de 2026  
**Versión:** 1.0  
**Autor:** Sistema de Documentación Streamify

---

## 📋 Índice

1. [Visión General del Sistema](#visión-general-del-sistema)
2. [Análisis de la Arquitectura Actual](#análisis-de-la-arquitectura-actual)
3. [Roles del Bot Empleado](#roles-del-bot-empleado)
4. [Acciones por Rol](#acciones-por-rol)
5. [Endpoints API Propuestos](#endpoints-api-propuestos)
6. [Flujos de Interacción](#flujos-de-interacción)
7. [Estructura de Datos](#estructura-de-datos)
8. [Consideraciones de Seguridad](#consideraciones-de-seguridad)

---

## 🎯 Visión General del Sistema

### Objetivo Principal
Crear un sistema de bot inteligente para empleados de Streamify que automatice tareas operativas, genere informes y asista en la toma de decisiones a través de Telegram.

### Alcance del Proyecto
- **Modo Empleado:** Sistema completo de asistencia para 3 roles específicos
- **Modo Cliente:** Ya implementado (fuera del alcance de este documento)

### Diferencias Clave: Cliente vs Empleado

| Aspecto | Modo Cliente | Modo Empleado |
|---------|-------------|---------------|
| **Autenticación** | Optional (anónimo permitido) | Requerida (credenciales empleado) |
| **Permisos** | Limitados a consultas | Basados en rol asignado |
| **Acceso a Datos** | Propios | Globales de la empresa |
| **Acciones** | Consulta información | CRUD + reportes + análisis |

---

## 🔍 Análisis de la Arquitectura Actual

### Controllers V2 Existentes

#### 1. **AuthController** (`Api\V2\AuthController`)
**Responsabilidad Actual:** Autenticación de clientes (no empleados)

**Métodos Implementados:**
- `validarCredenciales(Request)` - Valida email/password de clientes
- `crearCliente(Request)` - Registro de nuevos clientes
- `formatearTelefono($telefono)` - Normalización de teléfonos

**Características:**
- ✅ Validación robusta con Validator
- ✅ Manejo de referencias (códigos de referido)
- ✅ Actualización de clientes existentes sin email
- ✅ Respuestas JSON consistentes (200 status)
- ❌ **NO maneja autenticación de empleados**

#### 2. **InformationController** (`Api\V2\InformationController`)
**Responsabilidad Actual:** Información pública de precios y métodos de pago

**Métodos Implementados:**
- `getPrecios(Request)` - Precios generales, productos, combos o por servicio
- `getMetodosPago()` - Lista todos los bancos disponibles
- `getBanco($id)` - Información de un banco específico

**Características:**
- ✅ Endpoints públicos (sin autenticación)
- ✅ Generación de mensajes formateados para Telegram
- ✅ Múltiples tipos de consulta (tipo: general, productos, combos, servicio)
- ✅ Manejo de errores con try-catch
- 📊 **Datos útiles para el rol Contador**

### Rutas API V2 Actuales

```php
Route::prefix('v2')->group(function () {
    // Autenticación (solo clientes)
    Route::post('/auth/create-customer', [AuthController::class, 'crearCliente']);
    Route::post('/auth/validate-credentials', [AuthController::class, 'validarCredenciales']);

    // Información Pública
    Route::get('/precios', [InformationController::class, 'getPrecios']);
    Route::get('/metodos-pago', [InformationController::class, 'getMetodosPago']);
    Route::get('/banco/{nombrebanco}', [InformationController::class, 'getBanco']);
});
```

**Observaciones:**
- ⚠️ No hay middleware de autenticación para empleados
- ⚠️ No hay rutas específicas para operaciones de empleados
- ⚠️ Falta sistema de permisos basado en roles

---

## 👥 Roles del Bot Empleado

### 1. 👔 Contador (Accountant)

**Descripción:** Responsable de la gestión financiera, generación de informes contables y análisis de datos de ventas.

**Permisos:**
- ✅ Lectura completa de datos financieros
- ✅ Generación de reportes
- ✅ Acceso a estadísticas globales
- ❌ NO modifica datos directamente (solo consulta)

**Casos de Uso:**
1. Consultar resumen de ventas diarias/semanales/mensuales
2. Obtener listado de facturas pendientes
3. Generar reportes de ingresos por servicio
4. Análisis de clientes morosos
5. Estadísticas de métodos de pago utilizados
6. Proyecciones de ingresos
7. Comparativas de períodos

---

### 2. 🔧 Técnico (Technical Support)

**Descripción:** Resuelve problemas técnicos, gestiona cuentas de streaming y asiste en configuraciones.

**Permisos:**
- ✅ Lectura de cuentas de clientes
- ✅ Modificación de datos técnicos (perfiles, contraseñas)
- ✅ Gestión de problemas reportados
- ✅ Consulta de manual de soluciones
- ❌ NO modifica datos financieros

**Casos de Uso:**
1. Buscar cuenta de cliente por email/teléfono
2. Obtener credenciales de acceso a servicios
3. Registrar problema técnico reportado
4. Consultar historial de tickets
5. Cambiar perfil de servicio
6. Regenerar contraseñas de cuentas
7. Verificar estado de servicios activos
8. Consultar manual de resolución de problemas

---

### 3. 📋 Secretaria (Administrative Assistant)

**Descripción:** Gestiona la agenda del negocio, recordatorios y actividades administrativas diarias.

**Permisos:**
- ✅ Creación/edición de eventos
- ✅ Gestión de recordatorios
- ✅ Consulta de agenda
- ✅ Registro de actividades
- ❌ NO accede a datos financieros sensibles

**Casos de Uso:**
1. Crear recordatorio para fecha específica
2. Consultar agenda del día/semana
3. Registrar actividad completada
4. Listar tareas pendientes
5. Marcar recordatorio como completado
6. Obtener resumen de actividades diarias
7. Programar seguimiento de clientes
8. Generar checklist de tareas

---

## 🚀 Acciones por Rol

### 📊 Contador - Acciones Detalladas

#### A1. Resumen de Ventas
**Descripción:** Obtener resumen financiero por período

**Parámetros:**
- `periodo`: daily, weekly, monthly, yearly, custom
- `fecha_inicio`: (opcional) para custom
- `fecha_fin`: (opcional) para custom

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "periodo": "2026-01-15",
    "total_ventas": 1250.50,
    "cantidad_ventas": 45,
    "promedio_venta": 27.79,
    "servicios": [
      {
        "servicio": "NETFLIX",
        "cantidad": 20,
        "total": 600.00
      }
    ],
    "metodos_pago": [
      {
        "metodo": "Transferencia Bancaria",
        "cantidad": 30,
        "total": 900.00
      }
    ]
  }
}
```

#### A2. Facturas Pendientes
**Descripción:** Listado de ventas sin comprobante de pago

**Parámetros:**
- `dias_atrasados`: (opcional) filtrar por días
- `servicio`: (opcional) filtrar por servicio

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "total_pendientes": 15,
    "monto_total": 450.00,
    "facturas": [
      {
        "id": 123,
        "cliente": "Juan Pérez",
        "servicio": "NETFLIX",
        "monto": 30.00,
        "fecha_venta": "2026-01-10",
        "dias_atraso": 5
      }
    ]
  }
}
```

#### A3. Ingresos por Servicio
**Descripción:** Análisis de rentabilidad por servicio

**Parámetros:**
- `periodo`: daily, weekly, monthly
- `fecha`: (opcional)

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "periodo": "monthly",
    "mes": "2026-01",
    "total_general": 3500.00,
    "servicios": [
      {
        "servicio": "NETFLIX",
        "cantidad_ventas": 50,
        "ingresos": 1500.00,
        "porcentaje": 42.86
      }
    ]
  }
}
```

#### A4. Clientes Morosos
**Descripción:** Listado de clientes con pagos vencidos

**Parámetros:**
- `dias_minimos`: (default: 3)

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "total_morosos": 8,
    "monto_total_deuda": 240.00,
    "clientes": [
      {
        "id": 45,
        "nombre": "María López",
        "email": "maria@example.com",
        "telefono": "+593 96 123 4567",
        "ventas_vencidas": 2,
        "deuda_total": 60.00,
        "dias_max_atraso": 15
      }
    ]
  }
}
```

#### A5. Proyección de Ingresos
**Descripción:** Estimación de ingresos futuros basado en renovaciones

**Parámetros:**
- `periodo`: next_week, next_month

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "periodo": "next_month",
    "fecha_inicio": "2026-02-01",
    "fecha_fin": "2026-02-28",
    "renovaciones_esperadas": 120,
    "ingreso_estimado": 3600.00,
    "desglose": [
      {
        "servicio": "NETFLIX",
        "cantidad": 50,
        "monto_estimado": 1500.00
      }
    ]
  }
}
```

---

### 🔧 Técnico - Acciones Detalladas

#### T1. Buscar Cliente
**Descripción:** Búsqueda de cliente por múltiples criterios

**Parámetros:**
- `criterio`: email, telefono, nombre, id
- `valor`: valor a buscar

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "cliente": {
      "id": 45,
      "nombre": "Juan Pérez",
      "email": "juan@example.com",
      "telefono": "+593 96 123 4567",
      "saldo": 15.50,
      "servicios_activos": 3,
      "fecha_registro": "2025-06-15"
    }
  }
}
```

#### T2. Obtener Cuentas de Cliente
**Descripción:** Listado de servicios activos con credenciales

**Parámetros:**
- `cliente_id`: ID del cliente

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "cliente": "Juan Pérez",
    "cuentas": [
      {
        "servicio": "NETFLIX",
        "perfil": "Perfil 1",
        "email": "netflix@cuenta.com",
        "password": "password123",
        "pin": "1234",
        "fecha_inicio": "2026-01-01",
        "fecha_vencimiento": "2026-02-01",
        "estado": "activo",
        "dias_restantes": 17
      }
    ]
  }
}
```

#### T3. Registrar Problema Técnico
**Descripción:** Crear ticket de soporte

**Parámetros:**
- `cliente_id`: ID del cliente
- `servicio`: servicio afectado
- `tipo_problema`: acceso, calidad, configuracion, otro
- `descripcion`: detalle del problema
- `prioridad`: baja, media, alta

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "ticket_id": 789,
    "cliente": "Juan Pérez",
    "servicio": "NETFLIX",
    "tipo_problema": "acceso",
    "prioridad": "alta",
    "estado": "abierto",
    "fecha_creacion": "2026-01-15 10:30:00",
    "asignado_a": "Empleado Técnico"
  },
  "message": "Ticket creado exitosamente. ID: 789"
}
```

#### T4. Historial de Tickets
**Descripción:** Consultar tickets del cliente o globales

**Parámetros:**
- `cliente_id`: (opcional) filtrar por cliente
- `estado`: abierto, cerrado, todos
- `limit`: (default: 20)

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "total_tickets": 15,
    "tickets": [
      {
        "id": 789,
        "cliente": "Juan Pérez",
        "servicio": "NETFLIX",
        "tipo_problema": "acceso",
        "descripcion": "No puedo iniciar sesión",
        "estado": "abierto",
        "prioridad": "alta",
        "fecha_creacion": "2026-01-15 10:30:00",
        "resuelto_por": null,
        "fecha_resolucion": null
      }
    ]
  }
}
```

#### T5. Cambiar Perfil de Servicio
**Descripción:** Modificar perfil asignado a cliente

**Parámetros:**
- `venta_id`: ID de la venta
- `nuevo_perfil`: nombre del nuevo perfil

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "venta_id": 123,
    "servicio": "NETFLIX",
    "perfil_anterior": "Perfil 1",
    "perfil_nuevo": "Perfil 2",
    "fecha_cambio": "2026-01-15 11:00:00"
  },
  "message": "Perfil cambiado exitosamente"
}
```

#### T6. Regenerar Contraseña
**Descripción:** Generar nueva contraseña para cuenta de servicio

**Parámetros:**
- `venta_id`: ID de la venta

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "venta_id": 123,
    "servicio": "NETFLIX",
    "nueva_password": "NuevaPass123",
    "fecha_cambio": "2026-01-15 11:15:00"
  },
  "message": "Contraseña regenerada exitosamente"
}
```

#### T7. Manual de Soluciones
**Descripción:** Base de conocimiento de problemas comunes

**Parámetros:**
- `query`: búsqueda de problema
- `servicio`: (opcional) filtrar por servicio

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "resultados": [
      {
        "id": 1,
        "servicio": "NETFLIX",
        "problema": "Error de inicio de sesión",
        "solucion": "1. Verificar email/password\n2. Intentar desde otro dispositivo\n3. Regenerar contraseña si persiste",
        "relevancia": 95
      }
    ]
  }
}
```

---

### 📋 Secretaria - Acciones Detalladas

#### S1. Crear Recordatorio
**Descripción:** Programar recordatorio para fecha específica

**Parámetros:**
- `titulo`: título del recordatorio
- `descripcion`: (opcional) detalles
- `fecha`: fecha del recordatorio (YYYY-MM-DD)
- `hora`: (opcional) hora específica (HH:MM)
- `prioridad`: baja, media, alta

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "recordatorio_id": 456,
    "titulo": "Llamar a cliente Juan",
    "descripcion": "Seguimiento de renovación",
    "fecha": "2026-01-20",
    "hora": "10:00",
    "prioridad": "media",
    "estado": "pendiente"
  },
  "message": "Recordatorio creado exitosamente"
}
```

#### S2. Consultar Agenda
**Descripción:** Obtener eventos de un período

**Parámetros:**
- `periodo`: today, tomorrow, week, month, custom
- `fecha_inicio`: (opcional) para custom
- `fecha_fin`: (opcional) para custom

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "periodo": "today",
    "fecha": "2026-01-15",
    "total_eventos": 5,
    "eventos": [
      {
        "id": 456,
        "tipo": "recordatorio",
        "titulo": "Llamar a cliente Juan",
        "hora": "10:00",
        "prioridad": "media",
        "estado": "pendiente"
      }
    ]
  }
}
```

#### S3. Registrar Actividad
**Descripción:** Registrar actividad completada

**Parámetros:**
- `titulo`: título de la actividad
- `descripcion`: detalle de lo realizado
- `categoria`: llamada, reunion, tarea, otro
- `duracion_minutos`: (opcional)

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "actividad_id": 789,
    "titulo": "Reunión con proveedores",
    "categoria": "reunion",
    "fecha_realizacion": "2026-01-15 14:30:00",
    "duracion_minutos": 45
  },
  "message": "Actividad registrada exitosamente"
}
```

#### S4. Listar Tareas Pendientes
**Descripción:** Obtener todas las tareas sin completar

**Parámetros:**
- `prioridad`: (opcional) filtrar por prioridad
- `vencidas`: (opcional) solo mostrar vencidas

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "total_pendientes": 8,
    "total_vencidas": 2,
    "tareas": [
      {
        "id": 456,
        "titulo": "Llamar a cliente Juan",
        "fecha_limite": "2026-01-15",
        "prioridad": "media",
        "vencida": false
      }
    ]
  }
}
```

#### S5. Marcar Recordatorio Completado
**Descripción:** Completar un recordatorio

**Parámetros:**
- `recordatorio_id`: ID del recordatorio
- `notas`: (opcional) notas sobre la completación

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "recordatorio_id": 456,
    "titulo": "Llamar a cliente Juan",
    "estado_anterior": "pendiente",
    "estado_nuevo": "completado",
    "fecha_completado": "2026-01-15 15:00:00"
  },
  "message": "Recordatorio marcado como completado"
}
```

#### S6. Resumen de Actividades Diarias
**Descripción:** Reporte de productividad del día

**Parámetros:**
- `fecha`: (opcional, default: hoy)

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "fecha": "2026-01-15",
    "tareas_completadas": 5,
    "tareas_pendientes": 3,
    "recordatorios_cumplidos": 4,
    "tiempo_total_minutos": 240,
    "actividades": [
      {
        "categoria": "llamada",
        "cantidad": 3,
        "tiempo_total": 45
      }
    ]
  }
}
```

#### S7. Programar Seguimiento de Cliente
**Descripción:** Crear seguimiento automático para cliente

**Parámetros:**
- `cliente_id`: ID del cliente
- `tipo_seguimiento`: renovacion, satisfaccion, soporte
- `fecha`: fecha del seguimiento
- `notas`: (opcional)

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "seguimiento_id": 999,
    "cliente": "Juan Pérez",
    "tipo_seguimiento": "renovacion",
    "fecha_programada": "2026-01-25",
    "notas": "Cliente con vencimiento próximo"
  },
  "message": "Seguimiento programado exitosamente"
}
```

---

## 🌐 Endpoints API Propuestos

### Estructura de Rutas V2 para Empleados

```php
Route::prefix('v2')->group(function () {
    
    // ==========================================
    // AUTENTICACIÓN DE EMPLEADOS
    // ==========================================
    Route::prefix('employee')->group(function () {
        Route::post('/auth/login', 'EmployeeAuthController@login');
        Route::post('/auth/logout', 'EmployeeAuthController@logout');
        Route::post('/auth/validate-session', 'EmployeeAuthController@validateSession');
        Route::get('/auth/profile', 'EmployeeAuthController@getProfile');
    });

    // ==========================================
    // RUTAS PROTEGIDAS POR ROL
    // ==========================================
    Route::middleware(['auth:employee', 'role:employee'])->group(function () {
        
        // ------------------------------------------
        // CONTADOR (Accountant)
        // ------------------------------------------
        Route::prefix('accountant')->middleware('role:contador')->group(function () {
            // Ventas y Finanzas
            Route::get('/ventas/resumen', 'AccountantController@resumenVentas');
            Route::get('/ventas/facturas-pendientes', 'AccountantController@facturasPendientes');
            Route::get('/ventas/ingresos-por-servicio', 'AccountantController@ingresosPorServicio');
            Route::get('/ventas/proyeccion', 'AccountantController@proyeccionIngresos');
            
            // Clientes y Análisis
            Route::get('/clientes/morosos', 'AccountantController@clientesMorosos');
            Route::get('/clientes/estadisticas', 'AccountantController@estadisticasClientes');
            
            // Reportes
            Route::get('/reportes/general', 'AccountantController@reporteGeneral');
            Route::post('/reportes/exportar', 'AccountantController@exportarReporte');
            
            // Métodos de Pago
            Route::get('/metodos-pago/estadisticas', 'AccountantController@estadisticasMetodosPago');
        });

        // ------------------------------------------
        // TÉCNICO (Technical Support)
        // ------------------------------------------
        Route::prefix('technical')->middleware('role:tecnico')->group(function () {
            // Búsqueda de Clientes
            Route::post('/clientes/buscar', 'TechnicalController@buscarCliente');
            Route::get('/clientes/{id}/cuentas', 'TechnicalController@obtenerCuentasCliente');
            Route::get('/clientes/{id}/historial', 'TechnicalController@historialCliente');
            
            // Gestión de Cuentas
            Route::post('/cuentas/{id}/cambiar-perfil', 'TechnicalController@cambiarPerfil');
            Route::post('/cuentas/{id}/regenerar-password', 'TechnicalController@regenerarPassword');
            Route::patch('/cuentas/{id}/actualizar', 'TechnicalController@actualizarCuenta');
            
            // Tickets de Soporte
            Route::get('/tickets', 'TechnicalController@listarTickets');
            Route::post('/tickets', 'TechnicalController@crearTicket');
            Route::get('/tickets/{id}', 'TechnicalController@detalleTicket');
            Route::patch('/tickets/{id}/resolver', 'TechnicalController@resolverTicket');
            Route::patch('/tickets/{id}/cerrar', 'TechnicalController@cerrarTicket');
            
            // Base de Conocimiento
            Route::get('/manual/buscar', 'TechnicalController@buscarSolucion');
            Route::get('/manual/{id}', 'TechnicalController@obtenerSolucion');
        });

        // ------------------------------------------
        // SECRETARIA (Administrative Assistant)
        // ------------------------------------------
        Route::prefix('secretary')->middleware('role:secretaria')->group(function () {
            // Recordatorios
            Route::get('/recordatorios', 'SecretaryController@listarRecordatorios');
            Route::post('/recordatorios', 'SecretaryController@crearRecordatorio');
            Route::get('/recordatorios/{id}', 'SecretaryController@detalleRecordatorio');
            Route::patch('/recordatorios/{id}/completar', 'SecretaryController@completarRecordatorio');
            Route::delete('/recordatorios/{id}', 'SecretaryController@eliminarRecordatorio');
            
            // Agenda
            Route::get('/agenda', 'SecretaryController@consultarAgenda');
            Route::get('/agenda/pendientes', 'SecretaryController@tareasPendientes');
            
            // Actividades
            Route::get('/actividades', 'SecretaryController@listarActividades');
            Route::post('/actividades', 'SecretaryController@registrarActividad');
            Route::get('/actividades/resumen', 'SecretaryController@resumenDiario');
            
            // Seguimientos
            Route::get('/seguimientos', 'SecretaryController@listarSeguimientos');
            Route::post('/seguimientos', 'SecretaryController@programarSeguimiento');
            Route::patch('/seguimientos/{id}/completar', 'SecretaryController@completarSeguimiento');
        });

        // ------------------------------------------
        // RUTAS COMUNES PARA TODOS LOS EMPLEADOS
        // ------------------------------------------
        Route::prefix('common')->group(function () {
            // Información de la Empresa
            Route::get('/precios', 'CommonController@obtenerPrecios');
            Route::get('/servicios', 'CommonController@listarServicios');
            Route::get('/metodos-pago', 'CommonController@metodosPago');
            
            // Búsquedas Generales
            Route::post('/buscar-cliente', 'CommonController@buscarCliente');
            Route::get('/estadisticas/general', 'CommonController@estadisticasGenerales');
        });
    });
});
```

---

## 🔄 Flujos de Interacción

### Flujo 1: Autenticación de Empleado (Telegram Bot)

```
┌─────────────┐
│  Empleado   │
│  (Telegram) │
└──────┬──────┘
       │
       ├──> 1. Comando: /login
       │
       ├──> 2. Bot solicita: Email
       │
       ├──> 3. Empleado envía: empleado@streamify.com
       │
       ├──> 4. Bot solicita: Password
       │
       ├──> 5. Empleado envía: ********
       │
       ├──> 6. API POST /v2/employee/auth/login
       │         {
       │           "email": "empleado@streamify.com",
       │           "password": "********",
       │           "telegram_id": "123456789"
       │         }
       │
       ├──> 7. Sistema valida credenciales
       │         - Verifica en tabla empleados
       │         - Verifica rol activo
       │         - Genera token de sesión
       │
       ├──> 8. API Response:
       │         {
       │           "success": true,
       │           "data": {
       │             "token": "eyJ0eXAiOiJKV1...",
       │             "empleado": {
       │               "id": 1,
       │               "nombre": "Juan Técnico",
       │               "rol": "tecnico",
       │               "permisos": ["ver_cuentas", "editar_cuentas"]
       │             }
       │           }
       │         }
       │
       └──> 9. Bot muestra menú según rol
             ┌─────────────────────────┐
             │ ¡Bienvenido Juan! 🔧   │
             │                         │
             │ Eres: Técnico          │
             │                         │
             │ Opciones:              │
             │ 1️⃣ Buscar Cliente      │
             │ 2️⃣ Ver Tickets         │
             │ 3️⃣ Crear Ticket        │
             │ 4️⃣ Manual Técnico      │
             └─────────────────────────┘
```

### Flujo 2: Contador - Resumen de Ventas Diarias

```
┌──────────────┐
│  Contador    │
│  (Telegram)  │
└──────┬───────┘
       │
       ├──> 1. Comando: /ventas_hoy
       │
       ├──> 2. API GET /v2/accountant/ventas/resumen?periodo=daily
       │         Headers: Authorization: Bearer {token}
       │
       ├──> 3. Sistema procesa:
       │         - Obtiene ventas del día actual
       │         - Calcula totales por servicio
       │         - Agrupa por método de pago
       │         - Calcula promedios
       │
       ├──> 4. API Response:
       │         {
       │           "success": true,
       │           "data": {
       │             "periodo": "2026-01-15",
       │             "total_ventas": 1250.50,
       │             "cantidad_ventas": 45,
       │             "promedio_venta": 27.79,
       │             "servicios": [...]
       │           }
       │         }
       │
       └──> 5. Bot formatea y muestra:
             ┌─────────────────────────────┐
             │ 📊 Ventas del Día           │
             │ Fecha: 15/01/2026          │
             │                             │
             │ Total: $1,250.50           │
             │ Ventas: 45                 │
             │ Promedio: $27.79           │
             │                             │
             │ Por Servicio:              │
             │ • Netflix: $600 (20)       │
             │ • Disney+: $350 (15)       │
             │ • HBO Max: $300.50 (10)    │
             │                             │
             │ Métodos de Pago:           │
             │ • Transferencia: $900 (30) │
             │ • PayPal: $350.50 (15)     │
             └─────────────────────────────┘
```

### Flujo 3: Técnico - Resolver Problema de Cliente

```
┌──────────────┐
│   Técnico    │
│  (Telegram)  │
└──────┬───────┘
       │
       ├──> 1. Cliente reporta: "No puedo entrar a Netflix"
       │
       ├──> 2. Técnico: /buscar_cliente
       │
       ├──> 3. Bot: "¿Criterio de búsqueda?"
       │         1. Email
       │         2. Teléfono
       │         3. Nombre
       │
       ├──> 4. Técnico: 1
       │
       ├──> 5. Bot: "Ingresa el email"
       │
       ├──> 6. Técnico: cliente@example.com
       │
       ├──> 7. API POST /v2/technical/clientes/buscar
       │         {
       │           "criterio": "email",
       │           "valor": "cliente@example.com"
       │         }
       │
       ├──> 8. Bot muestra datos del cliente
       │
       ├──> 9. Técnico: /ver_cuentas {cliente_id}
       │
       ├──> 10. API GET /v2/technical/clientes/{id}/cuentas
       │
       ├──> 11. Bot muestra cuentas activas
       │
       ├──> 12. Técnico identifica problema (cuenta bloqueada)
       │
       ├──> 13. Técnico: /crear_ticket
       │
       ├──> 14. API POST /v2/technical/tickets
       │          {
       │            "cliente_id": 45,
       │            "servicio": "NETFLIX",
       │            "tipo_problema": "acceso",
       │            "descripcion": "Cuenta bloqueada por múltiples dispositivos",
       │            "prioridad": "alta"
       │          }
       │
       ├──> 15. Técnico: /regenerar_password {venta_id}
       │
       ├──> 16. API POST /v2/technical/cuentas/{id}/regenerar-password
       │
       ├──> 17. Bot muestra nueva contraseña
       │
       ├──> 18. Técnico proporciona nueva contraseña al cliente
       │
       └──> 19. Técnico: /resolver_ticket {ticket_id}
              API PATCH /v2/technical/tickets/{id}/resolver
              
              ┌─────────────────────────────────┐
              │ ✅ Ticket Resuelto              │
              │                                 │
              │ ID: 789                        │
              │ Cliente: Juan Pérez            │
              │ Problema: Cuenta bloqueada     │
              │ Solución: Password regenerada  │
              │                                 │
              │ Nueva Password: NuevaPass123   │
              │ Tiempo de Resolución: 5 min    │
              └─────────────────────────────────┘
```

### Flujo 4: Secretaria - Gestión de Agenda

```
┌──────────────┐
│  Secretaria  │
│  (Telegram)  │
└──────┬───────┘
       │
       ├──> 1. Inicia jornada: /agenda_hoy
       │
       ├──> 2. API GET /v2/secretary/agenda?periodo=today
       │
       ├──> 3. Bot muestra eventos del día
       │         ┌─────────────────────────────┐
       │         │ 📅 Agenda del Día          │
       │         │ 15/01/2026                 │
       │         │                             │
       │         │ 🔔 Pendientes (5):         │
       │         │ • 10:00 - Llamar a Juan    │
       │         │ • 14:00 - Reunión equipo   │
       │         │ • 16:00 - Seguimiento María │
       │         │                             │
       │         │ ✅ Completadas (2)         │
       │         └─────────────────────────────┘
       │
       ├──> 4. Durante el día: /completar_recordatorio 456
       │
       ├──> 5. API PATCH /v2/secretary/recordatorios/{id}/completar
       │         {
       │           "notas": "Cliente contactado, renovará la próxima semana"
       │         }
       │
       ├──> 6. Cliente requiere seguimiento
       │
       ├──> 7. Secretaria: /seguimiento_cliente
       │
       ├──> 8. Bot: "ID del cliente?"
       │
       ├──> 9. Secretaria: 45
       │
       ├──> 10. Bot: "Tipo de seguimiento?"
       │          1. Renovación
       │          2. Satisfacción
       │          3. Soporte
       │
       ├──> 11. Secretaria: 1
       │
       ├──> 12. Bot: "¿Fecha? (YYYY-MM-DD)"
       │
       ├──> 13. Secretaria: 2026-01-22
       │
       ├──> 14. API POST /v2/secretary/seguimientos
       │          {
       │            "cliente_id": 45,
       │            "tipo_seguimiento": "renovacion",
       │            "fecha": "2026-01-22",
       │            "notas": "Cliente interesado en combo"
       │          }
       │
       ├──> 15. Al final del día: /resumen_dia
       │
       └──> 16. API GET /v2/secretary/actividades/resumen
              
              ┌─────────────────────────────────┐
              │ 📊 Resumen del Día              │
              │ 15/01/2026                      │
              │                                 │
              │ ✅ Tareas Completadas: 5       │
              │ ⏳ Pendientes: 3               │
              │ 📞 Llamadas Realizadas: 8      │
              │ ⏱️ Tiempo Total: 4h 30min      │
              │                                 │
              │ 💪 ¡Excelente trabajo!         │
              └─────────────────────────────────┘
```

---

## 📊 Estructura de Datos

### Tablas Nuevas Requeridas

#### 1. `employee_sessions` - Sesiones de Empleados
```sql
CREATE TABLE employee_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT UNSIGNED NOT NULL,
    telegram_id VARCHAR(255) NOT NULL,
    token VARCHAR(500) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    last_activity TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(idempl) ON DELETE CASCADE,
    INDEX idx_telegram_id (telegram_id),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);
```

#### 2. `tickets_soporte` - Tickets de Soporte Técnico
```sql
CREATE TABLE tickets_soporte (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    venta_id INT UNSIGNED NULL,
    servicio VARCHAR(50) NOT NULL,
    tipo_problema ENUM('acceso', 'calidad', 'configuracion', 'otro') NOT NULL,
    descripcion TEXT NOT NULL,
    prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media',
    estado ENUM('abierto', 'en_proceso', 'resuelto', 'cerrado') DEFAULT 'abierto',
    creado_por INT UNSIGNED NULL,
    asignado_a INT UNSIGNED NULL,
    resuelto_por INT UNSIGNED NULL,
    fecha_resolucion TIMESTAMP NULL,
    solucion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(idcli) ON DELETE CASCADE,
    FOREIGN KEY (venta_id) REFERENCES ventas(idven) ON DELETE SET NULL,
    FOREIGN KEY (creado_por) REFERENCES empleados(idempl) ON DELETE SET NULL,
    FOREIGN KEY (asignado_a) REFERENCES empleados(idempl) ON DELETE SET NULL,
    FOREIGN KEY (resuelto_por) REFERENCES empleados(idempl) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_estado (estado),
    INDEX idx_prioridad (prioridad)
);
```

#### 3. `recordatorios` - Recordatorios y Agenda
```sql
CREATE TABLE recordatorios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    fecha DATE NOT NULL,
    hora TIME NULL,
    prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media',
    estado ENUM('pendiente', 'completado', 'cancelado') DEFAULT 'pendiente',
    creado_por INT UNSIGNED NOT NULL,
    completado_por INT UNSIGNED NULL,
    fecha_completado TIMESTAMP NULL,
    notas_completacion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES empleados(idempl) ON DELETE CASCADE,
    FOREIGN KEY (completado_por) REFERENCES empleados(idempl) ON DELETE SET NULL,
    INDEX idx_fecha (fecha),
    INDEX idx_estado (estado),
    INDEX idx_creado_por (creado_por)
);
```

#### 4. `actividades` - Registro de Actividades
```sql
CREATE TABLE actividades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    categoria ENUM('llamada', 'reunion', 'tarea', 'otro') NOT NULL,
    duracion_minutos INT UNSIGNED NULL,
    empleado_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(idempl) ON DELETE CASCADE,
    INDEX idx_empleado (empleado_id),
    INDEX idx_fecha (created_at)
);
```

#### 5. `seguimientos_clientes` - Seguimientos Programados
```sql
CREATE TABLE seguimientos_clientes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    tipo_seguimiento ENUM('renovacion', 'satisfaccion', 'soporte') NOT NULL,
    fecha_programada DATE NOT NULL,
    estado ENUM('pendiente', 'completado', 'cancelado') DEFAULT 'pendiente',
    notas TEXT NULL,
    creado_por INT UNSIGNED NOT NULL,
    completado_por INT UNSIGNED NULL,
    fecha_completado TIMESTAMP NULL,
    resultado TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(idcli) ON DELETE CASCADE,
    FOREIGN KEY (creado_por) REFERENCES empleados(idempl) ON DELETE CASCADE,
    FOREIGN KEY (completado_por) REFERENCES empleados(idempl) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_fecha_programada (fecha_programada),
    INDEX idx_estado (estado)
);
```

#### 6. `manual_soluciones` - Base de Conocimiento
```sql
CREATE TABLE manual_soluciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    servicio VARCHAR(50) NULL,
    problema VARCHAR(255) NOT NULL,
    solucion TEXT NOT NULL,
    palabras_clave TEXT NULL,
    categoria VARCHAR(100) NULL,
    relevancia INT DEFAULT 0,
    creado_por INT UNSIGNED NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES empleados(idempl) ON DELETE SET NULL,
    INDEX idx_servicio (servicio),
    INDEX idx_activo (activo),
    FULLTEXT KEY fulltext_problema_solucion (problema, solucion, palabras_clave)
);
```

### Modificaciones a Tablas Existentes

#### Tabla `empleados` - Agregar campos
```sql
ALTER TABLE empleados 
ADD COLUMN rol ENUM('contador', 'tecnico', 'secretaria', 'admin') NULL AFTER cargo,
ADD COLUMN telegram_id VARCHAR(255) NULL AFTER telefono,
ADD COLUMN telegram_username VARCHAR(255) NULL AFTER telegram_id,
ADD COLUMN activo BOOLEAN DEFAULT TRUE AFTER estado,
ADD INDEX idx_telegram_id (telegram_id),
ADD INDEX idx_rol (rol);
```

---

## 🔒 Consideraciones de Seguridad

### 1. Autenticación de Empleados

**Flujo de Autenticación:**
1. Empleado envía credenciales (email + password) desde Telegram
2. Sistema valida contra tabla `empleados`
3. Verifica que el campo `activo = true`
4. Genera JWT token con datos:
   - `empleado_id`
   - `rol`
   - `telegram_id`
   - `exp` (expiración: 24 horas)
5. Guarda sesión en `employee_sessions`
6. Retorna token al bot

**Validación de Sesión:**
- Cada request debe incluir: `Authorization: Bearer {token}`
- Middleware `auth:employee` valida el token
- Verifica que la sesión no haya expirado
- Verifica que el empleado siga activo

### 2. Control de Acceso por Rol

**Middleware `role:{rol}`:**
```php
// Ejemplo de verificación
public function handle($request, Closure $next, $rol)
{
    $empleado = auth()->guard('empleado')->user();
    
    if (!$empleado || $empleado->rol !== $rol) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permisos para esta acción'
        ], 403);
    }
    
    return $next($request);
}
```

**Matriz de Permisos:**

| Acción | Contador | Técnico | Secretaria |
|--------|----------|---------|------------|
| Ver ventas | ✅ | ❌ | ❌ |
| Ver clientes | ✅ | ✅ | ✅ |
| Editar cuentas | ❌ | ✅ | ❌ |
| Crear tickets | ❌ | ✅ | ❌ |
| Gestionar agenda | ❌ | ❌ | ✅ |
| Ver estadísticas financieras | ✅ | ❌ | ❌ |

### 3. Registro de Auditoría

**Acciones a Registrar:**
- Inicio/cierre de sesión
- Consultas de datos sensibles (credenciales de clientes)
- Modificaciones de cuentas
- Creación/resolución de tickets
- Exportación de reportes

**Tabla `audit_logs`:**
```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT UNSIGNED NOT NULL,
    accion VARCHAR(255) NOT NULL,
    entidad VARCHAR(100) NULL,
    entidad_id INT UNSIGNED NULL,
    datos_anteriores JSON NULL,
    datos_nuevos JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(idempl) ON DELETE CASCADE,
    INDEX idx_empleado (empleado_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (created_at)
);
```

### 4. Limitación de Peticiones (Rate Limiting)

**Configuración Sugerida:**
- Autenticación: 5 intentos / 15 minutos
- Búsquedas: 60 peticiones / minuto
- Reportes pesados: 10 peticiones / hora
- Exportaciones: 5 peticiones / hora

### 5. Encriptación de Datos Sensibles

**Datos a Encriptar:**
- Tokens de sesión
- Contraseñas de cuentas de streaming (ya implementado)
- Números de cuenta bancaria (si se agregan)

### 6. Validación de Telegram ID

**Prevención de Suplantación:**
```php
public function login(Request $request)
{
    // Verificar que telegram_id no esté ya vinculado a otro empleado
    if ($request->telegram_id) {
        $existente = Empleado::where('telegram_id', $request->telegram_id)
            ->where('idemp', '!=', $empleado->idemp)
            ->first();
        
        if ($existente) {
            return response()->json([
                'success' => false,
                'message' => 'Este Telegram ya está vinculado a otra cuenta'
            ], 400);
        }
    }
}
```

---

## 📝 Próximos Pasos de Implementación

### Fase 1: Fundamentos (Semana 1)
- [x] Documentación completa de roles y acciones
- [ ] Creación de migraciones para nuevas tablas
- [ ] Implementación de `EmployeeAuthController`
- [ ] Middleware de autenticación para empleados
- [ ] Middleware de control de roles

### Fase 2: Módulo Contador (Semana 2)
- [ ] Modelo y migración de auditoría
- [ ] `AccountantController` completo
- [ ] Endpoints de ventas y finanzas
- [ ] Endpoints de reportes
- [ ] Testing de endpoints

### Fase 3: Módulo Técnico (Semana 3)
- [ ] Modelo `TicketSoporte`
- [ ] Modelo `ManualSoluciones`
- [ ] `TechnicalController` completo
- [ ] Endpoints de gestión de cuentas
- [ ] Endpoints de tickets
- [ ] Sistema de búsqueda en manual

### Fase 4: Módulo Secretaria (Semana 4)
- [ ] Modelo `Recordatorio`
- [ ] Modelo `Actividad`
- [ ] Modelo `SeguimientoCliente`
- [ ] `SecretaryController` completo
- [ ] Endpoints de agenda
- [ ] Sistema de notificaciones

### Fase 5: Integración con Telegram Bot (Semana 5)
- [ ] Actualización del flujo de N8N
- [ ] Comandos para cada rol
- [ ] Formateo de respuestas
- [ ] Menús interactivos
- [ ] Manejo de estados de conversación

### Fase 6: Testing y Optimización (Semana 6)
- [ ] Tests unitarios
- [ ] Tests de integración
- [ ] Optimización de queries
- [ ] Documentación de API (Swagger/Postman)
- [ ] Manual de usuario para cada rol

---

## 📚 Referencias

- [Documentación Laravel](https://laravel.com/docs)
- [JWT Authentication](https://jwt.io/)
- [Telegram Bot API](https://core.telegram.org/bots/api)
- [N8N Workflows](https://n8n.io/)
- [API REST Best Practices](https://restfulapi.net/)

---

**Fin del Documento** | Streamify v6.0 | 2026
