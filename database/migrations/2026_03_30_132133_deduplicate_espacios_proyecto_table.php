<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar duplicados: para cada (tipo_proyecto_id, nombre) conservar solo el menor id
        DB::statement('
            DELETE ep1 FROM espacios_proyecto ep1
            INNER JOIN espacios_proyecto ep2
                ON ep1.tipo_proyecto_id = ep2.tipo_proyecto_id
               AND LOWER(TRIM(ep1.nombre)) = LOWER(TRIM(ep2.nombre))
               AND ep1.id > ep2.id
        ');

        // Unique constraint para evitar futuros duplicados
        Schema::table('espacios_proyecto', function (Blueprint $table) {
            $table->unique(['tipo_proyecto_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::table('espacios_proyecto', function (Blueprint $table) {
            $table->dropUnique(['tipo_proyecto_id', 'nombre']);
        });
    }
};
