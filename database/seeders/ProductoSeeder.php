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
                'foto' => 'storage/fotos/netflix.jpg',
                'tipo_producto_id' => 1, // ID de 'Inmediata' en la tabla tipos_producto
                'categoria_id' => 1, // ID de 'Individual' en la tabla categorias
                'activo' => true,
                'detalles' => [
                    [
                        'idser' => 'NETFLIX',
                        'descripcion' => 'Netflix es la plataforma más popular, para código hogar solicitar con soporte técnico. Disfruta de un mes de contenido ilimitado en calidad premium.',
                        'meses' => 1,
                    ],
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
                'detalles' => [
                    [
                        'idser' => 'PRIME',
                        'descripcion' => 'Disfruta de contenido exclusivo de Amazon Prime Video durante un mes. Incluye series, películas y más.',
                        'meses' => 1,
                    ],
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
                'detalles' => [
                    [
                        'idser' => 'MAX',
                        'descripcion' => 'Max es una plataforma que ofrece contenido exclusivo de alta calidad. Incluye series, películas y documentales.',
                        'meses' => 1,
                    ],
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
                'detalles' => [
                    [
                        'idser' => 'DISNEYP',
                        'descripcion' => 'Disney Premium incluye acceso a todas las películas y series exclusivas de Disney durante un mes.',
                        'meses' => 1,
                    ],
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
                'detalles' => [
                    [
                        'idser' => 'DISNEYS',
                        'descripcion' => 'Disfruta de Disney Plus con acceso a contenido exclusivo de Disney, Pixar, Marvel y más.',
                        'meses' => 1,
                    ],
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
                'detalles' => [
                    [
                        'idser' => 'MAGIS',
                        'descripcion' => 'Flujo TV te ofrece una experiencia única de entretenimiento con contenido exclusivo y canales premium.',
                        'meses' => 1,
                    ],
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
                'detalles' => [
                    [
                        'idser' => 'PARAMOUNT',
                        'descripcion' => 'Paramount te ofrece una gran variedad de contenido exclusivo de películas y series premium.',
                        'meses' => 1,
                    ],
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
                'detalles' => [
                    [
                        'idser' => 'CRUNCHY',
                        'descripcion' => 'Crunchyroll es la mejor plataforma para disfrutar de anime en alta calidad y con estrenos semanales.',
                        'meses' => 1,
                    ],
                ],
            ],
            [
                'codigopro' => 'COMBO_TRIO1',
                'nombrepro' => 'Combo Trio 1',
                'preciopro' => 6.00,
                'estrellaspro' => 4,
                'descripcionpro' => 'Combo que incluye Spotify, Prime Video y Disney Plus.',
                'foto' => 'storage/fotos/32.png',
                'tipo_producto_id' => 2, // Supongamos que 2 es para pedidos
                'categoria_id' => 2, // Supongamos que 2 es categoría de combos
                'activo' => true,
                'detalles' => [
                    [
                        'idser' => 'SPOTIFY',
                        'descripcion' => 'Acceso a Spotify Premium por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'PRIME',
                        'descripcion' => 'Acceso a Prime Video por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'DISNEYS',
                        'descripcion' => 'Acceso a Disney Plus por 1 mes.',
                        'meses' => 1,
                    ],
                ],
            ],
            [
                'codigopro' => 'COMBO_TRIO2',
                'nombrepro' => 'Combo Trio 2',
                'preciopro' => 8.00,
                'estrellaspro' => 4,
                'descripcionpro' => 'Combo que incluye Netflix, Disney Plus y Spotify Premium.',
                'foto' => 'storage/fotos/33.png',
                'tipo_producto_id' => 2, // Supongamos que 2 es para pedidos
                'categoria_id' => 2, // Supongamos que 2 es categoría de combos
                'activo' => true,
                'detalles' => [
                    [
                        'idser' => 'NETFLIX',
                        'descripcion' => 'Acceso a Netflix Premium por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'DISNEYS',
                        'descripcion' => 'Acceso a Disney Plus por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'SPOTIFY',
                        'descripcion' => 'Acceso a Spotify Premium por 1 mes.',
                        'meses' => 1,
                    ],
                ],
            ],
            [
                'codigopro' => 'COMBO_TRIO3',
                'nombrepro' => 'Combo Trio 3',
                'preciopro' => 7.50,
                'estrellaspro' => 5,
                'descripcionpro' => 'Combo que incluye Netflix, Disney Premium con ESPN y MAX.',
                'foto' => 'storage/fotos/34.png',
                'tipo_producto_id' => 1, // Supongamos que 1 es para inmediata
                'categoria_id' => 2, // Supongamos que 2 es categoría de combos
                'activo' => true,
                'detalles' => [
                    [
                        'idser' => 'NETFLIX',
                        'descripcion' => 'Acceso a Netflix Premium por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'DISNEYP',
                        'descripcion' => 'Acceso a Disney Premium con ESPN por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'MAX',
                        'descripcion' => 'Acceso a MAX por 1 mes.',
                        'meses' => 1,
                    ],
                ],
            ],
            [
                'codigopro' => 'COMBO_TRIO4',
                'nombrepro' => 'Combo Trio 4',
                'preciopro' => 7.50,
                'estrellaspro' => 5,
                'descripcionpro' => 'Combo que incluye Netflix, Spotify Premium y MAX.',
                'foto' => 'storage/fotos/35.png',
                'tipo_producto_id' => 2, // Supongamos que 2 es para pedidos
                'categoria_id' => 2, // Supongamos que 2 es categoría de combos
                'activo' => true,
                'detalles' => [
                    [
                        'idser' => 'NETFLIX',
                        'descripcion' => 'Acceso a Netflix Premium por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'SPOTIFY',
                        'descripcion' => 'Acceso a Spotify Premium por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'MAX',
                        'descripcion' => 'Acceso a MAX por 1 mes.',
                        'meses' => 1,
                    ],
                ],
            ],
            [
                'codigopro' => 'COMBO_CUARTETO1',
                'nombrepro' => 'Combo Cuarteto 1',
                'preciopro' => 7.00,
                'estrellaspro' => 5,
                'descripcionpro' => 'Combo que incluye MAX, Disney Plus, Paramount Plus y Crunchyroll Mega Fan.',
                'foto' => 'storage/fotos/36.png',
                'tipo_producto_id' => 1, // Supongamos que 1 es para inmediata
                'categoria_id' => 2, // Supongamos que 2 es categoría de combos
                'activo' => true,
                'detalles' => [
                    [
                        'idser' => 'MAX',
                        'descripcion' => 'Acceso a MAX por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'DISNEYS',
                        'descripcion' => 'Acceso a Disney Plus por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'PARAMOUNT',
                        'descripcion' => 'Acceso a Paramount Plus por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'CRUNCHY',
                        'descripcion' => 'Acceso a Crunchyroll por 1 mes.',
                        'meses' => 1,
                    ],
                ],
            ],
            [
                'codigopro' => 'COMBO_CUARTETO2',
                'nombrepro' => 'Combo Cuarteto 2',
                'preciopro' => 8.25,
                'estrellaspro' => 5,
                'descripcionpro' => 'Combo que incluye Netflix Premium, Disney Premium, Paramount Plus y Crunchyroll Mega Fan.',
                'foto' => 'storage/fotos/37.png',
                'tipo_producto_id' => 1, // Supongamos que 1 es para inmediata
                'categoria_id' => 2, // Supongamos que 2 es categoría de combos
                'activo' => true,
                'detalles' => [
                    [
                        'idser' => 'NETFLIX',
                        'descripcion' => 'Acceso a Netflix Premium por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'DISNEYP',
                        'descripcion' => 'Acceso a Disney Premium por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'PARAMOUNT',
                        'descripcion' => 'Acceso a Paramount Plus por 1 mes.',
                        'meses' => 1,
                    ],
                    [
                        'idser' => 'CRUNCHY',
                        'descripcion' => 'Acceso a Crunchyroll por 1 mes.',
                        'meses' => 1,
                    ],
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

            // Verificar si el producto tiene detalles antes de iterar
            if (isset($producto['detalles']) && is_array($producto['detalles'])) {
                foreach ($producto['detalles'] as $detalle) {
                    DetalleProducto::create([
                        'producto_id' => $nuevoProducto->id,
                        'idser' => $detalle['idser'],
                        'descripcion' => $detalle['descripcion'],
                        'meses' => $detalle['meses'],
                    ]);
                }
            }
        }
    }
}
