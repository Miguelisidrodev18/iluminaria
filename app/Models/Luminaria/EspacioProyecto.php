<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;

class EspacioProyecto extends Model
{
    protected $table = 'espacios_proyecto';

    protected $fillable = ['tipo_proyecto_id', 'nombre', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function tipoProyecto()
    {
        return $this->belongsTo(TipoProyecto::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
