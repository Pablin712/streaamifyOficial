<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('bancos', function (Blueprint $table) {
            $table->decimal('monto', 15, 2)->default(0)->after('idban');
        });
    }
    public function down() {
        Schema::table('bancos', function (Blueprint $table) {
            $table->dropColumn('monto');
        });
    }
};
