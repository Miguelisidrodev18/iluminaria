<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_proyecto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');           // Ej: "Residencial", "Comercial"
            $table->string('icono')->nullable(); // Clase de icono para UI
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Datos iniciales
        DB::table('tipos_proyecto')->insert([
            ['nombre' => 'Residencial',     'icono' => 'home',      'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Comercial',       'icono' => 'store',     'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Hotelero',        'icono' => 'hotel',     'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Oficina',         'icono' => 'office',    'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Centro médico',   'icono' => 'medical',   'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Paisajismo',      'icono' => 'park',      'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Industrial',      'icono' => 'factory',   'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Educativo',       'icono' => 'school',    'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_proyecto');
    }
};
