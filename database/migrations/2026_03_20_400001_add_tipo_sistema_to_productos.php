<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->enum('tipo_sistema', ['simple', 'compuesto', 'componente'])
                  ->default('simple')
                  ->after('estado')
                  ->comment('simple=producto normal, compuesto=kit armado, componente=usado en kits');

            $table->boolean('descontar_componentes')
                  ->default(false)
                  ->after('tipo_sistema')
                  ->comment('Si true: vender este kit descuenta stock de sus componentes en vez del kit');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['tipo_sistema', 'descontar_componentes']);
        });
    }
};
