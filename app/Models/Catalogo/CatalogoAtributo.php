<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Definición de un atributo dinámico del catálogo.
 *
 * @property int    $id
 * @property string $nombre
 * @property string $slug
 * @property string $tipo        select|multiselect|number|text|checkbox
 * @property string $grupo       tecnico|comercial|instalacion|estetico
 * @property string $unidad      Ej: W, lm, K, hrs
 * @property bool   $requerido
 * @property bool   $en_nombre_auto
 * @property int    $orden_nombre
 * @property int    $orden
 * @property bool   $activo
 */
class CatalogoAtributo extends Model
{
    use HasFactory;

    protected $table = 'catalogo_atributos';

    protected $fillable = [
        'nombre', 'slug', 'tipo', 'grupo', 'unidad',
        'placeholder', 'requerido', 'en_nombre_auto',
        'orden_nombre', 'orden', 'activo', 'descripcion',
    ];

    protected $casts = [
        'requerido'       => 'boolean',
        'en_nombre_auto'  => 'boolean',
        'activo'          => 'boolean',
        'orden'           => 'integer',
        'orden_nombre'    => 'integer',
    ];

    const TIPOS = [
        'select'      => 'Lista desplegable',
        'multiselect' => 'Selección múltiple',
        'number'      => 'Número',
        'text'        => 'Texto libre',
        'checkbox'    => 'Sí / No',
    ];

    const GRUPOS = [
        'tecnico'     => 'Técnico',
        'comercial'   => 'Comercial',
        'instalacion' => 'Instalación',
        'estetico'    => 'Estético / Acabado',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function valores()
    {
        return $this->hasMany(CatalogoValor::class, 'atributo_id')
                    ->orderBy('orden')
                    ->orderBy('valor');
    }

    public function valoresActivos()
    {
        return $this->hasMany(CatalogoValor::class, 'atributo_id')
                    ->where('activo', true)
                    ->orderBy('orden')
                    ->orderBy('valor');
    }

    public function productoAtributos()
    {
        return $this->hasMany(\App\Models\ProductoAtributo::class, 'atributo_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorGrupo($query, string $grupo)
    {
        return $query->where('grupo', $grupo);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('grupo')->orderBy('orden')->orderBy('nombre');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function esSelect(): bool
    {
        return in_array($this->tipo, ['select', 'multiselect']);
    }

    public function esMultiple(): bool
    {
        return $this->tipo === 'multiselect';
    }

    public function esTexto(): bool
    {
        return in_array($this->tipo, ['number', 'text']);
    }

    /**
     * Devuelve los atributos agrupados por grupo para el formulario de producto.
     */
    public static function paraFormulario(): \Illuminate\Support\Collection
    {
        return static::activos()
            ->ordenados()
            ->with('valoresActivos')
            ->get()
            ->groupBy('grupo');
    }
}
