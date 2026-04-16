<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('almacenes', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('sucursales')
                  ->onDelete('set null');
        });

        // Vincular almacenes existentes a su sucursal según sucursales.almacen_id
        \DB::statement('
            UPDATE almacenes a
            INNER JOIN sucursales s ON s.almacen_id = a.id
            SET a.sucursal_id = s.id
        ');
    }

    public function down(): void
    {
        Schema::table('almacenes', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
