<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\ProductoComponente;
use App\Models\Categoria;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\UnidadMedida;
use App\Models\Luminaria\TipoProducto;
use App\Models\Luminaria\TipoLuminaria;
use App\Models\Luminaria\ProductoEspecificacion;
use App\Models\Luminaria\ProductoDimension;
use App\Models\Luminaria\ProductoMaterial;

class ImportadorProductosController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Administrador,Almacenero');
    }

    // ─── Vista del formulario de importación ──────────────────────────────────

    public function index()
    {
        return view('inventario.productos.importar');
    }

    // ─── Descargar plantilla Excel ────────────────────────────────────────────

    public function descargarPlantilla()
    {
        // Crear CSV como plantilla simplificada (más compatible que Excel v1)
        $filename = 'plantilla_productos.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            // BOM para UTF-8 en Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Hoja PRODUCTOS
            fputcsv($handle, ['=== HOJA: PRODUCTOS ===']);
            fputcsv($handle, [
                'codigo', 'nombre', 'tipo_sistema', 'categoria', 'marca',
                'tipo_kyrios', 'tipo_luminaria', 'potencia_w', 'lumenes',
                'voltaje', 'temperatura_color_k', 'cri', 'ip',
                'alto_mm', 'ancho_mm', 'diametro_mm',
                'material_1', 'color_acabado_1',
                'stock_inicial', 'precio_compra', 'estado',
            ]);
            fputcsv($handle, [
                'DL-8W-EMP-BL', 'Downlight LED Slim 8W Blanco', 'simple', 'Empotrables', 'Kyrios',
                'LU', 'ET', '8', '800', '220', '3000', '80', '65',
                '45', '145', '145',
                'Aluminio', 'Blanco',
                '10', '25.50', 'activo',
            ]);

            fputcsv($handle, []);
            fputcsv($handle, ['=== HOJA: VARIANTES ===']);
            fputcsv($handle, ['codigo_padre', 'especificacion', 'sobreprecio', 'stock_inicial']);
            fputcsv($handle, ['DL-8W-EMP-BL', '3000K', '0', '5']);

            fputcsv($handle, []);
            fputcsv($handle, ['=== HOJA: COMPONENTES (para kits) ===']);
            fputcsv($handle, ['codigo_padre', 'codigo_hijo', 'cantidad', 'unidad', 'es_opcional', 'orden']);
            fputcsv($handle, ['KIT-DL-18W', 'DL-8W-EMP-BL', '1', 'unidad', '0', '1']);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── Procesar archivo Excel / CSV ─────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        $archivo = $request->file('archivo');
        $ext     = strtolower($archivo->getClientOriginalExtension());

        try {
            if (in_array($ext, ['xlsx', 'xls'])) {
                $filas = $this->leerExcel($archivo->getRealPath());
            } else {
                $filas = $this->leerCsv($archivo->getRealPath());
            }

            $resultado = $this->procesarFilas($filas);

            return back()->with('importacion', $resultado);

        } catch (\Throwable $e) {
            return back()->withErrors(['archivo' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    // ─── Leer CSV ─────────────────────────────────────────────────────────────

    private function leerCsv(string $path): array
    {
        $filas = [];
        if (($handle = fopen($path, 'r')) === false) {
            throw new \RuntimeException('No se pudo abrir el archivo CSV.');
        }

        // Detectar y saltar BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $cabecera  = null;
        $seccion   = null; // 'productos' | 'variantes' | 'componentes'
        $secciones = ['productos' => [], 'variantes' => [], 'componentes' => []];

        while (($fila = fgetcsv($handle, 1000, ',')) !== false) {
            $first = trim($fila[0] ?? '');

            if (str_contains($first, 'HOJA: PRODUCTOS')) { $seccion = 'productos'; $cabecera = null; continue; }
            if (str_contains($first, 'HOJA: VARIANTES')) { $seccion = 'variantes'; $cabecera = null; continue; }
            if (str_contains($first, 'HOJA: COMPONENTES')) { $seccion = 'componentes'; $cabecera = null; continue; }

            if (!$seccion) continue;
            if (empty(array_filter($fila))) continue; // fila vacía

            if ($cabecera === null) {
                $cabecera = array_map('trim', $fila);
                continue;
            }

            $secciones[$seccion][] = array_combine(
                $cabecera,
                array_pad(array_map('trim', $fila), count($cabecera), '')
            );
        }

        fclose($handle);
        return $secciones;
    }

    // ─── Leer XLSX usando maatwebsite/excel v1.1 ──────────────────────────────

    private function leerExcel(string $path): array
    {
        $secciones = ['productos' => [], 'variantes' => [], 'componentes' => []];

        $data = \Excel::load($path, function ($reader) {
            $reader->noHeading();
        })->get();

        $sheetMap = [
            'PRODUCTOS'   => 'productos',
            'VARIANTES'   => 'variantes',
            'COMPONENTES' => 'componentes',
        ];

        foreach ($data->toArray() as $sheetName => $rows) {
            $key = strtoupper(trim($sheetName));
            foreach ($sheetMap as $name => $sectionKey) {
                if (str_contains($key, $name)) {
                    $cabecera = null;
                    foreach ($rows as $row) {
                        $row = array_map('trim', $row);
                        if (empty(array_filter($row))) continue;
                        if ($cabecera === null) {
                            $cabecera = $row;
                            continue;
                        }
                        $secciones[$sectionKey][] = array_combine(
                            $cabecera,
                            array_pad($row, count($cabecera), '')
                        );
                    }
                }
            }
        }

        return $secciones;
    }

    // ─── Procesar las filas extraídas ─────────────────────────────────────────

    private function procesarFilas(array $secciones): array
    {
        $log    = ['creados' => 0, 'actualizados' => 0, 'variantes' => 0, 'componentes' => 0, 'errores' => []];
        $mapaId = []; // codigo => producto_id

        DB::beginTransaction();
        try {
            // ── 1. PRODUCTOS ───────────────────────────────────────────────────
            foreach ($secciones['productos'] as $i => $fila) {
                $linea = $i + 2;
                try {
                    $nombre = $fila['nombre'] ?? '';
                    if (empty($nombre)) {
                        $log['errores'][] = "Fila {$linea}: falta el nombre del producto.";
                        continue;
                    }

                    // Resolver categoría (crea si no existe)
                    $catNombre = $fila['categoria'] ?? 'General';
                    $categoria = Categoria::firstOrCreate(
                        ['nombre' => $catNombre],
                        ['estado' => 'activo', 'descripcion' => 'Creada automáticamente desde importación']
                    );

                    // Resolver marca (crea si no existe)
                    $marcaId = null;
                    if (!empty($fila['marca'])) {
                        $marca   = Marca::firstOrCreate(
                            ['nombre' => $fila['marca']],
                            ['estado' => 'activo', 'codigo' => Str::upper(Str::slug($fila['marca'], ''))]
                        );
                        $marcaId = $marca->id;
                    }

                    // Unidad de medida por defecto
                    $unidadId = UnidadMedida::where('simbolo', 'und')
                        ->orWhere('nombre', 'like', '%unidad%')
                        ->value('id') ?? 1;

                    // Tipo de producto
                    $tipoProdId = null;
                    if (!empty($fila['tipo_kyrios'])) {
                        $tipoProdId = TipoProducto::where('codigo', $fila['tipo_kyrios'])->value('id');
                    }
                    if (!$tipoProdId) {
                        $tipoProdId = TipoProducto::first()?->id ?? 1;
                    }

                    // Tipo de luminaria
                    $tipoLumId = null;
                    if (!empty($fila['tipo_luminaria'])) {
                        $tipoLumId = TipoLuminaria::where('codigo', $fila['tipo_luminaria'])->value('id');
                    }

                    $codigo = !empty($fila['codigo']) ? $fila['codigo'] : Producto::generarCodigo();
                    $tipoSistema = in_array($fila['tipo_sistema'] ?? '', ['simple', 'compuesto', 'componente'])
                        ? $fila['tipo_sistema']
                        : 'simple';

                    $existe = Producto::where('codigo', $codigo)->first();

                    $datos = [
                        'nombre'           => $nombre,
                        'categoria_id'     => $categoria->id,
                        'marca_id'         => $marcaId,
                        'unidad_medida_id' => $unidadId,
                        'tipo_producto_id' => $tipoProdId,
                        'tipo_luminaria_id'=> $tipoLumId,
                        'tipo_sistema'     => $tipoSistema,
                        'tipo_inventario'  => 'cantidad',
                        'stock_minimo'     => 0,
                        'stock_maximo'     => 9999,
                        'estado'           => in_array($fila['estado'] ?? 'activo', ['activo', 'inactivo', 'descontinuado'])
                                             ? ($fila['estado'] ?? 'activo') : 'activo',
                        'ultimo_costo_compra' => !empty($fila['precio_compra']) ? (float)$fila['precio_compra'] : null,
                        'costo_promedio'      => !empty($fila['precio_compra']) ? (float)$fila['precio_compra'] : null,
                    ];

                    if ($existe) {
                        $existe->update($datos);
                        $producto = $existe;
                        $log['actualizados']++;
                    } else {
                        $datos['codigo'] = $codigo;
                        $datos['estado_aprobacion'] = 'borrador';
                        $producto = Producto::create($datos);
                        $log['creados']++;
                    }

                    $mapaId[$codigo] = $producto->id;

                    // Stock inicial via increment directo (evitar observer)
                    $stockInicial = (int)($fila['stock_inicial'] ?? 0);
                    if ($stockInicial > 0 && !$existe) {
                        $producto->update(['stock_actual' => $stockInicial]);
                    }

                    // Especificaciones técnicas
                    $this->guardarEspecificaciones($producto, $fila);

                } catch (\Throwable $e) {
                    $log['errores'][] = "Fila {$linea} ('{$nombre}'): " . $e->getMessage();
                }
            }

            // ── 2. VARIANTES ───────────────────────────────────────────────────
            foreach ($secciones['variantes'] as $i => $fila) {
                $linea = $i + 2;
                try {
                    $codigoPadre = $fila['codigo_padre'] ?? '';
                    if (empty($codigoPadre)) continue;

                    $padreId = $mapaId[$codigoPadre]
                        ?? Producto::where('codigo', $codigoPadre)->value('id');

                    if (!$padreId) {
                        $log['errores'][] = "Variante fila {$linea}: producto padre '{$codigoPadre}' no encontrado.";
                        continue;
                    }

                    $especificacion = $fila['especificacion'] ?? null;

                    $variante = ProductoVariante::firstOrCreate(
                        ['producto_id' => $padreId, 'especificacion' => $especificacion ?: null],
                        [
                            'sobreprecio'  => (float)($fila['sobreprecio'] ?? 0),
                            'stock_actual' => (int)($fila['stock_inicial'] ?? 0),
                            'estado'       => 'activo',
                        ]
                    );

                    if ($variante->wasRecentlyCreated) {
                        $log['variantes']++;
                    }
                } catch (\Throwable $e) {
                    $log['errores'][] = "Variante fila {$linea}: " . $e->getMessage();
                }
            }

            // ── 3. COMPONENTES BOM ─────────────────────────────────────────────
            foreach ($secciones['componentes'] as $i => $fila) {
                $linea = $i + 2;
                try {
                    $codigoPadre = $fila['codigo_padre'] ?? '';
                    $codigoHijo  = $fila['codigo_hijo']  ?? '';
                    if (empty($codigoPadre) || empty($codigoHijo)) continue;

                    $padreId = $mapaId[$codigoPadre]
                        ?? Producto::where('codigo', $codigoPadre)->value('id');
                    $hijoId  = $mapaId[$codigoHijo]
                        ?? Producto::where('codigo', $codigoHijo)->value('id');

                    if (!$padreId || !$hijoId) {
                        $log['errores'][] = "Componente fila {$linea}: padre '{$codigoPadre}' o hijo '{$codigoHijo}' no encontrado.";
                        continue;
                    }

                    ProductoComponente::firstOrCreate(
                        ['padre_id' => $padreId, 'hijo_id' => $hijoId, 'variante_id' => null],
                        [
                            'cantidad'    => (float)($fila['cantidad'] ?? 1),
                            'unidad'      => $fila['unidad'] ?? 'unidad',
                            'es_opcional' => (bool)(int)($fila['es_opcional'] ?? 0),
                            'orden'       => (int)($fila['orden'] ?? 0),
                        ]
                    );
                    $log['componentes']++;
                } catch (\Throwable $e) {
                    $log['errores'][] = "Componente fila {$linea}: " . $e->getMessage();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $log;
    }

    // ─── Guardar especificaciones y dimensiones técnicas ──────────────────────

    private function guardarEspecificaciones(Producto $producto, array $fila): void
    {
        $camposEspec = ['potencia_w', 'lumenes', 'voltaje', 'temperatura_color_k', 'cri', 'ip'];
        $tieneEspec  = array_filter(array_intersect_key($fila, array_flip($camposEspec)));

        if ($tieneEspec) {
            ProductoEspecificacion::updateOrCreate(
                ['producto_id' => $producto->id],
                [
                    'potencia'            => !empty($fila['potencia_w'])          ? (float)$fila['potencia_w'] : null,
                    'lumenes'             => !empty($fila['lumenes'])              ? (int)$fila['lumenes']     : null,
                    'voltaje'             => !empty($fila['voltaje'])              ? (float)$fila['voltaje']   : null,
                    'temperatura_color'   => !empty($fila['temperatura_color_k'])  ? (int)$fila['temperatura_color_k'] : null,
                    'cri'                 => !empty($fila['cri'])                  ? (int)$fila['cri']         : null,
                    'ip'                  => !empty($fila['ip'])                   ? (int)$fila['ip']          : null,
                ]
            );
        }

        $camposDim = ['alto_mm', 'ancho_mm', 'diametro_mm'];
        $tieneDim  = array_filter(array_intersect_key($fila, array_flip($camposDim)));

        if ($tieneDim) {
            ProductoDimension::updateOrCreate(
                ['producto_id' => $producto->id],
                [
                    'alto'     => !empty($fila['alto_mm'])     ? (float)$fila['alto_mm']     : null,
                    'ancho'    => !empty($fila['ancho_mm'])     ? (float)$fila['ancho_mm']    : null,
                    'diametro' => !empty($fila['diametro_mm'])  ? (float)$fila['diametro_mm'] : null,
                ]
            );
        }

        if (!empty($fila['material_1'])) {
            ProductoMaterial::updateOrCreate(
                ['producto_id' => $producto->id],
                [
                    'material_1'       => $fila['material_1'],
                    'color_acabado_1'  => $fila['color_acabado_1'] ?? null,
                ]
            );
        }
    }
}
