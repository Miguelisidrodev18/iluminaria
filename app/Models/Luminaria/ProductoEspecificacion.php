<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

class ProductoEspecificacion extends Model
{
    protected $table = 'producto_especificaciones';

    protected $fillable = [
        'producto_id',
        'potencia',
        'lumenes',
        'voltaje',
        'temperatura_color',
        'cri',
        'ip',
        'ik',
        'angulo_apertura',
        'driver',
        'regulable',
        'protocolo_regulacion',
        'socket',
        'numero_lamparas',
    ];

    protected $casts = [
        'regulable'      => 'boolean',
        'cri'            => 'integer',
        'numero_lamparas'=> 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
