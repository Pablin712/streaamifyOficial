<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\DetalleProducto;
class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista de productos con sus detalles
        $productos = [
            [
                'codigopro' => 'NETFLIX',
                'nombrepro' => 'Netflix Premium',
                'preciopro' => 3.50,
                'estrellaspro' => 4,
                'descripcionpro' => '1 dispositivo por un mes, para renovar realizar el pago al menos 1 día antes para evitar corte.',
                'foto' => 'storage/fotos/netflix.png',
                'tipo_producto_id' => 1, // ID de 'Inmediata' en la tabla tipos_producto
                'categoria_id' => 1, // ID de 'Individual' en la tabla categorias
                'activo' => true,
                'detalle' => [
                    'idser' => 'NETFLIX',
                    'descripcion' => 'Netflix es la plataforma más popular, para código hogar solicitar con soporte técnico. Disfruta de un mes de contenido ilimitado en calidad premium.',
                    'meses' => 1,
                ],
            ],
            [
                'codigopro' => 'PRIME',
                'nombrepro' => 'Prime Video',
                'preciopro' => 2.50,
                'estrellaspro' => 4,
                'descripcionpro' => 'Acceso a la plataforma Prime Video por un mes. Contenido exclusivo y películas premium.',
                'foto' => 'storage/fotos/prime.jpg',
                'tipo_producto_id' => 1, // ID de 'Inmediata'
                'categoria_id' => 1, // ID de 'Individual'
                'activo' => true,
                'detalle' => [
                    'idser' => 'PRIME',
                    'descripcion' => 'Disfruta de contenido exclusivo de Amazon Prime Video durante un mes. Incluye series, películas y más.',
                    'meses' => 1,
                ],
            ],
            [
                'codigopro' => 'MAX',
                'nombrepro' => 'Max',
                'preciopro' => 2.50,
                'estrellaspro' => 5,
                'descripcionpro' => 'Servicio Max por un mes. Contenido premium de películas y series.',
                'foto' => 'storage/fotos/max.jpg',
                'tipo_producto_id' => 1,
                'categoria_id' => 1,
                'activo' => true,
                'detalle' => [
                    'idser' => 'MAX',
                    'descripcion' => 'Max es una plataforma que ofrece contenido exclusivo de alta calidad. Incluye series, películas y documentales.',
                    'meses' => 1,
                ],
            ],
            [
                'codigopro' => 'DISNEYP',
                'nombrepro' => 'Disney Premium',
                'preciopro' => 3.50,
                'estrellaspro' => 5,
                'descripcionpro' => 'Acceso Premium a Disney por un mes. Ideal para disfrutar contenido exclusivo.',
                'foto' => 'storage/fotos/disney.jpg',
                'tipo_producto_id' => 1,
                'categoria_id' => 1,
                'activo' => true,
                'detalle' => [
                    'idser' => 'DISNEYP',
                    'descripcion' => 'Disney Premium incluye acceso a todas las películas y series exclusivas de Disney durante un mes.',
                    'meses' => 1,
                ],
            ],
            [
                'codigopro' => 'DISNEYS',
                'nombrepro' => 'Disney Plus',
                'preciopro' => 2.50,
                'estrellaspro' => 5,
                'descripcionpro' => 'Acceso a Disney Plus por un mes. Contenido variado para toda la familia.',
                'foto' => 'storage/fotos/disney.jpg',
                'tipo_producto_id' => 1,
                'categoria_id' => 1,
                'activo' => true,
                'detalle' => [
                    'idser' => 'DISNEYS',
                    'descripcion' => 'Disfruta de Disney Plus con acceso a contenido exclusivo de Disney, Pixar, Marvel y más.',
                    'meses' => 1,
                ],
            ],
            [
                'codigopro' => 'MAGIS',
                'nombrepro' => 'Flujo TV',
                'preciopro' => 3.25,
                'estrellaspro' => 3,
                'descripcionpro' => 'Servicio de TV Flujo con acceso a contenido variado y canales exclusivos por un mes.',
                'foto' => 'storage/fotos/magis.jpg',
                'tipo_producto_id' => 1,
                'categoria_id' => 1,
                'activo' => true,
                'detalle' => [
                    'idser' => 'MAGIS',
                    'descripcion' => 'Flujo TV te ofrece una experiencia única de entretenimiento con contenido exclusivo y canales premium.',
                    'meses' => 1,
                ],
            ],
            [
                'codigopro' => 'PARAMOUNT',
                'nombrepro' => 'Paramount',
                'preciopro' => 1.99,
                'estrellaspro' => 4,
                'descripcionpro' => 'Acceso al catálogo de Paramount por un mes.',
                'foto' => 'storage/fotos/paramount.jpg',
                'tipo_producto_id' => 1,
                'categoria_id' => 1,
                'activo' => true,
                'detalle' => [
                    'idser' => 'PARAMOUNT',
                    'descripcion' => 'Paramount te ofrece una gran variedad de contenido exclusivo de películas y series premium.',
                    'meses' => 1,
                ],
            ],
            [
                'codigopro' => 'CRUNCHY',
                'nombrepro' => 'Crunchyroll',
                'preciopro' => 1.99,
                'estrellaspro' => 5,
                'descripcionpro' => 'Acceso a Crunchyroll por un mes. Ideal para amantes del anime.',
                'foto' => 'storage/fotos/crunchy.jpg',
                'tipo_producto_id' => 1,
                'categoria_id' => 1,
                'activo' => true,
                'detalle' => [
                    'idser' => 'CRUNCHY',
                    'descripcion' => 'Crunchyroll es la mejor plataforma para disfrutar de anime en alta calidad y con estrenos semanales.',
                    'meses' => 1,
                ],
            ],
        ];

        // Insertar los productos y sus detalles
        foreach ($productos as $producto) {
            // Crear el producto
            $nuevoProducto = Producto::create([
                'codigopro' => $producto['codigopro'],
                'nombrepro' => $producto['nombrepro'],
                'preciopro' => $producto['preciopro'],
                'estrellaspro' => $producto['estrellaspro'],
                'descripcionpro' => $producto['descripcionpro'],
                'foto' => $producto['foto'],
                'tipo_producto_id' => $producto['tipo_producto_id'],
                'categoria_id' => $producto['categoria_id'],
                'activo' => $producto['activo'],
            ]);

            // Crear el detalle asociado al producto
            DetalleProducto::create([
                'producto_id' => $nuevoProducto->id,
                'idser' => $producto['detalle']['idser'],
                'descripcion' => $producto['detalle']['descripcion'],
                'meses' => $producto['detalle']['meses'],
            ]);
        }
    }
}
