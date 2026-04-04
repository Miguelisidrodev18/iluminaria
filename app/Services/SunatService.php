<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SunatService
{
    protected const RUC_URL = 'https://api.apis.net.pe/v1/ruc';
    protected const DNI_URL = 'https://api.apis.net.pe/v1/dni';

    protected function token(): string
    {
        return env('APIS_NET_PE_TOKEN', '');
    }

    protected function httpClient(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout(10)->withHeaders([
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $this->token(),
        ]);
    }

    public function consultarRuc(string $ruc): array
    {
        if (strlen($ruc) !== 11 || !ctype_digit($ruc)) {
            return ['success' => false, 'message' => 'El RUC debe tener 11 dígitos numéricos.'];
        }

        $cacheKey = "sunat_ruc_{$ruc}";

        // Solo cachear resultados exitosos
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = $this->httpClient()->get(self::RUC_URL, ['numero' => $ruc]);

            if ($response->successful()) {
                $data = $response->json();

                if (empty($data['numeroDocumento']) && empty($data['nombre'])) {
                    return ['success' => false, 'message' => 'RUC no encontrado en SUNAT.'];
                }

                $partesDireccion = array_filter([
                    $data['direccion']    ?? null,
                    $data['distrito']     ?? null,
                    $data['provincia']    ?? null,
                    $data['departamento'] ?? null,
                ], fn($v) => $v && $v !== '-');

                $resultado = [
                    'success' => true,
                    'data' => [
                        'ruc'              => $data['numeroDocumento'] ?? $ruc,
                        'razon_social'     => $data['nombre']          ?? '',
                        'nombre_comercial' => null,
                        'direccion'        => implode(', ', $partesDireccion) ?: null,
                        'estado'           => $data['estado']          ?? null,
                        'condicion'        => $data['condicion']       ?? null,
                        'tipo'             => $data['tipoDocumento']   ?? null,
                    ],
                ];

                Cache::put($cacheKey, $resultado, 604800);
                return $resultado;
            }

            $status = $response->status();
            return ['success' => false, 'message' => $status === 404
                ? 'RUC no encontrado en SUNAT.'
                : "Error al consultar SUNAT (HTTP {$status})."];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'No se pudo conectar al servicio de consulta RUC.'];
        }
    }

    public function consultarDni(string $dni): array
    {
        if (strlen($dni) !== 8 || !ctype_digit($dni)) {
            return ['success' => false, 'message' => 'El DNI debe tener 8 dígitos numéricos.'];
        }

        $cacheKey = "sunat_dni_{$dni}";

        // Solo cachear resultados exitosos
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = $this->httpClient()->get(self::DNI_URL, ['numero' => $dni]);

            if ($response->successful()) {
                $data = $response->json();

                if (empty($data['numeroDocumento']) && empty($data['nombre'])) {
                    return ['success' => false, 'message' => 'DNI no encontrado en RENIEC.'];
                }

                // Preferir campo 'nombre' completo; si no, construirlo desde partes
                $nombre = !empty($data['nombre'])
                    ? $data['nombre']
                    : trim(implode(' ', array_filter([
                        $data['apellidoPaterno'] ?? null,
                        $data['apellidoMaterno'] ?? null,
                        $data['nombres']         ?? null,
                    ])));

                $resultado = [
                    'success' => true,
                    'data' => [
                        'dni'    => $data['numeroDocumento'] ?? $dni,
                        'nombre' => $nombre,
                    ],
                ];

                Cache::put($cacheKey, $resultado, 604800);
                return $resultado;
            }

            $status = $response->status();
            return ['success' => false, 'message' => $status === 404
                ? 'DNI no encontrado en RENIEC.'
                : "Error al consultar RENIEC (HTTP {$status})."];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'No se pudo conectar al servicio de consulta DNI.'];
        }
    }
}
