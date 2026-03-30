<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspacioProyectoSeeder extends Seeder
{
    public function run(): void
    {
        $mapa = [
            'Residencial' => [
                'Fachada', 'Ingreso / Hall', 'Baño de Visita', 'Escritorio',
                'Sala', 'Comedor', 'Terraza', 'Jardín', 'Cocina',
                'Dormitorio', 'SSHH', 'Sala de TV', 'Walking Closet',
                'Área de Servicio',
            ],
            'Comercial' => [
                'Fachada', 'Ingreso / Hall', 'Sala de Ventas', 'Vitrina',
                'Probador', 'Caja', 'Depósito', 'Pasillo', 'Baño',
                'Zona de Carga', 'Estacionamiento',
            ],
            'Hotelero' => [
                'Fachada', 'Lobby', 'Recepción', 'Habitación', 'Baño',
                'Restaurante', 'Bar', 'Piscina', 'Spa', 'Sala de Reuniones',
                'Pasillo', 'Terraza', 'Jardín', 'Estacionamiento',
            ],
            'Oficina' => [
                'Fachada', 'Ingreso / Hall', 'Área de Trabajo', 'Sala de Reuniones',
                'Recepción', 'Cafetería', 'Archivo', 'Baño', 'Pasillo',
                'Estacionamiento',
            ],
            'Restaurante' => [
                'Fachada', 'Ingreso / Hall', 'Salón Principal', 'Terraza',
                'Bar', 'Cocina', 'Baño', 'Pasillo', 'Bodega',
            ],
            'Laboratorio' => [
                'Fachada', 'Área de Trabajo', 'Sala Limpia', 'Pasillo',
                'Almacén', 'Baño', 'Área de Urgencias',
            ],
            'Centro Médico' => [
                'Fachada', 'Sala de Espera', 'Consulta', 'Cirugía',
                'Urgencias', 'Habitación', 'Pasillo', 'Baño', 'Laboratorio',
            ],
            'Estación de Servicios' => [
                'Fachada', 'Área de Despacho', 'Tienda / Minimarket',
                'Baño', 'Estacionamiento', 'Zona de Lavado',
            ],
            'Paisajismo' => [
                'Jardín', 'Caminería', 'Estanque / Piscina', 'Fachada',
                'Acceso Vehicular', 'Área de Descanso', 'Bosque / Arboleda',
            ],
            'Clubes' => [
                'Fachada', 'Lobby', 'Salón Social', 'Piscina', 'Gimnasio',
                'Cancha Deportiva', 'Vestuario', 'Bar / Restaurante',
                'Terraza', 'Jardín', 'Estacionamiento',
            ],
            'Condominios' => [
                'Fachada', 'Ingreso / Hall', 'Área Común', 'Pasillo',
                'Escaleras', 'Piscina', 'Jardín', 'Estacionamiento',
                'Salón de Usos Múltiples',
            ],
            'Urbano' => [
                'Calle / Vía Pública', 'Plaza', 'Parque', 'Paso Peatonal',
                'Túnel', 'Rotonda', 'Pasarela', 'Puente',
            ],
            'Industrial' => [
                'Nave Industrial', 'Almacén', 'Línea de Producción',
                'Zona de Carga', 'Oficinas', 'Perímetro', 'Cámara Fría',
            ],
            'Educativo' => [
                'Fachada', 'Aula', 'Biblioteca', 'Laboratorio', 'Auditorio',
                'Patio', 'Cafetería', 'Pasillo', 'Baño', 'Sala de Profesores',
            ],
            'Hospitalario' => [
                'Fachada', 'Sala de Espera', 'Consulta', 'Cirugía',
                'Urgencias', 'Habitación', 'UCI', 'Pasillo', 'Baño',
                'Laboratorio', 'Farmacia',
            ],
            'Deportivo' => [
                'Cancha / Campo', 'Graderío', 'Vestuario', 'Gimnasio',
                'Piscina Deportiva', 'Pista de Atletismo', 'Exterior',
                'Pasillo', 'Baño',
            ],
            'Galerias y Museos' => [
                'Fachada', 'Hall / Entrada', 'Sala de Exposición',
                'Almacén de Obra', 'Iluminación de Acento', 'Pasillo', 'Baño',
            ],
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
