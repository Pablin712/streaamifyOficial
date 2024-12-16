<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;

    class ServicioSeeder extends Seeder
    {
        public function run(): void
        {
            DB::table('servicios')->insert([
                ['idser' => 'NETFLIX', 'nombreser' => 'NETFLIX PREMIUM', 'completoser' => 11, 'precioser' => 3.25, 'comboser' => 2.75, 'reventaser' => 2.5, 'revcompser' => 9],
                ['idser' => 'DISNEYP', 'nombreser' => 'DISNEY PREMIUM', 'completoser' => 11, 'precioser' => 3.5, 'comboser' => 2.75, 'reventaser' => 2.5, 'revcompser' => 9],
                ['idser' => 'DISNEYS', 'nombreser' => 'DISNEY STANDARD', 'completoser' => 8, 'precioser' => 2.5, 'comboser' => 2, 'reventaser' => 2, 'revcompser' => 4.5],
                ['idser' => 'PRIME', 'nombreser' => 'PRIME VIDEO', 'completoser' => 6, 'precioser' => 2.25, 'comboser' => 1.5, 'reventaser' => 1.25, 'revcompser' => 3.5],
                ['idser' => 'MAX', 'nombreser' => 'MAX STANDARD', 'completoser' => 6, 'precioser' => 2.5, 'comboser' => 2, 'reventaser' => 2, 'revcompser' => 4],
                ['idser' => 'MAGIS', 'nombreser' => 'MAGIS TV', 'completoser' => 8, 'precioser' => 3.25, 'comboser' => 2.75, 'reventaser' => 2.5, 'revcompser' => 5],
                ['idser' => 'CRUNCHY', 'nombreser' => 'CRUNCHYROLL', 'completoser' => 4, 'precioser' => 1.5, 'comboser' => 1, 'reventaser' => 1, 'revcompser' => 3.5],
                ['idser' => 'PARAMOUNT', 'nombreser' => 'PARAMOUNT PLUS', 'completoser' => 4, 'precioser' => 1.75, 'comboser' => 1.25, 'reventaser' => 1.1, 'revcompser' => 3.5],
                ['idser' => 'SPOTIFY', 'nombreser' => 'SPOTIFY PREMIUM', 'completoser' => 11, 'precioser' => 3, 'comboser' => 2.75, 'reventaser' => 2.5, 'revcompser' => 11],
                ['idser' => 'VIX', 'nombreser' => 'VIX', 'completoser' => 7.5, 'precioser' => 2.75, 'comboser' => 2.25, 'reventaser' => 2.25, 'revcompser' => 5],
                ['idser' => 'OTRO', 'nombreser' => 'OTRAS QUE SE VENDEN', 'completoser' => 0, 'precioser' => 0, 'comboser' => 0, 'reventaser' => 0, 'revcompser' => 0],
            ]);
        }
    }
?>