<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedor_certificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
            $table->enum('cert_type', ['generales', 'por_producto', 'iso'])->default('generales');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index('proveedor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedor_certificaciones');
    }
};
