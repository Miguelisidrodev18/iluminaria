<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedorCertificacion extends Model
{
    protected $table = 'proveedor_certificaciones';

    protected $fillable = ['proveedor_id', 'cert_type', 'descripcion'];

    const TIPOS = [
        'generales'    => 'Certificaciones Generales',
        'por_producto' => 'Por Producto',
        'iso'          => 'ISO',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}
