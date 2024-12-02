<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CuentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cuentas')->insert([
            ['idcue' => 'DISNEY-1', 'idval' => 'DISNEYP-JUAN', 'usuariocue' => 'combosjose01@scarlitamail.com', 'fechavencue' => '2024-08-15', 'contrasenacue' => 'legopoli7P$$', 'caidacue' => false],
            ['idcue' => 'DISNEY-2', 'idval' => 'DISNEYP-JUAN', 'usuariocue' => 'combosjose60@scarlitamail.com', 'fechavencue' => '2024-08-09', 'contrasenacue' => 'cuenta123', 'caidacue' => false],
            ['idcue' => 'DISNEY-3', 'idval' => 'DISNEYP-JUAN', 'usuariocue' => 'combosjose69@scarlitamail.com', 'fechavencue' => '2024-08-09', 'contrasenacue' => 'cuenta123', 'caidacue' => false],
            ['idcue' => 'DISNEY-4', 'idval' => 'DISNEYP-JUAN', 'usuariocue' => 'combosjose80@scarlitamail.com', 'fechavencue' => '2024-08-09', 'contrasenacue' => 'cuenta123', 'caidacue' => false],
            ['idcue' => 'DISNEY-5', 'idval' => 'DISNEYP-JUAN', 'usuariocue' => 'combosjose90@scarlitamail.com', 'fechavencue' => '2024-08-09', 'contrasenacue' => 'cuenta123', 'caidacue' => false],
            ['idcue' => 'NETFLIX-1', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => 't-williams23.gb@onehitpe.xyz', 'fechavencue' => '2024-08-03', 'contrasenacue' => 'relax0', 'caidacue' => false],
            ['idcue' => 'NETFLIX-2', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => 'cartexmanxd.us@pronyx.xyz', 'fechavencue' => '2024-08-17', 'contrasenacue' => 'ele750', 'caidacue' => false],
            ['idcue' => 'NETFLIX-3', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => 'hnadh.us@nextaon.com', 'fechavencue' => '2024-08-03', 'contrasenacue' => '836634578', 'caidacue' => false],
            ['idcue' => 'NETFLIX-4', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => 'yenadira.us@scarlitamail.com', 'fechavencue' => '2024-08-03', 'contrasenacue' => '836634578', 'caidacue' => false],
            ['idcue' => 'NETFLIX-5', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => 'j.yacoub@jcarlos.vip', 'fechavencue' => '2024-08-25', 'contrasenacue' => 'combo123', 'caidacue' => false],
            ['idcue' => 'NETFLIX-6', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => 'dairon.br@pronyx.xyz', 'fechavencue' => '2024-08-04', 'contrasenacue' => 'DOLOR123', 'caidacue' => false],
            ['idcue' => 'NETFLIX-8', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => 'katie-whiteman@jcarlos.vip', 'fechavencue' => '2024-08-09', 'contrasenacue' => 'R102530', 'caidacue' => false],
            ['idcue' => 'NETFLIX-9', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => 'yaren.ca@yampe.xyz', 'fechavencue' => '2024-08-01', 'contrasenacue' => 'ander30', 'caidacue' => false],
            ['idcue' => 'CRUNCHYROLL-1', 'idval' => 'CRUNCHY-JUAN', 'usuariocue' => 'cruncry042@lissistr.com', 'fechavencue' => '2024-08-07', 'contrasenacue' => 'JC12345', 'caidacue' => false],
            ['idcue' => 'MAX-1', 'idval' => 'MAX-JUAN', 'usuariocue' => 'jcmax21@scarlitamail.com', 'fechavencue' => '2024-08-13', 'contrasenacue' => 'RE44123120', 'caidacue' => false],
            ['idcue' => 'MAX-2', 'idval' => 'MAX-JUAN', 'usuariocue' => 'cc3@scarlitamail.com', 'fechavencue' => '2024-08-28', 'contrasenacue' => 'jc123456', 'caidacue' => false],
            ['idcue' => 'MAX-3', 'idval' => 'MAX-JUAN', 'usuariocue' => 'jcmax22@scarlitamail.com', 'fechavencue' => '2024-08-13', 'contrasenacue' => 'RE44123120', 'caidacue' => false],
            ['idcue' => 'MAX-4', 'idval' => 'MAX-JUAN', 'usuariocue' => 'feoloco@scarlitamail.com', 'fechavencue' => '2024-08-28', 'contrasenacue' => 'jc123456', 'caidacue' => false],
            ['idcue' => 'MAGIS-1', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => '16mundo91', 'fechavencue' => '2024-08-16', 'contrasenacue' => 'lego777', 'caidacue' => false],
            ['idcue' => 'MAGIS-2', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => '2mick', 'fechavencue' => '2024-08-03', 'contrasenacue' => 'lego777', 'caidacue' => false],
            ['idcue' => 'MAGIS-3', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => '23magax', 'fechavencue' => '2024-08-23', 'contrasenacue' => 'lego777', 'caidacue' => false],
            ['idcue' => 'SPOTIFY-1', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => 'pablitoutn@outlook.es', 'fechavencue' => '2024-08-23', 'contrasenacue' => 'HUeWj:Mg2-T.wE8', 'caidacue' => true],
            ['idcue' => 'SPOTIFY-2', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => 'tel: 0999947287', 'fechavencue' => '2024-08-26', 'contrasenacue' => 'teléfono ma', 'caidacue' => false],
            ['idcue' => 'SPOTIFY-3', 'idval' => 'NETFLIX-JUAN', 'usuariocue' => 'pdjimeneze@utn.edu.ec', 'fechavencue' => '2024-08-13', 'contrasenacue' => 'legopoli7$', 'caidacue' => false],
        ]);
    }
}
