<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_componentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('padre_id')
                  ->constrained('productos')
                  ->onDelete('cascade')
                  ->comment('Producto compuesto (kit)');
            $table->foreignId('hijo_id')
                  ->constrained('productos')
                  ->onDelete('restrict')
                  ->comment('Producto componente');
            $table->foreignId('variante_id')
                  ->nullable()
                  ->constrained('producto_variantes')
                  ->onDelete('set null')
                  ->comment('Variante específica del componente (opcional)');
            $table->decimal('cantidad', 10, 3)->default(1);
            $table->string('unidad', 20)->default('unidad');
            $table->boolean('es_opcional')->default(false)
                  ->comment('Si true: puede excluirse en cotización personalizada');
            $table->tinyInteger('orden')->unsigned()->default(0);
            $table->string('observacion', 255)->nullable();
            $table->timestamps();

            $table->unique(['padre_id', 'hijo_id', 'variante_id'], 'uq_bom');
            $table->index('padre_id');
            $table->index('hijo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_componentes');
    }
};
