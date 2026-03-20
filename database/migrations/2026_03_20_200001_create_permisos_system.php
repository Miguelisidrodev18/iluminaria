<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Crea tabla de permisos y pivot role_permiso.
     * Siembra permisos base y asigna por rol.
     */
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();     // slug: crear_producto
            $table->string('etiqueta', 150);             // UI: "Crear Productos"
            $table->string('grupo', 80)->default('general'); // agrupación en UI
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('role_permiso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permiso_id']);
        });

        // Permisos base del sistema
        $permisos = [
            ['nombre' => 'crear_producto',   'etiqueta' => 'Crear Productos',      'grupo' => 'productos'],
            ['nombre' => 'editar_producto',  'etiqueta' => 'Editar Productos',     'grupo' => 'productos'],
            ['nombre' => 'eliminar_producto','etiqueta' => 'Eliminar Productos',   'grupo' => 'productos'],
            ['nombre' => 'aprobar_producto', 'etiqueta' => 'Aprobar Productos',    'grupo' => 'productos'],
            ['nombre' => 'editar_precios',   'etiqueta' => 'Editar Precios',       'grupo' => 'precios'],
            ['nombre' => 'ver_costos',       'etiqueta' => 'Ver Costos de Compra', 'grupo' => 'precios'],
            ['nombre' => 'gestionar_marcas', 'etiqueta' => 'Gestionar Marcas',     'grupo' => 'catalogo'],
            ['nombre' => 'gestionar_usuarios','etiqueta' => 'Gestionar Usuarios',  'grupo' => 'admin'],
        ];

        foreach ($permisos as $p) {
            DB::table('permisos')->insert(array_merge($p, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Asignar todos los permisos al Administrador
        $adminRole = DB::table('roles')->where('nombre', 'Administrador')->first();
        if ($adminRole) {
            $todosIds = DB::table('permisos')->pluck('id');
            foreach ($todosIds as $pid) {
                DB::table('role_permiso')->insert([
                    'role_id'    => $adminRole->id,
                    'permiso_id' => $pid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Almacenero: crear y editar productos (sin eliminar, sin precios)
        $almaceneroRole = DB::table('roles')->where('nombre', 'Almacenero')->first();
        if ($almaceneroRole) {
            $permisosAlmacenero = DB::table('permisos')
                ->whereIn('nombre', ['crear_producto', 'editar_producto', 'ver_costos'])
                ->pluck('id');
            foreach ($permisosAlmacenero as $pid) {
                DB::table('role_permiso')->insert([
                    'role_id'    => $almaceneroRole->id,
                    'permiso_id' => $pid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Vendedor: solo ver costos y editar precios (si se le concede individualmente)
        // Por defecto el vendedor no tiene permisos de productos
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permiso');
        Schema::dropIfExists('permisos');
    }
};
