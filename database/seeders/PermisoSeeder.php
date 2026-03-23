<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;
use App\Models\Role;

/**
 * Define la matriz completa de permisos del sistema y los asigna por rol.
 *
 * PERMISOS agrupados por módulo:
 *   productos   → CRUD, aprobación, importación, BOM
 *   inventario  → stock, movimientos, ajustes
 *   compras     → órdenes de compra, proveedores
 *   ventas      → ventas, clientes, precios, descuentos
 *   almacen     → almacenes, ubicaciones, traslados
 *   catalogo    → categorías, marcas, atributos
 *   admin       → usuarios, roles, empresa, reportes, auditorías
 *
 * ROLES y su nivel de acceso:
 *   Administrador → TODOS los permisos
 *   Supervisor    → Casi todo, sin gestión de usuarios/roles/empresa
 *   Almacenero    → Inventario, productos (sin aprobar/eliminar), almacén
 *   Compras       → Órdenes de compra, proveedores, costos
 *   Vendedor      → Ventas, clientes, consulta de inventario
 *   Tienda        → Ventas al público, consulta básica
 *   Proveedor     → Solo consulta catálogo
 *
 * Idempotente: se puede ejecutar múltiples veces sin duplicar ni perder datos.
 * Usa firstOrCreate para permisos y syncWithoutDetaching para asignaciones.
 */
class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermisos();
        $this->asignarPermisosPorRol();

        $this->command->info('✅ Permisos definidos y asignados por rol correctamente.');
    }

    // ─── Definición completa de permisos ─────────────────────────────────────

    private function seedPermisos(): void
    {
        $permisos = [

            // ── Módulo: Productos ──────────────────────────────────────────
            ['nombre' => 'ver_productos',        'etiqueta' => 'Ver Productos',               'grupo' => 'productos',  'descripcion' => 'Listar y consultar el catálogo de productos.'],
            ['nombre' => 'crear_producto',       'etiqueta' => 'Crear Productos',             'grupo' => 'productos',  'descripcion' => 'Registrar nuevos productos en el sistema.'],
            ['nombre' => 'editar_producto',      'etiqueta' => 'Editar Productos',            'grupo' => 'productos',  'descripcion' => 'Modificar datos de productos existentes.'],
            ['nombre' => 'eliminar_producto',    'etiqueta' => 'Eliminar Productos',          'grupo' => 'productos',  'descripcion' => 'Dar de baja o eliminar productos del catálogo.'],
            ['nombre' => 'aprobar_producto',     'etiqueta' => 'Aprobar / Rechazar Productos','grupo' => 'productos',  'descripcion' => 'Gestionar el flujo de aprobación de productos.'],
            ['nombre' => 'importar_productos',   'etiqueta' => 'Importación Masiva',          'grupo' => 'productos',  'descripcion' => 'Importar productos en lote mediante Excel o CSV.'],
            ['nombre' => 'gestionar_bom',        'etiqueta' => 'Gestionar BOM / Kits',        'grupo' => 'productos',  'descripcion' => 'Definir y editar la lista de materiales (BOM) de productos compuestos.'],
            ['nombre' => 'gestionar_variantes',  'etiqueta' => 'Gestionar Variantes',         'grupo' => 'productos',  'descripcion' => 'Crear y editar variantes de productos (potencia, temperatura, color).'],
            ['nombre' => 'gestionar_precios',    'etiqueta' => 'Gestionar Precios',           'grupo' => 'productos',  'descripcion' => 'Crear y editar listas de precios de venta del producto.'],

            // ── Módulo: Inventario ─────────────────────────────────────────
            ['nombre' => 'ver_inventario',       'etiqueta' => 'Ver Inventario',              'grupo' => 'inventario', 'descripcion' => 'Consultar niveles de stock por producto y almacén.'],
            ['nombre' => 'gestionar_stock',      'etiqueta' => 'Gestionar Stock',             'grupo' => 'inventario', 'descripcion' => 'Actualizar cantidades de stock manualmente.'],
            ['nombre' => 'ver_movimientos',      'etiqueta' => 'Ver Movimientos',             'grupo' => 'inventario', 'descripcion' => 'Consultar el historial de movimientos de inventario.'],
            ['nombre' => 'crear_movimiento',     'etiqueta' => 'Crear Movimientos',           'grupo' => 'inventario', 'descripcion' => 'Registrar entradas, salidas y traslados de inventario.'],
            ['nombre' => 'ajustar_inventario',   'etiqueta' => 'Ajustar Inventario',          'grupo' => 'inventario', 'descripcion' => 'Realizar ajustes de inventario por diferencia de conteo o merma.'],

            // ── Módulo: Compras ────────────────────────────────────────────
            ['nombre' => 'ver_compras',          'etiqueta' => 'Ver Compras',                 'grupo' => 'compras',    'descripcion' => 'Consultar órdenes de compra y su estado.'],
            ['nombre' => 'crear_compra',         'etiqueta' => 'Crear Órdenes de Compra',     'grupo' => 'compras',    'descripcion' => 'Generar nuevas órdenes de compra a proveedores.'],
            ['nombre' => 'editar_compra',        'etiqueta' => 'Editar Órdenes de Compra',    'grupo' => 'compras',    'descripcion' => 'Modificar órdenes de compra pendientes.'],
            ['nombre' => 'aprobar_compra',       'etiqueta' => 'Aprobar Órdenes de Compra',   'grupo' => 'compras',    'descripcion' => 'Autorizar o rechazar órdenes de compra.'],
            ['nombre' => 'gestionar_proveedores','etiqueta' => 'Gestionar Proveedores',       'grupo' => 'compras',    'descripcion' => 'Crear, editar y desactivar proveedores.'],
            ['nombre' => 'ver_costos',           'etiqueta' => 'Ver Costos de Compra',        'grupo' => 'compras',    'descripcion' => 'Consultar costos de compra y márgenes de productos.'],

            // ── Módulo: Ventas ─────────────────────────────────────────────
            ['nombre' => 'ver_ventas',           'etiqueta' => 'Ver Ventas',                  'grupo' => 'ventas',     'descripcion' => 'Consultar pedidos, ventas y su historial.'],
            ['nombre' => 'crear_venta',          'etiqueta' => 'Crear Ventas',                'grupo' => 'ventas',     'descripcion' => 'Registrar nuevas ventas o pedidos de clientes.'],
            ['nombre' => 'editar_venta',         'etiqueta' => 'Editar Ventas',               'grupo' => 'ventas',     'descripcion' => 'Modificar ventas o pedidos en curso.'],
            ['nombre' => 'anular_venta',         'etiqueta' => 'Anular Ventas',               'grupo' => 'ventas',     'descripcion' => 'Anular o revertir ventas ya registradas.'],
            ['nombre' => 'gestionar_clientes',   'etiqueta' => 'Gestionar Clientes',          'grupo' => 'ventas',     'descripcion' => 'Crear, editar y consultar datos de clientes.'],
            ['nombre' => 'editar_precios',       'etiqueta' => 'Editar Precios de Venta',     'grupo' => 'ventas',     'descripcion' => 'Modificar precios de venta en cotizaciones o pedidos.'],
            ['nombre' => 'aplicar_descuentos',   'etiqueta' => 'Aplicar Descuentos',          'grupo' => 'ventas',     'descripcion' => 'Aplicar descuentos a líneas de venta o al total.'],

            // ── Módulo: Almacén ────────────────────────────────────────────
            ['nombre' => 'ver_almacenes',        'etiqueta' => 'Ver Almacenes',               'grupo' => 'almacen',    'descripcion' => 'Consultar almacenes y sus ubicaciones.'],
            ['nombre' => 'gestionar_almacenes',  'etiqueta' => 'Gestionar Almacenes',         'grupo' => 'almacen',    'descripcion' => 'Crear y configurar almacenes.'],
            ['nombre' => 'gestionar_ubicaciones','etiqueta' => 'Gestionar Ubicaciones',       'grupo' => 'almacen',    'descripcion' => 'Definir y editar ubicaciones físicas dentro de almacenes.'],
            ['nombre' => 'realizar_traslados',   'etiqueta' => 'Realizar Traslados',          'grupo' => 'almacen',    'descripcion' => 'Mover stock entre almacenes o ubicaciones.'],

            // ── Módulo: Catálogo ───────────────────────────────────────────
            ['nombre' => 'gestionar_categorias', 'etiqueta' => 'Gestionar Categorías',        'grupo' => 'catalogo',   'descripcion' => 'Crear y editar categorías de productos.'],
            ['nombre' => 'gestionar_marcas',     'etiqueta' => 'Gestionar Marcas',            'grupo' => 'catalogo',   'descripcion' => 'Crear y editar marcas del catálogo.'],
            ['nombre' => 'gestionar_atributos',  'etiqueta' => 'Gestionar Atributos',         'grupo' => 'catalogo',   'descripcion' => 'Administrar el sistema de atributos dinámicos de productos.'],

            // ── Módulo: Administración ─────────────────────────────────────
            ['nombre' => 'gestionar_usuarios',   'etiqueta' => 'Gestionar Usuarios',          'grupo' => 'admin',      'descripcion' => 'Crear, editar y desactivar usuarios del sistema.'],
            ['nombre' => 'gestionar_roles',      'etiqueta' => 'Gestionar Roles y Permisos',  'grupo' => 'admin',      'descripcion' => 'Asignar roles y ajustar permisos por rol.'],
            ['nombre' => 'ver_reportes',         'etiqueta' => 'Ver Reportes',                'grupo' => 'admin',      'descripcion' => 'Acceder a reportes de ventas, compras e inventario.'],
            ['nombre' => 'configurar_empresa',   'etiqueta' => 'Configurar Empresa',          'grupo' => 'admin',      'descripcion' => 'Gestionar datos de la empresa, sucursales y series de comprobantes.'],
            ['nombre' => 'ver_auditorias',       'etiqueta' => 'Ver Auditorías',              'grupo' => 'admin',      'descripcion' => 'Consultar el registro de actividad y cambios del sistema.'],
        ];

        foreach ($permisos as $p) {
            Permiso::firstOrCreate(
                ['nombre' => $p['nombre']],
                [
                    'etiqueta'    => $p['etiqueta'],
                    'grupo'       => $p['grupo'],
                    'descripcion' => $p['descripcion'],
                ]
            );
        }

        $this->command->line('   → ' . count($permisos) . ' permisos definidos.');
    }

    // ─── Asignación de permisos por rol ───────────────────────────────────────

    private function asignarPermisosPorRol(): void
    {
        // null = todos los permisos del sistema
        $mapa = [

            'Administrador' => null,

            'Supervisor' => [
                // Productos (todo excepto eliminar)
                'ver_productos', 'crear_producto', 'editar_producto', 'aprobar_producto',
                'importar_productos', 'gestionar_bom', 'gestionar_variantes', 'gestionar_precios',
                // Inventario completo
                'ver_inventario', 'gestionar_stock', 'ver_movimientos', 'crear_movimiento', 'ajustar_inventario',
                // Compras completo
                'ver_compras', 'crear_compra', 'editar_compra', 'aprobar_compra', 'gestionar_proveedores', 'ver_costos',
                // Ventas completo
                'ver_ventas', 'crear_venta', 'editar_venta', 'anular_venta',
                'gestionar_clientes', 'editar_precios', 'aplicar_descuentos',
                // Almacén completo
                'ver_almacenes', 'gestionar_almacenes', 'gestionar_ubicaciones', 'realizar_traslados',
                // Catálogo completo
                'gestionar_categorias', 'gestionar_marcas', 'gestionar_atributos',
                // Reportes (sin gestionar usuarios/roles/empresa)
                'ver_reportes',
            ],

            'Almacenero' => [
                // Productos: crear/editar, BOM y variantes (no aprobar, no eliminar)
                'ver_productos', 'crear_producto', 'editar_producto',
                'gestionar_bom', 'gestionar_variantes',
                // Inventario completo
                'ver_inventario', 'gestionar_stock', 'ver_movimientos', 'crear_movimiento', 'ajustar_inventario',
                // Almacén completo
                'ver_almacenes', 'gestionar_almacenes', 'gestionar_ubicaciones', 'realizar_traslados',
                // Costos (necesita saber precios de compra al mover stock)
                'ver_costos',
            ],

            'Compras' => [
                // Productos: consulta y carga básica para asociar a compras
                'ver_productos', 'crear_producto', 'editar_producto',
                // Inventario: solo consulta (no ajusta)
                'ver_inventario', 'ver_movimientos',
                // Compras completo
                'ver_compras', 'crear_compra', 'editar_compra', 'gestionar_proveedores', 'ver_costos',
                // Catálogo (para mantener marcas al importar)
                'gestionar_marcas',
            ],

            'Vendedor' => [
                // Productos: solo consulta
                'ver_productos',
                // Inventario: solo consulta
                'ver_inventario',
                // Ventas: crear y gestionar clientes; editar precio en su venta
                'ver_ventas', 'crear_venta', 'editar_venta',
                'gestionar_clientes', 'editar_precios', 'aplicar_descuentos',
            ],

            'Tienda' => [
                // Consulta básica
                'ver_productos', 'ver_inventario',
                // Ventas al mostrador
                'ver_ventas', 'crear_venta', 'gestionar_clientes',
            ],

            'Proveedor' => [
                // Acceso de solo lectura al catálogo
                'ver_productos',
            ],
        ];

        $roles         = Role::with('permisos')->get()->keyBy('nombre');
        $todosPermisos = Permiso::pluck('id', 'nombre');

        foreach ($mapa as $rolNombre => $slugs) {
            $role = $roles->get($rolNombre);
            if (!$role) {
                $this->command->warn("   ⚠️  Rol '{$rolNombre}' no encontrado, omitiendo.");
                continue;
            }

            $ids = $slugs === null
                ? $todosPermisos->values()->toArray()
                : collect($slugs)
                    ->map(fn ($s) => $todosPermisos->get($s))
                    ->filter()
                    ->values()
                    ->toArray();

            // syncWithoutDetaching: agrega sin quitar permisos asignados manualmente
            $role->permisos()->syncWithoutDetaching($ids);

            $this->command->line("   → {$rolNombre}: " . count($ids) . ' permisos asignados.');
        }
    }
}
