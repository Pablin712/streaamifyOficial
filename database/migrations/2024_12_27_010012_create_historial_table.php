<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial', function (Blueprint $table) {
            $table->id(); // ID único para cada registro
            $table->string('accion'); 
            $table->text('descripcion')->nullable(); 
            $table->string('realizado_por'); // Almacena el nombre o ID del usuario
            $table->timestamp('fecha')->useCurrent(); // Fecha y hora de la acción
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historial');
    }
}
