<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Catalogo\Color;

/**
 * Variantes de productos para entornos de desarrollo.
 * Requiere que ProductoSeeder haya corrido primero.
 *
 * Variantes creadas:
 *   Downlight LED 9W (KY-LUEDL-001):
 *     • 3000K Blanco  (Blanco cálido, uso residencial)
 *     • 4000K Blanco  (Blanco neutro, uso comercial — la más vendida)
 *     • 6000K Blanco  (Blanco frío, uso industrial)
 *     • 4000K Negro   (Acabado negro mate, +sobreprecio S/3)
 *     • 12W / 4000K Blanco  (Potencia superior, +sobreprecio S/8)
 *
 * Patrón de SKU: [CODIGO_BASE]-[SPEC]-[COL]
 *   Ej:  KY-LUEDL-001-3K-BL  /  KY-LUEDL-001-4K-NK
 *
 * NUNCA ejecutar directamente en producción.
 */
class VarianteSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('⚠️  VarianteSeeder omitido en producción.');
            return;
        }

        $this->seedVariantesDownlight();

        $this->command->info('✅ Variantes de productos creadas correctamente.');
    }

    // ─── Variantes: Downlight LED 9W ─────────────────────────────────────────

    private function seedVariantesDownlight(): void
    {
        $downlight = Producto::where('codigo_kyrios', 'KY-LUEDL-001')->first();

        if (!$downlight) {
            $this->command->warn('⚠️  Downlight (KY-LUEDL-001) no encontrado. Ejecuta ProductoSeeder primero.');
            return;
        }

        $idBlanco = Color::where('nombre', 'Blanco')->value('id');
        $idNegro  = Color::where('nombre', 'Negro Mate')->value('id')
                 ?? Color::where('nombre', 'Negro')->value('id');

        $variantes = [
            [
                'sku'            => 'KY-LUEDL-001-3K-BL',
                'nombre'         => 'Downlight 9W 3000K Blanco',
                'especificacion' => '3000K',
                'color_id'       => $idBlanco,
                'sobreprecio'    => 0.00,
                'stock_actual'   => 25,
                'stock_minimo'   => 5,
                'atributos'      => ['temperatura_color' => '3000K', 'potencia_w' => '9'],
            ],
            [
                'sku'            => 'KY-LUEDL-001-4K-BL',
                'nombre'         => 'Downlight 9W 4000K Blanco',
                'especificacion' => '4000K',
                'color_id'       => $idBlanco,
                'sobreprecio'    => 0.00,
                'stock_actual'   => 40,
                'stock_minimo'   => 5,
                'atributos'      => ['temperatura_color' => '4000K', 'potencia_w' => '9'],
            ],
            [
                'sku'            => 'KY-LUEDL-001-6K-BL',
                'nombre'         => 'Downlight 9W 6000K Blanco',
                'especificacion' => '6000K',
                'color_id'       => $idBlanco,
                'sobreprecio'    => 0.00,
                'stock_actual'   => 15,
                'stock_minimo'   => 5,
                'atributos'      => ['temperatura_color' => '6000K', 'potencia_w' => '9'],
            ],
            [
                'sku'            => 'KY-LUEDL-001-4K-NK',
                'nombre'         => 'Downlight 9W 4000K Negro Mate',
                'especificacion' => '4000K',
                'color_id'       => $idNegro,
                'sobreprecio'    => 3.00,
                'stock_actual'   => 10,
                'stock_minimo'   => 3,
                'atributos'      => ['temperatura_color' => '4000K', 'potencia_w' => '9'],
            ],
            [
                'sku'            => 'KY-LUEDL-001-12W-BL',
                'nombre'         => 'Downlight 12W 4000K Blanco',
                'especificacion' => '12W / 4000K',
                'color_id'       => $idBlanco,
                'sobreprecio'    => 8.00,
                'stock_actual'   => 20,
                'stock_minimo'   => 5,
                'atributos'      => ['temperatura_color' => '4000K', 'potencia_w' => '12'],
            ],
        ];

        $stockTotal = 0;

        foreach ($variantes as $v) {
            ProductoVariante::firstOrCreate(
                ['sku' => $v['sku']],
                array_merge($v, [
                    'producto_id' => $downlight->id,
                    'estado'      => 'activo',
                ])
            );
            $stockTotal += $v['stock_actual'];
        }

        // Sincronizar stock total en el producto base
        $stockReal = ProductoVariante::where('producto_id', $downlight->id)
                        ->where('estado', 'activo')
                        ->sum('stock_actual');
        $downlight->update(['stock_actual' => $stockReal]);

        $this->command->line("   → Downlight 9W: " . count($variantes) . " variantes / stock total: {$stockReal} uds.");
    }
}
