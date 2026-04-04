<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Catalogo\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VarianteService
{
    /**
     * Obtener o crear variante dado el producto base, color, especificación y atributos luminaria.
     */
    public function obtenerOCrearVariante(
        Producto $producto,
        ?int $colorId,
        ?string $especificacion,
        float $sobreprecio = 0,
        array $atributos = []
    ): ProductoVariante {
        // Normalizar
        $especificacion = $especificacion ? trim($especificacion) : null;
        $atributos      = array_filter($atributos, fn($v) => !is_null($v) && $v !== '');

        // Buscar variante existente por color + especificacion (compatibilidad)
        $variante = ProductoVariante::where('producto_id', $producto->id)
            ->when($colorId, fn($q) => $q->where('color_id', $colorId), fn($q) => $q->whereNull('color_id'))
            ->when($especificacion, fn($q) => $q->where('especificacion', $especificacion), fn($q) => $q->whereNull('especificacion'))
            ->first();

        if ($variante) {
            // Si hay nuevos atributos, fusionarlos
            if (!empty($atributos)) {
                $variante->update([
                    'atributos' => array_merge($variante->atributos ?? [], $atributos),
                ]);
            }
            return $variante;
        }

        // Crear nueva variante
        $color = $colorId ? Color::find($colorId) : null;
        $sku   = ProductoVariante::generarSku($producto, $color, $especificacion, $atributos);

        $variante = ProductoVariante::create([
            'producto_id'    => $producto->id,
            'color_id'       => $colorId,
            'especificacion' => $especificacion,
            'atributos'      => !empty($atributos) ? $atributos : null,
            'sku'            => $sku,
            'sobreprecio'    => $sobreprecio,
            'stock_actual'   => 0,
            'stock_minimo'   => $producto->stock_minimo ?? 0,
            'estado'         => 'activo',
            'creado_por'     => auth()->id(),
        ]);

        Log::info('Variante de producto creada', [
            'variante_id'    => $variante->id,
            'producto_id'    => $producto->id,
            'sku'            => $sku,
            'color_id'       => $colorId,
            'especificacion' => $especificacion,
        ]);

        return $variante;
    }

    /**
     * Listado de variantes de un producto, para consumo en la UI.
     */
    public function getVariantesParaSelector(Producto $producto, ?int $almacenId = null): array
    {
        return $producto->variantesActivas()
            ->with('color')
            ->get()
            ->map(fn($v) => $this->formatearVariante($v))
            ->toArray();
    }

    /**
     * Formatea una variante para respuesta JSON.
     */
    public function formatearVariante(ProductoVariante $v): array
    {
        return [
            'id'             => $v->id,
            'sku'            => $v->sku,
            'nombre'         => $v->nombre,
            'color_id'       => $v->color_id,
            'color_nombre'   => $v->color?->nombre,
            'color_hex'      => $v->color?->codigo_hex,
            'especificacion' => $v->especificacion,
            'atributos'      => $v->atributos ?? [],
            'sobreprecio'    => (float) $v->sobreprecio,
            'stock_actual'   => (int) $v->stock_actual,
            'stock_minimo'   => (int) $v->stock_minimo,
            'estado'         => $v->estado,
            'nombre_completo'=> $v->nombre_completo,
            'tiene_stock'    => $v->tieneStock(),
        ];
    }

    /**
     * Actualizar sobreprecio de una variante existente.
     */
    public function actualizarSobreprecio(ProductoVariante $variante, float $sobreprecio): void
    {
        $variante->update(['sobreprecio' => $sobreprecio]);
    }

    /**
     * Desactivar variante (soft-disable).
     */
    public function desactivarVariante(ProductoVariante $variante): void
    {
        $variante->update(['estado' => 'inactivo']);
        $variante->sincronizarStockProductoBase();
    }

    /**
     * Obtener variantes agrupadas por producto para el índice de inventario.
     */
    public function getProductosConVariantes(array $filtros = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Producto::with(['variantesActivas.color', 'categoria', 'marca', 'modelo'])
            ->where('estado', 'activo');

        if (!empty($filtros['buscar'])) {
            $t = $filtros['buscar'];
            $query->where(function ($q) use ($t) {
                $q->where('nombre', 'like', "%{$t}%")
                  ->orWhere('codigo', 'like', "%{$t}%")
                  ->orWhereHas('variantesActivas', fn($qv) => $qv->where('sku', 'like', "%{$t}%"));
            });
        }

        if (!empty($filtros['categoria_id'])) {
            $query->where('categoria_id', $filtros['categoria_id']);
        }

        return $query->orderBy('nombre')->paginate(15);
    }
}
