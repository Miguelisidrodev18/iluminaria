<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Models\Cliente;
use App\Models\Visita;
use App\Models\Proyecto;
use Carbon\Carbon;

class ClientesSeeder extends Seeder
{
    public function run(): void
    {
        $ruta = storage_path('app/clientes.xlsx');

        if (!file_exists($ruta)) {
            $this->command->error("Archivo no encontrado: {$ruta}");
            $this->command->info("Copia el Excel a storage/app/clientes.xlsx y vuelve a ejecutar.");
            return;
        }

        $this->command->info("Leyendo archivo...");

        $reader = IOFactory::createReaderForFile($ruta);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($ruta);
        $hoja = $spreadsheet->getActiveSheet();
        $ultimaFila = $hoja->getHighestRow();

        $this->command->info("Filas encontradas: {$ultimaFila}. Importando desde fila 9...");

        $creados = $errores = 0;

        DB::transaction(function () use ($hoja, $ultimaFila, &$creados, &$errores) {
            for ($fila = 9; $fila <= $ultimaFila; $fila++) {
                $apellidos = $this->limpiarTexto($this->cel($hoja, 5, $fila));
                $nombres   = $this->limpiarTexto($this->cel($hoja, 6, $fila));

                if (!$apellidos && !$nombres) {
                    continue;
                }

                try {
                    $dni = $this->normalizarDocumento($this->cel($hoja, 7, $fila), 8);
                    $ruc = $this->normalizarDocumento($this->cel($hoja, 19, $fila), 11);

                    $tipoDocumento = 'DNI';
                    $numeroDocumento = $dni;

                    if (!$numeroDocumento && $ruc) {
                        $tipoDocumento = 'RUC';
                        $numeroDocumento = $ruc;
                    }

                    if (!$numeroDocumento) {
                        $numeroDocumento = sprintf('TMP%08d', $fila);
                    }

                    $clienteData = [
                        'tipo_documento'       => $tipoDocumento,
                        'numero_documento'     => $numeroDocumento,
                        'fecha_registro'       => $this->parsearFecha($this->cel($hoja, 2, $fila)),
                        'registrado_por'       => $this->cel($hoja, 3, $fila) ?: null,
                        'tipo_cliente'         => $this->normalizarTipo($this->cel($hoja, 4, $fila)),
                        'apellidos'            => $apellidos,
                        'nombres'              => $nombres,
                        'nombre'               => trim($apellidos . ' ' . $nombres),
                        'dni'                  => $dni,
                        'fecha_cumpleanos'     => $this->parsearFecha($this->cel($hoja, 8, $fila)),
                        'celular'              => $this->normalizarTelefono($this->cel($hoja, 9, $fila)),
                        'direccion_residencia' => $this->cel($hoja, 10, $fila) ?: null,
                        'telefono_casa'        => $this->normalizarTelefono($this->cel($hoja, 11, $fila)),
                        'correo_personal'      => $this->cel($hoja, 12, $fila) ?: null,
                        'ocupacion'            => $this->cel($hoja, 13, $fila) ?: null,
                        'especialidad'         => $this->cel($hoja, 14, $fila) ?: null,
                        'empresa'              => $this->cel($hoja, 18, $fila) ?: null,
                        'ruc'                  => $ruc,
                        'correo_empresa'       => $this->cel($hoja, 20, $fila) ?: null,
                        'direccion_empresa'    => $this->cel($hoja, 21, $fila) ?: null,
                        'telefono_empresa'     => $this->normalizarTelefono($this->cel($hoja, 22, $fila)),
                        'comision'             => $this->parsearDecimal($this->cel($hoja, 26, $fila)),
                        'preferencias'         => $this->cel($hoja, 27, $fila) ?: null,
                        'estado'               => 'activo',
                    ];

                    // Crea o actualiza el cliente para re-ejecutar sin duplicados.
                    $cliente = Cliente::updateOrCreate(
                        ['numero_documento' => $numeroDocumento],
                        $clienteData
                    );

                    // ---- VISITA (solo si hay fecha_visita) ----
                    $fechaVisita = $this->parsearFecha($this->cel($hoja, 29, $fila));
                    if ($fechaVisita) {
                        $visitaData = [
                            'cliente_id'             => $cliente->id,
                            'atendido_por'           => $this->cel($hoja, 28, $fila) ?: null,
                            'fecha_visita'           => $fechaVisita,
                            'hora_atencion'          => $this->parsearHora($this->cel($hoja, 30, $fila)),
                            'monto_presup_soles'     => $this->parsearDecimal($this->cel($hoja, 31, $fila)),
                            'monto_presup_dolares'   => $this->parsearDecimal($this->cel($hoja, 33, $fila)),
                            'monto_comprado_soles'   => $this->parsearDecimal($this->cel($hoja, 35, $fila)),
                            'monto_comprado_dolares' => $this->parsearDecimal($this->cel($hoja, 37, $fila)),
                            'observaciones'          => $this->cel($hoja, 38, $fila) ?: null,
                            'resumen_visita'         => $this->cel($hoja, 39, $fila) ?: null,
                            'probabilidad_venta'     => (int) ($this->cel($hoja, 40, $fila) ?? 0),
                            'medio_contacto'         => $this->cel($hoja, 41, $fila) ?: null,
                        ];

                        // Evita duplicar visitas al re-ejecutar el seeder.
                        Visita::firstOrCreate($visitaData);
                    }

                    // ---- PROYECTO (solo si hay id_proyecto) ----
                    $idProyecto = trim((string) $this->cel($hoja, 42, $fila));
                    if ($idProyecto) {
                        Proyecto::firstOrCreate(['id_proyecto' => $idProyecto], [
                            'cliente_id'          => $cliente->id,
                            'persona_cargo'       => $this->cel($hoja, 43, $fila) ?: null,
                            'prioridad'           => $this->normalizarPrioridad($this->cel($hoja, 44, $fila)),
                            'nombre_proyecto'     => strtoupper(trim((string) $this->cel($hoja, 45, $fila))) ?: 'SIN NOMBRE',
                            'fecha_recepcion'     => $this->parsearFecha($this->cel($hoja, 46, $fila)),
                            'fecha_entrega_aprox' => $this->parsearFecha($this->cel($hoja, 47, $fila)),
                            'max_revisiones'      => $this->parsearEnteroRango($this->cel($hoja, 50, $fila), 0, 127, 3),
                            'fecha_entrega_real'  => $this->parsearFecha($this->cel($hoja, 51, $fila)),
                            'monto_presup_proy'   => $this->parsearDecimal($this->cel($hoja, 52, $fila)),
                            'monto_vendido_proy'  => $this->parsearDecimal($this->cel($hoja, 53, $fila)),
                            'centro_costos'       => $this->cel($hoja, 54, $fila) ?: null,
                            'resultado'           => $this->normalizarResultado($this->cel($hoja, 55, $fila)),
                            'seguimiento'         => $this->cel($hoja, 56, $fila) ?: null,
                        ]);
                    }

                    $creados++;
                } catch (\Throwable $e) {
                    $errores++;
                    $this->command->warn("Fila {$fila}: " . $e->getMessage());
                }
            }
        });

        $this->command->info("Importación completada: {$creados} clientes creados, {$errores} errores.");
    }

    /** Obtiene el valor de una celda usando índice numérico de columna (A=1) */
    private function cel($hoja, int $col, int $fila): mixed
    {
        return $hoja->getCell(Coordinate::stringFromColumnIndex($col) . $fila)->getValue();
    }

    private function normalizarDocumento(mixed $valor, int $longitud): ?string
    {
        $soloDigitos = preg_replace('/\D+/', '', (string) $valor) ?? '';

        if (strlen($soloDigitos) !== $longitud) {
            return null;
        }

        return $soloDigitos;
    }

    private function limpiarTexto($valor): string
    {
        return strtoupper(trim((string) $valor));
    }

    private function parsearFecha($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        if (is_numeric($valor)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $valor)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }
        try {
            return Carbon::parse((string) $valor)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parsearHora($valor): ?string
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        if (is_numeric($valor)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $valor)->format('H:i:s');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $texto = strtoupper(trim((string) $valor));
        $texto = str_replace(['A.M.', 'P.M.'], ['AM', 'PM'], $texto);
        $texto = preg_replace('/(\d)\.(\d{2})/', '$1:$2', $texto) ?? $texto;
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;
        $texto = preg_replace('/\s*(AM|PM)$/', ' $1', $texto) ?? $texto;

        try {
            return Carbon::parse($texto)->format('H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parsearDecimal($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0.0;
        }
        return (float) str_replace(',', '.', (string) $valor);
    }

    private function parsearEnteroRango(mixed $valor, int $min, int $max, int $porDefecto): int
    {
        if (!is_numeric($valor)) {
            return $porDefecto;
        }

        $numero = (int) $valor;
        if ($numero < $min || $numero > $max) {
            return $porDefecto;
        }

        return $numero;
    }

    private function normalizarTelefono(mixed $valor, int $max = 20): ?string
    {
        $texto = trim((string) $valor);
        if ($texto === '') {
            return null;
        }

        return substr($texto, 0, $max);
    }

    private function normalizarTipo($valor): ?string
    {
        $v = strtoupper(trim((string) $valor));
        return in_array($v, ['ARQ', 'ING', 'DIS', 'PN', 'PJ']) ? $v : null;
    }

    private function normalizarPrioridad($valor): string
    {
        $v = strtoupper(trim((string) $valor));
        return in_array($v, ['A', 'M', 'B']) ? $v : 'M';
    }

    private function normalizarResultado($valor): ?string
    {
        $v = strtoupper(trim((string) $valor));
        return in_array($v, ['G', 'P', 'EP', 'ENT', 'ENV', 'I']) ? $v : null;
    }
}
