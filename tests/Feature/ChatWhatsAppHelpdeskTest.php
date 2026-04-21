<?php

namespace Tests\Feature;

use App\Livewire\Chat\WhatsAppHelpdesk;
use App\Models\ChatContactoCanal;
use App\Models\Cliente;
use App\Models\Conversacion;
use App\Models\Empleado;
use App\Models\Mensaje;
use App\Services\Chat\WhatsAppHelpdeskService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ChatWhatsAppHelpdeskTest extends TestCase
{
    protected Empleado $operator;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('services.n8n.chat_webhook_token', 'test-chat-token');
        Config::set('services.n8n.client_message_webhook', 'https://n8n.test/webhook/chat-outbound');

        DB::purge('sqlite');
        DB::reconnect('sqlite');
        Cache::flush();

        $this->createSchema();

        Gate::before(function ($user, string $ability) {
            return in_array($ability, ['chat.ver', 'chat.responder', 'chat.supervisor'], true) ?: null;
        });

        $this->operator = Empleado::create([
            'nombreemp' => 'Operador Chat',
            'telefonoemp' => '0999999999',
            'usuarioemp' => 'operador-chat',
            'passwordemp' => 'secret',
            'email' => 'operador@example.com',
        ]);

        $this->grantChatPermissions($this->operator);
    }

    public function test_inbound_webhook_creates_conversation_and_message(): void
    {
        $response = $this->postJson('/api/chat/whatsapp/inbound', [
            'token' => 'test-chat-token',
            'canal_user_id' => '593999999999@s.whatsapp.net',
            'telefono' => '593999999999',
            'nombre' => 'Cliente WhatsApp',
            'mensaje' => 'Necesito ayuda',
            'tipo' => 'texto',
            'external_message_id' => 'wamid-001',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.duplicate', false);

        $this->assertDatabaseHas('conversaciones', [
            'canal_principal' => 'whatsapp',
            'estado' => 'nueva',
            'unread_count' => 1,
        ]);

        $this->assertDatabaseHas('mensajes', [
            'tipo_remitente' => 'cliente',
            'contenido' => 'Necesito ayuda',
            'tipo' => 'texto',
            'external_id' => 'wamid-001',
        ]);
    }

    public function test_inbound_duplicate_external_id_is_ignored(): void
    {
        $payload = [
            'token' => 'test-chat-token',
            'canal_user_id' => '593999999999@s.whatsapp.net',
            'telefono' => '593999999999',
            'mensaje' => 'Duplicado',
            'tipo' => 'texto',
            'external_message_id' => 'wamid-dup',
        ];

        $this->postJson('/api/chat/whatsapp/inbound', $payload)->assertCreated();
        $this->postJson('/api/chat/whatsapp/inbound', $payload)
            ->assertOk()
            ->assertJsonPath('data.duplicate', true);

        $this->assertSame(1, Mensaje::query()->where('external_id', 'wamid-dup')->count());
    }

    public function test_operator_opens_whatsapp_chat_panel(): void
    {
        $this->actingAs($this->operator);

        $this->get('/chat/whatsapp')
            ->assertOk()
            ->assertSee('WhatsApp');
    }

    public function test_operator_sends_text_and_updates_conversation_state(): void
    {
        Http::fake([
            'n8n.test/*' => Http::response(['ok' => true], 200),
        ]);

        $conversation = $this->createConversation();

        $this->actingAs($this->operator);

        Livewire::test(WhatsAppHelpdesk::class)
            ->set('activeConversationId', $conversation->idconv)
            ->set('messageText', 'Respuesta operador')
            ->call('sendText')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mensajes', [
            'idconv' => $conversation->idconv,
            'tipo_remitente' => 'empleado',
            'contenido' => 'Respuesta operador',
            'tipo' => 'texto',
        ]);

        $this->assertDatabaseHas('conversaciones', [
            'idconv' => $conversation->idconv,
            'estado' => 'atendiendo',
            'assigned_to' => $this->operator->idemp,
        ]);
    }

    public function test_operator_uploads_image_and_audio(): void
    {
        Http::fake([
            'n8n.test/*' => Http::response(['ok' => true], 200),
        ]);
        Storage::fake('public');

        $conversation = $this->createConversation();

        $this->actingAs($this->operator);

        Livewire::test(WhatsAppHelpdesk::class)
            ->set('activeConversationId', $conversation->idconv)
            ->set('imageUpload', UploadedFile::fake()->image('capture.jpg', 640, 480))
            ->call('sendImage')
            ->assertHasNoErrors();

        Livewire::test(WhatsAppHelpdesk::class)
            ->set('activeConversationId', $conversation->idconv)
            ->set('audioUpload', UploadedFile::fake()->create('voice.mp3', 128, 'audio/mpeg'))
            ->call('sendAudio')
            ->assertHasNoErrors();

        $imageMessage = Mensaje::query()->where('tipo', 'imagen')->latest('idmsg')->firstOrFail();
        $imagePath = str_replace('/storage/', '', parse_url($imageMessage->media_url, PHP_URL_PATH) ?: '');

        Storage::disk('public')->assertExists($imagePath);

        $this->assertDatabaseHas('mensajes', ['tipo' => 'audio']);
    }

    public function test_operator_closes_and_reopens_conversation(): void
    {
        $conversation = $this->createConversation();

        $this->actingAs($this->operator);

        Livewire::test(WhatsAppHelpdesk::class)
            ->set('activeConversationId', $conversation->idconv)
            ->call('closeConversation')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('conversaciones', [
            'idconv' => $conversation->idconv,
            'estado' => 'cerrado',
        ]);

        Livewire::test(WhatsAppHelpdesk::class)
            ->set('activeConversationId', $conversation->idconv)
            ->call('reopenConversation')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('conversaciones', [
            'idconv' => $conversation->idconv,
            'estado' => 'abierto',
        ]);
    }

    public function test_inbound_reopens_closed_conversation_for_same_contact(): void
    {
        $conversation = $this->createConversation();
        $conversation->update([
            'estado' => 'cerrado',
            'closed_at' => now()->subMinute(),
        ]);

        $this->postJson('/api/chat/whatsapp/inbound', [
            'token' => 'test-chat-token',
            'canal_user_id' => '593999999999@s.whatsapp.net',
            'telefono' => '593999999999',
            'mensaje' => 'Vuelvo a escribir',
            'tipo' => 'texto',
            'external_message_id' => 'wamid-reopen-001',
        ])->assertCreated();

        $this->assertSame(1, Conversacion::query()->count());
        $this->assertDatabaseHas('conversaciones', [
            'idconv' => $conversation->idconv,
            'estado' => 'nueva',
            'unread_count' => 2,
        ]);
        $this->assertDatabaseHas('mensajes', [
            'idconv' => $conversation->idconv,
            'contenido' => 'Vuelvo a escribir',
            'external_id' => 'wamid-reopen-001',
        ]);
    }

    private function createConversation(): Conversacion
    {
        $cliente = Cliente::create([
            'nombrecli' => 'Cliente Chat',
            'telefonocli' => '593999999999',
            'email' => 'cliente-chat@example.com',
            'password' => 'Clave12@',
            'saldo' => 0,
        ]);

        $contact = ChatContactoCanal::create([
            'canal' => 'whatsapp',
            'canal_user_id' => '593999999999@s.whatsapp.net',
            'telefono_normalizado' => '593999999999',
            'nombre_canal' => 'Cliente Chat',
            'idcli' => $cliente->idcli,
            'estado_relacion' => 'cliente',
            'metadata' => [
                'instance' => 'Streamify Azul',
                'apikey' => 'test-key',
                'server_url' => 'https://evo.test',
            ],
        ]);

        $conversation = Conversacion::create([
            'idcli' => $cliente->idcli,
            'canal_principal' => 'whatsapp',
            'canal_contacto_id' => $contact->id,
            'estado' => 'nueva',
            'ultima_actividad' => now(),
            'last_message_at' => now(),
            'mensajes_no_leidos' => 1,
            'unread_count' => 1,
            'prioridad' => 'normal',
            'metadata' => [
                'tags' => ['qa'],
                'instance' => 'Streamify Azul',
                'apikey' => 'test-key',
                'server_url' => 'https://evo.test',
            ],
        ]);

        Mensaje::create([
            'idconv' => $conversation->idconv,
            'tipo_remitente' => 'cliente',
            'idcli' => $cliente->idcli,
            'contenido' => 'Hola',
            'tipo_contenido' => 'texto',
            'tipo' => 'texto',
            'leido' => false,
        ]);

        return $conversation;
    }

    private function createSchema(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id('idemp');
            $table->string('nombreemp');
            $table->string('telefonoemp')->nullable();
            $table->string('usuarioemp')->unique();
            $table->string('passwordemp');
            $table->string('foto_url')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id('idcli');
            $table->string('nombrecli', 50);
            $table->string('telefonocli', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->string('idven')->primary();
            $table->unsignedBigInteger('idemp')->nullable();
            $table->unsignedBigInteger('idcli')->nullable();
            $table->dateTime('fechaven')->nullable();
            $table->decimal('totalpagoven', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('chat_contactos_canal', function (Blueprint $table) {
            $table->id();
            $table->string('canal');
            $table->string('canal_user_id');
            $table->string('telefono_normalizado')->nullable();
            $table->string('nombre_canal')->nullable();
            $table->unsignedBigInteger('idcli')->nullable();
            $table->string('estado_relacion')->default('lead');
            $table->string('origen')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('conversaciones', function (Blueprint $table) {
            $table->id('idconv');
            $table->unsignedBigInteger('idcli')->nullable();
            $table->string('canal_principal')->nullable();
            $table->unsignedBigInteger('canal_contacto_id')->nullable();
            $table->string('origen')->nullable();
            $table->string('subagente_codigo')->nullable();
            $table->string('estado')->default('nueva');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('operator_typing_id')->nullable();
            $table->timestamp('operator_typing_at')->nullable();
            $table->unsignedBigInteger('ultimo_idemp')->nullable();
            $table->timestamp('ultima_actividad')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedInteger('mensajes_no_leidos')->default(0);
            $table->unsignedInteger('unread_count')->default(0);
            $table->string('prioridad')->default('normal');
            $table->boolean('requiere_humano')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('mensajes', function (Blueprint $table) {
            $table->id('idmsg');
            $table->unsignedBigInteger('idconv');
            $table->string('tipo_remitente');
            $table->unsignedBigInteger('idcli')->nullable();
            $table->unsignedBigInteger('idemp')->nullable();
            $table->text('contenido')->default('');
            $table->string('tipo_contenido')->default('texto');
            $table->string('tipo')->nullable();
            $table->text('archivo_url')->nullable();
            $table->text('media_url')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('external_id')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('leido')->default(false);
            $table->timestamp('leido_at')->nullable();
            $table->boolean('respondido_por_ai')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_mensajes_canal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idmsg')->nullable();
            $table->unsignedBigInteger('idconv')->nullable();
            $table->unsignedBigInteger('contacto_canal_id')->nullable();
            $table->string('canal');
            $table->string('direccion');
            $table->string('external_message_id')->nullable();
            $table->string('external_thread_id')->nullable();
            $table->string('external_status')->nullable();
            $table->string('media_id')->nullable();
            $table->text('media_url')->nullable();
            $table->string('media_mime_type')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_whatsapp_channels', function (Blueprint $table) {
            $table->id();
            $table->string('instance_name');
            $table->string('display_name')->nullable();
            $table->text('api_key')->nullable();
            $table->string('server_url')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('outbound_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        foreach ([
            'chat_allow_text' => ['1', 'bool'],
            'chat_allow_image' => ['1', 'bool'],
            'chat_allow_audio' => ['1', 'bool'],
            'chat_allow_document' => ['0', 'bool'],
            'chat_allow_location' => ['0', 'bool'],
            'chat_allow_template' => ['0', 'bool'],
            'chat_realtime_enabled' => ['1', 'bool'],
            'chat_auto_assign' => ['0', 'bool'],
            'chat_max_upload_mb' => ['10', 'int'],
        ] as $key => [$value, $type]) {
            DB::table('chat_settings')->insert([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function grantChatPermissions(Empleado $operator): void
    {
        $now = now();

        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Administrador',
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $permissions = [
            'chat.ver',
            'chat.responder',
            'chat.supervisor',
            'dashboard',
            'cuentas',
            'empleados',
            'roles.index',
            'bancos.index',
            'empleado.recargas.index',
            'costos',
            'gastos',
            'ventas',
            'empleado.pedidos.index',
            'clientes',
            'gestion',
            'productos.index',
            'usuarios',
            'mantenimientos',
            'soportes',
            'servicios',
            'proveedores',
            'valores',
            'mails.index',
            'historial',
        ];

        foreach ($permissions as $index => $permission) {
            DB::table('permissions')->insert([
                'id' => $index + 1,
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('role_has_permissions')->insert([
                'permission_id' => $index + 1,
                'role_id' => 1,
            ]);
        }

        DB::table('model_has_roles')->insert([
            'role_id' => 1,
            'model_type' => Empleado::class,
            'model_id' => $operator->idemp,
        ]);
    }
}
