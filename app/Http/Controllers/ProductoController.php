<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Categoria;
use App\Models\Almacen;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\Color;
use App\Models\Luminaria\ProductoEmbalaje;
use App\Models\Catalogo\UnidadMedida;
use App\Models\Luminaria\TipoProyecto;
use App\Models\Luminaria\TipoProducto;
use App\Models\Luminaria\TipoLuminaria;
use App\Models\Luminaria\Clasificacion;
use App\Models\Luminaria\ProductoEspecificacion;
use App\Models\Luminaria\ProductoDimension;
use App\Models\Luminaria\ProductoMaterial;
use App\Models\Luminaria\ProductoClasificacion;
use App\Models\Ubicacion;
use App\Services\CodigoBarrasService;
use App\Services\VarianteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Constructor - Definir permisos por rol
     */
    public function __construct()
    {
        // Solo Admin y Almacenero pueden crear/editar
        $this->middleware('role:Administrador,Almacenero')
                ->except(['index', 'show', 'consultaTienda', 'buscarAjax']);

        
        // Solo Admin puede eliminar
        $this->middleware('role:Administrador')->only(['destroy']);
    }
    public function consultaTienda(Request $request)
{
    $query = Producto::with('categoria')->activos();
    
    // Búsqueda simple
    if ($request->filled('buscar')) {
        $query->buscar($request->buscar);
    }
    
    // Filtro por categoría
    if ($request->filled('categoria_id')) {
        $query->where('categoria_id', $request->categoria_id);
    }
    
    $productos = $query->orderBy('nombre')->paginate(20);
    $categorias = \App\Models\Categoria::activas()->orderBy('nombre')->get();
    
    return view('inventario.consulta-tienda', compact('productos', 'categorias'));
}
    /**
     * Mostrar listado de productos con filtros
     */
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'variantesActivas.color', 'tipoProducto', 'marca', 'unidadMedida']);

        // Excluir borradores — solo se gestionan en "Aprobar Importados"
        $query->where('estado_aprobacion', '!=', 'borrador');

        // Filtro por búsqueda
        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        // Filtro por categoría (= tipo de producto)

        if ($request->filled('tipo_producto_id')) {
            $query->where('tipo_producto_id', $request->tipo_producto_id);
        }

        // Filtro por estado — por defecto solo 'activo'
        $estadoFiltro = $request->get('estado', 'activo');
        if ($estadoFiltro !== 'todos') {
            $query->where('estado', $estadoFiltro);
        }

        // Filtro por tipo de inventario
        if ($request->filled('tipo_inventario')) {
            $query->where('tipo_inventario', $request->tipo_inventario);
        }

        // Filtro por estado de stock
        if ($request->filled('stock_estado')) {
            switch ($request->stock_estado) {
                case 'bajo':    $query->stockBajo(); break;
                case 'sin_stock': $query->sinStock(); break;
            }
        }

        // Filtro por estado de aprobación
        if ($request->filled('estado_aprobacion')) {
            $query->where('estado_aprobacion', $request->estado_aprobacion);
        }

        $productos     = $query->orderBy('nombre')->paginate(15);
        $tiposProducto = TipoProducto::activos()->orderBy('nombre')->get();

        // Verificar permisos
        $canCreate = in_array(auth()->user()->role->nombre, ['Administrador', 'Almacenero']);
        $canEdit   = in_array(auth()->user()->role->nombre, ['Administrador', 'Almacenero']);
        $canDelete = auth()->user()->role->nombre === 'Administrador';

        return view('inventario.productos.index', compact(
            'productos', 'tiposProducto',
            'canCreate', 'canEdit', 'canDelete'
        ));
    }

    /**
     * Mostrar formulario de crear producto
     */
public function create()
{
    // Obtener datos de inventario
    $categorias = Categoria::where('estado', 'activo')
        ->with(['marcas' => fn($q) => $q->where('estado', 'activo')])
        ->orderBy('nombre')
        ->get();
    $almacenes = Almacen::where('estado', 'activo')->orderBy('nombre')->get();
    
    // Obtener datos del catálogo
    $marcas = Marca::where('estado', 'activo')->orderBy('nombre')->get();
    $colores = Color::where('estado', 'activo')->orderBy('nombre')->get();
    $unidades = UnidadMedida::where('estado', 'activo')->orderBy('nombre')->get(); // ✅ YA LO TIENES
    
    $tiposProyecto   = TipoProyecto::activos()->with(['espacios' => fn($q) => $q->where('activo', true)->orderBy('nombre')])->orderBy('nombre')->get();
    $tiposProducto   = TipoProducto::activos()->orderBy('nombre')->get();
    $tiposLuminaria  = TipoLuminaria::activos()->orderBy('nombre')->get();
    $clasificaciones = Clasificacion::activos()->orderBy('nombre')->get();
    $ubicaciones     = Ubicacion::activas()->orderBy('nombre')->get();

    return view('inventario.productos.create', compact(
        'categorias',
        'almacenes',
        'marcas',
        'colores',
        'unidades',
        'tiposProyecto',
        'tiposProducto',
        'tiposLuminaria',
        'clasificaciones',
        'ubicaciones'
    ));
}

    /**
     * Guardar nuevo producto
     */
    public function store(Request $request, CodigoBarrasService $codigoBarrasService)
{
    $validated = $request->validate([
        // Campos básicos
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'categoria_id' => 'nullable|exists:categorias,id',
        
        // ✅ NUEVO: Foreign Keys desde catálogos
        'marca_id' => 'nullable|exists:marcas,id',           // Cambiado de 'marca'
        'color_id' => 'nullable|exists:colores,id',
        'unidad_medida_id' => 'required|exists:unidades_medida,id', // Cambiado de 'unidad_medida'
        
        // Tipo de producto — FK relacional (siempre requerido)
        'tipo_producto_id'  => 'required|exists:tipos_producto,id',
        'tipo_luminaria_id' => [
            'nullable',
            'exists:tipos_luminaria,id',
            \Illuminate\Validation\Rule::requiredIf(function () use ($request) {
                $tp = \App\Models\Luminaria\TipoProducto::find($request->tipo_producto_id);
                return $tp && $tp->usa_tipo_luminaria;
            }),
        ],

        // Tipo de inventario — solo 'cantidad' para luminarias
        'tipo_inventario' => 'required|in:cantidad',

        // Garantía
        'dias_garantia' => 'nullable|integer|min:0',
        'tipo_garantia' => 'nullable|in:proveedor,tienda,fabricante',

        // Códigos
        'codigo_barras' => 'nullable|string|max:50|unique:productos,codigo_barras',

        // Stock
        'stock_minimo' => 'required|integer|min:0',
        'stock_maximo' => 'required|integer|min:1',
        'ubicacion' => 'nullable|string|max:100',

        // Imagen y estado
        'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        'estado' => 'required|in:activo,inactivo,descontinuado',

        // Stock inicial
        'stock_inicial' => 'nullable|integer|min:0',
        'almacen_id' => 'nullable|required_with:stock_inicial|exists:almacenes,id',

        // Kyrios / ficha técnica
        'codigo_kyrios'     => 'nullable|string|max:100',
        'codigo_fabrica'    => 'nullable|string|max:100',
        'procedencia'       => 'nullable|string|max:100',
        'linea'             => 'nullable|string|max:100',
        'ficha_tecnica_url' => 'nullable|url|max:500',
        'observaciones'     => 'nullable|string',

        // Especificaciones técnicas
        'especificacion.vida_util_horas' => 'nullable|integer|min:0',

        // Dimensiones
        'dimensiones.peso' => 'nullable|numeric|min:0',

        // Tipos de proyecto (multi-valor, pivot)
        'tipo_proyecto_ids'    => 'nullable|array',
        'tipo_proyecto_ids.*'  => 'exists:tipos_proyecto,id',
        // Ubicaciones físicas con cantidad
        'ubicaciones'          => 'nullable|array',
        'ubicaciones.*.id'     => 'required|exists:ubicaciones,id',
        'ubicaciones.*.cantidad' => 'required|integer|min:0',
        'ubicaciones.*.observacion' => 'nullable|string|max:255',
        // Driver controlado
        'especificacion.driver' => 'nullable|in:incluido,no_incluido',
    ]);

    // Generar código automático si no existe
    if (empty($request->codigo)) {
        $validated['codigo'] = Producto::generarCodigo();
    }

    // Generar código de barras si no se proporcionó
    if (empty($validated['codigo_barras'])) {
        $tipoBarras = 'luminaria';
        $validated['codigo_barras'] = $codigoBarrasService->generarCodigoUnico(null, $tipoBarras);
    }

    // Manejar imagen
    if ($request->hasFile('imagen')) {
        $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
    }

    // Fallback de categoria_id si no se envió (campo oculto)
    if (empty($validated['categoria_id'])) {
        $validated['categoria_id'] = \App\Models\Categoria::where('estado', 'activo')->value('id') ?? 1;
    }

    \DB::transaction(function () use ($validated, $request) {
        // Crear producto — siempre inicia como borrador
        $validated['estado_aprobacion'] = 'borrador';
        $validated['creado_por'] = auth()->id();
        $producto = Producto::create($validated);

        // (usos y ambientes se guardan dentro de guardarFichaTecnica)

        // Guardar ficha técnica luminaria
        $this->guardarFichaTecnica($producto, $request);

        // Sincronizar ubicaciones físicas (pivot con cantidad)
        $this->sincronizarUbicaciones($producto, $request);

        \Log::info('Producto creado:', [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'tipo_inventario' => $producto->tipo_inventario,
        ]);

        // Stock inicial SOLO para productos tipo 'cantidad' sin variantes
        if ($producto->tipo_inventario === 'cantidad' &&
            $request->filled('stock_inicial') &&
            $request->stock_inicial > 0 &&
            empty($request->variantes_iniciales)) {

            if ($request->filled('almacen_id')) {
                \App\Models\MovimientoInventario::registrarMovimiento([
                    'producto_id'     => $producto->id,
                    'almacen_id'      => $request->almacen_id,
                    'tipo_movimiento' => 'ingreso',
                    'cantidad'        => $request->stock_inicial,
                    'motivo'          => 'Stock inicial del producto',
                    'usuario_id'      => auth()->id(),
                ]);
            }
        }

        // Si tiene código de barras, guardarlo en la tabla de códigos múltiples
        if ($producto->codigo_barras) {
            $producto->codigosBarras()->create([
                'codigo_barras' => $producto->codigo_barras,
                'descripcion'   => 'Principal',
                'es_principal'  => true,
            ]);
        }

        // Crear variantes iniciales si las hay
        $variantesData = array_filter(
            (array)$request->input('variantes_iniciales', []),
            fn($v) => !empty($v['color_id']) || !empty($v['especificacion']) || !empty($v['nombre'])
                   || !empty(array_filter($v['atributos'] ?? []))
        );
        if (!empty($variantesData)) {
            $varianteService = app(VarianteService::class);
            foreach ($variantesData as $vData) {
                $atributos = array_filter($vData['atributos'] ?? [], fn($a) => $a !== '' && !is_null($a));
                $variante  = $varianteService->obtenerOCrearVariante(
                    $producto,
                    !empty($vData['color_id']) ? (int)$vData['color_id'] : null,
                    !empty($vData['especificacion']) ? $vData['especificacion'] : null,
                    !empty($vData['sobreprecio']) ? (float)$vData['sobreprecio'] : 0,
                    $atributos
                );
                if (!empty($vData['nombre'])) {
                    $variante->update(['nombre' => $vData['nombre']]);
                }
            }
            // Marcar el producto como con variantes
            $producto->update(['tiene_variantes' => true]);
        }
    });

    return redirect()
        ->route('inventario.productos.index')
        ->with('success', 'Producto creado exitosamente');
}
    public function show(Producto $producto)
    {
        $producto->load([
            'categoria', 'marca', 'unidadMedida',
            'especificacion', 'dimensiones', 'materiales', 'clasificacion',
            'tiposProyecto',
            'variantes.color',
            'movimientos' => fn($q) => $q->latest()->limit(10),
        ]);

        $colores = \App\Models\Catalogo\Color::where('estado', 'activo')->orderBy('nombre')->get();

        return view('inventario.productos.show', compact('producto', 'colores'));
    }

    /**
     * Mostrar formulario de editar producto
     */
    public function edit(Producto $producto)
    {
        // Cargar relaciones necesarias
        $producto->load([
            'marca', 'categoria', 'unidadMedida',
            'tipoProducto', 'tipoLuminaria',
            'especificacion', 'dimensiones', 'materiales', 'clasificacion',
            'clasificaciones', 'tiposProyecto', 'ubicaciones',
            'variantes.color',
            'atributos.atributo', 'atributos.valor',
            'componentes.hijo', 'componentes.variante',
        ]);
        
        // Obtener datos para los selects
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();
        $marcas = \App\Models\Catalogo\Marca::where('estado', 'activo')->orderBy('nombre')->get();

        // Obtener colores y unidades de medida
        $colores = \App\Models\Catalogo\Color::where('estado', 'activo')
                    ->orderBy('nombre')
                    ->get();
        
        $unidades = \App\Models\Catalogo\UnidadMedida::where('estado', 'activo')
                    ->orderBy('nombre')
                    ->get();

        // Obtener códigos de barras (opcional)
        $codigosBarras = $producto->codigosBarras ?? collect();

        $tiposProyecto   = TipoProyecto::activos()->with(['espacios' => fn($q) => $q->where('activo', true)->orderBy('nombre')])->orderBy('nombre')->get();
        $tiposProducto   = TipoProducto::activos()->orderBy('nombre')->get();
        $tiposLuminaria  = TipoLuminaria::activos()->orderBy('nombre')->get();
        $clasificaciones = Clasificacion::activos()->orderBy('nombre')->get();
        $ubicaciones     = Ubicacion::activas()->orderBy('nombre')->get();

        // Atributos dinámicos agrupados + valores actuales del producto
        return view('inventario.productos.edit', compact(
            'producto',
            'categorias',
            'marcas',
            'colores',
            'unidades',
            'codigosBarras',
            'tiposProyecto',
            'tiposProducto',
            'tiposLuminaria',
            'clasificaciones',
            'ubicaciones'
        ));
    }

    public function actualizarStockUbicacion(Request $request, Producto $producto)
    {
        $request->validate([
            'stock_minimo' => 'nullable|integer|min:0',
            'stock_maximo' => 'nullable|integer|min:1',
            'ubicacion'    => 'nullable|string|max:100',
        ]);

        $producto->update(array_filter([
            'stock_minimo' => $request->stock_minimo,
            'stock_maximo' => $request->stock_maximo,
            'ubicacion'    => $request->ubicacion,
        ], fn($v) => $v !== null));

        return response()->json(['ok' => true]);
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, Producto $producto)
{
    // Actualización rápida solo del flag descontar_componentes (desde BOM partial)
    if ($request->boolean('_only_descontar_componentes')) {
        $producto->update(['descontar_componentes' => $request->boolean('descontar_componentes')]);
        return back()->with('success', 'Opción actualizada correctamente.');
    }

    $validated = $request->validate([
        'nombre' => 'required|string|max:200',
        'descripcion' => 'nullable|string',
        'categoria_id' => 'nullable|exists:categorias,id',
        
        // Foreign Keys
        'marca_id' => 'nullable|exists:marcas,id',
        'color_id' => 'nullable|exists:colores,id',
        'unidad_medida_id' => 'required|exists:unidades_medida,id',
        
        // Tipo de producto — FK relacional (siempre requerido)
        'tipo_producto_id'  => 'required|exists:tipos_producto,id',
        'tipo_luminaria_id' => [
            'nullable',
            'exists:tipos_luminaria,id',
            \Illuminate\Validation\Rule::requiredIf(function () use ($request) {
                $tp = \App\Models\Luminaria\TipoProducto::find($request->tipo_producto_id);
                return $tp && $tp->usa_tipo_luminaria;
            }),
        ],

        // Tipo de inventario
        'tipo_inventario' => 'required|in:cantidad,serie',

        // Garantía
        'dias_garantia' => 'required_if:tipo_inventario,serie|integer|min:0',
        'tipo_garantia' => 'required_if:tipo_inventario,serie|in:proveedor,tienda,fabricante',

        // Código de barras (único excepto este producto)
        'codigo_barras' => 'nullable|string|max:100|unique:productos,codigo_barras,' . $producto->id,

        // Imagen
        'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

        // Stock
        'stock_minimo' => 'required|integer|min:0',
        'stock_maximo' => 'required|integer|min:1',
        'ubicacion' => 'nullable|string|max:50',
        'estado' => 'required|in:activo,inactivo,descontinuado',

        // Kyrios / ficha técnica
        'nombre_kyrios'     => 'nullable|string|max:255|unique:productos,nombre_kyrios,' . $producto->id,
        'codigo_kyrios'     => 'nullable|string|max:100',
        'codigo_fabrica'    => 'nullable|string|max:100',
        'procedencia'       => 'nullable|string|max:100',
        'linea'             => 'nullable|string|max:100',
        'ficha_tecnica_url' => 'nullable|url|max:500',
        'observaciones'     => 'nullable|string',

        // Embalaje
        'embalaje.peso'             => 'nullable|numeric|min:0',
        'embalaje.volumen'          => 'nullable|numeric|min:0',
        'embalaje.embalado'         => 'nullable|boolean',
        'embalaje.medida_embalaje'  => 'nullable|string|max:100',
        'embalaje.cantidad_por_caja'=> 'nullable|integer|min:1',

        // Clasificaciones de uso (array de IDs)
        'clasificacion_ids'   => 'nullable|array',
        'clasificacion_ids.*' => 'exists:clasificaciones,id',
        // Ubicaciones físicas con cantidad
        'ubicaciones'           => 'nullable|array',
        'ubicaciones.*.id'      => 'required|exists:ubicaciones,id',
        'ubicaciones.*.cantidad' => 'required|integer|min:0',
        'ubicaciones.*.observacion' => 'nullable|string|max:255',
        // Driver controlado
        'especificacion.driver' => 'nullable|in:incluido,no_incluido',
    ]);

    // Subir nueva imagen si existe (antes de la transacción)
    if ($request->hasFile('imagen')) {
        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }
        $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
    }

    DB::transaction(function () use ($request, $producto, $validated) {
        // Actualizar producto
        $producto->update($validated);

        // (usos y ambientes se guardan dentro de guardarFichaTecnica)

        // Guardar ficha técnica luminaria
        $this->guardarFichaTecnica($producto, $request);

        // Guardar embalaje
        $this->guardarEmbalaje($producto, $request);

        // Sincronizar ubicaciones físicas
        $this->sincronizarUbicaciones($producto, $request);

        // Sincronizar atributos dinámicos del configurador
        // Actualizar código de barras principal si cambió
        if ($producto->wasChanged('codigo_barras') && $producto->codigo_barras) {
            $principal = $producto->codigosBarras()->where('es_principal', true)->first();
            if ($principal) {
                $principal->update(['codigo_barras' => $producto->codigo_barras]);
            } else {
                $producto->codigosBarras()->create([
                    'codigo_barras' => $producto->codigo_barras,
                    'descripcion'   => 'Principal',
                    'es_principal'  => true,
                ]);
            }
        }
    });

    return redirect()
        ->route('inventario.productos.index')
        ->with('success', 'Producto actualizado exitosamente');
}

    /**
     * Guardar / actualizar ficha técnica luminaria (especificacion, dimensiones, materiales, clasificacion)
     */
    private function guardarFichaTecnica(Producto $producto, Request $request): void
    {
        if ($request->filled('especificacion')) {
            $data = $request->input('especificacion');
            $data['regulable'] = isset($data['regulable']) ? (bool)$data['regulable'] : false;
            $producto->especificacion()->updateOrCreate(
                ['producto_id' => $producto->id],
                $data
            );
        }

        if ($request->filled('dimensiones')) {
            $producto->dimensiones()->updateOrCreate(
                ['producto_id' => $producto->id],
                $request->input('dimensiones')
            );
        }

        if ($request->filled('materiales')) {
            $producto->materiales()->updateOrCreate(
                ['producto_id' => $producto->id],
                $request->input('materiales')
            );
        }

        // Guardar clasificación de uso (usos, ambientes, tipo_instalacion, estilo)
        $clData          = $request->input('clasificacion', []);
        $usos            = array_values(array_filter((array) ($clData['usos']            ?? [])));
        $ambientes       = array_values(array_filter((array) ($clData['ambientes']       ?? [])));
        $tipoInstalacion = array_values(array_filter((array) ($clData['tipo_instalacion'] ?? [])));
        $estilos         = array_values(array_filter((array) ($clData['estilo']           ?? [])));

        $producto->clasificacion()->updateOrCreate(
            ['producto_id' => $producto->id],
            [
                'usos'             => $usos,
                'ambientes'        => $ambientes,
                'tipo_instalacion' => $tipoInstalacion,
                'estilo'           => $estilos,
            ]
        );

        // Sincronizar tipos de proyecto (pivot multi-valor)
        $tiposProyectoIds = array_filter((array) $request->input('tipo_proyecto_ids', []));
        $producto->tiposProyecto()->sync($tiposProyectoIds);
    }

    /**
     * Guardar / actualizar datos de embalaje y logística
     */
    private function guardarEmbalaje(Producto $producto, Request $request): void
    {
        $data = $request->input('embalaje', []);
        if (empty(array_filter($data, fn($v) => $v !== null && $v !== ''))) {
            return;
        }

        $data['embalado'] = isset($data['embalado']) ? (bool) $data['embalado'] : false;

        $producto->embalaje()->updateOrCreate(
            ['producto_id' => $producto->id],
            $data
        );
    }

    /**
     * Sincronizar ubicaciones físicas del producto (pivot producto_ubicaciones)
     */
    private function sincronizarUbicaciones(Producto $producto, Request $request): void
    {
        $ubicacionesInput = $request->input('ubicaciones', []);
        if (empty($ubicacionesInput)) {
            return;
        }

        $sync = [];
        foreach ($ubicacionesInput as $ub) {
            if (!empty($ub['id']) && isset($ub['cantidad'])) {
                $sync[$ub['id']] = [
                    'cantidad'    => (int) $ub['cantidad'],
                    'observacion' => $ub['observacion'] ?? null,
                ];
            }
        }

        $producto->ubicaciones()->sync($sync);
    }

    /**
     * Generar código Kyrios automático (AJAX)
     *
     * Formato si tipo_producto.usa_tipo_luminaria = true:
     *   KY-[TP][TL][M]-[NNNN]
     *
     * Formato si tipo_producto.usa_tipo_luminaria = false:
     *   KY-[TP]00[M]-[NNNN]
     *
     * TP = tipos_producto.codigo (2 chars)
     * TL = tipos_luminaria.codigo (2 chars) | '00'
     * M  = marcas.codigo (2 chars) | 'XX'
     * NNNN = correlativo por (tipo_producto_id, tipo_luminaria_id, marca_id)
     */
    public function generarCodigoKyrios(Request $request)
    {
        $tipoProductoId  = $request->get('tipo_producto_id');
        $tipoLuminariaId = $request->get('tipo_luminaria_id');
        $marcaId         = $request->get('marca_id');

        // Cargar tipo de producto
        $tipoProducto = TipoProducto::find($tipoProductoId);
        if (!$tipoProducto) {
            return response()->json(['success' => false, 'message' => 'Selecciona un tipo de producto'], 422);
        }

        // Segmento TP (2 chars)
        $segTP = strtoupper($tipoProducto->codigo);

        // Segmento TL (2 chars) o '00'
        $segTL = '00';
        if ($tipoProducto->usa_tipo_luminaria && $tipoLuminariaId) {
            $tipoLuminaria = TipoLuminaria::find($tipoLuminariaId);
            if ($tipoLuminaria) {
                $segTL = strtoupper($tipoLuminaria->codigo);
            }
        }

        // Segmento M (2 chars del código de marca) o 'XX'
        $segM = 'XX';
        if ($marcaId) {
            $marca = \App\Models\Catalogo\Marca::find($marcaId);
            if ($marca && $marca->codigo) {
                $segM = strtoupper($marca->codigo);
            }
        }

        $prefijo = "KY-{$segTP}{$segTL}{$segM}";

        // Correlativo incremental por combinación (tipo_producto_id, tipo_luminaria_id, marca_id)
        $correlativo = Producto::where('tipo_producto_id', $tipoProductoId)
            ->where('tipo_luminaria_id', $tipoLuminariaId ?: null)
            ->where('marca_id', $marcaId ?: null)
            ->whereNotNull('codigo_kyrios')
            ->count() + 1;

        $codigo = $prefijo . '-' . str_pad($correlativo, 4, '0', STR_PAD_LEFT);

        // Garantizar unicidad global
        while (Producto::where('codigo_kyrios', $codigo)->exists()) {
            $correlativo++;
            $codigo = $prefijo . '-' . str_pad($correlativo, 4, '0', STR_PAD_LEFT);
        }

        return response()->json([
            'success'  => true,
            'codigo'   => $codigo,
            'prefijo'  => $prefijo,
            'segmentos' => compact('segTP', 'segTL', 'segM'),
        ]);
    }

    /**
     * Eliminar producto
     */
    public function destroy(Producto $producto)
    {
        try {
            // Eliminar imagen si existe
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            
            $producto->delete();
            
            return redirect()
                ->route('inventario.productos.index')
                ->with('success', 'Producto eliminado exitosamente');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('inventario.productos.index')
                ->with('error', 'No se puede eliminar el producto porque tiene movimientos registrados');
        }
    }
    /**
     * Mostrar gestión de códigos de barras del producto
     */
    public function codigosBarras(Producto $producto)
    {
        $codigosBarras = $producto->codigosBarras()->orderBy('es_principal', 'desc')->get();
        
        return view('inventario.productos.codigos-barras', compact('producto', 'codigosBarras'));
    }

    /**
     * Guardar nuevo código de barras
     */
    public function storeCodigoBarras(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'codigo_barras' => 'required|string|max:50|unique:productos_codigos_barras,codigo_barras',
            'descripcion' => 'nullable|string|max:100',
            'es_principal' => 'boolean'
        ]);

        // Si es principal, quitar principal de los demás
        if ($request->boolean('es_principal')) {
            $producto->codigosBarras()->update(['es_principal' => false]);
        }

        $producto->codigosBarras()->create($validated);

        return redirect()->back()->with('success', 'Código de barras agregado');
    }

    /**
     * Eliminar código de barras
     */
    public function destroyCodigoBarras($codigoBarrasId)
    {
        $codigoBarras = \App\Models\ProductoCodigoBarras::findOrFail($codigoBarrasId);
        $codigoBarras->delete();

        return redirect()->back()->with('success', 'Código de barras eliminado');
    }

    /**
 * Generar código de barras automático (AJAX)
 */
public function generarCodigoBarras(Request $request, CodigoBarrasService $codigoBarrasService)
{
    try {
        $request->validate([
            'tipo' => 'nullable|in:celular,accesorio,otros'
        ]);

        $tipo = $request->get('tipo', 'otros');

        $codigo = $codigoBarrasService->generarCodigoUnico(null, $tipo);
        
        return response()->json([
            'success' => true,
            'codigo' => $codigo,
            'message' => 'Código generado correctamente'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error generando código de barras', [
            'error' => $e->getMessage(),
            'tipo' => $request->tipo
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error al generar código: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Validar si un código de barras ya existe (AJAX)
 */
public function validarCodigoBarras(Request $request)
    {
        $codigo = $request->get('codigo');
        $productoId = $request->get('producto_id');
        
        $query = Producto::where('codigo_barras', $codigo);
        
        // Si estamos editando, excluir el producto actual
        if ($productoId) {
            $query->where('id', '!=', $productoId);
        }
        
        $existe = $query->exists();
        
        // También verificar en la tabla de códigos de barras múltiples
        if (!$existe && class_exists('\App\Models\ProductoCodigoBarras')) {
            $existe = \App\Models\ProductoCodigoBarras::where('codigo_barras', $codigo)->exists();
        }
        
        return response()->json([
            'existe' => $existe,
            'codigo' => $codigo
        ]);
    }
    /**
     * Establecer como principal
     */
    public function setPrincipalCodigoBarras($codigoBarrasId)
    {
        $codigoBarras = \App\Models\ProductoCodigoBarras::findOrFail($codigoBarrasId);
        
        // Quitar principal de todos los demás
        $codigoBarras->producto->codigosBarras()->update(['es_principal' => false]);
        
        // Establecer este como principal
        $codigoBarras->update(['es_principal' => true]);

        return redirect()->back()->with('success', 'Código de barras principal actualizado');
    }
    /**
     * Búsqueda AJAX para autocompletado
     */
    public function buscarAjax(Request $request)
{
    $termino = $request->get('q');
    $proveedor_id = $request->get('proveedor_id'); // Para filtrar por proveedor
    
    $query = Producto::activos()
        ->with(['marca', 'unidadMedida'])
        ->buscar($termino);
    
    // Si se especifica proveedor, filtrar productos que tiene ese proveedor
    if ($proveedor_id) {
        $query->whereHas('proveedores', function($q) use ($proveedor_id) {
            $q->where('proveedor_id', $proveedor_id);
        });
    }
    
    $productos = $query->limit(10)->get([
        'id', 
        'codigo', 
        'nombre', 
        'tipo_inventario',
        'marca_id',
        'modelo_id',
        'color_id',
        'unidad_medida_id'
    ]);
    
    // Formatear respuesta para el select2
    $resultados = $productos->map(function($producto) {
        return [
            'id' => $producto->id,
            'text' => $producto->codigo . ' - ' . $producto->nombre,
            'nombre' => $producto->nombre,
            'codigo' => $producto->codigo,
            'tipo_inventario' => $producto->tipo_inventario,
            'marca' => $producto->marca->nombre ?? '',
            'unidad' => $producto->unidadMedida->nombre ?? ''
        ];
    });
    
    return response()->json($resultados);
}

// ─────────────────────────────────────────────────────────────────────────────
// GESTIÓN DE VARIANTES
// ─────────────────────────────────────────────────────────────────────────────

/**
 * POST /inventario/productos/{producto}/toggle-variantes
 * Activar o desactivar el modo variantes del producto
 */
public function toggleVariantesMode(Producto $producto)
{
    $nuevo = !$producto->tiene_variantes;
    $producto->update(['tiene_variantes' => $nuevo]);

    $msg = $nuevo
        ? 'Modo variantes activado. Ahora puedes agregar variantes al producto.'
        : 'Modo variantes desactivado.';

    return back()->with('success', $msg);
}

/**
 * Mostrar variantes de un producto (vista web)
 */
public function variantes(Producto $producto)
{
    $producto->load(['variantes.color', 'marca', 'categoria', 'tipoProducto']);
    $colores = Color::where('estado', 'activo')->orderBy('nombre')->get();
    $atributosLuminaria = \App\Models\ProductoVariante::ATRIBUTOS_LUMINARIA;

    return view('inventario.productos.variantes', compact('producto', 'colores', 'atributosLuminaria'));
}

/**
 * POST /inventario/productos/{producto}/variantes
 * Crear variante desde el formulario web
 */
public function storeVariante(Request $request, Producto $producto, VarianteService $varianteService)
{
    // Validar campos base + cada clave de atributos luminaria
    $atributoKeys = array_keys(\App\Models\ProductoVariante::ATRIBUTOS_LUMINARIA);
    $atributoRules = array_fill_keys(
        array_map(fn($k) => "atributos.{$k}", $atributoKeys),
        'nullable|string|max:150'
    );

    $validated = $request->validate(array_merge([
        'nombre'         => 'nullable|string|max:100',
        'color_id'       => 'nullable|exists:colores,id',
        'especificacion' => 'nullable|string|max:100',
        'sobreprecio'    => 'nullable|numeric|min:0',
        'precio_venta'   => 'nullable|numeric|min:0',
        'moneda'         => 'nullable|in:PEN,USD',
        'stock_inicial'  => 'nullable|integer|min:0',
        'atributos'      => 'nullable|array',
    ], $atributoRules));

    // Limpiar atributos vacíos
    $atributos = array_filter($validated['atributos'] ?? [], fn($v) => !is_null($v) && $v !== '');

    try {
        $variante = $varianteService->obtenerOCrearVariante(
            $producto,
            $validated['color_id'] ?? null,
            $validated['especificacion'] ?? null,
            (float)($validated['sobreprecio'] ?? 0),
            $atributos
        );

        $actualizaciones = [];
        if (!empty($validated['nombre'])) {
            $actualizaciones['nombre'] = $validated['nombre'];
        }
        if (!empty($atributos)) {
            $actualizaciones['atributos'] = $atributos;
        }
        if (isset($validated['precio_venta']) && $validated['precio_venta'] !== null) {
            $actualizaciones['precio_venta'] = $validated['precio_venta'] ?: null;
        }
        if (!empty($validated['moneda'])) {
            $actualizaciones['moneda'] = $validated['moneda'];
        }
        if (!empty($actualizaciones)) {
            $variante->update($actualizaciones);
        }

        if (!empty($validated['stock_inicial']) && $validated['stock_inicial'] > 0) {
            $variante->incrementarStock((int)$validated['stock_inicial']);
        }

        // Asegurarse de que el producto tenga el flag activo
        if (!$producto->tiene_variantes) {
            $producto->update(['tiene_variantes' => true]);
        }

        return redirect()
            ->route('inventario.productos.variantes', $producto)
            ->with('success', 'Variante agregada: ' . $variante->sku);

    } catch (\Exception $e) {
        return back()->withInput()->with('error', $e->getMessage());
    }
}

/**
 * PUT /inventario/productos/variantes/{variante}
 * Actualizar variante (inline o desde el formulario)
 */
public function updateVariante(Request $request, \App\Models\ProductoVariante $variante)
{
    $atributoKeys = array_keys(\App\Models\ProductoVariante::ATRIBUTOS_LUMINARIA);
    $atributoRules = array_fill_keys(
        array_map(fn($k) => "atributos.{$k}", $atributoKeys),
        'nullable|string|max:150'
    );

    $validated = $request->validate(array_merge([
        'nombre'         => 'nullable|string|max:100',
        'color_id'       => 'nullable|exists:colores,id',
        'especificacion' => 'nullable|string|max:100',
        'sobreprecio'    => 'nullable|numeric|min:0',
        'precio_venta'   => 'nullable|numeric|min:0',
        'moneda'         => 'nullable|in:PEN,USD',
        'stock_minimo'   => 'nullable|integer|min:0',
        'atributos'      => 'nullable|array',
        'estado'         => 'nullable|in:activo,inactivo',
    ], $atributoRules));

    // Limpiar atributos vacíos pero conservar los que ya existían
    if (isset($validated['atributos'])) {
        $existentes = $variante->atributos ?? [];
        $nuevos     = array_filter($validated['atributos'], fn($v) => !is_null($v) && $v !== '');
        $validated['atributos'] = array_merge($existentes, $nuevos);
    }

    $variante->update($validated);

    if ($request->wantsJson()) {
        return response()->json([
            'success'  => true,
            'variante' => array_merge($variante->fresh()->toArray(), [
                'nombre_completo' => $variante->fresh()->nombre_completo,
            ]),
        ]);
    }

    return back()->with('success', 'Variante actualizada');
}

/**
 * PATCH /inventario/productos/variantes/{variante}/precio
 * Actualizar solo el precio de una variante (AJAX desde tabla de precios)
 */
public function actualizarPrecioVariante(Request $request, ProductoVariante $variante)
{
    $request->validate([
        'precio_venta' => 'nullable|numeric|min:0',
        'moneda'       => 'in:PEN,USD',
    ]);

    $variante->update([
        'precio_venta' => $request->filled('precio_venta') ? (float)$request->precio_venta : null,
        'moneda'       => $request->moneda ?? 'PEN',
    ]);

    return response()->json([
        'ok'          => true,
        'precio_venta' => $variante->precio_venta,
        'moneda'       => $variante->moneda,
    ]);
}

/**
 * POST /inventario/productos/variantes/{variante}/reactivar
 * Reactivar variante inactiva
 */
public function reactivarVariante(ProductoVariante $variante)
{
    $variante->update(['estado' => 'activo']);
    $variante->sincronizarStockProductoBase();

    return back()->with('success', 'Variante reactivada: ' . $variante->nombre_completo);
}

/**
 * DELETE /inventario/productos/variantes/{variante}
 * Desactivar variante
 */
public function destroyVariante(ProductoVariante $variante, VarianteService $varianteService)
{
    try {
        $productoId = $variante->producto_id;
        $varianteService->desactivarVariante($variante);

        return redirect()
            ->route('inventario.productos.variantes', $productoId)
            ->with('success', 'Variante desactivada');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}

/**
 * POST /inventario/productos/{producto}/aprobar
 * Aprobar producto (requiere permiso aprobar_producto)
 */
public function approve(Producto $producto)
{
    $this->authorize('aprobar_producto');

    $producto->update([
        'estado_aprobacion' => 'aprobado',
        'aprobado_por'      => auth()->id(),
        'aprobado_en'       => now(),
        'motivo_rechazo'    => null,
    ]);

    return back()->with('success', "Producto «{$producto->nombre}» aprobado.");
}

/**
 * POST /inventario/productos/{producto}/rechazar
 * Rechazar producto con motivo (requiere permiso aprobar_producto)
 */
public function reject(Request $request, Producto $producto)
{
    $this->authorize('aprobar_producto');

    $request->validate([
        'motivo_rechazo' => 'required|string|max:500',
    ]);

    $producto->update([
        'estado_aprobacion' => 'rechazado',
        'aprobado_por'      => auth()->id(),
        'aprobado_en'       => now(),
        'motivo_rechazo'    => $request->motivo_rechazo,
    ]);

    return back()->with('success', "Producto «{$producto->nombre}» rechazado.");
}

/**
 * POST /inventario/productos/{producto}/enviar-aprobacion
 * El almacenero envía el producto a revisión
 */
public function submitForApproval(Producto $producto)
{
    if ($producto->estado_aprobacion !== 'borrador' && $producto->estado_aprobacion !== 'rechazado') {
        return back()->with('error', 'El producto ya fue enviado o está aprobado.');
    }

    $producto->update([
        'estado_aprobacion' => 'pendiente_aprobacion',
        'motivo_rechazo'    => null,
    ]);

    return back()->with('success', 'Producto enviado a revisión.');
}

/**
 * Mostrar productos de un proveedor específico
 */
public function productosPorProveedor($proveedorId)
{
    $proveedor = \App\Models\Proveedor::findOrFail($proveedorId);
    
    $productos = Producto::whereHas('proveedores', function($q) use ($proveedorId) {
        $q->where('proveedor_id', $proveedorId);
    })->with(['marca'])->get();
    
    return view('proveedores.productos', compact('proveedor', 'productos'));
}

/**
 * Asociar producto a proveedor
 */
public function asociarProveedor(Request $request, $productoId)
{
    $validated = $request->validate([
        'proveedor_id' => 'required|exists:proveedores,id',
        'codigo_proveedor' => 'nullable|string|max:100',
        'plazo_entrega_dias' => 'nullable|integer|min:0',
        'es_preferente' => 'boolean'
    ]);
    
    $producto = Producto::findOrFail($productoId);
    
    $producto->proveedores()->syncWithoutDetaching([
        $validated['proveedor_id'] => [
            'codigo_proveedor' => $validated['codigo_proveedor'] ?? null,
            'plazo_entrega_dias' => $validated['plazo_entrega_dias'] ?? 0,
            'es_preferente' => $validated['es_preferente'] ?? false
        ]
    ]);
    
    return response()->json(['success' => true]);
}
}