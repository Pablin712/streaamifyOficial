# Android Studio: Auth + CRUD Clientes (API Streamify)

Fecha: 2026-05-04

## 1) Base URL

Si pruebas desde emulador Android:
- `http://10.0.2.2:8000/api/`

Si pruebas desde dispositivo fisico en la misma red:
- `http://IP_LOCAL_DE_TU_PC:8000/api/`

## 2) Endpoints de autenticacion (v2)

### Registro cliente
- Metodo: `POST`
- URL: `/v2/auth/create-customer`
- Body JSON:

```json
{
  "name": "Juan Perez",
  "email": "juan@test.com",
  "password": "Secret123$",
  "confirm": "Secret123$",
  "telefono": "51987654321",
  "codigo_referidor": null
}
```

### Validar credenciales
- Metodo: `POST`
- URL: `/v2/auth/validate-credentials`
- Body JSON:

```json
{
  "email": "juan@test.com",
  "password": "Secret123$"
}
```

## 3) CRUD completo de clientes (v2)

Estos endpoints requieren API Key.

Header obligatorio:
- `X-API-Key: TU_API_KEY`

### Listar clientes
- Metodo: `GET`
- URL: `/v2/clientes?per_page=15&search=juan`

### Ver cliente
- Metodo: `GET`
- URL: `/v2/clientes/{id}`

### Crear cliente
- Metodo: `POST`
- URL: `/v2/clientes`
- Body JSON:

```json
{
  "nombrecli": "Juan Perez",
  "telefonocli": "51987654321",
  "email": "juan@test.com",
  "password": "Secret123$",
  "pais": "PE",
  "saldo": 0
}
```

### Actualizar cliente
- Metodo: `PUT` o `PATCH`
- URL: `/v2/clientes/{id}`
- Body JSON (ejemplo):

```json
{
  "nombrecli": "Juan Perez Actualizado",
  "telefonocli": "51911111111",
  "pais": "PE",
  "saldo": 25.5
}
```

### Eliminar cliente
- Metodo: `DELETE`
- URL: `/v2/clientes/{id}`

### Ventas por cliente
- Metodo: `GET`
- URL: `/v2/clientes/{id}/ventas`

## 4) Ejemplo rapido Retrofit

```kotlin
interface ApiService {
    @POST("v2/auth/validate-credentials")
    suspend fun validarCredenciales(@Body body: LoginRequest): LoginResponse

    @GET("v2/clientes")
    suspend fun listarClientes(
        @Header("X-API-Key") apiKey: String,
        @Query("per_page") perPage: Int = 15,
        @Query("search") search: String? = null
    ): ClientesResponse

    @POST("v2/clientes")
    suspend fun crearCliente(
        @Header("X-API-Key") apiKey: String,
        @Body body: CrearClienteRequest
    ): ClienteResponse

    @PUT("v2/clientes/{id}")
    suspend fun actualizarCliente(
        @Header("X-API-Key") apiKey: String,
        @Path("id") id: Int,
        @Body body: ActualizarClienteRequest
    ): ClienteResponse

    @DELETE("v2/clientes/{id}")
    suspend fun eliminarCliente(
        @Header("X-API-Key") apiKey: String,
        @Path("id") id: Int
    ): DeleteResponse
}
```

## 5) Flujo recomendado para tu tarea

1. Registrar o validar usuario con `/v2/auth/*`.
2. Guardar API Key en `BuildConfig` o variable segura (si te la dieron fija para la tarea).
3. Implementar pantallas Android:
   - Lista clientes
   - Crear cliente
   - Editar cliente
   - Eliminar cliente
4. Probar con Postman primero, luego desde app Android.

## 6) Nota importante

- `validate-credentials` valida email/password y devuelve datos del cliente.
- El control de acceso para CRUD en v2 actualmente es por `X-API-Key`.
- Si luego quieres autenticacion por token JWT/Sanctum para clientes, se puede agregar en una segunda fase.
