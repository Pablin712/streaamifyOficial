# 🔄 Implementación de Cambios en Sistema de Cuentas

## ✅ CAMBIOS IMPLEMENTADOS

### 1. ⚡ ID Automático con Trigger

**Antes:**
```php
// Se ingresaba manualmente: "NET001", "SPOTIFY-10", etc.
```

**Ahora:**
```sql
-- El trigger genera automáticamente basándose en:
-- - Tipo de cuenta (completa/individual)
-- - Servicio (desde el valor seleccionado)
-- - Siguiente número secuencial

Ejemplos:
- Completa Netflix: NETFLIX-1, NETFLIX-2, NETFLIX-3...
- Individual Netflix: IND.NETFLIX-1, IND.NETFLIX-2...
- Completa Spotify: SPOTIFY-1, SPOTIFY-2...
- Individual Spotify: IND.SPOTIFY-1, IND.SPOTIFY-2...
```

**Archivos modificados:**
- ✅ `database/migrations/2026_01_24_000001_add_tipo_cuenta_and_trigger.php` - Migración con trigger
- ✅ `database/migrations/2026_01_24_add_tipo_cuenta_trigger.sql` - SQL standalone

---

### 2. 📝 Formulario Actualizado

**Antes:**
- Campo manual: "ID de Cuenta" (texto libre)

**Ahora:**
- Selector: "Tipo de Cuenta" (completa/individual)
- Previsualización: Muestra el formato que se generará

**Archivo modificado:**
- ✅ `resources/views/inventory/cuentas/modals/create.blade.php`

**Interfaz:**
```
┌─────────────────────────────────────────┐
│ Tipo de Cuenta *                        │
│ [Completa ▼]                            │
│ El ID se generará automáticamente       │
├─────────────────────────────────────────┤
│ Servicio/Valor *                        │
│ [NETFLIX - Proveedor A (12m) ▼]        │
├─────────────────────────────────────────┤
│ ℹ️ ID a generar: NETFLIX-[AUTO]        │
└─────────────────────────────────────────┘
```

---

### 3. 🎯 Modelos Especializados

Se crearon 8 modelos que heredan de `Cuenta`, cada uno con lógica específica:

#### **Spotify** (Especial - Maneja perfiles diferentes)
```php
$spotify = Spotify::find('SPOTIFY-1');

// Perfil 1 (Owner/Admin)
$owner = $spotify->perfil1;
// ['usuario' => 'admin@spotify.com', 'contrasena' => 'pass123', 'tipo' => 'owner']

// Perfil 2-6 (Invitados - formato "usuario|contraseña" en pinper)
$invitado = $spotify->getPerfilInvitado(2);
// ['usuario' => 'user2@mail.com', 'contrasena' => 'abc456', 'tipo' => 'invitado']

// Obtener todos los perfiles
$perfiles = $spotify->getTodosLosPerfiles();

// Configurar un perfil invitado
$spotify->configurarPerfilInvitado(3, 'nuevo@mail.com', 'password');

// Mensaje de entrega formateado
$mensaje = $spotify->getMensajeEntrega(2);
```

**Archivo:**
- ✅ `app/Models/Spotify.php`

#### **Netflix**
```php
$netflix = Netflix::find('NETFLIX-1');

// Obtener perfiles con PINs
$perfiles = $netflix->getPerfilesConPin();
// [
//   ['numero' => 1, 'pin' => '1000', 'disponible' => false],
//   ['numero' => 2, 'pin' => '5555', 'disponible' => true],
// ]

// Scopes
$completas = Netflix::completas()->get();
$individuales = Netflix::individuales()->get();
```

**Archivo:**
- ✅ `app/Models/Netflix.php`

#### **Disney** (Premium/Standard)
```php
$disney = Disney::find('DISNEYP-1');

// Verificar tipo
if ($disney->isPremium()) {
    // 7 perfiles
} else {
    // 5 perfiles (Standard)
}

// Scopes
$premium = Disney::premium()->get();
$standard = Disney::standard()->get();
```

**Archivo:**
- ✅ `app/Models/Disney.php`

#### **Max, Prime, Paramount, Crunchyroll, Magis**

Todos con scopes básicos:
```php
$cuentas = Max::completas()->get();
$individuales = Prime::individuales()->get();
```

**Archivos:**
- ✅ `app/Models/Max.php`
- ✅ `app/Models/Prime.php`
- ✅ `app/Models/Paramount.php`
- ✅ `app/Models/Crunchyroll.php`
- ✅ `app/Models/Magis.php`

---

## 🔧 INTEGRACIÓN CON TRIGGER DE PERFILES

**El trigger actual `insertar_perfiles` ya funciona correctamente** porque usa `LIKE`:

```sql
-- Ya existente en triggers.sql
IF NEW.idcue LIKE 'NETFLIX%' THEN
    -- Crea 5 perfiles
ELSEIF NEW.idcue LIKE 'SPOTIFY%' THEN
    -- Crea 6 perfiles (owner + 5 invitados)
ELSEIF NEW.idcue LIKE 'IND%' THEN
    -- Crea 1 perfil solo
...
```

**Funcionará con:**
- `NETFLIX-1`, `NETFLIX-2` ✅
- `IND.NETFLIX-1`, `IND.NETFLIX-2` ✅
- `SPOTIFY-1`, `IND.SPOTIFY-1` ✅

---

## 🚀 CÓMO USAR

### Crear cuenta nueva (Formulario Web)

1. Selecciona **Tipo**: Completa o Individual
2. Selecciona **Servicio/Valor**
3. Verás previsualización: `NETFLIX-[AUTO]` o `IND.NETFLIX-[AUTO]`
4. Llena usuario, contraseña, fecha
5. Al guardar, el trigger genera automáticamente el ID

### Usar Modelos Especializados (Código)

```php
// Crear cuenta de Spotify
$cuenta = new Spotify();
$cuenta->idval = 'SPOTIFY-PREMIUM-12M';
$cuenta->tipo_cuenta = 'completa';
$cuenta->usuariocue = 'admin@spotify.com';
$cuenta->contrasenacue = 'password123';
$cuenta->fechavencue = now()->addMonths(12);
$cuenta->save();
// idcue se genera automáticamente: SPOTIFY-1

// Configurar perfiles invitados
$cuenta->configurarPerfilInvitado(2, 'user2@mail.com', 'pass456');
$cuenta->configurarPerfilInvitado(3, 'user3@mail.com', 'pass789');

// Obtener datos para entregar a cliente
$perfilOwner = $cuenta->perfil1;
$perfilInvitado = $cuenta->getPerfilInvitado(2);

// Mensaje formateado
echo $cuenta->getMensajeEntrega(2);
```

```php
// Trabajar con Netflix
$netflix = Netflix::completas()
    ->where('activocue', true)
    ->with('perfiles')
    ->first();

$perfiles = $netflix->getPerfilesConPin();
foreach ($perfiles as $perfil) {
    if ($perfil['disponible']) {
        echo "Perfil {$perfil['numero']} disponible - PIN: {$perfil['pin']}";
    }
}
```

```php
// Filtrar Disney Premium
$disneyPremium = Disney::premium()->get();
$disneyStandard = Disney::standard()->get();
```

---

## 📋 PASOS PARA APLICAR EN PRODUCCIÓN

### 1. Ejecutar Migración
```bash
php artisan migrate
```

Esto hará:
- ✅ Agrega columna `tipo_cuenta` a tabla `cuentas`
- ✅ Actualiza cuentas existentes que empiezan con "IND"
- ✅ Crea trigger `trg_generar_idcue`

### 2. Verificar Trigger en BD
```sql
SHOW TRIGGERS LIKE 'cuentas';
-- Debes ver: trg_generar_idcue (BEFORE INSERT)
```

### 3. Probar Creación Manual
```sql
-- Probar trigger manualmente
INSERT INTO cuentas (idval, tipo_cuenta, usuariocue, contrasenacue, fechavencue, activocue)
VALUES ('SPOTIFY-PREMIUM-12M', 'completa', 'test@spotify.com', 'pass123', '2027-01-24', 1);

-- Verificar que se generó correctamente
SELECT idcue, tipo_cuenta FROM cuentas ORDER BY created_at DESC LIMIT 1;
-- Resultado esperado: SPOTIFY-1 (o el siguiente número)
```

### 4. Probar desde Formulario Web
- Ir a Cuentas → Crear Nueva
- Seleccionar tipo y servicio
- Verificar previsualización
- Guardar y verificar ID generado

---

## 🔍 VENTAJAS DEL SISTEMA

### ✅ **Consistencia**
- IDs siempre con el mismo formato
- No más errores de tipeo
- Secuencia automática sin duplicados

### ✅ **Eficiencia**
- No pensar en qué ID usar
- Sistema calcula siguiente número
- Trigger es instantáneo

### ✅ **Organización**
```
Antes (manual):
- NET001, Netflix1, NETFLIX-A, netfli-1 ❌

Ahora (automático):
- NETFLIX-1, NETFLIX-2, NETFLIX-3 ✅
- IND.NETFLIX-1, IND.NETFLIX-2 ✅
```

### ✅ **Manejo Especializado**
- Cada servicio con su lógica
- Spotify: Perfiles owner vs invitados
- Disney: Premium (7) vs Standard (5)
- Código más limpio y mantenible

---

## 🛠️ COMPATIBILIDAD

### ✅ **Con código existente**
- Modelo `Cuenta` base sigue funcionando
- Se agregó campo `tipo_cuenta` con default
- Trigger solo actúa si idcue está vacío

### ✅ **Con triggers existentes**
- `insertar_perfiles` sigue funcionando
- Usa `LIKE` por lo que detecta nuevos formatos
- No requiere cambios

### ✅ **Con vistas y consultas**
- `ViewUsuarioActivo` sigue funcionando
- Joins por `idcue` siguen válidos
- Filtros por servicio funcionan

---

## 📊 EJEMPLOS DE CASOS ESPECIALES

### Spotify con Invitados
```php
$spotify = Spotify::find('SPOTIFY-5');

// Configurar invitaciones (guardar en pinper como "usuario|contraseña")
$spotify->configurarPerfilInvitado(2, 'juan@mail.com', 'pass1');
$spotify->configurarPerfilInvitado(3, 'maria@mail.com', 'pass2');

// Entregar a cliente
$mensajeOwner = $spotify->getMensajeEntrega(1);
// 🎵 SPOTIFY PREMIUM
// Usuario: admin@spotify.com
// Contraseña: adminpass
// Perfil: Owner (Administrador)

$mensajeInvitado = $spotify->getMensajeEntrega(2);
// 🎵 SPOTIFY PREMIUM
// Usuario: juan@mail.com
// Contraseña: pass1
// Perfil: Invitado #2
```

### Cuenta Individual vs Completa
```php
// Individual: Un solo perfil para una persona
$individual = new Netflix();
$individual->tipo_cuenta = 'individual';
$individual->save();
// ID generado: IND.NETFLIX-1

// Completa: Múltiples perfiles para compartir
$completa = new Netflix();
$completa->tipo_cuenta = 'completa';
$completa->save();
// ID generado: NETFLIX-1
```

---

## 🎯 PRÓXIMOS PASOS

1. ✅ Probar migración en entorno local
2. ⏳ Verificar que trigger funciona correctamente
3. ⏳ Probar creación de cuentas desde formulario
4. ⏳ Validar que perfiles se crean correctamente
5. ⏳ Probar modelos especializados (especialmente Spotify)
6. ⏳ Aplicar en producción
7. ⏳ Migrar cuentas antiguas si es necesario

---

## ⚠️ IMPORTANTE

- **Backup de BD** antes de ejecutar migración
- **Probar en desarrollo** primero
- El trigger es **BEFORE INSERT**, no afecta cuentas existentes
- Si necesitas deshabilitar el trigger: `DROP TRIGGER trg_generar_idcue`
- Puedes seguir creando cuentas con `idcue` manual si es necesario (compatibilidad)

---

**Última actualización:** Enero 24, 2026  
**Versión:** 1.0  
**Estado:** ✅ Implementado y listo para probar
