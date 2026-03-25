<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

class ProductoDimension extends Model
{
    protected $table = 'producto_dimensiones';

    protected $fillable = [
        'producto_id',
        // Dimensiones del cuerpo (en mm)
        'alto',
        'ancho',
        'diametro',          // Para circulares
        'lado',              // Para cuadrados
        'profundidad',
        'alto_suspendido',
        // Dimensiones del agujero/corte de instalación (en mm)
        'diametro_agujero',  // Corte circular
        'ancho_agujero',     // Corte rectangular
        'profundidad_agujero',
        // NOTA: 'peso' está en tabla producto_embalaje, no aquí
    ];

    protected $casts = [
        'alto'               => 'decimal:2',
        'ancho'              => 'decimal:2',
        'diametro'           => 'decimal:2',
        'lado'               => 'decimal:2',
        'profundidad'        => 'decimal:2',
        'alto_suspendido'    => 'decimal:2',
        'diametro_agujero'   => 'decimal:2',
        'ancho_agujero'      => 'decimal:2',
        'profundidad_agujero'=> 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
