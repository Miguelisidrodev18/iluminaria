<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspacioProyectoSeeder extends Seeder
{
    public function run(): void
    {
        // Mapa: nombre del tipo → espacios
        $mapa = [
            'Residencial' => ['Sala', 'Dormitorio', 'Cocina', 'Baño', 'Jardín', 'Garaje'],
            'Comercial'   => ['Lobby', 'Pasillo', 'Oficina', 'Sala de Reuniones', 'Fachada', 'Estacionamiento'],
            'Industrial'  => ['Nave Industrial', 'Almacén', 'Zona de Carga', 'Oficina Industrial', 'Perímetro'],
            'Educativo'   => ['Aula', 'Laboratorio', 'Biblioteca', 'Patio', 'Cafetería', 'Pasillo'],
            'Hospitalario'=> ['Sala de Espera', 'Habitación', 'Quirófano', 'Pasillo', 'Urgencias', 'Exterior'],
            'Urbano'      => ['Calle', 'Plaza', 'Parque', 'Paso Peatonal', 'Túnel', 'Rotonda'],
            'Hotelero'    => ['Recepción', 'Habitación', 'Restaurante', 'Spa', 'Piscina', 'Fachada'],
            'Deportivo'   => ['Cancha', 'Gimnasio', 'Vestuario', 'Graderío', 'Piscina Deportiva', 'Exterior'],
        ];

        foreach ($mapa as $tipoNombre => $espacios) {
            $tipo = DB::table('tipos_proyecto')->where('nombre', $tipoNombre)->first();
            if (!$tipo) {
                continue;
            }

            foreach ($espacios as $nombre) {
                DB::table('espacios_proyecto')->upsert(
                    [['tipo_proyecto_id' => $tipo->id, 'nombre' => $nombre, 'activo' => true]],
                    ['tipo_proyecto_id', 'nombre'],
                    ['activo']
                );
            }
        }
    }
}
