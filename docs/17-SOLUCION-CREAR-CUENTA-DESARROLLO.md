# Solución: Crear Cuenta Funciona en Local y Desarrollo

## 📋 Problema Identificado

La funcionalidad de crear cuentas funcionaba en **local** pero fallaba en **desarrollo/producción**.

## 🔍 Causas Principales

### 1. **Headers Incorrectos en FormData**
- ❌ **Problema**: Se intentaba establecer `Content-Type` manualmente con FormData
- ✅ **Solución**: Eliminar header `Content-Type` para que el navegador lo establezca automáticamente con el boundary correcto

### 2. **Detección de Peticiones AJAX Inconsistente**
- ❌ **Problema**: Solo se verificaba `$request->ajax()` o `Accept: application/json`
- ✅ **Solución**: Múltiples verificaciones incluyendo:
  - `$request->expectsJson()`
  - `$request->ajax()`
  - `$request->wantsJson()`
  - `$request->header('X-Requested-With') === 'XMLHttpRequest'`

### 3. **Manejo de Errores Insuficiente**
- ❌ **Problema**: No había diferenciación entre errores de validación y errores generales
- ✅ **Solución**: Separación de catches para `ValidationException` y `Exception` general

### 4. **Falta de Logging**
- ❌ **Problema**: No había forma de debugear qué estaba fallando en desarrollo
- ✅ **Solución**: Logging detallado en cada paso del proceso

## 🛠️ Cambios Implementados

### Frontend: `resources/views/inventory/cuentas/index.blade.php`

```javascript
async function submitCreate(event) {
    event.preventDefault();
    console.log('📤 Enviando formulario de crear cuenta...');

    const form = event.target;
    const formData = new FormData(form);
    
    // Log de datos que se enviarán
    console.log('📋 Datos del formulario:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }

    try {
        const response = await fetch('{{ route("cuentas.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' // ✅ Identifica como AJAX
            },
            body: formData,
            credentials: 'same-origin' // ✅ Incluye cookies de sesión
        });

        // ✅ Verificar tipo de respuesta
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('La respuesta del servidor no es JSON válido');
        }

        const data = await response.json();

        if (response.ok && data.success) {
            showTemporaryAlert(data.message || 'Cuenta creada exitosamente', 'success');
            closeCreateModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            // ✅ Manejo de errores de validación
            if (data.errors) {
                const errorList = Object.values(data.errors).flat().join('\n');
                showTemporaryAlert(errorList, 'danger');
            } else {
                showTemporaryAlert(data.message, 'danger');
            }
        }
    } catch (error) {
        console.error('❌ Error:', error);
        showTemporaryAlert('Error de conexión. Por favor, intenta nuevamente.\n' + error.message, 'danger');
    }
}
```

### Backend: `app/Http/Controllers/CuentaController.php`

```php
public function store(Request $request)
{
    // ✅ Logging detallado para debugging
    Log::info('=== INICIO STORE CUENTA ===');
    Log::info('Método: ' . $request->method());
    Log::info('Content-Type: ' . $request->header('Content-Type'));
    Log::info('Accept: ' . $request->header('Accept'));
    Log::info('Datos recibidos: ' . json_encode($request->all()));
    
    if (!Gate::allows('cuentas.store')) {
        Log::warning('Permiso denegado para crear cuentas');
        
        // ✅ Respuesta JSON para peticiones AJAX
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para crear cuentas.'
            ], 403);
        }
        abort(403, 'No tienes permiso para crear cuentas.');
    }
    
    try {
        $request->merge(['idcue' => strtoupper($request->idcue)]);
        
        Log::info('ID cuenta (mayúsculas): ' . $request->idcue);
        
        $validated = $request->validate([
            'idcue' => 'required|string|max:20|unique:cuentas,idcue',
            'idval' => 'required|exists:valores,idval',
            'fechavencue' => 'required|date',
            'usuariocue' => 'required|string|max:50|unique:cuentas,usuariocue',
            'contrasenacue' => 'required|string|max:50',
            'caidacue' => 'required|boolean',
        ]);
        
        Log::info('Validación exitosa');
        
        $cuenta = Cuenta::create($validated);
        
        // ... resto del código ...
        
        Log::info('Cuenta creada exitosamente: ' . $cuenta->idcue);
        Log::info('=== FIN STORE CUENTA (ÉXITO) ===');
        
        // ✅ Múltiples verificaciones para AJAX
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || 
            $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Cuenta creada con éxito.',
                'cuenta' => $cuenta
            ], 200);
        }

        return redirect()->route('cuentas')->with('success', 'Cuenta creada con éxito.');
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        // ✅ Manejo específico de errores de validación
        Log::error('Error de validación: ' . json_encode($e->errors()));
        Log::error('=== FIN STORE CUENTA (ERROR VALIDACIÓN) ===');
        
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || 
            $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación en los datos.',
                'errors' => $e->errors()
            ], 422);
        }
        return redirect()->back()->withInput()->withErrors($e->errors());
        
    } catch (\Exception $e) {
        // ✅ Manejo de errores generales
        Log::error('Error general al crear cuenta: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        Log::error('=== FIN STORE CUENTA (ERROR GENERAL) ===');
        
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || 
            $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => false,
                'message' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()
            ], 500);
        }
        return redirect()->back()->withInput()->withErrors(['error' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()]);
    }
}
```

## 🧪 Cómo Probar

### En Local:
```bash
# Limpiar cache de configuración
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Verificar los logs
tail -f storage/logs/laravel.log
```

### En Desarrollo:
```bash
# Verificar permisos de escritura en logs
chmod -R 775 storage/logs

# Limpiar cache
php artisan optimize:clear

# Verificar logs en tiempo real
tail -f storage/logs/laravel.log
```

### Desde el Navegador:
1. Abrir **DevTools** (F12)
2. Ir a la pestaña **Console**
3. Ir a la pestaña **Network**
4. Intentar crear una cuenta
5. Verificar:
   - ✅ Los logs en console muestran los datos enviados
   - ✅ La petición aparece en Network con status 200
   - ✅ La respuesta es JSON válido

## 🔧 Troubleshooting

### Si sigue fallando en desarrollo:

#### 1. Verificar CSRF Token
```javascript
// En la consola del navegador
console.log('{{ csrf_token() }}');
```

#### 2. Verificar Sesión
```php
// En el controlador, agregar temporalmente:
Log::info('Session ID: ' . session()->getId());
Log::info('CSRF Token: ' . csrf_token());
```

#### 3. Verificar Middleware
```bash
php artisan route:list --name=cuentas.store
```

#### 4. Revisar Logs
```bash
# Ver últimas 50 líneas
tail -n 50 storage/logs/laravel.log

# Ver logs en tiempo real con filtro
tail -f storage/logs/laravel.log | grep "STORE CUENTA"
```

#### 5. Verificar Variables de Entorno
```bash
# Verificar APP_URL
php artisan tinker
>>> config('app.url')

# Verificar SESSION_DRIVER
>>> config('session.driver')
```

## ✅ Checklist de Verificación

- [x] Headers AJAX correctos (`X-Requested-With: XMLHttpRequest`)
- [x] No establecer `Content-Type` manualmente con FormData
- [x] Incluir `credentials: 'same-origin'` en fetch
- [x] Múltiples verificaciones de petición AJAX en backend
- [x] Separar manejo de errores de validación vs errores generales
- [x] Logging detallado en cada paso
- [x] Verificar respuesta es JSON antes de parsear
- [x] Mostrar errores de validación específicos al usuario
- [x] Status codes HTTP correctos (200, 422, 500)

## 📝 Notas Importantes

1. **NO** eliminar el header `X-Requested-With: XMLHttpRequest` - es crucial para identificar peticiones AJAX
2. **NO** establecer `Content-Type` cuando uses `FormData` - el navegador lo hace automáticamente
3. **SÍ** incluir `credentials: 'same-origin'` para que las cookies de sesión se envíen
4. **SÍ** verificar múltiples condiciones para detectar AJAX (diferentes servidores se comportan diferente)
5. Los logs están en `storage/logs/laravel.log` - revisar siempre en caso de problemas

## 🚀 Mejoras Futuras

- [ ] Agregar rate limiting específico para creación de cuentas
- [ ] Implementar notificaciones en tiempo real con WebSockets
- [ ] Agregar validación del lado del cliente antes de enviar
- [ ] Implementar retry automático en caso de fallo de red
