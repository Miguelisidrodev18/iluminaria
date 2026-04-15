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

        // ── 6. CLASIFICACIONES (texto plano — 6 columnas para rellenar con comas) ──
        $ws = new Worksheet($spreadsheet, 'CLASIFICACIONES');
        $spreadsheet->addSheet($ws);
        $ws->fromArray([['codigo_fabrica','uso','tipo_proyecto','ambiente','instalacion','estilo']], null, 'A1');
        $applyStyle($ws, 'A1:F1', $styleHeader);
        $ws->fromArray([[
            null,
            'Valores: interiores | exteriores | alumbrado_publico | piscina  (separar con coma)',
            'Nombre exacto del tipo de proyecto — múltiples separados por coma',
            'Nombre del ambiente — múltiples separados por coma (ej: Sala, Cocina, Dormitorio)',
            'Ver hoja INSTALACION_VALORES — múltiples separados por coma',
            'Ver hoja ESTILOS_VALORES — múltiples separados por coma',
        ]], null, 'A2');
        $applyStyle($ws, 'A2:F2', $styleNote);
        $ws->fromArray([[
            '',
            'interiores, exteriores',
            $tiposProyecto->first()?->nombre ?? 'Residencial',
            'Sala, Cocina, Dormitorio',
            'plafon, colgante',
            'Moderno, Minimalista',
        ]], null, 'A3');
        $applyStyle($ws, 'A3:F3', $styleExample);
        foreach (['A','B','C','D','E','F'] as $c) $ws->getColumnDimension($c)->setAutoSize(true);

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

        // ── 16. CLASIFICACIONES_PROYECTO (matriz con X — una columna por opción) ──
        $ws = new Worksheet($spreadsheet, 'CLASIFICACIONES_PROYECTO');
        $spreadsheet->addSheet($ws);

        $coord = fn(int $col) => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);

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

        $usoLabelsM   = array_values(ProductoClasificacion::USOS_PRODUCTO);
        $instLabelsM  = array_values(ProductoClasificacion::TIPOS_INSTALACION);
        $estilosListM = ProductoClasificacion::ESTILOS_SUGERIDOS;
        $tpNombresM   = $tiposProyecto->pluck('nombre')->toArray();

        $row1M   = [];
        $row2M   = [];
        $gruposM = [];
        $ambColorsM = ['006064','00695C','00838F','0277BD','283593','4527A0','5D4037','37474F','1B5E20','4A148C','BF360C','4E342E'];

        $row1M[] = 'codigo_fabrica';
        $row2M[] = 'codigo_fabrica';

        if (!empty($tpNombresM)) {
            $gStart = count($row1M);
            foreach ($tpNombresM as $nombre) { $row1M[] = ''; $row2M[] = mb_strtoupper($nombre, 'UTF-8'); }
            $gruposM[] = ['name' => 'TIPO DE PROYECTO', 'start' => $gStart, 'end' => count($row1M) - 1, 'color' => '1565C0'];
        }

        $ambColorIdx = 0;
        foreach ($ambientesPorGrupo as $groupName => $ambList) {
            if (empty($ambList)) continue;
            $gStart = count($row1M);
            foreach ($ambList as $label) { $row1M[] = ''; $row2M[] = mb_strtoupper($label, 'UTF-8'); }
            $gruposM[] = ['name' => $groupName, 'start' => $gStart, 'end' => count($row1M) - 1, 'color' => $ambColorsM[$ambColorIdx % count($ambColorsM)]];
            $ambColorIdx++;
        }

        $gStart = count($row1M);
        foreach ($usoLabelsM as $label) { $row1M[] = ''; $row2M[] = mb_strtoupper($label, 'UTF-8'); }
        $gruposM[] = ['name' => 'USO', 'start' => $gStart, 'end' => count($row1M) - 1, 'color' => '6A1B9A'];

        $gStart = count($row1M);
        foreach ($instLabelsM as $label) { $row1M[] = ''; $row2M[] = mb_strtoupper($label, 'UTF-8'); }
        $gruposM[] = ['name' => 'TIPO DE INSTALACIÓN', 'start' => $gStart, 'end' => count($row1M) - 1, 'color' => '2E7D32'];

        $gStart = count($row1M);
        foreach ($estilosListM as $estilo) { $row1M[] = ''; $row2M[] = mb_strtoupper($estilo, 'UTF-8'); }
        $gruposM[] = ['name' => 'ESTILO', 'start' => $gStart, 'end' => count($row1M) - 1, 'color' => 'E65100'];

        $nTotalM = count($row1M);
        foreach ($gruposM as $g) { $row1M[$g['start']] = $g['name']; }

        $ws->fromArray([$row1M], null, 'A1');
        $ws->fromArray([$row2M], null, 'A2');

        foreach ($gruposM as $g) {
            if ($g['start'] < $g['end']) {
                $ws->mergeCells($coord($g['start'] + 1) . '1:' . $coord($g['end'] + 1) . '1');
            }
        }

        $baseStyleM = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'wrapText'   => true],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ];
        $darkFill = ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2B2E2C']]];
        $applyStyle($ws, 'A1', array_merge($baseStyleM, $darkFill));
        $applyStyle($ws, 'A2', array_merge($baseStyleM, $darkFill));
        foreach ($gruposM as $g) {
            $gs = array_merge($baseStyleM, ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $g['color']]]]);
            $ws->getStyle($coord($g['start'] + 1) . '1:' . $coord($g['end'] + 1) . '1')->applyFromArray($gs);
            $ws->getStyle($coord($g['start'] + 1) . '2:' . $coord($g['end'] + 1) . '2')->applyFromArray($gs);
        }

        $ws->getRowDimension(1)->setRowHeight(22);
        $ws->getRowDimension(2)->setRowHeight(65);
        $ws->getColumnDimension('A')->setWidth(22);
        for ($c = 2; $c <= $nTotalM; $c++) { $ws->getColumnDimensionByColumn($c)->setWidth(14); }
        $ws->freezePane('B3');

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

    // ─── Leer hoja CLASIFICACIONES (fila 1=grupos, fila 2=etiquetas amigables, fila 3+=datos con X) ─

    private function leerHojaMatrix(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, string $nombre): array
    {
        if (!$spreadsheet->sheetNameExists($nombre)) {
            return [];
        }

        $ws      = $spreadsheet->getSheetByName($nombre);
        $numFila = 0;
        $fila1   = null;   // cabeceras de grupo (fila 1)
        $cabecera = null;  // claves internas traducidas (fila 2)
        $filas   = [];
        $isTrue  = fn($v) => in_array(strtolower(trim((string) $v)), ['x', '1', 'si', 'sí', 'yes', 'true'], true);

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

            if ($numFila === 1) {
                $fila1 = $valores; // guardar grupos para contexto
                continue;
            }

            if ($numFila === 2) {
                // Traducir etiquetas amigables → claves internas usando contexto de grupos
                $cabecera = $this->traducirCabeceras($valores, $fila1 ?? []);
                continue;
            }

            // Fila 3+: datos
            if ($cabecera === null || empty(array_filter($valores))) continue;

            $codigoFabrica = trim((string) ($valores[0] ?? ''));
            if ($codigoFabrica === '') continue;

            $fila      = ['codigo_fabrica' => $codigoFabrica];
            $tpNombres = [];
            $ambSlugs  = [];

            foreach ($cabecera as $i => $clave) {
                if ($i === 0) continue; // codigo_fabrica ya procesado
                $valor = $valores[$i] ?? '';

                if (str_starts_with($clave, '__tp:')) {
                    // Tipo de proyecto: acumular nombres para join
                    if ($isTrue($valor)) $tpNombres[] = substr($clave, 5);
                } elseif (str_starts_with($clave, '__amb:')) {
                    // Ambiente: acumular slugs para join
                    if ($isTrue($valor)) $ambSlugs[] = substr($clave, 6);
                } else {
                    // USO / INSTALACION / ESTILO: clave directa con valor X
                    $fila[$clave] = $valor;
                }
            }

            // Agregar como texto separado por coma (guardarClasificaciones los split)
            if (!empty($tpNombres)) $fila['tipo_proyecto'] = implode(',', $tpNombres);
            if (!empty($ambSlugs))  $fila['ambiente']      = implode(',', $ambSlugs);

            $filas[] = $fila;
        }

        return $filas;
    }

    /**
     * Traduce las etiquetas amigables de la fila 2 a claves internas.
     * Usa el contexto de grupo de la fila 1 para resolver ambigüedades
     * (ej: ESCRITORIO aparece tanto en AMBIENTE RESIDENCIAL como en TIPO DE INSTALACIÓN).
     * Las celdas fusionadas solo tienen valor en la celda master; el resto llega vacío,
     * por eso propagamos el nombre de grupo hacia la derecha.
     */
    private function traducirCabeceras(array $fila2, array $fila1): array
    {
        // Propagar nombre de grupo de izquierda a derecha
        $groupPerCol  = [];
        $currentGroup = '';
        foreach ($fila1 as $i => $val) {
            if ($val !== '') $currentGroup = strtolower(trim(Str::ascii($val)));
            $groupPerCol[$i] = $currentGroup;
        }

        // Mapa dinámico: nombres de tipos de proyecto desde BD (ascii+lowercase → __tp:nombre)
        $tpMap = TipoProyecto::pluck('nombre')
            ->mapWithKeys(fn($n) => [strtolower(trim(Str::ascii($n))) => '__tp:' . strtolower(trim($n))])
            ->toArray();

        // Claves en ASCII puro (sin tildes) — el label ya viene normalizado con Str::ascii()
        $staticMap = [
            // USO
            'interiores'             => 'interior',
            'exteriores'             => 'exterior',
            'alumbrado publico'      => 'alumbrado_publico',
            'piscina'                => 'piscina',
            // TIPO DE INSTALACIÓN
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
            // ESTILO (preservar case original de ESTILOS_SUGERIDOS)
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

        // Claves en ASCII puro — etiqueta (ascii+lowercase) → slug con prefijo __amb:
        $ambMap = [
            'fachada'                        => '__amb:fachada',
            'ingreso/hall'                   => '__amb:ingreso_hall',
            'bano de visita'                 => '__amb:banio_visita',
            'escritorio'                     => '__amb:escritorio',
            'sala'                           => '__amb:sala',
            'comedor'                        => '__amb:comedor',
            'terraza'                        => '__amb:terraza',
            'jardin'                         => '__amb:jardin',
            'cocina'                         => '__amb:cocina',
            'dormitorio'                     => '__amb:dormitorio',
            'sshh'                           => '__amb:sshh',
            'sala de tv'                     => '__amb:sala_tv',
            'walking closet'                 => '__amb:walking_closet',
            'area de servicio'               => '__amb:area_servicio',
            'vitrina'                        => '__amb:vitrina',
            'counter'                        => '__amb:counter',
            'showroom'                       => '__amb:showroom',
            'mueble vitrina'                 => '__amb:mueble_vitrina',
            'exhibidor'                      => '__amb:exhibidor',
            'mesa atencion'                  => '__amb:mesa_atencion',
            'probadores'                     => '__amb:probadores',
            'estacionamiento'                => '__amb:estacionamiento',
            'recepcion'                      => '__amb:recepcion',
            'oficinas abiertas'              => '__amb:oficinas_abiertas',
            'oficinas cerradas'              => '__amb:oficinas_cerradas',
            'sala de reuniones'              => '__amb:sala_reuniones',
            'directorio'                     => '__amb:directorio',
            'kitchenette'                    => '__amb:kitchenette',
            'archivador'                     => '__amb:archivador',
            'lobby'                          => '__amb:lobby',
            'business center'                => '__amb:business_center',
            'sum'                            => '__amb:sum',
            'corredores'                     => '__amb:corredores',
            'restaurante'                    => '__amb:restaurante',
            'bar'                            => '__amb:bar',
            'habitacion'                     => '__amb:habitacion',
            'gimnasio'                       => '__amb:gimnasio',
            'spa'                            => '__amb:spa',
            'salon'                          => '__amb:salon',
            'buffet'                         => '__amb:buffet',
            'deposito'                       => '__amb:deposito',
            'directorios'                    => '__amb:directorios',
            'laboratorios'                   => '__amb:laboratorios',
            'consultorio'                    => '__amb:consultorio',
            'quirofano'                      => '__amb:quirofano',
            'sala de espera'                 => '__amb:sala_espera',
            'tienda'                         => '__amb:tienda',
            'zona de mesas'                  => '__amb:zona_mesas',
            'islas de atencion'              => '__amb:islas_atencion',
            'perimetros'                     => '__amb:perimetros',
            'jardinera'                      => '__amb:jardinera',
            'macizos'                        => '__amb:macizos',
            'arboles y plantas altas'        => '__amb:arboles_plantas_altas',
            'cercos vivos/jardin vertical'   => '__amb:cercos_vivos',
            'espejo de agua/piletas'         => '__amb:espejo_agua',
            'pergola'                        => '__amb:pergola',
            'caminos'                        => '__amb:caminos',
            'jardines'                       => '__amb:jardines',
            'zona de juegos'                 => '__amb:zona_juegos',
            'canchas deportivas'             => '__amb:canchas_deportivas',
            'salones sociales'               => '__amb:salones_sociales',
            'ingresos privados'              => '__amb:ingresos_privados',
            'pista interna'                  => '__amb:pista_interna',
            'club house'                     => '__amb:club_house',
            'cines'                          => '__amb:cines',
            'ornamental'                     => '__amb:ornamental',
            'alamedas'                       => '__amb:alamedas',
            'parques'                        => '__amb:parques',
            'veredas'                        => '__amb:veredas',
            'pergolas'                       => '__amb:pergolas',
            'salones'                        => '__amb:salones',
        ];

        $result = [];
        foreach ($fila2 as $i => $label) {
            if ($i === 0) {
                $result[] = 'codigo_fabrica';
                continue;
            }

            $labelNorm = strtolower(trim(Str::ascii($label)));
            $group     = strtolower(Str::ascii($groupPerCol[$i] ?? ''));

            if (str_contains($group, 'ambiente')) {
                // Contexto ambiente: usar ambMap; fallback a slug genérico
                $result[] = $ambMap[$labelNorm] ?? '__amb:' . Str::slug($label, '_');
            } elseif (str_contains($group, 'tipo de proyecto') || isset($tpMap[$labelNorm])) {
                // Contexto tipo de proyecto: mapear desde BD
                $result[] = $tpMap[$labelNorm] ?? '__tp:' . $labelNorm;
            } else {
                // Contexto USO / INSTALACIÓN / ESTILO: usar staticMap
                $result[] = $staticMap[$labelNorm] ?? $labelNorm;
            }
        }

        return $result;
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
        // Preferir CLASIFICACIONES_PROYECTO (matriz con X) si existe; si no, usar CLASIFICACIONES (texto)
        if ($spreadsheet->sheetNameExists('CLASIFICACIONES_PROYECTO')) {
            $clasificaciones = collect($this->leerHojaMatrix($spreadsheet, 'CLASIFICACIONES_PROYECTO'))->keyBy('codigo_fabrica');
        } else {
            $clasificaciones = collect($this->leerHoja($spreadsheet, 'CLASIFICACIONES'))
                ->filter(fn($r) => !empty($r['codigo_fabrica']))
                ->keyBy('codigo_fabrica');
        }
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
