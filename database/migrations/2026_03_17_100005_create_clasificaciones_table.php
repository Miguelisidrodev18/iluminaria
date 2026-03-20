<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clasificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo', 3)->unique()->comment('Código corto identificador');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        DB::table('clasificaciones')->insert([
            ['nombre' => 'Interior',       'codigo' => 'INT', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Exterior',       'codigo' => 'EXT', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Comercial',      'codigo' => 'COM', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Industrial',     'codigo' => 'IND', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Decorativo',     'codigo' => 'DEC', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Arquitectónico', 'codigo' => 'ARQ', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('clasificaciones');
    }
};
