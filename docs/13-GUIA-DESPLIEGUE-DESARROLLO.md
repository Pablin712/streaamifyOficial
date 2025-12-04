# 🚀 GUÍA DE DESPLIEGUE A DESARROLLO

**Fecha**: Diciembre 3, 2025  
**Branch**: version-5  
**Módulos incluidos**: API REST + Sistema de Mensajería/Chat Completo

---

## 📦 ARCHIVOS A SUBIR AL SERVIDOR

### 1️⃣ ARCHIVOS NUEVOS (Untracked - Copiar completos)

#### 📁 Documentación
```
DOCUMENTACION_13_NOTIFICACIONES_GLOBALES.md
docs/09-GUIA-API-REST.md
docs/10-API-EMPLEADOS-AUTOMATIZACION-IA.md
docs/11-MODULO-MENSAJERIA-CHAT.md
docs/12-CHAT-FRONTEND-WIDGET.md
```

#### 📁 Backend - Controllers
```
app/Http/Controllers/Api/                    (carpeta completa con todos los archivos)
├── ApiKeyController.php
├── AsistenciaController.php
├── ClienteController.php
├── EmpleadoController.php
├── PerfilController.php
├── ProductoController.php
├── ProveedorController.php
├── RolController.php
├── ServicioController.php
├── TareaController.php
├── UserController.php
└── VentaController.php
```

#### 📁 Backend - Middleware
```
app/Http/Middleware/AuthenticateApiKey.php
```

#### 📁 Backend - Livewire Components
```
app/Livewire/Chat/                           (carpeta completa)
├── NotificadorGlobal.php
└── PanelConversaciones.php
```

#### 📁 Backend - Models
```
app/Models/ApiKey.php
app/Models/Conversacion.php
app/Models/Mensaje.php
```

#### 📁 Backend - Console Commands
```
app/Console/Commands/GenerateNotificationSounds.php
```

#### 📁 Database - Migrations
```
database/migrations/2025_12_03_152323_create_personal_access_tokens_table.php
database/migrations/2025_12_03_153623_create_api_keys_table.php
database/migrations/2025_12_03_185657_create_conversaciones_table.php
database/migrations/2025_12_03_185703_create_mensajes_table.php
database/migrations/2025_12_03_185709_create_empleados_online_table.php
```

#### 📁 Database - Seeders
```
database/seeders/ApiKeySeeder.php
database/seeders/ChatPermisosSeeder.php
```

#### 📁 Frontend - JavaScript
```
resources/js/chat-widget.js
resources/js/components/                     (carpeta completa)
├── ChatWidget.vue
└── (otros componentes Vue si existen)
```

#### 📁 Frontend - Views
```
resources/views/chat/                        (carpeta completa)
├── index.blade.php
└── panel.blade.php

resources/views/livewire/chat/               (carpeta completa)
├── notificador-global.blade.php
└── panel-conversaciones.blade.php
```

#### 📁 Public - Assets
```
public/css/chat-system.css
public/sounds/                               (carpeta completa si existe)
```

#### 📁 Config
```
config/sanctum.php
```

---

### 2️⃣ ARCHIVOS MODIFICADOS (Modified - Reemplazar)

#### 📁 Backend
```
bootstrap/app.php
config/auth.php
routes/api.php
routes/web.php
```

#### 📁 Frontend
```
resources/views/layouts/cliente.blade.php
resources/views/layouts/navigation.blade.php
resources/views/partials/sidebar.blade.php
resources/js/app.js
public/js/scripts2.js
```

#### 📁 Configuración
```
composer.json
composer.lock
package.json
package-lock.json
vite.config.js
```

---

## 📋 LISTA COMPLETA DE ARCHIVOS (Para copiar con SFTP/SCP)

### Copiar desde local a servidor:

```bash
# === ARCHIVOS NUEVOS ===

# Documentación
DOCUMENTACION_13_NOTIFICACIONES_GLOBALES.md
docs/09-GUIA-API-REST.md
docs/10-API-EMPLEADOS-AUTOMATIZACION-IA.md
docs/11-MODULO-MENSAJERIA-CHAT.md
docs/12-CHAT-FRONTEND-WIDGET.md

# Backend - Controllers
app/Http/Controllers/Api/ApiKeyController.php
app/Http/Controllers/Api/AsistenciaController.php
app/Http/Controllers/Api/ClienteController.php
app/Http/Controllers/Api/EmpleadoController.php
app/Http/Controllers/Api/PerfilController.php
app/Http/Controllers/Api/ProductoController.php
app/Http/Controllers/Api/ProveedorController.php
app/Http/Controllers/Api/RolController.php
app/Http/Controllers/Api/ServicioController.php
app/Http/Controllers/Api/TareaController.php
app/Http/Controllers/Api/UserController.php
app/Http/Controllers/Api/VentaController.php

# Backend - Middleware
app/Http/Middleware/AuthenticateApiKey.php

# Backend - Livewire
app/Livewire/Chat/NotificadorGlobal.php
app/Livewire/Chat/PanelConversaciones.php

# Backend - Models
app/Models/ApiKey.php
app/Models/Conversacion.php
app/Models/Mensaje.php

# Backend - Commands
app/Console/Commands/GenerateNotificationSounds.php

# Database
database/migrations/2025_12_03_152323_create_personal_access_tokens_table.php
database/migrations/2025_12_03_153623_create_api_keys_table.php
database/migrations/2025_12_03_185657_create_conversaciones_table.php
database/migrations/2025_12_03_185703_create_mensajes_table.php
database/migrations/2025_12_03_185709_create_empleados_online_table.php
database/seeders/ApiKeySeeder.php
database/seeders/ChatPermisosSeeder.php

# Frontend - JS
resources/js/chat-widget.js
resources/js/components/ChatWidget.vue

# Frontend - Views
resources/views/chat/index.blade.php
resources/views/chat/panel.blade.php
resources/views/livewire/chat/notificador-global.blade.php
resources/views/livewire/chat/panel-conversaciones.blade.php

# Public
public/css/chat-system.css

# Config
config/sanctum.php

# === ARCHIVOS MODIFICADOS ===

bootstrap/app.php
config/auth.php
routes/api.php
routes/web.php
resources/views/layouts/cliente.blade.php
resources/views/layouts/navigation.blade.php
resources/views/partials/sidebar.blade.php
resources/js/app.js
public/js/scripts2.js
composer.json
composer.lock
package.json
package-lock.json
vite.config.js
```

---

## 🔧 COMANDOS A EJECUTAR EN SSH (ORDEN IMPORTANTE)

### Paso 1: Conectar al servidor
```bash
ssh usuario@tu-servidor.com
cd /ruta/a/tu/proyecto
```

---

### Paso 2: Hacer backup (IMPORTANTE)
```bash
# Backup de la base de datos
php artisan db:backup  # (si tienes este comando)
# O manualmente:
mysqldump -u usuario -p nombre_base_datos > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup de archivos críticos
cp -r app/Http/Controllers app/Http/Controllers_backup_$(date +%Y%m%d)
cp -r resources/views resources/views_backup_$(date +%Y%m%d)
```

---

### Paso 3: Subir archivos
```bash
# Opción A: Usando SCP desde tu máquina local
# (Ejecutar en tu terminal local, no en SSH)

# Subir carpetas completas
scp -r app/Http/Controllers/Api usuario@servidor:/ruta/proyecto/app/Http/Controllers/
scp -r app/Livewire/Chat usuario@servidor:/ruta/proyecto/app/Livewire/
scp -r resources/views/chat usuario@servidor:/ruta/proyecto/resources/views/
scp -r resources/views/livewire/chat usuario@servidor:/ruta/proyecto/resources/views/livewire/
scp -r database/migrations usuario@servidor:/ruta/proyecto/database/

# Subir archivos individuales modificados
scp composer.json usuario@servidor:/ruta/proyecto/
scp composer.lock usuario@servidor:/ruta/proyecto/
scp package.json usuario@servidor:/ruta/proyecto/
scp package-lock.json usuario@servidor:/ruta/proyecto/
scp routes/api.php usuario@servidor:/ruta/proyecto/routes/
scp routes/web.php usuario@servidor:/ruta/proyecto/routes/
scp config/auth.php usuario@servidor:/ruta/proyecto/config/
scp bootstrap/app.php usuario@servidor:/ruta/proyecto/bootstrap/
scp vite.config.js usuario@servidor:/ruta/proyecto/
# ... etc

# Opción B: Usando SFTP (FileZilla, WinSCP, etc.)
# Sube los archivos manualmente según la lista de arriba
```

---

### Paso 4: Instalar dependencias de Composer
```bash
# Una vez en SSH
cd /ruta/a/tu/proyecto

# Instalar/actualizar dependencias
composer install --optimize-autoloader --no-dev

# Si hay errores, probar:
composer update --no-dev
composer dump-autoload
```

---

### Paso 5: Instalar dependencias de NPM
```bash
# Instalar node_modules
npm install

# Si hay errores de versión:
npm install --legacy-peer-deps
```

---

### Paso 6: Compilar assets (CRÍTICO)
```bash
# Compilar para producción
npm run build

# Verificar que se crearon los archivos en public/build/
ls -la public/build/assets/
```

---

### Paso 7: Ejecutar migraciones
```bash
# Ver qué migraciones faltan
php artisan migrate:status

# Ejecutar migraciones nuevas
php artisan migrate

# Si necesitas forzar (ten cuidado):
# php artisan migrate --force
```

---

### Paso 8: Ejecutar seeders (si es necesario)
```bash
# Solo si necesitas los permisos y API keys

# Seeders específicos
php artisan db:seed --class=ChatPermisosSeeder
php artisan db:seed --class=ApiKeySeeder

# O todos los seeders
# php artisan db:seed
```

---

### Paso 9: Limpiar y optimizar cachés
```bash
# Limpiar todas las cachés
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Regenerar cachés optimizadas
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimizar autoload
composer dump-autoload -o
```

---

### Paso 10: Registrar componentes Livewire
```bash
php artisan livewire:discover
```

---

### Paso 11: Permisos de archivos (IMPORTANTE)
```bash
# Dar permisos a storage y bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Cambiar propietario (ajusta según tu servidor)
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache

# O si usas otro usuario:
# chown -R tu_usuario:www-data storage
# chown -R tu_usuario:www-data bootstrap/cache
```

---

### Paso 12: Verificar .env
```bash
# Editar .env si es necesario
nano .env

# Verificar estas variables:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Verificar conexión a base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tu_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Sanctum (para API)
SANCTUM_STATEFUL_DOMAINS=tu-dominio.com,www.tu-dominio.com

# Guardar y salir (Ctrl+O, Enter, Ctrl+X)
```

---

### Paso 13: Reiniciar servicios (si aplica)
```bash
# Si usas PHP-FPM
sudo systemctl restart php8.2-fpm

# Si usas Apache
sudo systemctl restart apache2

# Si usas Nginx
sudo systemctl restart nginx

# Si usas Queue workers
php artisan queue:restart
```

---

### Paso 14: Verificar logs de errores
```bash
# Ver últimas líneas del log de Laravel
tail -n 50 storage/logs/laravel.log

# Monitorear en tiempo real
tail -f storage/logs/laravel.log
```

---

## ✅ CHECKLIST DE VERIFICACIÓN POST-DESPLIEGUE

### 1. Verificar página principal
- [ ] Entrar a `https://tu-dominio.com`
- [ ] Login funciona correctamente
- [ ] No hay errores 500

### 2. Verificar API REST
- [ ] Probar endpoint: `GET /api/v1/health`
- [ ] Debería retornar: `{"status": "ok", "message": "API funcionando"}`
- [ ] Probar autenticación con API key

### 3. Verificar Chat Sistema
- [ ] Login como empleado
- [ ] Ir a `/chat/panel`
- [ ] Verificar que carga sin errores
- [ ] Abrir widget desde vista de cliente
- [ ] Enviar mensaje de prueba
- [ ] Verificar que empleado recibe mensaje

### 4. Verificar notificaciones globales
- [ ] Login como empleado
- [ ] Ir a cualquier página del panel (NO chat)
- [ ] Enviar mensaje desde widget de cliente
- [ ] Verificar:
  - [ ] Suena el tono de notificación
  - [ ] Aparece burbuja de notificación
  - [ ] Click redirige a `/chat/panel`
  - [ ] Mensaje se marca como leído

### 5. Verificar consola del navegador
- [ ] Presionar F12 → Console
- [ ] No debe haber errores en rojo
- [ ] Verificar que Livewire carga: `Livewire.all()`

### 6. Verificar assets compilados
- [ ] Verificar que existen: `public/build/assets/app-*.js`
- [ ] Verificar que existen: `public/build/assets/app-*.css`
- [ ] Verificar que existen: `public/build/assets/chat-widget-*.js`
- [ ] Si no existen, ejecutar: `npm run build`

---

## 🆘 SOLUCIÓN DE PROBLEMAS COMUNES

### ❌ Error: "Class 'App\Livewire\Chat\NotificadorGlobal' not found"
```bash
composer dump-autoload
php artisan livewire:discover
php artisan config:clear
php artisan view:clear
```

---

### ❌ Error: "Mix manifest not found" o "Vite manifest not found"
```bash
npm install
npm run build
php artisan view:clear
```

---

### ❌ Error: "SQLSTATE[42S02]: Base table or view not found"
```bash
# Ejecutar migraciones
php artisan migrate

# Ver estado
php artisan migrate:status
```

---

### ❌ Error: "Permission denied" en storage
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

---

### ❌ No suena la notificación
```bash
# Verificar que se compilaron los assets
ls -la public/build/assets/ | grep chat

# Recompilar
npm run build

# Limpiar caché del navegador
# Ctrl + Shift + R (hard reload)
```

---

### ❌ Error 500 sin más detalles
```bash
# Activar debug temporalmente
nano .env
# Cambiar: APP_DEBUG=true

# Ver logs
tail -f storage/logs/laravel.log

# IMPORTANTE: Volver a poner APP_DEBUG=false después
```

---

## 📊 COMANDOS ÚTILES PARA MONITOREO

```bash
# Ver procesos PHP
ps aux | grep php

# Ver espacio en disco
df -h

# Ver uso de memoria
free -h

# Ver logs de Apache/Nginx
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# Ver logs de PHP
tail -f /var/log/php8.2-fpm.log

# Monitorear queries SQL en vivo
php artisan db:monitor
```

---

## 🔐 SEGURIDAD POST-DESPLIEGUE

```bash
# Verificar permisos de .env
chmod 600 .env

# Verificar que .env no sea accesible por web
curl https://tu-dominio.com/.env
# Debería dar 404 o 403

# Verificar que storage no sea accesible
curl https://tu-dominio.com/storage/logs/laravel.log
# Debería dar 404 o 403

# Generar nueva APP_KEY si es primera vez
php artisan key:generate
```

---

## 📝 NOTAS IMPORTANTES

1. **Backup siempre antes de desplegar**
2. **Probar primero en staging/desarrollo**
3. **No desplegar en horas pico de producción**
4. **Mantener APP_DEBUG=false en producción**
5. **Monitorear logs después del despliegue**
6. **Tener plan de rollback preparado**

---

## 🔄 ROLLBACK (Si algo sale mal)

```bash
# Restaurar base de datos
mysql -u usuario -p nombre_base_datos < backup_YYYYMMDD_HHMMSS.sql

# Restaurar archivos
rm -rf app/Http/Controllers/Api
mv app/Http/Controllers_backup_YYYYMMDD app/Http/Controllers

# Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Volver a migración anterior
php artisan migrate:rollback --step=5
```

---

## 📞 CONTACTO DE SOPORTE

**Desarrollador**: [Tu nombre]  
**Fecha de deploy**: Diciembre 3, 2025  
**Versión**: 5.0  
**Módulos**: API REST + Chat/Mensajería  

---

## ✅ FIN DE LA GUÍA

**Recuerda**: Si tienes dudas durante el despliegue, detente y consulta los logs antes de continuar.

**Comando de emergencia** (si todo falla):
```bash
php artisan down --message="Mantenimiento en proceso"
# ... hacer cambios ...
php artisan up
```
