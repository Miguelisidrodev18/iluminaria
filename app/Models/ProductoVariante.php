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
        'especificacion',
        'tamano',
        'sku',
        'sobreprecio',
        'precio_venta',
        'moneda',
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
        'precio_venta'  => 'decimal:2',
        'estado'        => 'string',
        'atributos'     => 'array',
    ];

    // ─── Atributos específicos de luminaria ────────────────────────────────────

    /**
     * Claves usadas en el JSON `atributos` para variantes de luminaria.
     * Cada clave tiene: label (para UI), placeholder, tipo de input.
     */
    const ATRIBUTOS_LUMINARIA = [
        'acabado'               => ['label' => 'Acabado',               'placeholder' => 'Negro mate, Blanco, Cromado…',     'tipo' => 'text'],
        'tonalidad_luz'         => ['label' => 'Tonalidad de luz',      'placeholder' => '2700K, 3000K, 4000K, 5000K…',      'tipo' => 'text'],
        'tipo_lampara'          => ['label' => 'Tipo de lámpara',       'placeholder' => 'LED, Halógeno, Fluorescente…',     'tipo' => 'text'],
        'angulo_haz'            => ['label' => 'Ángulo de haz',         'placeholder' => '15°, 24°, 36°, 60°…',             'tipo' => 'text'],
        'protocolo_regulacion'  => ['label' => 'Protocolo regulación',  'placeholder' => 'DALI, 0-10V, Triac, PWM…',        'tipo' => 'text'],
        'eficiencia_luminica'   => ['label' => 'Eficiencia lumínica',   'placeholder' => '100 lm/W, 120 lm/W…',             'tipo' => 'text'],
        'garantia'              => ['label' => 'Garantía',              'placeholder' => '2 años, 5 años…',                  'tipo' => 'text'],
        'vida_util'             => ['label' => 'Vida útil',             'placeholder' => '25000h, 50000h…',                  'tipo' => 'text'],
        'ip'                    => ['label' => 'IP',                    'placeholder' => 'IP20, IP44, IP65…',                'tipo' => 'text'],
        'cri'                   => ['label' => 'CRI',                   'placeholder' => '>80, >90, Ra97…',                  'tipo' => 'text'],
        'otros'                 => ['label' => 'Otros',                 'placeholder' => 'Cualquier diferenciador adicional','tipo' => 'text'],
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

    /**
     * Nombre legible de la variante, construido a partir de los atributos
     * diferenciadores más relevantes (nombre manual > atributos luminaria > color).
     */
    public function getNombreCompletoAttribute(): string
    {
        // 1. Si hay nombre manual, úsalo directamente
        if (!empty($this->nombre)) {
            return $this->nombre;
        }

        $partes = [];

        // 2. Atributos luminaria en orden de relevancia
        $prioridad = ['tonalidad_luz', 'acabado', 'tipo_lampara', 'angulo_haz', 'ip', 'cri'];
        $atribs    = $this->atributos ?? [];

        foreach ($prioridad as $clave) {
            if (!empty($atribs[$clave])) {
                $partes[] = $atribs[$clave];
            }
        }

        // 3. Color de variante
        if ($this->color) {
            $partes[] = $this->color->nombre;
        }

        // 4. Especificación genérica (legado)
        if ($this->especificacion && !in_array($this->especificacion, $partes)) {
            $partes[] = $this->especificacion;
        }

        return implode(' / ', $partes) ?: 'Variante base';
    }

    /**
     * Resumen corto para listas: "Producto — variante"
     */
    public function getDescripcionCorta(): string
    {
        $nombre   = $this->producto?->nombre ?? 'Producto';
        $variante = $this->nombre_completo;
        return $variante !== 'Variante base' ? "{$nombre} — {$variante}" : $nombre;
    }

    /**
     * Helper: obtiene un atributo luminaria del JSON
     */
    public function getAtributo(string $clave): ?string
    {
        return ($this->atributos ?? [])[$clave] ?? null;
    }

    /**
     * Helper: establece un atributo luminaria en el JSON (sin guardar)
     */
    public function setAtributo(string $clave, ?string $valor): void
    {
        $atribs         = $this->atributos ?? [];
        $atribs[$clave] = $valor ?: null;
        $this->atributos = array_filter($atribs, fn($v) => !is_null($v));
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
     * Formato: [CODIGO_BASE]-[COL]-[TONALIDAD/SPEC]
     */
    public static function generarSku(Producto $producto, ?Color $color, ?string $especificacion, array $atributos = []): string
    {
        $base    = strtoupper($producto->codigo ?? 'PROD');
        $sufijos = [];

        // Tonalidad de luz es el primer diferenciador en luminaria
        if (!empty($atributos['tonalidad_luz'])) {
            $sufijos[] = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $atributos['tonalidad_luz']));
        }

        if ($color) {
            $sufijos[] = strtoupper(substr(preg_replace('/\s+/', '', $color->nombre), 0, 3));
        }

        if ($especificacion) {
            $sufijos[] = strtoupper(preg_replace('/\s+/', '', $especificacion));
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
