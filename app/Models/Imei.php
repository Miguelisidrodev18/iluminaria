<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Stub de compatibilidad — el sistema IMEI fue eliminado.
 * Este modelo devuelve resultados vacíos para no romper el código heredado
 * mientras se migran los controladores y servicios que aún lo referencian.
 */
class Imei extends Model
{
    protected $table = 'imeis';

    protected $fillable = [
        'codigo_imei', 'serie', 'estado_imei',
        'producto_id', 'variante_id', 'almacen_id', 'color_id', 'compra_id',
    ];

    // ── Relaciones stub ───────────────────────────────────────────────────────

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_id');
    }

    // ── Queries siempre devuelven vacío (tabla no existe) ─────────────────────

    public static function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        return new class {
            public function where(...$args)          { return $this; }
            public function whereIn(...$args)        { return $this; }
            public function distinct(...$args)       { return $this; }
            public function pluck(...$args)          { return collect(); }
            public function count(...)              { return 0; }
            public function exists()                { return false; }
            public function get(...$args)           { return collect(); }
            public function limit(...$args)         { return $this; }
            public function orderBy(...$args)       { return $this; }
            public function first()                 { return null; }
            public function update(...$args)        { return 0; }
            public function delete()                { return 0; }
            public function when(...$args)          { return $this; }
            public function with(...$args)          { return $this; }
        };
    }

    public static function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        return static::where($column, $values);
    }

    public static function count($columns = '*')
    {
        return 0;
    }

    public static function create(array $attributes = [])
    {
        return new static($attributes);
    }
}
