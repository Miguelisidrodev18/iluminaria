<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

class Clasificacion extends Model
{
    protected $table = 'clasificaciones';

    protected $fillable = [
        'nombre',
        'codigo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_clasificaciones', 'clasificacion_id', 'producto_id');
    }
}
