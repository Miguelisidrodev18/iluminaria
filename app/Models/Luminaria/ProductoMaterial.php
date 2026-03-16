<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

class ProductoMaterial extends Model
{
    protected $table = 'producto_materiales';

    protected $fillable = [
        'producto_id',
        'material_1',
        'material_2',
        'color_acabado_1',
        'color_acabado_2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
