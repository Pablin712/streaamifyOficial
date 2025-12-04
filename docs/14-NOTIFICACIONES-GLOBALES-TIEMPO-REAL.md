# 📋 DOCUMENTACIÓN 13 - SISTEMA DE NOTIFICACIONES GLOBALES EN TIEMPO REAL

## 📅 Fecha de Implementación
**Diciembre 3, 2025**

---

## 🎯 Resumen de Cambios

Se implementó un **sistema completo de notificaciones en tiempo real** para que los empleados reciban alertas sonoras y visuales de nuevos mensajes de chat en **todas las páginas del panel de administración**, no solo en la vista de chat.

### ✨ Características Principales

- ✅ **Notificaciones globales**: Sonido y alerta visual en cualquier página del panel
- ✅ **Polling optimizado**: Verificación cada 1 segundo para respuesta casi instantánea
- ✅ **Web Audio API**: Sonido dual-tone generado programáticamente (sin archivos externos)
- ✅ **Detección inteligente**: Verifica automáticamente al volver a la pestaña
- ✅ **Burbujas visuales**: Notificaciones flotantes estilo WhatsApp con click para ir al chat
- ✅ **Auto-ocultación**: Las notificaciones desaparecen automáticamente después de 10 segundos

---

## 📦 Archivos Creados

### Nuevos Componentes Livewire

```bash
app/Livewire/Chat/NotificadorGlobal.php
resources/views/livewire/chat/notificador-global.blade.php
```

**Descripción**: Componente que monitorea mensajes no leídos y dispara eventos cuando hay nuevos mensajes.

---

## 📝 Archivos Modificados

### 1. Backend (PHP/Laravel)

#### `app/Livewire/Chat/NotificadorGlobal.php` (NUEVO)
```php
<?php

namespace App\Livewire\Chat;

use Livewire\Component;
use App\Models\Conversacion;

class NotificadorGlobal extends Component
{
    public $ultimoConteoMensajes = 0;
    public $totalNoLeidos = 0;

    public function mount() { ... }
    public function verificarNuevosMensajes() { ... }
    private function obtenerTotalNoLeidos() { ... }
}
```

**Función**: Componente Livewire que verifica cada segundo si hay mensajes nuevos y dispara eventos.

---

#### `app/Models/Mensaje.php`
**Sección modificada**: Se eliminó el método `boot()` con invalidación de caché (ya no se usa caché).

**Cambios**:
- ❌ Removido: `use Illuminate\Support\Facades\Cache;`
- ❌ Removido: Método `boot()` con `Cache::flush()`

**Razón**: Se optimizó para consultas directas sin caché, reduciendo complejidad y mejorando tiempo real.

---

### 2. Frontend (Blade/JavaScript/CSS)

#### `resources/views/livewire/chat/notificador-global.blade.php` (NUEVO)
**Contenido completo**: 130+ líneas
- Polling cada 1 segundo (`wire:poll.1s`)
- Listeners de eventos Livewire
- Generación de notificaciones visuales
- Web Audio API para sonidos
- Detección de visibilidad de pestaña

**Características clave**:
```javascript
// Polling automático
wire:poll.1s="verificarNuevosMensajes"

// Evento Livewire
Livewire.on('nuevoMensajeGlobal', (event) => { ... })

// Web Audio API - Dual tone
oscillator1.frequency.value = 800;  // Primer tono
oscillator2.frequency.value = 1000; // Segundo tono

// Detección de visibilidad
document.addEventListener('visibilitychange', () => { ... })
```

---

#### `resources/views/layouts/navigation.blade.php`
**Línea añadida**: ~53

```blade
@can('chat.ver')
    @livewire('chat.notificador-global')
@endcan
```

**Ubicación**: Después del `<body>` tag, antes del navbar.

**Función**: Carga el notificador global en todas las páginas para empleados con permiso `chat.ver`.

---

#### `public/css/chat-system.css`
**Sección añadida**: Estilos de notificaciones globales

```css
/* Notificaciones globales */
.chat-notification-bubble {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: linear-gradient(...);
    /* ... más estilos */
}

.chat-notification-icon { ... }
.chat-notification-content { ... }
.chat-notification-title { ... }
.chat-notification-message { ... }
.chat-notification-close { ... }

/* Animaciones */
@keyframes slideInUp { ... }
@keyframes pulse { ... }
```

**Líneas aproximadas**: 669-850 (revisar archivo para exactas)

---

### 3. Configuración

#### **SIN CAMBIOS EN:**
- ❌ `composer.json` - No se agregaron dependencias
- ❌ `package.json` - No se agregaron paquetes npm
- ❌ Migraciones - No hay cambios en BD
- ❌ `.env` - No requiere variables nuevas
- ❌ Configuraciones de Laravel

---

## 🚀 Pasos para Desplegar en Desarrollo

### 1️⃣ Subir Código al Repositorio

```bash
git add .
git commit -m "feat: Sistema de notificaciones globales en tiempo real para chat"
git push origin version-5
```

---

### 2️⃣ En el Servidor de Desarrollo

#### Paso A: Actualizar código
```bash
cd /ruta/al/proyecto
git pull origin version-5
```

#### Paso B: Dependencias Composer
```bash
composer install --optimize-autoloader --no-dev
```

**Nota**: No hay nuevas dependencias, pero es buena práctica ejecutarlo.

---

#### Paso C: Dependencias NPM y Assets

```bash
# Instalar/actualizar dependencias (aunque no hay cambios)
npm install

# Compilar assets para producción
npm run build
```

**⚠️ IMPORTANTE**: Ejecuta `npm run build` porque modificamos `chat-system.css` y necesitas recompilar los assets de Vite.

---

#### Paso D: Limpiar cachés de Laravel

```bash
# Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerar cachés optimizadas
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

#### Paso E: Optimización Livewire

```bash
php artisan livewire:discover
```

**Función**: Registra el nuevo componente `NotificadorGlobal`.

---

### 3️⃣ Verificación Post-Despliegue

#### ✅ Checklist de Pruebas

1. **Login como empleado** con permiso `chat.ver`
2. **Navegar a cualquier página** del panel (NO la de chat)
3. **Abrir consola del navegador** (F12 → Console)
4. **Enviar mensaje desde widget de cliente**
5. **Verificar**:
   - [ ] Aparece log en consola: `Evento recibido: [...]`
   - [ ] Suena el tono dual (beep-boop)
   - [ ] Aparece burbuja de notificación abajo a la derecha
   - [ ] El mensaje dice "Tienes X mensaje(s) nuevo(s)"
   - [ ] Click en burbuja redirige a `/chat/panel`
   - [ ] Burbuja desaparece después de 10 segundos

6. **Prueba de pestaña inactiva**:
   - [ ] Cambiar a otra pestaña del navegador
   - [ ] Enviar mensaje desde cliente
   - [ ] Volver a la pestaña original
   - [ ] Debe sonar inmediatamente si pasaron >2s

---

## 🔧 Posibles Problemas y Soluciones

### ❌ Problema: "No suena ni aparece notificación"

**Causa**: Assets no compilados o caché de navegador

**Solución**:
```bash
npm run build
php artisan view:clear
```

Luego en navegador: `Ctrl + Shift + R` (hard reload)

---

### ❌ Problema: "Livewire component not found"

**Causa**: Componente no registrado

**Solución**:
```bash
composer dump-autoload
php artisan livewire:discover
php artisan config:clear
```

---

### ❌ Problema: "Error en consola: @this is not defined"

**Causa**: Livewire no cargado correctamente

**Solución**: Verificar que en `navigation.blade.php` esté:
```blade
@livewireScripts
```

Antes del cierre de `</body>`.

---

### ❌ Problema: "Sonido no se reproduce"

**Causa**: Política del navegador (requiere interacción del usuario)

**Solución**: El usuario debe hacer **cualquier click** en la página primero. Después de eso, el sonido funcionará automáticamente.

**Alternativa**: Mostrar mensaje inicial: "Haz click en cualquier lugar para activar notificaciones sonoras".

---

## 📊 Rendimiento y Carga del Servidor

### Impacto en Base de Datos

**Consulta ejecutada cada segundo** (por empleado activo):
```sql
SELECT SUM(mensajes_no_leidos) 
FROM conversaciones 
WHERE estado != 'cerrada';
```

**Estimación de carga**:
- 1 empleado activo: 60 consultas/minuto = 3,600/hora
- 10 empleados activos: 600 consultas/minuto = 36,000/hora

**Optimización recomendada** (si hay >20 empleados concurrentes):
- Añadir índice: `CREATE INDEX idx_estado ON conversaciones(estado);`
- Implementar Redis para caché de 1-2 segundos
- Migrar a WebSockets (Laravel Reverb)

---

## 🔮 Mejoras Futuras (No Incluidas)

### Opción 1: Laravel Reverb (WebSockets nativos)
```bash
composer require laravel/reverb
php artisan reverb:install
```

**Beneficios**: Notificaciones instantáneas (0ms), sin polling, menos carga de servidor.

---

### Opción 2: Notificaciones del Sistema Operativo
```javascript
if (Notification.permission === 'granted') {
    new Notification('Nuevo mensaje', {
        body: 'Tienes 1 mensaje nuevo',
        icon: '/logo.png'
    });
}
```

**Beneficios**: Notificaciones incluso con navegador minimizado.

---

### Opción 3: Service Workers
```javascript
navigator.serviceWorker.register('/sw.js');
```

**Beneficios**: Notificaciones en segundo plano real, push notifications.

---

## 📚 Referencias Técnicas

### Tecnologías Utilizadas

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| Laravel | 11.x | Framework backend |
| Livewire | 3.6+ | Componentes reactivos |
| Web Audio API | Nativa | Generación de sonidos |
| JavaScript ES6+ | Nativo | Lógica frontend |
| CSS3 Animations | Nativo | Animaciones de burbujas |

---

### Permisos Requeridos

**Permiso Spatie**: `chat.ver`

**Verificación**:
```php
if (auth()->user()->can('chat.ver')) {
    // Cargar notificador
}
```

**Roles con acceso** (ajustar según tus seeders):
- `super-admin`
- `admin`
- `empleado` (si tiene permiso asignado)

---

## 🎨 Personalización

### Cambiar Sonido

**Archivo**: `resources/views/livewire/chat/notificador-global.blade.php`

**Líneas**: ~90-110

```javascript
// Cambiar frecuencias para diferente tono
oscillator1.frequency.value = 800;  // Cambiar a 600-1200
oscillator2.frequency.value = 1000; // Cambiar a 800-1400

// Cambiar duración
oscillator1.stop(audioContext.currentTime + 0.15); // Aumentar para más largo
```

---

### Cambiar Tiempo de Polling

**Archivo**: `resources/views/livewire/chat/notificador-global.blade.php`

**Línea**: 1

```blade
<!-- Cambiar de 1s a 3s, 5s, etc -->
<div wire:poll.3s="verificarNuevosMensajes">
```

**⚠️ Advertencia**: Menos de 1s puede causar sobrecarga en servidor.

---

### Cambiar Estilos de Burbuja

**Archivo**: `public/css/chat-system.css`

**Líneas**: 669-850

```css
.chat-notification-bubble {
    background: linear-gradient(135deg, #667eea, #764ba2); /* Cambiar colores */
    bottom: 20px; /* Cambiar posición */
    right: 20px;
}
```

---

## 📞 Soporte y Contacto

**Desarrollador**: [Tu nombre]  
**Fecha**: Diciembre 3, 2025  
**Versión**: 5.0  
**Commit**: Sistema de notificaciones globales en tiempo real  

---

## ✅ Checklist Final de Despliegue

- [ ] Código subido a repositorio
- [ ] `git pull` en servidor
- [ ] `composer install` ejecutado
- [ ] `npm install` ejecutado
- [ ] `npm run build` ejecutado (**CRÍTICO**)
- [ ] `php artisan cache:clear` ejecutado
- [ ] `php artisan view:clear` ejecutado
- [ ] `php artisan livewire:discover` ejecutado
- [ ] Prueba manual de notificación exitosa
- [ ] Prueba con múltiples pestañas
- [ ] Prueba de sonido funcionando
- [ ] Verificado en consola del navegador (sin errores)
- [ ] Verificado click en burbuja redirige correctamente

---

## 🎉 Fin de Documentación

**Estado**: ✅ LISTO PARA PRODUCCIÓN

**Próximos pasos recomendados**:
1. Desplegar en desarrollo
2. Pruebas de carga con 10+ usuarios concurrentes
3. Monitorear rendimiento de base de datos
4. Evaluar migración a WebSockets si escala >50 usuarios
