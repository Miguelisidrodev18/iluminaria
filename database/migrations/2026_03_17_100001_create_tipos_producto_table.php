<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_producto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo', 2)->unique()->comment('Código de 2 caracteres para el código Kyrios');
            $table->boolean('usa_tipo_luminaria')->default(false)->comment('Si true, requiere seleccionar tipo_luminaria');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Datos iniciales del catálogo
        DB::table('tipos_producto')->insert([
            ['nombre' => 'Luminaria',  'codigo' => 'LU', 'usa_tipo_luminaria' => true,  'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Lámpara',    'codigo' => 'LA', 'usa_tipo_luminaria' => false, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cinta LED',  'codigo' => 'CL', 'usa_tipo_luminaria' => false, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Accesorio',  'codigo' => 'AC', 'usa_tipo_luminaria' => false, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Fuente',     'codigo' => 'FU', 'usa_tipo_luminaria' => false, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_producto');
    }
};
