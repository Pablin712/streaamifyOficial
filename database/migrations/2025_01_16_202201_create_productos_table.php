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
        Schema::create('tipos_producto', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->string('nombre', 50)->unique(); // Nombre del tipo de producto
            $table->text('descripcion')->nullable(); // Descripción del tipo
            $table->timestamps(); // created_at y updated_at
        });
        Schema::create('categorias', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->string('nombre', 50)->unique(); // Nombre de la categoría
            $table->text('descripcion')->nullable(); // Descripción de la categoría
            $table->timestamps(); // created_at y updated_at
        });
        Schema::create('productos', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->string('codigopro', 20)->unique(); // Código único del producto
            $table->string('nombrepro', 100); // Nombre del producto
            $table->decimal('preciopro', 10, 2); // Precio del producto
            $table->unsignedTinyInteger('estrellaspro')->default(0); // Estrellas (1 a 5)
            $table->text('descripcionpro')->nullable(); // Descripción del producto
            $table->string('foto')->nullable(); // URL de la foto
            $table->foreignId('tipo_producto_id')->constrained('tipos_producto'); // Llave foránea a tipos_producto
            $table->foreignId('categoria_id')->constrained('categorias'); // Llave foránea a categorias
            $table->boolean('activo')->default(true); // Producto activo/inactivo
            $table->timestamps(); // created_at y updated_at
        });
        Schema::create('detalle_productos', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->foreignId('producto_id')->constrained('productos'); // Llave foránea a productos
            $table->string('idser', 10); // ID del servicio (varchar)
            $table->string('descripcion', 255); // Descripción del detalle
            $table->unsignedInteger('meses'); // Tiempo de uso del servicio en meses
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_producto');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('detalle_productos');
    }
};
