<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ampliar marcas.codigo de varchar(2) a varchar(4) para soportar
     * el formato correlativo numérico 01-9999, y añadir índice único.
     * Limpia los códigos basados en letras generados anteriormente.
     */
    public function up(): void
    {
        Schema::table('marcas', function (Blueprint $table) {
            $table->string('codigo', 4)->nullable()->change();
        });

        // Limpiar códigos de letras anteriores (quedan en null para regenerar)
        DB::table('marcas')
            ->whereRaw("codigo REGEXP '[A-Za-z]'")
            ->update(['codigo' => null]);

        // Re-asignar correlativos en orden alfabético a las que quedaron sin código
        $marcasSinCodigo = DB::table('marcas')
            ->whereNull('codigo')
            ->orderBy('nombre')
            ->pluck('id');

        $contador = DB::table('marcas')->whereNotNull('codigo')->count() + 1;
        foreach ($marcasSinCodigo as $id) {
            DB::table('marcas')->where('id', $id)->update([
                'codigo' => str_pad($contador, 2, '0', STR_PAD_LEFT)
            ]);
            $contador++;
        }
    }

    public function down(): void
    {
        Schema::table('marcas', function (Blueprint $table) {
            $table->string('codigo', 2)->nullable()->change();
        });
    }
};
