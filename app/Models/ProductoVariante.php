<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Catalogo\Color;

class ProductoVariante extends Model
{
    use HasFactory;

    protected $table = 'producto_variantes';

    protected $fillable = [
        'producto_id',
        'nombre',
        'atributos',
        'color_id',
        'especificacion',   // Genérico: potencia, temperatura, versión, etc.
        'tamano',           // Ej: "600x600mm", "Circular 4\"", "30x120cm"
        'sku',
        'sobreprecio',
        'stock_actual',
        'stock_minimo',
        'estado',
        'imagen',
        'creado_por',
    ];

    protected $casts = [
        'stock_actual'  => 'integer',
        'stock_minimo'  => 'integer',
        'sobreprecio'   => 'decimal:2',
        'estado'        => 'string',
        'atributos'     => 'array',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function detallesCompra()
    {
        return $this->hasMany(DetalleCompra::class, 'variante_id');
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'variante_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'variante_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeConStock($query)
    {
        return $query->where('stock_actual', '>', 0);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getNombreCompletoAttribute(): string
    {
        $partes = [];
        if ($this->color) {
            $partes[] = $this->color->nombre;
        }
        if ($this->especificacion) {
            $partes[] = $this->especificacion;
        }
        return implode(' / ', $partes) ?: 'Variante base';
    }

    public function getDescripcionCorta(): string
    {
        $nombre   = $this->producto?->nombre ?? 'Producto';
        $variante = $this->nombre_completo;
        return $variante !== 'Variante base' ? "{$nombre} — {$variante}" : $nombre;
    }

    // ─── Lógica de stock ───────────────────────────────────────────────────────

    public function tieneStock(int $cantidad = 1): bool
    {
        return $this->stock_actual >= $cantidad;
    }

    public function incrementarStock(int $cantidad): void
    {
        $this->increment('stock_actual', $cantidad);
        $this->sincronizarStockProductoBase();
    }

    public function decrementarStock(int $cantidad): void
    {
        if ($this->stock_actual < $cantidad) {
            throw new \Exception(
                "Stock insuficiente para la variante {$this->nombre_completo}. " .
                "Disponible: {$this->stock_actual}, solicitado: {$cantidad}"
            );
        }
        $this->decrement('stock_actual', $cantidad);
        $this->sincronizarStockProductoBase();
    }

    public function sincronizarStockProductoBase(): void
    {
        if ($this->producto) {
            $totalStock = static::where('producto_id', $this->producto_id)
                ->where('estado', 'activo')
                ->sum('stock_actual');

            $this->producto->update(['stock_actual' => $totalStock]);
        }
    }

    // ─── Generación de SKU ─────────────────────────────────────────────────────

    /**
     * Genera un SKU único para la variante.
     * Formato: [CODIGO_BASE]-[COL]-[SPEC]
     * Ej: KYR-00001-BLA-18W  /  KYR-00001-3000K
     */
    public static function generarSku(Producto $producto, ?Color $color, ?string $especificacion): string
    {
        $base   = strtoupper($producto->codigo ?? 'PROD');
        $sufijos = [];

        if ($color) {
            $sufijos[] = strtoupper(substr(preg_replace('/\s+/', '', $color->nombre), 0, 3));
        }

        if ($especificacion) {
            $spec = strtoupper(preg_replace('/\s+/', '', $especificacion));
            $sufijos[] = $spec;
        }

        $sku      = $base . (count($sufijos) ? '-' . implode('-', $sufijos) : '');
        $original = $sku;
        $contador = 1;

        while (static::where('sku', $sku)->exists()) {
            $sku = $original . '-' . $contador;
            $contador++;
        }

        return $sku;
    }
}
