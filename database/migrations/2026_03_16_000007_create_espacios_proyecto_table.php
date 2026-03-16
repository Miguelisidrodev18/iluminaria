<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('espacios_proyecto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_proyecto_id')->constrained('tipos_proyecto')->cascadeOnDelete();
            $table->string('nombre');            // Ej: "Sala", "Dormitorio"
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Datos iniciales agrupados por tipo de proyecto
        $espacios = [
            // Residencial (id=1)
            1 => ['Sala', 'Comedor', 'Dormitorio', 'Cocina', 'Baño', 'Terraza', 'Jardín', 'Garaje', 'Pasillo'],
            // Comercial (id=2)
            2 => ['Sala de ventas', 'Vitrina', 'Caja', 'Depósito', 'Probador', 'Área de ingreso'],
            // Hotelero (id=3)
            3 => ['Habitación', 'Lobby', 'Restaurante', 'Bar', 'Piscina', 'Spa', 'Sala de reuniones', 'Corredor'],
            // Oficina (id=4)
            4 => ['Área de trabajo', 'Sala de reuniones', 'Recepción', 'Cafetería', 'Archivo', 'Baño'],
            // Centro médico (id=5)
            5 => ['Consulta', 'Sala de espera', 'Cirugía', 'Urgencias', 'Laboratorio', 'Pasillo'],
            // Paisajismo (id=6)
            6 => ['Jardín', 'Caminería', 'Estanque', 'Fachada', 'Acceso vehicular', 'Piscina'],
            // Industrial (id=7)
            7 => ['Nave industrial', 'Almacén', 'Línea de producción', 'Oficinas', 'Cámara fría'],
            // Educativo (id=8)
            8 => ['Aula', 'Biblioteca', 'Laboratorio', 'Auditorio', 'Patio', 'Pasillo'],
        ];

        foreach ($espacios as $tipoId => $nombres) {
            foreach ($nombres as $nombre) {
                DB::table('espacios_proyecto')->insert([
                    'tipo_proyecto_id' => $tipoId,
                    'nombre'           => $nombre,
                    'activo'           => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('espacios_proyecto');
    }
};
