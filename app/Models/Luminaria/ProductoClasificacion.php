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
        'tipo_instalacion',
        'estilo',
    ];

    protected $casts = [
        'tipo_instalacion' => 'array',
        'estilo'           => 'array',
    ];

    const TIPOS_INSTALACION = [
        'empotrado'  => 'Empotrado',
        'superficie' => 'Superficie',
        'suspendido' => 'Suspendido',
        'poste'      => 'Poste',
        'carril'     => 'Carril',
        'portatil'   => 'Portátil',
    ];

    const ESTILOS_SUGERIDOS = [
        'Moderno', 'Clásico', 'Industrial', 'Minimalista',
        'Rústico', 'Contemporáneo', 'Nórdico', 'Art Deco',
        'Retro', 'Natural',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // tipoProyecto ya no existe aquí — se gestiona via Producto->tiposProyecto() pivot
}
