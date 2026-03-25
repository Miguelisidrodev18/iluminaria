<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_dimensiones', function (Blueprint $table) {
            // Dimensiones del agujero/corte rectangular (para empotrables cuadrados)
            $table->decimal('ancho_agujero', 8, 2)->nullable()->after('diametro_agujero')
                  ->comment('Ancho del corte en techo para empotrables rectangulares (mm)');

            $table->decimal('profundidad_agujero', 8, 2)->nullable()->after('ancho_agujero')
                  ->comment('Profundidad del corte/hueco de instalación (mm)');

            // NOTA: el campo 'peso' del modelo está aquí pero NO en la tabla.
            // El peso va exclusivamente en tabla producto_embalaje.
            // No se agrega peso aquí.
        });
    }

    public function down(): void
    {
        Schema::table('producto_dimensiones', function (Blueprint $table) {
            $table->dropColumn(['ancho_agujero', 'profundidad_agujero']);
        });
    }
};
