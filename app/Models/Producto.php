<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categoria;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\Modelo;
use App\Models\Catalogo\UnidadMedida;
use App\Models\Catalogo\Color;
use App\Models\Luminaria\ProductoEspecificacion;
use App\Models\Luminaria\ProductoDimension;
use App\Models\Luminaria\ProductoMaterial;
use App\Models\Luminaria\ProductoClasificacion;
use App\Models\Luminaria\ProductoEmbalaje;
use App\Models\Luminaria\TipoProducto;
use App\Models\Luminaria\TipoLuminaria;
use App\Models\Luminaria\Clasificacion;


class Producto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo', 'codigo_kyrios', 'codigo_fabrica',
        'nombre', 'nombre_kyrios', 'descripcion', 'categoria_id',
        'marca_id', 'color_id', 'unidad_medida_id',
        'tipo_inventario', 'tipo_producto_id', 'tipo_luminaria_id',
        'dias_garantia', 'tipo_garantia',
        'stock_actual', 'stock_minimo', 'stock_maximo', 'ubicacion',
        'costo_promedio', 'ultimo_costo_compra', 'fecha_ultima_compra',
        'estado', 'imagen', 'ficha_tecnica_url', 'observaciones',
        'procedencia', 'linea',
        'creado_por', 'modificado_por', 'codigo_barras',
        'estado_aprobacion', 'aprobado_por', 'aprobado_en', 'motivo_rechazo',
        'tipo_sistema', 'descontar_componentes',
        'tiene_variantes',
    ];

    const ESTADOS_APROBACION = [
        'borrador'             => 'Borrador',
        'pendiente_aprobacion' => 'Pendiente',
        'aprobado'             => 'Aprobado',
        'rechazado'            => 'Rechazado',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'stock_actual'        => 'integer',
        'stock_minimo'        => 'integer',
        'stock_maximo'        => 'integer',
        'estado'              => 'string',
        'tipo_inventario'     => 'string',
        'tipo_producto_id'    => 'integer',
        'tipo_luminaria_id'   => 'integer',
        'tipo_garantia'       => 'string',
        'fecha_ultima_compra' => 'date',
        'ultimo_costo_compra' => 'decimal:2',
        'costo_promedio'      => 'decimal:2',
        'aprobado_en'            => 'datetime',
        'descontar_componentes'  => 'boolean',
        'tiene_variantes'        => 'boolean',
    ];

    const TIPOS_SISTEMA = [
        'simple'    => 'Simple',
        'compuesto' => 'Compuesto (Kit)',
        'componente'=> 'Componente',
    ];

    /**
     * Relación: Un producto pertenece a una categoría
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    public function proveedores()
    {
        return $this->belongsToMany(Proveedor::class, 'productos_proveedor')
                    ->withPivot('codigo_proveedor', 'ultimo_precio_compra', 
                               'ultima_fecha_compra', 'plazo_entrega_dias', 
                               'es_preferente', 'observaciones')
                    ->withTimestamps();
    }

    /**
     * Relación: Un producto tiene muchos movimientos de inventario
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    // En app/Models/Producto.php

    // Relación con precios
    public function precios()
    {
        return $this->hasMany(ProductoPrecio::class);
    }

    // Obtener precio de venta actual
    public function getPrecioVentaAttribute(): float
    {
        try {
            return (float) ($this->precios()
                ->where('tipo_precio', 'venta_regular')
                ->where('activo', true)
                ->where(function ($q) {
                    $q->whereNull('fecha_inicio')
                      ->orWhere('fecha_inicio', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('fecha_fin')
                      ->orWhere('fecha_fin', '>=', now());
                })
                ->orderBy('prioridad', 'desc')
                ->value('precio') ?? 0);
        } catch (\Throwable) {
            return 0.0;
        }
    }

    // Obtener precio mayorista
    public function getPrecioMayoristaAttribute(): ?float
    {
        try {
            $precio = $this->precios()
                ->where('tipo_precio', 'venta_mayorista')
                ->where('activo', true)
                ->orderBy('prioridad', 'desc')
                ->value('precio');

            return $precio !== null ? (float) $precio : null;
        } catch (\Throwable) {
            return null;
        }
    }
    /**
     * Scope para productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para productos con stock bajo
     */
    public function scopeStockBajo($query)
    {
        return $query->whereRaw('stock_actual <= stock_minimo');
    }

    /**
     * Scope para productos sin stock
     */
    public function scopeSinStock($query)
    {
        return $query->where('stock_actual', 0);
    }

    /**
     * Scope para productos por categoría
     */
    public function scopeDeCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    /**
     * Scope para búsqueda por código o nombre
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('codigo', 'like', "%{$termino}%")
                ->orWhere('nombre', 'like', "%{$termino}%")
                ->orWhere('codigo_barras', 'like', "%{$termino}%");
        });
    }
    protected $appends = ['imagen_url'];

    /**
     * Accessor: URL completa de la imagen
     */
    public function getImagenUrlAttribute()
    {
        if ($this->imagen) {
            return asset('storage/' . $this->imagen);
        }

        return null;
    }

    /**
     * Accessor: Nombre de categoría
     */
    public function getNombreCategoriaAttribute()
    {
        return $this->categoria ? $this->categoria->nombre : 'Sin categoría';
    }


    /**
     * Accessor: Estado del stock
     */
    public function getEstadoStockAttribute()
    {
        if ($this->stock_actual == 0) {
            return 'sin_stock';
        } elseif ($this->stock_actual <= $this->stock_minimo) {
            return 'bajo';
        } elseif ($this->stock_actual >= $this->stock_maximo) {
            return 'exceso';
        }
        return 'normal';
    }

    /**
     * Accessor: Color del estado de stock para UI
     */
    public function colores()
    {
        return $this->belongsToMany(Color::class, 'producto_color')
                    ->withTimestamps();
    }
    public function getColorEstadoStockAttribute()
    {
        return match($this->estado_stock) {
            'sin_stock' => 'red',
            'bajo' => 'yellow',
            'exceso' => 'blue',
            default => 'green',
        };
    }

    /**
     * Verificar si el producto está activo
     */
    public function estaActivo()
    {
        return $this->estado === 'activo';
    }

    /**
     * Verificar si tiene stock disponible
     */
    public function tieneStock($cantidad = 1)
    {
        return $this->stock_actual >= $cantidad;
    }

    /**
     * Verificar si está en stock bajo
     */
    public function stockBajo()
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    /**
     * Incrementar stock
     */
    public function incrementarStock($cantidad)
    {
        $this->increment('stock_actual', $cantidad);
    }

    /**
     * Decrementar stock
     */
    public function decrementarStock($cantidad)
    {
        if ($this->stock_actual < $cantidad) {
            throw new \Exception('Stock insuficiente para el producto: ' . $this->nombre);
        }
        $this->decrement('stock_actual', $cantidad);
    }

    /**
     * Calcular valor total del inventario de este producto
     */
    public function getValorInventarioAttribute()
    {
        return $this->stock_actual * ($this->costo_promedio ?? 0);
    }

    /**
     * Generar código automático para nuevo producto
     */
    public static function generarCodigo()
    {
        $ultimoProducto = self::latest('id')->first();
        $numero = $ultimoProducto ? $ultimoProducto->id + 1 : 1;
        return 'PROD-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener productos más vendidos (placeholder para cuando tengamos ventas)
     */
    public static function masVendidos($limite = 10)
    {
        // TODO: Implementar cuando tengamos el módulo de ventas
        return self::activos()->limit($limite)->get();
    }

    /**
     * Boot method para eventos del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de eliminar, verificar que no tenga movimientos
        static::deleting(function ($producto) {
            if ($producto->movimientos()->count() > 0) {
                throw new \Exception('No se puede eliminar un producto que tiene movimientos registrados.');
            }
        });
    }
    
    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }
    public function codigosBarras()
    {
        return $this->hasMany(ProductoCodigoBarras::class);
    }

    /**
     * Variantes del producto (color + capacidad)
     */
    public function variantes()
    {
        return $this->hasMany(ProductoVariante::class);
    }

    public function variantesActivas()
    {
        return $this->hasMany(ProductoVariante::class)->where('estado', 'activo');
    }

    public function tieneVariantes(): bool
    {
        return $this->variantes()->exists();
    }

    public function getStockVariantesAttribute(): int
    {
        return $this->variantesActivas()->sum('stock_actual');
    }

    // ─── Relaciones del catálogo técnico de luminarias ────────────────────────

    public function especificacion()
    {
        return $this->hasOne(ProductoEspecificacion::class);
    }

    public function dimensiones()
    {
        return $this->hasOne(ProductoDimension::class);
    }

    public function materiales()
    {
        return $this->hasOne(ProductoMaterial::class);
    }

    public function embalaje()
    {
        return $this->hasOne(ProductoEmbalaje::class);
    }

    public function clasificacion()
    {
        return $this->hasOne(ProductoClasificacion::class);
    }

    // ─── Relaciones de tipo de producto (catálogo relacional) ─────────────────

    public function tiposProyecto()
    {
        return $this->belongsToMany(
            \App\Models\Luminaria\TipoProyecto::class,
            'producto_tipos_proyecto',
            'producto_id',
            'tipo_proyecto_id'
        )->withTimestamps();
    }

    public function tipoProducto()
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }

    public function tipoLuminaria()
    {
        return $this->belongsTo(TipoLuminaria::class, 'tipo_luminaria_id');
    }

    /**
     * Clasificaciones de uso (many-to-many via tabla pivot producto_clasificaciones)
     */
    public function clasificaciones()
    {
        return $this->belongsToMany(
            Clasificacion::class,
            'producto_clasificaciones',
            'producto_id',
            'clasificacion_id'
        )->withTimestamps();
    }

    /**
     * Ubicaciones físicas donde se almacena este producto con cantidad por ubicación
     */
    public function ubicaciones()
    {
        return $this->belongsToMany(
            \App\Models\Ubicacion::class,
            'producto_ubicaciones'
        )->withPivot('cantidad', 'observacion')->withTimestamps();
    }

    // ── Atributos dinámicos ───────────────────────────────────────────────────

    /**
     * Todos los atributos asignados a este producto.
     */
    public function atributos()
    {
        return $this->hasMany(\App\Models\ProductoAtributo::class, 'producto_id')
                    ->with(['atributo', 'valor']);
    }

    /**
     * Retorna los atributos del producto como array estructurado.
     * Útil para pasar a JS como JSON.
     * Formato: { atributo_slug => valor_id|[valor_ids]|valor_texto }
     */
    public function getAtributosParaFormularioAttribute(): array
    {
        $result = [];
        $agrupados = $this->atributos->groupBy('atributo_id');

        foreach ($agrupados as $atributoId => $filas) {
            $atributo = $filas->first()->atributo;
            if (!$atributo) continue;

            if ($atributo->tipo === 'multiselect') {
                // Devuelve array de valor_ids
                $result[$atributo->slug] = $filas->pluck('valor_id')->filter()->values()->toArray();
            } elseif ($atributo->tipo === 'number' || $atributo->tipo === 'text') {
                $result[$atributo->slug] = $filas->first()->valor_texto;
            } else {
                // select, checkbox
                $result[$atributo->slug] = $filas->first()->valor_id;
            }
        }

        return $result;
    }

    /**
     * Sincroniza los atributos dinámicos del producto.
     * Recibe array con clave = slug del atributo, valor = id/ids/texto.
     */
    public function sincronizarAtributos(array $atributosInput): void
    {
        // Borrar atributos existentes y re-insertar
        $this->atributos()->delete();

        $atributosMap = \App\Models\Catalogo\CatalogoAtributo::activos()
            ->get()
            ->keyBy('slug');

        foreach ($atributosInput as $slug => $valor) {
            $atributo = $atributosMap->get($slug);
            if (!$atributo || is_null($valor) || $valor === '') continue;

            if ($atributo->tipo === 'multiselect') {
                $ids = is_array($valor) ? $valor : [$valor];
                foreach ($ids as $valorId) {
                    if (!empty($valorId)) {
                        \App\Models\ProductoAtributo::create([
                            'producto_id' => $this->id,
                            'atributo_id' => $atributo->id,
                            'valor_id'    => $valorId,
                        ]);
                    }
                }
            } elseif (in_array($atributo->tipo, ['number', 'text', 'checkbox'])) {
                \App\Models\ProductoAtributo::create([
                    'producto_id'  => $this->id,
                    'atributo_id'  => $atributo->id,
                    'valor_texto'  => (string) $valor,
                ]);
            } else {
                // select
                \App\Models\ProductoAtributo::create([
                    'producto_id' => $this->id,
                    'atributo_id' => $atributo->id,
                    'valor_id'    => $valor,
                ]);
            }
        }
    }

    // ─── Relaciones BOM (productos compuestos) ────────────────────────────────

    /** Componentes de este producto compuesto, ordenados por posición. */
    public function componentes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductoComponente::class, 'padre_id')->orderBy('orden');
    }

    /** Kits en los que este producto aparece como componente. */
    public function usadoEn(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductoComponente::class, 'hijo_id');
    }

    public function esCompuesto(): bool
    {
        return $this->tipo_sistema === 'compuesto';
    }

    public function esSimple(): bool
    {
        return $this->tipo_sistema === 'simple';
    }

    /**
     * Verifica si hay stock suficiente de todos los componentes obligatorios
     * para armar `$cantidad` kits.
     */
    public function tieneStockComponentes(int $cantidad = 1): bool
    {
        if (!$this->esCompuesto()) return true;

        return $this->componentes()
            ->where('es_opcional', false)
            ->with(['hijo', 'variante'])
            ->get()
            ->every(fn($comp) => $comp->tieneStockParaKits($cantidad));
    }
}
