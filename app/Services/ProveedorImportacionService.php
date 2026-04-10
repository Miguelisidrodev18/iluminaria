<?php

namespace App\Services;

use App\Models\Proveedor;
use App\Models\ProveedorCategoriaProducto;
use App\Models\ProveedorCertificacion;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProveedorImportacionService
{
    // ── Columnas de la plantilla (orden fijo) ─────────────────────────────────
    const COLUMNS = [
        'A' => 'supplier_type',
        'B' => 'commercial_name',
        'C' => 'real_name',
        'D' => 'contact_person',
        'E' => 'phone',
        'F' => 'email',
        'G' => 'website',
        'H' => 'catalog_url',
        'I' => 'fiscal_address',
        'J' => 'factory_address',
        'K' => 'country',
        'L' => 'district',
        'M' => 'port',
        'N' => 'moq',
        'O' => 'price_level',
        'P' => 'quality_level',
        'Q' => 'bank_detail',
        'R' => 'observations',
        'S' => 'product_categories',
        'T' => 'certifications',
    ];

    // ── Sinónimos para price_level ─────────────────────────────────────────────
    const PRICE_SYNONYMS = [
        'caro'      => 'muy_caro',
        'muy caro'  => 'muy_caro',
        'muycaro'   => 'muy_caro',
        'muy_caro'  => 'muy_caro',
        'accesible' => 'accesible',
        'normal'    => 'accesible',
        'barato'    => 'barato',
        'economico' => 'barato',
        'económico' => 'barato',
    ];

    // ── Generar plantilla Excel ────────────────────────────────────────────────

    public function generarPlantilla(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        // ── Hoja 1: PROVEEDORES ───────────────────────────────────────────────
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('PROVEEDORES');

        $styleHeader = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A237E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ];
        $styleRequired = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B71C1C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
        ];
        $styleNote = [
            'font' => ['italic' => true, 'color' => ['rgb' => '777777'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFDE7']],
        ];
        $styleExample = [
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F8E9']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ];

        // Fila 1: cabeceras
        $headers = [
            'A1' => ['supplier_type *', $styleRequired],
            'B1' => ['commercial_name', $styleHeader],
            'C1' => ['real_name *', $styleRequired],
            'D1' => ['contact_person', $styleHeader],
            'E1' => ['phone', $styleHeader],
            'F1' => ['email', $styleHeader],
            'G1' => ['website', $styleHeader],
            'H1' => ['catalog_url', $styleHeader],
            'I1' => ['fiscal_address', $styleHeader],
            'J1' => ['factory_address', $styleHeader],
            'K1' => ['country', $styleHeader],
            'L1' => ['district', $styleHeader],
            'M1' => ['port', $styleHeader],
            'N1' => ['moq', $styleHeader],
            'O1' => ['price_level', $styleHeader],
            'P1' => ['quality_level', $styleHeader],
            'Q1' => ['bank_detail', $styleHeader],
            'R1' => ['observations', $styleHeader],
            'S1' => ['product_categories', $styleHeader],
            'T1' => ['certifications', $styleHeader],
        ];
        foreach ($headers as $cell => [$val, $style]) {
            $ws->setCellValue($cell, $val);
            $ws->getStyle($cell)->applyFromArray($style);
        }

        // Fila 2: instrucciones
        $ws->fromArray([[
            'nacional|extranjero|importacion',
            'Nombre comercial',
            'Nombre real / razón social (obligatorio)',
            'Nombres separados por |',
            'Teléfonos separados por |',
            'correos@ejemplo.com separados por |',
            'URL con https://',
            'Enlace o nombre del catálogo',
            'Dirección fiscal u oficina',
            'Dirección de fábrica (extranjeros)',
            'País (requerido si extranjero)',
            'Distrito (requerido si nacional)',
            'Puerto de entrada (extranjero)',
            'Mínimo de pedido',
            'muy_caro|accesible|barato',
            'excelente|regular|mala',
            'Datos bancarios (importación)',
            'Observaciones libres',
            'CATEGORIA:subcategoria|CATEGORIA:subcategoria',
            'generales|por_producto|iso',
        ]], null, 'A2');
        $ws->getStyle('A2:T2')->applyFromArray($styleNote);

        // Fila 3 & 4: ejemplos
        $ws->fromArray([[
            'extranjero', 'Shenzhen Lights Co.', 'GUANGDONG LIGHTING GROUP LTD.',
            'Li Wei|Zhang Fang', '+86 755 8888 1234', 'sales@gzlighting.com',
            'https://www.gzlighting.com', 'https://catalog.gzlighting.com/2025',
            'Rm 501, Longhua District, Shenzhen', 'Factory Zone A, Nanshan',
            'China', '', 'Puerto del Callao', '500 units',
            'accesible', 'excelente', '',
            'Proveedor principal de luminarias decorativas',
            'DECORATIVAS INTERIORES:Metal|CINTA LED:Económicos|SOLARES:Flood Light',
            'generales|iso',
        ]], null, 'A3');
        $ws->fromArray([[
            'nacional', '', 'DISTRIBUIDORA LUZ Y COLOR SAC',
            'Carlos Mendoza', '01-234-5678|999888777', 'ventas@luzycolor.pe',
            'https://luzycolor.pe', '',
            'Av. Industrial 456, Ate, Lima', '',
            '', 'Ate', '', '100 und',
            'accesible', 'regular', '',
            'Distribuidor local Lima',
            'LÁMPARAS:LED|VENTILADORES:Modernos',
            '',
        ]], null, 'A4');
        $ws->getStyle('A3:T4')->applyFromArray($styleExample);

        // Ancho de columnas
        $widths = ['A'=>18,'B'=>22,'C'=>28,'D'=>22,'E'=>18,'F'=>26,'G'=>28,
                   'H'=>28,'I'=>28,'J'=>28,'K'=>12,'L'=>14,'M'=>18,'N'=>14,
                   'O'=>14,'P'=>14,'Q'=>22,'R'=>24,'S'=>40,'T'=>20];
        foreach ($widths as $col => $w) {
            $ws->getColumnDimension($col)->setWidth($w);
        }
        $ws->getRowDimension(1)->setRowHeight(28);
        $ws->getRowDimension(2)->setRowHeight(36);
        $ws->freezePane('A3');

        // ── Hoja 2: CATEGORIAS_REFERENCIA ────────────────────────────────────
        $spreadsheet->createSheet()->setTitle('CATEGORIAS_REFERENCIA');
        $ref = $spreadsheet->getSheetByName('CATEGORIAS_REFERENCIA');
        $ref->setCellValue('A1', 'CATEGORÍA');
        $ref->setCellValue('B1', 'SUBCATEGORÍAS DISPONIBLES');
        $ref->setCellValue('C1', 'USO EN PLANTILLA (columna S)');
        $refHeader = ['font' => ['bold' => true,'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID,'startColor' => ['rgb' => '1A237E']]];
        $ref->getStyle('A1:C1')->applyFromArray($refHeader);

        $fila = 2;
        $colors = ['E3F2FD','E8F5E9','FFF3E0','F3E5F5','E0F2F1','FFF8E1','FCE4EC','E8EAF6','F1F8E9','E0F7FA','FBE9E7','F9FBE7','EDE7F6'];
        $ci = 0;
        foreach (Proveedor::CATEGORIAS_PRODUCTO as $cat => $subs) {
            $color = $colors[$ci % count($colors)];
            $ref->setCellValue("A{$fila}", $cat);
            $ref->setCellValue("B{$fila}", implode(', ', $subs));
            // Ejemplo uso
            $ejemploSub = $subs[0];
            $ref->setCellValue("C{$fila}", "{$cat}:{$ejemploSub}");
            $ref->getStyle("A{$fila}:C{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
            $fila++;
            $ci++;
        }
        $ref->getColumnDimension('A')->setWidth(28);
        $ref->getColumnDimension('B')->setWidth(70);
        $ref->getColumnDimension('C')->setWidth(35);
        // Proteger hoja de referencia
        $ref->getProtection()->setSheet(true)->setPassword('kyrios2026');

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    // ── Procesar archivo Excel ─────────────────────────────────────────────────

    public function procesar(string $filePath): array
    {
        $reader = new XlsxReader();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $ws          = $spreadsheet->getSheetByName('PROVEEDORES') ?? $spreadsheet->getActiveSheet();
        $insertados  = 0;
        $actualizados = 0;
        $filas       = [];
        $filaNum     = 0;

        foreach ($ws->getRowIterator() as $row) {
            $filaNum++;
            if ($filaNum <= 2) continue; // Saltar cabecera e instrucciones

            $iter = $row->getCellIterator('A', 'T');
            $iter->setIterateOnlyExistingCells(false);
            $valores = [];
            foreach ($iter as $cell) {
                $valores[] = $cell->getValue() !== null ? trim((string) $cell->getValue()) : '';
            }

            if (empty(array_filter($valores))) continue;

            $colKeys = array_values(self::COLUMNS);
            $data    = array_combine($colKeys, array_pad($valores, count($colKeys), ''));

            $errores = $this->validar($data, $filaNum);
            if (!empty($errores)) {
                $filas[] = [
                    'fila'   => $filaNum,
                    'nombre' => $data['real_name'] ?: $data['commercial_name'] ?: '—',
                    'tipo'   => $data['supplier_type'] ?: '?',
                    'ok'     => false,
                    'accion' => null,
                    'errores'=> array_column($errores, 'razon'),
                    'advertencias' => [],
                ];
                continue;
            }

            try {
                $data   = $this->normalizar($data);
                $accion = 'creado';

                DB::transaction(function () use ($data, &$accion, &$insertados, &$actualizados) {
                    $query = Proveedor::whereRaw('LOWER(razon_social) = ?', [strtolower($data['real_name'])]);
                    if ($data['country'])  $query->where('country', $data['country']);
                    if ($data['district']) $query->where('district', $data['district']);
                    $existing = $query->first();

                    $provData = [
                        'supplier_type'   => $data['supplier_type'],
                        'razon_social'    => $data['real_name'],
                        'nombre_comercial'=> $data['commercial_name'] ?: null,
                        'contacto_nombre' => $data['contact_person']  ?: null,
                        'telefono'        => $data['phone']           ?: null,
                        'email'           => $this->primerEmail($data['email']),
                        'website'         => $data['website']         ?: null,
                        'catalog_url'     => $data['catalog_url']     ?: null,
                        'direccion'       => $data['fiscal_address']  ?: null,
                        'factory_address' => $data['factory_address'] ?: null,
                        'country'         => $data['country']         ?: null,
                        'district'        => $data['district']        ?: null,
                        'port'            => $data['port']            ?: null,
                        'moq'             => $data['moq']             ?: null,
                        'price_level'     => $data['price_level']     ?: null,
                        'quality_level'   => $data['quality_level']   ?: null,
                        'bank_detail'     => $data['bank_detail']     ?: null,
                        'observations'    => $data['observations']    ?: null,
                        'estado'          => 'activo',
                    ];

                    if ($existing) {
                        $existing->update($provData);
                        $proveedor = $existing;
                        $accion    = 'actualizado';
                        $actualizados++;
                    } else {
                        $proveedor = Proveedor::create($provData);
                        $insertados++;
                    }

                    if (!empty($data['product_categories'])) {
                        $this->guardarCategorias($proveedor, $data['product_categories']);
                    }
                    if (!empty($data['certifications'])) {
                        $this->guardarCertificaciones($proveedor, $data['certifications']);
                    }
                });

                $filas[] = [
                    'fila'         => $filaNum,
                    'nombre'       => $data['real_name'],
                    'tipo'         => $data['supplier_type'],
                    'ok'           => true,
                    'accion'       => $accion,
                    'errores'      => [],
                    'advertencias' => [],
                ];

            } catch (\Throwable $e) {
                $filas[] = [
                    'fila'         => $filaNum,
                    'nombre'       => $data['real_name'] ?? '—',
                    'tipo'         => $data['supplier_type'] ?? '?',
                    'ok'           => false,
                    'accion'       => null,
                    'errores'      => [$e->getMessage()],
                    'advertencias' => [],
                ];
            }
        }

        $erroresCsv = null;
        $filasFallidas = array_filter($filas, fn($f) => !$f['ok']);
        if (!empty($filasFallidas)) {
            $erroresCsv = $this->buildErroresCsv($filasFallidas);
        }

        return [
            'insertados'    => $insertados,
            'actualizados'  => $actualizados,
            'errores_count' => count($filasFallidas),
            'filas'         => $filas,
            'errores_csv'   => $erroresCsv,
        ];
    }

    private function buildErroresCsv(array $filasFallidas): string
    {
        $lines = ["Fila,Proveedor,Tipo,Errores"];
        foreach ($filasFallidas as $f) {
            $errores = implode(' | ', $f['errores']);
            $lines[] = implode(',', [
                $f['fila'],
                '"' . str_replace('"', '""', $f['nombre']) . '"',
                $f['tipo'],
                '"' . str_replace('"', '""', $errores) . '"',
            ]);
        }
        return implode("\n", $lines);
    }

    // ── Validaciones por fila ─────────────────────────────────────────────────

    private function validar(array $data, int $fila): array
    {
        $errores = [];

        // supplier_type obligatorio
        if (empty($data['supplier_type']) || !in_array($data['supplier_type'], ['nacional','extranjero','importacion'])) {
            $errores[] = ['fila' => $fila, 'campo' => 'supplier_type', 'razon' => 'Debe ser nacional, extranjero o importacion.'];
        }

        // real_name obligatorio
        if (empty($data['real_name'])) {
            $errores[] = ['fila' => $fila, 'campo' => 'real_name', 'razon' => 'El nombre real es obligatorio.'];
        } elseif (mb_strlen($data['real_name']) > 200) {
            $errores[] = ['fila' => $fila, 'campo' => 'real_name', 'razon' => 'Excede 200 caracteres.'];
        }

        // email (puede ser múltiple separado por |)
        if (!empty($data['email'])) {
            foreach (explode('|', $data['email']) as $mail) {
                $mail = trim($mail);
                if ($mail && !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                    $errores[] = ['fila' => $fila, 'campo' => 'email', 'razon' => "Email inválido: {$mail}"];
                }
            }
        }

        // website
        if (!empty($data['website']) && !preg_match('#^https?://#i', $data['website'])) {
            $errores[] = ['fila' => $fila, 'campo' => 'website', 'razon' => 'La URL debe iniciar con http:// o https://'];
        }

        // price_level
        if (!empty($data['price_level'])) {
            $normalized = self::PRICE_SYNONYMS[strtolower($data['price_level'])] ?? $data['price_level'];
            if (!in_array($normalized, ['muy_caro','accesible','barato'])) {
                $errores[] = ['fila' => $fila, 'campo' => 'price_level', 'razon' => "Valor no válido: {$data['price_level']}. Use: muy_caro, accesible, barato."];
            }
        }

        // quality_level
        if (!empty($data['quality_level']) && !in_array(strtolower($data['quality_level']), ['excelente','regular','mala'])) {
            $errores[] = ['fila' => $fila, 'campo' => 'quality_level', 'razon' => "Valor no válido: {$data['quality_level']}. Use: excelente, regular, mala."];
        }

        return $errores;
    }

    // ── Normalizar datos ──────────────────────────────────────────────────────

    private function normalizar(array $data): array
    {
        $data['supplier_type'] = strtolower(trim($data['supplier_type']));
        $data['real_name']     = trim($data['real_name']);
        $data['price_level']   = self::PRICE_SYNONYMS[strtolower($data['price_level'])] ?? (strtolower($data['price_level']) ?: null);
        $data['quality_level'] = strtolower($data['quality_level']) ?: null;
        return $data;
    }

    // ── Guardar categorías (formato: CAT:subcat|CAT:subcat) ───────────────────

    private function guardarCategorias(Proveedor $proveedor, string $raw): void
    {
        $validCats = Proveedor::CATEGORIAS_PRODUCTO;
        $pares = explode('|', $raw);

        foreach ($pares as $par) {
            $par = trim($par);
            if (!str_contains($par, ':')) continue;
            [$cat, $sub] = array_map('trim', explode(':', $par, 2));
            $cat = strtoupper($cat);

            // Validar contra catálogo maestro
            if (!isset($validCats[$cat])) continue;
            if (!in_array($sub, $validCats[$cat])) continue;

            ProveedorCategoriaProducto::firstOrCreate([
                'proveedor_id' => $proveedor->id,
                'categoria'    => $cat,
                'subcategoria' => $sub,
            ]);
        }
    }

    // ── Guardar certificaciones (formato: generales|por_producto|iso) ─────────

    private function guardarCertificaciones(Proveedor $proveedor, string $raw): void
    {
        $validos = ['generales', 'por_producto', 'iso'];
        foreach (explode('|', $raw) as $cert) {
            $cert = strtolower(trim($cert));
            if (!in_array($cert, $validos)) continue;
            ProveedorCertificacion::firstOrCreate([
                'proveedor_id' => $proveedor->id,
                'cert_type'    => $cert,
            ]);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function primerEmail(string $raw): ?string
    {
        if (empty($raw)) return null;
        $emails = array_map('trim', explode('|', $raw));
        $valid  = array_filter($emails, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL));
        return $valid ? array_values($valid)[0] : null;
    }
}
