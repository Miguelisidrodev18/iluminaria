<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

class TipoLuminaria extends Model
{
    protected $table = 'tipos_luminaria';

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
        return $this->hasMany(Producto::class, 'tipo_luminaria_id');
    }
}
