<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\ProductoComponente;
use App\Models\Categoria;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\Color;
use App\Models\Catalogo\UnidadMedida;
use App\Models\Luminaria\TipoProducto;
use App\Models\Luminaria\TipoLuminaria;
use App\Models\Luminaria\TipoProyecto;
use App\Models\Luminaria\ProductoClasificacion;
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

    // ─── Descargar plantilla Excel multi-hoja ────────────────────────────────

    public function descargarPlantilla()
    {
        // Cargar catálogos reales de la BD
        $tiposProducto  = TipoProducto::orderBy('nombre')->get();
        $tiposLuminaria = TipoLuminaria::orderBy('nombre')->get();
        $marcas         = Marca::where('estado', 'activo')->orderBy('nombre')->get();
        $unidades       = UnidadMedida::orderBy('simbolo')->get();
        $tiposProyecto  = \App\Models\Luminaria\TipoProyecto::orderBy('nombre')->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);
        $spreadsheet->removeSheetByIndex(0);

        $styleHeader = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2B2E2C']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ];
        $styleRef = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '555E57']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $styleExample = [
            'fill'    => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7F7E8']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ];
        $styleNote = [
            'font' => ['italic' => true, 'color' => ['rgb' => '888888']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFDE7']],
        ];

        // ── HELPER: aplicar estilo a rango ────────────────────────────────────
        $applyStyle = function (Worksheet $ws, string $range, array $style) {
            $ws->getStyle($range)->applyFromArray($style);
        };
        $autoWidth = function (Worksheet $ws, array $cols) {
            foreach ($cols as $col) {
                $ws->getColumnDimension($col)->setAutoSize(true);
            }
        };

        // ── 1. completo_kyrios ────────────────────────────────────────────────
        $ws = new Worksheet($spreadsheet, 'completo_kyrios');
        $spreadsheet->addSheet($ws);
        $cabecera = [
            'codigo_fabrica', 'nombre', 'nombre_kyrios',
            'tipo_producto_codigo', 'tipo_luminaria_codigo', 'marca_codigo',
            'linea', 'procedencia', 'ficha_tecnica_url', 'estado', 'unidad_medida_codigo',
        ];
        $ws->fromArray([$cabecera], null, 'A1');
        $applyStyle($ws, 'A1:K1', $styleHeader);
        // Fila de instrucciones
        $ws->fromArray([[
            '↑ código único del fabricante',
            'nombre completo del producto',
            'nombre interno Kyrios (opcional)',
            'ver hoja TIPOS_PRODUCTO',
            'ver hoja TIPOS_LUMINARIA',
            'ver hoja MARCAS',
            'línea comercial (ej: Premium)',
            'ej: China, Perú',
            'URL ficha técnica (opcional)',
            'activo | inactivo',
            'ver hoja UNIDADES',
        ]], null, 'A2');
        $applyStyle($ws, 'A2:K2', $styleNote);
        // Ejemplo
        $ws->fromArray([[
            'DL-8W-001', 'Downlight LED Slim 8W', 'DL-KYRIOS-001',
            $tiposProducto->first()?->codigo ?? 'LU',
            $tiposLuminaria->first()?->codigo ?? 'ET',
            $marcas->first()?->codigo ?? 'KYRIOS',
            'Premium', 'China', '', 'activo', 'und',
        ]], null, 'A3');
        $applyStyle($ws, 'A3:K3', $styleExample);
        $autoWidth($ws, ['A','B','C','D','E','F','G','H','I','J','K']);
        $ws->getRowDimension(1)->setRowHeight(20);

        // ── 2. ATRIBUTOS_PRODUCTO ────────────────────────────────────────────
        $ws = new Worksheet($spreadsheet, 'ATRIBUTOS_PRODUCTO');
        $spreadsheet->addSheet($ws);
        $cols = [
            'codigo_fabrica',
            'tipo_fuente', 'nivel_potencia', 'socket', 'numero_lamparas',
            'potencia', 'voltaje', 'ip', 'ik', 'angulo_apertura',
            'driver', 'regulable', 'protocolo_regulacion',
            'vida_util_horas', 'nominal_lumenes', 'real_lumenes',
            'eficacia_luminosa', 'temperatura_color', 'tonalidad_luz', 'cri',
        ];
        $ws->fromArray([$cols], null, 'A1');
        $applyStyle($ws, 'A1:T1', $styleHeader);
        $ws->fromArray([[
            'igual que en completo_kyrios',
            'LED | Fluorescente | Halógena | Incandescente',
            'Baja (0-10W) | Media (10-30W) | Alta (>30W)',
            'GU10 | E27 | E14 | G13 | G9 | integrado',
            '1',
            '8W', '220V', 'IP20–IP68', 'IK08', '36°',
            'Integrado | Externo',
            'SI | NO',
            'DALI | 0-10V | Triac | PWM',
            '50000', '800', '780',
            '100', '3000K', 'Cálido | Neutro | Frío', '80',
        ]], null, 'A2');
        $applyStyle($ws, 'A2:T2', $styleNote);
        $ws->fromArray([[
            'DL-8W-001',
            'LED', 'Baja (0-10W)', 'GU10', '1',
            '8W', '220V', 'IP65', 'IK08', '36°',
            'Integrado', 'SI', 'DALI',
            '50000', '800', '780',
            '100', '3000K', 'Cálido', '80',
        ]], null, 'A3');
        $applyStyle($ws, 'A3:T3', $styleExample);
        foreach (range('A', 'T') as $c) $ws->getColumnDimension($c)->setAutoSize(true);

        // ── 3. DIMENSIONES ───────────────────────────────────────────────────
        $ws = new Worksheet($spreadsheet, 'DIMENSIONES');
        $spreadsheet->addSheet($ws);
        $cols = [
            'codigo_fabrica',
            'alto_mm', 'ancho_mm', 'diametro_mm', 'lado_mm',
            'profundidad_mm', 'alto_suspendido_mm',
            'diametro_agujero_mm', 'ancho_agujero_mm', 'profundidad_agujero_mm',
            'material_1', 'material_2', 'material_3',
        ];
        $ws->fromArray([$cols], null, 'A1');
        $applyStyle($ws, 'A1:M1', $styleHeader);
        $ws->fromArray([[
            'igual que en completo_kyrios',
            'altura cuerpo (mm)', 'ancho (mm)', 'diámetro (mm)', 'lado si es cuadrado (mm)',
            'profundidad (mm)', 'alto cable/suspensión (mm)',
            'diámetro agujero instalación (mm)', 'ancho agujero (mm)', 'prof. agujero (mm)',
            'ej: Aluminio', 'ej: PC | Acrílico', 'ej: Acero',
        ]], null, 'A2');
        $applyStyle($ws, 'A2:M2', $styleNote);
        $ws->fromArray([['DL-8W-001','45','','145','','','','135','','','Aluminio','PC','']], null, 'A3');
        $applyStyle($ws, 'A3:M3', $styleExample);
        foreach (range('A', 'M') as $c) $ws->getColumnDimension($c)->setAutoSize(true);

        // ── 4. EMBALAJE ──────────────────────────────────────────────────────
        $ws = new Worksheet($spreadsheet, 'EMBALAJE');
        $spreadsheet->addSheet($ws);
        $ws->fromArray([['codigo_fabrica','peso_kg','volumen_cm3','medida_embalaje','cantidad_por_caja','embalado']], null, 'A1');
        $applyStyle($ws, 'A1:F1', $styleHeader);
        $ws->fromArray([['igual que en completo_kyrios','peso en kg (ej: 0.250)','volumen cm³ (ej: 500)','ej: 20x20x10 cm','unidades por caja','SI | NO']], null, 'A2');
        $applyStyle($ws, 'A2:F2', $styleNote);
        $ws->fromArray([['DL-8W-001','0.250','500','20x20x10 cm','12','SI']], null, 'A3');
        $applyStyle($ws, 'A3:F3', $styleExample);
        foreach (['A','B','C','D','E','F'] as $c) $ws->getColumnDimension($c)->setAutoSize(true);

        // ── 5. VARIANTES ─────────────────────────────────────────────────────
        $ws = new Worksheet($spreadsheet, 'VARIANTES');
        $spreadsheet->addSheet($ws);
        $cols = [
            'codigo_fabrica','variante_nombre','color','acabado',
            'tonalidad_luz','tipo_lampara','angulo_haz',
            'protocolo_regulacion','eficiencia_luminica',
            'garantia','vida_util','ip','cri','otros',
            'sobreprecio','stock',
        ];
        $ws->fromArray([$cols], null, 'A1');
        $applyStyle($ws, 'A1:P1', $styleHeader);
        $ws->fromArray([[
            'igual que completo_kyrios','nombre de la variante','nombre del color','acabado superficial',
            'ej: 3000K','ej: LED','ej: 36°',
            'DALI | 0-10V | Triac','ej: 110 lm/W',
            'ej: 3 años','ej: 50000h','ej: IP65','>80','info extra',
            'sobreprecio en S/ (0 si no aplica)','stock inicial',
        ]], null, 'A2');
        $applyStyle($ws, 'A2:P2', $styleNote);
        $ws->fromArray([['DL-8W-001','LED 3000K Negro','Negro','Negro mate','3000K','LED','36°','DALI','110 lm/W','3 años','50000h','IP65','>80','','0','10']], null, 'A3');
        $ws->fromArray([['DL-8W-001','LED 4000K Blanco','Blanco','Blanco','4000K','LED','36°','0-10V','115 lm/W','3 años','50000h','IP65','>80','','5','5']], null, 'A4');
        $applyStyle($ws, 'A3:P4', $styleExample);
        foreach (range('A', 'P') as $c) $ws->getColumnDimension($c)->setAutoSize(true);

        // ── 6. CLASIFICACIONES (formato matriz con X) ────────────────────────
        $ws = new Worksheet($spreadsheet, 'CLASIFICACIONES');
        $spreadsheet->addSheet($ws);

        $usoKeys    = array_keys(ProductoClasificacion::USOS_PRODUCTO);
        $usoLabels  = array_values(ProductoClasificacion::USOS_PRODUCTO);
        $instKeys   = array_keys(ProductoClasificacion::TIPOS_INSTALACION);
        $instLabels = array_values(ProductoClasificacion::TIPOS_INSTALACION);
        $estilosList= ProductoClasificacion::ESTILOS_SUGERIDOS;

        // Columnas: [codigo_fabrica, ambiente, tipo_proyecto] + usos + instalaciones + estilos
        $fixedCols = ['codigo_fabrica', 'ambiente', 'tipo_proyecto'];
        $nFijo     = count($fixedCols);
        $nUso      = count($usoKeys);
        $nInst     = count($instKeys);
        $nEst      = count($estilosList);
        $allColKeys = array_merge($fixedCols, $usoKeys, $instKeys, $estilosList);
        $nTotal     = count($allColKeys);

        $coord = fn(int $col) => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);

        // ── Fila 1: cabeceras de grupo (visual) ───
        $row1 = array_fill(0, $nTotal, '');
        $row1[0] = 'codigo_fabrica';
        $row1[1] = 'ambiente';
        $row1[2] = 'tipo_proyecto';
        $row1[$nFijo]            = 'USO';
        $row1[$nFijo + $nUso]    = 'TIPO DE INSTALACIÓN';
        $row1[$nFijo + $nUso + $nInst] = 'ESTILOS';
        $ws->fromArray([$row1], null, 'A1');

        // ── Fila 2: claves reales (usa el parser) ─
        $ws->fromArray([$allColKeys], null, 'A2');

        // ── Fila 3: etiquetas amigables (se omite al parsear) ─
        $row3 = array_merge(
            ['↑ igual que completo_kyrios', 'Ej: Sala, Cocina', 'Ver hoja TIPOS_PROYECTO'],
            $usoLabels, $instLabels, $estilosList
        );
        $ws->fromArray([$row3], null, 'A3');

        // ── Fila 4: ejemplo ──
        $exRow = array_fill(0, $nTotal, '');
        $exRow[0] = 'DL-8W-001';
        $exRow[1] = 'Sala, Cocina';
        $exRow[2] = $tiposProyecto->first()?->nombre ?? '';
        $exRow[$nFijo + array_search('interior', $usoKeys)]          = 'x';
        $exRow[$nFijo + $nUso + array_search('colgante', $instKeys)]  = 'x';
        $ws->fromArray([$exRow], null, 'A4');

        // ── Estilos ──
        $lastCol = $coord($nTotal);
        // Fila 1: fondo negro en fijos, azul en USO, verde en INST, naranja en ESTILOS
        $applyStyle($ws, 'A1:C1', $styleHeader);
        $usoC1  = $coord($nFijo + 1);         $usoC2  = $coord($nFijo + $nUso);
        $instC1 = $coord($nFijo + $nUso + 1); $instC2 = $coord($nFijo + $nUso + $nInst);
        $estC1  = $coord($nFijo + $nUso + $nInst + 1); $estC2 = $coord($nTotal);
        $ws->getStyle("{$usoC1}1:{$usoC2}1")->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1565C0']]]);
        $ws->getStyle("{$instC1}1:{$instC2}1")->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']]]);
        $ws->getStyle("{$estC1}1:{$estC2}1")->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E65100']]]);
        // Fila 2: claves (ref oscuro)
        $applyStyle($ws, "A2:{$lastCol}2", $styleRef);
        // Fila 3: nota
        $applyStyle($ws, "A3:{$lastCol}3", $styleNote);
        // Fila 4: ejemplo
        $applyStyle($ws, "A4:{$lastCol}4", $styleExample);
        // Congelar columna A y filas 1-2
        $ws->freezePane('D4');
        for ($c = 1; $c <= $nTotal; $c++) {
            $ws->getColumnDimensionByColumn($c)->setAutoSize(true);
        }

        // ── 7. COMPONENTES ───────────────────────────────────────────────────
        $ws = new Worksheet($spreadsheet, 'COMPONENTES');
        $spreadsheet->addSheet($ws);
        $ws->fromArray([['codigo_fabrica_padre','codigo_fabrica_hijo','cantidad']], null, 'A1');
        $applyStyle($ws, 'A1:C1', $styleHeader);
        $ws->fromArray([['código del producto principal','código del accesorio/componente','cantidad por unidad']], null, 'A2');
        $applyStyle($ws, 'A2:C2', $styleNote);
        $ws->fromArray([['DL-8W-001','ACC-DRV-8W','1']], null, 'A3');
        $applyStyle($ws, 'A3:C3', $styleExample);
        foreach (['A','B','C'] as $c) $ws->getColumnDimension($c)->setAutoSize(true);

        // ── 8. TIPOS_PRODUCTO (referencia de BD) ──────────────────────────────
        $ws = new Worksheet($spreadsheet, 'TIPOS_PRODUCTO');
        $spreadsheet->addSheet($ws);
        $ws->fromArray([['codigo','nombre']], null, 'A1');
        $applyStyle($ws, 'A1:B1', $styleRef);
        $fila = 2;
        foreach ($tiposProducto as $tp) {
            $ws->setCellValue("A{$fila}", $tp->codigo);
            $ws->setCellValue("B{$fila}", $tp->nombre);
            $fila++;
        }
        $ws->getColumnDimension('A')->setAutoSize(true);
        $ws->getColumnDimension('B')->setAutoSize(true);

        // ── 9. TIPOS_LUMINARIA (referencia de BD) ────────────────────────────
        $ws = new Worksheet($spreadsheet, 'TIPOS_LUMINARIA');
        $spreadsheet->addSheet($ws);
        $ws->fromArray([['codigo','nombre']], null, 'A1');
        $applyStyle($ws, 'A1:B1', $styleRef);
        $fila = 2;
        foreach ($tiposLuminaria as $tl) {
            $ws->setCellValue("A{$fila}", $tl->codigo);
            $ws->setCellValue("B{$fila}", $tl->nombre);
            $fila++;
        }
        $ws->getColumnDimension('A')->setAutoSize(true);
        $ws->getColumnDimension('B')->setAutoSize(true);

        // ── 10. MARCAS (referencia de BD) ────────────────────────────────────
        $ws = new Worksheet($spreadsheet, 'MARCAS');
        $spreadsheet->addSheet($ws);
        $ws->fromArray([['codigo','nombre']], null, 'A1');
        $applyStyle($ws, 'A1:B1', $styleRef);
        $fila = 2;
        foreach ($marcas as $m) {
            $ws->setCellValue("A{$fila}", $m->codigo);
            $ws->setCellValue("B{$fila}", $m->nombre);
            $fila++;
        }
        $ws->getColumnDimension('A')->setAutoSize(true);
        $ws->getColumnDimension('B')->setAutoSize(true);

        // ── 11. UNIDADES (referencia de BD) ──────────────────────────────────
        $ws = new Worksheet($spreadsheet, 'UNIDADES');
        $spreadsheet->addSheet($ws);
        $ws->fromArray([['simbolo','nombre']], null, 'A1');
        $applyStyle($ws, 'A1:B1', $styleRef);
        $fila = 2;
        foreach ($unidades as $u) {
            $ws->setCellValue("A{$fila}", $u->simbolo);
            $ws->setCellValue("B{$fila}", $u->nombre);
            $fila++;
        }
        $ws->getColumnDimension('A')->setAutoSize(true);
        $ws->getColumnDimension('B')->setAutoSize(true);

        // ── 12. TIPOS_PROYECTO (referencia de BD) ────────────────────────────
        $ws = new Worksheet($spreadsheet, 'TIPOS_PROYECTO');
        $spreadsheet->addSheet($ws);
        $ws->fromArray([['nombre']], null, 'A1');
        $applyStyle($ws, 'A1:A1', $styleRef);
        $fila = 2;
        foreach ($tiposProyecto as $tp) {
            $ws->setCellValue("A{$fila}", $tp->nombre);
            $fila++;
        }
        $ws->getColumnDimension('A')->setAutoSize(true);

        // ── 13. INSTALACION_VALORES (referencia) ──────────────────────────────
        $ws = new Worksheet($spreadsheet, 'INSTALACION_VALORES');
        $spreadsheet->addSheet($ws);
        $ws->fromArray([['valor_instalacion','descripcion']], null, 'A1');
        $applyStyle($ws, 'A1:B1', $styleRef);
        $fila = 2;
        foreach (ProductoClasificacion::TIPOS_INSTALACION as $codigo => $label) {
            $ws->setCellValue("A{$fila}", $codigo);
            $ws->setCellValue("B{$fila}", $label);
            $fila++;
        }
        $ws->getColumnDimension('A')->setAutoSize(true);
        $ws->getColumnDimension('B')->setAutoSize(true);

        // ── 14. USOS_VALORES (referencia) ────────────────────────────────────
        $ws = new Worksheet($spreadsheet, 'USOS_VALORES');
        $spreadsheet->addSheet($ws);
        $ws->fromArray([['valor_uso','descripcion']], null, 'A1');
        $applyStyle($ws, 'A1:B1', $styleRef);
        $fila = 2;
        foreach (ProductoClasificacion::USOS_PRODUCTO as $codigo => $label) {
            $ws->setCellValue("A{$fila}", $codigo);
            $ws->setCellValue("B{$fila}", $label);
            $fila++;
        }
        $ws->getColumnDimension('A')->setAutoSize(true);
        $ws->getColumnDimension('B')->setAutoSize(true);

        // ── 15. ESTILOS_VALORES (referencia) ─────────────────────────────────
        $ws = new Worksheet($spreadsheet, 'ESTILOS_VALORES');
        $spreadsheet->addSheet($ws);
        $ws->fromArray([['estilo_sugerido']], null, 'A1');
        $applyStyle($ws, 'A1:A1', $styleRef);
        $fila = 2;
        foreach (ProductoClasificacion::ESTILOS_SUGERIDOS as $estilo) {
            $ws->setCellValue("A{$fila}", $estilo);
            $fila++;
        }
        $ws->getColumnDimension('A')->setAutoSize(true);

        // ── Proteger todas las hojas de referencia ────────────────────────────
        $hojasRef = ['TIPOS_PRODUCTO','TIPOS_LUMINARIA','MARCAS','UNIDADES','TIPOS_PROYECTO',
                     'INSTALACION_VALORES','USOS_VALORES','ESTILOS_VALORES'];
        foreach ($hojasRef as $nombre) {
            $protection = $spreadsheet->getSheetByName($nombre)->getProtection();
            $protection->setSheet(true)->setPassword('kyrios2026');
        }

        // Activar la primera hoja al abrir
        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new XlsxWriter($spreadsheet);
            $writer->save('php://output');
        }, 'plantilla_importacion_kyrios.xlsx', [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plantilla_importacion_kyrios.xlsx"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ─── Procesar archivo Excel ───────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        try {
            $reader = new XlsxReader();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($request->file('archivo')->getRealPath());

            $resultado = $this->procesarSpreadsheet($spreadsheet);

            return back()->with('importacion', $resultado);

        } catch (\Throwable $e) {
            return back()->withErrors(['archivo' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    // ─── Leer hoja CLASIFICACIONES en formato matriz (fila 1=grupo, fila 2=claves, fila 3=etiquetas, fila 4+=datos) ─

    private function leerHojaMatrix(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, string $nombre): array
    {
        if (!$spreadsheet->sheetNameExists($nombre)) {
            return [];
        }

        $ws       = $spreadsheet->getSheetByName($nombre);
        $cabecera = null;
        $numFila  = 0;
        $filas    = [];

        foreach ($ws->getRowIterator() as $row) {
            $numFila++;
            $iter = $row->getCellIterator();
            $iter->setIterateOnlyExistingCells(false);

            $valores = [];
            foreach ($iter as $cell) {
                $valores[] = $cell->getValue();
            }
            // Quitar trailing nulls
            while (!empty($valores) && ($valores[array_key_last($valores)] === null || $valores[array_key_last($valores)] === '')) {
                array_pop($valores);
            }
            if (empty($valores)) continue;

            $valores = array_map(fn($v) => $v !== null ? trim((string) $v) : '', $valores);

            if ($numFila === 1) continue; // Fila de grupos (visual)
            if ($numFila === 2) { $cabecera = $valores; continue; } // Claves reales
            if ($numFila === 3) continue; // Fila de etiquetas amigables (descripción)

            if ($cabecera === null) continue;
            if (empty(array_filter($valores))) continue;

            $fila = array_combine($cabecera, array_pad($valores, count($cabecera), ''));

            if (empty($fila['codigo_fabrica'])) continue;

            $filas[] = $fila;
        }

        return $filas;
    }

    // ─── Leer una hoja como array de filas asociativas ────────────────────────

    private function leerHoja(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, string $nombre): array
    {
        if (!$spreadsheet->sheetNameExists($nombre)) {
            return [];
        }

        $ws       = $spreadsheet->getSheetByName($nombre);
        $filas    = [];
        $cabecera = null;

        foreach ($ws->getRowIterator() as $row) {
            $iter = $row->getCellIterator();
            $iter->setIterateOnlyExistingCells(false);

            $valores = [];
            foreach ($iter as $cell) {
                $valores[] = $cell->getValue();
            }

            // Eliminar celdas vacías del final
            while (!empty($valores) && ($valores[array_key_last($valores)] === null || $valores[array_key_last($valores)] === '')) {
                array_pop($valores);
            }

            if (empty($valores)) continue;

            $valores = array_map(fn($v) => $v !== null ? trim((string) $v) : '', $valores);

            if (empty(array_filter($valores, fn($v) => $v !== ''))) continue;

            if ($cabecera === null) {
                $cabecera = $valores;
                continue;
            }

            // Ignorar filas de instrucción (sin codigo_fabrica válido en la primera columna, si aplica)
            $filas[] = array_combine(
                $cabecera,
                array_pad($valores, count($cabecera), '')
            );
        }

        return $filas;
    }

    // ─── Orquestador principal ────────────────────────────────────────────────

    private function procesarSpreadsheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): array
    {
        $log = ['creados' => 0, 'actualizados' => 0, 'variantes' => 0, 'errores' => []];

        // Leer todas las hojas relevantes
        $productos      = $this->leerHoja($spreadsheet, 'completo_kyrios');
        $dimensiones    = collect($this->leerHoja($spreadsheet, 'DIMENSIONES'))->keyBy('codigo_fabrica');
        $embalajes      = collect($this->leerHoja($spreadsheet, 'EMBALAJE'))->keyBy('codigo_fabrica');
        $atributos      = collect($this->leerHoja($spreadsheet, 'ATRIBUTOS_PRODUCTO'))->keyBy('codigo_fabrica');
        $clasificaciones= collect($this->leerHojaMatrix($spreadsheet, 'CLASIFICACIONES'))->keyBy('codigo_fabrica');
        $variantes      = collect($this->leerHoja($spreadsheet, 'VARIANTES'))->groupBy('codigo_fabrica');
        $componentes    = $this->leerHoja($spreadsheet, 'COMPONENTES');

        $mapaId = [];

        DB::beginTransaction();
        try {
            foreach ($productos as $i => $fila) {
                $linea = $i + 2;
                $codigoFabrica = '';
                try {
                    $codigoFabrica = strtoupper(trim($fila['codigo_fabrica'] ?? ''));
                    if (!$codigoFabrica) {
                        $log['errores'][] = "Fila {$linea}: omitida — falta codigo_fabrica.";
                        continue;
                    }

                    $nombre = trim($fila['nombre'] ?? '');
                    if (!$nombre) {
                        $log['errores'][] = "Fila {$linea} ({$codigoFabrica}): omitida — falta nombre.";
                        continue;
                    }

                    // Lookups de catálogos
                    $marcaId = null;
                    if (!empty($fila['marca_codigo'])) {
                        $marca = Marca::firstOrCreate(
                            ['codigo' => strtoupper(trim($fila['marca_codigo']))],
                            ['nombre' => trim($fila['marca_codigo']), 'estado' => 'activo']
                        );
                        $marcaId = $marca->id;
                    }

                    $tipoProdId = null;
                    if (!empty($fila['tipo_producto_codigo'])) {
                        $tipoProdId = TipoProducto::where('codigo', trim($fila['tipo_producto_codigo']))->value('id');
                    }
                    $tipoProdId ??= TipoProducto::first()?->id ?? 1;

                    $tipoLumId = null;
                    if (!empty($fila['tipo_luminaria_codigo'])) {
                        $tipoLumId = TipoLuminaria::where('codigo', trim($fila['tipo_luminaria_codigo']))->value('id');
                    }

                    $unidadId = null;
                    if (!empty($fila['unidad_medida_codigo'])) {
                        $unidadId = UnidadMedida::whereRaw('UPPER(simbolo) = ?', [strtoupper(trim($fila['unidad_medida_codigo']))])->value('id');
                    }
                    $unidadId ??= UnidadMedida::where('simbolo', 'und')
                        ->orWhere('nombre', 'like', '%unidad%')
                        ->value('id') ?? 1;

                    $categoria = Categoria::firstOrCreate(
                        ['nombre' => 'Importados'],
                        ['estado' => 'activo', 'descripcion' => 'Importados desde Excel']
                    );

                    $datosProducto = [
                        'nombre'            => $nombre,
                        'nombre_kyrios'     => trim($fila['nombre_kyrios'] ?? '') ?: null,
                        'codigo_fabrica'    => $codigoFabrica,
                        'categoria_id'      => $categoria->id,
                        'marca_id'          => $marcaId,
                        'unidad_medida_id'  => $unidadId,
                        'tipo_producto_id'  => $tipoProdId,
                        'tipo_luminaria_id' => $tipoLumId,
                        'tipo_sistema'      => 'simple',
                        'tipo_inventario'   => 'cantidad',
                        'stock_minimo'      => 0,
                        'stock_maximo'      => 9999,
                        'estado'            => trim($fila['estado'] ?? 'activo') ?: 'activo',
                        'linea'             => trim($fila['linea'] ?? '') ?: null,
                        'procedencia'       => trim($fila['procedencia'] ?? '') ?: null,
                        'ficha_tecnica_url' => trim($fila['ficha_tecnica_url'] ?? '') ?: null,
                    ];

                    $producto = Producto::updateOrCreate(
                        ['codigo_fabrica' => $codigoFabrica],
                        $datosProducto + ['codigo' => Producto::generarCodigo(), 'estado_aprobacion' => 'borrador']
                    );

                    if ($producto->wasRecentlyCreated) {
                        $log['creados']++;
                    } else {
                        $log['actualizados']++;
                    }

                    $mapaId[$codigoFabrica] = $producto->id;

                    // Sub-tablas: usar la fila correspondiente de cada hoja
                    $filaDim  = $dimensiones->get($codigoFabrica, []);
                    $filaEmb  = $embalajes->get($codigoFabrica, []);
                    $filaAtr  = $atributos->get($codigoFabrica, []);
                    $filaClas = $clasificaciones->get($codigoFabrica, []);
                    $filasVar = $variantes->get($codigoFabrica, collect())->all();

                    $this->guardarEspecificaciones($producto, $filaAtr);
                    $this->guardarDimensiones($producto, $filaDim);
                    $this->guardarMateriales($producto, $filaDim);
                    $this->guardarEmbalaje($producto, $filaEmb);
                    $this->guardarVariantes($producto, $filasVar, $log);
                    $this->guardarClasificaciones($producto, $filaClas);

                } catch (\Throwable $e) {
                    $log['errores'][] = "Fila {$linea} ('{$codigoFabrica}'): " . $e->getMessage();
                }
            }

            // Segunda pasada: COMPONENTES
            foreach ($componentes as $i => $fila) {
                $padreCode = strtoupper(trim($fila['codigo_fabrica_padre'] ?? ''));
                $hijoCode  = strtoupper(trim($fila['codigo_fabrica_hijo'] ?? ''));
                if (!$padreCode || !$hijoCode) continue;

                $padreId = $mapaId[$padreCode] ?? Producto::where('codigo_fabrica', $padreCode)->value('id');
                $hijoId  = $mapaId[$hijoCode]  ?? Producto::where('codigo_fabrica', $hijoCode)->value('id');

                if (!$padreId || !$hijoId || $padreId === $hijoId) continue;

                $cantidad = max(1, (int) ($fila['cantidad'] ?? 1));

                ProductoComponente::firstOrCreate(
                    ['padre_id' => $padreId, 'hijo_id' => $hijoId, 'variante_id' => null],
                    ['cantidad' => $cantidad, 'unidad' => 'unidad', 'es_opcional' => false, 'orden' => 0]
                );
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $log;
    }

    // ─── Guardar especificaciones técnicas (hoja ATRIBUTOS_PRODUCTO) ──────────

    private function guardarEspecificaciones(Producto $producto, array $fila): void
    {
        if (empty($fila)) return;

        $data = [
            'tipo_fuente'          => $fila['tipo_fuente'] ?? null ?: null,
            'nivel_potencia'       => $fila['nivel_potencia'] ?? null ?: null,
            'socket'               => $fila['socket'] ?? null ?: null,
            'numero_lamparas'      => $this->normInt($fila['numero_lamparas'] ?? ''),
            'potencia'             => $fila['potencia'] ?? null ?: null,
            'voltaje'              => $fila['voltaje'] ?? null ?: null,
            'ip'                   => strtoupper($fila['ip'] ?? '') ?: null,
            'ik'                   => strtoupper($fila['ik'] ?? '') ?: null,
            'angulo_apertura'      => $fila['angulo_apertura'] ?? null ?: null,
            'driver'               => $fila['driver'] ?? null ?: null,
            'regulable'            => $this->normBool($fila['regulable'] ?? ''),
            'protocolo_regulacion' => $fila['protocolo_regulacion'] ?? null ?: null,
            'vida_util_horas'      => $this->normInt($fila['vida_util_horas'] ?? ''),
            'nominal_lumenes'      => $this->normNum($fila['nominal_lumenes'] ?? ''),
            'real_lumenes'         => $this->normNum($fila['real_lumenes'] ?? ''),
            'eficacia_luminosa'    => $this->normNum($fila['eficacia_luminosa'] ?? ''),
            'temperatura_color'    => $fila['temperatura_color'] ?? null ?: null,
            'tonalidad_luz'        => $fila['tonalidad_luz'] ?? null ?: null,
            'cri'                  => $this->normInt($fila['cri'] ?? ''),
        ];

        if (array_filter($data)) {
            ProductoEspecificacion::updateOrCreate(
                ['producto_id' => $producto->id],
                $data
            );
        }
    }

    // ─── Guardar dimensiones (hoja DIMENSIONES) ───────────────────────────────

    private function guardarDimensiones(Producto $producto, array $fila): void
    {
        if (empty($fila)) return;

        $data = [
            'alto'               => $this->normNum($fila['alto_mm'] ?? ''),
            'ancho'              => $this->normNum($fila['ancho_mm'] ?? ''),
            'diametro'           => $this->normNum($fila['diametro_mm'] ?? ''),
            'lado'               => $this->normNum($fila['lado_mm'] ?? ''),
            'profundidad'        => $this->normNum($fila['profundidad_mm'] ?? ''),
            'alto_suspendido'    => $this->normNum($fila['alto_suspendido_mm'] ?? ''),
            'diametro_agujero'   => $this->normNum($fila['diametro_agujero_mm'] ?? ''),
            'ancho_agujero'      => $this->normNum($fila['ancho_agujero_mm'] ?? ''),
            'profundidad_agujero'=> $this->normNum($fila['profundidad_agujero_mm'] ?? ''),
        ];

        if (array_filter($data)) {
            ProductoDimension::updateOrCreate(
                ['producto_id' => $producto->id],
                $data
            );
        }
    }

    // ─── Guardar materiales (columnas de la hoja DIMENSIONES) ────────────────

    private function guardarMateriales(Producto $producto, array $fila): void
    {
        if (empty($fila)) return;

        $data = [
            'material_1'       => trim($fila['material_1'] ?? '') ?: null,
            'material_2'       => trim($fila['material_2'] ?? '') ?: null,
            'material_terciario'=> trim($fila['material_3'] ?? '') ?: null,
        ];

        if (array_filter($data)) {
            ProductoMaterial::updateOrCreate(
                ['producto_id' => $producto->id],
                $data
            );
        }
    }

    // ─── Guardar embalaje (hoja EMBALAJE) ─────────────────────────────────────

    private function guardarEmbalaje(Producto $producto, array $fila): void
    {
        if (empty($fila)) return;

        $data = [
            'peso'              => $this->normNum($fila['peso_kg'] ?? ''),
            'volumen'           => $this->normNum($fila['volumen_cm3'] ?? ''),
            'medida_embalaje'   => trim($fila['medida_embalaje'] ?? '') ?: null,
            'cantidad_por_caja' => $this->normInt($fila['cantidad_por_caja'] ?? ''),
            'embalado'          => $this->normBool($fila['embalado'] ?? ''),
        ];

        if (array_filter($data, fn($v) => $v !== null && $v !== false)) {
            ProductoEmbalaje::updateOrCreate(
                ['producto_id' => $producto->id],
                $data
            );
        }
    }

    // ─── Guardar variantes (hoja VARIANTES) ───────────────────────────────────

    private function guardarVariantes(Producto $producto, array $filasVar, array &$log): void
    {
        foreach ($filasVar as $fila) {
            $nombre = trim($fila['variante_nombre'] ?? '');
            if (!$nombre) continue;

            // Buscar color por nombre en el catálogo
            $colorId = null;
            $colorNombre = trim($fila['color'] ?? '');
            if ($colorNombre) {
                $color = Color::whereRaw('LOWER(nombre) = ?', [strtolower($colorNombre)])->first();
                if (!$color) {
                    $color = Color::create([
                        'nombre' => $colorNombre,
                        'estado' => 'activo',
                    ]);
                }
                $colorId = $color->id;
            }

            // Construir JSON de atributos
            $atributos = [];
            foreach (['acabado', 'tonalidad_luz', 'tipo_lampara', 'angulo_haz',
                      'protocolo_regulacion', 'eficiencia_luminica',
                      'garantia', 'vida_util', 'ip', 'cri', 'otros'] as $campo) {
                $valor = trim($fila[$campo] ?? '');
                if ($valor !== '') $atributos[$campo] = $valor;
            }

            $variante = ProductoVariante::firstOrCreate(
                [
                    'producto_id' => $producto->id,
                    'nombre'      => $nombre,
                    'color_id'    => $colorId,
                ],
                [
                    'atributos'   => $atributos ?: null,
                    'sobreprecio' => $this->normNum($fila['sobreprecio'] ?? '') ?? 0,
                    'stock_actual'=> $this->normInt($fila['stock'] ?? '') ?? 0,
                    'estado'      => 'activo',
                ]
            );

            if (!$variante->wasRecentlyCreated && !empty($atributos)) {
                $variante->update(['atributos' => array_merge($variante->atributos ?? [], $atributos)]);
            }

            if ($variante->wasRecentlyCreated) {
                $log['variantes']++;
            }
        }
    }

    // ─── Guardar clasificaciones (hoja CLASIFICACIONES — formato matriz) ────────

    private function guardarClasificaciones(Producto $producto, array $fila): void
    {
        if (empty($fila)) return;

        $isTrue = fn($v) => in_array(strtolower(trim((string) $v)), ['x', '1', 'si', 'sí', 'yes', 'true'], true);

        $splitLimpio = fn(?string $v) => array_values(array_filter(
            array_map('trim', explode(',', $v ?? '')),
            fn($s) => $s !== ''
        ));

        // ── Detectar formato matriz (columnas = claves de USO/INSTALACION) ──────
        $usoKeys  = array_keys(ProductoClasificacion::USOS_PRODUCTO);
        $instKeys = array_keys(ProductoClasificacion::TIPOS_INSTALACION);
        $estList  = ProductoClasificacion::ESTILOS_SUGERIDOS;

        $esMatriz = !empty(array_intersect(array_keys($fila), $usoKeys))
                 || !empty(array_intersect(array_keys($fila), $instKeys));

        if ($esMatriz) {
            // ── Leer columnas con X ─────────────────────────────────────────
            $usos       = array_values(array_filter($usoKeys,  fn($k) => $isTrue($fila[$k] ?? '')));
            $instalacion= array_values(array_filter($instKeys, fn($k) => $isTrue($fila[$k] ?? '')));
            $estilos    = array_values(array_filter($estList,  fn($e) => $isTrue($fila[$e] ?? '')));
            $ambientes  = $splitLimpio($fila['ambiente'] ?? '');
        } else {
            // ── Formato texto legado (separado por coma) ────────────────────
            $usos       = $splitLimpio($fila['uso'] ?? '');
            $ambientes  = $splitLimpio($fila['ambiente'] ?? '');
            $instalacion= $splitLimpio($fila['instalacion'] ?? '');
            $estilos    = $splitLimpio($fila['estilo'] ?? '');
        }

        // Guardar en producto_clasificacion (JSON)
        $datosClasif = [];
        if (!empty($usos))        $datosClasif['usos']             = $usos;
        if (!empty($ambientes))   $datosClasif['ambientes']        = $ambientes;
        if (!empty($instalacion)) $datosClasif['tipo_instalacion'] = $instalacion;
        if (!empty($estilos))     $datosClasif['estilo']           = $estilos;

        if (!empty($datosClasif)) {
            ProductoClasificacion::updateOrCreate(
                ['producto_id' => $producto->id],
                $datosClasif
            );
        }

        // Asociar tipos de proyecto (pivot)
        $tipoProyectoNombres = $splitLimpio($fila['tipo_proyecto'] ?? '');
        if (!empty($tipoProyectoNombres)) {
            $ids = [];
            foreach ($tipoProyectoNombres as $nombre) {
                $tp = TipoProyecto::whereRaw('LOWER(nombre) = ?', [strtolower($nombre)])->first();
                if ($tp) $ids[] = $tp->id;
            }
            if (!empty($ids)) $producto->tiposProyecto()->syncWithoutDetaching($ids);
        }
    }

    // ─── Normalización ────────────────────────────────────────────────────────

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
}
