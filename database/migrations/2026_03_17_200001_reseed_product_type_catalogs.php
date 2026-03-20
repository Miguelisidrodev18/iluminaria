<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. Eliminar unique constraint en tipos_luminaria.codigo ──────────
        // Es necesario porque existen tipos con el mismo código (ej: baliza y estaca → 17)
        Schema::table('tipos_luminaria', function (Blueprint $table) {
            $table->dropUnique('tipos_luminaria_codigo_unique');
        });

        // ─── 2. Limpiar catálogos anteriores (sin datos de negocio reales) ────
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('tipos_luminaria')->truncate();
        DB::table('tipos_producto')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ─── 3. Insertar 14 tipos de producto oficiales (Excel Kyrios) ────────
        DB::table('tipos_producto')->insert([
            // usa_tipo_luminaria=1 solo para Luminaria, que tiene subtipos reales
            ['nombre' => 'Luminaria',                       'codigo' => 'LU', 'usa_tipo_luminaria' => 1, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Lámpara',                         'codigo' => 'LA', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cinta LED',                       'codigo' => 'CL', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Sistema modular',                 'codigo' => 'SM', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Accesorio',                       'codigo' => 'AC', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Fuente (driver, transformador)',  'codigo' => 'EA', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Perfiles',                        'codigo' => 'PE', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pantalla',                        'codigo' => 'PA', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ventilador',                      'codigo' => 'VE', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cabezal',                         'codigo' => 'CA', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Poste',                           'codigo' => 'PO', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Luces de emergencia',             'codigo' => 'LE', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Solares',                         'codigo' => 'SO', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Recargables',                     'codigo' => 'RE', 'usa_tipo_luminaria' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ─── 4. Insertar 14 tipos de luminaria oficiales (Excel Kyrios) ───────
        // Nota: algunos comparten código (de mesa/de escritorio → 10; estaca/baliza → 17)
        // El campo codigo_kyrios usa estos códigos directamente en el prefijo
        DB::table('tipos_luminaria')->insert([
            ['nombre' => 'De pie',              'codigo' => '25', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'De mesa',             'codigo' => '10', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'De escritorio',       'codigo' => '10', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Braquete',            'codigo' => '05', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Colgante',            'codigo' => '20', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Techo (plafón / adosado)', 'codigo' => '15', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Proyector',           'codigo' => '16', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Riel',                'codigo' => '71', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Empotrado de techo',  'codigo' => 'ET', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Estaca',              'codigo' => '17', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Empotrado de pared',  'codigo' => 'EM', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Empotrado de piso',   'codigo' => 'EP', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Baliza',              'codigo' => '17', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Reflector',           'codigo' => '18', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        // Restaurar unique index (solo si no hay duplicados)
        Schema::table('tipos_luminaria', function (Blueprint $table) {
            $table->unique('codigo');
        });
    }
};
