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
        'etiquetas',
        'acepta_whatsapp',
    ];

    protected $casts = [
        'fecha_registro'   => 'date',
        'fecha_cumpleanos' => 'date',
        'comision'         => 'decimal:2',
        'etiquetas'        => 'array',
        'acepta_whatsapp'  => 'boolean',
    ];

    // Etiquetas complementarias (lo que tipo_cliente NO cubre)
    // tipo_cliente ya maneja: ARQ, ING, DIS, PN, PJ
    public const ETIQUETAS_DISPONIBLES = [
        // Roles no cubiertos por tipo_cliente
        'Decorador/a'  => ['color' => 'pink',   'icono' => '🪴'],
        'Paisajista'   => ['color' => 'green',  'icono' => '🌿'],
        // Género
        'Mujer'        => ['color' => 'rose',   'icono' => '👩'],
        'Hombre'       => ['color' => 'sky',    'icono' => '👨'],
        // Rol familiar (difusión por fechas especiales)
        'Mamá'         => ['color' => 'rose',   'icono' => '💐'],
        'Papá'         => ['color' => 'sky',    'icono' => '👔'],
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

    /**
     * Filtra clientes que tienen la etiqueta indicada.
     * Usa JSON_SEARCH para compatibilidad con MariaDB (JSON_CONTAINS falla con UTF-8).
     */
    public function scopeConEtiqueta($query, string $etiqueta)
    {
        return $query->whereRaw("JSON_SEARCH(etiquetas, 'one', ?) IS NOT NULL", [$etiqueta]);
    }

    /**
     * Filtra clientes que aceptan difusión por WhatsApp y tienen celular registrado.
     */
    public function scopeParaWhatsapp($query)
    {
        return $query->where('acepta_whatsapp', true)
                     ->whereNotNull('celular')
                     ->where('celular', '!=', '');
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

    /**
     * Verifica si el cliente tiene una etiqueta específica.
     */
    public function tieneEtiqueta(string $etiqueta): bool
    {
        return in_array($etiqueta, $this->etiquetas ?? []);
    }
}
