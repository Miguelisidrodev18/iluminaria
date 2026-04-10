<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guias_remision', function (Blueprint $table) {
            $table->id();

            // Comprobante
            $table->foreignId('serie_comprobante_id')->constrained('series_comprobantes');
            $table->unsignedInteger('correlativo');

            // Relaciones
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('user_id')->constrained('users');

            // Fechas
            $table->date('fecha_emision');
            $table->date('fecha_traslado');

            // Datos de traslado
            $table->string('motivo_traslado', 2)->default('01');
            // 01=Venta, 02=Compra, 04=Consignación, 08=Importación, 09=Exportación, 13=Otros no especificados, 14=Venta sujeta a confirmación del comprador
            $table->string('modalidad_transporte', 2)->default('01');
            // 01=Transporte Privado, 02=Transporte Público
            $table->decimal('peso_bruto', 10, 3)->nullable();
            $table->unsignedSmallInteger('numero_bultos')->nullable();

            // Destinatario (puede diferir del cliente)
            $table->string('destinatario_tipo_doc', 2)->nullable(); // 1=DNI, 6=RUC
            $table->string('destinatario_num_doc', 15)->nullable();
            $table->string('destinatario_nombre', 200)->nullable();
            $table->string('destinatario_direccion', 300)->nullable();

            // Punto de partida
            $table->string('partida_ubigeo', 6)->nullable();
            $table->string('partida_direccion', 300)->nullable();

            // Punto de llegada
            $table->string('llegada_ubigeo', 6)->nullable();
            $table->string('llegada_direccion', 300)->nullable();

            // Transporte privado
            $table->string('placa_vehiculo', 20)->nullable();
            $table->string('conductor_nombre', 200)->nullable();
            $table->string('conductor_tipo_doc', 2)->nullable(); // 1=DNI
            $table->string('conductor_num_doc', 20)->nullable();
            $table->string('conductor_licencia', 50)->nullable();

            // Transporte público
            $table->string('transportista_ruc', 11)->nullable();
            $table->string('transportista_nombre', 200)->nullable();

            // Estado y SUNAT
            $table->string('estado', 20)->default('borrador');
            // borrador, enviado, aceptado, rechazado, anulado
            $table->json('sunat_respuesta')->nullable();
            $table->string('sunat_hash', 100)->nullable();
            $table->string('sunat_enlace_pdf', 500)->nullable();
            $table->string('sunat_enlace_xml', 500)->nullable();
            $table->string('sunat_enlace_cdr', 500)->nullable();

            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['serie_comprobante_id', 'correlativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guias_remision');
    }
};
