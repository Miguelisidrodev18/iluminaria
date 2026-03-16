<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;

class TipoProyecto extends Model
{
    protected $table = 'tipos_proyecto';

    protected $fillable = ['nombre', 'icono', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function espacios()
    {
        return $this->hasMany(EspacioProyecto::class);
    }

    public function clasificaciones()
    {
        return $this->hasMany(ProductoClasificacion::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
