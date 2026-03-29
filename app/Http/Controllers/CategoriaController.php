<?php

namespace App\Http\Controllers;

use App\Models\Luminaria\TipoProducto;
use App\Models\Producto;
use App\Models\Catalogo\Marca;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listado de "categorías" = tipos de producto con conteo de productos
     */
    public function index()
    {
        $tiposProducto = TipoProducto::withCount([
                'productos',
                'productos as productos_activos_count' => fn($q) => $q->where('estado', 'activo'),
            ])
            ->orderBy('nombre')
            ->get();

        $totalProductos = Producto::count();

        return view('inventario.categorias.index', compact('tiposProducto', 'totalProductos'));
    }

    /**
     * Catálogo visual de productos de un tipo de producto
     */
    public function show(Request $request, TipoProducto $tipoProducto)
    {
        $query = Producto::with(['marca', 'variantesActivas'])
            ->where('tipo_producto_id', $tipoProducto->id);

        // Filtro por estado
        $estado = $request->get('estado', 'activo');
        if ($estado !== 'todos') {
            $query->where('estado', $estado);
        }

        // Filtro por marca
        if ($request->filled('marca_id')) {
            $query->where('marca_id', $request->marca_id);
        }

        // Búsqueda por nombre o código
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->buscar . '%')
                  ->orWhere('codigo_kyrios', 'like', '%' . $request->buscar . '%')
                  ->orWhere('codigo', 'like', '%' . $request->buscar . '%');
            });
        }

        $productos = $query->orderBy('nombre')->paginate(16)->withQueryString();

        $marcas = Marca::whereIn('id',
                \App\Models\Producto::where('tipo_producto_id', $tipoProducto->id)
                    ->whereNotNull('marca_id')
                    ->pluck('marca_id')
                    ->unique()
            )->orderBy('nombre')->get();

        return view('inventario.categorias.show', compact('tipoProducto', 'productos', 'marcas', 'estado'));
    }
}
