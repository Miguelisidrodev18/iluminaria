<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Identificadores Kyrios
            $table->string('codigo_kyrios')->nullable()->unique()->after('codigo');
            $table->string('codigo_fabrica')->nullable()->after('codigo_kyrios');

            // Origen / línea comercial
            $table->string('procedencia')->nullable()->after('descripcion'); // Ej: "China", "Italia", "España"
            $table->string('linea')->nullable()->after('procedencia');       // Ej: "Premium", "Básica", "Arquitectónica"

            // Documentación técnica
            $table->string('ficha_tecnica_url')->nullable()->after('imagen');

            // Notas internas
            $table->text('observaciones')->nullable()->after('ficha_tecnica_url');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'codigo_kyrios',
                'codigo_fabrica',
                'procedencia',
                'linea',
                'ficha_tecnica_url',
                'observaciones',
            ]);
        });
    }
};
