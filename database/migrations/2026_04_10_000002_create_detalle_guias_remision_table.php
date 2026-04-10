<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_guias_remision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_remision_id')->constrained('guias_remision')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('codigo', 50)->nullable();
            $table->string('descripcion', 250);
            $table->string('unidad_medida', 10)->default('NIU');
            // NIU=unidad, KGM=kilogramo, MTR=metro, LTR=litro, ZZ=servicio
            $table->decimal('cantidad', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_guias_remision');
    }
};
