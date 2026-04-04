<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'cliente_id',
        'atendido_por',
        'fecha_visita',
        'hora_atencion',
        'monto_presup_soles',
        'monto_presup_dolares',
        'monto_comprado_soles',
        'monto_comprado_dolares',
        'observaciones',
        'resumen_visita',
        'probabilidad_venta',
        'medio_contacto',
    ];

    protected $casts = [
        'fecha_visita'           => 'date',
        'monto_presup_soles'     => 'decimal:2',
        'monto_presup_dolares'   => 'decimal:2',
        'monto_comprado_soles'   => 'decimal:2',
        'monto_comprado_dolares' => 'decimal:2',
        'probabilidad_venta'     => 'integer',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
