<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->boolean('tiene_variantes')
                  ->default(false)
                  ->after('tipo_inventario')
                  ->comment('true = producto gestiona variantes luminaria; false = producto simple');
        });

        // Sincronizar con la realidad: si ya tiene variantes activas, marcar el flag
        DB::statement("
            UPDATE productos p
            SET tiene_variantes = 1
            WHERE EXISTS (
                SELECT 1 FROM producto_variantes pv
                WHERE pv.producto_id = p.id AND pv.estado = 'activo'
            )
        ");
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('tiene_variantes');
        });
    }
};
