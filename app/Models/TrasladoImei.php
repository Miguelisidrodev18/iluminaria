<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stub de compatibilidad — el sistema IMEI fue eliminado.
 */
class TrasladoImei extends Model
{
    protected $table = 'traslado_imeis';

    protected $fillable = ['traslado_id', 'imei_id'];

    public static function create(array $attributes = [])
    {
        return new static($attributes);
    }
}
