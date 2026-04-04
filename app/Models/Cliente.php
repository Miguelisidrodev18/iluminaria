<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Campos legacy (usados por módulo ventas)
        'tipo_documento',
        'numero_documento',
        'nombre',
        'direccion',
        'telefono',
        'email',
        'estado',
        // Campos nuevos
        'fecha_registro',
        'registrado_por',
        'tipo_cliente',
        'apellidos',
        'nombres',
        'dni',
        'fecha_cumpleanos',
        'celular',
        'direccion_residencia',
        'telefono_casa',
        'correo_personal',
        'ocupacion',
        'especialidad',
        'redes_personales',
        'empresa',
        'ruc',
        'correo_empresa',
        'direccion_empresa',
        'telefono_empresa',
        'redes_empresa',
        'comision',
        'preferencias',
    ];

    protected $casts = [
        'fecha_registro'   => 'date',
        'fecha_cumpleanos' => 'date',
        'comision'         => 'decimal:2',
    ];

    // =====================
    // Relaciones
    // =====================

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }

    // =====================
    // Scopes
    // =====================

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_cliente', $tipo);
    }

    public function scopeConProyectoActivo($query)
    {
        return $query->whereHas('proyectos', fn ($q) => $q->whereNull('fecha_entrega_real'));
    }

    // =====================
    // Accessors
    // =====================

    public function getNombreCompletoAttribute(): string
    {
        if ($this->apellidos && $this->nombres) {
            return strtoupper($this->apellidos) . ', ' . $this->nombres;
        }
        return $this->nombre ?? '';
    }
}
