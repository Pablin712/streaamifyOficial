# Streamify ERP — Funcionalidades del Panel `/admin`

> Documento de apoyo para el video explicativo (perspectiva de un empleado usando el panel).
> Cubre **únicamente** las rutas bajo `/admin` (el panel operativo interno). No incluye el
> área de cliente (`/cliente/*`), la tienda pública (`/shop`) ni las APIs de integración (n8n, apps móviles).
> Organizado igual que el menú lateral real del sistema, para que el guion del video siga el mismo
> recorrido que vería un empleado al iniciar sesión.

---

## 0. Acceso y sesión

- **Login de empleados** (`/admin/login`) y **recuperación de contraseña** (`/admin/recover`) vía email.
- **Modo concentración**: un toggle que un empleado activa cuando quiere enfocarse solo en sus tareas asignadas (oculta ruido de otras secciones).
- **Notificaciones internas** en tiempo real (campanita) con marcado de leído.
- **Registro de asistencia automático** (ping cada 5 minutos mientras el panel está abierto, con la ruta actual) — alimenta el módulo de Control (sección 2.2).

**Punto de venta:** el sistema no es "todo o nada" — se puede dar acceso quirúrgico por empleado, e incluso temporal, sin fricción administrativa. (Detalle completo del control por rol en la sección 0.1.)

---

## 0.1 Control de acceso por rol y por tarea (el corazón de la seguridad del sistema)

Esto es probablemente el punto técnico más vendible del ERP para un negocio que quiere **contratar personal externo sin miedo a filtraciones de datos**.

### Roles fijos (Spatie Permission)
`Admin`, `Gerente`, `Trabajador`, `Técnico`, `Vendedor`, `Bodeguero`, `Contador`, `Visitante` (solo lectura) y **`Trabajador externo`**. Cada módulo, botón y acción (crear/editar/eliminar/exportar) tiene su propio permiso — un vendedor puede ver ventas pero no tocar bancos; un técnico puede gestionar cuentas pero no ver reportes financieros.

### El rol especial "Trabajador externo": acceso filtrado por tarea, no por módulo
A diferencia de los demás roles (que ven un módulo completo si tienen el permiso), un **Trabajador externo nunca ve el negocio completo**: su "modo concentración" queda **forzado y bloqueado** — solo puede ver y actuar sobre lo que sus tareas asignadas le habilitan, tarea por tarea:

| Tarea asignada | Qué desbloquea |
|---|---|
| `cobrar_usuario` | Chats y usuarios del cliente — **sin acceso a cuentas** |
| `quitar_usuario` | Chats, cuentas y usuarios relacionados a ese usuario |
| `renovar_cuenta` | Chats con el proveedor, cuentas y usuarios de esa cuenta |
| `cuenta_caida` | Soportes, chats, cuentas y usuarios relacionados |
| `colapso_cuenta` (ajustar espacio) | Chats con proveedores, cuentas y usuarios |
| `soporte_pendiente` | El soporte propio, chats del cliente, cuentas y usuarios de ese soporte |
| `agregar_stock` | Chats con **todos** los proveedores, cuentas (solo vista vacía), servicios y valores |

Es decir: si a un externo le asignan "cobrar al cliente Juan", **solo ve a Juan** — no ve el resto de la cartera de clientes, ni cuentas que no le corresponden.

### Roles temporales por tarea ("Rol temporal") — sin crear roles nuevos
Cuando un externo necesita cubrir un rol amplio por un rato (ej. "sé el vendedor de hoy" o "atiende todos los soportes esta tarde"), un Admin/Gerente le asigna una **tarea general** (`general_vender`, `general_atender_clientes`, `general_administrar_cuentas`) desde el mismo tablero de tareas. Mientras esa tarea está activa:
- Se le amplía el alcance sin filtro (ve todas las cuentas/usuarios/clientes/soportes según el dominio).
- Se le otorgan **permisos extra en tiempo real** (ej. crear ventas, editar servicios) que su rol base no tiene.
- Todo se revierte automáticamente en cuanto la tarea se marca como completada — **no queda ningún permiso permanente**.

**Punto de venta:** se puede escalar el equipo con freelancers sin exponer toda la base de clientes ni el negocio completo — y se les puede dar poder temporal para cubrir un turno sin tener que andar creando y borrando roles manualmente.

---

## 1. El Copiloto de IA interno (widget flotante, disponible en TODAS las páginas del admin)

Este es un asistente distinto a Donna y distinto al Chat WhatsApp: es un **robot flotante** (burbuja abajo a la derecha) que aparece en **cada pantalla del panel** para cualquier empleado autenticado, con historial de conversación guardado en el navegador.

- El empleado le escribe en lenguaje natural ("¿cuánto llevamos de ingresos este mes en Netflix?", "¿qué clientes están morosos?", "desactiva los usuarios vencidos de hoy", "muéstrame las cuentas de Disney con espacio libre").
- Por debajo, el mensaje viaja (vía proxy Laravel, evitando problemas de CORS) a un flujo de IA en n8n que tiene acceso a **endpoints propios de solo-lectura y de acción** sobre el negocio:
  - **Consulta/análisis:** resumen de ventas, facturas pendientes/vencidas, proyección de ingresos, ingresos por servicio, clientes morosos, estadísticas de clientes, análisis financiero y de costos por servicio, estado de cuentas por servicio, servicios más vendidos, promedio de clientes por cuenta.
  - **Acciones reales sobre el sistema** (no solo lectura): crear o editar ventas, cambiar precios (individual o masivo), activar/desactivar productos, mover un usuario a otra cuenta o a "la mesa", desactivar usuarios vencidos en lote, crear servicios/valores/proveedores nuevos.
- Es, en la práctica, un **empleado de IA adicional** que cualquiera del equipo puede "preguntar" en vez de tener que navegar por 10 pantallas distintas o pedirle el dato al contador.

**Punto de venta fuerte:** ningún ERP de reventa típico trae un copiloto de IA embebido que responde preguntas de negocio y **ejecuta acciones** con solo escribirle — esto es diferenciador frente a la competencia y complementa (no reemplaza) a Donna, que es la IA de cara al cliente final.

---

## 2. Sección "Principal"

### 2.0 Inicio (`/admin/inicio`)
Página de bienvenida al entrar al panel: saluda al empleado por nombre y ofrece **accesos rápidos** (tarjetas) a las funciones más usadas, para no tener que buscarlas en el menú. Es distinta del "Dashboard" (que es el panel financiero/analítico).

### 2.1 Tareas (`/admin/tareas`)
Tablero de tareas (kanban) que centraliza el trabajo operativo diario:
- Tareas automáticas generadas por el sistema (cobros pendientes, cuentas caídas, soportes sin atender, stock por agregar, renovaciones) y tareas manuales.
- Cada tarea tiene un tipo (cobrar usuario, quitar usuario, renovar cuenta, cuenta caída, ajustar espacio, soporte pendiente, agregar stock, manual) que alimenta el sistema de rendimiento.
- Envío de **cobros masivos por WhatsApp** directo desde una tarea (integrado con n8n).
- Filtro automático: un trabajador externo solo ve las tareas que le corresponden según reglas de negocio (evita que vea todo el negocio sin necesidad).

**Punto de venta:** convierte un negocio de mensajería/Excel en un sistema con lista de trabajo diaria clara — nadie "se olvida" de cobrar o renovar algo.

### 2.2 Dashboard (`/admin/dashboard`)
Panel financiero/operativo central:
- Ingresos del mes, gastos, ganancia neta.
- Desglose por plataforma: Netflix, Disney+, Prime, Max, Magis, Crunchyroll, Paramount, Spotify y "otros".
- Filtros de fecha y exportación a **PDF**.
- Guarda estadísticas diarias históricas (para comparar evolución mes a mes).

### 2.3 Calendario (`/admin/calendario`)
Agenda visual de eventos del negocio (vencimientos, tareas programadas, recordatorios) que además incluye **gestión de horarios/turnos de empleados**: un Admin/Gerente puede programar el turno (fecha, hora de inicio/fin, notas) de cualquier empleado, y un empleado normal puede programar el suyo propio.

### 2.4 Chat WhatsApp (`/chat/whatsapp`)
Helpdesk de WhatsApp integrado (Livewire):
- Bandeja de conversaciones de clientes en vivo, respuesta directa desde el panel.
- **Respuestas rápidas** reutilizables (comandos predefinidos).
- Envío/recepción de multimedia.
- Puede derivar a un **bot de IA** (n8n + IA) que responde automáticamente y solo escala a un humano cuando hace falta.

### 2.5 Biblioteca del Agente (`/agente/biblioteca`)
Repositorio de imágenes/recursos que el agente de IA usa para responder a clientes por chat (catálogos, comprobantes tipo, etc.). No visible para el rol "Trabajador externo".

**Punto de venta:** atención al cliente sin depender 100% de una persona pegada al celular — el agente de IA contiene la primera línea de respuesta.

### 2.6 Historial (`/admin/historial`)
**Bitácora de auditoría** de todo lo que pasa en el sistema: cada acción relevante queda registrada con el empleado que la hizo, la acción y una descripción, con tabla paginada, búsqueda y orden por fecha. Incluye la opción de **purgar historial por rango de fechas** (permiso `historial.clear`, separado de solo verlo).

**Punto de venta:** trazabilidad total — el dueño siempre puede saber "quién hizo qué y cuándo", algo que Excel/WhatsApp nunca ofrecen.

---

## 3. Sección "Negocio" → Administración

### 3.1 Empleados (`/admin/empleados`)
- Alta, edición, cambio de contraseña y **gestión de roles por empleado**.
- Separación clara entre empleados internos (Admin, Gerente, Trabajador, Técnico, Vendedor, Bodeguero, Contador) y externos (rol "Trabajador externo").

### 3.2 Control de asistencias (`/admin/empleados/asistencias`)
- Registro automático de presencia (ping periódico mientras el empleado tiene el panel abierto), con protección anti-duplicados.
- Estadísticas mensuales por empleado, ordenables.

### 3.3 Rendimiento (`/admin/empleados/rendimiento`)
Sistema de puntos por tarea completada, para medir productividad real (no solo "horas conectado"):
- Puntaje por tipo de tarea (ej. quitar un usuario vale más que cobrar uno).
- Niveles diarios automáticos: Sin actividad, Poco esfuerzo, Medio tiempo, Trabajo normal, Buen trabajo, Extra ⭐.
- Filtros por hoy / semana / mes con promedio diario.
- **Detección de tareas sospechosas**: marca en amarillo tareas completadas en menos de 60 segundos desde su asignación (posible fraude de productividad), para que el admin revise.

**Punto de venta:** pagar o premiar según desempeño real y detectar automáticamente quién "hace trampa" marcando tareas sin trabajarlas.

### 3.4 Roles (`/admin/roles`)
CRUD completo de roles y permisos Spatie desde la interfaz, sin tocar código.

---

## 4. Sección "Negocio" → Finanza

### 4.1 Bancos (`/admin/bancos`)
- Registro de cuentas bancarias del negocio con foto/comprobante.
- Transacciones (ingresos/egresos), **transferencias entre cuentas**, pago de deudas.
- Exportación de movimientos a **PDF y Excel**.

### 4.2 Recargas (`/admin/recargas`)
Gestión de recargas/depósitos de clientes (saldo), con verificación de comprobantes (incluye flujo automatizado con IA vía n8n para validar pagos por WhatsApp).

### 4.3 Costos (`/admin/costos`)
CRUD de costos operativos del negocio.

### 4.4 Gastos (`/admin/gastos`)
CRUD de gastos, clasificados por **tipo de gasto** (catálogo propio configurable en `/admin/tipos`).

**Punto de venta:** contabilidad completa del negocio de reventa sin depender de una hoja de Excel aparte — todo conectado al dashboard.

---

## 5. Sección "Negocio" → Comercio

### 5.1 Ventas (`/admin/ventas`)
- Registro de ventas nuevas, renovaciones, ventas asociadas a un cliente existente.
- Cambio de estado de venta, edición, detalle completo.
- **Envío de factura/comprobante** directo al cliente.

### 5.2 Pedidos (`/admin/empleado/pedidos`)
Gestión de pedidos de clientes en curso (estado, seguimiento).

### 5.3 Clientes (`/admin/clientes`)
- CRUD completo de clientes.
- **Mensajería masiva** a clientes (WhatsApp).
- Exportación de listado de clientes.

### 5.4 Gestión de Productos (`/admin/gestion-productos`)
Catálogo maestro: **categorías** y **tipos de producto**, base para el módulo de productos.

### 5.5 Productos (`/admin/productos`)
- CRUD completo de productos (para tienda/catálogo).
- **Exportación SRI** (formato para declaración tributaria — pensado para Ecuador).
- "Curar códigos" (limpieza/normalización masiva de códigos de producto).
- Actualización masiva de precios.
- Catálogo exportable a **PDF**.

---

## 6. Sección "Negocio" → Cuentas

Este es el corazón operativo de un negocio de reventa de streaming:

### 6.1 Cuentas y Perfiles (`/admin/cuentas`)
- Alta de cuentas maestras (Netflix, Disney+, etc.) con estado (activa/dañada/etc.).
- **Mover clientes** entre cuentas, **dispersar clientes** automáticamente para optimizar ocupación.
- **Acomodar usuarios** de una cuenta (reorganizar perfiles).
- Envío de mensajes a clientes o al proveedor directo desde la cuenta (WhatsApp).
- Envío de **inventario disponible al proveedor**.
- Solicitud de **código de verificación de Netflix** integrada.
- Gestión especial de **perfiles de Spotify** (family/duo).
- Renovación de cuenta con historial.
- Eliminación individual o **masiva** de cuentas.

### 6.2 Usuarios Activos (`/admin/usuarios`)
Vista operativa de cada perfil/usuario asignado a un cliente dentro de una cuenta:
- Cambio de perfil, renovación, mover a otra cuenta/mesa/otro servicio.
- Marcar **estado de cobro** y **cuenta dañada**.
- Mensajería directa al cliente.
- Eliminación individual o múltiple.

### 6.3 Mantenimientos (`/admin/mantenimientos`)
Registro de mantenimientos programados a cuentas (ej. cambios de contraseña preventivos, revisiones).

### 6.4 Soportes (`/admin/soportes`)
Ticket de soporte al cliente: atención y respuesta por WhatsApp desde el mismo ticket.

**Punto de venta:** esto reemplaza directamente la gestión manual en Excel/WhatsApp que usan casi todos los negocios de reventa de streaming — con trazabilidad total de qué perfil está en qué cuenta y quién lo atendió.

---

## 7. Sección "Donna Hub" — Agente de IA como producto propio

Donna es un **asistente de IA vendible a los clientes finales** (no solo uso interno), con dos modalidades: `personal` y `business`. Todo su ciclo de vida se administra desde `/admin/donna`:

- **Dashboard** (`/admin/donna/dashboard`): estado general del servicio.
- **Conversaciones** (`/admin/donna/conversaciones`): histórico de mensajes que Donna sostiene con los clientes/usuarios finales.
- **Planes** (`/admin/donna/planes`): CRUD de planes comerciales de Donna (precio, tipo de servicio).
- **Suscripciones** (`/admin/donna/suscripciones`): alta, suspensión y renovación de suscripciones de clientes a Donna.
- **Configuración del agente por suscripción** (`/admin/donna/suscripciones/{id}/config`): personalizar el comportamiento del agente para cada cliente/negocio.
- **Solicitudes** (`/admin/donna/solicitudes`): aprobar o rechazar solicitudes de clientes que quieren activar Donna.
- **Integraciones Google**: administración/revocación de la conexión de Google Calendar/Sheets del cliente.
- Capacidades de Donna vía herramientas de IA: gestión de calendario (crear/editar/eliminar eventos, disponibilidad), tareas (Sheets), memoria de contacto/negocio, base de conocimiento propia por cliente, y flujo completo de ventas/soporte conversacional (crear venta, renovar, crear pedido, crear ticket de soporte) directamente desde el chat.

**Punto de venta fuerte para el video:** Streamify no es solo un ERP de reventa — trae su **propio producto de IA empaquetado y monetizable** (planes, suscripciones, aprobación de solicitudes) que la empresa puede revender a sus propios clientes como un servicio adicional. (No confundir con el Copiloto de IA interno de la sección 1: Donna habla con los *clientes finales*, el Copiloto habla con *tus empleados*.)

---

## 8. Sección "Negocio" → Inventario

### 8.1 Servicios (`/admin/servicios`)
Catálogo de servicios de streaming que el negocio revende (Netflix, Disney+, etc.), con sus variantes.

### 8.2 Proveedores (`/admin/proveedores`)
CRUD de proveedores de cuentas/stock.

### 8.3 Valores (`/admin/valores`)
- Configuración de precios/valores por servicio y pantalla (perfil).
- Corrección masiva de valores, exportación a PDF.
- Asignación de pantallas disponibles por plan.

### 8.4 Correos (`/admin/mails`)
Gestión de plantillas/registros de correo del sistema.

---

## 9. Configuración del sistema

`/admin/sistema` — Panel de configuración exclusivo para el rol **Admin** (ajustes generales de la plataforma).

---

## Resumen para el guion del video

Un buen hilo narrativo "como empleado" podría ser:

1. **Login** → entra con su usuario, ve solo lo que su rol le permite (mostrar cómo un "Trabajador externo" ve un panel recortado vs. un Admin que lo ve todo).
2. **Tareas del día** → sabe exactamente qué cobrar, renovar o atender, sin preguntar a nadie; mencionar cómo un admin le puede dar "rol temporal" a un externo para cubrir un turno.
3. **Le pregunta algo al Copiloto de IA flotante** ("¿cuánto llevo vendido hoy?", "desactiva los vencidos") para mostrar que no todo es clickear — se le puede *hablar* al sistema.
4. **Atiende un cliente por WhatsApp** desde el mismo panel (con ayuda del bot de IA si hace falta).
5. **Registra una venta / renovación** y el sistema envía la factura solo.
6. **Gestiona cuentas y usuarios** (mueve un perfil, marca una cuenta dañada, pide un código de Netflix) sin salir del sistema.
7. **El dueño revisa el Dashboard financiero**, el **Rendimiento del equipo** y el **Historial de auditoría** para tomar decisiones con datos, no con intuición.
8. **Cierre fuerte:** presentar **Donna** como el diferenciador — no es solo gestionar el negocio, es poder *vender IA* como servicio adicional a los propios clientes.

Este recorrido demuestra que el sistema cubre: operación diaria, control de acceso fino por rol/tarea, auditoría, atención al cliente, ventas, finanzas, control de equipo, un copiloto de IA interno, y un producto de IA vendible — todo en un solo panel `/admin`.
