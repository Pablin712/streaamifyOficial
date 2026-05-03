<?php

namespace App\Console\Commands;

use App\Models\ChatContactoCanal;
use App\Models\Cliente;
use App\Support\PhoneNumber;
use Illuminate\Console\Command;

class EstandarizarTelefonosCommand extends Command
{
    protected $signature = 'telefonos:estandarizar {--dry-run : Solo mostrar cambios sin guardar}';
    protected $aliases = ['telefono:estandarizar'];

    protected $description = 'Estandariza telefonos de clientes y contactos de canal para compatibilidad WhatsApp/Evolution API';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $clientesActualizados = 0;
        $contactosActualizados = 0;
        $contactosConflicto = 0;

        Cliente::query()
            ->whereNotNull('telefonocli')
            ->where('telefonocli', '!=', '')
            ->chunkById(200, function ($clientes) use (&$clientesActualizados, $dryRun) {
                foreach ($clientes as $cliente) {
                    $nuevo = PhoneNumber::formatForStorage($cliente->telefonocli);

                    if ($nuevo === '' || $nuevo === $cliente->telefonocli) {
                        continue;
                    }

                    $clientesActualizados++;

                    if (!$dryRun) {
                        $cliente->forceFill(['telefonocli' => $nuevo])->save();
                    }
                }
            }, 'idcli');

        ChatContactoCanal::query()
            ->where('canal', 'whatsapp')
            ->chunkById(200, function ($contactos) use (&$contactosActualizados, &$contactosConflicto, $dryRun) {
                foreach ($contactos as $contacto) {
                    $nuevoTelefono = PhoneNumber::canonicalEc($contacto->telefono_normalizado);
                    $nuevoCanalUserId = PhoneNumber::canonicalEc($contacto->canal_user_id);

                    $cambios = [];

                    if ($nuevoTelefono && $nuevoTelefono !== $contacto->telefono_normalizado) {
                        $cambios['telefono_normalizado'] = $nuevoTelefono;
                    }

                    if ($nuevoCanalUserId && $nuevoCanalUserId !== $contacto->canal_user_id) {
                        $cambios['canal_user_id'] = $nuevoCanalUserId;
                    }

                    if (isset($cambios['canal_user_id'])) {
                        $colision = ChatContactoCanal::query()
                            ->where('canal', 'whatsapp')
                            ->where('canal_user_id', $cambios['canal_user_id'])
                            ->where('id', '!=', $contacto->id)
                            ->exists();

                        if ($colision) {
                            unset($cambios['canal_user_id']);
                            $contactosConflicto++;
                        }
                    }

                    if ($cambios === []) {
                        continue;
                    }

                    $contactosActualizados++;

                    if (!$dryRun) {
                        $contacto->fill($cambios)->save();
                    }
                }
            });

        $this->info('Clientes ajustados: ' . $clientesActualizados);
        $this->info('Contactos WhatsApp ajustados: ' . $contactosActualizados);
        $this->warn('Contactos WhatsApp con conflicto de llave unica (omitidos): ' . $contactosConflicto);

        if ($dryRun) {
            $this->comment('Modo dry-run activo: no se guardaron cambios.');
        }

        return self::SUCCESS;
    }
}
