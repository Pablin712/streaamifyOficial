<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\TelegramAuthSession;
use App\Services\TelegramAuthService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClienteAuthTest extends TestCase
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
            $table->bigInteger('telegram_chat_id')->nullable()->unique();
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

        Schema::create('telegram_auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id')->unique();
            $table->string('step', 50)->default('inicio');
            $table->string('proceso', 50)->nullable();
            $table->text('datos')->nullable();
            $table->tinyInteger('intentos')->unsigned()->default(0);
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();
        });

        Schema::create('historial', function (Blueprint $table) {
            $table->id();
            $table->string('accion', 255);
            $table->text('descripcion')->nullable();
            $table->bigInteger('empleado_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_cliente_register_rejects_full_name_longer_than_database_limit(): void
    {
        $response = $this->from('/register')->post('/register', [
            'first_name' => 'Nombrelarguisimo Nombrelarguisimo',
            'last_name' => 'Apellidoextensisimo Apellidoextensisimo',
            'email' => 'registro-' . Str::uuid() . '@example.com',
            'telefonocli' => '+593 99 999 9999',
            'pais' => 'Ecuador',
            'password' => 'Clave12@',
            'password_confirmation' => 'Clave12@',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'first_name' => 'El nombre completo no puede superar los 50 caracteres.',
        ]);
    }

    public function test_cliente_login_authenticates_with_valid_credentials(): void
    {
        $cliente = Cliente::create([
            'nombrecli' => 'Cliente Prueba Auth',
            'email' => 'login-' . Str::uuid() . '@example.com',
            'password' => 'Clave12@',
            'telefonocli' => '+593 98 765 4321',
            'pais' => 'Ecuador',
            'saldo' => 0,
        ]);

        $response = $this->post('/cliente/login', [
            'email' => $cliente->email,
            'password' => 'Clave12@',
        ]);

        $response->assertRedirect(route('shop'));
        $this->assertAuthenticatedAs($cliente, 'cliente');
    }

    public function test_telegram_auth_existing_email_can_switch_to_login_flow(): void
    {
        $cliente = Cliente::create([
            'nombrecli' => 'Telegram Cliente Existente',
            'email' => 'telegram-' . Str::uuid() . '@example.com',
            'password' => 'Clave12@',
            'telefonocli' => '+593 97 654 3210',
            'pais' => 'Ecuador',
            'saldo' => 0,
        ]);

        TelegramAuthSession::create([
            'chat_id' => 987654321,
            'step' => 'registro_email',
            'proceso' => 'registro',
            'datos' => ['nombre' => 'Cliente Telegram'],
            'intentos' => 0,
        ]);

        $service = app(TelegramAuthService::class);

        $resultadoRegistro = $service->procesarPaso(987654321, 'registro_email', $cliente->email);

        $this->assertSame('registro_email_existe', $resultadoRegistro['paso_siguiente']);

        $session = TelegramAuthSession::where('chat_id', 987654321)->firstOrFail();
        $this->assertSame($cliente->email, $session->datos['email']);

        $resultadoLogin = $service->procesarPaso(987654321, 'registro_email_existe', 'SI');

        $this->assertTrue($resultadoLogin['exito']);
        $this->assertSame('login_password', $resultadoLogin['paso_siguiente']);

        $session->refresh();
        $this->assertSame('login_password', $session->step);
    }
}
