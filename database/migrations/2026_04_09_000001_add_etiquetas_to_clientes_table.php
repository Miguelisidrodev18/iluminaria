<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Etiquetas para segmentación y listas de difusión WhatsApp
            $table->json('etiquetas')->nullable()->after('preferencias')
                ->comment('Segmentos: Mamá, Papá, Mujer, Hombre, Arquitecto/a, etc.');

            // Consentimiento para comunicaciones WhatsApp
            $table->boolean('acepta_whatsapp')->default(true)->after('etiquetas');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['etiquetas', 'acepta_whatsapp']);
        });
    }
};
