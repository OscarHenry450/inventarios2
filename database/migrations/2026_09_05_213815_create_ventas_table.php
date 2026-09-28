<?php

use App\Models\Cliente;
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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50)->nullable()->unique();

            $table->foreignIdFor(\App\Models\Ubicacione::class)->constrained();
            $table->foreignIdfor(Cliente::class)->nullable()->constrained();

            $table->foreignIdfor(\App\Models\User::class)->constrained();

            $table->enum('estado', [
                'pendiente',
                'reservada',
                'vendida',
                'cancelada',
            ])->default('pendiente');

            $table->dateTime('fecha')->nullable();

            $table->dateTime('fecha_reserva')->nullable();

            $table->dateTime('fecha_vencimiento_reserva')->nullable();

            $table->dateTime('fecha_venta')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
