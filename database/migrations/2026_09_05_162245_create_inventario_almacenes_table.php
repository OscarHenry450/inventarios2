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
        Schema::create('inventario_almacenes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Producto::class)->constrained();

            $table->foreignIdFor(\App\Models\Ubicacione::class)->constrained();

            $table->decimal('stock', 12, 2)->default(0);
            $table->decimal('stock_reservado', 12, 2)->default(0);

            $table->decimal('stock_minimo', 12, 2)
                ->default(0);

            $table->unique([
                'producto_id',
                'Ubicacione_id',
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_almacenes');
    }
};
