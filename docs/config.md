# Algunas configuraciones importantes
## Configuración de almacenamiento storage en producción
# 1. Eliminar todo lo que está mal dentro de public/storage
rm -rf ~/domains/streamify.aaronsoft.es/public_html/public/storage

# 2. Crear el symlink con ruta absoluta correcta
ln -s /home/u557565149/domains/streamify.aaronsoft.es/public_html/storage/app/public \
      /home/u557565149/domains/streamify.aaronsoft.es/public_html/public/storage

# 3. Verificar
ls -la ~/domains/streamify.aaronsoft.es/public_html/public/storage
