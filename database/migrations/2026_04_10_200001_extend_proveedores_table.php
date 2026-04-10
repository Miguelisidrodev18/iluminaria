<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            // RUC opcional para extranjeros
            $table->string('ruc', 11)->nullable()->change();

            // Tipo de proveedor
            $table->enum('supplier_type', ['nacional', 'extranjero', 'importacion'])
                  ->default('nacional')->after('id');

            // Campos extendidos
            $table->text('factory_address')->nullable()->after('direccion');
            $table->string('country', 60)->nullable()->after('factory_address');
            $table->string('district', 80)->nullable()->after('country');
            $table->string('port', 80)->nullable()->after('district');
            $table->string('moq', 50)->nullable()->after('port');
            $table->text('bank_detail')->nullable()->after('moq');
            $table->string('catalog_url', 255)->nullable()->after('bank_detail');
            $table->string('website', 255)->nullable()->after('catalog_url');
            $table->enum('price_level', ['muy_caro', 'accesible', 'barato'])->nullable()->after('website');
            $table->enum('quality_level', ['excelente', 'regular', 'mala'])->nullable()->after('price_level');
            $table->text('observations')->nullable()->after('quality_level');
        });

        // Índices para filtrado
        Schema::table('proveedores', function (Blueprint $table) {
            $table->index('supplier_type');
            $table->index('country');
            $table->index('price_level');
            $table->index('quality_level');
        });
    }

    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropIndex(['supplier_type']);
            $table->dropIndex(['country']);
            $table->dropIndex(['price_level']);
            $table->dropIndex(['quality_level']);
            $table->dropColumn([
                'supplier_type', 'factory_address', 'country', 'district',
                'port', 'moq', 'bank_detail', 'catalog_url', 'website',
                'price_level', 'quality_level', 'observations',
            ]);
        });
    }
};
