<?php

namespace App\Models\Luminaria;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

class ProductoEspecificacion extends Model
{
    protected $table = 'producto_especificaciones';

    protected $fillable = [
        'producto_id',
        // Especificaciones técnicas
        'potencia',
        'voltaje',
        'ip',
        'ik',
        'angulo_apertura',
        'driver',
        'regulable',
        'protocolo_regulacion',
        'socket',
        'numero_lamparas',
        'tipo_fuente',       // LED, Fluorescente, Halógena, HID
        'salida_luz',        // Directa, Indirecta, Mixta
        'nivel_potencia',    // Baja, Media, Alta
        'vida_util_horas',
        // Fotometría
        'lumenes',
        'temperatura_color',
        'cri',
        'eficacia_luminosa', // lm/W (W_lumenes del Excel)
        'nominal_lumenes',   // lúmenes catálogo fabricante
        'real_lumenes',      // lúmenes medidos
        'tonalidad_luz',     // Cálido, Neutro, Frío, Bicolor
    ];

    protected $casts = [
        'regulable'         => 'boolean',
        'cri'               => 'integer',
        'numero_lamparas'   => 'integer',
        'vida_util_horas'   => 'integer',
        'eficacia_luminosa' => 'decimal:2',
        'nominal_lumenes'   => 'decimal:2',
        'real_lumenes'      => 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
