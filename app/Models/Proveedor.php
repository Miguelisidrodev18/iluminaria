<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'supplier_type',
        'ruc',
        'razon_social',
        'nombre_comercial',
        'contacto_nombre',
        'telefono',
        'email',
        'website',
        'catalog_url',
        'direccion',
        'factory_address',
        'country',
        'district',
        'port',
        'moq',
        'bank_detail',
        'price_level',
        'quality_level',
        'observations',
        'estado',
    ];

    const TIPOS = [
        'nacional'     => 'Nacional',
        'extranjero'   => 'Extranjero',
        'importacion'  => 'Importación',
    ];

    const PRICE_LEVELS = [
        'muy_caro'  => 'Muy Caro',
        'accesible' => 'Accesible',
        'barato'    => 'Barato',
    ];

    const QUALITY_LEVELS = [
        'excelente' => 'Excelente',
        'regular'   => 'Regular',
        'mala'      => 'Mala',
    ];

    const CATEGORIAS_PRODUCTO = [
        'DECORATIVAS INTERIORES'  => ['Convencionales','Metal','Madera','Cristal','Alabastro','Acrílico','Mimbre','Tejidos','De diseño','Arañas','Doble altura','Portátiles'],
        'DECORATIVAS EXTERIORES'  => ['Colgantes','Plafones','Braquetes','Sobremuro','Mesa','De pie'],
        'TÉCNICAS INTERIOR'       => ['Spot','Paneles básicos','Paneles Baldosas','Rieles/proyectores','Sistema magnético','Plafones','Lineales','Luces guía'],
        'TÉCNICAS EXTERIOR'       => ['Spot','Plafones','Empotrables de piso','Luces guía','Proyectores fachada','Highbay','DMX','De plástico','Estacas altas','Balizas','Sumergibles'],
        'CABEZALES'               => ['Económicos','Alta eficiencia'],
        'LUMINARIAS RECARGABLES'  => ['De mesa','Escritorio','Otros'],
        'POSTES ORNAMENTALES'     => ['Económicos','Con opciones de fotometría'],
        'CINTA LED'               => ['Económicos','220-240V','Especializado','Perfiles básicos','Perfiles especializados','Driver','Balizas'],
        'SOLARES'                 => ['Cabezales pastoral','Cabezales jardines','Estaca','Empotrable','Flood Light','Flood Light portátil','Highbay','Cinta LED','Decorativas'],
        'LÁMPARAS'                => ['LED','Halógenas','Vapor de Sodio'],
        'VENTILADORES'            => ['Modernos','Minimalista','Rústicos'],
        'LUCES DE EMERGENCIA'     => ['Dos faroles','Adosable rectangular','Tipo spot','Para exterior'],
        'PERSONALIZADOS'          => ['Solo línea de producción','Cualquier modelo','MOQ'],
    ];

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->supplier_type] ?? ucfirst($this->supplier_type ?? 'nacional');
    }

    public function getTipoBadgeClassAttribute(): string
    {
        return match($this->supplier_type) {
            'extranjero'  => 'bg-blue-100 text-blue-800',
            'importacion' => 'bg-purple-100 text-purple-800',
            default       => 'bg-green-100 text-green-800',
        };
    }

    public function getPriceLabelAttribute(): string
    {
        return self::PRICE_LEVELS[$this->price_level] ?? '-';
    }

    public function getPriceBadgeClassAttribute(): string
    {
        return match($this->price_level) {
            'muy_caro'  => 'bg-red-100 text-red-800',
            'accesible' => 'bg-green-100 text-green-800',
            'barato'    => 'bg-blue-100 text-blue-800',
            default     => 'bg-gray-100 text-gray-600',
        };
    }

    public function getQualityLabelAttribute(): string
    {
        return self::QUALITY_LEVELS[$this->quality_level] ?? '-';
    }

    public function getQualityBadgeClassAttribute(): string
    {
        return match($this->quality_level) {
            'excelente' => 'bg-green-100 text-green-800',
            'regular'   => 'bg-yellow-100 text-yellow-800',
            'mala'      => 'bg-red-100 text-red-800',
            default     => 'bg-gray-100 text-gray-600',
        };
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function categoriasProducto()
    {
        return $this->hasMany(ProveedorCategoriaProducto::class);
    }

    public function certificaciones()
    {
        return $this->hasMany(ProveedorCertificacion::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeDeType($query, string $type)
    {
        return $query->where('supplier_type', $type);
    }
}
