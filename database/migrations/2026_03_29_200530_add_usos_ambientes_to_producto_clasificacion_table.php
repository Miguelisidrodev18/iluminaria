<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_clasificacion', function (Blueprint $table) {
            // Usos del producto: interiores, exteriores, alumbrado_publico, piscina
            $table->json('usos')->nullable()->after('producto_id');
            // Ambientes seleccionados (espacios_proyecto IDs) por tipo de proyecto
            $table->json('ambientes')->nullable()->after('usos');
        });
    }

    public function down(): void
    {
        Schema::table('producto_clasificacion', function (Blueprint $table) {
            $table->dropColumn(['usos', 'ambientes']);
        });
    }
};
