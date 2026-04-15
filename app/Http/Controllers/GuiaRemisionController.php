<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetalleGuiaRemision;
use App\Models\Empresa;
use App\Models\GuiaRemision;
use App\Models\Producto;
use App\Models\SerieComprobante;
use App\Models\Sucursal;
use App\Models\Venta;
use App\Services\GuiaRemisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuiaRemisionController extends Controller
{
    public function __construct(protected GuiaRemisionService $service) {}

    // ── Index ─────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = GuiaRemision::with(['serieComprobante', 'cliente', 'usuario', 'sucursal'])
            ->when($user->almacen_id || $user->role->nombre !== 'Administrador', function ($q) use ($user) {
                // Vendedor/Tienda solo ven sus propias guías
                if ($user->role->nombre === 'Vendedor') {
                    $q->where('user_id', $user->id);
                }
            })
            ->when($request->filled('estado'), fn($q) => $q->where('estado', $request->estado))
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $q->whereHas('cliente', fn($c) => $c->where('nombre', 'like', '%' . $request->buscar . '%'))
                  ->orWhereHas('serieComprobante', fn($s) => $s->where('serie', 'like', '%' . $request->buscar . '%'));
            })
            ->when($request->filled('fecha_desde'), fn($q) => $q->whereDate('fecha_emision', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn($q) => $q->whereDate('fecha_emision', '<=', $request->fecha_hasta))
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'     => GuiaRemision::count(),
            'enviadas'  => GuiaRemision::where('estado', 'enviado')->count(),
            'aceptadas' => GuiaRemision::where('estado', 'aceptado')->count(),
            'borradores'=> GuiaRemision::where('estado', 'borrador')->count(),
        ];

        return view('guias-remision.index', compact('query', 'stats'));
    }

    // ── Create ────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $user      = auth()->user();
        $empresa   = Empresa::instancia();
        $clientes  = Cliente::orderBy('nombre')->get(['id', 'nombre', 'tipo_documento', 'numero_documento', 'direccion']);
        $productos = Producto::where('estado', 'activo')
            ->orderBy('nombre')
            ->with('unidadMedida:id,abreviatura')
            ->get(['id', 'nombre', 'codigo', 'unidad_medida_id']);

        // Serie de guías para la sucursal del usuario
        $sucursalId = $user->sucursal_id ?? Sucursal::first()?->id;
        $serie = $this->service->serieGuia($sucursalId);

        // Datos de la empresa como punto de partida por defecto
        $partidaDefecto = [
            'ubigeo'    => $empresa?->ubigeo ?? '',
            'direccion' => $empresa?->direccion ?? '',
        ];

        // Precargar datos desde una venta si se pasa ?venta_id=X
        $ventaPreload = null;
        if ($request->filled('venta_id')) {
            $ventaPreload = Venta::with(['cliente', 'detalles.producto'])->find($request->venta_id);
        }

        $guiaRemision = null;

        return view('guias-remision.create', compact(
            'clientes', 'productos', 'serie', 'empresa',
            'partidaDefecto', 'ventaPreload', 'guiaRemision'
        ));
    }

    // ── Store ─────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'serie_comprobante_id'    => 'required|exists:series_comprobantes,id',
            'cliente_id'              => 'nullable|exists:clientes,id',
            'venta_id'                => 'nullable|exists:ventas,id',
            'fecha_emision'           => 'required|date',
            'fecha_traslado'          => 'required|date',
            'motivo_traslado'         => 'required|in:' . implode(',', array_keys(GuiaRemision::MOTIVOS)),
            'modalidad_transporte'    => 'required|in:01,02',
            'peso_bruto'              => 'nullable|numeric|min:0',
            'numero_bultos'           => 'nullable|integer|min:1',
            // Destinatario
            'destinatario_tipo_doc'   => 'nullable|in:1,4,6,7,A',
            'destinatario_num_doc'    => 'nullable|string|max:15',
            'destinatario_nombre'     => 'nullable|string|max:200',
            'destinatario_direccion'  => 'nullable|string|max:300',
            // Puntos
            'partida_ubigeo'          => 'nullable|string|max:6',
            'partida_direccion'       => 'required|string|max:300',
            'llegada_ubigeo'          => 'nullable|string|max:6',
            'llegada_direccion'       => 'required|string|max:300',
            // Transporte privado
            'placa_vehiculo'          => 'nullable|string|max:20',
            'conductor_nombre'        => 'nullable|string|max:200',
            'conductor_tipo_doc'      => 'nullable|in:1,4',
            'conductor_num_doc'       => 'nullable|string|max:20',
            'conductor_licencia'      => 'nullable|string|max:50',
            // Transporte público
            'transportista_ruc'       => 'nullable|digits:11',
            'transportista_nombre'    => 'nullable|string|max:200',
            // Detalle
            'detalles'                => 'required|array|min:1',
            'detalles.*.descripcion'  => 'required|string|max:250',
            'detalles.*.unidad_medida'=> 'required|string|max:10',
            'detalles.*.cantidad'     => 'required|numeric|min:0.01',
            'detalles.*.producto_id'  => 'nullable|exists:productos,id',
            'detalles.*.codigo'       => 'nullable|string|max:50',
            // Obs
            'observaciones'           => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $sucursalId = $user->sucursal_id ?? Sucursal::first()?->id;

            $guia = GuiaRemision::create(array_merge(
                $validated,
                [
                    'user_id'    => $user->id,
                    'sucursal_id'=> $sucursalId,
                    'estado'     => 'borrador',
                ]
            ));

            // Asignar correlativo
            $this->service->asignarCorrelativo($guia);

            // Guardar detalles
            foreach ($validated['detalles'] as $item) {
                DetalleGuiaRemision::create(array_merge($item, ['guia_remision_id' => $guia->id]));
            }

            DB::commit();

            return redirect()->route('guias-remision.show', $guia)
                ->with('success', 'Guía de Remisión ' . $guia->numero_guia . ' creada correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al crear la guía: ' . $e->getMessage());
        }
    }

    // ── Show ──────────────────────────────────────────────────────────

    public function show(GuiaRemision $guiaRemision)
    {
        $guiaRemision->load(['serieComprobante', 'cliente', 'venta', 'usuario', 'sucursal', 'detalles.producto']);
        $empresa = Empresa::instancia();

        return view('guias-remision.show', compact('guiaRemision', 'empresa'));
    }

    // ── Edit ──────────────────────────────────────────────────────────

    public function edit(GuiaRemision $guiaRemision)
    {
        if ($guiaRemision->estado !== 'borrador') {
            return redirect()->route('guias-remision.show', $guiaRemision)
                ->with('error', 'Solo se pueden editar guías en estado Borrador.');
        }

        $guiaRemision->load('detalles.producto');
        $empresa   = Empresa::instancia();
        $clientes  = Cliente::orderBy('nombre')->get(['id', 'nombre', 'tipo_documento', 'numero_documento', 'direccion']);
        $productos = Producto::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre', 'sku', 'unidad_medida']);
        $serie     = $guiaRemision->serieComprobante;
        $partidaDefecto = [
            'ubigeo'    => $empresa?->ubigeo ?? '',
            'direccion' => $empresa?->direccion ?? '',
        ];
        $ventaPreload = null;

        return view('guias-remision.create', compact(
            'guiaRemision', 'clientes', 'productos', 'serie', 'empresa',
            'partidaDefecto', 'ventaPreload'
        ));
    }

    // ── Update ────────────────────────────────────────────────────────

    public function update(Request $request, GuiaRemision $guiaRemision)
    {
        if ($guiaRemision->estado !== 'borrador') {
            return back()->with('error', 'Solo se pueden editar guías en estado Borrador.');
        }

        $validated = $request->validate([
            'cliente_id'              => 'nullable|exists:clientes,id',
            'venta_id'                => 'nullable|exists:ventas,id',
            'fecha_emision'           => 'required|date',
            'fecha_traslado'          => 'required|date',
            'motivo_traslado'         => 'required|in:' . implode(',', array_keys(GuiaRemision::MOTIVOS)),
            'modalidad_transporte'    => 'required|in:01,02',
            'peso_bruto'              => 'nullable|numeric|min:0',
            'numero_bultos'           => 'nullable|integer|min:1',
            'destinatario_tipo_doc'   => 'nullable|in:1,4,6,7,A',
            'destinatario_num_doc'    => 'nullable|string|max:15',
            'destinatario_nombre'     => 'nullable|string|max:200',
            'destinatario_direccion'  => 'nullable|string|max:300',
            'partida_ubigeo'          => 'nullable|string|max:6',
            'partida_direccion'       => 'required|string|max:300',
            'llegada_ubigeo'          => 'nullable|string|max:6',
            'llegada_direccion'       => 'required|string|max:300',
            'placa_vehiculo'          => 'nullable|string|max:20',
            'conductor_nombre'        => 'nullable|string|max:200',
            'conductor_tipo_doc'      => 'nullable|in:1,4',
            'conductor_num_doc'       => 'nullable|string|max:20',
            'conductor_licencia'      => 'nullable|string|max:50',
            'transportista_ruc'       => 'nullable|digits:11',
            'transportista_nombre'    => 'nullable|string|max:200',
            'detalles'                => 'required|array|min:1',
            'detalles.*.descripcion'  => 'required|string|max:250',
            'detalles.*.unidad_medida'=> 'required|string|max:10',
            'detalles.*.cantidad'     => 'required|numeric|min:0.01',
            'detalles.*.producto_id'  => 'nullable|exists:productos,id',
            'detalles.*.codigo'       => 'nullable|string|max:50',
            'observaciones'           => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $guiaRemision->update($validated);

            // Reemplazar detalles
            $guiaRemision->detalles()->delete();
            foreach ($validated['detalles'] as $item) {
                DetalleGuiaRemision::create(array_merge($item, ['guia_remision_id' => $guiaRemision->id]));
            }

            DB::commit();

            return redirect()->route('guias-remision.show', $guiaRemision)
                ->with('success', 'Guía actualizada correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    // ── Destruir (soft delete) ────────────────────────────────────────

    public function destroy(GuiaRemision $guiaRemision)
    {
        if (!in_array($guiaRemision->estado, ['borrador'])) {
            return back()->with('error', 'Solo se pueden eliminar guías en estado Borrador.');
        }

        $numero = $guiaRemision->numero_guia;
        $guiaRemision->delete();

        return redirect()->route('guias-remision.index')
            ->with('success', "Guía {$numero} eliminada.");
    }

    // ── Anular ────────────────────────────────────────────────────────

    public function anular(Request $request, GuiaRemision $guiaRemision)
    {
        $request->validate([
            'motivo_anulacion' => 'required|string|max:300',
        ]);

        $result = $this->service->anular($guiaRemision, $request->motivo_anulacion);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    // ── Enviar a SUNAT ────────────────────────────────────────────────

    public function enviarSunat(GuiaRemision $guiaRemision)
    {
        if (!$guiaRemision->puede_enviarse) {
            return back()->with('error', 'La guía no puede enviarse en su estado actual (' . $guiaRemision->estado . ').');
        }

        // Marcar como enviado antes de llamar
        $guiaRemision->update(['estado' => 'enviado']);

        $result = $this->service->enviarASunat($guiaRemision);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', 'SUNAT: ' . $result['message']);
    }

    // ── PDF ───────────────────────────────────────────────────────────

    public function pdf(GuiaRemision $guiaRemision)
    {
        // Si SUNAT ya entregó un PDF, redirigir al enlace
        if ($guiaRemision->sunat_enlace_pdf) {
            return redirect($guiaRemision->sunat_enlace_pdf);
        }

        // Generar PDF local con DomPDF
        $guiaRemision->load(['serieComprobante', 'cliente', 'detalles.producto', 'sucursal', 'usuario']);
        $empresa = Empresa::instancia();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('guias-remision.pdf', compact('guiaRemision', 'empresa'))
            ->setPaper('a4');

        return $pdf->download('guia-' . $guiaRemision->numero_guia . '.pdf');
    }
}
