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
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('tipos_producto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigopro', 20)->unique();
            $table->string('nombrepro', 100);
            $table->decimal('preciopro', 10, 2);
            $table->unsignedTinyInteger('estrellaspro')->default(0);
            $table->text('descripcionpro')->nullable();
            $table->string('foto', 255)->nullable();
            $table->unsignedBigInteger('tipo_producto_id');
            $table->unsignedBigInteger('categoria_id');
            $table->boolean('activo')->default(1);
            $table->timestamps();
            
            $table->foreign('tipo_producto_id')->references('id')->on('tipos_producto');
            $table->foreign('categoria_id')->references('id')->on('categorias');
        });

        Schema::create('detalle_productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->string('idser', 10);
            $table->string('descripcion', 255);
            $table->unsignedInteger('meses');
            $table->timestamps();
            
            $table->foreign('producto_id')->references('id')->on('productos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_productos');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('tipos_producto');
        Schema::dropIfExists('categorias');
    }
};