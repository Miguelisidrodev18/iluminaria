<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar tablas dependientes primero (FK constraints)
        Schema::dropIfExists('traslado_imeis');
        Schema::dropIfExists('imeis');

        // 2. Quitar imei_id de movimientos_inventario
        if (Schema::hasColumn('movimientos_inventario', 'imei_id')) {
            Schema::table('movimientos_inventario', function (Blueprint $table) {
                $table->dropForeign(['imei_id']);
                $table->dropColumn('imei_id');
            });
        }

        // 3. Quitar imei_id de detalle_ventas
        if (Schema::hasColumn('detalle_ventas', 'imei_id')) {
            Schema::table('detalle_ventas', function (Blueprint $table) {
                $table->dropForeign(['imei_id']);
                $table->dropColumn('imei_id');
            });
        }

        // 4. Quitar detalle_compra_id, modelo_id, color_id de detalle_compras
        //    (campos específicos de celulares; variante_id se mantiene)
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

        // 5. Quitar tipo_inventario='serie' ya no tiene sentido; se mantiene el campo
        //    pero se cambia el enum para reflejar luminarias
        //    (se hace en FASE 3 junto con productos)
    }

    public function down(): void
    {
        // Recrear columna imei_id en detalle_ventas
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('imei_id')->nullable()->after('variante_id');
        });

        // Recrear columna imei_id en movimientos_inventario
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->unsignedBigInteger('imei_id')->nullable()->after('almacen_id');
        });
    }
};
