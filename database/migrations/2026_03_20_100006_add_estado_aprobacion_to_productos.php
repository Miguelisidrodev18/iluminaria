<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->enum('estado_aprobacion', [
                'borrador',
                'pendiente_aprobacion',
                'aprobado',
                'rechazado',
            ])->default('borrador')->after('estado');

            $table->unsignedBigInteger('aprobado_por')->nullable()->after('estado_aprobacion');
            $table->timestamp('aprobado_en')->nullable()->after('aprobado_por');
            $table->text('motivo_rechazo')->nullable()->after('aprobado_en');

            $table->foreign('aprobado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['aprobado_por']);
            $table->dropColumn(['estado_aprobacion', 'aprobado_por', 'aprobado_en', 'motivo_rechazo']);
        });
    }
};
