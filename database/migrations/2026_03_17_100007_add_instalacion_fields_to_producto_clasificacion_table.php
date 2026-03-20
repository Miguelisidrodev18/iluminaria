<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La tabla producto_clasificacion conserva los campos de instalación
     * (tipo_instalacion, estilo, tipo_proyecto_id). Solo renombramos para
     * dejar claro que ya no almacena 'uso'.
     * No renombramos la tabla para no romper el modelo existente ProductoClasificacion.
     */
    public function up(): void
    {
        // La tabla ya tiene tipo_instalacion, estilo, tipo_proyecto_id
        // Solo agregamos los campos técnicos que estaban en el partial
        // pero no existían en BD: vida_util_horas, peso, material_terciario
        Schema::table('producto_especificaciones', function (Blueprint $table) {
            $table->unsignedInteger('vida_util_horas')->nullable()->after('numero_lamparas')
                  ->comment('Vida útil en horas (Ej: 25000, 50000)');
        });

        Schema::table('producto_dimensiones', function (Blueprint $table) {
            $table->decimal('peso', 8, 3)->nullable()->after('diametro_agujero')
                  ->comment('Peso en kilogramos');
        });

        Schema::table('producto_materiales', function (Blueprint $table) {
            $table->string('material_terciario')->nullable()->after('material_2')
                  ->comment('Tercer material (Ej: Policarbonato, Acrílico)');
        });
    }

    public function down(): void
    {
        Schema::table('producto_especificaciones', function (Blueprint $table) {
            $table->dropColumn('vida_util_horas');
        });

        Schema::table('producto_dimensiones', function (Blueprint $table) {
            $table->dropColumn('peso');
        });

        Schema::table('producto_materiales', function (Blueprint $table) {
            $table->dropColumn('material_terciario');
        });
    }
};
