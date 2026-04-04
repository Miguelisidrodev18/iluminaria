<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('id_proyecto')->unique();
            $table->string('persona_cargo')->nullable();
            $table->enum('prioridad', ['A', 'M', 'B'])->default('M');
            $table->string('nombre_proyecto');
            $table->date('fecha_recepcion')->nullable();
            $table->date('fecha_entrega_aprox')->nullable();
            $table->tinyInteger('max_revisiones')->default(3);
            $table->json('revisiones_json')->nullable();
            $table->date('fecha_entrega_real')->nullable();
            $table->decimal('monto_presup_proy', 12, 2)->default(0);
            $table->decimal('monto_vendido_proy', 12, 2)->default(0);
            $table->string('centro_costos')->nullable();
            $table->enum('resultado', ['G', 'P', 'EP', 'ENT', 'ENV', 'I'])->nullable();
            $table->text('seguimiento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
