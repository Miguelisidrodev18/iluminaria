<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_variantes', function (Blueprint $table) {
            // Tamaño nominal de la variante
            $table->string('tamano', 100)->nullable()->after('especificacion')
                  ->comment('Ej: "600x600mm", "Circular 4\"", "30x120cm"');

            // Reemplazar el índice anterior (no-único) por uno único
            // que incluya tamano para evitar variantes duplicadas
            $table->dropIndex('producto_variantes_producto_id_color_id_capacidad_index');
            $table->unique(
                ['producto_id', 'tamano', 'especificacion', 'color_id'],
                'uq_variante_tamano_spec_color'
            );
        });
    }

    public function down(): void
    {
        Schema::table('producto_variantes', function (Blueprint $table) {
            $table->dropUnique('uq_variante_tamano_spec_color');
            $table->dropColumn('tamano');
            $table->index(['producto_id', 'color_id', 'especificacion']);
        });
    }
};
