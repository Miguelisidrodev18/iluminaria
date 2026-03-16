<?php

namespace App\Http\Controllers\Luminaria;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Luminaria\ProductoMaterial;
use Illuminate\Http\Request;

class ProductoMaterialController extends Controller
{
    public function index()
    {
        $materiales = ProductoMaterial::with('producto')->paginate(20);
        return view('luminarias.producto-materiales.index', compact('materiales'));
    }

    public function create()
    {
        $productos = Producto::activos()->orderBy('nombre')->get();
        return view('luminarias.producto-materiales.create', compact('productos'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        ProductoMaterial::updateOrCreate(
            ['producto_id' => $data['producto_id']],
            $data
        );

        return redirect()->route('inventario.productos.show', $data['producto_id'])
                         ->with('success', 'Materiales guardados.');
    }

    public function edit(ProductoMaterial $productoMaterial)
    {
        $productos = Producto::activos()->orderBy('nombre')->get();
        return view('luminarias.producto-materiales.edit', [
            'material'  => $productoMaterial,
            'productos' => $productos,
        ]);
    }

    public function update(Request $request, ProductoMaterial $productoMaterial)
    {
        $data = $this->validar($request);
        $productoMaterial->update($data);

        return redirect()->route('inventario.productos.show', $productoMaterial->producto_id)
                         ->with('success', 'Materiales actualizados.');
    }

    public function destroy(ProductoMaterial $productoMaterial)
    {
        $productoId = $productoMaterial->producto_id;
        $productoMaterial->delete();
        return redirect()->route('inventario.productos.show', $productoId)
                         ->with('success', 'Materiales eliminados.');
    }

    public function show(ProductoMaterial $productoMaterial)
    {
        return redirect()->route('luminarias.producto-materiales.edit', $productoMaterial);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'producto_id'     => 'required|exists:productos,id',
            'material_1'      => 'nullable|string|max:100',
            'material_2'      => 'nullable|string|max:100',
            'color_acabado_1' => 'nullable|string|max:100',
            'color_acabado_2' => 'nullable|string|max:100',
        ]);
    }
}
