<?php
// app/Models/Catalogo/Marca.php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Marca extends Model
{
    use HasFactory;

    protected $table = 'marcas';

    protected $fillable = [
        'nombre',
        'codigo',
        'logo',
        'descripcion',
        'sitio_web',
        'estado',
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Genera el siguiente código correlativo numérico (01, 02, 03...).
     * Busca el mayor valor numérico existente e incrementa.
     */
    public static function generarCodigoSiguiente(): string
    {
        $ultimo = static::whereNotNull('codigo')
            ->whereRaw("codigo REGEXP '^[0-9]+$'")
            ->orderByRaw('CAST(codigo AS UNSIGNED) DESC')
            ->value('codigo');

        $siguiente = $ultimo ? ((int) $ultimo + 1) : 1;

        // Si supera 99 usamos 3 dígitos (la columna acepta más al crecer)
        return str_pad($siguiente, 2, '0', STR_PAD_LEFT);
    }

    public function modelos()
    {
        return $this->hasMany(Modelo::class);
    }

    public function categorias()
    {
        return $this->belongsToMany(\App\Models\Categoria::class, 'categoria_marca');
    }
}