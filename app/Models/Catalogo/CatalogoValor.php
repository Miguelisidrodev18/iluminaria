<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Valor predefinido para un atributo de tipo select / multiselect.
 * Ej: atributo "Socket" → valores E27, GU10, E14, B22...
 *
 * @property int    $id
 * @property int    $atributo_id
 * @property string $valor        Valor interno (ej: "3000K", "E27", "IP65")
 * @property string $etiqueta     Texto display (ej: "Cálido 3000K", puede ser null = usar valor)
 * @property string $color_hex    Para atributos de color, muestra swatch visual
 * @property int    $orden
 * @property bool   $activo
 */
class CatalogoValor extends Model
{
    use HasFactory;

    protected $table = 'catalogo_valores';

    protected $fillable = [
        'atributo_id', 'valor', 'etiqueta', 'color_hex', 'orden', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function atributo()
    {
        return $this->belongsTo(CatalogoAtributo::class, 'atributo_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Texto a mostrar en el UI: etiqueta si existe, si no el valor.
     */
    public function getTextoDisplayAttribute(): string
    {
        return $this->etiqueta ?? $this->valor;
    }
}
