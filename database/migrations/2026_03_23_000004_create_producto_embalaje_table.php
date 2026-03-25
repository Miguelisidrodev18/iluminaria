<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_embalaje', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            // Peso del producto (único lugar donde se almacena)
            $table->decimal('peso', 8, 3)->nullable()
                  ->comment('Peso del producto en kg');

            // Volumen del embalaje
            $table->decimal('volumen', 10, 3)->nullable()
                  ->comment('Volumen del embalaje en cm³ o m³ (según preferencia)');

            // ¿El producto llega embalado?
            $table->boolean('embalado')->default(false)
                  ->comment('Indica si el producto incluye embalaje individual');

            // Medidas del embalaje como string descriptivo
            $table->string('medida_embalaje', 100)->nullable()
                  ->comment('Ej: "62x62x10 cm"');

            // Unidades por caja
            $table->smallInteger('cantidad_por_caja')->unsigned()->nullable()
                  ->comment('Cantidad de unidades por caja/lote');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_embalaje');
    }
};
