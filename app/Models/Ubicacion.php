<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'estado',
    ];

    public const TIPOS = [
        'almacen'  => 'Almacén',
        'tienda'   => 'Tienda',
        'showroom' => 'Showroom',
        'taller'   => 'Taller',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_ubicaciones')
                    ->withPivot('cantidad', 'observacion')
                    ->withTimestamps();
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activo');
    }
}
