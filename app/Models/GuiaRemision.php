<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuiaRemision extends Model
{
    use SoftDeletes;

    protected $table = 'guias_remision';

    protected $fillable = [
        'serie_comprobante_id', 'correlativo',
        'cliente_id', 'venta_id', 'sucursal_id', 'user_id',
        'fecha_emision', 'fecha_traslado',
        'motivo_traslado', 'modalidad_transporte',
        'peso_bruto', 'numero_bultos',
        'destinatario_tipo_doc', 'destinatario_num_doc',
        'destinatario_nombre', 'destinatario_direccion',
        'partida_ubigeo', 'partida_direccion',
        'llegada_ubigeo', 'llegada_direccion',
        'placa_vehiculo', 'conductor_nombre',
        'conductor_tipo_doc', 'conductor_num_doc', 'conductor_licencia',
        'transportista_ruc', 'transportista_nombre',
        'estado', 'sunat_respuesta', 'sunat_hash',
        'sunat_enlace_pdf', 'sunat_enlace_xml', 'sunat_enlace_cdr',
        'observaciones',
    ];

    protected $casts = [
        'fecha_emision'   => 'date',
        'fecha_traslado'  => 'date',
        'peso_bruto'      => 'decimal:3',
        'sunat_respuesta' => 'array',
    ];

    // ── Constantes ──────────────────────────────────────────────────

    public const MOTIVOS = [
        '01' => 'Venta',
        '02' => 'Compra',
        '04' => 'Consignación',
        '08' => 'Importación',
        '09' => 'Exportación',
        '13' => 'Otros no especificados',
        '14' => 'Venta sujeta a confirmación del comprador',
        '18' => 'Traslado emisor itinerante CP',
        '19' => 'Traslado a zona primaria',
    ];

    public const MODALIDADES = [
        '01' => 'Transporte Privado',
        '02' => 'Transporte Público',
    ];

    public const ESTADOS = [
        'borrador'  => ['label' => 'Borrador',   'color' => 'gray'],
        'enviado'   => ['label' => 'Enviado',     'color' => 'blue'],
        'aceptado'  => ['label' => 'Aceptado',    'color' => 'green'],
        'rechazado' => ['label' => 'Rechazado',   'color' => 'red'],
        'anulado'   => ['label' => 'Anulado',     'color' => 'orange'],
    ];

    public const UNIDADES = [
        'NIU' => 'Unidad',
        'KGM' => 'Kilogramo',
        'MTR' => 'Metro',
        'LTR' => 'Litro',
        'BLL' => 'Barril',
        'ZZ'  => 'Servicio',
    ];

    // ── Relaciones ──────────────────────────────────────────────────

    public function serieComprobante()
    {
        return $this->belongsTo(SerieComprobante::class, 'serie_comprobante_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleGuiaRemision::class);
    }

    // ── Accessors ───────────────────────────────────────────────────

    public function getNumeroGuiaAttribute(): string
    {
        if ($this->serieComprobante && $this->correlativo) {
            return $this->serieComprobante->serie . '-' . str_pad($this->correlativo, 8, '0', STR_PAD_LEFT);
        }
        return 'GR-' . str_pad($this->id ?? 0, 8, '0', STR_PAD_LEFT);
    }

    public function getMotivoLabelAttribute(): string
    {
        return self::MOTIVOS[$this->motivo_traslado] ?? $this->motivo_traslado;
    }

    public function getModalidadLabelAttribute(): string
    {
        return self::MODALIDADES[$this->modalidad_transporte] ?? $this->modalidad_transporte;
    }

    public function getEstadoInfoAttribute(): array
    {
        return self::ESTADOS[$this->estado] ?? ['label' => $this->estado, 'color' => 'gray'];
    }

    public function getEsPrivadoAttribute(): bool
    {
        return $this->modalidad_transporte === '01';
    }

    public function getPuedeEnviarseAttribute(): bool
    {
        return in_array($this->estado, ['borrador', 'rechazado']);
    }

    public function getPuedeAnularseAttribute(): bool
    {
        return in_array($this->estado, ['borrador', 'enviado', 'aceptado']);
    }
}
