/**
 * OBSOLETO — no se carga desde ningún layout.
 *
 * Este archivo registraba un SEGUNDO manejador de clic sobre el mismo botón
 * #toggleDarkMode que ya maneja navbar.js, así que cada clic alternaba dos
 * veces. Además llamaba a ThemeManager.setTheme('dark'), un tema que nunca
 * existió en el catálogo, por lo que fallaba en silencio.
 *
 * La gestión de apariencia vive ahora en:
 *   - app/Services/AparienciaService.php  (fuente de verdad, en el servidor)
 *   - public/js/theme-manager.js          (refleja y persiste)
 *   - public/js/navbar.js                 (botón del navbar)
 *
 * Se deja vacío en lugar de borrarlo para no romper ninguna caché o enlace
 * directo que todavía apunte al archivo. Se puede eliminar más adelante.
 */
