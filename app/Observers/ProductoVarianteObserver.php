<?php

namespace App\Observers;

use App\Models\ProductoVariante;
use Illuminate\Support\Facades\Log;

class ProductoVarianteObserver
{
    /**
     * Sincroniza el stock del producto base cada vez que una variante se guarda.
     * Se dispara en: create, update (incluyendo cambios de estado/stock).
     */
    public function saved(ProductoVariante $variante): void
    {
        $this->sincronizar($variante);
    }

    /**
     * Sincroniza cuando se elimina una variante.
     */
    public function deleted(ProductoVariante $variante): void
    {
        $this->sincronizar($variante);
    }

    private function sincronizar(ProductoVariante $variante): void
    {
        // Evitar loop si el update del producto_base re-dispara eventos
        if (!$variante->producto_id) return;

        $totalStock = ProductoVariante::where('producto_id', $variante->producto_id)
            ->where('estado', 'activo')
            ->sum('stock_actual');

        // Usar updateQuietly para no disparar eventos del modelo Producto
        \App\Models\Producto::where('id', $variante->producto_id)
            ->update(['stock_actual' => $totalStock]);

        Log::debug('Stock base sincronizado por Observer', [
            'producto_id'  => $variante->producto_id,
            'stock_total'  => $totalStock,
            'variante_id'  => $variante->id,
        ]);
    }
}
