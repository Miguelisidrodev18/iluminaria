<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_materiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            // Materiales del cuerpo/estructura
            $table->string('material_1')->nullable();        // Ej: "Aluminio fundido"
            $table->string('material_2')->nullable();        // Ej: "Vidrio templado"

            // Acabados
            $table->string('color_acabado_1')->nullable();   // Ej: "Blanco RAL 9003"
            $table->string('color_acabado_2')->nullable();   // Ej: "Negro mate"

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_materiales');
    }
};
