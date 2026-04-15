<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\GuiaRemision;
use App\Models\SerieComprobante;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuiaRemisionService
{
    protected ?Empresa $empresa = null;

    public function __construct()
    {
        $this->empresa = Empresa::query()->first();
    }

    /**
     * Obtener la serie activa de guia para una sucursal.
     */
    public function serieGuia(int $sucursalId): ?SerieComprobante
    {
        return SerieComprobante::where('sucursal_id', $sucursalId)
            ->where('tipo_comprobante', '09')
            ->where('activo', true)
            ->first();
    }

    /**
     * Reservar el siguiente correlativo de una serie dentro de una transaccion.
     */
    public function reservarCorrelativo(int $serieComprobanteId): int
    {
        $serie = SerieComprobante::query()->lockForUpdate()->find($serieComprobanteId);

        if (!$serie) {
            throw new \RuntimeException('No se encontro la serie de comprobante.');
        }

        $correlativo = $serie->correlativo_actual;
        $serie->increment('correlativo_actual');

        return $correlativo;
    }

    /**
     * Asignar correlativo y guardar la guia con numero definitivo.
     */
    public function asignarCorrelativo(GuiaRemision $guia): void
    {
        if (!$guia->serie_comprobante_id) {
            throw new \RuntimeException('No se encontro la serie de comprobante.');
        }

        $guia->correlativo = $this->reservarCorrelativo($guia->serie_comprobante_id);
        $guia->save();
    }

    /**
     * Enviar guia a SUNAT via API externa configurada en la empresa.
     */
    public function enviarASunat(GuiaRemision $guia): array
    {
        if (!$this->empresa) {
            return ['success' => false, 'message' => 'No hay empresa configurada.'];
        }

        $apiUrl = rtrim($this->empresa->api_url ?? '', '/');
        $apiKey = $this->empresa->api_key ?? '';

        if ($apiUrl === '' || $apiKey === '') {
            return ['success' => false, 'message' => 'API URL o API Key no configurados en la empresa.'];
        }

        try {
            $payload = $this->buildPayload($guia);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl . '/guia', $payload);

            $data = $response->json();

            if ($response->successful() && ($data['aceptada'] ?? false)) {
                $guia->update([
                    'estado' => 'aceptado',
                    'sunat_respuesta' => $data,
                    'sunat_hash' => $data['hash'] ?? null,
                    'sunat_enlace_pdf' => $data['enlace_del_pdf'] ?? null,
                    'sunat_enlace_xml' => $data['enlace_del_xml'] ?? null,
                    'sunat_enlace_cdr' => $data['enlace_del_cdr'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message' => 'Guia enviada y aceptada por SUNAT.',
                    'data' => $data,
                ];
            }

            $mensaje = $data['errors'][0] ?? ($data['message'] ?? 'Respuesta inesperada de SUNAT.');
            $guia->update([
                'estado' => 'rechazado',
                'sunat_respuesta' => $data,
            ]);

            return ['success' => false, 'message' => $mensaje, 'data' => $data];
        } catch (\Throwable $e) {
            Log::error('GuiaRemisionService::enviarASunat', [
                'guia_id' => $guia->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Error de conexion con la API: ' . $e->getMessage()];
        }
    }

    /**
     * Construir el payload para la API de facturacion electronica.
     */
    public function buildPayload(GuiaRemision $guia): array
    {
        $guia->load(['serieComprobante', 'detalles.producto', 'cliente']);

        if (!$this->empresa) {
            throw new \RuntimeException('No hay empresa configurada.');
        }

        $empresa = $this->empresa;

        $destTipoDoc = $guia->destinatario_tipo_doc ?? '1';
        $destNumDoc = $guia->destinatario_num_doc ?? '';
        $destNombre = $guia->destinatario_nombre ?? '';

        if ($guia->cliente && empty($destNumDoc)) {
            $destTipoDoc = match ($guia->cliente->tipo_documento ?? 'DNI') {
                'RUC' => '6',
                'DNI' => '1',
                'CE' => '4',
                'PAS' => '7',
                default => '1',
            };
            $destNumDoc = $guia->cliente->numero_documento ?? '';
            $destNombre = $guia->cliente->nombre ?? '';
        }

        $payload = [
            'operacion' => 'generar_guia',
            'tipo_de_comprobante' => 9,
            'serie' => $guia->serieComprobante->serie,
            'numero' => $guia->correlativo,
            'sunat_transaction' => 1,
            'emisor_tipo_de_documento' => 6,
            'emisor_numero_de_documento' => $empresa->ruc,
            'emisor_denominacion' => $empresa->razon_social,
            'emisor_direccion' => $empresa->direccion,
            'emisor_ubigeo' => $empresa->ubigeo,
            'cliente_tipo_de_documento' => ctype_digit((string) $destTipoDoc) ? (int) $destTipoDoc : $destTipoDoc,
            'cliente_numero_de_documento' => $destNumDoc,
            'cliente_denominacion' => $destNombre,
            'cliente_direccion' => $guia->destinatario_direccion ?? '',
            'fecha_de_emision' => $guia->fecha_emision->format('Y-m-d'),
            'fecha_de_traslado' => $guia->fecha_traslado->format('Y-m-d'),
            'motivo_de_traslado' => $guia->motivo_traslado,
            'descripcion_motivo_traslado' => GuiaRemision::MOTIVOS[$guia->motivo_traslado] ?? '',
            'modalidad_de_transporte' => $guia->modalidad_transporte,
            'peso_bruto_total' => (float) ($guia->peso_bruto ?? 0),
            'numero_de_bultos' => (int) ($guia->numero_bultos ?? 1),
            'punto_de_partida' => [
                'ubigeo' => $guia->partida_ubigeo ?? '',
                'direccion' => $guia->partida_direccion ?? '',
            ],
            'punto_de_llegada' => [
                'ubigeo' => $guia->llegada_ubigeo ?? '',
                'direccion' => $guia->llegada_direccion ?? '',
            ],
            'observaciones' => $guia->observaciones ?? '',
            'items' => $guia->detalles->map(function ($detalle) {
                return [
                    'unidad_de_medida' => $detalle->unidad_medida,
                    'codigo' => $detalle->codigo ?? ($detalle->producto->codigo ?? ''),
                    'descripcion' => $detalle->descripcion,
                    'cantidad' => (float) $detalle->cantidad,
                ];
            })->values()->toArray(),
        ];

        if ($guia->modalidad_transporte === '01') {
            $payload['transportista'] = [
                'tipo_de_documento' => (int) ($guia->conductor_tipo_doc ?? 1),
                'numero_de_documento' => $guia->conductor_num_doc ?? '',
                'denominacion' => $guia->conductor_nombre ?? '',
                'placa_del_vehiculo' => $guia->placa_vehiculo ?? '',
                'numero_de_licencia' => $guia->conductor_licencia ?? '',
            ];
        } else {
            $payload['transportista'] = [
                'tipo_de_documento' => 6,
                'numero_de_documento' => $guia->transportista_ruc ?? '',
                'denominacion' => $guia->transportista_nombre ?? '',
            ];
        }

        return $payload;
    }

    /**
     * Anular guia localmente.
     */
    public function anular(GuiaRemision $guia, string $motivo = ''): array
    {
        if (!$guia->puede_anularse) {
            return ['success' => false, 'message' => 'La guia no se puede anular en su estado actual.'];
        }

        $guia->update([
            'estado' => 'anulado',
            'observaciones' => ($guia->observaciones ? $guia->observaciones . ' | ' : '') . 'ANULADO: ' . $motivo,
        ]);

        return ['success' => true, 'message' => 'Guia anulada correctamente.'];
    }
}
