<?php

use App\Models\Marca;
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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->foreignIdFor(Marca::class)->constrained();
            $table->string('modelo', 50)->nullable();
            $table->string(column: 'foto')->nullable(); // Cambiado a string para almacenar la ruta de la imagen
            $table->boolean('estado')->default(true); // Agregar columna 'estado'
            $table->integer('cantidad_caja')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
