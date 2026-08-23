<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('codigos')) {
            return;
        }

        $missingColumns = [
            'idcli' => !Schema::hasColumn('codigos', 'idcli'),
            'idcue' => !Schema::hasColumn('codigos', 'idcue'),
            'usuariocue' => !Schema::hasColumn('codigos', 'usuariocue'),
            'idser' => !Schema::hasColumn('codigos', 'idser'),
            'instance' => !Schema::hasColumn('codigos', 'instance'),
            'apikey' => !Schema::hasColumn('codigos', 'apikey'),
            'usuarios_habilitados' => !Schema::hasColumn('codigos', 'usuarios_habilitados'),
        ];

        if (in_array(true, $missingColumns, true)) {
            Schema::table('codigos', function (Blueprint $table) use ($missingColumns) {
                if ($missingColumns['idcli']) {
                $table->unsignedBigInteger('idcli')->nullable()->after('telefono');
                }

                if ($missingColumns['idcue']) {
                    $table->string('idcue', 50)->nullable()->after('idcli');
                }

                if ($missingColumns['usuariocue']) {
                    $table->string('usuariocue', 191)->nullable()->after('idcue');
                }

                if ($missingColumns['idser']) {
                    $table->string('idser', 20)->nullable()->after('usuariocue');
                }

                if ($missingColumns['instance']) {
                    $table->string('instance', 50)->nullable()->after('idser');
                }

                if ($missingColumns['apikey']) {
                    $table->string('apikey', 50)->nullable()->after('instance');
                }

                if ($missingColumns['usuarios_habilitados']) {
                    $table->unsignedInteger('usuarios_habilitados')->default(0)->after($missingColumns['apikey'] ? 'apikey' : ($missingColumns['instance'] ? 'instance' : 'idser'));
                }
            });
        }

        Schema::table('codigos', function (Blueprint $table) {
            if (!$this->indexExists('codigos', 'codigos_lookup_idx')) {
                $table->index(['telefono', 'usuariocue', 'idser', 'estado', 'created_at'], 'codigos_lookup_idx');
            }

            if (!$this->indexExists('codigos', 'codigos_cliente_idx')) {
                $table->index(['idcli', 'created_at'], 'codigos_cliente_idx');
            }

            if (!$this->indexExists('codigos', 'codigos_cuenta_idx')) {
                $table->index(['idcue', 'created_at'], 'codigos_cuenta_idx');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('codigos')) {
            return;
        }

        Schema::table('codigos', function (Blueprint $table) {
            try {
                $table->dropIndex('codigos_lookup_idx');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex('codigos_cliente_idx');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex('codigos_cuenta_idx');
            } catch (\Throwable $e) {
            }
        });

        Schema::table('codigos', function (Blueprint $table) {
            if (Schema::hasColumn('codigos', 'usuarios_habilitados')) {
                $table->dropColumn('usuarios_habilitados');
            }

            if (Schema::hasColumn('codigos', 'apikey')) {
                $table->dropColumn('apikey');
            }

            if (Schema::hasColumn('codigos', 'instance')) {
                $table->dropColumn('instance');
            }

            if (Schema::hasColumn('codigos', 'idser')) {
                $table->dropColumn('idser');
            }

            if (Schema::hasColumn('codigos', 'usuariocue')) {
                $table->dropColumn('usuariocue');
            }

            if (Schema::hasColumn('codigos', 'idcue')) {
                $table->dropColumn('idcue');
            }

            if (Schema::hasColumn('codigos', 'idcli')) {
                $table->dropColumn('idcli');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]))->isNotEmpty();
    }
};
