<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\Color;
use App\Models\Catalogo\UnidadMedida;

/**
 * Seeder de catálogo específico para luminarias.
 * Reemplaza datos de celulares (Apple, Samsung, etc.) con marcas reales de iluminación.
 * Idempotente: se puede ejecutar múltiples veces.
 */
class CatalogoLuminariasSeeder extends Seeder
{
    public function run(): void
    {
        $this->desactivarMarcasIrrelevantes();
        $this->seedMarcasLuminarias();
        $this->seedColoresLuminarias();
        $this->seedUnidadesLuminarias();

        $this->command->info('✅ Catálogo de luminarias cargado correctamente.');
    }

    // ── Desactivar marcas de celulares ────────────────────────────────────────

    private function desactivarMarcasIrrelevantes(): void
    {
        $marcasObsoletas = ['Apple', 'Samsung', 'Xiaomi', 'Huawei', 'Motorola', 'Sony'];

        Marca::whereIn('nombre', $marcasObsoletas)
             ->where('estado', 'activo')
             ->update(['estado' => 'inactivo']);
    }

    // ── Marcas reales de iluminación ──────────────────────────────────────────

    private function seedMarcasLuminarias(): void
    {
        $marcas = [
            // Propias / genéricas
            ['nombre' => 'Kyrios',       'descripcion' => 'Marca propia ADIVON SAC — línea profesional',   'sitio_web' => null],
            ['nombre' => 'Genérico',     'descripcion' => 'Productos sin marca específica',                'sitio_web' => null],

            // Marcas internacionales de iluminación
            ['nombre' => 'Philips',      'descripcion' => 'Signify — líder mundial en iluminación LED',    'sitio_web' => 'https://www.lighting.philips.com'],
            ['nombre' => 'Osram',        'descripcion' => 'Fabricante alemán de iluminación profesional',  'sitio_web' => 'https://www.osram.com'],
            ['nombre' => 'GE Lighting',  'descripcion' => 'General Electric Lighting — USA',               'sitio_web' => 'https://www.gelighting.com'],
            ['nombre' => 'LEDVANCE',     'descripcion' => 'Ex-Osram, LEDs y luminarias de eficiencia',     'sitio_web' => 'https://www.ledvance.com'],
            ['nombre' => 'Cree',         'descripcion' => 'Tecnología LED de alta eficiencia — USA',       'sitio_web' => 'https://www.cree.com'],
            ['nombre' => 'Nichia',       'descripcion' => 'Fabricante japonés de chips LED',               'sitio_web' => 'https://www.nichia.co.jp'],
            ['nombre' => 'Opple',        'descripcion' => 'Luminarias LED — origen chino, alta calidad',   'sitio_web' => 'https://www.opple.com'],
            ['nombre' => 'Havells',      'descripcion' => 'Iluminación residencial y comercial — India',   'sitio_web' => 'https://www.havells.com'],
            ['nombre' => 'Eaton',        'descripcion' => 'Iluminación industrial y emergencia',           'sitio_web' => 'https://www.eaton.com'],
            ['nombre' => 'Thorn',        'descripcion' => 'Iluminación exterior y vial profesional',       'sitio_web' => 'https://www.thornlighting.com'],
            ['nombre' => 'Trilux',       'descripcion' => 'Iluminación de oficinas e industrial — Alemania','sitio_web' => 'https://www.trilux.com'],

            // Marcas presentes en mercado peruano
            ['nombre' => 'TBCIN',        'descripcion' => 'Luminarias para proyectos en Perú',             'sitio_web' => null],
            ['nombre' => 'Sylvania',     'descripcion' => 'Iluminación general y LED',                     'sitio_web' => 'https://www.sylvania.com'],
            ['nombre' => 'TCP',          'descripcion' => 'Lámparas y LEDs — TCP Lighting',                'sitio_web' => 'https://www.tcpi.com'],
        ];

        foreach ($marcas as $marca) {
            Marca::firstOrCreate(
                ['nombre' => $marca['nombre']],
                array_diff_key($marca, ['nombre' => ''])
            );
        }
    }

    // ── Colores completos para luminarias ─────────────────────────────────────

    private function seedColoresLuminarias(): void
    {
        $colores = [
            // Base (ya existentes — mantenemos hex, no duplican)
            ['nombre' => 'Negro',             'codigo_hex' => '#1A1A1A', 'descripcion' => 'Negro estándar'],
            ['nombre' => 'Blanco',            'codigo_hex' => '#FFFFFF', 'descripcion' => 'Blanco puro'],
            ['nombre' => 'Gris',              'codigo_hex' => '#808080', 'descripcion' => 'Gris medio'],
            ['nombre' => 'Plateado',          'codigo_hex' => '#C0C0C0', 'descripcion' => 'Acabado plateado / aluminio'],
            ['nombre' => 'Dorado',            'codigo_hex' => '#CFB53B', 'descripcion' => 'Acabado dorado / champagne'],

            // Acabados mate y brillante
            ['nombre' => 'Negro Mate',        'codigo_hex' => '#2C2C2C', 'descripcion' => 'Negro con acabado mate'],
            ['nombre' => 'Negro Brillante',   'codigo_hex' => '#0A0A0A', 'descripcion' => 'Negro con acabado brillante'],
            ['nombre' => 'Blanco Mate',       'codigo_hex' => '#F5F5F5', 'descripcion' => 'Blanco con acabado mate'],
            ['nombre' => 'Blanco Brillante',  'codigo_hex' => '#FAFAFA', 'descripcion' => 'Blanco con acabado brillante'],

            // Metálicos / industriales
            ['nombre' => 'Aluminio',          'codigo_hex' => '#A8A9AD', 'descripcion' => 'Acabado aluminio natural'],
            ['nombre' => 'Cromado',           'codigo_hex' => '#D4D4D4', 'descripcion' => 'Acabado cromado espejo'],
            ['nombre' => 'Bronce',            'codigo_hex' => '#8B6914', 'descripcion' => 'Acabado bronce envejecido'],
            ['nombre' => 'Cobre',             'codigo_hex' => '#B87333', 'descripcion' => 'Acabado cobre'],
            ['nombre' => 'Acero Inoxidable',  'codigo_hex' => '#B0B0B0', 'descripcion' => 'Acabado acero inoxidable cepillado'],

            // Colores especiales
            ['nombre' => 'Arena / Beige',     'codigo_hex' => '#D2B48C', 'descripcion' => 'Tono arena / beige cálido'],
            ['nombre' => 'Antracita',         'codigo_hex' => '#3B3B3B', 'descripcion' => 'Gris muy oscuro tipo antracita'],
            ['nombre' => 'Verde Musgo',       'codigo_hex' => '#4A5240', 'descripcion' => 'Verde exterior / jardín'],

            // Acabados de madera
            ['nombre' => 'Natural Madera',    'codigo_hex' => '#C4A265', 'descripcion' => 'Imitación madera natural'],
            ['nombre' => 'Madera Oscura',     'codigo_hex' => '#5C3A1E', 'descripcion' => 'Imitación madera oscura / wengué'],

            // Colores vivos (señalización / decoración)
            ['nombre' => 'Rojo',              'codigo_hex' => '#CC0000', 'descripcion' => 'Rojo señalización'],
            ['nombre' => 'Amarillo',          'codigo_hex' => '#F7D600', 'descripcion' => 'Amarillo Kyrios'],
            ['nombre' => 'Azul',              'codigo_hex' => '#0055A5', 'descripcion' => 'Azul industrial'],
            ['nombre' => 'Verde',             'codigo_hex' => '#2E7D32', 'descripcion' => 'Verde señalización'],
            ['nombre' => 'Naranja',           'codigo_hex' => '#E65100', 'descripcion' => 'Naranja señalización'],
        ];

        foreach ($colores as $color) {
            Color::firstOrCreate(
                ['nombre' => $color['nombre']],
                ['codigo_hex' => $color['codigo_hex']]
                // Nota: columna 'descripcion' eliminada en migración 2026_02_20_173238
            );
        }
    }

    // ── Unidades de medida adicionales para luminarias ────────────────────────

    private function seedUnidadesLuminarias(): void
    {
        $unidades = [
            ['nombre' => 'Rollo',      'abreviatura' => 'ROL', 'categoria' => 'unidad',   'permite_decimales' => false],
            ['nombre' => 'Bobina',     'abreviatura' => 'BOB', 'categoria' => 'unidad',   'permite_decimales' => false],
            ['nombre' => 'Juego',      'abreviatura' => 'JGO', 'categoria' => 'unidad',   'permite_decimales' => false],
            ['nombre' => 'Módulo',     'abreviatura' => 'MOD', 'categoria' => 'unidad',   'permite_decimales' => false],
            ['nombre' => 'Par',        'abreviatura' => 'PAR', 'categoria' => 'unidad',   'permite_decimales' => false],
            ['nombre' => 'Metro cuadrado', 'abreviatura' => 'M2', 'categoria' => 'longitud', 'permite_decimales' => true],
            ['nombre' => 'Watt',       'abreviatura' => 'W',   'categoria' => 'otros',    'permite_decimales' => false],
            ['nombre' => 'Lumen',      'abreviatura' => 'LM',  'categoria' => 'otros',    'permite_decimales' => false],
        ];

        foreach ($unidades as $unidad) {
            UnidadMedida::firstOrCreate(
                ['abreviatura' => $unidad['abreviatura']],
                [
                    'nombre'            => $unidad['nombre'],
                    'categoria'         => $unidad['categoria'],
                    'permite_decimales' => $unidad['permite_decimales'],
                ]
            );
        }
    }
}
