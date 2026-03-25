<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\UnidadMedida;
use App\Models\Luminaria\TipoProducto;
use App\Models\Luminaria\TipoLuminaria;
use App\Models\Luminaria\ProductoEspecificacion;
use App\Models\Luminaria\ProductoDimension;
use App\Models\Luminaria\ProductoMaterial;
use App\Models\Luminaria\ProductoEmbalaje;

/**
 * Productos de ejemplo que cubren los tres arquetipos del sistema.
 *
 *  componente  → insumo reutilizable en kits (cable, driver, conector)
 *  simple      → producto vendible directamente (downlight, panel)
 *  compuesto   → kit con BOM; componentes definidos en ComponenteSeeder
 *
 * Referencia cruzada con ComponenteSeeder:
 *   KY-KIT-DL9-001  ← KY-LUEDL-001 + KY-DRIV-001 + KY-CABL-001 + KY-CONE-001
 *   KY-KIT-PA40-001 ← KY-LUPA-001  + KY-DRIV-002 + KY-CABL-002 + KY-CONE-001
 *
 * NUNCA ejecutar directamente en producción.
 */
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('⚠️  ProductoSeeder omitido en producción.');
            return;
        }

        DB::transaction(function () {
            // ── Resolver IDs de catálogos por código/nombre ───────────────
            $catLumiInt = Categoria::where('codigo', 'LUMI-INT')->value('id');
            $catDriv    = Categoria::where('codigo', 'DRIV')->value('id');
            $catCabl    = Categoria::where('codigo', 'CABL')->value('id');
            $catAcce    = Categoria::where('codigo', 'ACCE')->value('id');

            $und   = UnidadMedida::where('abreviatura', 'UND')->value('id');
            $metro = UnidadMedida::where('abreviatura', 'MT')->value('id');

            $marcaKyrios  = Marca::where('nombre', 'Kyrios')->value('id');
            $marcaGeneric = Marca::where('nombre', 'Genérico')->value('id');

            $tipoLU = TipoProducto::where('codigo', 'LU')->value('id');  // Luminaria
            $tipoFU = TipoProducto::where('codigo', 'FU')->value('id');  // Fuente/Driver
            $tipoAC = TipoProducto::where('codigo', 'AC')->value('id');  // Accesorio

            $lumDL = TipoLuminaria::where('codigo', 'DL')->value('id');  // Downlight
            $lumPA = TipoLuminaria::where('codigo', 'PA')->value('id');  // Panel

            // ════════════════════════════════════════════════════════════
            // ── COMPONENTES — insumos reutilizables en kits ─────────────
            // ════════════════════════════════════════════════════════════

            // Componente 1 — Cable H07V-U 1.5mm² Negro
            $this->crearProducto('KY-CABL-001', [
                'codigo'           => 'COMP-CABL-001',
                'codigo_fabrica'   => 'H07VU-1.5-NEG',
                'nombre'           => 'Cable H07V-U 1.5mm² Negro',
                'descripcion'      => 'Cable unipolar rígido 1.5mm² para instalaciones eléctricas de baja tensión. Norma IEC 60227.',
                'categoria_id'     => $catCabl,
                'marca_id'         => $marcaGeneric,
                'tipo_producto_id' => $tipoAC,
                'unidad_medida_id' => $metro,
                'tipo_sistema'     => 'componente',
                'procedencia'      => 'Perú',
                'stock_actual'     => 500,
                'stock_minimo'     => 50,
                'stock_maximo'     => 2000,
                'costo_promedio'   => 1.20,
            ]);

            // Componente 2 — Cable H07V-U 2.5mm² Negro
            $this->crearProducto('KY-CABL-002', [
                'codigo'           => 'COMP-CABL-002',
                'codigo_fabrica'   => 'H07VU-2.5-NEG',
                'nombre'           => 'Cable H07V-U 2.5mm² Negro',
                'descripcion'      => 'Cable unipolar rígido 2.5mm² para instalaciones de media carga. Norma IEC 60227.',
                'categoria_id'     => $catCabl,
                'marca_id'         => $marcaGeneric,
                'tipo_producto_id' => $tipoAC,
                'unidad_medida_id' => $metro,
                'tipo_sistema'     => 'componente',
                'procedencia'      => 'Perú',
                'stock_actual'     => 300,
                'stock_minimo'     => 30,
                'stock_maximo'     => 1000,
                'costo_promedio'   => 1.90,
            ]);

            // Componente 3 — Driver LED 20W 12V DC
            $driver20 = $this->crearProducto('KY-DRIV-001', [
                'codigo'           => 'COMP-DRIV-001',
                'codigo_fabrica'   => 'DRV-20W-CV12V',
                'nombre'           => 'Driver LED 20W 12V DC',
                'descripcion'      => 'Fuente de alimentación conmutada para LED. Entrada 100–240V AC / 50–60Hz. Salida 12V DC, 1.67A, 20W.',
                'categoria_id'     => $catDriv,
                'marca_id'         => $marcaGeneric,
                'tipo_producto_id' => $tipoFU,
                'unidad_medida_id' => $und,
                'tipo_sistema'     => 'componente',
                'procedencia'      => 'China',
                'stock_actual'     => 80,
                'stock_minimo'     => 10,
                'stock_maximo'     => 300,
                'costo_promedio'   => 18.00,
            ]);

            if ($driver20->wasRecentlyCreated) {
                ProductoEspecificacion::create([
                    'producto_id' => $driver20->id,
                    'potencia'    => '20W',
                    'voltaje'     => 'Entrada: 100–240V AC  |  Salida: 12V DC',
                    'ip'          => 'IP20',
                    'regulable'   => false,
                ]);
                ProductoDimension::create([
                    'producto_id' => $driver20->id,
                    'ancho'       => 69,
                    'alto'        => 26,
                    'profundidad' => 150,
                ]);
                ProductoMaterial::create([
                    'producto_id'     => $driver20->id,
                    'material_1'      => 'Carcasa metálica',
                    'color_acabado_1' => 'Gris metálico',
                ]);
            }

            // Componente 4 — Driver LED 60W 24V DC
            $driver60 = $this->crearProducto('KY-DRIV-002', [
                'codigo'           => 'COMP-DRIV-002',
                'codigo_fabrica'   => 'DRV-60W-CV24V',
                'nombre'           => 'Driver LED 60W 24V DC',
                'descripcion'      => 'Fuente de alimentación para paneles LED de alta potencia. Entrada 220V AC. Salida 24V DC, 2.5A, 60W.',
                'categoria_id'     => $catDriv,
                'marca_id'         => $marcaGeneric,
                'tipo_producto_id' => $tipoFU,
                'unidad_medida_id' => $und,
                'tipo_sistema'     => 'componente',
                'procedencia'      => 'China',
                'stock_actual'     => 40,
                'stock_minimo'     => 5,
                'stock_maximo'     => 150,
                'costo_promedio'   => 38.00,
            ]);

            if ($driver60->wasRecentlyCreated) {
                ProductoEspecificacion::create([
                    'producto_id' => $driver60->id,
                    'potencia'    => '60W',
                    'voltaje'     => 'Entrada: 220V AC  |  Salida: 24V DC',
                    'ip'          => 'IP20',
                    'regulable'   => false,
                ]);
                ProductoDimension::create([
                    'producto_id' => $driver60->id,
                    'ancho'       => 99,
                    'alto'        => 30,
                    'profundidad' => 199,
                ]);
                ProductoMaterial::create([
                    'producto_id'     => $driver60->id,
                    'material_1'      => 'Carcasa metálica',
                    'color_acabado_1' => 'Gris metálico',
                ]);
            }

            // Componente 5 — Conector Wago 221-412
            $this->crearProducto('KY-CONE-001', [
                'codigo'           => 'COMP-CONE-001',
                'codigo_fabrica'   => 'WAGO-221-412',
                'nombre'           => 'Conector Wago 221-412 (2 entradas)',
                'descripcion'      => 'Conector de empalme sin herramienta para 2 conductores. Sección: 0.2–4mm². 400V / 32A.',
                'categoria_id'     => $catAcce,
                'marca_id'         => $marcaGeneric,
                'tipo_producto_id' => $tipoAC,
                'unidad_medida_id' => $und,
                'tipo_sistema'     => 'componente',
                'procedencia'      => 'Alemania',
                'stock_actual'     => 1000,
                'stock_minimo'     => 100,
                'stock_maximo'     => 5000,
                'costo_promedio'   => 0.85,
            ]);

            // ════════════════════════════════════════════════════════════
            // ── PRODUCTOS SIMPLES — vendibles directamente ───────────────
            // ════════════════════════════════════════════════════════════

            // Simple 1 — Downlight LED 9W Redondo (producto base con variantes de temperatura)
            $downlight = $this->crearProducto('KY-LUEDL-001', [
                'codigo'                => 'PROD-DL9W-001',
                'codigo_fabrica'        => 'KY-DL-9W-RD-WH',
                'nombre'                => 'Downlight LED 9W Redondo',
                'nombre_kyrios'         => 'Downlight Kyrios 9W Slim',
                'descripcion'           => 'Luminaria empotrada circular LED 9W. Disponible en temperatura de color 3000K, 4000K y 6000K. Cuerpo aluminio, difusor policarbonato.',
                'categoria_id'          => $catLumiInt,
                'marca_id'              => $marcaKyrios,
                'tipo_producto_id'      => $tipoLU,
                'tipo_luminaria_id'     => $lumDL,
                'unidad_medida_id'      => $und,
                'tipo_sistema'          => 'simple',
                'descontar_componentes' => false,
                'procedencia'           => 'China',
                'linea'                 => 'Kyrios Pro',
                'stock_actual'          => 0,   // Se gestiona por variantes
                'stock_minimo'          => 10,
                'stock_maximo'          => 500,
                'costo_promedio'        => 22.00,
            ]);

            if ($downlight->wasRecentlyCreated) {
                ProductoEspecificacion::create([
                    'producto_id'       => $downlight->id,
                    'potencia'          => '9W',
                    'tipo_fuente'       => 'LED',
                    'salida_luz'        => 'Directa',
                    'nivel_potencia'    => 'Baja (0–10W)',
                    'nominal_lumenes'   => 900,
                    'real_lumenes'      => 810,
                    'eficacia_luminosa' => 90.00,
                    'tonalidad_luz'     => 'Cálido',
                    'lumenes'           => '810lm',
                    'voltaje'           => '220V',
                    'temperatura_color' => '3000K',
                    'cri'               => 80,
                    'ip'                => 'IP20',
                    'angulo_apertura'   => '120°',
                    'driver'            => 'incluido',
                    'regulable'         => false,
                    'numero_lamparas'   => 1,
                    'vida_util_horas'   => 25000,
                ]);
                ProductoDimension::create([
                    'producto_id'      => $downlight->id,
                    'diametro'         => 110,
                    'diametro_agujero' => 90,
                    'alto'             => 32,
                ]);
                ProductoMaterial::create([
                    'producto_id'     => $downlight->id,
                    'material_1'      => 'Aluminio fundido',
                    'material_2'      => 'Policarbonato difusor',
                    'color_acabado_1' => 'Blanco',
                ]);
                ProductoEmbalaje::create([
                    'producto_id'      => $downlight->id,
                    'peso'             => 0.180,
                    'volumen'          => 400.000,
                    'embalado'         => true,
                    'medida_embalaje'  => '13x13x5 cm',
                    'cantidad_por_caja'=> 20,
                ]);
            }

            // Simple 2 — Panel LED 40W 60×60cm
            $panel = $this->crearProducto('KY-LUPA-001', [
                'codigo'                => 'PROD-PA40-001',
                'codigo_fabrica'        => 'KY-PA-40W-6060',
                'nombre'                => 'Panel LED 40W 60×60cm',
                'nombre_kyrios'         => 'Panel Kyrios Office 40W',
                'descripcion'           => 'Panel LED retroiluminado de aluminio ultradelgado. 4000K, CRI>80. Ideal para oficinas, salas de reuniones y comercios.',
                'categoria_id'          => $catLumiInt,
                'marca_id'              => $marcaKyrios,
                'tipo_producto_id'      => $tipoLU,
                'tipo_luminaria_id'     => $lumPA,
                'unidad_medida_id'      => $und,
                'tipo_sistema'          => 'simple',
                'descontar_componentes' => false,
                'procedencia'           => 'China',
                'linea'                 => 'Kyrios Office',
                'stock_actual'          => 35,
                'stock_minimo'          => 5,
                'stock_maximo'          => 200,
                'costo_promedio'        => 48.00,
            ]);

            if ($panel->wasRecentlyCreated) {
                ProductoEspecificacion::create([
                    'producto_id'       => $panel->id,
                    'potencia'          => '40W',
                    'tipo_fuente'       => 'LED',
                    'salida_luz'        => 'Difusa',
                    'nivel_potencia'    => 'Alta (31W+)',
                    'nominal_lumenes'   => 4000,
                    'real_lumenes'      => 3800,
                    'eficacia_luminosa' => 95.00,
                    'tonalidad_luz'     => 'Neutro',
                    'lumenes'           => '3800lm',
                    'voltaje'           => '220V',
                    'temperatura_color' => '4000K',
                    'cri'               => 80,
                    'ip'                => 'IP20',
                    'angulo_apertura'   => '120°',
                    'driver'            => 'incluido',
                    'regulable'         => false,
                    'numero_lamparas'   => 1,
                    'vida_util_horas'   => 50000,
                ]);
                ProductoDimension::create([
                    'producto_id' => $panel->id,
                    'lado'        => 595,   // panel cuadrado 595×595mm
                    'alto'        => 10,
                ]);
                ProductoMaterial::create([
                    'producto_id'     => $panel->id,
                    'material_1'      => 'Perfil de aluminio',
                    'material_2'      => 'PMMA difusor',
                    'color_acabado_1' => 'Blanco',
                ]);
                ProductoEmbalaje::create([
                    'producto_id'      => $panel->id,
                    'peso'             => 1.200,
                    'volumen'          => 3600.000,
                    'embalado'         => true,
                    'medida_embalaje'  => '62x62x5 cm',
                    'cantidad_por_caja'=> 4,
                ]);
            }

            // ════════════════════════════════════════════════════════════
            // ── PRODUCTOS COMPUESTOS — kits con BOM ──────────────────────
            // BOM completo definido en ComponenteSeeder
            // ════════════════════════════════════════════════════════════

            // Compuesto 1 — Kit Downlight 9W Empotrado Completo
            // Incluye: 1× Downlight 9W + 1× Driver 20W + 1.5m Cable 1.5mm² + 2× Wago
            $this->crearProducto('KY-KIT-DL9-001', [
                'codigo'                => 'KIT-DL9W-001',
                'codigo_fabrica'        => 'KY-KIT-DL9W-EMP',
                'nombre'                => 'Kit Downlight 9W Empotrado Completo',
                'descripcion'           => 'Kit listo para instalar: luminaria Downlight 9W + driver LED 20W + 1.5m de cable + 2 conectores Wago. Sin necesidad de comprar piezas por separado.',
                'categoria_id'          => $catLumiInt,
                'marca_id'              => $marcaKyrios,
                'tipo_producto_id'      => $tipoLU,
                'tipo_luminaria_id'     => $lumDL,
                'unidad_medida_id'      => $und,
                'tipo_sistema'          => 'compuesto',
                'descontar_componentes' => true,
                'procedencia'           => 'China',
                'linea'                 => 'Kyrios Pro',
                'stock_actual'          => 0,
                'stock_minimo'          => 5,
                'stock_maximo'          => 100,
                // 22.00 (downlight) + 18.00 (driver) + 1.5×1.20 (cable) + 2×0.85 (wago)
                'costo_promedio'        => 42.50,
            ]);

            // Compuesto 2 — Kit Panel LED Oficina 40W
            // Incluye: 1× Panel 40W + 1× Driver 60W + 2m Cable 2.5mm² + 4× Wago
            $this->crearProducto('KY-KIT-PA40-001', [
                'codigo'                => 'KIT-PA40-001',
                'codigo_fabrica'        => 'KY-KIT-PA40W-OF',
                'nombre'                => 'Kit Panel LED Oficina 40W',
                'descripcion'           => 'Kit completo para instalación de panel LED en techo de yeso u oficina: panel 60×60 + driver 60W + 2m de cable + 4 conectores Wago.',
                'categoria_id'          => $catLumiInt,
                'marca_id'              => $marcaKyrios,
                'tipo_producto_id'      => $tipoLU,
                'tipo_luminaria_id'     => $lumPA,
                'unidad_medida_id'      => $und,
                'tipo_sistema'          => 'compuesto',
                'descontar_componentes' => true,
                'procedencia'           => 'China',
                'linea'                 => 'Kyrios Office',
                'stock_actual'          => 0,
                'stock_minimo'          => 3,
                'stock_maximo'          => 80,
                // 48.00 (panel) + 38.00 (driver) + 2×1.90 (cable) + 4×0.85 (wago)
                'costo_promedio'        => 93.20,
            ]);
        });

        $this->command->info('✅ Productos de desarrollo creados:');
        $this->command->table(
            ['Código Kyrios', 'Nombre', 'Tipo'],
            [
                ['KY-CABL-001',    'Cable H07V-U 1.5mm² Negro',            'componente'],
                ['KY-CABL-002',    'Cable H07V-U 2.5mm² Negro',            'componente'],
                ['KY-DRIV-001',    'Driver LED 20W 12V DC',                'componente'],
                ['KY-DRIV-002',    'Driver LED 60W 24V DC',                'componente'],
                ['KY-CONE-001',    'Conector Wago 221-412',                'componente'],
                ['KY-LUEDL-001',   'Downlight LED 9W Redondo',             'simple'],
                ['KY-LUPA-001',    'Panel LED 40W 60×60cm',                'simple'],
                ['KY-KIT-DL9-001', 'Kit Downlight 9W Empotrado Completo',  'compuesto'],
                ['KY-KIT-PA40-001','Kit Panel LED Oficina 40W',            'compuesto'],
            ]
        );
    }

    // ─── Helper ──────────────────────────────────────────────────────────────

    /**
     * Crea o recupera un producto por su codigo_kyrios.
     * Aplica campos base comunes (estado, estado_aprobacion, tipo_inventario).
     */
    private function crearProducto(string $codigoKyrios, array $datos): Producto
    {
        return Producto::firstOrCreate(
            ['codigo_kyrios' => $codigoKyrios],
            array_merge([
                'tipo_inventario'  => 'cantidad',
                'estado'           => 'activo',
                'estado_aprobacion'=> 'aprobado',
                'ultimo_costo_compra' => $datos['costo_promedio'] ?? 0,
            ], $datos)
        );
    }
}
