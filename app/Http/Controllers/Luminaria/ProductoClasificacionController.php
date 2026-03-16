<?php

namespace App\Http\Controllers\Luminaria;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Luminaria\ProductoClasificacion;
use App\Models\Luminaria\TipoProyecto;
use Illuminate\Http\Request;

class ProductoClasificacionController extends Controller
{
    public function index()
    {
        $clasificaciones = ProductoClasificacion::with(['producto', 'tipoProyecto'])->paginate(20);
        return view('luminarias.producto-clasificacion.index', compact('clasificaciones'));
    }

    public function create()
    {
        $productos = Producto::activos()->orderBy('nombre')->get();
        $tipos     = TipoProyecto::activos()->orderBy('nombre')->get();
        return view('luminarias.producto-clasificacion.create', compact('productos', 'tipos'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        ProductoClasificacion::updateOrCreate(
            ['producto_id' => $data['producto_id']],
            $data
        );

        return redirect()->route('inventario.productos.show', $data['producto_id'])
                         ->with('success', 'Clasificación guardada.');
    }

    public function edit(ProductoClasificacion $productoClasificacion)
    {
        $productos = Producto::activos()->orderBy('nombre')->get();
        $tipos     = TipoProyecto::activos()->orderBy('nombre')->get();
        return view('luminarias.producto-clasificacion.edit', [
            'clasificacion' => $productoClasificacion,
            'productos'     => $productos,
            'tipos'         => $tipos,
        ]);
    }

    public function update(Request $request, ProductoClasificacion $productoClasificacion)
    {
        $data = $this->validar($request);
        $productoClasificacion->update($data);

        return redirect()->route('inventario.productos.show', $productoClasificacion->producto_id)
                         ->with('success', 'Clasificación actualizada.');
    }

    public function destroy(ProductoClasificacion $productoClasificacion)
    {
        $productoId = $productoClasificacion->producto_id;
        $productoClasificacion->delete();
        return redirect()->route('inventario.productos.show', $productoId)
                         ->with('success', 'Clasificación eliminada.');
    }

    public function show(ProductoClasificacion $productoClasificacion)
    {
        return redirect()->route('luminarias.producto-clasificacion.edit', $productoClasificacion);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'producto_id'       => 'required|exists:productos,id',
            'uso'               => 'required|in:interior,exterior,interior_exterior',
            'tipo_instalacion'  => 'nullable|in:empotrado,superficie,suspendido,poste,carril,portatil',
            'estilo'            => 'nullable|string|max:100',
            'tipo_proyecto_id'  => 'nullable|exists:tipos_proyecto,id',
        ]);
    }
}
