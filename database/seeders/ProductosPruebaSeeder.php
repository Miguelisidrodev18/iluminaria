<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Luminaria\ProductoEspecificacion;
use App\Models\Luminaria\ProductoDimension;
use App\Models\Luminaria\ProductoMaterial;
use App\Models\Luminaria\ProductoClasificacion;
use Illuminate\Support\Facades\DB;

/**
 * 5 productos de prueba que cubren diferentes tipos del catálogo Kyrios.
 *
 * Datos reales del sistema:
 *   categorias:       id=1 Luminaria
 *   unidades_medida:  id=1 Unidad (UND)
 *   marcas (codigo):  GE=8·Genérico, SA=2·Samsung, LG=7·LG, AP=1·Apple, SO=6·Sony
 *   tipos_producto:   LU=1, LA=2, CL=3, EA=6, SO=13
 *   tipos_luminaria:  ET=9·Empotrado techo, 20=5·Colgante, 15=6·Techo plafón
 *   clasificaciones:  INT=1, EXT=2, COM=3, IND=4, DEC=5, ARQ=6
 *   tipos_proyecto:   1=Residencial, 2=Comercial, 3=Hotelero, 4=Oficina, 6=Paisajismo
 *   almacenes:        id=1
 */
class ProductosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // ─── PRODUCTO 1 ───────────────────────────────────────────────────
            // Luminaria empotrada LED  |  LU + ET + GE  →  KY-LUETGE-0001
            $p1 = Producto::create([
                'codigo'            => 'PROD-00002',
                'codigo_kyrios'     => 'KY-LUETGE-0001',
                'codigo_fabrica'    => 'SR-SLIM-860-WH',
                'nombre'            => 'Downlight LED Slim 8W Empotrado Blanco',
                'descripcion'       => 'Luminaria empotrada circular LED de ultra bajo perfil. Ideal para plafones de yeso y cielos rasos.',
                'categoria_id'      => 1,
                'marca_id'          => 8,   // Genérico
                'tipo_producto_id'  => 1,   // LU – Luminaria
                'tipo_luminaria_id' => 9,   // ET – Empotrado de techo
                'unidad_medida_id'  => 1,
                'tipo_inventario'   => 'cantidad',
                'estado'            => 'activo',
                'procedencia'       => 'China',
                'linea'             => 'Slim Pro',
                'stock_actual'      => 48,
                'stock_minimo'      => 10,
                'stock_maximo'      => 200,
                'ubicacion'         => 'A-01',
                'costo_promedio'    => 18.50,
                'ultimo_costo_compra' => 18.50,
            ]);

            ProductoEspecificacion::create([
                'producto_id'        => $p1->id,
                'potencia'           => '8W',
                'lumenes'            => '720lm',
                'voltaje'            => '220V',
                'temperatura_color'  => '4000K',
                'cri'                => 80,
                'ip'                 => 'IP20',
                'angulo_apertura'    => '120°',
                'driver'             => 'Integrado',
                'regulable'          => false,
                'vida_util_horas'    => 30000,
            ]);

            ProductoDimension::create([
                'producto_id'    => $p1->id,
                'diametro'       => 90,
                'diametro_agujero' => 75,
                'alto'           => 28,
                'peso'           => 0.180,
            ]);

            ProductoMaterial::create([
                'producto_id'     => $p1->id,
                'material_1'      => 'Aluminio',
                'material_2'      => 'Policarbonato difusor',
                'color_acabado_1' => 'Blanco',
            ]);

            $p1->clasificacion()->create([
                'tipo_instalacion' => ['empotrado'],
                'estilo'           => ['Moderno', 'Minimalista'],
            ]);

            $p1->clasificaciones()->sync([1, 3]);   // Interior, Comercial
            $p1->tiposProyecto()->sync([2, 4]);     // Comercial, Oficina

            // ─── PRODUCTO 2 ───────────────────────────────────────────────────
            // Lámpara colgante decorativa  |  LA + 00 + GE  →  KY-LA00GE-0001
            $p2 = Producto::create([
                'codigo'            => 'PROD-00003',
                'codigo_kyrios'     => 'KY-LA00GE-0001',
                'codigo_fabrica'    => 'PD-NORDIC-E27-BK',
                'nombre'            => 'Lámpara Colgante Nórdica E27 Negro Mate',
                'descripcion'       => 'Lámpara colgante estilo escandinavo, pantalla metálica. Incluye cable textil de 1.5m.',
                'categoria_id'      => 1,
                'marca_id'          => 8,   // Genérico
                'tipo_producto_id'  => 2,   // LA – Lámpara
                'tipo_luminaria_id' => null,
                'unidad_medida_id'  => 1,
                'tipo_inventario'   => 'cantidad',
                'estado'            => 'activo',
                'procedencia'       => 'China',
                'linea'             => 'Nordic',
                'stock_actual'      => 25,
                'stock_minimo'      => 5,
                'stock_maximo'      => 80,
                'ubicacion'         => 'B-03',
                'costo_promedio'    => 42.00,
                'ultimo_costo_compra' => 42.00,
            ]);

            ProductoEspecificacion::create([
                'producto_id'       => $p2->id,
                'potencia'          => 'E27 hasta 60W',
                'voltaje'           => '220V',
                'socket'            => 'E27',
                'numero_lamparas'   => 1,
                'regulable'         => false,
            ]);

            ProductoDimension::create([
                'producto_id' => $p2->id,
                'diametro'    => 200,
                'alto'        => 180,
                'alto_suspendido' => 1500,
                'peso'        => 0.650,
            ]);

            ProductoMaterial::create([
                'producto_id'     => $p2->id,
                'material_1'      => 'Acero',
                'color_acabado_1' => 'Negro mate',
                'color_acabado_2' => 'Cable textil negro',
            ]);

            $p2->clasificacion()->create([
                'tipo_instalacion' => ['suspendido'],
                'estilo'           => ['Nórdico', 'Minimalista', 'Industrial'],
            ]);

            $p2->clasificaciones()->sync([1, 5]);   // Interior, Decorativo
            $p2->tiposProyecto()->sync([1, 3]);     // Residencial, Hotelero

            // ─── PRODUCTO 3 ───────────────────────────────────────────────────
            // Cinta LED exterior RGB  |  CL + 00 + SA  →  KY-CL00SA-0001
            $p3 = Producto::create([
                'codigo'            => 'PROD-00004',
                'codigo_kyrios'     => 'KY-CL00SA-0001',
                'codigo_fabrica'    => 'TL-5050-RGB-IP65-5M',
                'nombre'            => 'Tira LED RGB 5050 IP65 5 metros 12V',
                'descripcion'       => 'Cinta LED flexible RGB con protección IP65. 60 LEDs/m. Apta para exteriores y ambientes húmedos.',
                'categoria_id'      => 1,
                'marca_id'          => 2,   // Samsung
                'tipo_producto_id'  => 3,   // CL – Cinta LED
                'tipo_luminaria_id' => null,
                'unidad_medida_id'  => 1,
                'tipo_inventario'   => 'cantidad',
                'estado'            => 'activo',
                'procedencia'       => 'China',
                'linea'             => 'ColorFlex',
                'stock_actual'      => 60,
                'stock_minimo'      => 10,
                'stock_maximo'      => 300,
                'ubicacion'         => 'C-02',
                'costo_promedio'    => 28.90,
                'ultimo_costo_compra' => 28.90,
            ]);

            ProductoEspecificacion::create([
                'producto_id'           => $p3->id,
                'potencia'              => '72W/rollo',
                'lumenes'               => '1440lm/m',
                'voltaje'               => '12V DC',
                'ip'                    => 'IP65',
                'protocolo_regulacion'  => 'RGB Controller',
                'regulable'             => true,
                'vida_util_horas'       => 50000,
            ]);

            ProductoDimension::create([
                'producto_id' => $p3->id,
                'ancho'       => 10,
                'alto'        => 2.5,
                'peso'        => 0.300,
            ]);

            ProductoMaterial::create([
                'producto_id'     => $p3->id,
                'material_1'      => 'PCB flexible',
                'material_2'      => 'Silicona IP65',
                'color_acabado_1' => 'RGB multicolor',
            ]);

            $p3->clasificacion()->create([
                'tipo_instalacion' => ['superficie', 'carril'],
                'estilo'           => ['Moderno', 'Contemporáneo'],
            ]);

            $p3->clasificaciones()->sync([1, 2, 5, 6]);    // Interior, Exterior, Decorativo, Arquitectónico
            $p3->tiposProyecto()->sync([1, 2, 3]);          // Residencial, Comercial, Hotelero

            // ─── PRODUCTO 4 ───────────────────────────────────────────────────
            // Driver LED 50W  |  EA + 00 + GE  →  KY-EA00GE-0001
            $p4 = Producto::create([
                'codigo'            => 'PROD-00005',
                'codigo_kyrios'     => 'KY-EA00GE-0001',
                'codigo_fabrica'    => 'DRV-50W-CV12V-IP67',
                'nombre'            => 'Driver LED 50W 12V DC IP67 Meanwell',
                'descripcion'       => 'Fuente de alimentación conmutada para sistemas LED. Salida regulada 12V DC, grado IP67 para exteriores.',
                'categoria_id'      => 1,
                'marca_id'          => 8,   // Genérico
                'tipo_producto_id'  => 6,   // EA – Fuente/driver
                'tipo_luminaria_id' => null,
                'unidad_medida_id'  => 1,
                'tipo_inventario'   => 'cantidad',
                'estado'            => 'activo',
                'procedencia'       => 'Taiwan',
                'linea'             => 'LRS Series',
                'stock_actual'      => 15,
                'stock_minimo'      => 5,
                'stock_maximo'      => 50,
                'ubicacion'         => 'D-04',
                'costo_promedio'    => 55.00,
                'ultimo_costo_compra' => 55.00,
            ]);

            ProductoEspecificacion::create([
                'producto_id'  => $p4->id,
                'potencia'     => '50W',
                'voltaje'      => 'Entrada: 100-240VAC / Salida: 12V DC',
                'ip'           => 'IP67',
                'regulable'    => false,
            ]);

            ProductoDimension::create([
                'producto_id' => $p4->id,
                'ancho'       => 99,
                'alto'        => 30,
                'profundidad' => 199,
                'peso'        => 0.620,
            ]);

            ProductoMaterial::create([
                'producto_id'     => $p4->id,
                'material_1'      => 'Aluminio',
                'color_acabado_1' => 'Gris metálico',
            ]);

            // Driver no tiene clasificacion de instalación ni proyectos

            // ─── PRODUCTO 5 ───────────────────────────────────────────────────
            // Luminaria solar de jardín  |  SO + 00 + LG  →  KY-SO00LG-0001
            $p5 = Producto::create([
                'codigo'            => 'PROD-00006',
                'codigo_kyrios'     => 'KY-SO00LG-0001',
                'codigo_fabrica'    => 'SOL-GARD-20W-IP65',
                'nombre'            => 'Luminaria Solar LED 20W con Sensor de Movimiento IP65',
                'descripcion'       => 'Luminaria autónoma solar con panel integrado de 6V/6W, batería Li-Ion 2000mAh, sensor PIR 120° y autonomía de hasta 12h.',
                'categoria_id'      => 1,
                'marca_id'          => 7,   // LG
                'tipo_producto_id'  => 13,  // SO – Solares
                'tipo_luminaria_id' => null,
                'unidad_medida_id'  => 1,
                'tipo_inventario'   => 'cantidad',
                'estado'            => 'activo',
                'procedencia'       => 'China',
                'linea'             => 'EcoSolar',
                'stock_actual'      => 20,
                'stock_minimo'      => 3,
                'stock_maximo'      => 60,
                'ubicacion'         => 'E-01',
                'costo_promedio'    => 85.00,
                'ultimo_costo_compra' => 85.00,
            ]);

            ProductoEspecificacion::create([
                'producto_id'       => $p5->id,
                'potencia'          => '20W',
                'lumenes'           => '1800lm',
                'voltaje'           => 'Panel: 6V / Batería: 3.7V',
                'temperatura_color' => '6500K',
                'ip'                => 'IP65',
                'regulable'         => false,
                'vida_util_horas'   => 50000,
            ]);

            ProductoDimension::create([
                'producto_id' => $p5->id,
                'ancho'       => 220,
                'alto'        => 180,
                'profundidad' => 35,
                'peso'        => 1.200,
            ]);

            ProductoMaterial::create([
                'producto_id'        => $p5->id,
                'material_1'         => 'ABS resistente UV',
                'material_2'         => 'Panel solar monocristalino',
                'material_terciario' => 'Batería Li-Ion 2000mAh',
                'color_acabado_1'    => 'Negro',
            ]);

            $p5->clasificacion()->create([
                'tipo_instalacion' => ['poste', 'superficie'],
                'estilo'           => ['Moderno', 'Natural'],
            ]);

            $p5->clasificaciones()->sync([2, 5]);   // Exterior, Decorativo
            $p5->tiposProyecto()->sync([1, 6]);     // Residencial, Paisajismo
        });

        $this->command->info('✓ 5 productos de prueba creados correctamente.');
        $this->command->table(
            ['Código Kyrios', 'Nombre', 'Tipo'],
            [
                ['KY-LUETGE-0001', 'Downlight LED Slim 8W Empotrado',         'LU – Luminaria'],
                ['KY-LA00GE-0001', 'Lámpara Colgante Nórdica E27',            'LA – Lámpara'],
                ['KY-CL00SA-0001', 'Tira LED RGB 5050 IP65 5m',               'CL – Cinta LED'],
                ['KY-EA00GE-0001', 'Driver LED 50W 12V DC IP67',              'EA – Fuente/Driver'],
                ['KY-SO00LG-0001', 'Luminaria Solar LED 20W con Sensor PIR',  'SO – Solares'],
            ]
        );
    }
}
