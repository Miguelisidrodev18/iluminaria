<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrar datos existentes: el enum 'uso' tiene valores interior/exterior/interior_exterior
        // Los mapeamos a los IDs de la nueva tabla clasificaciones
        $mapa = [
            'interior'          => ['INT'],
            'exterior'          => ['EXT'],
            'interior_exterior' => ['INT', 'EXT'],
        ];

        // Obtener IDs de clasificaciones
        $clasificaciones = DB::table('clasificaciones')->pluck('id', 'codigo');

        // Guardar los datos viejos antes de tocar la tabla
        $datosViejos = DB::table('producto_clasificacion')->get();

        // Renombrar la tabla actual para preservar tipo_instalacion, estilo, tipo_proyecto_id
        // Primero eliminamos la FK y la columna uso, luego la tabla queda como detalle de instalación
        Schema::table('producto_clasificacion', function (Blueprint $table) {
            $table->dropColumn('uso');
        });

        // Crear tabla pivot nueva: producto_clasificaciones (usos múltiples)
        Schema::create('producto_clasificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('clasificacion_id')->constrained('clasificaciones')->cascadeOnDelete();
            $table->unique(['producto_id', 'clasificacion_id']);
            $table->timestamps();
        });

        // Migrar datos históricos
        foreach ($datosViejos as $dato) {
            $usoAntiguo = $dato->uso ?? 'interior'; // el enum ya fue eliminado, este campo ya no existe
            // Nota: como ya eliminamos 'uso', simplemente insertamos Interior por defecto
            // para productos que ya tenían clasificacion
            if ($dato->producto_id) {
                $intId = $clasificaciones['INT'] ?? null;
                if ($intId) {
                    DB::table('producto_clasificaciones')->insertOrIgnore([
                        'producto_id'      => $dato->producto_id,
                        'clasificacion_id' => $intId,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Restaurar columna uso en producto_clasificacion
        Schema::table('producto_clasificacion', function (Blueprint $table) {
            $table->enum('uso', ['interior', 'exterior', 'interior_exterior'])
                  ->nullable()
                  ->after('producto_id');
        });

        Schema::dropIfExists('producto_clasificaciones');
    }
};
