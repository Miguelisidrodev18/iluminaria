<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_variantes', function (Blueprint $table) {
            // Renombrar capacidad → especificacion (campo genérico)
            // Ejemplos: "18W", "3000K", "IP65", "Version A"
            $table->renameColumn('capacidad', 'especificacion');
        });
    }

    public function down(): void
    {
        Schema::table('producto_variantes', function (Blueprint $table) {
            $table->renameColumn('especificacion', 'capacidad');
        });
    }
};
