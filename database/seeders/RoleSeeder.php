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
            [
                'nombre'      => 'Logística',
                'descripcion' => 'Gestión de fábricas y proveedores. Crea y aprueba órdenes de compra, gestiona proveedores y controla el flujo de abastecimiento.',
            ],
            [
                'nombre'      => 'Cliente',
                'descripcion' => 'Acceso externo para clientes. Puede realizar pedidos y hacer seguimiento al estado de arribo (en tránsito, en aduanas, en almacén, listo para entrega).',
            ],
            [
                'nombre'      => 'Administración',
                'descripcion' => 'Acceso administrativo completo: inventario, facturación, pagos, caja y reportes. Sin gestión de usuarios ni roles.',
            ],
            [
                'nombre'      => 'Operaciones',
                'descripcion' => 'Programación de tareas internas y externas (instalaciones). Coordina proyectos, clientes y logística de campo.',
            ],
            [
                'nombre'      => 'Contador',
                'descripcion' => 'Acceso a información financiera: boletas, facturas, pagos, cuentas por pagar y reportes contables.',
            ],
        ];

        foreach ($roles as $rol) {
            Role::firstOrCreate(
                ['nombre' => $rol['nombre']],
                ['descripcion' => $rol['descripcion']]
            );
        }

        $this->command->info('✅ Roles verificados/creados: Administrador, Supervisor, Vendedor, Almacenero, Compras, Proveedor, Tienda, Logística, Cliente, Administración, Operaciones, Contador.');
    }
}
