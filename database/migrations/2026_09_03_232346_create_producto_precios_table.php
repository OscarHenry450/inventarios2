<?php

use App\Models\Producto;
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
        Schema::create('producto_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Producto::class)->constrained();
            $table->foreignIdFor(\App\Models\Cantidade::class)->constrained();


            $table->decimal('precio_unitario', 10, 2);

            $table->boolean('activo')->default(true);

            $table->unique([
                'producto_id',
                'cantidade_id'
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_precios');
    }
};
