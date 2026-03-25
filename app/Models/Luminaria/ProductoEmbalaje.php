<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

class ProductoEmbalaje extends Model
{
    protected $table = 'producto_embalaje';

    protected $fillable = [
        'producto_id',
        'peso',             // kg
        'volumen',          // cm³ o m³
        'embalado',         // boolean: incluye embalaje individual
        'medida_embalaje',  // Ej: "62x62x10 cm"
        'cantidad_por_caja',
    ];

    protected $casts = [
        'peso'             => 'decimal:3',
        'volumen'          => 'decimal:3',
        'embalado'         => 'boolean',
        'cantidad_por_caja'=> 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
