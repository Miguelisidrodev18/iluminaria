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

        if ($request->filled('tipo_cliente')) {
            $query->where('tipo_cliente', $request->tipo_cliente);
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

        return view('clientes.index', compact('clientes', 'canCreate', 'canEdit', 'canDelete'));
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
        return view('clientes.create');
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
        ], [
            'apellidos.required' => 'Los apellidos son obligatorios',
            'nombres.required'   => 'Los nombres son obligatorios',
            'celular.required'   => 'El celular es obligatorio',
            'dni.unique'         => 'Este DNI ya está registrado',
            'numero_documento.unique' => 'Este documento ya está registrado',
        ]);

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
        return view('clientes.edit', compact('cliente'));
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
        ], [
            'apellidos.required' => 'Los apellidos son obligatorios',
            'nombres.required'   => 'Los nombres son obligatorios',
            'celular.required'   => 'El celular es obligatorio',
        ]);

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
            $query->where('tipo_cliente', $request->tipo_cliente);
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
            'ID', 'Tipo', 'Apellidos', 'Nombres', 'DNI', 'Celular',
            'Empresa', 'RUC', 'Correo Personal', 'Correo Empresa',
            'Ocupación', 'Especialidad', 'Comisión', 'Fecha Registro',
        ];

        foreach ($cabeceras as $col => $cab) {
            $ws->setCellValueByColumnAndRow($col + 1, 1, $cab);
        }

        foreach ($clientes as $i => $c) {
            $fila = $i + 2;
            $ws->setCellValueByColumnAndRow(1,  $fila, $c->id);
            $ws->setCellValueByColumnAndRow(2,  $fila, $c->tipo_cliente);
            $ws->setCellValueByColumnAndRow(3,  $fila, $c->apellidos);
            $ws->setCellValueByColumnAndRow(4,  $fila, $c->nombres);
            $ws->setCellValueByColumnAndRow(5,  $fila, $c->dni);
            $ws->setCellValueByColumnAndRow(6,  $fila, $c->celular);
            $ws->setCellValueByColumnAndRow(7,  $fila, $c->empresa);
            $ws->setCellValueByColumnAndRow(8,  $fila, $c->ruc);
            $ws->setCellValueByColumnAndRow(9,  $fila, $c->correo_personal);
            $ws->setCellValueByColumnAndRow(10, $fila, $c->correo_empresa);
            $ws->setCellValueByColumnAndRow(11, $fila, $c->ocupacion);
            $ws->setCellValueByColumnAndRow(12, $fila, $c->especialidad);
            $ws->setCellValueByColumnAndRow(13, $fila, $c->comision);
            $ws->setCellValueByColumnAndRow(14, $fila, $c->fecha_registro?->format('d/m/Y'));
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'clientes-' . now()->format('Y-m-d') . '.xlsx';

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
