<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Importacion extends Model
{
    protected $table = 'importaciones';

    protected $fillable = [
        'uuid', 'nombre_archivo', 'ruta_archivo', 'categoria_id',
        'total_filas', 'procesadas', 'exitosas', 'fallidas',
        'estado', 'errores', 'creado_por',
        'started_at', 'finished_at',
    ];

    protected $casts = [
        'errores'      => 'array',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
        'total_filas'  => 'integer',
        'procesadas'   => 'integer',
        'exitosas'     => 'integer',
        'fallidas'     => 'integer',
    ];

    // ── Boot: generar UUID automáticamente ───────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function creadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'creado_por');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function porcentaje(): int
    {
        if ($this->estado === 'completado') return 100;
        if ($this->total_filas === 0) return 0;
        return (int) min(99, round(($this->procesadas / $this->total_filas) * 100));
    }

    public function estaActiva(): bool
    {
        return in_array($this->estado, ['pendiente', 'procesando']);
    }

    public function agregarError(string $mensaje): void
    {
        $errores   = $this->errores ?? [];
        $errores[] = $mensaje;

        $this->update([
            'errores'  => $errores,
            'fallidas' => $this->fallidas + 1,
        ]);
    }

    public function actualizarProgreso(int $procesadas, int $exitosas): void
    {
        $this->update([
            'procesadas' => $procesadas,
            'exitosas'   => $exitosas,
        ]);
    }
}
