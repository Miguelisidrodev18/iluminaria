<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. Agregar columnas JSON temporales ───────────────────────────────
        Schema::table('producto_clasificacion', function (Blueprint $table) {
            $table->json('tipo_instalacion_json')->nullable()->after('tipo_instalacion');
            $table->json('estilo_json')->nullable()->after('estilo');
        });

        // ─── 2. Migrar datos existentes a JSON ─────────────────────────────────
        DB::table('producto_clasificacion')->orderBy('id')->each(function ($row) {
            DB::table('producto_clasificacion')->where('id', $row->id)->update([
                'tipo_instalacion_json' => $row->tipo_instalacion
                    ? json_encode([$row->tipo_instalacion])
                    : json_encode([]),
                'estilo_json' => $row->estilo
                    ? json_encode(array_values(array_filter(array_map('trim', explode(',', $row->estilo)))))
                    : json_encode([]),
            ]);
        });

        // ─── 3. Eliminar columnas antiguas ─────────────────────────────────────
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('producto_clasificacion', function (Blueprint $table) {
            $table->dropForeign('producto_clasificacion_tipo_proyecto_id_foreign');
            $table->dropColumn(['tipo_instalacion', 'estilo', 'tipo_proyecto_id']);
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ─── 4. Renombrar columnas temporales (MariaDB: CHANGE) ────────────────
        DB::statement('ALTER TABLE producto_clasificacion CHANGE tipo_instalacion_json tipo_instalacion JSON NULL');
        DB::statement('ALTER TABLE producto_clasificacion CHANGE estilo_json estilo JSON NULL');

        // ─── 5. Crear tabla pivot producto_tipos_proyecto ──────────────────────
        Schema::create('producto_tipos_proyecto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->cascadeOnDelete();
            $table->foreignId('tipo_proyecto_id')
                  ->constrained('tipos_proyecto')
                  ->cascadeOnDelete();
            $table->unique(['producto_id', 'tipo_proyecto_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_tipos_proyecto');

        Schema::table('producto_clasificacion', function (Blueprint $table) {
            $table->dropColumn(['tipo_instalacion', 'estilo']);
            $table->enum('tipo_instalacion', ['empotrado','superficie','suspendido','poste','carril','portatil'])->nullable();
            $table->string('estilo')->nullable();
            $table->foreignId('tipo_proyecto_id')->nullable()->constrained('tipos_proyecto')->nullOnDelete();
        });
    }
};
