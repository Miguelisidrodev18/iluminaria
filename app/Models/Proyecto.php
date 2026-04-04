<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $fillable = [
        'cliente_id',
        'id_proyecto',
        'persona_cargo',
        'prioridad',
        'nombre_proyecto',
        'fecha_recepcion',
        'fecha_entrega_aprox',
        'max_revisiones',
        'revisiones_json',
        'fecha_entrega_real',
        'monto_presup_proy',
        'monto_vendido_proy',
        'centro_costos',
        'resultado',
        'seguimiento',
    ];

    protected $casts = [
        'fecha_recepcion'     => 'date',
        'fecha_entrega_aprox' => 'date',
        'fecha_entrega_real'  => 'date',
        'revisiones_json'     => 'array',
        'monto_presup_proy'   => 'decimal:2',
        'monto_vendido_proy'  => 'decimal:2',
    ];

    public static array $etiquetasResultado = [
        'G'   => 'Ganado',
        'P'   => 'Perdido',
        'EP'  => 'En Proceso',
        'ENT' => 'Entregado',
        'ENV' => 'Enviado',
        'I'   => 'Inactivo',
    ];

    public static array $etiquetasPrioridad = [
        'A' => 'Alta',
        'M' => 'Media',
        'B' => 'Baja',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
