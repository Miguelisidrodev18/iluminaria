<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('nombre_kyrios', 255)->nullable()->unique()->after('nombre')
                  ->comment('Nombre interno del sistema Kyrios');

            // Deprecar FK de modelo_id: quitar constraint pero mantener columna
            $table->dropForeign(['modelo_id']);
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('nombre_kyrios');
            $table->foreign('modelo_id')->references('id')->on('modelos')->nullOnDelete();
        });
    }
};
