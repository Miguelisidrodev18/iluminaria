<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedorCategoriaProducto extends Model
{
    protected $table = 'proveedor_categorias_producto';

    protected $fillable = ['proveedor_id', 'categoria', 'subcategoria'];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}
