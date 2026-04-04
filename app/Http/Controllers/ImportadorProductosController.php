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
use App\Models\Luminaria\Clasificacion;
use App\Models\Luminaria\ProductoEspecificacion;
use App\Models\Luminaria\ProductoDimension;
use App\Models\Luminaria\ProductoMaterial;
use App\Models\Luminaria\ProductoEmbalaje;

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
        $filename = 'plantilla_productos.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM UTF-8

            // ── Hoja PRODUCTOS ──
            fputcsv($handle, [
                'Código_fabrica', 'tipo_producto', 'tipo_fuente', 'ambiente', 'tipo_luminaria',
                'nombre_origen', 'nombre_kyrios', 'color_acabado', 'Tamaño', 'diametro_lado',
                'ancho_producto', 'salida', 'alto', 'alto-suspendido',
                'diámetro_lado_producto_agujero', 'ancho_producto_ajugero', 'profundidad_producto_ajugero',
                'socket_producto', 'numero_lamparas_producto', 'material_1_producto', 'material_2_producto',
                'nivel_potencia_producto', 'W_lumenes_producto', 'real_lumenes', 'nominal_lumenes',
                'tonalidad_luz_lumenes', 'T° color especifica', 'IP', 'CRI', 'Angulo de apertura',
                'IK', 'Voltaje', 'Driver', 'Posibilidad de regulación', 'Protocolo de regulación',
                'Marca', 'línea', 'Procedencia', 'Ficha tecnica fabrica',
                'PESO', 'VOLUMEN', 'EMBALADO', 'Medida de embalaje', 'CANTIDAD POR CAJA',
                'CODIGOS RELACIONADOS',
            ]);
            fputcsv($handle, [
                'DL-8W-001', 'LU', 'LED', 'Interior', 'ET',
                'Downlight LED Slim 8W', 'DL-KYRIOS-001', 'Blanco', '145mm', '145',
                '', 'Directa', '45', '',
                '135', '', '',
                'GU10', '1', 'Aluminio', 'PC',
                'Baja (0–10W)', '100', '800', '850',
                'Cálido', '3000K', 'IP65', '80', '36°',
                'IK08', '220V', 'incluido', '1', '0-10V',
                'Kyrios', 'Premium', 'China', 'https://...',
                '0.250', '500', '0', '20x20x10 cm', '12',
                '',
            ]);

            // ── Separador + Hoja VARIANTES ──
            fputcsv($handle, []);
            fputcsv($handle, ['=== VARIANTES (una fila por variante) ===']);
            fputcsv($handle, [
                'codigo_fabrica',       // FK al producto base
                'variante_nombre',      // Nombre descriptivo libre
                'variante_color',       // Nombre del color (debe existir en catálogo)
                'variante_acabado',     // Acabado: Negro mate, Blanco, Cromado…
                'variante_tonalidad_luz',       // 2700K, 3000K, 4000K, 5000K…
                'variante_tipo_lampara',        // LED, Halógeno, Fluorescente…
                'variante_angulo_haz',          // 15°, 24°, 36°, 60°…
                'variante_protocolo_regulacion',// DALI, 0-10V, Triac, PWM…
                'variante_eficiencia_luminica', // 100 lm/W, 120 lm/W…
                'variante_garantia',            // 2 años, 5 años…
                'variante_vida_util',           // 25000h, 50000h…
                'variante_ip',                  // IP20, IP44, IP65…
                'variante_cri',                 // >80, >90, Ra97…
                'variante_otros',               // Cualquier diferenciador adicional
                'variante_sobreprecio',         // Sobreprecio sobre precio base (0 si igual)
                'variante_stock_inicial',       // Stock inicial para esta variante
            ]);
            fputcsv($handle, [
                'DL-8W-001', 'LED 3000K Negro', 'Negro',
                'Negro mate', '3000K', 'LED', '36°', 'DALI',
                '110 lm/W', '3 años', '50000h', 'IP65', '>80', '',
                '0', '10',
            ]);
            fputcsv($handle, [
                'DL-8W-001', 'LED 4000K Blanco', 'Blanco',
                'Blanco', '4000K', 'LED', '36°', '0-10V',
                '115 lm/W', '3 años', '50000h', 'IP65', '>80', '',
                '5', '5',
            ]);

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

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $cabecera = null;

        while (($fila = fgetcsv($handle, 2000, ',')) !== false) {
            if (empty(array_filter($fila))) continue;

            if ($cabecera === null) {
                $cabecera = array_map('trim', $fila);
                continue;
            }

            $filas[] = array_combine(
                $cabecera,
                array_pad(array_map('trim', $fila), count($cabecera), '')
            );
        }

        fclose($handle);
        return $filas;
    }

    // ─── Leer XLSX ────────────────────────────────────────────────────────────

    private function leerExcel(string $path): array
    {
        $filas    = [];
        $cabecera = null;

        $data = \Excel::load($path, function ($reader) {
            $reader->noHeading();
        })->get()->toArray();

        // Usar primera hoja con datos
        $rows = is_array(reset($data)) ? reset($data) : [];

        foreach ($rows as $row) {
            $row = array_map('trim', $row);
            if (empty(array_filter($row))) continue;

            if ($cabecera === null) {
                $cabecera = $row;
                continue;
            }

            $filas[] = array_combine(
                $cabecera,
                array_pad($row, count($cabecera), '')
            );
        }

        return $filas;
    }

    // ─── Normalización de valores ─────────────────────────────────────────────

    private function norm(string $campo, mixed $valor): ?string
    {
        $valor = trim((string) $valor);
        if ($valor === '' || $valor === null) return null;

        $upper = ['IP', 'IK', 'CRI', 'Código_fabrica', 'codigo_fabrica'];
        if (in_array($campo, $upper)) return strtoupper($valor);

        return $valor;
    }

    private function normNum(mixed $valor): ?float
    {
        $v = trim((string) $valor);
        return ($v !== '' && is_numeric($v)) ? (float) $v : null;
    }

    private function normInt(mixed $valor): ?int
    {
        $v = trim((string) $valor);
        return ($v !== '' && is_numeric($v)) ? (int) $v : null;
    }

    private function normBool(mixed $valor): bool
    {
        $v = strtolower(trim((string) $valor));
        return in_array($v, ['1', 'si', 'sí', 'yes', 'true', 'x'], true);
    }

    // ─── Procesar las filas ───────────────────────────────────────────────────

    private function procesarFilas(array $filas): array
    {
        $log    = ['creados' => 0, 'actualizados' => 0, 'variantes' => 0, 'errores' => []];
        $mapaId = [];

        DB::beginTransaction();
        try {
            foreach ($filas as $i => $fila) {
                $linea = $i + 2;
                try {
                    // Validar campo obligatorio
                    $codigoFabrica = $this->norm('Código_fabrica', $fila['Código_fabrica'] ?? '');
                    if (!$codigoFabrica) {
                        $log['errores'][] = "Fila {$linea}: omitida — falta Código_fabrica.";
                        continue;
                    }

                    $nombre = $this->norm('nombre', $fila['nombre_origen'] ?? '');
                    if (!$nombre) {
                        $log['errores'][] = "Fila {$linea}: omitida — falta nombre_origen.";
                        continue;
                    }

                    // Lookups
                    $marcaId = null;
                    if (!empty($fila['Marca'])) {
                        $marca   = Marca::firstOrCreate(
                            ['nombre' => trim($fila['Marca'])],
                            ['estado' => 'activo', 'codigo' => Str::upper(Str::slug(trim($fila['Marca']), ''))]
                        );
                        $marcaId = $marca->id;
                    }

                    $tipoProdId = null;
                    if (!empty($fila['tipo_producto'])) {
                        $tipoProdId = TipoProducto::where('codigo', trim($fila['tipo_producto']))->value('id');
                    }
                    $tipoProdId ??= TipoProducto::first()?->id ?? 1;

                    $tipoLumId = null;
                    if (!empty($fila['tipo_luminaria'])) {
                        $tipoLumId = TipoLuminaria::where('codigo', trim($fila['tipo_luminaria']))->value('id');
                    }

                    $categoria  = Categoria::firstOrCreate(
                        ['nombre' => 'Importados'],
                        ['estado' => 'activo', 'descripcion' => 'Importados desde Excel']
                    );

                    $unidadId = UnidadMedida::where('simbolo', 'und')
                        ->orWhere('nombre', 'like', '%unidad%')
                        ->value('id') ?? 1;

                    // Datos principales del producto
                    $datos = [
                        'nombre'           => $nombre,
                        'nombre_kyrios'    => $this->norm('nombre_kyrios', $fila['nombre_kyrios'] ?? '') ?: null,
                        'codigo_fabrica'   => $codigoFabrica,
                        'categoria_id'     => $categoria->id,
                        'marca_id'         => $marcaId,
                        'unidad_medida_id' => $unidadId,
                        'tipo_producto_id' => $tipoProdId,
                        'tipo_luminaria_id'=> $tipoLumId,
                        'tipo_sistema'     => 'simple',
                        'tipo_inventario'  => 'cantidad',
                        'stock_minimo'     => 0,
                        'stock_maximo'     => 9999,
                        'estado'           => 'activo',
                        'linea'            => $this->norm('linea', $fila['línea'] ?? '') ?: null,
                        'procedencia'      => $this->norm('procedencia', $fila['Procedencia'] ?? '') ?: null,
                        'ficha_tecnica_url'=> $this->norm('ficha_tecnica_url', $fila['Ficha tecnica fabrica'] ?? '') ?: null,
                    ];

                    $producto = Producto::updateOrCreate(
                        ['codigo_fabrica' => $codigoFabrica],
                        $datos + ['codigo' => Producto::generarCodigo(), 'estado_aprobacion' => 'borrador']
                    );

                    if ($producto->wasRecentlyCreated) {
                        $log['creados']++;
                    } else {
                        $log['actualizados']++;
                    }

                    $mapaId[$codigoFabrica] = $producto->id;

                    // Guardar subtablas
                    $this->guardarEspecificaciones($producto, $fila);
                    $this->guardarDimensiones($producto, $fila);
                    $this->guardarMateriales($producto, $fila);
                    $this->guardarEmbalaje($producto, $fila);
                    $this->guardarVariante($producto, $fila, $log);
                    $this->guardarClasificaciones($producto, $fila);

                } catch (\Throwable $e) {
                    $log['errores'][] = "Fila {$linea} ('{$codigoFabrica}'): " . $e->getMessage();
                }
            }

            // BOM: procesar CODIGOS RELACIONADOS en segunda pasada
            foreach ($filas as $i => $fila) {
                $codigoFabrica = $this->norm('Código_fabrica', $fila['Código_fabrica'] ?? '');
                if (!$codigoFabrica || empty($fila['CODIGOS RELACIONADOS'])) continue;

                $padreId = $mapaId[$codigoFabrica]
                    ?? Producto::where('codigo_fabrica', $codigoFabrica)->value('id');
                if (!$padreId) continue;

                $codigos = preg_split('/[\s,;|]+/', trim($fila['CODIGOS RELACIONADOS']));
                foreach (array_filter($codigos) as $codigoHijo) {
                    $codigoHijo = strtoupper(trim($codigoHijo));
                    $hijoId = $mapaId[$codigoHijo]
                        ?? Producto::where('codigo_fabrica', $codigoHijo)->value('id');
                    if (!$hijoId || $hijoId === $padreId) continue;

                    ProductoComponente::firstOrCreate(
                        ['padre_id' => $padreId, 'hijo_id' => $hijoId, 'variante_id' => null],
                        ['cantidad' => 1, 'unidad' => 'unidad', 'es_opcional' => false, 'orden' => 0]
                    );
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $log;
    }

    // ─── Guardar especificaciones técnicas ────────────────────────────────────

    private function guardarEspecificaciones(Producto $producto, array $fila): void
    {
        $data = [
            'tipo_fuente'          => $this->norm('tipo_fuente', $fila['tipo_fuente'] ?? '') ?: null,
            'salida_luz'           => $this->norm('salida_luz', $fila['salida'] ?? '') ?: null,
            'nivel_potencia'       => $this->norm('nivel_potencia', $fila['nivel_potencia_producto'] ?? '') ?: null,
            'socket'               => $this->norm('socket', $fila['socket_producto'] ?? '') ?: null,
            'numero_lamparas'      => $this->normInt($fila['numero_lamparas_producto'] ?? ''),
            'eficacia_luminosa'    => $this->normNum($fila['W_lumenes_producto'] ?? ''),
            'real_lumenes'         => $this->normNum($fila['real_lumenes'] ?? ''),
            'nominal_lumenes'      => $this->normNum($fila['nominal_lumenes'] ?? ''),
            'tonalidad_luz'        => $this->norm('tonalidad_luz', $fila['tonalidad_luz_lumenes'] ?? '') ?: null,
            'temperatura_color'    => $this->norm('temperatura_color', $fila['T° color especifica'] ?? '') ?: null,
            'ip'                   => $this->norm('IP', $fila['IP'] ?? '') ?: null,
            'cri'                  => $this->normInt($fila['CRI'] ?? ''),
            'angulo_apertura'      => $this->norm('angulo_apertura', $fila['Angulo de apertura'] ?? '') ?: null,
            'ik'                   => $this->norm('IK', $fila['IK'] ?? '') ?: null,
            'voltaje'              => $this->norm('voltaje', $fila['Voltaje'] ?? '') ?: null,
            'driver'               => $this->norm('driver', $fila['Driver'] ?? '') ?: null,
            'regulable'            => $this->normBool($fila['Posibilidad de regulación'] ?? ''),
            'protocolo_regulacion' => $this->norm('protocolo_regulacion', $fila['Protocolo de regulación'] ?? '') ?: null,
        ];

        if (array_filter($data)) {
            ProductoEspecificacion::updateOrCreate(
                ['producto_id' => $producto->id],
                $data
            );
        }
    }

    // ─── Guardar dimensiones ──────────────────────────────────────────────────

    private function guardarDimensiones(Producto $producto, array $fila): void
    {
        $diamLado = $this->normNum($fila['diametro_lado'] ?? '');

        $data = [
            'alto'                 => $this->normNum($fila['alto'] ?? ''),
            'ancho'                => $this->normNum($fila['ancho_producto'] ?? ''),
            'diametro'             => $diamLado, // por defecto en diametro
            'alto_suspendido'      => $this->normNum($fila['alto-suspendido'] ?? ''),
            'diametro_agujero'     => $this->normNum($fila['diámetro_lado_producto_agujero'] ?? ''),
            'ancho_agujero'        => $this->normNum($fila['ancho_producto_ajugero'] ?? ''),
            'profundidad_agujero'  => $this->normNum($fila['profundidad_producto_ajugero'] ?? ''),
        ];

        if (array_filter($data)) {
            ProductoDimension::updateOrCreate(
                ['producto_id' => $producto->id],
                $data
            );
        }
    }

    // ─── Guardar materiales ───────────────────────────────────────────────────

    private function guardarMateriales(Producto $producto, array $fila): void
    {
        $data = [
            'material_1'      => $this->norm('material_1', $fila['material_1_producto'] ?? '') ?: null,
            'material_2'      => $this->norm('material_2', $fila['material_2_producto'] ?? '') ?: null,
            'color_acabado_1' => $this->norm('color_acabado_1', $fila['color_acabado'] ?? '') ?: null,
        ];

        if (array_filter($data)) {
            ProductoMaterial::updateOrCreate(
                ['producto_id' => $producto->id],
                $data
            );
        }
    }

    // ─── Guardar embalaje ─────────────────────────────────────────────────────

    private function guardarEmbalaje(Producto $producto, array $fila): void
    {
        $data = [
            'peso'             => $this->normNum($fila['PESO'] ?? ''),
            'volumen'          => $this->normNum($fila['VOLUMEN'] ?? ''),
            'embalado'         => $this->normBool($fila['EMBALADO'] ?? ''),
            'medida_embalaje'  => $this->norm('medida_embalaje', $fila['Medida de embalaje'] ?? '') ?: null,
            'cantidad_por_caja'=> $this->normInt($fila['CANTIDAD POR CAJA'] ?? ''),
        ];

        if (array_filter($data, fn($v) => $v !== null && $v !== false)) {
            ProductoEmbalaje::updateOrCreate(
                ['producto_id' => $producto->id],
                $data
            );
        }
    }

    // ─── Guardar variante del producto ────────────────────────────────────────

    private function guardarVariante(Producto $producto, array $fila, array &$log): void
    {
        $tamano = $this->norm('tamano', $fila['Tamaño'] ?? '') ?: null;
        if (!$tamano) return;

        $variante = ProductoVariante::firstOrCreate(
            ['producto_id' => $producto->id, 'tamano' => $tamano, 'especificacion' => null, 'color_id' => null],
            ['sobreprecio' => 0, 'stock_actual' => 0, 'estado' => 'activo']
        );

        if ($variante->wasRecentlyCreated) {
            $log['variantes']++;
        }
    }

    // ─── Guardar clasificaciones (ambiente → pivot) ───────────────────────────

    private function guardarClasificaciones(Producto $producto, array $fila): void
    {
        $ambiente = $this->norm('ambiente', $fila['ambiente'] ?? '') ?: null;
        if (!$ambiente) return;

        // Buscar o crear clasificación por nombre
        $clf = Clasificacion::firstOrCreate(
            ['nombre' => $ambiente],
            ['codigo' => strtoupper(substr(Str::slug($ambiente, ''), 0, 10)), 'estado' => 'activo']
        );

        $ids = $producto->clasificaciones()->pluck('clasificaciones.id')->toArray();
        if (!in_array($clf->id, $ids)) {
            $producto->clasificaciones()->attach($clf->id);
        }
    }
}
