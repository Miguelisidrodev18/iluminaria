<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleGuiaRemision extends Model
{
    protected $table = 'detalle_guias_remision';

    protected $fillable = [
        'guia_remision_id', 'producto_id',
        'codigo', 'descripcion', 'unidad_medida', 'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
    ];

    public function guiaRemision()
    {
        return $this->belongsTo(GuiaRemision::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
