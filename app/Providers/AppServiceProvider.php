<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\ProductoVariante;
use App\Observers\ProductoVarianteObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // ── Observers ────────────────────────────────────────────────────────
        ProductoVariante::observe(ProductoVarianteObserver::class);

        // ── Gates basados en permisos del rol ────────────────────────────────
        $permisosGates = [
            'crear_producto',
            'editar_producto',
            'eliminar_producto',
            'aprobar_producto',
            'editar_precios',
            'ver_costos',
            'gestionar_marcas',
            'gestionar_usuarios',
        ];

        foreach ($permisosGates as $permiso) {
            Gate::define($permiso, function (User $user) use ($permiso) {
                return $user->puedeHacer($permiso);
            });
        }
    }
}
