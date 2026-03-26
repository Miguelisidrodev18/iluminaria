<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar duplicados: conservar el de menor id por nombre
        DB::statement('
            DELETE FROM tipos_proyecto
            WHERE id NOT IN (
                SELECT min_id FROM (
                    SELECT MIN(id) AS min_id FROM tipos_proyecto GROUP BY nombre
                ) AS tmp
            )
        ');

        Schema::table('tipos_proyecto', function (Blueprint $table) {
            $table->unique('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('tipos_proyecto', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
        });
    }
};
