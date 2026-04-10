<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Visita;
use App\Services\SunatService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::withCount(['visitas', 'proyectos'])
            ->with(['visitas' => fn ($q) => $q->latest('fecha_visita')->limit(1)]);

        // El select unificado usa prefijo "etq:" para etiquetas, o el código directo para tipo_cliente
        if ($request->filled('tipo_cliente')) {
            $segmento = $request->tipo_cliente;
            if (str_starts_with($segmento, 'etq:')) {
                $query->conEtiqueta(substr($segmento, 4));
            } else {
                $query->where('tipo_cliente', $segmento);
            }
        }

        // Compatibilidad con chips de etiqueta directa (URL: ?etiqueta=Mamá)
        if ($request->filled('etiqueta') && !$request->filled('tipo_cliente')) {
            $query->conEtiqueta($request->etiqueta);
        }

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(fn ($w) => $w
                ->where('apellidos', 'like', "%{$b}%")
                ->orWhere('nombres', 'like', "%{$b}%")
                ->orWhere('nombre', 'like', "%{$b}%")
                ->orWhere('empresa', 'like', "%{$b}%")
                ->orWhere('dni', 'like', "%{$b}%")
            );
        }

        $clientes   = $query->orderBy('apellidos')->paginate(15)->withQueryString();
        $canCreate  = in_array(auth()->user()->role->nombre, ['Administrador', 'Vendedor', 'Tienda']);
        $canEdit    = in_array(auth()->user()->role->nombre, ['Administrador', 'Vendedor', 'Tienda']);
        $canDelete  = auth()->user()->role->nombre === 'Administrador';
        $etiquetas  = array_keys(Cliente::ETIQUETAS_DISPONIBLES);

        return view('clientes.index', compact('clientes', 'canCreate', 'canEdit', 'canDelete', 'etiquetas'));
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'visitas'  => fn ($q) => $q->latest('fecha_visita')->limit(5),
            'proyectos' => fn ($q) => $q->latest(),
        ]);

        return view('clientes.show', compact('cliente'));
    }

    public function create()
    {
        $etiquetasDisponibles = Cliente::ETIQUETAS_DISPONIBLES;
        return view('clientes.create', compact('etiquetasDisponibles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Legacy
            'tipo_documento'   => 'nullable|in:DNI,RUC,CE',
            'numero_documento' => 'nullable|string|max:15|unique:clientes,numero_documento',
            'nombre'           => 'nullable|string|max:255',
            'direccion'        => 'nullable|string|max:255',
            'telefono'         => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255',
            'estado'           => 'required|in:activo,inactivo',
            // Nuevos — requeridos
            'apellidos'        => 'required|string|max:150',
            'nombres'          => 'required|string|max:150',
            'celular'          => 'required|string|max:20',
            // Nuevos — opcionales
            'tipo_cliente'     => 'nullable|in:ARQ,ING,DIS,PN,PJ',
            'dni'              => 'nullable|string|max:20|unique:clientes,dni',
            'fecha_registro'   => 'nullable|date',
            'registrado_por'   => 'nullable|string|max:100',
            'fecha_cumpleanos' => 'nullable|date',
            'direccion_residencia' => 'nullable|string|max:255',
            'telefono_casa'    => 'nullable|string|max:20',
            'correo_personal'  => 'nullable|email|max:150',
            'ocupacion'        => 'nullable|string|max:100',
            'especialidad'     => 'nullable|string|max:100',
            'redes_personales' => 'nullable|string',
            'empresa'          => 'nullable|string|max:200',
            'ruc'              => 'nullable|string|max:20',
            'correo_empresa'   => 'nullable|email|max:150',
            'direccion_empresa' => 'nullable|string|max:255',
            'telefono_empresa' => 'nullable|string|max:20',
            'redes_empresa'    => 'nullable|string',
            'comision'         => 'nullable|numeric|min:0|max:100',
            'preferencias'     => 'nullable|string',
            'etiquetas'        => 'nullable|array',
            'etiquetas.*'      => 'string|in:' . implode(',', array_keys(Cliente::ETIQUETAS_DISPONIBLES)),
            'acepta_whatsapp'  => 'boolean',
        ], [
            'apellidos.required' => 'Los apellidos son obligatorios',
            'nombres.required'   => 'Los nombres son obligatorios',
            'celular.required'   => 'El celular es obligatorio',
            'dni.unique'         => 'Este DNI ya está registrado',
            'numero_documento.unique' => 'Este documento ya está registrado',
        ]);

        $validated['acepta_whatsapp'] = $request->boolean('acepta_whatsapp', true);

        // Sincronizar nombre legacy
        if (empty($validated['nombre'])) {
            $validated['nombre'] = trim(($validated['apellidos'] ?? '') . ' ' . ($validated['nombres'] ?? ''));
        }

        $cliente = Cliente::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['id' => $cliente->id, 'nombre' => $cliente->nombre_completo]);
        }

        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente registrado exitosamente');
    }

    public function edit(Cliente $cliente)
    {
        $etiquetasDisponibles = Cliente::ETIQUETAS_DISPONIBLES;
        return view('clientes.edit', compact('cliente', 'etiquetasDisponibles'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'tipo_documento'   => 'nullable|in:DNI,RUC,CE',
            'numero_documento' => 'nullable|string|max:15|unique:clientes,numero_documento,' . $cliente->id,
            'nombre'           => 'nullable|string|max:255',
            'direccion'        => 'nullable|string|max:255',
            'telefono'         => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255',
            'estado'           => 'required|in:activo,inactivo',
            'apellidos'        => 'required|string|max:150',
            'nombres'          => 'required|string|max:150',
            'celular'          => 'required|string|max:20',
            'tipo_cliente'     => 'nullable|in:ARQ,ING,DIS,PN,PJ',
            'dni'              => 'nullable|string|max:20|unique:clientes,dni,' . $cliente->id,
            'fecha_registro'   => 'nullable|date',
            'registrado_por'   => 'nullable|string|max:100',
            'fecha_cumpleanos' => 'nullable|date',
            'direccion_residencia' => 'nullable|string|max:255',
            'telefono_casa'    => 'nullable|string|max:20',
            'correo_personal'  => 'nullable|email|max:150',
            'ocupacion'        => 'nullable|string|max:100',
            'especialidad'     => 'nullable|string|max:100',
            'redes_personales' => 'nullable|string',
            'empresa'          => 'nullable|string|max:200',
            'ruc'              => 'nullable|string|max:20',
            'correo_empresa'   => 'nullable|email|max:150',
            'direccion_empresa' => 'nullable|string|max:255',
            'telefono_empresa' => 'nullable|string|max:20',
            'redes_empresa'    => 'nullable|string',
            'comision'         => 'nullable|numeric|min:0|max:100',
            'preferencias'     => 'nullable|string',
            'etiquetas'        => 'nullable|array',
            'etiquetas.*'      => 'string|in:' . implode(',', array_keys(Cliente::ETIQUETAS_DISPONIBLES)),
            'acepta_whatsapp'  => 'boolean',
        ], [
            'apellidos.required' => 'Los apellidos son obligatorios',
            'nombres.required'   => 'Los nombres son obligatorios',
            'celular.required'   => 'El celular es obligatorio',
        ]);

        $validated['acepta_whatsapp'] = $request->boolean('acepta_whatsapp', true);

        // Sincronizar nombre legacy
        $validated['nombre'] = trim(($validated['apellidos'] ?? '') . ' ' . ($validated['nombres'] ?? ''));

        $cliente->update($validated);

        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete(); // SoftDelete
        return redirect()->route('clientes.index')->with('success', 'Cliente archivado exitosamente');
    }

    public function storeVisita(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'fecha_visita'           => 'required|date',
            'atendido_por'           => 'nullable|string|max:100',
            'hora_atencion'          => 'nullable|date_format:H:i',
            'monto_presup_soles'     => 'nullable|numeric|min:0',
            'monto_presup_dolares'   => 'nullable|numeric|min:0',
            'monto_comprado_soles'   => 'nullable|numeric|min:0',
            'monto_comprado_dolares' => 'nullable|numeric|min:0',
            'observaciones'          => 'nullable|string',
            'resumen_visita'         => 'nullable|string',
            'probabilidad_venta'     => 'nullable|integer|between:0,100',
            'medio_contacto'         => 'nullable|string|max:100',
        ]);

        $cliente->visitas()->create($validated);

        return redirect()->route('clientes.show', $cliente)->with('success', 'Visita registrada');
    }

    public function exportar(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('tipo_cliente')) {
            $segmento = $request->tipo_cliente;
            if (str_starts_with($segmento, 'etq:')) {
                $query->conEtiqueta(substr($segmento, 4));
            } else {
                $query->where('tipo_cliente', $segmento);
            }
        }
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(fn ($w) => $w
                ->where('apellidos', 'like', "%{$b}%")
                ->orWhere('nombres', 'like', "%{$b}%")
                ->orWhere('empresa', 'like', "%{$b}%")
            );
        }

        $clientes = $query->orderBy('apellidos')->get();

        $spreadsheet = new Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('Clientes');

        $cabeceras = [
            'A' => 'ID',             'B' => 'Tipo',        'C' => 'Apellidos',
            'D' => 'Nombres',        'E' => 'DNI',         'F' => 'Celular',
            'G' => 'Empresa',        'H' => 'RUC',         'I' => 'Correo Personal',
            'J' => 'Correo Empresa', 'K' => 'Ocupación',   'L' => 'Especialidad',
            'M' => 'Etiquetas',      'N' => 'Acepta WhatsApp', 'O' => 'Comisión', 'P' => 'Fecha Registro',
        ];

        foreach ($cabeceras as $col => $cab) {
            $ws->setCellValue($col . '1', $cab);
        }

        foreach ($clientes as $i => $c) {
            $row = $i + 2;
            $ws->setCellValue("A{$row}", $c->id);
            $ws->setCellValue("B{$row}", $c->tipo_cliente);
            $ws->setCellValue("C{$row}", $c->apellidos);
            $ws->setCellValue("D{$row}", $c->nombres);
            $ws->setCellValue("E{$row}", $c->dni);
            $ws->setCellValue("F{$row}", $c->celular);
            $ws->setCellValue("G{$row}", $c->empresa);
            $ws->setCellValue("H{$row}", $c->ruc);
            $ws->setCellValue("I{$row}", $c->correo_personal);
            $ws->setCellValue("J{$row}", $c->correo_empresa);
            $ws->setCellValue("K{$row}", $c->ocupacion);
            $ws->setCellValue("L{$row}", $c->especialidad);
            $ws->setCellValue("M{$row}", implode(', ', $c->etiquetas ?? []));
            $ws->setCellValue("N{$row}", $c->acepta_whatsapp ? 'Sí' : 'No');
            $ws->setCellValue("O{$row}", $c->comision);
            $ws->setCellValue("P{$row}", $c->fecha_registro?->format('d/m/Y'));
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'clientes-' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Vista de listas de difusión por etiqueta para WhatsApp.
     */
    public function difusion(Request $request)
    {
        $etiquetasDisponibles = Cliente::ETIQUETAS_DISPONIBLES;
        $etiquetaSeleccionada = $request->input('etiqueta');

        $clientes = collect();
        $total    = 0;

        if ($etiquetaSeleccionada) {
            $clientes = Cliente::conEtiqueta($etiquetaSeleccionada)
                ->paraWhatsapp()
                ->orderBy('apellidos')
                ->get(['id', 'apellidos', 'nombres', 'celular', 'correo_personal', 'empresa', 'etiquetas']);

            $total = $clientes->count();
        }

        return view('clientes.difusion', compact('etiquetasDisponibles', 'etiquetaSeleccionada', 'clientes', 'total'));
    }

    /**
     * Exporta lista de difusión WhatsApp (número + nombre) filtrada por etiqueta.
     */
    public function exportarWhatsapp(Request $request)
    {
        $request->validate([
            'etiqueta' => 'required|string|in:' . implode(',', array_keys(Cliente::ETIQUETAS_DISPONIBLES)),
        ]);

        $clientes = Cliente::conEtiqueta($request->etiqueta)
            ->paraWhatsapp()
            ->orderBy('apellidos')
            ->get(['apellidos', 'nombres', 'celular', 'empresa']);

        $spreadsheet = new Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('Difusión WhatsApp');

        $ws->setCellValue('A1', 'Nombre Completo');
        $ws->setCellValue('B1', 'Celular');
        $ws->setCellValue('C1', 'Empresa');

        foreach ($clientes as $i => $c) {
            $row = $i + 2;
            $ws->setCellValue("A{$row}", strtoupper($c->apellidos) . ' ' . $c->nombres);
            $ws->setCellValue("B{$row}", $c->celular);
            $ws->setCellValue("C{$row}", $c->empresa);
        }

        $writer   = new Xlsx($spreadsheet);
        $etiqueta = str_replace(['/', ' '], '-', $request->etiqueta);
        $filename = 'difusion-' . $etiqueta . '-' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function consultarDocumento(Request $request)
    {
        $tipo   = strtoupper($request->input('tipo', 'DNI'));
        $numero = trim($request->input('numero', ''));
        $sunat  = app(SunatService::class);

        if ($tipo === 'RUC') {
            $result = $sunat->consultarRuc($numero);
            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['message']], 422);
            }
            return response()->json([
                'success' => true,
                'data' => [
                    'ruc'          => $result['data']['ruc']          ?? $numero,
                    'razon_social' => $result['data']['razon_social'] ?? '',
                    'nombre'       => $result['data']['razon_social'] ?? '',
                    'direccion'    => $result['data']['direccion']    ?? '',
                ],
            ]);
        }

        $result = $sunat->consultarDni($numero);
        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'dni'    => $result['data']['dni']    ?? $numero,
                'nombre' => $result['data']['nombre'] ?? '',
            ],
        ]);
    }
}
