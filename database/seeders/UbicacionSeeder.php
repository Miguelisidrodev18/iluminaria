<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ubicacion;

/**
 * Ubicaciones físicas del sistema.
 *
 * Tipos disponibles (según Ubicacion::TIPOS):
 *   almacen  → Almacén de inventario
 *   showroom → Sala de exhibición
 *   taller   → Taller de ensamblaje / reparación
 *   tienda   → Punto de venta al público
 *
 * Idempotente: usa firstOrCreate sobre nombre único.
 */
class UbicacionSeeder extends Seeder
{
    public function run(): void
    {
        $ubicaciones = [
            [
                'nombre'      => 'Almacén Principal',
                'tipo'        => 'almacen',
                'descripcion' => 'Almacén central de la empresa. Recepción de compras, almacenamiento masivo y despacho general de pedidos.',
                'estado'      => 'activo',
            ],
            [
                'nombre'      => 'Almacén Secundario',
                'tipo'        => 'almacen',
                'descripcion' => 'Almacén de desbordamiento para productos de baja rotación, devoluciones pendientes y stock de seguridad.',
                'estado'      => 'activo',
            ],
            [
                'nombre'      => 'Showroom Lima',
                'tipo'        => 'showroom',
                'descripcion' => 'Sala de exhibición con muestras de luminarias disponibles para clientes y proyectistas.',
                'estado'      => 'activo',
            ],
            [
                'nombre'      => 'Taller de Instalaciones',
                'tipo'        => 'taller',
                'descripcion' => 'Área interna de ensamblaje, pruebas eléctricas y reparación de luminarias y kits.',
                'estado'      => 'activo',
            ],
            [
                'nombre'      => 'Tienda Física',
                'tipo'        => 'tienda',
                'descripcion' => 'Punto de venta al público. Atención directa y retiro de pedidos contra entrega.',
                'estado'      => 'activo',
            ],
        ];

        foreach ($ubicaciones as $u) {
            Ubicacion::firstOrCreate(
                ['nombre' => $u['nombre']],
                [
                    'tipo'        => $u['tipo'],
                    'descripcion' => $u['descripcion'],
                    'estado'      => $u['estado'],
                ]
            );
        }

        $this->command->info('✅ Ubicaciones verificadas/creadas: ' . count($ubicaciones) . ' ubicaciones.');
    }
}
