<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_dimensiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            // Todas las dimensiones en mm para uniformidad
            $table->decimal('alto', 8, 2)->nullable();
            $table->decimal('ancho', 8, 2)->nullable();
            $table->decimal('diametro', 8, 2)->nullable();       // Para downlights, circulares
            $table->decimal('lado', 8, 2)->nullable();            // Para cuadrados
            $table->decimal('profundidad', 8, 2)->nullable();
            $table->decimal('alto_suspendido', 8, 2)->nullable(); // Longitud del cable/tubo de suspensión
            $table->decimal('diametro_agujero', 8, 2)->nullable(); // Diámetro de corte en techo

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_dimensiones');
    }
};
