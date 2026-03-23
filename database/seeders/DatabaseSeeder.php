<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Punto de entrada de todos los seeders del sistema.
 *
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │  SEEDERS DE PRODUCCIÓN (siempre se ejecutan)                        │
 * │                                                                     │
 * │  1. RoleSeeder              Roles del sistema                       │
 * │  2. PermisoSeeder           Permisos y asignación por rol           │
 * │  3. CatalogoSeeder          Unidades, colores, motivos, marca base  │
 * │  4. CatalogoLuminariasSeeder Marcas y colores reales de iluminación │
 * │  5. CategoriaSeeder         Categorías de productos                 │
 * │  6. UbicacionSeeder         Ubicaciones físicas del sistema         │
 * │  7. AdminUserSeeder         Usuario administrador principal         │
 * │  8. TipoProyectoSeeder      Tipos de proyecto (Residencial, etc.)   │
 * │  9. EspacioProyectoSeeder   Espacios por tipo de proyecto           │
 * │ 10. AtributosLuminariasSeeder Sistema dinámico de atributos         │
 * └─────────────────────────────────────────────────────────────────────┘
 *
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │  SEEDERS DE DESARROLLO (solo local / development / testing)         │
 * │                                                                     │
 * │  DevelopmentSeeder                                                  │
 * │    └─ seedUsuariosDemo     Un usuario por cada rol                  │
 * │    └─ ProductoSeeder       Componentes, simples y kits de ejemplo   │
 * │    └─ VarianteSeeder       Variantes del downlight por temperatura  │
 * │    └─ ComponenteSeeder     BOM (Bill of Materials) de los kits      │
 * └─────────────────────────────────────────────────────────────────────┘
 *
 * Variables de entorno para producción:
 *   ADMIN_EMAIL=admin@tuempresa.com
 *   ADMIN_NAME="Nombre Administrador"
 *   ADMIN_PASSWORD=contraseña_segura
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Datos base (producción y desarrollo) ─────────────────────────
        $this->call([
            RoleSeeder::class,               // 1. Roles del sistema
            PermisoSeeder::class,            // 2. Permisos por rol
            CatalogoSeeder::class,           // 3. Unidades, colores, motivos, Genérico
            CatalogoLuminariasSeeder::class, // 4. Marcas y colores de iluminación
            CategoriaSeeder::class,          // 5. Categorías de productos
            UbicacionSeeder::class,          // 6. Ubicaciones físicas
            AdminUserSeeder::class,          // 7. Usuario administrador
            TipoProyectoSeeder::class,       // 8. Tipos de proyecto
            EspacioProyectoSeeder::class,    // 9. Espacios por tipo de proyecto
            AtributosLuminariasSeeder::class,// 10. Atributos dinámicos de luminarias
        ]);

        // ── Datos de prueba (solo entornos no productivos) ───────────────
        if (app()->environment(['local', 'development', 'testing'])) {
            $this->call(DevelopmentSeeder::class);
        }
    }
}
