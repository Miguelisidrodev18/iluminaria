<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

/**
 * Seeder exclusivo para entornos de desarrollo y testing.
 * NUNCA se ejecuta en producción.
 *
 * Crea:
 *   1. Usuarios demo para cada rol del sistema (contraseña: "password")
 *   2. Catálogo de productos de ejemplo (componentes, simples, compuestos)
 *   3. Variantes de productos con especificación
 *   4. BOM (Bill of Materials) de los productos compuestos
 */
class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('⚠️  DevelopmentSeeder omitido en producción.');
            return;
        }

        $this->seedUsuariosDemo();
    }

    // ─── Usuarios demo ────────────────────────────────────────────────────────

    private function seedUsuariosDemo(): void
    {
        $roles = Role::pluck('id', 'nombre');

        if ($roles->isEmpty()) {
            $this->command->error('❌ No hay roles. Ejecuta RoleSeeder primero.');
            return;
        }

        $usuarios = [
            [
                'name'  => 'Admin Demo',
                'email' => 'admin@demo.test',
                'role'  => 'Administrador',
            ],
            [
                'name'  => 'Supervisor Demo',
                'email' => 'supervisor@demo.test',
                'role'  => 'Supervisor',
            ],
            [
                'name'  => 'Vendedor Demo',
                'email' => 'vendedor@demo.test',
                'role'  => 'Vendedor',
            ],
            [
                'name'  => 'Almacenero Demo',
                'email' => 'almacenero@demo.test',
                'role'  => 'Almacenero',
            ],
            [
                'name'  => 'Compras Demo',
                'email' => 'compras@demo.test',
                'role'  => 'Compras',
            ],
            [
                'name'  => 'Tienda Demo',
                'email' => 'tienda@demo.test',
                'role'  => 'Tienda',
            ],
            [
                'name'  => 'Proveedor Demo',
                'email' => 'proveedor@demo.test',
                'role'  => 'Proveedor',
            ],
        ];

        foreach ($usuarios as $datos) {
            $roleId = $roles->get($datos['role']);

            if (!$roleId) {
                $this->command->warn("   ⚠️  Rol '{$datos['role']}' no encontrado, omitiendo {$datos['email']}");
                continue;
            }

            User::firstOrCreate(
                ['email' => $datos['email']],
                [
                    'name'     => $datos['name'],
                    'password' => Hash::make('password'),
                    'role_id'  => $roleId,
                    'estado'   => 'activo',
                ]
            );
        }

        $this->command->info('✅ Usuarios demo creados (contraseña: "password"):');
        foreach ($usuarios as $u) {
            $this->command->line("   [{$u['role']}]  {$u['email']}");
        }
    }
}
