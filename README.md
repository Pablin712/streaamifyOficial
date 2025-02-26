<h1>Streamify HQ</h1>

## Versiones y módulos

1. Streamify v4.0.1: Es la versión para la administración y gestión de negocio, la ocupan principalmente los usuarios empleados
2. Streamify v5.1.2: Es la última versión, ya incluída el módulo de clientes (Eccomerce), como en esta ya existe el módulo de empleados (ERP), v5.1.2 es la aplicación completa, mejorada y lista para que clientes puedan comprar, renovar, y ver sus suscripciones con Streamify.

## Instalación

1. Clonar repositorio.
   ```sh
   git clone https://github.com/Pablin712/streaamifyOficial.git Streamify
   cd Streamify
   code .

2. Backend
    ```sh
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate --seed
    php artisan storage:link
    php artisan serve

3. Frontend
    ```sh
    npm install

## A tomar en cuenta Para subir cambios
1. Para crear una nueva rama.
    ```sh
    git branch (nombre de la rama)
    git switch (nombre de la rama)
2. Para subir los cambios a la rama main.
    ```sh
    git switch main
    git pull origin main
    git add . // git add (nombre del archivo)
    git commit -m "Comentario"
    git push origin main
3. Si da conflictos al realizar las migraciones ejecutar estos comandos.
    ```sh
    php artisan config:clear
    php artisan migrate:fresh
    php artisan db:seed
4. Si da conflictos en el merge solucionarlos en el editor.
5. Si se desea realizar cambios, crear una nueva rama y realizar sus cambios. 
6. Es necesario ejecutar el siguiente comando:
    ```sh
    php artisan storage:link
