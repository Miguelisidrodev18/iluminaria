<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de definición de atributos dinámicos del catálogo.
 * Cada fila es un "campo" configurable (Potencia, Color de luz, Socket, etc.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_atributos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 100)->unique()->comment('Identificador único para uso en código');
            $table->enum('tipo', ['select', 'multiselect', 'number', 'text', 'checkbox'])
                  ->default('select');
            $table->enum('grupo', ['tecnico', 'comercial', 'instalacion', 'estetico'])
                  ->default('tecnico');
            $table->string('unidad', 30)->nullable()->comment('Ej: W, lm, K, hrs');
            $table->string('placeholder', 150)->nullable();
            $table->boolean('requerido')->default(false);
            $table->boolean('en_nombre_auto')->default(false)
                  ->comment('Si este atributo se usa al auto-generar el nombre del producto');
            $table->unsignedSmallInteger('orden_nombre')->default(0)
                  ->comment('Posición en el nombre auto-generado');
            $table->unsignedSmallInteger('orden')->default(0)
                  ->comment('Orden de aparición en el formulario');
            $table->boolean('activo')->default(true);
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index(['grupo', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_atributos');
    }
};
