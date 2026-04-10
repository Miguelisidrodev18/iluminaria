<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedor_categorias_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
            $table->string('categoria', 80);
            $table->string('subcategoria', 100);
            $table->timestamps();

            $table->index('proveedor_id');
            $table->index(['categoria', 'subcategoria']);
            $table->unique(['proveedor_id', 'categoria', 'subcategoria'], 'prov_cat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedor_categorias_producto');
    }
};
