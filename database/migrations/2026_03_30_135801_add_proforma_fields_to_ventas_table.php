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
        Schema::table('ventas', function (Blueprint $table) {
            $table->enum('moneda', ['PEN', 'USD'])->default('PEN')->after('observaciones');
            $table->decimal('tipo_cambio', 10, 4)->default(1.0000)->after('moneda');
            $table->string('contacto', 150)->nullable()->after('tipo_cambio');
            $table->unsignedInteger('vigencia_dias')->default(5)->after('contacto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['moneda', 'tipo_cambio', 'contacto', 'vigencia_dias']);
        });
    }
};
