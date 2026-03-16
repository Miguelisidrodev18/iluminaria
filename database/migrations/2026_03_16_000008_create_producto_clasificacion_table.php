<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_clasificacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            $table->enum('uso', [
                'interior',
                'exterior',
                'interior_exterior',
            ])->default('interior');

            $table->enum('tipo_instalacion', [
                'empotrado',
                'superficie',
                'suspendido',
                'poste',
                'carril',
                'portatil',
            ])->nullable();

            $table->string('estilo')->nullable(); // Ej: "Moderno", "Industrial", "Clásico"

            $table->foreignId('tipo_proyecto_id')
                  ->nullable()
                  ->constrained('tipos_proyecto')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_clasificacion');
    }
};
