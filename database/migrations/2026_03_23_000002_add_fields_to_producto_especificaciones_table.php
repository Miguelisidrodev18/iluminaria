<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_especificaciones', function (Blueprint $table) {
            // Tipo de fuente de luz
            $table->string('tipo_fuente', 50)->nullable()->after('protocolo_regulacion')
                  ->comment('Ej: LED, Fluorescente, Halógena, HID');

            // Tipo de salida de luz
            $table->string('salida_luz', 50)->nullable()->after('tipo_fuente')
                  ->comment('Directa, Indirecta, Mixta');

            // Nivel de potencia (categoría)
            $table->string('nivel_potencia', 50)->nullable()->after('salida_luz')
                  ->comment('Baja, Media, Alta');

            // Fotometría: eficacia luminosa (lm/W)
            $table->decimal('eficacia_luminosa', 8, 2)->nullable()->after('nivel_potencia')
                  ->comment('Relación lm/W — columna W_lumenes del Excel');

            // Fotometría: lúmenes nominales (catálogo fabricante)
            $table->decimal('nominal_lumenes', 10, 2)->nullable()->after('eficacia_luminosa')
                  ->comment('Lúmenes según catálogo del fabricante');

            // Fotometría: lúmenes reales (medición)
            $table->decimal('real_lumenes', 10, 2)->nullable()->after('nominal_lumenes')
                  ->comment('Lúmenes medidos en laboratorio');

            // Tonalidad de color (categoría)
            $table->string('tonalidad_luz', 30)->nullable()->after('real_lumenes')
                  ->comment('Cálido, Neutro, Frío, Bicolor');

            // Vida útil (si no existe ya)
            if (!Schema::hasColumn('producto_especificaciones', 'vida_util_horas')) {
                $table->unsignedInteger('vida_util_horas')->nullable()->after('tonalidad_luz')
                      ->comment('Vida útil estimada en horas, ej: 50000');
            }
        });
    }

    public function down(): void
    {
        Schema::table('producto_especificaciones', function (Blueprint $table) {
            $columnas = [
                'tipo_fuente', 'salida_luz', 'nivel_potencia',
                'eficacia_luminosa', 'nominal_lumenes', 'real_lumenes',
                'tonalidad_luz',
            ];
            if (Schema::hasColumn('producto_especificaciones', 'vida_util_horas')) {
                $columnas[] = 'vida_util_horas';
            }
            $table->dropColumn($columnas);
        });
    }
};
