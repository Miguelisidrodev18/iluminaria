<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoComponente;
use App\Models\ProductoVariante;
use Illuminate\Http\Request;

class ProductoComponenteController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Administrador,Almacenero');
    }

    /**
     * Agregar un componente al BOM de un producto compuesto.
     */
    public function store(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'hijo_id'      => 'required|integer|exists:productos,id|different:producto',
            'variante_id'  => 'nullable|integer|exists:producto_variantes,id',
            'cantidad'     => 'required|numeric|min:0.001',
            'unidad'       => 'nullable|string|max:20',
            'es_opcional'  => 'boolean',
            'orden'        => 'nullable|integer|min:0',
            'observacion'  => 'nullable|string|max:255',
        ]);

        // Evitar que el producto sea componente de sí mismo
        if ($validated['hijo_id'] == $producto->id) {
            return back()->withErrors(['hijo_id' => 'Un producto no puede ser componente de sí mismo.'])->withInput();
        }

        // Verificar que no exista ya ese componente (misma combinación padre+hijo+variante)
        $existe = ProductoComponente::where('padre_id', $producto->id)
            ->where('hijo_id', $validated['hijo_id'])
            ->where('variante_id', $validated['variante_id'] ?? null)
            ->exists();

        if ($existe) {
            return back()->withErrors(['hijo_id' => 'Este componente ya existe en el producto.'])->withInput();
        }

        ProductoComponente::create([
            'padre_id'    => $producto->id,
            'hijo_id'     => $validated['hijo_id'],
            'variante_id' => $validated['variante_id'] ?? null,
            'cantidad'    => $validated['cantidad'],
            'unidad'      => $validated['unidad'] ?? 'unidad',
            'es_opcional' => $validated['es_opcional'] ?? false,
            'orden'       => $validated['orden'] ?? 0,
            'observacion' => $validated['observacion'] ?? null,
        ]);

        return back()->with('success', 'Componente agregado correctamente.');
    }

    /**
     * Actualizar un componente del BOM.
     */
    public function update(Request $request, ProductoComponente $componente)
    {
        $validated = $request->validate([
            'cantidad'    => 'required|numeric|min:0.001',
            'unidad'      => 'nullable|string|max:20',
            'es_opcional' => 'boolean',
            'orden'       => 'nullable|integer|min:0',
            'observacion' => 'nullable|string|max:255',
        ]);

        $componente->update([
            'cantidad'    => $validated['cantidad'],
            'unidad'      => $validated['unidad'] ?? $componente->unidad,
            'es_opcional' => $validated['es_opcional'] ?? false,
            'orden'       => $validated['orden'] ?? $componente->orden,
            'observacion' => $validated['observacion'] ?? null,
        ]);

        return back()->with('success', 'Componente actualizado.');
    }

    /**
     * Eliminar un componente del BOM.
     */
    public function destroy(ProductoComponente $componente)
    {
        $componente->delete();
        return back()->with('success', 'Componente eliminado del kit.');
    }

    /**
     * API: Listar componentes de un producto compuesto (para JS en ventas/cotizaciones).
     */
    public function apiComponentes(Producto $producto)
    {
        $componentes = $producto->componentes()
            ->with(['hijo:id,nombre,codigo,stock_actual', 'variante:id,producto_id,color_id,especificacion,stock_actual'])
            ->get()
            ->map(fn($c) => [
                'id'             => $c->id,
                'nombre'         => $c->nombre_completo,
                'cantidad'       => (float) $c->cantidad,
                'unidad'         => $c->unidad,
                'es_opcional'    => $c->es_opcional,
                'stock_disponible' => $c->stock_disponible,
                'tiene_stock'    => $c->tieneStockParaKits(1),
            ]);

        return response()->json($componentes);
    }
}
