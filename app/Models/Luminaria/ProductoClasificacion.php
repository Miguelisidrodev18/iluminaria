<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

/**
 * Almacena los atributos de instalación del producto:
 * tipo_instalacion, estilo, tipo_proyecto_id.
 * Los usos (Interior, Exterior, etc.) se gestionan via
 * la tabla pivot producto_clasificaciones → clasificaciones.
 */
class ProductoClasificacion extends Model
{
    protected $table = 'producto_clasificacion';

    protected $fillable = [
        'producto_id',
        'usos',
        'ambientes',
        'tipo_instalacion',
        'estilo',
    ];

    protected $casts = [
        'usos'             => 'array',
        'ambientes'        => 'array',
        'tipo_instalacion' => 'array',
        'estilo'           => 'array',
    ];

    const TIPOS_INSTALACION = [
        'colgante'              => 'Colgante',
        'colgante_doble_altura' => 'Colgante Doble Altura',
        'plafon'                => 'Plafón',
        'aplique'               => 'Aplique',
        'sobre_mesa'            => 'Sobre Mesa',
        'pie'                   => 'Pie',
        'escritorio'            => 'Escritorio',
        'lectura'               => 'Lectura',
        'empotrado_techo'       => 'Empotrado de Techo',
        'empotrado_piso'        => 'Empotrado de Piso',
        'empotrado_muro'        => 'Empotrado Sobre Muro',
        'ventilador'            => 'Ventilador',
        'estacas'               => 'Estacas',
        'balizas'               => 'Balizas',
        'empotrado_sumergible'  => 'Empotrado Sumergible',
        'portatil'              => 'Luminarias Portátiles',
        'proyector'             => 'Proyectores',
        'riel'                  => 'Sistema de Riel',
        'tira_led'              => 'Tiras LED',
        'poste'                 => 'Postes',
        'luz_guia'              => 'Luz Guía',
    ];

    const USOS_PRODUCTO = [
        'interior'          => 'Interiores',
        'exterior'          => 'Exteriores',
        'alumbrado_publico' => 'Alumbrado Público',
        'piscina'           => 'Piscina',
    ];

    const ESTILOS_SUGERIDOS = [
        'Clásico', 'Clásico-Moderno', 'Moderno', 'Contemporáneo',
        'Minimalista', 'Rústico', 'Náutico', 'Vintage',
        'Industrial', 'Tech', 'Nórdico', 'Inglés',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // tipoProyecto ya no existe aquí — se gestiona via Producto->tiposProyecto() pivot
}
