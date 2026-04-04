<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('atendido_por')->nullable();
            $table->date('fecha_visita');
            $table->time('hora_atencion')->nullable();
            $table->decimal('monto_presup_soles', 12, 2)->default(0);
            $table->decimal('monto_presup_dolares', 12, 2)->default(0);
            $table->decimal('monto_comprado_soles', 12, 2)->default(0);
            $table->decimal('monto_comprado_dolares', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->text('resumen_visita')->nullable();
            $table->tinyInteger('probabilidad_venta')->default(0)->comment('0-100');
            $table->string('medio_contacto')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};
