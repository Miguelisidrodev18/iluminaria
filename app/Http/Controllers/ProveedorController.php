<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Services\SunatService;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::withCount('compras', 'pedidos')
            ->with('categoriasProducto');

        // Filtros
        if ($request->filled('buscar')) {
            $q = $request->buscar;
            $query->where(function ($sq) use ($q) {
                $sq->where('razon_social', 'like', "%{$q}%")
                   ->orWhere('nombre_comercial', 'like', "%{$q}%")
                   ->orWhere('ruc', 'like', "%{$q}%")
                   ->orWhere('country', 'like', "%{$q}%");
            });
        }
        if ($request->filled('tipo'))        $query->where('supplier_type', $request->tipo);
        if ($request->filled('precio'))      $query->where('price_level', $request->precio);
        if ($request->filled('calidad'))     $query->where('quality_level', $request->calidad);
        if ($request->filled('estado'))      $query->where('estado', $request->estado);

        $proveedores = $query->orderBy('razon_social')->get();

        $stats = [
            'total'       => $proveedores->count(),
            'activos'     => $proveedores->where('estado', 'activo')->count(),
            'extranjeros' => $proveedores->where('supplier_type', 'extranjero')->count(),
            'nacionales'  => $proveedores->where('supplier_type', 'nacional')->count(),
        ];

        $canCreate = in_array(auth()->user()->role->nombre, ['Administrador', 'Almacenero']);
        $canEdit   = $canCreate;
        $canDelete = auth()->user()->role->nombre === 'Administrador';

        return view('proveedores.index', compact('proveedores', 'stats', 'canCreate', 'canEdit', 'canDelete'));
    }

    public function create()
    {
        $categorias = Proveedor::CATEGORIAS_PRODUCTO;
        return view('proveedores.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_type'   => 'required|in:nacional,extranjero,importacion',
            'ruc'             => 'nullable|string|size:11|unique:proveedores,ruc',
            'razon_social'    => 'required|string|max:255',
            'nombre_comercial'=> 'nullable|string|max:255',
            'contacto_nombre' => 'nullable|string|max:255',
            'telefono'        => 'nullable|string|max:100',
            'email'           => 'nullable|email|max:255',
            'website'         => 'nullable|url|max:255',
            'catalog_url'     => 'nullable|string|max:255',
            'direccion'       => 'nullable|string|max:500',
            'factory_address' => 'nullable|string|max:500',
            'country'         => 'nullable|string|max:60',
            'district'        => 'nullable|string|max:80',
            'port'            => 'nullable|string|max:80',
            'moq'             => 'nullable|string|max:50',
            'bank_detail'     => 'nullable|string|max:1000',
            'price_level'     => 'nullable|in:muy_caro,accesible,barato',
            'quality_level'   => 'nullable|in:excelente,regular,mala',
            'observations'    => 'nullable|string|max:2000',
            'estado'          => 'required|in:activo,inactivo',
        ]);

        $proveedor = Proveedor::create($validated);

        // Guardar categorías seleccionadas
        $this->syncCategorias($proveedor, $request->input('categorias', []));

        // Guardar certificaciones
        $this->syncCertificaciones($proveedor, $request->input('certificaciones', []));

        return redirect()->route('proveedores.show', $proveedor)
            ->with('success', 'Proveedor registrado exitosamente.');
    }

    public function show(Proveedor $proveedor)
    {
        $proveedor->load([
            'compras' => fn($q) => $q->latest()->take(10),
            'pedidos' => fn($q) => $q->latest()->take(10),
            'categoriasProducto',
            'certificaciones',
        ]);

        $categoriasPorGrupo = $proveedor->categoriasProducto
            ->groupBy('categoria')
            ->map(fn($items) => $items->pluck('subcategoria'));

        return view('proveedores.show', compact('proveedor', 'categoriasPorGrupo'));
    }

    public function edit(Proveedor $proveedor)
    {
        $proveedor->load('categoriasProducto', 'certificaciones');
        $categorias        = Proveedor::CATEGORIAS_PRODUCTO;
        $selCategorias     = $proveedor->categoriasProducto
            ->map(fn($c) => $c->categoria . ':' . $c->subcategoria)->toArray();
        $selCertificaciones = $proveedor->certificaciones->pluck('cert_type')->toArray();

        return view('proveedores.edit', compact('proveedor', 'categorias', 'selCategorias', 'selCertificaciones'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $validated = $request->validate([
            'supplier_type'   => 'required|in:nacional,extranjero,importacion',
            'ruc'             => 'nullable|string|size:11|unique:proveedores,ruc,' . $proveedor->id,
            'razon_social'    => 'required|string|max:255',
            'nombre_comercial'=> 'nullable|string|max:255',
            'contacto_nombre' => 'nullable|string|max:255',
            'telefono'        => 'nullable|string|max:100',
            'email'           => 'nullable|email|max:255',
            'website'         => 'nullable|url|max:255',
            'catalog_url'     => 'nullable|string|max:255',
            'direccion'       => 'nullable|string|max:500',
            'factory_address' => 'nullable|string|max:500',
            'country'         => 'nullable|string|max:60',
            'district'        => 'nullable|string|max:80',
            'port'            => 'nullable|string|max:80',
            'moq'             => 'nullable|string|max:50',
            'bank_detail'     => 'nullable|string|max:1000',
            'price_level'     => 'nullable|in:muy_caro,accesible,barato',
            'quality_level'   => 'nullable|in:excelente,regular,mala',
            'observations'    => 'nullable|string|max:2000',
            'estado'          => 'required|in:activo,inactivo',
        ]);

        $proveedor->update($validated);
        $this->syncCategorias($proveedor, $request->input('categorias', []));
        $this->syncCertificaciones($proveedor, $request->input('certificaciones', []));

        return redirect()->route('proveedores.show', $proveedor)
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        try {
            $proveedor->delete();
            return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado.');
        } catch (\Exception $e) {
            return redirect()->route('proveedores.index')
                ->with('error', 'No se puede eliminar: tiene compras o pedidos asociados.');
        }
    }

    public function consultarSunat(Request $request)
    {
        $ruc = $request->input('ruc');
        $result = app(SunatService::class)->consultarRuc($ruc);
        return response()->json($result);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function syncCategorias(Proveedor $proveedor, array $catArray): void
    {
        $proveedor->categoriasProducto()->delete();
        foreach ($catArray as $par) {
            if (!str_contains($par, ':')) continue;
            [$cat, $sub] = array_map('trim', explode(':', $par, 2));
            if (isset(Proveedor::CATEGORIAS_PRODUCTO[$cat]) && in_array($sub, Proveedor::CATEGORIAS_PRODUCTO[$cat])) {
                $proveedor->categoriasProducto()->create(['categoria' => $cat, 'subcategoria' => $sub]);
            }
        }
    }

    private function syncCertificaciones(Proveedor $proveedor, array $certs): void
    {
        $proveedor->certificaciones()->delete();
        $validos = ['generales', 'por_producto', 'iso'];
        foreach ($certs as $cert) {
            if (in_array($cert, $validos)) {
                $proveedor->certificaciones()->create(['cert_type' => $cert]);
            }
        }
    }
}
