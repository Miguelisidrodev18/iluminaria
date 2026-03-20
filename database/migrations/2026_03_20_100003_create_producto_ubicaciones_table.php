<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->constrained('ubicaciones')->cascadeOnDelete();
            $table->integer('cantidad')->default(0);
            $table->string('observacion', 255)->nullable();
            $table->timestamps();

            $table->unique(['producto_id', 'ubicacion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_ubicaciones');
    }
};
