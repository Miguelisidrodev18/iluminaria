<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoComponente extends Model
{
    protected $table = 'producto_componentes';

    protected $fillable = [
        'padre_id', 'hijo_id', 'variante_id',
        'cantidad', 'unidad', 'es_opcional', 'orden', 'observacion',
    ];

    protected $casts = [
        'cantidad'     => 'decimal:3',
        'es_opcional'  => 'boolean',
        'orden'        => 'integer',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function padre(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Producto::class, 'padre_id');
    }

    public function hijo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Producto::class, 'hijo_id');
    }

    public function variante(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Devuelve el nombre del componente incluyendo variante si aplica.
     */
    public function getNombreCompletoAttribute(): string
    {
        $nombre = $this->hijo?->nombre ?? '—';
        if ($this->variante) {
            $nombre .= ' (' . $this->variante->nombre_completo . ')';
        }
        return $nombre;
    }

    /**
     * Stock disponible del componente (variante si hay, sino producto base).
     */
    public function getStockDisponibleAttribute(): int
    {
        return $this->variante
            ? (int) $this->variante->stock_actual
            : (int) ($this->hijo?->stock_actual ?? 0);
    }

    /**
     * ¿Hay suficiente stock para cubrir `$cantidadKits` kits?
     */
    public function tieneStockParaKits(int $cantidadKits): bool
    {
        $necesario = $this->cantidad * $cantidadKits;
        return $this->stock_disponible >= $necesario;
    }
}
