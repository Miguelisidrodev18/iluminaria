<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Roles del sistema. Idempotente: se puede ejecutar múltiples veces sin duplicar.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre'      => 'Administrador',
                'descripcion' => 'Acceso total al sistema. Gestiona usuarios, roles, empresa, inventario, compras, ventas y reportes.',
            ],
            [
                'nombre'      => 'Supervisor',
                'descripcion' => 'Supervisa operaciones generales. Aprueba productos, compras y ventas. Accede a reportes. Sin administración de usuarios.',
            ],
            [
                'nombre'      => 'Vendedor',
                'descripcion' => 'Gestión de ventas y clientes. Puede crear ventas, consultar inventario y gestionar cartera de clientes.',
            ],
            [
                'nombre'      => 'Almacenero',
                'descripcion' => 'Gestión de inventario y almacenes. Crea y edita productos, gestiona stock, movimientos y traslados.',
            ],
            [
                'nombre'      => 'Compras',
                'descripcion' => 'Gestión del abastecimiento. Crea y edita órdenes de compra, gestiona proveedores y consulta costos.',
            ],
            [
                'nombre'      => 'Proveedor',
                'descripcion' => 'Acceso externo limitado. Consulta el catálogo de productos disponibles.',
            ],
            [
                'nombre'      => 'Tienda',
                'descripcion' => 'Punto de venta. Registra ventas al público y gestiona clientes en tienda física.',
            ],
        ];

        foreach ($roles as $rol) {
            Role::firstOrCreate(
                ['nombre' => $rol['nombre']],
                ['descripcion' => $rol['descripcion']]
            );
        }

        $this->command->info('✅ Roles verificados/creados: Administrador, Supervisor, Vendedor, Almacenero, Compras, Proveedor, Tienda.');
    }
}
