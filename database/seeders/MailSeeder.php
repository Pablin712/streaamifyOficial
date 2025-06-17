<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mail;
class MailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mails = [
            ['email' => 'pablin@aaronsoft.es',      'password' => 'Messigoat10$'],
            ['email' => 'ronaldodanielje11@gmail.com', 'password' => 'pablinmessi10$'],
            ['email' => 'streamifyhq@aaronsoft.es', 'password' => 'Messigoat10$$'],
            ['email' => 'streamify@aaronsoft.es',   'password' => 'Legopoli7P$'],
            ['email' => 'pablojimenez@aaronsoft.es','password' => 'Messithegoat10$$'],
            ['email' => 'streamify01@aaronsoft.es', 'password' => 'messiGoat10$'],
            ['email' => 'streamify02@aaronsoft.es', 'password' => 'messiGoat10$$'],
            ['email' => 'streamify03@aaronsoft.es', 'password' => 'messiGoat10$$$'],
            ['email' => 'streamify04@aaronsoft.es', 'password' => 'messiGoat10$$$$'],
            ['email' => 'streamify05@aaronsoft.es', 'password' => 'messiGoat10$$$$$'],
            ['email' => 'streamify06@aaronsoft.es', 'password' => 'messiGoat10$$$$$$'],
            ['email' => 'streamify07@aaronsoft.es', 'password' => 'messiGoat10$$$$$$$'],
            ['email' => 'streamify08@aaronsoft.es', 'password' => 'messiGoat10@'],
            ['email' => 'streamify09@aaronsoft.es', 'password' => 'messiGoat10@'],
        ];

        foreach ($mails as $mail) {
            $host = str_ends_with($mail['email'], '@gmail.com') ? 'Gmail' : 'mail.hostinger';
            Mail::create([
                'email' => $mail['email'],
                'password' => $mail['password'],
                'host' => $host,
                'description' => 'revisar',
            ]);
        }
    }
}
