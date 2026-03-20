<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalogo\CatalogoAtributo;
use App\Models\Catalogo\CatalogoValor;

/**
 * Pobla el sistema de atributos dinámicos con definiciones reales para luminarias.
 * Idempotente: usa firstOrCreate para no duplicar.
 */
class AtributosLuminariasSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAtributos();
        $this->command->info('✅ Atributos dinámicos de luminarias cargados.');
    }

    private function seedAtributos(): void
    {
        $atributos = [

            // ── GRUPO TÉCNICO ─────────────────────────────────────────────────

            [
                'slug'          => 'fuente_luz',
                'nombre'        => 'Fuente de luz',
                'tipo'          => 'select',
                'grupo'         => 'tecnico',
                'orden'         => 10,
                'en_nombre_auto'=> true,
                'orden_nombre'  => 20,
                'requerido'     => true,
                'valores'       => [
                    ['valor' => 'LED',                  'orden' => 1],
                    ['valor' => 'Halógena',              'orden' => 2],
                    ['valor' => 'Fluorescente compacta', 'orden' => 3],
                    ['valor' => 'Fluorescente tubular',  'orden' => 4],
                    ['valor' => 'Incandescente',         'orden' => 5],
                    ['valor' => 'Haluro metálico',       'orden' => 6],
                    ['valor' => 'Sodio alta presión',    'orden' => 7],
                    ['valor' => 'Solar LED',             'orden' => 8],
                ],
            ],

            [
                'slug'          => 'potencia_w',
                'nombre'        => 'Potencia',
                'tipo'          => 'number',
                'grupo'         => 'tecnico',
                'unidad'        => 'W',
                'placeholder'   => 'Ej: 18',
                'orden'         => 20,
                'en_nombre_auto'=> true,
                'orden_nombre'  => 30,
                'requerido'     => false,
                'valores'       => [],
            ],

            [
                'slug'          => 'temperatura_color',
                'nombre'        => 'Temperatura de color',
                'tipo'          => 'select',
                'grupo'         => 'tecnico',
                'orden'         => 30,
                'en_nombre_auto'=> true,
                'orden_nombre'  => 40,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => '2700K', 'etiqueta' => 'Cálido extra (2700K)',    'orden' => 1],
                    ['valor' => '3000K', 'etiqueta' => 'Cálido (3000K)',          'orden' => 2],
                    ['valor' => '3500K', 'etiqueta' => 'Blanco cálido (3500K)',   'orden' => 3],
                    ['valor' => '4000K', 'etiqueta' => 'Blanco neutro (4000K)',   'orden' => 4],
                    ['valor' => '4500K', 'etiqueta' => 'Blanco natural (4500K)',  'orden' => 5],
                    ['valor' => '5000K', 'etiqueta' => 'Blanco frío (5000K)',     'orden' => 6],
                    ['valor' => '5700K', 'etiqueta' => 'Frío (5700K)',            'orden' => 7],
                    ['valor' => '6000K', 'etiqueta' => 'Frío intenso (6000K)',    'orden' => 8],
                    ['valor' => '6500K', 'etiqueta' => 'Luz día (6500K)',         'orden' => 9],
                    ['valor' => 'RGB',   'etiqueta' => 'RGB Multicolor',          'orden' => 10],
                    ['valor' => 'RGBW',  'etiqueta' => 'RGBW (RGB + Blanco)',     'orden' => 11],
                    ['valor' => 'Tunable White', 'etiqueta' => 'Tunable White (ajustable)', 'orden' => 12],
                ],
            ],

            [
                'slug'          => 'flujo_luminoso',
                'nombre'        => 'Flujo luminoso',
                'tipo'          => 'number',
                'grupo'         => 'tecnico',
                'unidad'        => 'lm',
                'placeholder'   => 'Ej: 1600',
                'orden'         => 40,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [],
            ],

            [
                'slug'          => 'eficiencia_luminosa',
                'nombre'        => 'Eficiencia luminosa',
                'tipo'          => 'number',
                'grupo'         => 'tecnico',
                'unidad'        => 'lm/W',
                'placeholder'   => 'Ej: 120',
                'orden'         => 50,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [],
            ],

            [
                'slug'          => 'indice_reproduccion_cromatica',
                'nombre'        => 'IRC (CRI)',
                'tipo'          => 'select',
                'grupo'         => 'tecnico',
                'orden'         => 60,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'Ra>70',  'etiqueta' => 'Ra > 70 (General)',        'orden' => 1],
                    ['valor' => 'Ra>80',  'etiqueta' => 'Ra > 80 (Comercial)',      'orden' => 2],
                    ['valor' => 'Ra>90',  'etiqueta' => 'Ra > 90 (Alto)',            'orden' => 3],
                    ['valor' => 'Ra>95',  'etiqueta' => 'Ra > 95 (Premium)',         'orden' => 4],
                    ['valor' => 'Ra>97',  'etiqueta' => 'Ra > 97 (Museo/Joyería)',   'orden' => 5],
                ],
            ],

            [
                'slug'          => 'angulo_apertura',
                'nombre'        => 'Ángulo de apertura',
                'tipo'          => 'select',
                'grupo'         => 'tecnico',
                'orden'         => 70,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => '15°',  'orden' => 1],
                    ['valor' => '24°',  'orden' => 2],
                    ['valor' => '36°',  'orden' => 3],
                    ['valor' => '45°',  'orden' => 4],
                    ['valor' => '60°',  'orden' => 5],
                    ['valor' => '90°',  'orden' => 6],
                    ['valor' => '120°', 'orden' => 7],
                    ['valor' => '180°', 'orden' => 8],
                    ['valor' => '360°', 'orden' => 9],
                ],
            ],

            [
                'slug'          => 'vida_util',
                'nombre'        => 'Vida útil',
                'tipo'          => 'select',
                'grupo'         => 'tecnico',
                'orden'         => 80,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => '10.000h',  'orden' => 1],
                    ['valor' => '15.000h',  'orden' => 2],
                    ['valor' => '20.000h',  'orden' => 3],
                    ['valor' => '25.000h',  'orden' => 4],
                    ['valor' => '30.000h',  'orden' => 5],
                    ['valor' => '50.000h',  'orden' => 6],
                    ['valor' => '100.000h', 'orden' => 7],
                ],
            ],

            [
                'slug'          => 'proteccion_ip',
                'nombre'        => 'Grado de protección (IP)',
                'tipo'          => 'select',
                'grupo'         => 'tecnico',
                'orden'         => 90,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'IP20', 'etiqueta' => 'IP20 — Interior sin protección',       'orden' => 1],
                    ['valor' => 'IP44', 'etiqueta' => 'IP44 — Salpicaduras',                  'orden' => 2],
                    ['valor' => 'IP54', 'etiqueta' => 'IP54 — Polvo + salpicaduras',          'orden' => 3],
                    ['valor' => 'IP65', 'etiqueta' => 'IP65 — Chorro de agua',                'orden' => 4],
                    ['valor' => 'IP66', 'etiqueta' => 'IP66 — Chorro fuerte',                 'orden' => 5],
                    ['valor' => 'IP67', 'etiqueta' => 'IP67 — Sumergible 1m / 30min',         'orden' => 6],
                    ['valor' => 'IP68', 'etiqueta' => 'IP68 — Sumergible continuo',           'orden' => 7],
                ],
            ],

            [
                'slug'          => 'voltaje',
                'nombre'        => 'Voltaje',
                'tipo'          => 'select',
                'grupo'         => 'tecnico',
                'orden'         => 100,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => '110V',            'orden' => 1],
                    ['valor' => '220V',            'orden' => 2],
                    ['valor' => '100-240V',        'etiqueta' => '100-240V (Universal)', 'orden' => 3],
                    ['valor' => '12V DC',          'orden' => 4],
                    ['valor' => '24V DC',          'orden' => 5],
                    ['valor' => '48V DC',          'orden' => 6],
                    ['valor' => 'Solar (batería)', 'orden' => 7],
                ],
            ],

            [
                'slug'          => 'socket',
                'nombre'        => 'Socket / Casquillo',
                'tipo'          => 'select',
                'grupo'         => 'tecnico',
                'orden'         => 110,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'E27',       'orden' => 1],
                    ['valor' => 'E14',       'orden' => 2],
                    ['valor' => 'GU10',      'orden' => 3],
                    ['valor' => 'GU5.3',     'orden' => 4],
                    ['valor' => 'B22',       'orden' => 5],
                    ['valor' => 'MR16',      'orden' => 6],
                    ['valor' => 'T8',        'orden' => 7],
                    ['valor' => 'T5',        'orden' => 8],
                    ['valor' => 'PL-C',      'orden' => 9],
                    ['valor' => 'Integrado', 'etiqueta' => 'Driver integrado (sin casquillo)', 'orden' => 10],
                ],
            ],

            [
                'slug'          => 'driver',
                'nombre'        => 'Driver / Controlador',
                'tipo'          => 'select',
                'grupo'         => 'tecnico',
                'orden'         => 120,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'Incluido',       'orden' => 1],
                    ['valor' => 'No incluido',    'orden' => 2],
                    ['valor' => 'Externo',        'orden' => 3],
                    ['valor' => 'No aplica',      'orden' => 4],
                ],
            ],

            [
                'slug'          => 'regulable',
                'nombre'        => 'Regulable (Dimeable)',
                'tipo'          => 'select',
                'grupo'         => 'tecnico',
                'orden'         => 130,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'No',               'orden' => 1],
                    ['valor' => 'Sí',               'orden' => 2],
                    ['valor' => 'TRIAC / Corte de fase', 'orden' => 3],
                    ['valor' => '0-10V',            'orden' => 4],
                    ['valor' => 'DALI',             'orden' => 5],
                    ['valor' => 'PWM',              'orden' => 6],
                    ['valor' => 'RF (radio)',        'orden' => 7],
                    ['valor' => 'Zigbee/WiFi',      'orden' => 8],
                ],
            ],

            // ── GRUPO INSTALACIÓN ─────────────────────────────────────────────

            [
                'slug'          => 'tipo_montaje',
                'nombre'        => 'Tipo de montaje',
                'tipo'          => 'select',
                'grupo'         => 'instalacion',
                'orden'         => 10,
                'en_nombre_auto'=> true,
                'orden_nombre'  => 50,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'Empotrable techo',  'orden' => 1],
                    ['valor' => 'Empotrable pared',  'orden' => 2],
                    ['valor' => 'Adosado techo',     'orden' => 3],
                    ['valor' => 'Adosado pared',     'orden' => 4],
                    ['valor' => 'Colgante',          'orden' => 5],
                    ['valor' => 'Pie / Trípode',     'orden' => 6],
                    ['valor' => 'Estaca jardín',     'orden' => 7],
                    ['valor' => 'Proyector / Spot',  'orden' => 8],
                    ['valor' => 'Carril / Riel',     'orden' => 9],
                    ['valor' => 'Poste / Farola',    'orden' => 10],
                    ['valor' => 'Superficie libre',  'orden' => 11],
                ],
            ],

            [
                'slug'          => 'ip_instalacion',
                'nombre'        => 'Conexión eléctrica',
                'tipo'          => 'select',
                'grupo'         => 'instalacion',
                'orden'         => 20,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'Cable libre',       'orden' => 1],
                    ['valor' => 'Conector rápido',   'orden' => 2],
                    ['valor' => 'Enchufe estándar',  'orden' => 3],
                    ['valor' => 'USB',               'orden' => 4],
                    ['valor' => 'Batería integrada', 'orden' => 5],
                ],
            ],

            // ── GRUPO ESTÉTICO ────────────────────────────────────────────────

            [
                'slug'          => 'color_acabado',
                'nombre'        => 'Color / Acabado',
                'tipo'          => 'multiselect',
                'grupo'         => 'estetico',
                'orden'         => 10,
                'en_nombre_auto'=> true,
                'orden_nombre'  => 60,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'Negro mate',        'color_hex' => '#2C2C2C', 'orden' => 1],
                    ['valor' => 'Negro brillante',   'color_hex' => '#0A0A0A', 'orden' => 2],
                    ['valor' => 'Blanco mate',       'color_hex' => '#F5F5F5', 'orden' => 3],
                    ['valor' => 'Blanco brillante',  'color_hex' => '#FAFAFA', 'orden' => 4],
                    ['valor' => 'Plateado',          'color_hex' => '#C0C0C0', 'orden' => 5],
                    ['valor' => 'Dorado',            'color_hex' => '#CFB53B', 'orden' => 6],
                    ['valor' => 'Bronce',            'color_hex' => '#8B6914', 'orden' => 7],
                    ['valor' => 'Cromado',           'color_hex' => '#D4D4D4', 'orden' => 8],
                    ['valor' => 'Aluminio natural',  'color_hex' => '#A8A9AD', 'orden' => 9],
                    ['valor' => 'Gris antracita',    'color_hex' => '#3B3B3B', 'orden' => 10],
                    ['valor' => 'Arena / Beige',     'color_hex' => '#D2B48C', 'orden' => 11],
                    ['valor' => 'Natural madera',    'color_hex' => '#C4A265', 'orden' => 12],
                    ['valor' => 'Verde musgo',       'color_hex' => '#4A5240', 'orden' => 13],
                    ['valor' => 'Cobre',             'color_hex' => '#B87333', 'orden' => 14],
                ],
            ],

            [
                'slug'          => 'material_carcasa',
                'nombre'        => 'Material de carcasa',
                'tipo'          => 'select',
                'grupo'         => 'estetico',
                'orden'         => 20,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'Aluminio',          'orden' => 1],
                    ['valor' => 'Acero inoxidable',  'orden' => 2],
                    ['valor' => 'Hierro fundido',    'orden' => 3],
                    ['valor' => 'Policarbonato (PC)','orden' => 4],
                    ['valor' => 'PMMA (acrílico)',   'orden' => 5],
                    ['valor' => 'Vidrio',            'orden' => 6],
                    ['valor' => 'ABS',               'orden' => 7],
                    ['valor' => 'PVC',               'orden' => 8],
                    ['valor' => 'Madera',            'orden' => 9],
                    ['valor' => 'Fibra de vidrio',   'orden' => 10],
                    ['valor' => 'Zinc fundido',      'orden' => 11],
                ],
            ],

            [
                'slug'          => 'tamaño',
                'nombre'        => 'Tamaño',
                'tipo'          => 'select',
                'grupo'         => 'estetico',
                'orden'         => 30,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'Mini (< 8cm)',       'orden' => 1],
                    ['valor' => 'Pequeño (8-15cm)',   'orden' => 2],
                    ['valor' => 'Mediano (15-30cm)',  'orden' => 3],
                    ['valor' => 'Grande (30-60cm)',   'orden' => 4],
                    ['valor' => 'Extra grande (>60cm)','orden' => 5],
                ],
            ],

            // ── GRUPO COMERCIAL ───────────────────────────────────────────────

            [
                'slug'          => 'uso_aplicacion',
                'nombre'        => 'Uso / Aplicación',
                'tipo'          => 'multiselect',
                'grupo'         => 'comercial',
                'orden'         => 10,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'Residencial',          'orden' => 1],
                    ['valor' => 'Oficina / Comercial',  'orden' => 2],
                    ['valor' => 'Industrial',           'orden' => 3],
                    ['valor' => 'Hotelería',            'orden' => 4],
                    ['valor' => 'Hospitalario',         'orden' => 5],
                    ['valor' => 'Jardín / Paisajismo',  'orden' => 6],
                    ['valor' => 'Vía pública',          'orden' => 7],
                    ['valor' => 'Piscina / Fuente',     'orden' => 8],
                    ['valor' => 'Fachada / Monumento',  'orden' => 9],
                    ['valor' => 'Señalización',         'orden' => 10],
                ],
            ],

            [
                'slug'          => 'ambiente',
                'nombre'        => 'Ambiente',
                'tipo'          => 'select',
                'grupo'         => 'comercial',
                'orden'         => 20,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'Interior',          'orden' => 1],
                    ['valor' => 'Exterior',          'orden' => 2],
                    ['valor' => 'Interior/Exterior', 'orden' => 3],
                ],
            ],

            [
                'slug'          => 'eficiencia_energetica',
                'nombre'        => 'Eficiencia energética',
                'tipo'          => 'select',
                'grupo'         => 'comercial',
                'orden'         => 30,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'A+++', 'orden' => 1],
                    ['valor' => 'A++',  'orden' => 2],
                    ['valor' => 'A+',   'orden' => 3],
                    ['valor' => 'A',    'orden' => 4],
                    ['valor' => 'B',    'orden' => 5],
                    ['valor' => 'C',    'orden' => 6],
                ],
            ],

            [
                'slug'          => 'certificaciones',
                'nombre'        => 'Certificaciones',
                'tipo'          => 'multiselect',
                'grupo'         => 'comercial',
                'orden'         => 40,
                'en_nombre_auto'=> false,
                'requerido'     => false,
                'valores'       => [
                    ['valor' => 'CE',          'orden' => 1],
                    ['valor' => 'RoHS',        'orden' => 2],
                    ['valor' => 'UL',          'orden' => 3],
                    ['valor' => 'ETL',         'orden' => 4],
                    ['valor' => 'IEC 60598',   'orden' => 5],
                    ['valor' => 'IESNA LM-79', 'orden' => 6],
                    ['valor' => 'SAA',         'orden' => 7],
                    ['valor' => 'FCC',         'orden' => 8],
                ],
            ],
        ];

        foreach ($atributos as $data) {
            $valores = $data['valores'];
            unset($data['valores']);

            // Crear o actualizar atributo
            $atributo = CatalogoAtributo::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // Crear valores (idempotente)
            foreach ($valores as $v) {
                CatalogoValor::firstOrCreate(
                    ['atributo_id' => $atributo->id, 'valor' => $v['valor']],
                    array_merge(['atributo_id' => $atributo->id], $v)
                );
            }
        }
    }
}
