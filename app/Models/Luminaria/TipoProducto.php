<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

class TipoProducto extends Model
{
    protected $table = 'tipos_producto';

    protected $fillable = [
        'nombre',
        'codigo',
        'usa_tipo_luminaria',
        'activo',
    ];

    protected $casts = [
        'usa_tipo_luminaria' => 'boolean',
        'activo'             => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'tipo_producto_id');
    }
}
