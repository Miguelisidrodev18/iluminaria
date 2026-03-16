<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Desactivar FK checks para poder dropear tablas con dependencias
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // 1. Eliminar tablas IMEI completas
            Schema::dropIfExists('traslado_imeis');
            Schema::dropIfExists('imeis');

            // 2. Quitar imei_id de movimientos_inventario
            if (Schema::hasColumn('movimientos_inventario', 'imei_id')) {
                Schema::table('movimientos_inventario', function (Blueprint $table) {
                    try { $table->dropForeign(['imei_id']); } catch (\Throwable) {}
                    $table->dropColumn('imei_id');
                });
            }

            // 3. Quitar imei_id de detalle_ventas
            if (Schema::hasColumn('detalle_ventas', 'imei_id')) {
                Schema::table('detalle_ventas', function (Blueprint $table) {
                    try { $table->dropForeign(['imei_id']); } catch (\Throwable) {}
                    $table->dropColumn('imei_id');
                });
            }

            // 4. Quitar columnas específicas de celulares en detalle_compras
            Schema::table('detalle_compras', function (Blueprint $table) {
                $columns = Schema::getColumnListing('detalle_compras');

                if (in_array('modelo_id', $columns)) {
                    try { $table->dropForeign(['modelo_id']); } catch (\Throwable) {}
                    $table->dropColumn('modelo_id');
                }
                if (in_array('color_id', $columns)) {
                    try { $table->dropForeign(['color_id']); } catch (\Throwable) {}
                    $table->dropColumn('color_id');
                }
                if (in_array('codigo_barras', $columns)) {
                    $table->dropColumn('codigo_barras');
                }
            });

        } finally {
            // Siempre reactivar FK checks, incluso si algo falla
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('imei_id')->nullable()->after('variante_id');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->unsignedBigInteger('imei_id')->nullable()->after('almacen_id');
        });
    }
};
