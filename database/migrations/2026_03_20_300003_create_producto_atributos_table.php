<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla pivot: valores de atributos asignados a cada producto.
 * - Para select: una fila con valor_id
 * - Para multiselect: múltiples filas con distintos valor_id
 * - Para number/text: una fila con valor_texto
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_atributos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->cascadeOnDelete();
            $table->foreignId('atributo_id')
                  ->constrained('catalogo_atributos')
                  ->cascadeOnDelete();
            $table->foreignId('valor_id')
                  ->nullable()
                  ->constrained('catalogo_valores')
                  ->nullOnDelete()
                  ->comment('Para select/multiselect: ID del valor elegido');
            $table->string('valor_texto', 500)->nullable()
                  ->comment('Para number/text: valor libre ingresado');
            $table->timestamps();

            // Sin UNIQUE en (producto_id, atributo_id) porque multiselect permite múltiples filas
            $table->index(['producto_id', 'atributo_id']);
            $table->index('atributo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_atributos');
    }
};
