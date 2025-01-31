<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('pais',30)->default('Ecuador')->after('telefonocli');
            $table->string('email')->unique()->nullable()->after('nombrecli'); // Campo email único pero inicialmente nullable
            $table->string('password')->nullable()->after('email'); // Campo password inicialmente nullable
            $table->decimal('saldo', 15, 2)->default(0)->after('telefonocli'); // Campo saldo inicialmente nullable
            $table->rememberToken()->nullable()->after('password'); // Campo remember_token inicialmente nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['email', 'password', 'saldo', 'remember_token']);
        });
    }
};
