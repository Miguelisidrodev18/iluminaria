<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoProyectoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Residencial',   'icono' => 'fa-home',        'activo' => true],
            ['nombre' => 'Comercial',      'icono' => 'fa-store',       'activo' => true],
            ['nombre' => 'Industrial',     'icono' => 'fa-industry',    'activo' => true],
            ['nombre' => 'Educativo',      'icono' => 'fa-school',      'activo' => true],
            ['nombre' => 'Hospitalario',   'icono' => 'fa-hospital',    'activo' => true],
            ['nombre' => 'Urbano',         'icono' => 'fa-city',        'activo' => true],
            ['nombre' => 'Hotelero',       'icono' => 'fa-hotel',       'activo' => true],
            ['nombre' => 'Deportivo',      'icono' => 'fa-running',     'activo' => true],
        ];

        DB::table('tipos_proyecto')->upsert(
            $tipos,
            ['nombre'],          // columnas únicas para detectar duplicados
            ['icono', 'activo']  // columnas a actualizar si ya existe
        );
    }
}
