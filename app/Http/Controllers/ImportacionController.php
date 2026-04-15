<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

use App\Models\Importacion;
use App\Models\Producto;
use App\Models\Luminaria\ProductoClasificacion;
use App\Jobs\ProcesarImportacionJob;

class ImportacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Administrador,Almacenero');
    }

    // ── Formulario de carga ───────────────────────────────────────────────────

    public function index()
    {
        $recientes = Importacion::where('creado_por', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        return view('inventario.importacion.index', compact('recientes'));
    }

    // ── Subir archivo y procesar ──────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        $archivo      = $request->file('archivo');
        $nombreOrigen = $archivo->getClientOriginalName();

        $ruta = $archivo->storeAs(
            'importaciones',
            now()->format('Ymd_His') . '_' . $nombreOrigen
        );

        $importacion = Importacion::create([
            'nombre_archivo' => $nombreOrigen,
            'ruta_archivo'   => Storage::path($ruta),
            'creado_por'     => auth()->id(),
            'estado'         => 'pendiente',
        ]);

        // Con QUEUE_CONNECTION=sync el job corre aquí mismo (sin worker).
        // Con QUEUE_CONNECTION=database se despacha al worker.
        ProcesarImportacionJob::dispatch($importacion->id);

        // Refrescar para devolver estado actualizado (útil con sync)
        $importacion->refresh();

        return response()->json([
            'ok'             => true,
            'importacion_id' => $importacion->id,
            'uuid'           => $importacion->uuid,
            'estado'         => $importacion->estado,
        ]);
    }

    // ── Endpoint de progreso (polling) ────────────────────────────────────────

    public function progreso(Importacion $importacion): JsonResponse
    {
        return response()->json([
            'estado'      => $importacion->estado,
            'total'       => $importacion->total_filas,
            'procesadas'  => $importacion->procesadas,
            'exitosas'    => $importacion->exitosas,
            'fallidas'    => $importacion->fallidas,
            'porcentaje'  => $importacion->porcentaje(),
            'errores'     => $importacion->errores ?? [],
            'finished_at' => $importacion->finished_at?->toDateTimeString(),
        ]);
    }

    // ── Módulo de aprobación ──────────────────────────────────────────────────

    public function aprobacion(Request $request)
    {
        $query = Producto::where('estado_aprobacion', 'borrador')
            ->with([
                'marca', 'tipoProducto', 'tipoLuminaria', 'categoria',
                'especificacion', 'dimensiones', 'embalaje',
                'variantes.color', 'clasificaciones', 'tiposProyecto',
            ])
            ->latest();

        if ($buscar = $request->input('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo_fabrica', 'like', "%{$buscar}%")
                  ->orWhere('nombre_kyrios', 'like', "%{$buscar}%");
            });
        }

        $productos = $query->paginate(50)->withQueryString();

        return view('inventario.importacion.aprobacion', compact('productos'));
    }

    // ── Descargar plantilla Excel ─────────────────────────────────────────────

    public function descargarPlantilla()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Plantilla Importación Productos')
            ->setCreator('Kyrios Luces');

        // Definición de hojas: [nombre, color_tab, cabeceras, fila_ejemplo]
        $hojas = [
            [
                'nombre'   => 'completo_kyrios',
                'color'    => 'F7D600',
                'cabeceras' => [
                    'codigo_fabrica','nombre','nombre_kyrios','tipo_producto_codigo',
                    'tipo_luminaria_codigo','marca_codigo','linea','procedencia',
                    'ficha_tecnica_url','estado','unidad_medida_codigo',
                ],
                'ejemplo' => [
                    '25-0573-N3-B9','Oxygen','Ring','LU','de pie',
                    'LEDS-C4','GROK','España','https://ejemplo.com','activo','UND',
                ],
            ],
            [
                'nombre'   => 'DIMENSIONES',
                'color'    => '4472C4',
                'cabeceras' => [
                    'codigo_fabrica',
                    'alto_mm','ancho_mm','diametro_mm','lado_mm',
                    'profundidad_mm','alto_suspendido_mm',
                    'diametro_agujero_mm','ancho_agujero_mm','profundidad_agujero_mm',
                    'material_1','material_2','material_3',
                ],
                'ejemplo' => [
                    '25-0573-N3-B9','180','','300','','','','135','','','aluminio','','',
                ],
            ],
            [
                'nombre'   => 'EMBALAJE',
                'color'    => 'FF0000',
                'cabeceras' => [
                    'codigo_fabrica','peso_kg','volumen_cm3','medida_embalaje',
                    'cantidad_por_caja','embalado',
                ],
                'ejemplo' => ['25-0573-N3-B9','3.5','22000','62x62x10 cm','4','SI'],
            ],
            [
                'nombre'   => 'COMPONENTES',
                'color'    => 'A9A9A9',
                'cabeceras' => ['codigo_fabrica_padre','codigo_fabrica_hijo','cantidad'],
                'ejemplo'  => ['25-0573-N3-B9','P002','2'],
            ],
            [
                'nombre'   => 'VARIANTES',
                'color'    => '203864',
                'cabeceras' => [
                    'codigo_fabrica',
                    'variante_nombre',
                    'color',
                    'acabado',
                    'tonalidad_luz',
                    'tipo_lampara',
                    'angulo_haz',
                    'protocolo_regulacion',
                    'eficiencia_luminica',
                    'garantia',
                    'vida_util',
                    'ip',
                    'cri',
                    'otros',
                    'sobreprecio',
                    'stock',
                ],
                'ejemplo' => [
                    '25-0573-N3-B9',
                    'LED 3000K Negro',
                    'Negro',
                    'Negro mate',
                    '3000K',
                    'LED',
                    '36°',
                    'DALI',
                    '110 lm/W',
                    '3 años',
                    '50000h',
                    'IP65',
                    '>80',
                    '',
                    '0',
                    '10',
                ],
                'notas' => [
                    'FK al producto base (mismo codigo_fabrica de hoja completo_kyrios)',
                    'Nombre descriptivo libre — se muestra en la UI',
                    'Color de la variante (nombre del catálogo de colores)',
                    'Acabado: Negro mate, Blanco, Cromado, Cepillado…',
                    'Tonalidad de luz: 2700K, 3000K, 4000K, 5000K, 6500K…',
                    'Tipo de lámpara: LED, Halógeno, Fluorescente…',
                    'Ángulo de haz: 15°, 24°, 36°, 60°…',
                    'Protocolo regulación: DALI, 0-10V, Triac, PWM…',
                    'Eficiencia lumínica: 100 lm/W, 120 lm/W…',
                    'Garantía: 2 años, 5 años…',
                    'Vida útil: 25000h, 50000h…',
                    'IP: IP20, IP44, IP65…',
                    'CRI: >80, >90, Ra97…',
                    'Otros diferenciadores adicionales',
                    'Sobreprecio sobre el precio base (0 si igual)',
                    'Stock inicial de esta variante',
                ],
            ],
            [
                'nombre'   => 'ATRIBUTOS_PRODUCTO',
                'color'    => '70AD47',
                'cabeceras' => [
                    'codigo_fabrica','tipo_fuente','nivel_potencia','socket','numero_lamparas',
                    'potencia','voltaje','ip','ik','angulo_apertura','driver','regulable',
                    'protocolo_regulacion','vida_util_horas','nominal_lumenes','real_lumenes',
                    'eficacia_luminosa','temperatura_color','tonalidad_luz','cri',
                ],
                'ejemplo' => [
                    '25-0573-N3-B9','halógena','mediana potencia','G10','4',
                    '76','220-240V','','21','','','Si',
                    'Dimer en la misma luminaria','','','','','','','',
                ],
            ],
            [
                'nombre'    => 'CLASIFICACIONES',
                'color'     => '7030A0',
                'cabeceras' => [
                    'codigo_fabrica',
                    'uso',
                    'tipo_proyecto',
                    'ambiente',
                    'instalacion',
                    'estilo',
                ],
                'ejemplo' => [
                    '25-0573-N3-B9',
                    'interiores, exteriores',
                    'Residencial, Hotelero',
                    'Sala, Cocina, Dormitorio',
                    'plafon, colgante',
                    'Moderno, Minimalista',
                ],
                'notas' => [
                    '',
                    'Valores: interiores | exteriores | alumbrado_publico | piscina  (separar con coma)',
                    'Nombre exacto del tipo de proyecto — múltiples separados por coma',
                    'Nombre del ambiente — múltiples separados por coma (ej: Sala, Cocina, Dormitorio)',
                    'Ver hoja INSTALACION_VALORES — múltiples separados por coma',
                    'Ver hoja ESTILOS_VALORES — múltiples separados por coma',
                ],
            ],
            [
                'nombre'    => 'INSTALACION_VALORES',
                'color'     => 'C9B1D9',
                'cabeceras' => ['valor_instalacion', 'descripcion'],
                'ejemplo'   => ['plafon', 'Plafón'],
                'notas'     => [],
                'extra_rows' => collect(\App\Models\Luminaria\ProductoClasificacion::TIPOS_INSTALACION)
                    ->map(fn($lbl, $val) => [$val, $lbl])->values()->toArray(),
            ],
            [
                'nombre'    => 'ESTILOS_VALORES',
                'color'     => 'E2BCFF',
                'cabeceras' => ['estilo_sugerido'],
                'ejemplo'   => ['Moderno'],
                'notas'     => [],
                'extra_rows' => collect(\App\Models\Luminaria\ProductoClasificacion::ESTILOS_SUGERIDOS)
                    ->map(fn($e) => [$e])->values()->toArray(),
            ],
        ];

        $tiposProyecto = \App\Models\Luminaria\TipoProyecto::orderBy('nombre')->get();

        $spreadsheet->removeSheetByIndex(0); // quitar hoja vacía inicial

        foreach ($hojas as $i => $def) {
            $ws = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $def['nombre']);
            $spreadsheet->addSheet($ws);
            $ws->getTabColor()->setRGB($def['color']);

            foreach ($def['cabeceras'] as $idx => $cab) {
                $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1) . '1';
                $ws->setCellValue($coord, $cab);
            }

            // Estilo cabecera
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($def['cabeceras']));
            $ws->getStyle("A1:{$lastCol}1")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $def['color']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Fila de notas (fila 2) si existen
            $filaEjemplo = 2;
            if (!empty($def['notas'])) {
                foreach ($def['notas'] as $idx => $nota) {
                    if ($nota) {
                        $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1) . '2';
                        $ws->setCellValue($coord, $nota);
                    }
                }
                $ws->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '666666'], 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F5']],
                ]);
                $filaEjemplo = 3;
            }

            // Fila de ejemplo
            foreach ($def['ejemplo'] as $idx => $val) {
                $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1) . $filaEjemplo;
                $ws->setCellValue($coord, $val);
            }

            // Filas extras (catálogos de referencia)
            if (!empty($def['extra_rows'])) {
                $filaExtra = $filaEjemplo + 1;
                foreach ($def['extra_rows'] as $row) {
                    foreach ($row as $idx => $val) {
                        $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1) . $filaExtra;
                        $ws->setCellValue($coord, $val);
                    }
                    $filaExtra++;
                }
            }

            // Autoajustar ancho
            foreach (range(1, count($def['cabeceras'])) as $c) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $ws->getColumnDimension($colLetter)->setAutoSize(true);
            }

            // Congelar primera fila
            $ws->freezePane('A2');
        }

        // ── CLASIFICACIONES_PROYECTO (matriz con X) ──────────────────────────
        $coordFn = fn(int $col) => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);

        $ambientesPorGrupo = [
            'AMBIENTE RESIDENCIAL'           => ['FACHADA','INGRESO/HALL','BAÑO DE VISITA','ESCRITORIO','SALA','COMEDOR','TERRAZA','JARDIN','COCINA','DORMITORIO','SSHH','SALA DE TV','WALKING CLOSET','AREA DE SERVICIO'],
            'AMBIENTE COMERCIAL'             => ['VITRINA','COUNTER','SHOWROOM','MUEBLE VITRINA','EXHIBIDOR','MESA ATENCIÓN','PROBADORES','ESTACIONAMIENTO'],
            'AMBIENTE OFICINA'               => ['RECEPCIÓN','OFICINAS ABIERTAS','OFICINAS CERRADAS','SALA DE REUNIONES','DIRECTORIO','KITCHENETTE','ARCHIVADOR'],
            'AMBIENTE HOTELERO'              => ['LOBBY','BUSINESS CENTER','SUM','CORREDORES','RESTAURANTE','BAR','HABITACIÓN','GIMNASIO','SPA'],
            'AMBIENTE RESTAURANTE'           => ['SALÓN','BUFFET','DEPOSITO','DIRECTORIOS'],
            'AMBIENTE LABORATORIO'           => ['LABORATORIOS'],
            'AMBIENTE CENTRO MÉDICO'         => ['CONSULTORIO','QUIROFANO','SALA DE ESPERA'],
            'AMBIENTE ESTACIÓN DE SERVICIOS' => ['TIENDA','ZONA DE MESAS','ISLAS DE ATENCIÓN'],
            'AMBIENTE PAISAJISMO'            => ['PERÍMETROS','JARDINERA','MACIZOS','ARBOLES Y PLANTAS ALTAS','CERCOS VIVOS/JARDIN VERTICAL','ESPEJO DE AGUA/PILETAS','PÉRGOLA','CAMINOS','JARDINES'],
            'AMBIENTE CLUBES'                => ['ZONA DE JUEGOS','CANCHAS DEPORTIVAS','SALONES SOCIALES','INGRESOS PRIVADOS','PISTA INTERNA','CLUB HOUSE'],
            'AMBIENTE CONDOMINIOS'           => ['CINES','PISCINA'],
            'AMBIENTE URBANO'                => ['ORNAMENTAL','ALAMEDAS','PARQUES','VEREDAS','PÉRGOLAS','SALONES'],
        ];

        $wsCp = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'CLASIFICACIONES_PROYECTO');
        $spreadsheet->addSheet($wsCp);
        $wsCp->getTabColor()->setRGB('C000C0');

        $row1cp   = [];
        $row2cp   = [];
        $gruposCp = [];
        $ambColorsCp = ['006064','00695C','00838F','0277BD','283593','4527A0','5D4037','37474F','1B5E20','4A148C','BF360C','4E342E'];

        $row1cp[] = 'codigo_fabrica';
        $row2cp[] = 'codigo_fabrica';

        $tpNombresCp = $tiposProyecto->pluck('nombre')->toArray();
        if (!empty($tpNombresCp)) {
            $gStart = count($row1cp);
            foreach ($tpNombresCp as $nombre) { $row1cp[] = ''; $row2cp[] = mb_strtoupper($nombre, 'UTF-8'); }
            $gruposCp[] = ['name' => 'TIPO DE PROYECTO', 'start' => $gStart, 'end' => count($row1cp) - 1, 'color' => '1565C0'];
        }

        $ambIdx = 0;
        foreach ($ambientesPorGrupo as $groupName => $ambList) {
            if (empty($ambList)) continue;
            $gStart = count($row1cp);
            foreach ($ambList as $lbl) { $row1cp[] = ''; $row2cp[] = mb_strtoupper($lbl, 'UTF-8'); }
            $gruposCp[] = ['name' => $groupName, 'start' => $gStart, 'end' => count($row1cp) - 1, 'color' => $ambColorsCp[$ambIdx % count($ambColorsCp)]];
            $ambIdx++;
        }

        $gStart = count($row1cp);
        foreach (array_values(\App\Models\Luminaria\ProductoClasificacion::USOS_PRODUCTO) as $lbl) { $row1cp[] = ''; $row2cp[] = mb_strtoupper($lbl, 'UTF-8'); }
        $gruposCp[] = ['name' => 'USO', 'start' => $gStart, 'end' => count($row1cp) - 1, 'color' => '6A1B9A'];

        $gStart = count($row1cp);
        foreach (array_values(\App\Models\Luminaria\ProductoClasificacion::TIPOS_INSTALACION) as $lbl) { $row1cp[] = ''; $row2cp[] = mb_strtoupper($lbl, 'UTF-8'); }
        $gruposCp[] = ['name' => 'TIPO DE INSTALACIÓN', 'start' => $gStart, 'end' => count($row1cp) - 1, 'color' => '2E7D32'];

        $gStart = count($row1cp);
        foreach (\App\Models\Luminaria\ProductoClasificacion::ESTILOS_SUGERIDOS as $est) { $row1cp[] = ''; $row2cp[] = mb_strtoupper($est, 'UTF-8'); }
        $gruposCp[] = ['name' => 'ESTILO', 'start' => $gStart, 'end' => count($row1cp) - 1, 'color' => 'E65100'];

        $nTotalCp = count($row1cp);
        foreach ($gruposCp as $g) { $row1cp[$g['start']] = $g['name']; }

        $wsCp->fromArray([$row1cp], null, 'A1');
        $wsCp->fromArray([$row2cp], null, 'A2');

        foreach ($gruposCp as $g) {
            if ($g['start'] < $g['end']) {
                $wsCp->mergeCells($coordFn($g['start'] + 1) . '1:' . $coordFn($g['end'] + 1) . '1');
            }
        }

        $baseStyleCp = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ];
        $darkFillCp = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2B2E2C']]];
        $wsCp->getStyle('A1')->applyFromArray(array_merge($baseStyleCp, $darkFillCp));
        $wsCp->getStyle('A2')->applyFromArray(array_merge($baseStyleCp, $darkFillCp));
        foreach ($gruposCp as $g) {
            $gs = array_merge($baseStyleCp, ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $g['color']]]]);
            $wsCp->getStyle($coordFn($g['start'] + 1) . '1:' . $coordFn($g['end'] + 1) . '1')->applyFromArray($gs);
            $wsCp->getStyle($coordFn($g['start'] + 1) . '2:' . $coordFn($g['end'] + 1) . '2')->applyFromArray($gs);
        }

        $wsCp->getRowDimension(1)->setRowHeight(22);
        $wsCp->getRowDimension(2)->setRowHeight(65);
        $wsCp->getColumnDimension('A')->setWidth(22);
        for ($c = 2; $c <= $nTotalCp; $c++) { $wsCp->getColumnDimensionByColumn($c)->setWidth(14); }
        $wsCp->freezePane('B3');

        $writer   = new Xlsx($spreadsheet);
        $filename = 'plantilla_importacion_kyrios.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function cancelar(Importacion $importacion)
    {
        if (!in_array($importacion->estado, ['procesando', 'pendiente'])) {
            return back()->with('error', 'Solo se pueden cancelar importaciones en curso.');
        }

        $importacion->update([
            'estado'      => 'fallido',
            'finished_at' => now(),
        ]);

        return back()->with('success', 'Importación cancelada.');
    }

    public function aprobarLote(Request $request)
    {
        $request->validate([
            'ids'      => 'required|array|min:1',
            'ids.*'    => 'integer|exists:productos,id',
            'password' => 'required|string',
        ]);

        // Verificar contraseña maestra
        $masterPassword = env('MASTER_PASSWORD', 'ImportMaster2024');
        if ($request->password !== $masterPassword) {
            return response()->json([
                'ok'      => false,
                'error'   => 'Contraseña incorrecta.',
            ], 422);
        }

        $cantidad = Producto::whereIn('id', $request->ids)
            ->where('estado_aprobacion', 'borrador')
            ->update([
                'estado_aprobacion' => 'aprobado',
                'aprobado_por'      => auth()->id(),
                'aprobado_en'       => now(),
            ]);

        return response()->json([
            'ok'       => true,
            'aprobados' => $cantidad,
        ]);
    }

    public function eliminarLote(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:productos,id',
        ]);

        $eliminados = Producto::whereIn('id', $request->ids)
            ->where('estado_aprobacion', 'borrador')
            ->delete();

        return response()->json([
            'ok'         => true,
            'eliminados' => $eliminados,
        ]);
    }
}
