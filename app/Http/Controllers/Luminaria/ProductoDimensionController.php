<?php

namespace App\Http\Controllers\Luminaria;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Luminaria\ProductoDimension;
use Illuminate\Http\Request;

class ProductoDimensionController extends Controller
{
    public function index()
    {
        $dimensiones = ProductoDimension::with('producto')->paginate(20);
        return view('luminarias.producto-dimensiones.index', compact('dimensiones'));
    }

    public function create()
    {
        $productos = Producto::activos()->orderBy('nombre')->get();
        return view('luminarias.producto-dimensiones.create', compact('productos'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        ProductoDimension::updateOrCreate(
            ['producto_id' => $data['producto_id']],
            $data
        );

        return redirect()->route('inventario.productos.show', $data['producto_id'])
                         ->with('success', 'Dimensiones guardadas.');
    }

    public function edit(ProductoDimension $productoDimension)
    {
        $productos = Producto::activos()->orderBy('nombre')->get();
        return view('luminarias.producto-dimensiones.edit', [
            'dimension' => $productoDimension,
            'productos' => $productos,
        ]);
    }

    public function update(Request $request, ProductoDimension $productoDimension)
    {
        $data = $this->validar($request);
        $productoDimension->update($data);

        return redirect()->route('inventario.productos.show', $productoDimension->producto_id)
                         ->with('success', 'Dimensiones actualizadas.');
    }

    public function destroy(ProductoDimension $productoDimension)
    {
        $productoId = $productoDimension->producto_id;
        $productoDimension->delete();
        return redirect()->route('inventario.productos.show', $productoId)
                         ->with('success', 'Dimensiones eliminadas.');
    }

    public function show(ProductoDimension $productoDimension)
    {
        return redirect()->route('luminarias.producto-dimensiones.edit', $productoDimension);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'producto_id'      => 'required|exists:productos,id',
            'alto'             => 'nullable|numeric|min:0',
            'ancho'            => 'nullable|numeric|min:0',
            'diametro'         => 'nullable|numeric|min:0',
            'lado'             => 'nullable|numeric|min:0',
            'profundidad'      => 'nullable|numeric|min:0',
            'alto_suspendido'  => 'nullable|numeric|min:0',
            'diametro_agujero' => 'nullable|numeric|min:0',
        ]);
    }
}
