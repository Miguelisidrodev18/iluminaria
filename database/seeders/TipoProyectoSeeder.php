<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoProyectoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Residencial',           'icono' => 'fa-home',           'activo' => true],
            ['nombre' => 'Comercial',              'icono' => 'fa-store',          'activo' => true],
            ['nombre' => 'Oficina',                'icono' => 'fa-briefcase',      'activo' => true],
            ['nombre' => 'Hotelero',               'icono' => 'fa-hotel',          'activo' => true],
            ['nombre' => 'Restaurante',            'icono' => 'fa-utensils',       'activo' => true],
            ['nombre' => 'Laboratorio',            'icono' => 'fa-flask',          'activo' => true],
            ['nombre' => 'Centro Médico',          'icono' => 'fa-hospital',       'activo' => true],
            ['nombre' => 'Estación de Servicios',  'icono' => 'fa-gas-pump',       'activo' => true],
            ['nombre' => 'Paisajismo',             'icono' => 'fa-leaf',           'activo' => true],
            ['nombre' => 'Clubes',                 'icono' => 'fa-swimming-pool',  'activo' => true],
            ['nombre' => 'Condominios',            'icono' => 'fa-building',       'activo' => true],
            ['nombre' => 'Urbano',                 'icono' => 'fa-city',           'activo' => true],
            ['nombre' => 'Galerias y Museos',      'icono' => 'fa-landmark',       'activo' => true],
            ['nombre' => 'Industrial',             'icono' => 'fa-industry',       'activo' => true],
            ['nombre' => 'Educativo',              'icono' => 'fa-school',         'activo' => true],
            ['nombre' => 'Hospitalario',           'icono' => 'fa-hospital',       'activo' => true],
            ['nombre' => 'Deportivo',              'icono' => 'fa-running',        'activo' => true],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipos_proyecto')->updateOrInsert(
                ['nombre' => $tipo['nombre']],
                $tipo
            );
        }
    }
}
