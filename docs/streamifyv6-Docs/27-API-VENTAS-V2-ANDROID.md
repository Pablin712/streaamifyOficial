# API v2 — Ventas (Android Studio)

**Base URL:** `https://streamify.aaronsoft.es/public/api/v2`  
**Autenticación:**
- Flujo 1 (integraciones backend/n8n): `X-API-Key`
- Flujo 2 (app de empleado): `Bearer token` obtenido por login de empleado v2
**API Key:** `sk_FWmIdIrBXqYqsVwAGnX5gLU4FkyHp6WxSzNA6MpegIRa7e7lbYZVgkuNncGd`

---

## Autenticación

### A) Login de empleado v2 (recomendado para ver ventas desde app)

Este login es **distinto al login de cliente**.

- Cliente: usa email/password de la tabla clientes para tienda/chat.
- Empleado: usa usuarioemp/passwordemp de la tabla empleados para operaciones internas (ventas, backoffice).

**POST** `/api/v2/auth/empleado/login`

Body:

```json
{
  "usuarioemp": "usuario_empleado",
  "passwordemp": "tu_clave"
}
```

Respuesta exitosa `200`:

```json
{
  "success": true,
  "message": "Login de empleado exitoso.",
  "data": {
    "token_type": "Bearer",
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
    "expires_in": 3600,
    "empleado": {
      "idemp": 10,
      "nombreemp": "Empleado Demo",
      "usuarioemp": "empleado_demo",
      "email": "empleado@dominio.com",
      "roles": ["Tecnico"]
    }
  }
}
```

Con ese token, envía:

```
Authorization: Bearer TU_ACCESS_TOKEN
Accept: application/json
```

Endpoints de sesión empleado:

- **GET** `/api/v2/auth/empleado/me`
- **POST** `/api/v2/auth/empleado/logout`

### B) API Key (integraciones)

Agrega este header en cada request:

```
X-API-Key: sk_FWmIdIrBXqYqsVwAGnX5gLU4FkyHp6WxSzNA6MpegIRa7e7lbYZVgkuNncGd
```

También puedes enviarlo como query param: `?api_key=sk_FWmIdIrBXqYqsVwAGnX5gLU4FkyHp6WxSzNA6MpegIRa7e7lbYZVgkuNncGd`

---

## Endpoints

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/v2/clientes` | Listar clientes (paginado + búsqueda) |
| `GET` | `/api/v2/productos` | Listar productos (individual/combo, con filtros) |
| `POST` | `/api/v2/auth/empleado/login` | Login específico de empleado (JWT) |
| `GET` | `/api/v2/auth/empleado/me` | Perfil del empleado autenticado |
| `POST` | `/api/v2/auth/empleado/logout` | Cerrar sesión del empleado |
| `GET` | `/api/v2/ventas` | Listar ventas (paginado, con filtros) |
| `GET` | `/api/v2/empleado/ventas` | Listar ventas del empleado autenticado |
| `GET` | `/api/v2/empleado/ventas/{idven}` | Ver detalle de una venta (flujo empleado) |
| `POST` | `/api/v2/ventas` | Crear venta por producto |
| `GET` | `/api/v2/ventas/{idven}` | Ver detalle completo de una venta |
| `PUT/PATCH` | `/api/v2/ventas/{idven}` | Editar venta y/o sus detalles |
| `DELETE` | `/api/v2/ventas/{idven}` | Eliminar venta |
| `POST` | `/api/v2/chat/assistant/venta/renovar` | Renovar una venta existente usando saldo del cliente |
| `GET` | `/api/v2/tech-ventas/estadisticas` | Estadísticas de ventas |

---

## API necesarias para crear ventas

### A) Clientes

**GET** `/api/v2/clientes`

**Auth requerida:** `X-API-Key`

Query params opcionales:

| Param | Tipo | Descripción |
|-------|------|-------------|
| `per_page` | int | Cantidad por página (default: 15) |
| `search` | string | Busca por `nombrecli`, `telefonocli` o `email` |

Respuesta `200` (ejemplo):

```json
{
  "success": true,
  "data": [
    {
      "idcli": "CLI-001",
      "nombrecli": "Juan Perez",
      "telefonocli": "04141234567",
      "email": "juan@email.com",
      "saldo": 20
    }
  ],
  "pagination": {
    "total": 1,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1,
    "from": 1,
    "to": 1
  }
}
```

### B) Productos

**GET** `/api/v2/productos`

**Auth requerida:** `X-API-Key`

Query params opcionales:

| Param | Tipo | Descripción |
|-------|------|-------------|
| `servicio` | string | Filtra por nombre de servicio (ej: NETFLIX) |
| `activo` | bool | `true` o `false` |
| `tipo` | string | `individual` o `combo` |

Respuesta `200` (ejemplo):

```json
{
  "success": true,
  "count": 1,
  "productos": [
    {
      "id": 1,
      "codigo": "NET-1M",
      "nombre": "Netflix 1 mes",
      "precio": 5.99,
      "activo": true,
      "tipo": "individual",
      "servicios": [
        {
          "nombre": "NETFLIX",
          "meses": 1,
          "descripcion": "Plan mensual"
        }
      ]
    }
  ]
}
```

Con esto ya puedes poblar los selects para crear ventas: primero eliges cliente desde `/clientes` y luego producto desde `/productos`.

---

## 1. Listar Ventas

**GET** `/api/v2/ventas`

También disponible para empleado autenticado JWT en:

**GET** `/api/v2/empleado/ventas`

Nota: En `/api/v2/empleado/ventas`, si no envías `idemp`, el backend filtra automáticamente por el empleado autenticado del token.

### Query params (todos opcionales)

| Param | Tipo | Descripción |
|-------|------|-------------|
| `page` | int | Número de página (default: 1) |
| `per_page` | int | Registros por página (default: 20, max: 100) |
| `search` | string | Busca por idven, nombre o teléfono del cliente |
| `idcli` | string | Filtrar por cliente |
| `idemp` | int | Filtrar por empleado |
| `fecha_inicio` | date | Filtrar desde fecha `YYYY-MM-DD` |
| `fecha_fin` | date | Filtrar hasta fecha `YYYY-MM-DD` |
| `sort_by` | string | Campo de orden: `idven`, `fechaven`, `totalpagoven` |
| `sort_order` | string | `asc` o `desc` (default: `desc`) |

### Respuesta exitosa `200`

```json
{
  "success": true,
  "data": [
    {
      "idven": "VEN-001",
      "idcli": "CLI-001",
      "cliente": "Juan Pérez",
      "idemp": 1,
      "empleado": "María García",
      "fecha": "2026-05-04 10:30:00",
      "totalpagoven": 15.00,
      "cantidad_detalles": 2,
      "monto_detalles": 15.00
    }
  ],
  "pagination": {
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "last_page": 5,
    "from": 1,
    "to": 20
  }
}
```

### Ejemplo Android (Retrofit)

```java
@GET("api/v2/ventas")
Call<VentasListResponse> listarVentas(
    @Header("X-API-Key") String apiKey,
    @Query("page") int page,
    @Query("per_page") int perPage,
    @Query("search") String search
);
```

---

## Renovar Venta (automatizaciones)

**POST** `/api/v2/chat/assistant/venta/renovar`
**Content-Type:** `application/json`

Este endpoint esta pensado para automatizaciones y agentes como `vendedor_cierre`.

### Body (JSON)

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `idven` | string | ✅ | ID de la venta original a renovar |
| `idcli` | string | ✅ | ID del cliente dueño de la venta |
| `meses` | int | ✅ | Cantidad de meses a renovar (1-12) |
| `detalles` | array<int> | ❌ | IDs de detalles (`iddet`) que se desean renovar. Si no se envía, renueva todos los detalles activos de esa venta |

```json
{
  "idven": "VEN-045",
  "idcli": "CLI-001",
  "meses": 2,
  "detalles": [89]
}
```

Renovación parcial (ejemplo):
- Venta original tiene 2 detalles activos: Netflix (`iddet=89`) y Spotify (`iddet=90`).
- Si envías `detalles: [90]`, solo se renueva Spotify.
- Netflix queda activo en la venta anterior y no se copia a la nueva venta de renovación.

### Qué hace internamente

1. Verifica que la venta pertenezca al cliente.
2. Toma los detalles activos de la venta original.
  - si envías `detalles`, usa solo esos `iddet`.
  - si no envías `detalles`, usa todos los detalles activos de la venta.
3. Calcula el valor mensual base:
   - si encuentra un producto mensual exacto con los mismos servicios, usa ese precio;
   - si no, usa la suma mensual de los detalles activos.
4. Multiplica ese valor por los meses solicitados.
5. Verifica el saldo del cliente.
6. Desactiva solo los detalles renovados en la venta anterior.
7. Crea una nueva venta separada de tipo renovacion.
8. Crea nuevos detalles con nuevas fechas de vencimiento.
9. Descuenta el saldo del cliente.

### Respuesta exitosa `201`

```json
{
  "success": true,
  "message": "Venta renovada correctamente.",
  "data": {
    "venta_original": {
      "idven": "VEN-045"
    },
    "venta_renovada": {
      "idven": "VEN-046",
      "idcli": "CLI-001",
      "idemp": 10,
      "meses": 2,
      "total": 10.00,
      "saldo_restante": 25.00,
      "precio_mensual_base": 5.00,
      "estrategia_precio": "producto_mensual",
      "producto_base": {
        "id": 3,
        "nombre": "Netflix Premium 1 mes",
        "precio_mensual": 5.00
      }
    },
    "detalles_renovados": [
      {
        "iddet_anterior": 89,
        "iddet_nuevo": 104,
        "servicio": "Netflix",
        "usuario": "cuenta@gmail.com",
        "perfil": 2,
        "fecha_anterior": "2026-05-30",
        "fecha_nueva": "2026-07-30",
        "monto": 10.00
      }
    ]
  }
}
```

### Errores posibles

| Código | Motivo |
|--------|--------|
| `422` | Validación fallida (`idven`, `idcli`, `meses`) |
| `422` | La venta no pertenece al cliente enviado |
| `422` | La venta no tiene detalles activos para renovar |
| `422` | Uno o más `detalles` no pertenecen a la venta o no están activos |
| `422` | Saldo insuficiente |

**Saldo insuficiente `422`:**
```json
{
  "success": false,
  "message": "Saldo insuficiente para realizar la renovacion.",
  "data": {
    "saldo_actual": 3.00,
    "total_renovacion": 10.00,
    "faltante": 7.00,
    "precio_mensual_base": 5.00,
    "estrategia_precio": "producto_mensual",
    "producto_base": {
      "id": 3,
      "nombre": "Netflix Premium 1 mes",
      "precio_mensual": 5.00
    }
  }
}
```


## 2. Crear Venta

**POST** `/api/v2/ventas`  
**Content-Type:** `application/json`

### Body (JSON)

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `idcli` | string | ✅ | ID del cliente |
| `idproducto` | int | ✅ | ID del producto a vender |
| `idemp` | int | ❌ | ID del empleado (default: 10) |

```json
{
  "idcli": "CLI-001",
  "idproducto": 3,
  "idemp": 1
}
```

### Respuesta exitosa `201`

```json
{
  "success": true,
  "message": "Venta creada correctamente.",
  "data": {
    "venta": {
      "idven": "VEN-045",
      "idcli": "CLI-001",
      "idemp": 1,
      "producto_id": 3,
      "producto": "Netflix Premium 1 mes",
      "fecha": "2026-05-04 10:30:00",
      "total": 15.00,
      "saldo_restante": 35.00
    },
    "entregas": [
      {
        "iddet": "DET-089",
        "servicio": "Netflix",
        "usuario": "cuenta@gmail.com",
        "contrasena": "pass1234",
        "perfil": 2,
        "pin": "1234",
        "vence": "2026-06-03",
        "monto": 15.00
      }
    ]
  }
}
```

### Errores posibles

| Código | Motivo |
|--------|--------|
| `422` | Saldo insuficiente, producto inactivo, sin cuentas disponibles |
| `422` | Validación fallida (idcli o idproducto inválido) |

**Saldo insuficiente `422`:**
```json
{
  "success": false,
  "message": "Saldo insuficiente para realizar la compra.",
  "data": {
    "saldo_actual": 5.00,
    "precio_producto": 15.00,
    "faltante": 10.00
  }
}
```

### Ejemplo Android (Retrofit)

```java
@POST("api/v2/ventas")
Call<CrearVentaResponse> crearVenta(
    @Header("X-API-Key") String apiKey,
    @Body CrearVentaRequest body
);

// CrearVentaRequest.java
public class CrearVentaRequest {
    @SerializedName("idcli")
    public String idcli;

    @SerializedName("idproducto")
    public int idproducto;

    @SerializedName("idemp")
    public Integer idemp; // nullable
}
```

---

## 3. Ver Detalle de Venta

**GET** `/api/v2/ventas/{idven}`

### Respuesta exitosa `200`

```json
{
  "success": true,
  "data": {
    "venta": {
      "idven": "VEN-045",
      "fecha": "2026-05-04 10:30:00",
      "total": 15.00
    },
    "cliente": {
      "idcli": "CLI-001",
      "nombre": "Juan Pérez",
      "email": "juan@email.com",
      "telefono": "04141234567"
    },
    "empleado": {
      "idemp": 1,
      "nombre": "María García"
    },
    "detalles": [
      {
        "iddet": "DET-089",
        "cuenta": "CUE-010",
        "usuario": "cuenta@gmail.com",
        "perfil": 2,
        "servicio": "Netflix",
        "monto": 15.00,
        "descripcion": "Venta automatizada por API v2 (android)",
        "fecha_vencimiento": "2026-06-03",
        "activo": true
      }
    ],
    "transaccion": null
  }
}
```

### Ejemplo Android (Retrofit)

```java
@GET("api/v2/ventas/{idven}")
Call<DetalleVentaResponse> detalleVenta(
    @Header("X-API-Key") String apiKey,
    @Path("idven") String idven
);
```

---

## 4. Editar Venta

**PUT** `/api/v2/ventas/{idven}`  
**Content-Type:** `application/json`

Todos los campos son opcionales — solo envías lo que quieres cambiar.

### Body (JSON)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `idemp` | int | Nuevo empleado asignado |
| `idcli` | string | Nuevo cliente |
| `fechaven` | date | Nueva fecha de venta `YYYY-MM-DD` |
| `detalles` | array | Lista de detalles a modificar |
| `detalles[].iddet` | string | ✅ ID del detalle (requerido si se envía detalles) |
| `detalles[].montodet` | float | Nuevo monto |
| `detalles[].fechavendet` | date | Nueva fecha de vencimiento |
| `detalles[].descripciondet` | string | Nueva descripción |
| `detalles[].activodet` | boolean | Activar/desactivar detalle |

```json
{
  "idemp": 2,
  "detalles": [
    {
      "iddet": "DET-089",
      "fechavendet": "2026-07-03",
      "activodet": true
    }
  ]
}
```

### Respuesta exitosa `200`

```json
{
  "success": true,
  "message": "Venta actualizada exitosamente",
  "data": {
    "venta": {
      "idven": "VEN-045",
      "idcli": "CLI-001",
      "cliente": "Juan Pérez",
      "idemp": 2,
      "empleado": "Carlos López",
      "fecha": "2026-05-04 10:30:00",
      "total": 15.00
    },
    "detalles_actualizados": [
      {
        "iddet": "DET-089",
        "monto": 15.00,
        "fecha_vencimiento": "2026-07-03",
        "activo": true
      }
    ]
  }
}
```

### Ejemplo Android (Retrofit)

```java
@PUT("api/v2/ventas/{idven}")
Call<EditarVentaResponse> editarVenta(
    @Header("X-API-Key") String apiKey,
    @Path("idven") String idven,
    @Body EditarVentaRequest body
);
```

---

## 5. Eliminar Venta

**DELETE** `/api/v2/ventas/{idven}`

> ⚠️ Si la venta tiene **detalles activos**, debes enviar `?force=true` para confirmar la eliminación.

### Query params

| Param | Tipo | Descripción |
|-------|------|-------------|
| `force` | boolean | `true` para eliminar aunque tenga detalles activos |

### Respuesta exitosa `200`

```json
{
  "success": true,
  "message": "Venta eliminada correctamente.",
  "data": {
    "idven": "VEN-045"
  }
}
```

**Sin `force=true` y con detalles activos `400`:**
```json
{
  "success": false,
  "message": "La venta tiene detalles activos. Envia force=true para eliminarla.",
  "data": {
    "detalles_activos": 2
  }
}
```

### Ejemplo Android (Retrofit)

```java
@DELETE("api/v2/ventas/{idven}")
Call<BaseResponse> eliminarVenta(
    @Header("X-API-Key") String apiKey,
    @Path("idven") String idven,
    @Query("force") boolean force
);
```

---

## 6. Estadísticas de Ventas

**GET** `/api/v2/tech-ventas/estadisticas`

### Query params

| Param | Valores | Descripción |
|-------|---------|-------------|
| `periodo` | `hoy`, `semana`, `mes`, `anio` | Período de las estadísticas (default: `mes`) |

### Respuesta exitosa `200`

```json
{
  "success": true,
  "periodo": "mes",
  "fecha_inicio": "2026-05-01",
  "data": {
    "total_ventas": 45,
    "monto_total": 675.00,
    "promedio_venta": 15.00
  }
}
```

### Ejemplo Android (Retrofit)

```java
@GET("api/v2/tech-ventas/estadisticas")
Call<EstadisticasResponse> estadisticas(
    @Header("X-API-Key") String apiKey,
    @Query("periodo") String periodo // "hoy", "semana", "mes", "anio"
);
```

---

## Respuestas de error comunes

| Código | Descripción |
|--------|-------------|
| `401` | API Key no enviada |
| `403` | API Key inválida, expirada o IP no autorizada |
| `404` | Recurso no encontrado |
| `422` | Error de validación / regla de negocio |
| `500` | Error interno del servidor |

**Sin API Key `401`:**
```json
{
  "success": false,
  "error": "API Key no proporcionada",
  "message": "Incluye el header \"X-API-Key: tu_api_key\" o el parámetro ?api_key=tu_api_key"
}
```

---

## Configuración Retrofit completa (Android)

```java
// ApiClient.java
public class ApiClient {
    private static final String BASE_URL = "http://TU_DOMINIO/";
    private static final String API_KEY = "sk_FWmIdIrBXqYqsVwAGnX5gLU4FkyHp6WxSzNA6MpegIRa7e7lbYZVgkuNncGd";
    private static Retrofit retrofit;

    public static Retrofit getClient() {
        if (retrofit == null) {
            OkHttpClient client = new OkHttpClient.Builder()
                .addInterceptor(chain -> {
                    Request original = chain.request();
                    Request request = original.newBuilder()
                        .header("X-API-Key", API_KEY)
                        .header("Accept", "application/json")
                        .header("Content-Type", "application/json")
                        .method(original.method(), original.body())
                        .build();
                    return chain.proceed(request);
                })
                .build();

            retrofit = new Retrofit.Builder()
                .baseUrl(BASE_URL)
                .client(client)
                .addConverterFactory(GsonConverterFactory.create())
                .build();
        }
        return retrofit;
    }

    public static VentasApi getVentasApi() {
        return getClient().create(VentasApi.class);
    }
}
```

```java
// VentasApi.java — Interface de todos los endpoints
public interface VentasApi {

    @GET("api/v2/ventas")
    Call<VentasListResponse> listar(
        @Query("page") int page,
        @Query("per_page") int perPage,
        @Query("search") String search,
        @Query("fecha_inicio") String fechaInicio,
        @Query("fecha_fin") String fechaFin
    );

    @POST("api/v2/ventas")
    Call<CrearVentaResponse> crear(@Body CrearVentaRequest body);

    @GET("api/v2/ventas/{idven}")
    Call<DetalleVentaResponse> detalle(@Path("idven") String idven);

    @PUT("api/v2/ventas/{idven}")
    Call<EditarVentaResponse> editar(
        @Path("idven") String idven,
        @Body EditarVentaRequest body
    );

    @DELETE("api/v2/ventas/{idven}")
    Call<BaseResponse> eliminar(
        @Path("idven") String idven,
        @Query("force") boolean force
    );

    @GET("api/v2/tech-ventas/estadisticas")
    Call<EstadisticasResponse> estadisticas(@Query("periodo") String periodo);
}
```

> **Nota:** Con el interceptor en `ApiClient`, el header `X-API-Key` se agrega automáticamente a todas las peticiones — no necesitas pasarlo en cada método de la interfaz.

---

## Documentación interactiva (Swagger)

Disponible en: `http://TU_DOMINIO/docs/api`
