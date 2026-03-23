<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

/**
 * Categorías de productos para el catálogo de luminarias.
 *
 * Estructura:
 *   LUMI-INT → Luminarias de Interior    (downlights, paneles, apliques, etc.)
 *   LUMI-EXT → Luminarias de Exterior    (reflectores, proyectores, solares, etc.)
 *   LAMP-DEC → Lámparas Decorativas      (colgantes, sobremesa, apliques deco)
 *   CLED     → Cintas y Módulos LED      (tiras, perfiles, módulos)
 *   DRIV     → Drivers y Fuentes         (drivers, transformadores, fuentes)
 *   CABL     → Cables y Conductores      (cable eléctrico, mangueras)
 *   ACCE     → Accesorios y Herrajes     (conectores, bases, soportes)
 *   CTRL     → Controladores             (dimmers, controladores RGB/DALI)
 *
 * Idempotente: usa firstOrCreate sobre código único.
 */
class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'codigo'      => 'LUMI-INT',
                'nombre'      => 'Luminarias de Interior',
                'descripcion' => 'Downlights, paneles, apliques, lámparas de techo y luminarias para uso en interiores residenciales, comerciales e industriales.',
                'estado'      => 'activo',
            ],
            [
                'codigo'      => 'LUMI-EXT',
                'nombre'      => 'Luminarias de Exterior',
                'descripcion' => 'Proyectores, farolas, reflectores, luminarias solares y luminarias de exterior con protección IP adecuada.',
                'estado'      => 'activo',
            ],
            [
                'codigo'      => 'LAMP-DEC',
                'nombre'      => 'Lámparas Decorativas',
                'descripcion' => 'Lámparas colgantes (pendant), apliques decorativos de pared, lámparas de mesa y piezas de diseño con socket.',
                'estado'      => 'activo',
            ],
            [
                'codigo'      => 'CLED',
                'nombre'      => 'Cintas y Módulos LED',
                'descripcion' => 'Tiras LED SMD/COB, cintas LED flexibles, perfiles de aluminio para tiras y módulos LED inyectados.',
                'estado'      => 'activo',
            ],
            [
                'codigo'      => 'DRIV',
                'nombre'      => 'Drivers y Fuentes de Alimentación',
                'descripcion' => 'Drivers LED regulables y no regulables, transformadores de baja tensión y fuentes conmutadas para sistemas de iluminación.',
                'estado'      => 'activo',
            ],
            [
                'codigo'      => 'CABL',
                'nombre'      => 'Cables y Conductores',
                'descripcion' => 'Cables eléctricos unipolares y multipolares, conductores flexibles y rígidos para instalaciones eléctricas de iluminación.',
                'estado'      => 'activo',
            ],
            [
                'codigo'      => 'ACCE',
                'nombre'      => 'Accesorios y Herrajes',
                'descripcion' => 'Conectores rápidos, bases portafocos, abrazaderas, perfiles de montaje, rieles y elementos de sujeción para luminarias.',
                'estado'      => 'activo',
            ],
            [
                'codigo'      => 'CTRL',
                'nombre'      => 'Controladores y Automatización',
                'descripcion' => 'Reguladores de intensidad (dimmers), controladores RGB/RGBW, mandos remotos, receptores DMX/DALI y sistemas de automatización.',
                'estado'      => 'activo',
            ],
        ];

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(
                ['codigo' => $cat['codigo']],
                [
                    'nombre'  => $cat['nombre'],
                    // 'descripcion' eliminada en migración 2026_02_20_162203
                    'estado'  => $cat['estado'],
                ]
            );
        }

        $this->command->info('✅ Categorías verificadas/creadas: ' . count($categorias) . ' categorías.');
    }
}
