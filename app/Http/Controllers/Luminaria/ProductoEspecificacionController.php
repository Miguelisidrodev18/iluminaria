<?php

namespace App\Http\Controllers\Luminaria;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Luminaria\ProductoEspecificacion;
use Illuminate\Http\Request;

class ProductoEspecificacionController extends Controller
{
    public function index()
    {
        $especificaciones = ProductoEspecificacion::with('producto')->paginate(20);
        return view('luminarias.producto-especificaciones.index', compact('especificaciones'));
    }

    public function create()
    {
        $productos = Producto::activos()->orderBy('nombre')->get();
        return view('luminarias.producto-especificaciones.create', compact('productos'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        ProductoEspecificacion::updateOrCreate(
            ['producto_id' => $data['producto_id']],
            $data
        );

        return redirect()->route('inventario.productos.show', $data['producto_id'])
                         ->with('success', 'Especificaciones guardadas.');
    }

    public function edit(ProductoEspecificacion $productoEspecificacion)
    {
        $productos = Producto::activos()->orderBy('nombre')->get();
        return view('luminarias.producto-especificaciones.edit', [
            'especificacion' => $productoEspecificacion,
            'productos'      => $productos,
        ]);
    }

    public function update(Request $request, ProductoEspecificacion $productoEspecificacion)
    {
        $data = $this->validar($request, $productoEspecificacion->id);
        $productoEspecificacion->update($data);

        return redirect()->route('inventario.productos.show', $productoEspecificacion->producto_id)
                         ->with('success', 'Especificaciones actualizadas.');
    }

    public function destroy(ProductoEspecificacion $productoEspecificacion)
    {
        $productoId = $productoEspecificacion->producto_id;
        $productoEspecificacion->delete();
        return redirect()->route('inventario.productos.show', $productoId)
                         ->with('success', 'Especificaciones eliminadas.');
    }

    public function show(ProductoEspecificacion $productoEspecificacion)
    {
        return redirect()->route('luminarias.producto-especificaciones.edit', $productoEspecificacion);
    }

    private function validar(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'producto_id'          => 'required|exists:productos,id',
            'potencia'             => 'nullable|string|max:30',
            'lumenes'              => 'nullable|string|max:30',
            'voltaje'              => 'nullable|string|max:30',
            'temperatura_color'    => 'nullable|string|max:30',
            'cri'                  => 'nullable|integer|min:0|max:100',
            'ip'                   => 'nullable|string|max:10',
            'ik'                   => 'nullable|string|max:10',
            'angulo_apertura'      => 'nullable|string|max:20',
            'driver'               => 'nullable|string|max:100',
            'regulable'            => 'boolean',
            'protocolo_regulacion' => 'nullable|string|max:50',
            'socket'               => 'nullable|string|max:20',
            'numero_lamparas'      => 'nullable|integer|min:1',
        ]);
    }
}
