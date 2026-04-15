<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use App\Models\Importacion;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\ProductoComponente;
use App\Models\ProductoAtributo;
use App\Models\Categoria;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\Color;
use App\Models\Catalogo\UnidadMedida;
use App\Models\Catalogo\CatalogoAtributo;
use App\Models\Luminaria\TipoProducto;
use App\Models\Luminaria\TipoLuminaria;
use App\Models\Luminaria\ProductoDimension;
use App\Models\Luminaria\ProductoEmbalaje;
use App\Models\Luminaria\ProductoEspecificacion;
use App\Models\Luminaria\ProductoMaterial;
use App\Models\Luminaria\Clasificacion;

/**
 * Importador masivo de productos desde Excel multi-hoja.
 *
 * Acepta nombres de hoja flexibles (alias).
 * Llave de join entre hojas: codigo_fabrica
 */
class ImportadorMasivoService
{
    private const CHUNK_SIZE = 50;

    /**
     * Alias de nombres de hoja aceptados → nombre canónico interno.
     * Permite que el usuario use sus propios nombres sin renombrar el archivo.
     */
    private const SHEET_ALIASES = [
        'PRODUCTOS'          => ['PRODUCTOS', 'completo_kyrios', 'productos', 'PRODUCTO'],
        'ATRIBUTOS'          => ['ATRIBUTOS', 'ATRIBUTOS_PRODUCTO', 'atributos', 'atributos_producto'],
        'DIMENSIONES'        => ['DIMENSIONES', 'dimensiones'],
        'EMBALAJE'           => ['EMBALAJE', 'embalaje'],
        'VARIANTES'          => ['VARIANTES', 'variantes'],
        'COMPONENTES'        => ['COMPONENTES', 'componentes'],
        'CLASIFICACIONES'    => ['CLASIFICACIONES', 'clasificaciones'],
    ];

    /**
     * Mapeo columna Excel → slug de CatalogoAtributo.
     * Incluye los nombres reales que usa el usuario.
     */
    private const ATRIBUTOS_MAP = [
        // Columnas del usuario en ATRIBUTOS_PRODUCTO
        'tipo_fuente'           => 'tipo_fuente',
        'nivel_potencia'        => 'nivel_potencia',
        'socket'                => 'socket',
        'numero_lamparas'       => 'numero_lamparas',
        'potencia'              => 'potencia',
        'voltaje'               => 'voltaje',
        'ip'                    => 'ip',
        'ik'                    => 'ik',
        'angulo_apertura'       => 'angulo_apertura',
        'angulo_apert'          => 'angulo_apertura',   // truncado en Excel
        'angulo'                => 'angulo_apertura',
        'driver'                => 'driver',
        'regulable'             => 'regulable',
        'protocolo_regulacion'  => 'protocolo_regulacion',
        'protocolo'             => 'protocolo_regulacion',
        'vida_util_horas'       => 'vida_util_horas',
        'nominal_lumenes'       => 'nominal_lumenes',
        'real_lumenes'          => 'real_lumenes',
        'eficacia_luminosa'     => 'eficacia_luminosa',
        'temperatura_color'     => 'temperatura_color',
        'temperatura'           => 'temperatura_color',
        'tonalidad_luz'         => 'tonalidad_luz',
        'cri'                   => 'cri',
    ];

    // ── Catálogos en memoria (lookup cache) ───────────────────────────────────
    private array $marcas           = [];
    private array $colores          = [];
    private array $tiposProducto    = [];
    private array $tiposLuminaria   = [];
    private array $atributosSlug    = [];
    private array $clasificaciones  = [];
    private array $tiposProyecto    = [];   // nombre → id
    private array $espaciosProyecto = [];   // tipo_proyecto_id → [nombre_lower → id]
    private int   $unidadDefaultId    = 1;
    private int   $categoriaDefaultId = 1;

    // ── Estado del proceso ────────────────────────────────────────────────────
    private Importacion $importacion;
    private int $procesadas = 0;
    private int $exitosas   = 0;

    // ── Punto de entrada ──────────────────────────────────────────────────────

    public function procesar(Importacion $importacion): void
    {
        $this->importacion = $importacion;
        $this->cargarCatalogos();

        $reader = IOFactory::createReaderForFile($importacion->ruta_archivo);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($importacion->ruta_archivo);

        $total = $this->contarFilas($spreadsheet);
        $importacion->update(['total_filas' => $total, 'started_at' => now()]);

        // Pasada 1: productos base
        $mapa = $this->procesarHojaProductos($spreadsheet);

        // Pasada 2: datos relacionados
        $this->procesarHojaAtributos($spreadsheet, $mapa);
        $this->procesarHojaDimensiones($spreadsheet, $mapa);
        $this->procesarHojaEmbalaje($spreadsheet, $mapa);
        $this->procesarHojaVariantes($spreadsheet, $mapa);
        $this->procesarHojaClasificaciones($spreadsheet, $mapa);

        // Pasada 3: BOM (requiere todos los ids)
        $this->procesarHojaComponentes($spreadsheet, $mapa);
    }

    // ── Resolución flexible de hojas ──────────────────────────────────────────

    private function hoja(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, string $canonical): ?Worksheet
    {
        $nombres = self::SHEET_ALIASES[$canonical] ?? [$canonical];
        foreach ($nombres as $nombre) {
            $sheet = $spreadsheet->getSheetByName($nombre);
            if ($sheet !== null) return $sheet;
        }
        return null;
    }

    // ── Carga de catálogos en memoria ─────────────────────────────────────────

    private function cargarCatalogos(): void
    {
        // Solo cachear marcas que ya tienen código; las sin código caen al path de creación/actualización
        $this->marcas = Marca::whereNotNull('codigo')->where('codigo', '!=', '')->pluck('id', 'nombre')
            ->mapWithKeys(fn($id, $n) => [strtolower(trim($n)) => $id])->toArray();

        $this->colores = Color::pluck('id', 'nombre')
            ->mapWithKeys(fn($id, $n) => [strtolower(trim($n)) => $id])->toArray();

        $this->tiposProducto = TipoProducto::pluck('id', 'codigo')
            ->mapWithKeys(fn($id, $c) => [strtoupper(trim($c)) => $id])->toArray();

        $this->tiposLuminaria = TipoLuminaria::pluck('id', 'codigo')
            ->mapWithKeys(fn($id, $c) => [strtoupper(trim($c)) => $id])->toArray();

        $this->atributosSlug = CatalogoAtributo::pluck('id', 'slug')->toArray();

        $this->clasificaciones = Clasificacion::pluck('id', 'nombre')
            ->mapWithKeys(fn($id, $n) => [strtolower(trim($n)) => $id])->toArray();

        $this->tiposProyecto = \App\Models\Luminaria\TipoProyecto::pluck('id', 'nombre')
            ->mapWithKeys(fn($id, $n) => [strtolower(trim($n)) => $id])->toArray();

        // 2D map: tipo_proyecto_id → [nombre_lower → espacio_id]
        $this->espaciosProyecto = [];
        \App\Models\Luminaria\EspacioProyecto::all()->each(function ($esp) {
            $key = strtolower(trim($esp->nombre));
            // Guardar solo la primera aparición para evitar duplicados de datos
            if (!isset($this->espaciosProyecto[$esp->tipo_proyecto_id][$key])) {
                $this->espaciosProyecto[$esp->tipo_proyecto_id][$key] = $esp->id;
            }
        });

        $this->unidadDefaultId = UnidadMedida::where('abreviatura', 'und')
            ->orWhere('nombre', 'like', '%unidad%')->value('id') ?? 1;

        // Usar la categoría seleccionada en el formulario; fallback a primera categoría activa
        $this->categoriaDefaultId = $this->importacion->categoria_id
            ?? Categoria::where('estado', 'activo')->value('id')
            ?? 1;
    }

    // ── Hoja PRODUCTOS ────────────────────────────────────────────────────────

    private function procesarHojaProductos(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): array
    {
        $sheet = $this->hoja($spreadsheet, 'PRODUCTOS');
        if (!$sheet) {
            $this->registrarError('No se encontró la hoja PRODUCTOS (o sus alias: completo_kyrios).');
            return [];
        }

        $filas = $this->extraerFilas($sheet);
        $mapa  = [];

        foreach (array_chunk($filas, self::CHUNK_SIZE) as $chunk) {
            DB::transaction(function () use ($chunk, &$mapa) {
                foreach ($chunk as $fila) {
                    $codigo = null;
                    try {
                        $codigo = strtoupper($this->norm($fila['codigo_fabrica'] ?? ''));
                        $nombre = $this->norm($fila['nombre'] ?? '');

                        if (!$codigo || !$nombre) {
                            $this->registrarError("PRODUCTOS — omitida: falta codigo_fabrica o nombre.");
                            continue;
                        }

                        $marcaId     = $this->resolverMarca($fila['marca_codigo'] ?? '');
                        $tipoProdId  = $this->tiposProducto[strtoupper($this->norm($fila['tipo_producto_codigo'] ?? '') ?? '')] ?? null;
                        $tipoLumId   = $this->resolverTipoLuminaria($fila['tipo_luminaria_codigo'] ?? '');
                        $unidadId    = $this->resolverUnidadMedida($fila['unidad_medida_codigo'] ?? '');
                        $categoriaId = $this->resolverCategoria($fila['categoria_codigo'] ?? '');

                        $datosProducto = [
                            'nombre'            => $nombre,
                            'nombre_kyrios'     => $this->norm($fila['nombre_kyrios'] ?? '') ?: null,
                            'categoria_id'      => $categoriaId,
                            'marca_id'          => $marcaId,
                            'unidad_medida_id'  => $unidadId,
                            'tipo_producto_id'  => $tipoProdId ?? TipoProducto::first()?->id,
                            'tipo_luminaria_id' => $tipoLumId,
                            'tipo_sistema'      => 'simple',
                            'tipo_inventario'   => 'cantidad',
                            'stock_minimo'      => 0,
                            'stock_maximo'      => 9999,
                            'estado'            => $this->normEstado($fila['estado'] ?? 'activo'),
                            'linea'             => $this->norm($fila['linea'] ?? '') ?: null,
                            'procedencia'       => $this->norm($fila['procedencia'] ?? '') ?: null,
                            'ficha_tecnica_url' => $this->norm($fila['ficha_tecnica_url'] ?? '') ?: null,
                        ];

                        // Separar campos solo-creación para no pisar codigo/estado_aprobacion en updates
                        $producto = Producto::firstOrCreate(
                            ['codigo_fabrica' => $codigo],
                            array_merge($datosProducto, [
                                'codigo'            => Producto::generarCodigo(),
                                'estado_aprobacion' => 'borrador',
                                'creado_por'        => 1,
                            ])
                        );

                        // Si ya existía, actualizar solo los campos de datos
                        if (!$producto->wasRecentlyCreated) {
                            $producto->update($datosProducto);
                        }

                        // Generar/regenerar código Kyrios en cada importación
                        $producto->update([
                            'codigo_kyrios' => $this->generarCodigoKyrios($producto),
                        ]);

                        $mapa[$codigo] = $producto->id;
                        $this->exitosas++;

                    } catch (\Throwable $e) {
                        $this->registrarError("PRODUCTOS — '{$codigo}': " . $e->getMessage());
                    }
                    $this->procesadas++;
                }
            });
            $this->importacion->actualizarProgreso($this->procesadas, $this->exitosas);
        }

        return $mapa;
    }

    // ── Hoja ATRIBUTOS ────────────────────────────────────────────────────────

    /**
     * Guarda los campos de la hoja ATRIBUTOS_PRODUCTO en producto_especificaciones
     * (no en producto_atributos, que es para atributos dinámicos del configurador).
     */
    private function procesarHojaAtributos(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, array $mapa): void
    {
        $sheet = $this->hoja($spreadsheet, 'ATRIBUTOS');
        if (!$sheet) return;

        $filas = $this->extraerFilas($sheet);

        // Slugs que antes se guardaban mal en producto_atributos — limpiarlos
        $slugsEspecificacion = array_unique(array_values(self::ATRIBUTOS_MAP));

        foreach (array_chunk($filas, self::CHUNK_SIZE) as $chunk) {
            DB::transaction(function () use ($chunk, $mapa, $slugsEspecificacion) {
                foreach ($chunk as $fila) {
                    $codigo = null;
                    try {
                        $codigo     = strtoupper($this->norm($fila['codigo_fabrica'] ?? ''));
                        $productoId = $mapa[$codigo] ?? null;
                        if (!$productoId) continue;

                        // Eliminar entradas antiguas mal guardadas en producto_atributos
                        ProductoAtributo::where('producto_id', $productoId)
                            ->whereHas('atributo', fn($q) => $q->whereIn('slug', $slugsEspecificacion))
                            ->delete();

                        // Construir array de especificaciones
                        $specData = [];

                        // Campos string con normalización para dropdowns del formulario
                        $camposString = [
                            'socket', 'potencia', 'voltaje', 'ip', 'ik',
                            'angulo_apertura', 'protocolo_regulacion', 'temperatura_color',
                        ];
                        foreach ($camposString as $campo) {
                            $valor = $this->norm($fila[$campo] ?? '');
                            if ($valor !== null) $specData[$campo] = $valor;
                        }

                        // Campos enum — deben coincidir exactamente con las opciones del form
                        if ($v = $this->norm($fila['tipo_fuente'] ?? ''))
                            $specData['tipo_fuente']    = $this->normTipoFuente($v);
                        if ($v = $this->norm($fila['nivel_potencia'] ?? ''))
                            $specData['nivel_potencia'] = $this->normNivelPotencia($v);
                        if ($v = $this->norm($fila['driver'] ?? ''))
                            $specData['driver']         = $this->normDriver($v);
                        if ($v = $this->norm($fila['tonalidad_luz'] ?? ''))
                            $specData['tonalidad_luz']  = $this->normTonalidad($v);

                        // Campos enteros
                        $camposInt = ['numero_lamparas', 'vida_util_horas', 'cri'];
                        foreach ($camposInt as $campo) {
                            $valor = $this->normInt($fila[$campo] ?? '');
                            if ($valor !== null) $specData[$campo] = $valor;
                        }

                        // Campos decimales
                        $camposDecimal = ['nominal_lumenes', 'real_lumenes', 'eficacia_luminosa'];
                        foreach ($camposDecimal as $campo) {
                            $valor = $this->normNum($fila[$campo] ?? '');
                            if ($valor !== null) $specData[$campo] = $valor;
                        }

                        // Campo booleano
                        if (array_key_exists('regulable', $fila) && $fila['regulable'] !== '') {
                            $specData['regulable'] = $this->normBool($fila['regulable']);
                        }

                        if ($specData) {
                            ProductoEspecificacion::updateOrCreate(
                                ['producto_id' => $productoId],
                                $specData
                            );
                        }

                        $this->exitosas++;
                    } catch (\Throwable $e) {
                        $this->registrarError("ATRIBUTOS — '{$codigo}': " . $e->getMessage());
                    }
                    $this->procesadas++;
                }
            });
            $this->importacion->actualizarProgreso($this->procesadas, $this->exitosas);
        }
    }

    // ── Hoja DIMENSIONES (+ materiales inline) ────────────────────────────────

    private function procesarHojaDimensiones(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, array $mapa): void
    {
        $sheet = $this->hoja($spreadsheet, 'DIMENSIONES');
        if (!$sheet) return;

        $filas = $this->extraerFilas($sheet);

        foreach (array_chunk($filas, self::CHUNK_SIZE) as $chunk) {
            DB::transaction(function () use ($chunk, $mapa) {
                foreach ($chunk as $fila) {
                    try {
                        $codigo     = strtoupper($this->norm($fila['codigo_fabrica'] ?? ''));
                        $productoId = $mapa[$codigo] ?? null;
                        if (!$productoId) continue;

                        // ── Dimensiones ───────────────────────────────────────
                        $dimData = array_filter([
                            'alto'               => $this->normNum($fila['alto_mm'] ?? ''),
                            'ancho'              => $this->normNum($fila['ancho_mm'] ?? ''),
                            'diametro'           => $this->normNum($fila['diametro_mm'] ?? ''),
                            'lado'               => $this->normNum($fila['lado_mm'] ?? ''),
                            'profundidad'        => $this->normNum($fila['profundidad_mm'] ?? ''),
                            'alto_suspendido'    => $this->normNum($fila['alto_suspendido_mm'] ?? ''),
                            'diametro_agujero'   => $this->normNum($fila['diametro_agujero_mm'] ?? ''),
                            'ancho_agujero'      => $this->normNum($fila['ancho_agujero_mm'] ?? ''),
                            'profundidad_agujero'=> $this->normNum($fila['profundidad_agujero_mm'] ?? ''),
                        ], fn($v) => $v !== null);

                        if ($dimData) {
                            ProductoDimension::updateOrCreate(
                                ['producto_id' => $productoId],
                                $dimData
                            );
                        }

                        // ── Materiales (columnas opcionales en esta hoja) ─────
                        $matData = array_filter([
                            'material_1'        => $this->norm($fila['material_1'] ?? '') ?: null,
                            'material_2'        => $this->norm($fila['material_2'] ?? '') ?: null,
                            'material_terciario'=> $this->norm($fila['material_3'] ?? '') ?: null,
                        ], fn($v) => $v !== null);

                        if ($matData) {
                            ProductoMaterial::updateOrCreate(
                                ['producto_id' => $productoId],
                                $matData
                            );
                        }

                        $this->exitosas++;
                    } catch (\Throwable $e) {
                        $this->registrarError("DIMENSIONES — '{$codigo}': " . $e->getMessage());
                    }
                    $this->procesadas++;
                }
            });
            $this->importacion->actualizarProgreso($this->procesadas, $this->exitosas);
        }
    }

    // ── Hoja EMBALAJE ─────────────────────────────────────────────────────────

    private function procesarHojaEmbalaje(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, array $mapa): void
    {
        $sheet = $this->hoja($spreadsheet, 'EMBALAJE');
        if (!$sheet) return;

        $filas = $this->extraerFilas($sheet);

        foreach (array_chunk($filas, self::CHUNK_SIZE) as $chunk) {
            DB::transaction(function () use ($chunk, $mapa) {
                foreach ($chunk as $fila) {
                    try {
                        $codigo     = strtoupper($this->norm($fila['codigo_fabrica'] ?? ''));
                        $productoId = $mapa[$codigo] ?? null;
                        if (!$productoId) continue;

                        // "embalado" acepta: SI/NO, 1/0, true/false, o texto numérico (cantidad)
                        $embaladoRaw = $fila['embalado'] ?? '';
                        $embalado    = $this->normBool($embaladoRaw);

                        $data = [
                            'peso'              => $this->normNum($fila['peso_kg'] ?? ''),
                            'volumen'           => $this->normNum($fila['volumen_cm3'] ?? ''),
                            'embalado'          => $embalado,
                            'medida_embalaje'   => $this->norm($fila['medida_embalaje'] ?? '') ?: null,
                            'cantidad_por_caja' => $this->normInt($fila['cantidad_por_caja'] ?? ''),
                        ];

                        if (array_filter($data, fn($v) => $v !== null && $v !== false)) {
                            ProductoEmbalaje::updateOrCreate(
                                ['producto_id' => $productoId],
                                $data
                            );
                        }

                        $this->exitosas++;
                    } catch (\Throwable $e) {
                        $this->registrarError("EMBALAJE — '{$codigo}': " . $e->getMessage());
                    }
                    $this->procesadas++;
                }
            });
            $this->importacion->actualizarProgreso($this->procesadas, $this->exitosas);
        }
    }

    // ── Hoja VARIANTES ────────────────────────────────────────────────────────

    private function procesarHojaVariantes(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, array $mapa): void
    {
        $sheet = $this->hoja($spreadsheet, 'VARIANTES');
        if (!$sheet) return;

        $filas = $this->extraerFilas($sheet);

        // Claves de atributos luminaria mapeadas entre columna Excel → clave JSON
        $atributosMap = [
            'acabado'               => 'acabado',
            'tonalidad_luz'         => 'tonalidad_luz',
            'tipo_lampara'          => 'tipo_lampara',
            'angulo_haz'            => 'angulo_haz',
            'protocolo_regulacion'  => 'protocolo_regulacion',
            'eficiencia_luminica'   => 'eficiencia_luminica',
            'garantia'              => 'garantia',
            'vida_util'             => 'vida_util',
            'ip'                    => 'ip',
            'cri'                   => 'cri',
            'otros'                 => 'otros',
        ];

        foreach (array_chunk($filas, self::CHUNK_SIZE) as $chunk) {
            DB::transaction(function () use ($chunk, $mapa, $atributosMap) {
                foreach ($chunk as $fila) {
                    try {
                        $codigo     = strtoupper($this->norm($fila['codigo_fabrica'] ?? ''));
                        $productoId = $mapa[$codigo] ?? null;
                        if (!$productoId) continue;

                        // Acepta columna "color", "color_id" o "variante_color" (alias)
                        $colorTexto = $this->norm($fila['color'] ?? $fila['color_id'] ?? $fila['variante_color'] ?? '');
                        $colorId    = $this->resolverColor($colorTexto ?? '');

                        // Nombre descriptivo (nuevo) o legado especificacion
                        $nombre         = $this->norm($fila['variante_nombre'] ?? '') ?: null;
                        $especificacion = $this->norm($fila['especificacion'] ?? '') ?: null;
                        $tamano         = $this->norm($fila['tamano'] ?? '') ?: null;

                        // Recoger atributos luminaria — filtrar vacíos
                        $atributos = [];
                        foreach ($atributosMap as $colExcel => $claveJson) {
                            $val = $this->norm($fila[$colExcel] ?? '');
                            if ($val !== '' && $val !== null) {
                                $atributos[$claveJson] = $val;
                            }
                        }

                        // Buscar variante existente por color + especificacion (o tamano para legado)
                        $variante = ProductoVariante::where('producto_id', $productoId)
                            ->when($colorId, fn($q) => $q->where('color_id', $colorId), fn($q) => $q->whereNull('color_id'))
                            ->when($especificacion, fn($q) => $q->where('especificacion', $especificacion), fn($q) => $q->whereNull('especificacion'))
                            ->first();

                        $atributosExistentes = $variante?->atributos ?? [];
                        $atributosFinal      = array_merge($atributosExistentes, $atributos);

                        if ($variante) {
                            $variante->update([
                                'nombre'       => $nombre ?: $variante->nombre,
                                'atributos'    => !empty($atributosFinal) ? $atributosFinal : null,
                                'stock_actual' => $this->normInt($fila['stock'] ?? '') ?? $variante->stock_actual,
                                'sobreprecio'  => $this->normNum($fila['precio'] ?? $fila['sobreprecio'] ?? '') ?? $variante->sobreprecio,
                                'estado'       => 'activo',
                            ]);
                        } else {
                            $producto = Producto::find($productoId);
                            $colorObj = $colorId ? \App\Models\Catalogo\Color::find($colorId) : null;
                            ProductoVariante::create([
                                'producto_id'    => $productoId,
                                'nombre'         => $nombre,
                                'tamano'         => $tamano,
                                'especificacion' => $especificacion,
                                'color_id'       => $colorId,
                                'atributos'      => !empty($atributosFinal) ? $atributosFinal : null,
                                'sku'            => ProductoVariante::generarSku($producto, $colorObj, $especificacion, $atributosFinal),
                                'stock_actual'   => $this->normInt($fila['stock'] ?? '') ?? 0,
                                'sobreprecio'    => $this->normNum($fila['precio'] ?? $fila['sobreprecio'] ?? '') ?? 0,
                                'estado'         => 'activo',
                                'creado_por'     => auth()->id() ?? 1,
                            ]);
                        }

                        // Marcar el producto como con variantes
                        Producto::where('id', $productoId)->update(['tiene_variantes' => true]);

                        $this->exitosas++;
                    } catch (\Throwable $e) {
                        $this->registrarError("VARIANTES — '{$codigo}': " . $e->getMessage());
                    }
                    $this->procesadas++;
                }
            });
            $this->importacion->actualizarProgreso($this->procesadas, $this->exitosas);
        }
    }

    // ── Hoja CLASIFICACIONES ──────────────────────────────────────────────────

    /**
     * Columnas: codigo_fabrica, uso, tipo_proyecto, ambiente, instalacion, estilo
     *
     * - uso          → clasificacion.usos (interiores|exteriores|alumbrado_publico|piscina)
     * - tipo_proyecto → producto_tipos_proyecto pivot (por nombre)
     * - ambiente     → clasificacion.ambientes (espacio_proyecto_id por nombre)
     * - instalacion  → clasificacion.tipo_instalacion (clave del mapa TIPOS_INSTALACION)
     * - estilo       → clasificacion.estilo (texto libre o sugerido)
     *
     * Múltiples filas para el mismo codigo_fabrica se acumulan (merge).
     */
    private function procesarHojaClasificaciones(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, array $mapa): void
    {
        // Preferir CLASIFICACIONES_PROYECTO (matriz con X) si existe
        if ($spreadsheet->sheetNameExists('CLASIFICACIONES_PROYECTO')) {
            $this->procesarHojaClasificacionesMatriz($spreadsheet, $mapa);
            return;
        }

        $sheet = $this->hoja($spreadsheet, 'CLASIFICACIONES');
        if (!$sheet) return;

        $filas = $this->extraerFilas($sheet);

        // Acumular por producto (múltiples filas = múltiples valores)
        $acumulado = []; // codigo → ['usos'=>[], 'clasificacion_ids'=>[], 'tipo_proyecto_ids'=>[], 'ambientes'=>[], 'instalacion'=>[], 'estilo'=>[]]

        foreach ($filas as $fila) {
            $codigo = strtoupper($this->norm($fila['codigo_fabrica'] ?? ''));
            if (!$codigo || !isset($mapa[$codigo])) continue;

            if (!isset($acumulado[$codigo])) {
                $acumulado[$codigo] = ['usos' => [], 'clasificacion_ids' => [], 'tipo_proyecto_ids' => [], 'ambientes' => [], 'instalacion' => [], 'estilo' => []];
            }

            // Helper: split a cell by comma to support multiple values in one cell
            $splitCelda = fn(string $raw): array => array_filter(
                array_map('trim', explode(',', $raw)),
                fn($v) => $v !== ''
            );

            $usoMapa = [
                'interior'          => 'interior',
                'interiores'        => 'interior',
                'exterior'          => 'exterior',
                'exteriores'        => 'exterior',
                'alumbrado publico' => 'alumbrado_publico',
                'alumbrado_publico' => 'alumbrado_publico',
                'piscina'           => 'piscina',
            ];

            $instalacionMapa = [
                'plafon'                    => 'plafon',
                'plafón'                    => 'plafon',
                'colgante'                  => 'colgante',
                'colgante doble altura'     => 'colgante_doble_altura',
                'aplique'                   => 'aplique',
                'empotrado'                 => 'empotrado_techo',
                'empotrado techo'           => 'empotrado_techo',
                'empotrado de techo'        => 'empotrado_techo',
                'empotrado piso'            => 'empotrado_piso',
                'empotrado de piso'         => 'empotrado_piso',
                'empotrado muro'            => 'empotrado_muro',
                'empotrado sobre muro'      => 'empotrado_muro',
                'sobre mesa'                => 'sobre_mesa',
                'pie'                       => 'pie',
                'escritorio'                => 'escritorio',
                'lectura'                   => 'lectura',
                'ventilador'                => 'ventilador',
                'estacas'                   => 'estacas',
                'balizas'                   => 'balizas',
                'empotrado sumergible'      => 'empotrado_sumergible',
                'portatil'                  => 'portatil',
                'portátil'                  => 'portatil',
                'luminarias portatiles'     => 'portatil',
                'luminarias portátiles'     => 'portatil',
                'proyector'                 => 'proyector',
                'proyectores'               => 'proyector',
                'riel'                      => 'riel',
                'sistema de riel'           => 'riel',
                'tira led'                  => 'tira_led',
                'tiras led'                 => 'tira_led',
                'poste'                     => 'poste',
                'postes'                    => 'poste',
                'luz guia'                  => 'luz_guia',
                'luz guía'                  => 'luz_guia',
            ];

            // uso → usos[] (JSON) + clasificaciones pivot por nombre
            foreach ($splitCelda($this->norm($fila['uso'] ?? '')) as $u) {
                $usoKey = $usoMapa[strtolower($u)] ?? strtolower($u);
                if (!in_array($usoKey, $acumulado[$codigo]['usos'])) {
                    $acumulado[$codigo]['usos'][] = $usoKey;
                }
                // También buscar en la tabla clasificaciones por nombre (ej: "interior" → Clasificacion "Interior")
                if (isset($this->clasificaciones[$usoKey])) {
                    $clfId = $this->clasificaciones[$usoKey];
                    if (!in_array($clfId, $acumulado[$codigo]['clasificacion_ids'])) {
                        $acumulado[$codigo]['clasificacion_ids'][] = $clfId;
                    }
                }
                // Intentar también con el valor original normalizado (ej: "comercial" → Clasificacion "Comercial")
                $usoOrig = strtolower($u);
                if ($usoOrig !== $usoKey && isset($this->clasificaciones[$usoOrig])) {
                    $clfId = $this->clasificaciones[$usoOrig];
                    if (!in_array($clfId, $acumulado[$codigo]['clasificacion_ids'])) {
                        $acumulado[$codigo]['clasificacion_ids'][] = $clfId;
                    }
                }
            }

            // tipo_proyecto → pivot ids — soporta múltiples valores separados por coma
            foreach ($splitCelda($this->norm($fila['tipo_proyecto'] ?? '')) as $tp) {
                $tpKey = strtolower($tp);
                if (isset($this->tiposProyecto[$tpKey])) {
                    $tpId = $this->tiposProyecto[$tpKey];
                    if (!in_array($tpId, $acumulado[$codigo]['tipo_proyecto_ids'])) {
                        $acumulado[$codigo]['tipo_proyecto_ids'][] = $tpId;
                    }
                }
            }

            // ambiente → espacio_proyecto_id — busca bajo cada tipo_proyecto seleccionado en esta fila
            foreach ($splitCelda($this->norm($fila['ambiente'] ?? '')) as $amb) {
                $ambKey = strtolower($amb);
                foreach ($acumulado[$codigo]['tipo_proyecto_ids'] as $tpId) {
                    if (isset($this->espaciosProyecto[$tpId][$ambKey])) {
                        $espId = $this->espaciosProyecto[$tpId][$ambKey];
                        if (!in_array($espId, $acumulado[$codigo]['ambientes'])) {
                            $acumulado[$codigo]['ambientes'][] = $espId;
                        }
                    }
                }
            }

            // instalacion → tipo_instalacion key — soporta múltiples valores separados por coma
            foreach ($splitCelda($this->norm($fila['instalacion'] ?? '')) as $inst) {
                $instKey = $instalacionMapa[strtolower($inst)] ?? strtolower($inst);
                if (!in_array($instKey, $acumulado[$codigo]['instalacion'])) {
                    $acumulado[$codigo]['instalacion'][] = $instKey;
                }
            }

            // estilo → texto libre — soporta múltiples valores separados por coma
            foreach ($splitCelda($this->norm($fila['estilo'] ?? '')) as $est) {
                if (!in_array($est, $acumulado[$codigo]['estilo'])) {
                    $acumulado[$codigo]['estilo'][] = $est;
                }
            }
        }

        // Persistir acumulado
        foreach (array_chunk($acumulado, self::CHUNK_SIZE, true) as $chunk) {
            DB::transaction(function () use ($chunk, $mapa) {
                foreach ($chunk as $codigo => $datos) {
                    try {
                        $productoId = $mapa[$codigo];
                        $producto   = Producto::find($productoId);
                        if (!$producto) continue;

                        // Clasificación: usos, ambientes, instalacion, estilo
                        $producto->clasificacion()->updateOrCreate(
                            ['producto_id' => $producto->id],
                            [
                                'usos'             => $datos['usos'],
                                'ambientes'        => $datos['ambientes'],
                                'tipo_instalacion' => $datos['instalacion'],
                                'estilo'           => $datos['estilo'],
                            ]
                        );

                        // Tipos de proyecto (pivot)
                        if (!empty($datos['tipo_proyecto_ids'])) {
                            $producto->tiposProyecto()->syncWithoutDetaching($datos['tipo_proyecto_ids']);
                        }

                        // Clasificaciones de uso (pivot) — ej: Interior, Exterior, Comercial
                        if (!empty($datos['clasificacion_ids'])) {
                            $producto->clasificaciones()->syncWithoutDetaching($datos['clasificacion_ids']);
                        }

                        $this->exitosas++;
                    } catch (\Throwable $e) {
                        $this->registrarError("CLASIFICACIONES — '{$codigo}': " . $e->getMessage());
                    }
                    $this->procesadas++;
                }
            });
            $this->importacion->actualizarProgreso($this->procesadas, $this->exitosas);
        }
    }

    // ── Hoja CLASIFICACIONES_PROYECTO (formato matriz con X) ─────────────────

    private function procesarHojaClasificacionesMatriz(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, array $mapa): void
    {
        $ws     = $spreadsheet->getSheetByName('CLASIFICACIONES_PROYECTO');
        $isTrue = fn($v) => in_array(strtolower(trim((string) $v)), ['x', '1', 'si', 'sí', 'yes', 'true'], true);

        $numFila  = 0;
        $fila1    = null;   // cabeceras de grupo (fila 1)
        $cabecera = null;   // claves internas (fila 2 traducida)
        $acumulado = [];    // codigo → ['usos', 'clasificacion_ids', 'tipo_proyecto_ids', 'ambientes', 'instalacion', 'estilo']

        foreach ($ws->getRowIterator() as $row) {
            $numFila++;
            $iter = $row->getCellIterator();
            $iter->setIterateOnlyExistingCells(false);

            $valores = [];
            foreach ($iter as $cell) {
                $valores[] = $cell->getValue();
            }
            while (!empty($valores) && ($valores[array_key_last($valores)] === null || $valores[array_key_last($valores)] === '')) {
                array_pop($valores);
            }
            if (empty($valores)) continue;
            $valores = array_map(fn($v) => $v !== null ? trim((string) $v) : '', $valores);

            if ($numFila === 1) { $fila1 = $valores; continue; }
            if ($numFila === 2) {
                $cabecera = $this->traducirCabecerasClasifMatriz($valores, $fila1 ?? []);
                continue;
            }
            if ($cabecera === null || empty(array_filter($valores))) continue;

            $codigo = strtoupper(trim($valores[0] ?? ''));
            if ($codigo === '' || !isset($mapa[$codigo])) continue;

            if (!isset($acumulado[$codigo])) {
                $acumulado[$codigo] = ['usos' => [], 'clasificacion_ids' => [], 'tipo_proyecto_ids' => [], 'ambientes' => [], 'instalacion' => [], 'estilo' => []];
            }

            // Recolectar tipo_proyecto_ids primero (necesarios para resolver ambientes)
            foreach ($cabecera as $i => $clave) {
                if ($i === 0 || !$isTrue($valores[$i] ?? '')) continue;
                if (str_starts_with($clave, '__tp:')) {
                    $tpNombre = substr($clave, 5);
                    if (isset($this->tiposProyecto[$tpNombre])) {
                        $tpId = $this->tiposProyecto[$tpNombre];
                        if (!in_array($tpId, $acumulado[$codigo]['tipo_proyecto_ids'])) {
                            $acumulado[$codigo]['tipo_proyecto_ids'][] = $tpId;
                        }
                    }
                }
            }

            // Procesar resto de columnas
            $usoKeys = ['interior', 'exterior', 'alumbrado_publico', 'piscina'];

            foreach ($cabecera as $i => $clave) {
                if ($i === 0 || !$isTrue($valores[$i] ?? '')) continue;

                if (str_starts_with($clave, '__tp:')) {
                    continue; // ya procesado
                } elseif (str_starts_with($clave, '__amb:')) {
                    $slug = substr($clave, 6);
                    // Buscar bajo los tipo_proyecto_ids ya encontrados; si ninguno, buscar en todos
                    $tpIds = $acumulado[$codigo]['tipo_proyecto_ids'] ?: array_keys($this->espaciosProyecto);
                    foreach ($tpIds as $tpId) {
                        if (isset($this->espaciosProyecto[$tpId][$slug])) {
                            $espId = $this->espaciosProyecto[$tpId][$slug];
                            if (!in_array($espId, $acumulado[$codigo]['ambientes'])) {
                                $acumulado[$codigo]['ambientes'][] = $espId;
                            }
                            break;
                        }
                    }
                } elseif (in_array($clave, $usoKeys)) {
                    if (!in_array($clave, $acumulado[$codigo]['usos'])) {
                        $acumulado[$codigo]['usos'][] = $clave;
                    }
                    if (isset($this->clasificaciones[$clave])) {
                        $clfId = $this->clasificaciones[$clave];
                        if (!in_array($clfId, $acumulado[$codigo]['clasificacion_ids'])) {
                            $acumulado[$codigo]['clasificacion_ids'][] = $clfId;
                        }
                    }
                } elseif ($clave !== '') {
                    // Instalación o estilo
                    $estilosSugeridos = ['Clásico','Clásico-Moderno','Moderno','Contemporáneo','Minimalista','Rústico','Náutico','Vintage','Industrial','Tech','Nórdico','Inglés'];
                    if (in_array($clave, $estilosSugeridos)) {
                        if (!in_array($clave, $acumulado[$codigo]['estilo'])) {
                            $acumulado[$codigo]['estilo'][] = $clave;
                        }
                    } else {
                        if (!in_array($clave, $acumulado[$codigo]['instalacion'])) {
                            $acumulado[$codigo]['instalacion'][] = $clave;
                        }
                    }
                }
            }
        }

        // Persistir — misma lógica que procesarHojaClasificaciones
        foreach (array_chunk($acumulado, self::CHUNK_SIZE, true) as $chunk) {
            DB::transaction(function () use ($chunk, $mapa) {
                foreach ($chunk as $codigo => $datos) {
                    try {
                        $productoId = $mapa[$codigo];
                        $producto   = Producto::find($productoId);
                        if (!$producto) continue;

                        $producto->clasificacion()->updateOrCreate(
                            ['producto_id' => $producto->id],
                            [
                                'usos'             => $datos['usos'],
                                'ambientes'        => $datos['ambientes'],
                                'tipo_instalacion' => $datos['instalacion'],
                                'estilo'           => $datos['estilo'],
                            ]
                        );

                        if (!empty($datos['tipo_proyecto_ids'])) {
                            $producto->tiposProyecto()->syncWithoutDetaching($datos['tipo_proyecto_ids']);
                        }
                        if (!empty($datos['clasificacion_ids'])) {
                            $producto->clasificaciones()->syncWithoutDetaching($datos['clasificacion_ids']);
                        }

                        $this->exitosas++;
                    } catch (\Throwable $e) {
                        $this->registrarError("CLASIFICACIONES_PROYECTO — '{$codigo}': " . $e->getMessage());
                    }
                    $this->procesadas++;
                }
            });
            $this->importacion->actualizarProgreso($this->procesadas, $this->exitosas);
        }
    }

    private function traducirCabecerasClasifMatriz(array $fila2, array $fila1): array
    {
        // Propagar nombre de grupo de izquierda a derecha (celdas fusionadas)
        $groupPerCol  = [];
        $currentGroup = '';
        foreach ($fila1 as $i => $val) {
            if ($val !== '') $currentGroup = strtolower(trim(Str::ascii($val)));
            $groupPerCol[$i] = $currentGroup;
        }

        // tpMap: clave ascii+lowercase → __tp:nombre_original
        $tpMap = [];
        foreach ($this->tiposProyecto as $nombre => $id) {
            $tpMap[strtolower(trim(Str::ascii($nombre)))] = '__tp:' . $nombre;
        }

        // Claves en ASCII puro (sin tildes) — el label ya viene normalizado con Str::ascii()
        $staticMap = [
            'interiores'             => 'interior',
            'exteriores'             => 'exterior',
            'alumbrado publico'      => 'alumbrado_publico',
            'piscina'                => 'piscina',
            'colgante'               => 'colgante',
            'colgante doble altura'  => 'colgante_doble_altura',
            'plafon'                 => 'plafon',
            'aplique'                => 'aplique',
            'sobre mesa'             => 'sobre_mesa',
            'pie'                    => 'pie',
            'escritorio'             => 'escritorio',
            'lectura'                => 'lectura',
            'empotrado de techo'     => 'empotrado_techo',
            'empotrado de piso'      => 'empotrado_piso',
            'empotrado sobre muro'   => 'empotrado_muro',
            'ventilador'             => 'ventilador',
            'estacas'                => 'estacas',
            'balizas'                => 'balizas',
            'empotrado sumergible'   => 'empotrado_sumergible',
            'empotrados sumergibles' => 'empotrado_sumergible',
            'luminarias portatiles'  => 'portatil',
            'proyectores'            => 'proyector',
            'sistema de riel'        => 'riel',
            'tiras led'              => 'tira_led',
            'postes'                 => 'poste',
            'luz guia'               => 'luz_guia',
            'clasico'                => 'Clásico',
            'clasico-moderno'        => 'Clásico-Moderno',
            'moderno'                => 'Moderno',
            'contemporaneo'          => 'Contemporáneo',
            'minimalista'            => 'Minimalista',
            'rustico'                => 'Rústico',
            'nautico'                => 'Náutico',
            'vintage'                => 'Vintage',
            'industrial'             => 'Industrial',
            'tech'                   => 'Tech',
            'nordico'                => 'Nórdico',
            'ingles'                 => 'Inglés',
        ];

        $result = [];
        foreach ($fila2 as $i => $label) {
            if ($i === 0) { $result[] = 'codigo_fabrica'; continue; }

            $labelNorm = strtolower(trim(Str::ascii($label)));
            $group     = strtolower(Str::ascii($groupPerCol[$i] ?? ''));

            if (str_contains($group, 'ambiente')) {
                $result[] = '__amb:' . Str::slug($label, '_');
            } elseif (str_contains($group, 'tipo de proyecto') || isset($tpMap[$labelNorm])) {
                $result[] = $tpMap[$labelNorm] ?? '__tp:' . $labelNorm;
            } else {
                $result[] = $staticMap[$labelNorm] ?? $labelNorm;
            }
        }

        return $result;
    }

    // ── Hoja COMPONENTES (BOM) ────────────────────────────────────────────────

    private function procesarHojaComponentes(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, array $mapa): void
    {
        $sheet = $this->hoja($spreadsheet, 'COMPONENTES');
        if (!$sheet) return;

        $filas = $this->extraerFilas($sheet);

        foreach (array_chunk($filas, self::CHUNK_SIZE) as $chunk) {
            DB::transaction(function () use ($chunk, $mapa) {
                foreach ($chunk as $fila) {
                    try {
                        $codigoPadre = strtoupper($this->norm($fila['codigo_fabrica_padre'] ?? ''));
                        $codigoHijo  = strtoupper($this->norm($fila['codigo_fabrica_hijo'] ?? ''));

                        $padreId = $mapa[$codigoPadre]
                            ?? Producto::where('codigo_fabrica', $codigoPadre)->value('id');
                        $hijoId  = $mapa[$codigoHijo]
                            ?? Producto::where('codigo_fabrica', $codigoHijo)->value('id');

                        if (!$padreId || !$hijoId || $padreId === $hijoId) continue;

                        ProductoComponente::updateOrCreate(
                            ['padre_id' => $padreId, 'hijo_id' => $hijoId, 'variante_id' => null],
                            [
                                'cantidad'    => $this->normNum($fila['cantidad'] ?? '') ?? 1,
                                'unidad'      => 'unidad',
                                'es_opcional' => false,
                                'orden'       => 0,
                            ]
                        );

                        $this->exitosas++;
                    } catch (\Throwable $e) {
                        $this->registrarError("COMPONENTES: " . $e->getMessage());
                    }
                    $this->procesadas++;
                }
            });
            $this->importacion->actualizarProgreso($this->procesadas, $this->exitosas);
        }
    }

    // ── Extraer filas de una hoja ─────────────────────────────────────────────

    private function extraerFilas(Worksheet $sheet): array
    {
        $filas    = [];
        $cabecera = null;

        foreach ($sheet->getRowIterator() as $row) {
            $celdas = [];
            foreach ($row->getCellIterator() as $celda) {
                $celdas[] = trim((string) $celda->getValue());
            }

            if (empty(array_filter($celdas, fn($v) => $v !== ''))) continue;

            if ($cabecera === null) {
                // Normalizar cabecera: minúsculas y trim
                $cabecera = array_map(fn($c) => strtolower(trim($c)), $celdas);
                continue;
            }

            $filas[] = array_combine(
                $cabecera,
                array_pad(array_map('trim', $celdas), count($cabecera), '')
            );
        }

        return $filas;
    }

    // ── Contar filas totales ──────────────────────────────────────────────────

    private function contarFilas(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): int
    {
        $total = 0;
        foreach (array_keys(self::SHEET_ALIASES) as $canonical) {
            $sheet = $this->hoja($spreadsheet, $canonical);
            if ($sheet) {
                $total += max(0, $sheet->getHighestDataRow() - 1);
            }
        }
        return $total;
    }

    // ── Resolvers de lookup ───────────────────────────────────────────────────

    private function resolverMarca(string $valor): ?int
    {
        $valor = trim($valor);
        if (!$valor) return null;

        $key = strtolower($valor);
        if (isset($this->marcas[$key])) return $this->marcas[$key];

        // Auto-generar código de 2-3 chars: iniciales de palabras o primeras letras
        $codigoMarca = $this->generarCodigoMarca($valor);

        $marca = Marca::firstOrCreate(
            ['nombre' => $valor],
            ['estado' => 'activo', 'codigo' => $codigoMarca]
        );

        // Si ya existía sin código, asignarlo
        if (!$marca->codigo) {
            $marca->update(['codigo' => $codigoMarca]);
        }

        // Vincular la marca a la categoría por defecto para que aparezca en el selector
        DB::table('categoria_marca')->insertOrIgnore([
            'categoria_id' => $this->categoriaDefaultId,
            'marca_id'     => $marca->id,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return $this->marcas[$key] = $marca->id;
    }

    /**
     * Genera un código corto de 2-3 chars para una marca.
     * Toma las iniciales de cada palabra, o las primeras 2 letras si es una sola palabra.
     * Garantiza que no colisione con códigos existentes.
     */
    private function generarCodigoMarca(string $nombre): string
    {
        // Limpiar caracteres especiales y separar palabras
        $palabras = preg_split('/[\s\-_\.]+/', preg_replace('/[^a-zA-Z0-9\s\-_\.]/', '', $nombre));
        $palabras = array_filter($palabras);

        if (count($palabras) >= 2) {
            // Iniciales de las primeras 2-3 palabras
            $base = strtoupper(implode('', array_map(fn($p) => substr($p, 0, 1), array_slice($palabras, 0, 3))));
        } else {
            // Una sola palabra: primeras 2-3 letras
            $base = strtoupper(substr(reset($palabras), 0, 3));
        }
        $base = substr($base, 0, 3) ?: 'MK';

        // Garantizar unicidad
        $codigo = $base;
        $sufijo = 2;
        while (Marca::where('codigo', $codigo)->exists()) {
            $codigo = $base . $sufijo;
            $sufijo++;
        }
        return $codigo;
    }

    /**
     * Genera el código Kyrios para un producto: KY-[TP][TL][M]-[NNNN]
     * Replica la lógica de ProductoController::generarCodigoKyrios()
     */
    private function generarCodigoKyrios(Producto $producto): string
    {
        $tipoProducto = \App\Models\Luminaria\TipoProducto::find($producto->tipo_producto_id);
        $segTP = $tipoProducto ? strtoupper(substr($tipoProducto->codigo ?? 'XX', 0, 2)) : 'XX';

        $segTL = '00';
        if ($producto->tipo_luminaria_id) {
            $tipoLuminaria = \App\Models\Luminaria\TipoLuminaria::find($producto->tipo_luminaria_id);
            if ($tipoLuminaria?->codigo) {
                $segTL = strtoupper(substr($tipoLuminaria->codigo, 0, 2));
            }
        }

        $segM = 'XX';
        if ($producto->marca_id) {
            $marca = Marca::find($producto->marca_id);
            if ($marca?->codigo) {
                $segM = strtoupper(substr($marca->codigo, 0, 2));
            }
        }

        $prefijo = "KY-{$segTP}{$segTL}{$segM}";

        $correlativo = Producto::where('tipo_producto_id', $producto->tipo_producto_id)
            ->where('tipo_luminaria_id', $producto->tipo_luminaria_id ?: null)
            ->where('marca_id', $producto->marca_id ?: null)
            ->where('id', '!=', $producto->id)
            ->whereNotNull('codigo_kyrios')
            ->count() + 1;

        $codigo = $prefijo . '-' . str_pad($correlativo, 4, '0', STR_PAD_LEFT);

        while (Producto::where('codigo_kyrios', $codigo)->where('id', '!=', $producto->id)->exists()) {
            $correlativo++;
            $codigo = $prefijo . '-' . str_pad($correlativo, 4, '0', STR_PAD_LEFT);
        }

        return $codigo;
    }

    private function resolverColor(string $valor): ?int
    {
        $valor = trim($valor);
        if (!$valor) return null;

        $key = strtolower($valor);
        if (isset($this->colores[$key])) return $this->colores[$key];

        // Búsqueda parcial
        $id = Color::whereRaw('LOWER(nombre) LIKE ?', ["%{$key}%"])->value('id');
        if ($id) return $this->colores[$key] = $id;

        $color = Color::create(['nombre' => ucfirst($valor), 'estado' => 'activo']);
        return $this->colores[$key] = $color->id;
    }

    private function resolverUnidadMedida(string $valor): int
    {
        $valor = trim($valor);
        if (!$valor) return $this->unidadDefaultId;

        $id = UnidadMedida::where('abreviatura', strtolower($valor))
            ->orWhere('abreviatura', strtoupper($valor))
            ->orWhere('nombre', 'like', "%{$valor}%")
            ->value('id');

        return $id ?? $this->unidadDefaultId;
    }

    private function resolverCategoria(string $valor): int
    {
        $valor = trim($valor);
        if (!$valor) return $this->categoriaDefaultId;

        $id = Categoria::where('nombre', $valor)->value('id');
        if ($id) return $id;

        return Categoria::firstOrCreate(
            ['nombre' => $valor],
            ['estado' => 'activo', 'codigo' => strtoupper(substr(Str::slug($valor, ''), 0, 20))]
        )->id;
    }

    private function resolverAtributo(string $slug): int
    {
        if (isset($this->atributosSlug[$slug])) return $this->atributosSlug[$slug];

        $atributo = CatalogoAtributo::firstOrCreate(
            ['slug' => $slug],
            [
                'nombre' => ucwords(str_replace('_', ' ', $slug)),
                'tipo'   => 'text',
                'grupo'  => 'tecnico',
                'activo' => true,
            ]
        );

        return $this->atributosSlug[$slug] = $atributo->id;
    }

    private function resolverTipoLuminaria(string $valor): ?int
    {
        $valor = trim($valor);
        if (!$valor) return null;

        $key = strtoupper($valor);

        // Cache hit (keyed by codigo uppercase)
        if (isset($this->tiposLuminaria[$key])) return $this->tiposLuminaria[$key];

        // Try match by nombre (case-insensitive)
        $id = TipoLuminaria::whereRaw('LOWER(nombre) = ?', [strtolower($valor)])->value('id');
        if ($id) return $this->tiposLuminaria[$key] = $id;

        // Create new
        $codigoBase = strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $valor), 0, 10));
        $codigo = $codigoBase;
        $n = 1;
        while (TipoLuminaria::where('codigo', $codigo)->exists()) {
            $codigo = substr($codigoBase, 0, 8) . $n++;
        }

        $tl = TipoLuminaria::create(['nombre' => $valor, 'codigo' => $codigo, 'activo' => true]);
        return $this->tiposLuminaria[$key] = $tl->id;
    }

    private function resolverClasificacion(string $valor): int
    {
        $key = strtolower($valor);
        if (isset($this->clasificaciones[$key])) return $this->clasificaciones[$key];

        // codigo es varchar(3) — tomar primeras 3 letras y hacerlo único
        $codigoBase = strtoupper(substr(preg_replace('/[^A-Z]/i', '', $valor), 0, 3));
        $codigo = $codigoBase;
        $n = 1;
        while (Clasificacion::where('codigo', $codigo)->exists()) {
            $codigo = substr($codigoBase, 0, 2) . $n;
            $n++;
        }

        $clf = Clasificacion::firstOrCreate(
            ['nombre' => $valor],
            ['codigo' => $codigo, 'activo' => true]
        );

        return $this->clasificaciones[$key] = $clf->id;
    }

    // ── Normalización de valores ──────────────────────────────────────────────

    private function norm(mixed $valor): ?string
    {
        $v = trim((string) $valor);
        return ($v !== '') ? $v : null;
    }

    private function normNum(mixed $valor): ?float
    {
        $v = trim((string) $valor);
        // Limpiar unidades como "mm", "kg", " unidades" etc.
        $v = preg_replace('/[^0-9.\-]/', '', $v);
        return ($v !== '' && is_numeric($v)) ? (float) $v : null;
    }

    private function normInt(mixed $valor): ?int
    {
        $v = trim((string) $valor);
        $v = preg_replace('/[^0-9\-]/', '', $v);
        return ($v !== '' && is_numeric($v)) ? (int) $v : null;
    }

    private function normBool(mixed $valor): bool
    {
        $v = strtolower(trim((string) $valor));
        return in_array($v, ['1', 'si', 'sí', 'yes', 'true', 'x'], true);
    }

    private function normTipoFuente(string $v): string
    {
        $v = strtolower(trim($v));
        if (str_contains($v, 'led'))         return 'LED';
        if (str_contains($v, 'fluorescente')) return 'Fluorescente';
        if (str_contains($v, 'hal'))         return 'Halógena';
        if ($v === 'hid')                    return 'HID';
        if (str_contains($v, 'incand'))      return 'Incandescente';
        if (str_contains($v, 'fibra'))       return 'Fibra óptica';
        // Devolver el valor original capitalizado si no coincide
        return ucfirst($v);
    }

    private function normNivelPotencia(string $v): string
    {
        $v = strtolower(trim($v));
        if (str_contains($v, 'baja') || str_contains($v, 'low'))    return 'Baja (0–10W)';
        if (str_contains($v, 'alta') || str_contains($v, 'high'))   return 'Alta (31W+)';
        if (str_contains($v, 'medi') || str_contains($v, 'med'))    return 'Media (11–30W)';
        return ucfirst($v);
    }

    private function normDriver(string $v): string
    {
        $v = strtolower(trim($v));
        if (str_contains($v, 'meanwell') || str_contains($v, 'externo')) return 'externo_meanwell';
        if (str_contains($v, 'no') || str_contains($v, 'sin'))            return 'no_incluido';
        if (str_contains($v, 'incluid') || str_contains($v, 'si') || str_contains($v, 'con')) return 'incluido';
        return $v;
    }

    private function normTonalidad(string $v): string
    {
        $v = strtolower(trim($v));
        if (str_contains($v, 'cal') || str_contains($v, 'warm')) return 'Cálido';
        if (str_contains($v, 'neu') || str_contains($v, 'neut')) return 'Neutro';
        if (str_contains($v, 'fr')  || str_contains($v, 'cool') || str_contains($v, 'cold')) return 'Frío';
        if (str_contains($v, 'bic'))                              return 'Bicolor';
        if (str_contains($v, 'multi') || str_contains($v, 'rgb')) return 'Multicolor';
        return ucfirst($v);
    }

    private function normEstado(mixed $valor): string
    {
        $v = strtolower(trim((string) $valor));
        return in_array($v, ['activo', 'inactivo', 'descontinuado']) ? $v : 'activo';
    }

    // ── Registro de errores ───────────────────────────────────────────────────

    private function registrarError(string $mensaje): void
    {
        $this->importacion->agregarError($mensaje);
    }
}
