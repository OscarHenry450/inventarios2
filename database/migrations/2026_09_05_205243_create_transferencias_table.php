<?php

use App\Models\Producto;
use App\Models\Ubicacione;
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
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50)->unique();

            $table->foreignIdfor(Ubicacione::class,'ubicacion_origen_id')->constrained();

            $table->foreignIdFor(Ubicacione::class,'ubicacion_destino_id')->constrained();

            $table->foreignIdFor(\App\Models\User::class)->constrained();

            $table->foreignIdfor(\App\Models\User::class,'usuario_recibe_id')->constrained();

            $table->enum('estado', [
                'pendiente',
                'preparando',
                'en_transito',
                'completada',
                'cancelada',
            ])->default('pendiente');

            $table->dateTime('fecha_solicitud')->nullable();
            $table->dateTime('fecha_envio')->nullable();
            $table->dateTime('fecha_recepcion')->nullable();

            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencias');
    }
};
