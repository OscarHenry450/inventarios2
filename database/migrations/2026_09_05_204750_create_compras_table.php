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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();

            $table->string('numero')->unique();

            $table->string('proveedor');

            $table->foreignIdFor(\App\Models\Ubicacione::class)->constrained();

            $table->foreignIdFor(\App\Models\User::class)->constrained();

            $table->dateTime('fecha');

            $table->string('numero_documento')->nullable();

            $table->decimal('total', 12, 2)->default(0);

            $table->enum('estado', [
                'pendiente',
                'confirmada',
                'cancelada',
            ])->default('pendiente');

            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
