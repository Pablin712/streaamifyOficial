<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Recarga;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WhatsAppPaymentControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('clientes', function (Blueprint $table) {
            $table->id('idcli');
            $table->string('nombrecli', 50);
            $table->string('email', 255)->unique()->nullable();
            $table->string('password', 255)->nullable();
            $table->rememberToken();
            $table->string('telefonocli', 50)->nullable();
            $table->decimal('saldo', 15, 2)->default(0.00);
            $table->string('pais', 30)->default('Ecuador');
            $table->timestamps();
            $table->string('codigo_referidor', 50)->nullable();
            $table->unsignedInteger('referido_por')->nullable();
            $table->boolean('ya_compro')->default(0);
        });

        Schema::create('bancos', function (Blueprint $table) {
            $table->id('idban');
            $table->string('nombreban', 100);
            $table->string('propietarioban', 150)->default('Propietario');
            $table->string('cedulaban', 20)->default('9999999999');
            $table->string('numeroban', 20)->default('1234567890');
            $table->string('tipoban', 50)->default('ahorros');
            $table->text('detalleban')->nullable();
            $table->string('foto', 255)->nullable();
            $table->decimal('monto', 15, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('estado_recargas', function (Blueprint $table) {
            $table->id('idestado');
            $table->string('nombre', 50);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('recargas', function (Blueprint $table) {
            $table->id('idrec');
            $table->unsignedBigInteger('idcli');
            $table->string('numcomprobante', 50)->unique();
            $table->decimal('valor', 15, 2);
            $table->string('foto', 255)->nullable();
            $table->string('comprobante_hash', 64)->nullable()->unique();
            $table->unsignedBigInteger('idestado');
            $table->unsignedBigInteger('idban');
            $table->string('origen', 30)->nullable();
            $table->string('external_reference', 191)->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('transaccion_id')->nullable();
            $table->timestamps();
        });

        Schema::create('historial', function (Blueprint $table) {
            $table->id();
            $table->string('accion', 255);
            $table->text('descripcion')->nullable();
            $table->bigInteger('empleado_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('storage/comprobantes'));

        parent::tearDown();
    }

    public function test_receipt_intake_creates_unknown_client_and_recarga_from_base64(): void
    {
        DB::table('bancos')->insert([
            'idban' => 1,
            'nombreban' => 'Banco Pichincha',
            'propietarioban' => 'Propietario',
            'cedulaban' => '9999999999',
            'numeroban' => '1234567890',
            'tipoban' => 'ahorros',
            'monto' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('estado_recargas')->insert([
            'idestado' => 1,
            'nombre' => 'Pendiente',
            'descripcion' => 'Pendiente de revision',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('clientes')->insert([
            'nombrecli' => 'Cliente WhatsApp 1',
            'telefonocli' => '+593 00 000 0001',
            'saldo' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $base64Png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WnR8xwAAAAASUVORK5CYII=';

        $response = $this->postJson('/api/v2/payments/n8n/receipt-intake', [
            'cliente_telefono' => '+593 99 888 7777',
            'banco_nombre' => 'Pichincha',
            'valor' => 12.50,
            'numcomprobante' => 'COMP-INTAKE-001',
            'media_base64' => $base64Png,
            'media_mime_type' => 'image/png',
            'media_file_name' => 'comprobante.png',
            'canal' => 'whatsapp',
            'external_reference' => 'wa-msg-001',
            'ocr' => [
                'banco' => 'Pichincha',
                'monto' => 12.50,
            ],
            'validacion' => [
                'realista' => true,
                'confianza' => 92,
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.cliente_creado', true)
            ->assertJsonPath('data.verificacion_disparada', true)
            ->assertJsonPath('data.cliente.nombrecli', 'Cliente WhatsApp 2')
            ->assertJsonPath('data.recarga.numcomprobante', 'COMP-INTAKE-001')
            ->assertJsonPath('data.recarga.idban', 1);

        $cliente = Cliente::query()
            ->where('telefonocli', '+593 99 888 7777')
            ->first();
        $this->assertNotNull($cliente);
        $this->assertSame('+593 99 888 7777', $cliente->telefonocli);

        $recarga = Recarga::query()
            ->where('numcomprobante', 'COMP-INTAKE-001')
            ->first();
        $this->assertNotNull($recarga);
        $this->assertSame($cliente->idcli, $recarga->idcli);
        $this->assertSame('COMP-INTAKE-001', $recarga->numcomprobante);
        $this->assertSame('whatsapp', $recarga->origen);
        $this->assertNotEmpty($recarga->foto);
        $this->assertFileExists(public_path('storage/' . $recarga->foto));
    }

    public function test_receipt_intake_maps_banco_del_barrio_to_banco_guayaquil(): void
    {
        DB::table('bancos')->insert([
            'idban' => 2,
            'nombreban' => 'Banco Guayaquil',
            'propietarioban' => 'Propietario',
            'cedulaban' => '9999999999',
            'numeroban' => '1234567890',
            'tipoban' => 'ahorros',
            'detalleban' => 'Deposita en Banco Guayaquil o Banco del Barrio',
            'monto' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('estado_recargas')->insert([
            'idestado' => 1,
            'nombre' => 'Pendiente',
            'descripcion' => 'Pendiente de revision',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $base64Png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WnR8xwAAAAASUVORK5CYII=';

        $response = $this->postJson('/api/v2/payments/n8n/receipt-intake', [
            'cliente_telefono' => '+593 98 111 2233',
            'banco_nombre' => 'Banco del Barrio',
            'valor' => 4.50,
            'numcomprobante' => 'DEP-GYE-001',
            'media_base64' => $base64Png,
            'media_mime_type' => 'image/png',
            'media_file_name' => 'deposito.png',
            'canal' => 'whatsapp',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.recarga.idban', 2)
            ->assertJsonPath('data.recarga.banco.nombreban', 'Banco Guayaquil');
    }
}
