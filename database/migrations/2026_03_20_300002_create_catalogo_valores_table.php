<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de valores predefinidos para atributos de tipo select/multiselect.
 * Ej: Para el atributo "Socket" → E27, GU10, E14, B22...
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_valores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atributo_id')
                  ->constrained('catalogo_atributos')
                  ->cascadeOnDelete();
            $table->string('valor', 150);
            $table->string('etiqueta', 150)->nullable()
                  ->comment('Etiqueta display si difiere del valor. Ej: valor=3000K, etiqueta=Cálido (3000K)');
            $table->string('color_hex', 7)->nullable()
                  ->comment('Para colores/acabados: código hex de muestra visual');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['atributo_id', 'valor']);
            $table->index(['atributo_id', 'orden', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_valores');
    }
};
