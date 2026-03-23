<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\ProductoComponente;

/**
 * BOM (Bill of Materials) para productos compuestos.
 * Define qué componentes y en qué cantidad conforman cada kit.
 *
 * Requiere ProductoSeeder primero.
 *
 * Estructura del BOM:
 *   padre_id   → producto compuesto (Kit)
 *   hijo_id    → componente o producto simple
 *   cantidad   → unidades necesarias por 1 kit
 *   unidad     → unidad de medida de esa cantidad (UND, MT, etc.)
 *   es_opcional → si se puede omitir sin romper el kit
 *   orden      → secuencia de ensamblaje / presentación
 *
 * BOMs definidos:
 *   KY-KIT-DL9-001  (Kit Downlight 9W Empotrado Completo)
 *     1× KY-LUEDL-001  Downlight LED 9W
 *     1× KY-DRIV-001   Driver LED 20W 12V DC
 *     1.5m KY-CABL-001 Cable H07V-U 1.5mm²
 *     2× KY-CONE-001   Conector Wago 221-412
 *
 *   KY-KIT-PA40-001  (Kit Panel LED Oficina 40W)
 *     1× KY-LUPA-001   Panel LED 40W 60×60cm
 *     1× KY-DRIV-002   Driver LED 60W 24V DC
 *     2m KY-CABL-002   Cable H07V-U 2.5mm²
 *     4× KY-CONE-001   Conector Wago 221-412 (opcional)
 *
 * NUNCA ejecutar directamente en producción.
 */
class ComponenteSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('⚠️  ComponenteSeeder omitido en producción.');
            return;
        }

        $this->bomKitDownlight();
        $this->bomKitPanel();

        $this->command->info('✅ BOM (Bill of Materials) definido correctamente.');
    }

    // ─── BOM: Kit Downlight 9W Empotrado ─────────────────────────────────────

    private function bomKitDownlight(): void
    {
        $kit       = Producto::where('codigo_kyrios', 'KY-KIT-DL9-001')->first();
        $downlight = Producto::where('codigo_kyrios', 'KY-LUEDL-001')->first();
        $driver20  = Producto::where('codigo_kyrios', 'KY-DRIV-001')->first();
        $cable15   = Producto::where('codigo_kyrios', 'KY-CABL-001')->first();
        $wago      = Producto::where('codigo_kyrios', 'KY-CONE-001')->first();

        if (!$kit || !$downlight || !$driver20 || !$cable15 || !$wago) {
            $this->command->warn('⚠️  Kit Downlight: uno o más productos no encontrados. Verifica ProductoSeeder.');
            return;
        }

        $lineas = [
            [
                'padre_id'    => $kit->id,
                'hijo_id'     => $downlight->id,
                'cantidad'    => 1,
                'unidad'      => 'UND',
                'es_opcional' => false,
                'orden'       => 1,
                'observacion' => 'Luminaria principal del kit.',
            ],
            [
                'padre_id'    => $kit->id,
                'hijo_id'     => $driver20->id,
                'cantidad'    => 1,
                'unidad'      => 'UND',
                'es_opcional' => false,
                'orden'       => 2,
                'observacion' => 'Alimentación 20W para el downlight.',
            ],
            [
                'padre_id'    => $kit->id,
                'hijo_id'     => $cable15->id,
                'cantidad'    => 1.5,
                'unidad'      => 'MT',
                'es_opcional' => false,
                'orden'       => 3,
                'observacion' => '1.5 metros de cable de conexión al driver.',
            ],
            [
                'padre_id'    => $kit->id,
                'hijo_id'     => $wago->id,
                'cantidad'    => 2,
                'unidad'      => 'UND',
                'es_opcional' => false,
                'orden'       => 4,
                'observacion' => 'Conectores de empalme rápido para las uniones.',
            ],
        ];

        $this->insertarLineas($lineas, 'Kit Downlight 9W');
    }

    // ─── BOM: Kit Panel LED Oficina 40W ──────────────────────────────────────

    private function bomKitPanel(): void
    {
        $kit     = Producto::where('codigo_kyrios', 'KY-KIT-PA40-001')->first();
        $panel   = Producto::where('codigo_kyrios', 'KY-LUPA-001')->first();
        $driver60= Producto::where('codigo_kyrios', 'KY-DRIV-002')->first();
        $cable25 = Producto::where('codigo_kyrios', 'KY-CABL-002')->first();
        $wago    = Producto::where('codigo_kyrios', 'KY-CONE-001')->first();

        if (!$kit || !$panel || !$driver60 || !$cable25 || !$wago) {
            $this->command->warn('⚠️  Kit Panel: uno o más productos no encontrados. Verifica ProductoSeeder.');
            return;
        }

        $lineas = [
            [
                'padre_id'    => $kit->id,
                'hijo_id'     => $panel->id,
                'cantidad'    => 1,
                'unidad'      => 'UND',
                'es_opcional' => false,
                'orden'       => 1,
                'observacion' => 'Panel LED 60×60cm retroiluminado.',
            ],
            [
                'padre_id'    => $kit->id,
                'hijo_id'     => $driver60->id,
                'cantidad'    => 1,
                'unidad'      => 'UND',
                'es_opcional' => false,
                'orden'       => 2,
                'observacion' => 'Driver 60W para alimentar el panel LED.',
            ],
            [
                'padre_id'    => $kit->id,
                'hijo_id'     => $cable25->id,
                'cantidad'    => 2,
                'unidad'      => 'MT',
                'es_opcional' => false,
                'orden'       => 3,
                'observacion' => '2 metros de cable de conexión entre panel y driver.',
            ],
            [
                'padre_id'    => $kit->id,
                'hijo_id'     => $wago->id,
                'cantidad'    => 4,
                'unidad'      => 'UND',
                'es_opcional' => true,
                'orden'       => 4,
                'observacion' => 'Conectores de empalme. Opcionales si el instalador usa bornes de tornillo.',
            ],
        ];

        $this->insertarLineas($lineas, 'Kit Panel LED 40W');
    }

    // ─── Helper ──────────────────────────────────────────────────────────────

    private function insertarLineas(array $lineas, string $nombreKit): void
    {
        $insertadas = 0;
        foreach ($lineas as $linea) {
            ProductoComponente::firstOrCreate(
                [
                    'padre_id' => $linea['padre_id'],
                    'hijo_id'  => $linea['hijo_id'],
                ],
                $linea
            );
            $insertadas++;
        }
        $this->command->line("   → {$nombreKit}: {$insertadas} líneas BOM.");
    }
}
