# Catálogo Masivo De Productos

Este comando crea o actualiza variantes masivas de productos sin duplicarlos. Usa `codigopro` determinístico para hacer upsert.

## Comando

```bash
php artisan catalog:sync-products
```

## Casos rápidos

Netflix individual de 1, 2, 3, 6 y 12 meses; 1, 2, 3 y 4 dispositivos:

```bash
php artisan catalog:sync-products --services=NETFLIX --months=1,2,3,6,12 --devices=1,2,3,4
```

Varios individuales a la vez:

```bash
php artisan catalog:sync-products --services=NETFLIX,DISNEYP,SPOTIFY --months=1,2,3,6,12 --devices=1,2,3
```

Combos exactos:

```bash
php artisan catalog:sync-products --combos=NETFLIX+DISNEYP,NETFLIX+DISNEYP+SPOTIFY,NETFLIX+MAX --months=1,3,6,12 --devices=1
```

Individuales y combos en un solo comando:

```bash
php artisan catalog:sync-products --services=NETFLIX,SPOTIFY --combos=NETFLIX+DISNEYP+SPOTIFY --months=1,3,6,12 --devices=1,2
```

Simular sin guardar:

```bash
php artisan catalog:sync-products --services=NETFLIX --months=1,2,3 --devices=1,2 --dry-run
```

## Archivo JSON de especificación

Puedes usar un solo archivo para definir toda la matriz:

```bash
php artisan catalog:sync-products --spec=docs/catalog-product-matrix.example.json
```

## Regla de precios

- Un solo detalle total: usa `servicios.precioser`
- Dos o más detalles totales: usa `servicios.comboser` por cada detalle
- Si `months > 1`: aplica descuento moderado sobre el subtotal mensual
- Si quieres cambiar la base, actualiza `servicios.precioser` y `servicios.comboser`, luego vuelve a correr el comando

## Observaciones

- `devices=2` para un servicio crea 2 detalles del mismo servicio
- Un combo con `devices=2` replica cada servicio 2 veces
- El comando actualiza productos existentes y vuelve a sincronizar sus `detalle_productos`
- Si quieres precio fijo para una definición concreta, usa `price_override` en el JSON

## Flujo automático de imágenes

Si no quieres subir imagen por cada variante, el comando ahora soporta reutilizar imágenes por servicio y generar mosaicos para combos.

Mapa de imágenes por servicio:

```bash
php artisan catalog:sync-products --services=NETFLIX,MAX,DISNEYP --months=1,2,3 --devices=1,2 --service-photo-map=docs/catalog-service-photo-map.example.json
```

Generación automática de imagen para combos:

```bash
php artisan catalog:sync-products --services=NETFLIX,MAX,DISNEYP --combos=NETFLIX+MAX,NETFLIX+DISNEYP+MAX --months=1,2,3 --devices=1 --service-photo-map=docs/catalog-service-photo-map.example.json --generate-combo-images
```

Notas:

- Las imágenes de combo se guardan en `public/storage/fotos/auto-combos`
- Puedes cambiar el destino con `--combo-image-dir`
- Si no encuentra imagen para un servicio, intenta usar foto existente del producto o fallback de primer servicio
