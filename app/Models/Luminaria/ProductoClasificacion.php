<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

class ProductoClasificacion extends Model
{
    protected $table = 'producto_clasificacion';

    protected $fillable = [
        'producto_id',
        'uso',
        'tipo_instalacion',
        'estilo',
        'tipo_proyecto_id',
    ];

    const USOS = [
        'interior'           => 'Interior',
        'exterior'           => 'Exterior',
        'interior_exterior'  => 'Interior / Exterior',
    ];

    const TIPOS_INSTALACION = [
        'empotrado'   => 'Empotrado',
        'superficie'  => 'Superficie',
        'suspendido'  => 'Suspendido',
        'poste'       => 'Poste',
        'carril'      => 'Carril',
        'portatil'    => 'Portátil',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function tipoProyecto()
    {
        return $this->belongsTo(TipoProyecto::class);
    }
}
