<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

class ProductoDimension extends Model
{
    protected $table = 'producto_dimensiones';

    protected $fillable = [
        'producto_id',
        'alto',
        'ancho',
        'diametro',
        'lado',
        'profundidad',
        'alto_suspendido',
        'diametro_agujero',
        'peso',
    ];

    protected $casts = [
        'alto'             => 'decimal:2',
        'ancho'            => 'decimal:2',
        'diametro'         => 'decimal:2',
        'lado'             => 'decimal:2',
        'profundidad'      => 'decimal:2',
        'alto_suspendido'  => 'decimal:2',
        'diametro_agujero' => 'decimal:2',
        'peso'             => 'decimal:3',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
