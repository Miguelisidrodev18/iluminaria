<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Administrador');
    }

    /**
     * Consultar datos de empresa por RUC via apis.net.pe (acceso público v1).
     */
    public function consultarRuc(string $ruc)
    {
        if (!preg_match('/^\d{11}$/', $ruc)) {
            return response()->json(['error' => 'RUC inválido'], 422);
        }

        $url = 'https://api.apis.net.pe/v1/ruc?numero=' . $ruc;

        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(10)
                ->get($url);
            $data = $response->json();
        } catch (\Throwable $e) {
            // Fallback: file_get_contents para hostings con curl restringido
            $ctx = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'header'  => "Accept: application/json\r\n",
                    'timeout' => 10,
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);
            $raw = @file_get_contents($url, false, $ctx);
            if ($raw === false) {
                return response()->json(['error' => 'No se pudo conectar con el servicio SUNAT.'], 502);
            }
            $data = json_decode($raw, true);
        }

        if (empty($data) || isset($data['error'])) {
            return response()->json(['error' => 'RUC no encontrado o sin datos en SUNAT.'], 404);
        }

        return response()->json([
            'ruc'              => $data['numeroDocumento'] ?? $ruc,
            'razon_social'     => $data['nombre']          ?? '',
            'nombre_comercial' => '',
            'direccion'        => $data['direccion']       ?? '',
            'departamento'     => $data['departamento']    ?? '',
            'provincia'        => $data['provincia']       ?? '',
            'distrito'         => $data['distrito']        ?? '',
            'ubigeo'           => $data['ubigeo']          ?? '',
            'estado'           => $data['estado']          ?? '',
            'condicion'        => $data['condicion']       ?? '',
        ]);
    }

    /**
     * Probar conexión con el API de facturación electrónica configurado.
     */
    public function testApi()
    {
        $empresa = Empresa::instancia();

        if (!$empresa || empty($empresa->api_url)) {
            return response()->json(['success' => false, 'message' => 'No hay URL de API configurada. Guarda los datos primero.']);
        }

        if (empty($empresa->api_key)) {
            return response()->json(['success' => false, 'message' => 'No hay API Key configurada. Guarda los datos primero.']);
        }

        $url = rtrim($empresa->api_url, '/');

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $empresa->api_key,
                    'Accept'        => 'application/json',
                ])
                ->get($url . '/status');

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Conexión exitosa con el servidor de facturación (' . $response->status() . ').']);
            }

            // Algunos proveedores responden 200 solo en /ping o el root, probamos el root
            $response2 = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $empresa->api_key,
                    'Accept'        => 'application/json',
                ])
                ->get($url);

            if ($response2->status() < 500) {
                return response()->json(['success' => true, 'message' => 'API alcanzable (HTTP ' . $response2->status() . '). Credenciales enviadas correctamente.']);
            }

            return response()->json(['success' => false, 'message' => 'El servidor respondió con error HTTP ' . $response->status() . '. Verifica la URL y el token.']);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'No se pudo conectar: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar formulario de configuración (singleton).
     */
    public function edit()
    {
        $empresa = Empresa::first() ?? new Empresa();
        return view('admin.empresa.edit', compact('empresa'));
    }

    /**
     * Guardar / actualizar los datos de la empresa.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'ruc'               => 'required|digits:11',
            'razon_social'      => 'required|string|max:200',
            'nombre_comercial'  => 'nullable|string|max:200',
            'direccion'         => 'nullable|string|max:300',
            'ubigeo'            => 'nullable|string|max:6',
            'departamento'      => 'nullable|string|max:100',
            'provincia'         => 'nullable|string|max:100',
            'distrito'          => 'nullable|string|max:100',
            'regimen'           => 'required|in:RER,RG,RMT,RUS',
            'telefono'          => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:150',
            'web'               => 'nullable|url|max:200',
            'facebook'          => 'nullable|string|max:200',
            'instagram'         => 'nullable|string|max:200',
            'tiktok'            => 'nullable|string|max:200',
            'sunat_usuario_sol' => 'nullable|string|max:100',
            'sunat_clave_sol'   => 'nullable|string|max:100',
            'sunat_modo'        => 'nullable|in:beta,produccion',
            'api_url'           => 'nullable|url|max:300',
            'api_key'           => 'nullable|string|max:300',
            'logo'              => 'nullable|image|max:2048',
            'logo_pdf'          => 'nullable|image|max:2048',
        ], [
            'ruc.digits'           => 'El RUC debe tener exactamente 11 dígitos',
            'razon_social.required'=> 'La razón social es obligatoria',
        ]);

        $empresa = Empresa::first() ?? new Empresa();

        // Subir logos
        if ($request->hasFile('logo')) {
            if ($empresa->logo_path) Storage::disk('public')->delete($empresa->logo_path);
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }
        if ($request->hasFile('logo_pdf')) {
            if ($empresa->logo_pdf_path) Storage::disk('public')->delete($empresa->logo_pdf_path);
            $validated['logo_pdf_path'] = $request->file('logo_pdf')->store('logos', 'public');
        }

        // No sobrescribir campos sensibles si están vacíos
        if (empty($validated['sunat_clave_sol'])) unset($validated['sunat_clave_sol']);
        if (empty($validated['api_key'])) unset($validated['api_key']);

        unset($validated['logo'], $validated['logo_pdf']);

        if ($empresa->exists) {
            $empresa->update($validated);
        } else {
            Empresa::create($validated);
        }

        return redirect()->route('admin.empresa.edit')
            ->with('success', 'Datos de la empresa actualizados correctamente.');
    }
}
