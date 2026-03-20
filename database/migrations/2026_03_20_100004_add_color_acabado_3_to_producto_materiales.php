<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_materiales', function (Blueprint $table) {
            $table->string('color_acabado_3', 255)->nullable()->after('color_acabado_2');
        });
    }

    public function down(): void
    {
        Schema::table('producto_materiales', function (Blueprint $table) {
            $table->dropColumn('color_acabado_3');
        });
    }
};
