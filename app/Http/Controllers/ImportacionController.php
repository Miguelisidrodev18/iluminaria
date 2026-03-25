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

        $categorias = \App\Models\Categoria::where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        return view('inventario.importacion.index', compact('recientes', 'categorias'));
    }

    // ── Subir archivo y procesar ──────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'archivo'      => 'required|file|mimes:xlsx,xls|max:20480',
            'categoria_id' => 'required|exists:categorias,id',
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
            'categoria_id'   => $request->input('categoria_id'),
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
            ->with(['marca', 'tipoProducto', 'tipoLuminaria'])
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
                    'codigo_fabrica','salida_mm','alto_mm','ancho_mm','diametro_mm',
                    'lado_mm','profundidad_mm','alto_suspendido_mm','ancho_suspendido_mm',
                    'diametro_agujero_mm','material_1','material_2','material_3',
                ],
                'ejemplo' => [
                    '25-0573-N3-B9','','180','','300','','','','','','aluminio','','',
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
                    'codigo_fabrica','tamano','especificacion','color_id','stock','precio',
                ],
                'ejemplo' => ['25-0573-N3-B9','grande','4000K','gris','0','0'],
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
                'nombre'   => 'CLASIFICACIONES',
                'color'    => '7030A0',
                'cabeceras' => ['codigo_fabrica','uso','ambiente','instalacion','Estilo'],
                'ejemplo'  => ['25-0573-N3-B9','oficina','interior','plafon','moderno'],
            ],
        ];

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

            // Fila de ejemplo
            foreach ($def['ejemplo'] as $idx => $val) {
                $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1) . '2';
                $ws->setCellValue($coord, $val);
            }

            // Autoajustar ancho
            foreach (range(1, count($def['cabeceras'])) as $c) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $ws->getColumnDimension($colLetter)->setAutoSize(true);
            }

            // Congelar primera fila
            $ws->freezePane('A2');
        }

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
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:productos,id',
        ]);

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
}
