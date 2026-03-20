<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marcas', function (Blueprint $table) {
            $table->string('codigo', 2)->nullable()->after('nombre')
                  ->comment('Código de 2 caracteres para el código Kyrios');
        });

        // Generar códigos automáticos para marcas existentes (primeras 2 letras del nombre en mayúscula)
        DB::table('marcas')->orderBy('id')->each(function ($marca) {
            $codigo = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $marca->nombre), 0, 2));
            $codigo = str_pad($codigo, 2, 'X');

            // Garantizar unicidad añadiendo sufijo numérico si ya existe
            $original = $codigo;
            $i = 2;
            while (DB::table('marcas')->where('codigo', $codigo)->where('id', '!=', $marca->id)->exists()) {
                $codigo = substr($original, 0, 1) . $i;
                $i++;
            }

            DB::table('marcas')->where('id', $marca->id)->update(['codigo' => $codigo]);
        });
    }

    public function down(): void
    {
        Schema::table('marcas', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });
    }
};
