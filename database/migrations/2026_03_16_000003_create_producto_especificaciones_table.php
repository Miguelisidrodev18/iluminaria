<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_especificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            // Datos eléctricos
            $table->string('potencia')->nullable();          // Ej: "18W", "2x36W"
            $table->string('lumenes')->nullable();           // Ej: "1800lm"
            $table->string('voltaje')->nullable();           // Ej: "220V", "100-240V"
            $table->string('temperatura_color')->nullable(); // Ej: "3000K", "4000K", "6500K"
            $table->unsignedTinyInteger('cri')->nullable();  // Índice de reproducción cromática 0-100
            $table->string('ip')->nullable();                // Protección: "IP65", "IP20"
            $table->string('ik')->nullable();                // Protección mecánica: "IK08"
            $table->string('angulo_apertura')->nullable();   // Ej: "36°", "120°"

            // Driver y regulación
            $table->string('driver')->nullable();            // Ej: "Integrado", "Externo Meanwell"
            $table->boolean('regulable')->default(false);    // Dimeable
            $table->string('protocolo_regulacion')->nullable(); // Ej: "0-10V", "DALI", "Triac"

            // Lámpara
            $table->string('socket')->nullable();            // Ej: "E27", "GU10", "G13"
            $table->unsignedTinyInteger('numero_lamparas')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_especificaciones');
    }
};
