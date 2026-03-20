<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos — CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Catálogo de Productos" subtitle="Gestión de luminarias, accesorios y equipos eléctricos" />

        {{-- Alertas --}}
        @if(session('success'))
            <div class="mb-5 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-5 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-xl"></i>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        {{-- ── Tarjetas de estadísticas ───────────────────────────────────────── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background:#2B2E2C;">
                    <i class="fas fa-box text-xl" style="color:#F7D600;"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Total Productos</p>
                    <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $productos->total() }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-check-circle text-xl text-green-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Activos</p>
                    <p class="text-3xl font-bold text-gray-900 leading-tight">{{ \App\Models\Producto::activos()->count() }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-exclamation-triangle text-xl text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Stock Bajo</p>
                    <p class="text-3xl font-bold text-gray-900 leading-tight">{{ \App\Models\Producto::query()->stockBajo()->count() }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-times-circle text-xl text-red-500"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Sin Stock</p>
                    <p class="text-3xl font-bold text-gray-900 leading-tight">{{ \App\Models\Producto::query()->sinStock()->count() }}</p>
                </div>
            </div>
        </div>

        {{-- ── Panel de filtros ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-5">
            <form action="{{ route('inventario.productos.index') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

                    {{-- Búsqueda --}}
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                            <input type="text" name="buscar" value="{{ request('buscar') }}"
                                   placeholder="Código, nombre, código de barras o Kyrios"
                                   class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                        </div>
                    </div>

                    {{-- Tipo de Producto --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de Producto</label>
                        <select name="tipo_producto_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                            <option value="">Todos los tipos</option>
                            @foreach($tiposProducto as $tp)
                                <option value="{{ $tp->id }}" {{ request('tipo_producto_id') == $tp->id ? 'selected' : '' }}>
                                    {{ $tp->codigo }} — {{ $tp->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Categoría --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Categoría</label>
                        <select name="categoria_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                            <option value="">Todas</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Estado --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                        <select name="estado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                            <option value="todos"        {{ request('estado') == 'todos'         ? 'selected' : '' }}>Todos</option>
                            <option value="activo"       {{ request('estado', 'activo') == 'activo'       ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo"     {{ request('estado') == 'inactivo'      ? 'selected' : '' }}>Inactivo</option>
                            <option value="descontinuado"{{ request('estado') == 'descontinuado' ? 'selected' : '' }}>Descontinuado</option>
                        </select>
                    </div>

                    {{-- Stock --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Stock</label>
                        <select name="stock_estado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                            <option value="">Todos</option>
                            <option value="bajo"      {{ request('stock_estado') == 'bajo'      ? 'selected' : '' }}>Stock Bajo</option>
                            <option value="sin_stock" {{ request('stock_estado') == 'sin_stock' ? 'selected' : '' }}>Sin Stock</option>
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                    <a href="{{ route('inventario.productos.index') }}"
                       class="text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times mr-1"></i>Limpiar filtros
                    </a>
                    <button type="submit"
                            class="px-5 py-2 text-gray-900 rounded-lg text-sm font-semibold"
                            style="background-color:#F7D600;"
                            onmouseover="this.style.backgroundColor='#e8c900'"
                            onmouseout="this.style.backgroundColor='#F7D600'">
                        <i class="fas fa-search mr-1"></i>Buscar
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Tabla de productos ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

            {{-- Header tabla --}}
            <div class="px-6 py-4 flex items-center justify-between" style="background-color:#2B2E2C;">
                <div class="flex items-center gap-3">
                    <i class="fas fa-lightbulb text-xl" style="color:#F7D600;"></i>
                    <div>
                        <h2 class="text-base font-bold text-white leading-tight">Listado de Productos</h2>
                        <p class="text-xs text-gray-400">
                            {{ $productos->total() }} producto(s)
                            @if(request()->hasAny(['buscar','tipo_producto_id','categoria_id','stock_estado']))
                                con filtros activos
                            @endif
                        </p>
                    </div>
                </div>
                @if($canCreate)
                    <a href="{{ route('inventario.productos.create') }}"
                       class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-900 flex items-center gap-2"
                       style="background-color:#F7D600;"
                       onmouseover="this.style.backgroundColor='#e8c900'"
                       onmouseout="this.style.backgroundColor='#F7D600'">
                        <i class="fas fa-plus"></i>Nuevo Producto
                    </a>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Producto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo / Código Kyrios</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Marca</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Stock</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($productos as $producto)
                        @php
                            $stockClass = match($producto->estado_stock) {
                                'sin_stock' => 'bg-red-100 text-red-700 border-red-200',
                                'bajo'      => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                'exceso'    => 'bg-blue-100 text-blue-700 border-blue-200',
                                default     => 'bg-green-100 text-green-700 border-green-200',
                            };
                            $iconosTipo = ['LU'=>'fas fa-lightbulb','LA'=>'fas fa-fire','CL'=>'fas fa-grip-lines','SM'=>'fas fa-cubes','AC'=>'fas fa-tools','EA'=>'fas fa-plug','PE'=>'fas fa-bars','PA'=>'fas fa-tv','VE'=>'fas fa-fan','CA'=>'fas fa-dot-circle','PO'=>'fas fa-map-pin','LE'=>'fas fa-exclamation-triangle','SO'=>'fas fa-sun','RE'=>'fas fa-battery-half'];
                            $tpCodigo   = $producto->tipoProducto?->codigo ?? '??';
                            $tpIcono    = $iconosTipo[$tpCodigo] ?? 'fas fa-box';
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">

                            {{-- Producto (imagen + nombre + código) --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($producto->imagen)
                                        <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}"
                                             class="h-11 w-11 rounded-lg object-cover border border-gray-200 shrink-0">
                                    @else
                                        <div class="h-11 w-11 rounded-lg bg-gray-100 flex items-center justify-center shrink-0 border border-gray-200">
                                            <i class="{{ $tpIcono }} text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate max-w-[220px]" title="{{ $producto->nombre }}">
                                            {{ $producto->nombre }}
                                        </p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $producto->codigo }}</p>
                                        @if($producto->variantesActivas->count() > 0)
                                            <span class="inline-flex items-center gap-1 mt-0.5 px-1.5 py-0.5 bg-indigo-100 text-indigo-700 rounded text-xs">
                                                <i class="fas fa-layer-group text-xs"></i>
                                                {{ $producto->variantesActivas->count() }} variante(s)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Tipo + Kyrios --}}
                            <td class="px-4 py-3">
                                <div class="space-y-1">
                                    @if($producto->tipoProducto)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-semibold bg-gray-900 text-yellow-400">
                                            <i class="{{ $tpIcono }} text-xs"></i>
                                            {{ $producto->tipoProducto->codigo }} — {{ $producto->tipoProducto->nombre }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Sin tipo</span>
                                    @endif
                                    @if($producto->codigo_kyrios)
                                        <p class="text-xs font-mono text-gray-500">
                                            <i class="fas fa-key text-yellow-400 mr-0.5"></i>
                                            {{ $producto->codigo_kyrios }}
                                        </p>
                                    @endif
                                    @if($producto->codigo_fabrica)
                                        <p class="text-xs text-gray-400">Fab: {{ $producto->codigo_fabrica }}</p>
                                    @endif
                                </div>
                            </td>

                            {{-- Marca --}}
                            <td class="px-4 py-3">
                                <div class="text-sm">
                                    @if($producto->marca)
                                        <p class="font-medium text-gray-800">{{ $producto->marca->nombre }}</p>
                                        @if($producto->marca->codigo)
                                            <span class="text-xs font-mono text-gray-400 bg-gray-100 px-1.5 rounded">{{ $producto->marca->codigo }}</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400 italic">Sin marca</span>
                                    @endif
                                    @if($producto->categoria)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $producto->categoria->nombre }}</p>
                                    @endif
                                </div>
                            </td>

                            {{-- Stock --}}
                            <td class="px-4 py-3 text-center">
                                @if($producto->variantesActivas->count() > 0)
                                    @php $stockTotal = $producto->variantesActivas->sum('stock_actual'); @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border
                                                 {{ $stockTotal == 0 ? 'bg-red-100 text-red-700 border-red-200' : ($stockTotal <= 5 ? 'bg-yellow-100 text-yellow-700 border-yellow-200' : 'bg-green-100 text-green-700 border-green-200') }}">
                                        {{ $stockTotal }} unt
                                    </span>
                                    <div class="flex items-center justify-center gap-1 mt-1 flex-wrap">
                                        @foreach($producto->variantesActivas as $v)
                                            <span title="{{ $v->nombre_completo }}: {{ $v->stock_actual }}"
                                                  class="text-xs text-gray-500 flex items-center gap-0.5">
                                                @if($v->color?->codigo_hex)
                                                    <span class="w-2 h-2 rounded-full border border-gray-300"
                                                          style="background:{{ $v->color->codigo_hex }}"></span>
                                                @endif
                                                {{ $v->stock_actual }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $stockClass }}">
                                        {{ $producto->stock_actual }} {{ $producto->unidadMedida?->abreviatura ?? 'und' }}
                                    </span>
                                    @if($producto->stock_minimo > 0)
                                        <p class="text-xs text-gray-400 mt-0.5">Mín: {{ $producto->stock_minimo }}</p>
                                    @endif
                                @endif
                            </td>

                            {{-- Estado --}}
                            <td class="px-4 py-3 text-center">
                                @php
                                    $estadoBadge = match($producto->estado) {
                                        'activo'        => ['bg-green-100 text-green-700', 'Activo'],
                                        'inactivo'      => ['bg-gray-100 text-gray-600',   'Inactivo'],
                                        'descontinuado' => ['bg-red-100 text-red-600',     'Descont.'],
                                        default         => ['bg-gray-100 text-gray-500',   $producto->estado],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $estadoBadge[0] }}">
                                    {{ $estadoBadge[1] }}
                                </span>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('inventario.productos.show', $producto) }}"
                                       class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                                       title="Ver detalle">
                                        <i class="fas fa-eye text-sm"></i>
                                    </a>

                                    @if($canEdit)
                                        <a href="{{ route('inventario.productos.edit', $producto) }}"
                                           class="p-1.5 rounded-lg text-blue-500 hover:text-blue-700 hover:bg-blue-50 transition-colors"
                                           title="Editar">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                    @endif

                                    @if($producto->variantesActivas->count() > 0)
                                        <a href="{{ route('inventario.productos.variantes', $producto) }}"
                                           class="p-1.5 rounded-lg text-indigo-500 hover:text-indigo-700 hover:bg-indigo-50 transition-colors"
                                           title="Variantes">
                                            <i class="fas fa-layer-group text-sm"></i>
                                        </a>
                                    @endif

                                    @if($producto->tipo_inventario === 'serie')
                                        <a href="{{ route('inventario.imeis.index', ['producto_id' => $producto->id]) }}"
                                           class="p-1.5 rounded-lg text-purple-500 hover:text-purple-700 hover:bg-purple-50 transition-colors"
                                           title="IMEIs">
                                            <i class="fas fa-sim-card text-sm"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('inventario.productos.codigos-barras', $producto) }}"
                                       class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                                       title="Códigos de barras">
                                        <i class="fas fa-barcode text-sm"></i>
                                    </a>

                                    @if($canDelete)
                                        <form action="{{ route('inventario.productos.destroy', $producto) }}"
                                              method="POST" class="inline"
                                              onsubmit="return confirm('¿Eliminar {{ addslashes($producto->nombre) }}? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                    title="Eliminar">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="fas fa-search text-5xl mb-3"></i>
                                    <p class="text-base font-medium text-gray-600">No se encontraron productos</p>
                                    <p class="text-sm mt-1">Prueba con otros filtros o crea el primero</p>
                                    @if($canCreate)
                                        <a href="{{ route('inventario.productos.create') }}"
                                           class="mt-4 px-5 py-2 rounded-lg text-sm font-semibold text-gray-900"
                                           style="background-color:#F7D600;">
                                            <i class="fas fa-plus mr-1"></i>Nuevo Producto
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer: leyenda + paginación --}}
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span>Mostrando {{ $productos->firstItem() ?? 0 }}–{{ $productos->lastItem() ?? 0 }} de {{ $productos->total() }}</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-green-200 border border-green-500"></span>Normal</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-yellow-200 border border-yellow-500"></span>Bajo</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-200 border border-red-500"></span>Sin stock</span>
                </div>
                @if($productos->hasPages())
                    <div>{{ $productos->appends(request()->query())->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
