<?php

namespace Database\Seeders;

use App\Models\Cuenta;
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
            // Cuentas para NETFLIX
            ['idcue' => 'NETFLIX-1', 'usuariocue' => 'omcro@scarlitamail.com', 'contrasenacue' => 'Messigoat10', 'idval' => 'NETFLIX-JUAN', 'fechavencue' => '2025-01-03', 'caidacue' => false],
            ['idcue' => 'NETFLIX-3', 'usuariocue' => 'rittnie347@jcmylove.net', 'contrasenacue' => 'N102525', 'idval' => 'NETFLIX-JUAN', 'fechavencue' => '2025-01-01', 'caidacue' => false],
            ['idcue' => 'NETFLIX-4', 'usuariocue' => 'danielappereira@jcmylove.net', 'contrasenacue' => 'messigoat10', 'idval' => 'NETFLIX-JUAN', 'fechavencue' => '2025-01-06', 'caidacue' => false],
            ['idcue' => 'NETFLIX-5', 'usuariocue' => 'muratagod29@jcarlos.vip', 'contrasenacue' => '104545', 'idval' => 'NETFLIX-JUAN', 'fechavencue' => '2024-12-25', 'caidacue' => false],
            ['idcue' => 'NETFLIX-8', 'usuariocue' => 'katie-whiteman@jcarlos.vip', 'contrasenacue' => 'pablin7777', 'idval' => 'NETFLIX-JUAN', 'fechavencue' => '2025-01-09', 'caidacue' => false],
            ['idcue' => 'NETFLIX-9', 'usuariocue' => 'stabide@jcarlos.vip', 'contrasenacue' => '106363', 'idval' => 'NETFLIX-JUAN', 'fechavencue' => '2025-01-10', 'caidacue' => false],
            ['idcue' => 'NETFLIX-12', 'usuariocue' => 'YilberXM498@jcmylove.net', 'contrasenacue' => 'pablinmessi', 'idval' => 'NETFLIX-JUAN', 'fechavencue' => '2025-01-07', 'caidacue' => false],
            ['idcue' => 'NETFLIX-18', 'usuariocue' => 'mendezcastro3@jcmylove.net', 'contrasenacue' => 'EN MANTENIMIENTO', 'idval' => 'NETFLIX-JUAN', 'fechavencue' => '2025-01-11', 'caidacue' => false],
            ['idcue' => 'NETFLIX-23', 'usuariocue' => 'Pebbles@jcmylove.net', 'contrasenacue' => 'Messigoat10', 'idval' => 'NETFLIX-JUAN', 'fechavencue' => '2025-01-16', 'caidacue' => false],
            ['idcue' => 'NETFLIX-34', 'usuariocue' => 'YilberXM41@jcmylove.net', 'contrasenacue' => '104510', 'idval' => 'NETFLIX-JUAN', 'fechavencue' => '2025-01-02', 'caidacue' => false],
            ['idcue' => 'NETFLIX-42', 'usuariocue' => 'sposse@scarlitamail.com', 'contrasenacue' => 'Messigoat10', 'idval' => 'NETFLIX-JUAN', 'fechavencue' => '2025-01-02', 'caidacue' => false],

            // Cuentas para DISNEY
            ['idcue' => 'DISNEY-1', 'usuariocue' => 'combosjose10@scarlitamail.com', 'contrasenacue' => 'cuenta123', 'idval' => 'DISNEYP-JUAN', 'fechavencue' => '2025-01-10', 'caidacue' => false],
            ['idcue' => 'DISNEY-2', 'usuariocue' => 't_markers@jcmylove.net', 'contrasenacue' => 'R135976', 'idval' => 'DISNEYP-JUAN', 'fechavencue' => '2025-01-15', 'caidacue' => false],
            ['idcue' => 'DISNEY-3', 'usuariocue' => 'dpre1mium@scarlitamail.com', 'contrasenacue' => 'messigoat10', 'idval' => 'DISNEYP-JUAN', 'fechavencue' => '2025-01-03', 'caidacue' => false],
            ['idcue' => 'DISNEY-4', 'usuariocue' => 'germano_geller@jcmylove.net', 'contrasenacue' => 'JC12345', 'idval' => 'DISNEYP-JUAN', 'fechavencue' => '2025-01-07', 'caidacue' => false],
            ['idcue' => 'DISNEY-5', 'usuariocue' => 'dy573425@scarlitamail.com', 'contrasenacue' => 'R785772', 'idval' => 'DISNEYP-JUAN', 'fechavencue' => '2025-01-07', 'caidacue' => false],
            ['idcue' => 'DISNEY-6', 'usuariocue' => 'remelioyork45@scarlitamail.com', 'contrasenacue' => 'R076146', 'idval' => 'DISNEYP-JUAN', 'fechavencue' => '2025-01-09', 'caidacue' => false],
            ['idcue' => 'DISNEY-7', 'usuariocue' => 'neilson_cruz2@jcarlos.vip', 'contrasenacue' => 'D031365', 'idval' => 'DISNEYP-JUAN', 'fechavencue' => '2025-01-10', 'caidacue' => false],
            ['idcue' => 'DISNEY-8', 'usuariocue' => 'combosjose80@scarlitamail.com', 'contrasenacue' => 'cuenta123', 'idval' => 'DISNEYP-JUAN', 'fechavencue' => '2025-01-10', 'caidacue' => false],
            ['idcue' => 'DISNEY-9', 'usuariocue' => 'eduardoyamaneko@jcarlos.vip', 'contrasenacue' => 'R542248', 'idval' => 'DISNEYP-JUAN', 'fechavencue' => '2024-12-26', 'caidacue' => false],

            // Cuentas para DISNEYS
            ['idcue' => 'DISNEYS-10', 'usuariocue' => 'matisebariv@jcarlos.vip', 'contrasenacue' => 'R810420', 'idval' => 'DISNEYS-JUAN', 'fechavencue' => '2024-12-21', 'caidacue' => false],
            ['idcue' => 'DISNEYS-11', 'usuariocue' => 'alan_smash@jcarlos.vip', 'contrasenacue' => 'R12345', 'idval' => 'DISNEYS-JUAN', 'fechavencue' => '2024-12-28', 'caidacue' => false],
            ['idcue' => 'DISNEYS-12', 'usuariocue' => 'stuart.haigh1@jcmylove.net', 'contrasenacue' => 'R12345', 'idval' => 'DISNEYS-JUAN', 'fechavencue' => '2025-01-01', 'caidacue' => false],

            // Cuentas para MAX
            ['idcue' => 'MAX-1', 'usuariocue' => 'jc404797+de0@gmail.com', 'contrasenacue' => 'JC12345622', 'idval' => 'MAX-JUAN', 'fechavencue' => '2025-01-10', 'caidacue' => false],
            ['idcue' => 'MAX-2', 'usuariocue' => 'feo1@scarlitamail.com', 'contrasenacue' => 'jc12341225', 'idval' => 'MAX-JUAN', 'fechavencue' => '2025-01-10', 'caidacue' => false],
            ['idcue' => 'MAX-3', 'usuariocue' => 'Maxi004@scarlitamail.com', 'contrasenacue' => 'Tr123321VE', 'idval' => 'MAX-JUAN', 'fechavencue' => '2025-01-11', 'caidacue' => false],
            ['idcue' => 'MAX-4', 'usuariocue' => 'jcmax21@scarlitamail.com', 'contrasenacue' => 'RE44123120', 'idval' => 'MAX-JUAN', 'fechavencue' => '2025-01-13', 'caidacue' => false],
            ['idcue' => 'MAX-5', 'usuariocue' => 'csfdf@jcmylove.net', 'contrasenacue' => 'JC12345678', 'idval' => 'MAX-JUAN', 'fechavencue' => '2025-12-28', 'caidacue' => false],

            // Cuentas para PARAMOUNT
            ['idcue' => 'PARAMOUNT-1', 'usuariocue' => 'foututoneuffo@scarlitamail.com', 'contrasenacue' => 'N12345', 'idval' => 'PARAMOUNT-JUAN', 'fechavencue' => '2025-01-12', 'caidacue' => false],
        ]);
        //Cuenta::factory()->count(30)->create();  // Crea 130 registros de cuentas
    }
}
