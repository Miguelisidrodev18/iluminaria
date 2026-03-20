<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_luminaria', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo', 2)->unique()->comment('Código de 2 caracteres para el código Kyrios');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Datos iniciales del catálogo
        DB::table('tipos_luminaria')->insert([
            ['nombre' => 'Downlight',   'codigo' => 'DL', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Panel',       'codigo' => 'PA', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Aplique',     'codigo' => 'AP', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Reflector',   'codigo' => 'RE', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Lineal',      'codigo' => 'LI', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Proyector',   'codigo' => 'PR', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Campana',     'codigo' => 'CA', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Suspendido',  'codigo' => 'SU', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Exterior',    'codigo' => 'EX', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Emergencia',  'codigo' => 'EM', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_luminaria');
    }
};
