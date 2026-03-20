<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('tipo_producto_id')
                  ->nullable()
                  ->after('tipo_inventario')
                  ->constrained('tipos_producto')
                  ->nullOnDelete();

            $table->foreignId('tipo_luminaria_id')
                  ->nullable()
                  ->after('tipo_producto_id')
                  ->constrained('tipos_luminaria')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['tipo_producto_id']);
            $table->dropForeign(['tipo_luminaria_id']);
            $table->dropColumn(['tipo_producto_id', 'tipo_luminaria_id']);
        });
    }
};
