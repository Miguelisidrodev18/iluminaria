<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->date('fecha_registro')->nullable()->after('id');
            $table->string('registrado_por')->nullable()->after('fecha_registro');
            $table->enum('tipo_cliente', ['ARQ', 'ING', 'DIS', 'PN', 'PJ'])->nullable()->after('registrado_por');
            $table->string('apellidos')->nullable()->after('tipo_cliente');
            $table->string('nombres')->nullable()->after('apellidos');
            $table->string('dni', 20)->unique()->nullable()->after('nombres');
            $table->date('fecha_cumpleanos')->nullable()->after('dni');
            $table->string('celular', 20)->nullable()->after('fecha_cumpleanos');
            $table->string('direccion_residencia')->nullable()->after('celular');
            $table->string('telefono_casa', 20)->nullable()->after('direccion_residencia');
            $table->string('correo_personal')->nullable()->after('telefono_casa');
            $table->string('ocupacion')->nullable()->after('correo_personal');
            $table->string('especialidad')->nullable()->after('ocupacion');
            $table->text('redes_personales')->nullable()->after('especialidad');
            $table->string('empresa')->nullable()->after('redes_personales');
            $table->string('ruc', 20)->nullable()->after('empresa');
            $table->string('correo_empresa')->nullable()->after('ruc');
            $table->string('direccion_empresa')->nullable()->after('correo_empresa');
            $table->string('telefono_empresa', 20)->nullable()->after('direccion_empresa');
            $table->text('redes_empresa')->nullable()->after('telefono_empresa');
            $table->decimal('comision', 5, 2)->default(0)->after('redes_empresa');
            $table->text('preferencias')->nullable()->after('comision');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['dni']);
            $table->dropColumn([
                'fecha_registro', 'registrado_por', 'tipo_cliente',
                'apellidos', 'nombres', 'dni', 'fecha_cumpleanos',
                'celular', 'direccion_residencia', 'telefono_casa',
                'correo_personal', 'ocupacion', 'especialidad',
                'redes_personales', 'empresa', 'ruc', 'correo_empresa',
                'direccion_empresa', 'telefono_empresa', 'redes_empresa',
                'comision', 'preferencias',
            ]);
        });
    }
};
