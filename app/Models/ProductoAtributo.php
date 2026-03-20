<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Catalogo\CatalogoAtributo;
use App\Models\Catalogo\CatalogoValor;

/**
 * Valor de atributo asignado a un producto específico.
 * Una fila por cada (producto, atributo, valor) — multiselect genera múltiples filas.
 */
class ProductoAtributo extends Model
{
    protected $table = 'producto_atributos';

    protected $fillable = [
        'producto_id', 'atributo_id', 'valor_id', 'valor_texto',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function atributo()
    {
        return $this->belongsTo(CatalogoAtributo::class, 'atributo_id');
    }

    public function valor()
    {
        return $this->belongsTo(CatalogoValor::class, 'valor_id');
    }

    // ── Accessor ──────────────────────────────────────────────────────────────

    /**
     * Texto legible del valor asignado.
     */
    public function getTextoAttribute(): string
    {
        if ($this->valor) {
            return $this->valor->texto_display;
        }
        return $this->valor_texto ?? '';
    }
}
