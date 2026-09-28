<?php

use App\Models\Producto;
use App\Models\ProductoPrecio;
use App\Models\Venta;
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
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdfor(Venta::class)->constrained();

            $table->foreignIdfor(Producto::class)->constrained();

            $table->foreignIdfor(ProductoPrecio::class)->constrained();

            // Cantidad REAL de unidades vendidas
            $table->decimal('cantidad', 12, 2);

            // Precio elegido por unidad
            $table->decimal('precio_unitario', 12, 2);

            $table->decimal('descuento', 12, 2)->default(0);

            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
