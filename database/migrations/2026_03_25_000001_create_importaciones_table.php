<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importaciones', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');

            // Contadores de progreso
            $table->unsignedInteger('total_filas')->default(0);
            $table->unsignedInteger('procesadas')->default(0);
            $table->unsignedInteger('exitosas')->default(0);
            $table->unsignedInteger('fallidas')->default(0);

            // Estado del proceso
            $table->enum('estado', ['pendiente', 'procesando', 'completado', 'fallido'])
                  ->default('pendiente')
                  ->index();

            // Errores por fila (array de strings)
            $table->json('errores')->nullable();

            // Auditoría
            $table->foreignId('creado_por')->constrained('users');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importaciones');
    }
};
