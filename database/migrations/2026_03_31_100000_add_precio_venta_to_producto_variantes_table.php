<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_variantes', function (Blueprint $table) {
            $table->decimal('precio_venta', 12, 2)
                  ->nullable()
                  ->after('sobreprecio')
                  ->comment('Precio de venta directo de la variante (nulo = sin precio fijo)');

            $table->char('moneda', 3)
                  ->default('PEN')
                  ->after('precio_venta')
                  ->comment('PEN o USD');
        });
    }

    public function down(): void
    {
        Schema::table('producto_variantes', function (Blueprint $table) {
            $table->dropColumn(['precio_venta', 'moneda']);
        });
    }
};
