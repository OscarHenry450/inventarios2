<?php

use App\Models\Producto;
use App\Models\Transferencia;
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
        Schema::create('detalle_transferencias', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Transferencia::class)->constrained();

            $table->foreignIdFor(Producto::class)->constrained();

            $table->decimal('cantidad_solicitada', 12, 2);

            $table->decimal('cantidad_enviada', 12, 2)
                ->nullable();

            $table->decimal('cantidad_recibida', 12, 2)
                ->nullable();

            $table->timestamps();

            $table->unique([
                'transferencia_id',
                'producto_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_transferencias');
    }
};
