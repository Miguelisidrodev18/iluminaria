<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tipoProducto->nombre }} — Catálogo Kyrios</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">

        {{-- Breadcrumb + Título --}}
        <div class="mb-6">
            <a href="{{ route('inventario.categorias.index') }}"
               class="inline-flex items-center gap-2 text-sm font-medium mb-4 hover:opacity-80 transition-opacity"
               style="color: #2B2E2C;">
                <i class="fas fa-arrow-left text-xs"></i> Tipos de Producto
            </a>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 flex items-center justify-between" style="background: linear-gradient(135deg, #2B2E2C 0%, #3A3E3B 100%);">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0"
                             style="background: rgba(247,214,0,0.15);">
                            <i class="fas fa-lightbulb text-2xl" style="color: #F7D600;"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs font-bold px-2 py-0.5 rounded" style="background: rgba(247,214,0,0.25); color: #F7D600;">
                                    {{ $tipoProducto->codigo }}
                                </span>
                            </div>
                            <h1 class="text-xl font-bold text-white">{{ $tipoProducto->nombre }}</h1>
                        </div>
                    </div>
                    <div class="text-right hidden sm:block">
                        <p class="text-3xl font-bold" style="color: #F7D600;">{{ $productos->total() }}</p>
                        <p class="text-xs text-gray-300">{{ $productos->total() === 1 ? 'producto' : 'productos' }}</p>
                    </div>
                </div>

                {{-- Filtros --}}
                <form method="GET" action="{{ route('inventario.categorias.show', $tipoProducto) }}"
                      class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex flex-wrap gap-3 items-end">

                    {{-- Búsqueda --}}
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="buscar" value="{{ request('buscar') }}"
                                   placeholder="Nombre, código Kyrios..."
                                   class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-300 focus:border-transparent bg-white">
                        </div>
                    </div>

                    {{-- Marca --}}
                    <div class="min-w-40">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Marca</label>
                        <select name="marca_id"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-300 bg-white">
                            <option value="">Todas las marcas</option>
                            @foreach($marcas as $marca)
                                <option value="{{ $marca->id }}" {{ request('marca_id') == $marca->id ? 'selected' : '' }}>
                                    {{ $marca->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Estado --}}
                    <div class="min-w-36">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                        <select name="estado"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-300 bg-white">
                            <option value="activo"  {{ $estado === 'activo'  ? 'selected' : '' }}>Activos</option>
                            <option value="todos"   {{ $estado === 'todos'   ? 'selected' : '' }}>Todos</option>
                            <option value="inactivo"{{ $estado === 'inactivo'? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>

                    {{-- Botones --}}
                    <div class="flex gap-2">
                        <button type="submit"
                                class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
                                style="background:#F7D600; color:#2B2E2C;">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        @if(request()->hasAny(['buscar','marca_id','estado']))
                            <a href="{{ route('inventario.categorias.show', $tipoProducto) }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-times mr-1"></i> Limpiar
                            </a>
                        @endif
                    </div>

                    {{-- Ir a crear producto --}}
                    @if(in_array(auth()->user()->role->nombre, ['Administrador','Almacenero']))
                        <a href="{{ route('inventario.productos.create') }}"
                           class="ml-auto px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors"
                           style="background:#2B2E2C; color:white;">
                            <i class="fas fa-plus"></i> Nuevo producto
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Grid de productos --}}
        @if($productos->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($productos as $producto)
            @php
                $stockBajo = $producto->stock_actual <= $producto->stock_minimo && $producto->stock_minimo > 0;
                $sinStock  = $producto->stock_actual <= 0;
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-150 flex flex-col">

                {{-- Imagen --}}
                <div class="relative h-44 bg-gray-50 flex items-center justify-center overflow-hidden">
                    @if($producto->imagen)
                        <img src="{{ asset('storage/'.$producto->imagen) }}"
                             alt="{{ $producto->nombre }}"
                             class="w-full h-full object-contain p-2">
                    @else
                        <div class="flex flex-col items-center text-gray-300">
                            <i class="fas fa-image text-5xl mb-1"></i>
                            <span class="text-xs">Sin imagen</span>
                        </div>
                    @endif

                    {{-- Badge estado --}}
                    <div class="absolute top-2 right-2">
                        @if($producto->estado === 'activo')
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Activo</span>
                        @elseif($producto->estado === 'descontinuado')
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Descontinuado</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">Inactivo</span>
                        @endif
                    </div>

                    {{-- Badge stock --}}
                    @if($sinStock)
                        <div class="absolute top-2 left-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-500 text-white">Sin stock</span>
                        </div>
                    @elseif($stockBajo)
                        <div class="absolute top-2 left-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-400 text-white">Stock bajo</span>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="p-4 flex-1 flex flex-col justify-between">
                    <div>
                        {{-- Marca --}}
                        @if($producto->marca)
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">{{ $producto->marca->nombre }}</p>
                        @endif

                        {{-- Nombre --}}
                        <h3 class="text-sm font-bold text-gray-800 leading-snug line-clamp-2 mb-2">
                            {{ $producto->nombre }}
                        </h3>

                        {{-- Código Kyrios --}}
                        @if($producto->codigo_kyrios)
                            <span class="inline-flex items-center gap-1 text-xs font-mono font-semibold px-2 py-0.5 rounded"
                                  style="background:#F7D600; color:#2B2E2C;">
                                <i class="fas fa-qrcode text-[10px]"></i>
                                {{ $producto->codigo_kyrios }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400 italic">Sin código Kyrios</span>
                        @endif
                    </div>

                    {{-- Stock + Acción --}}
                    <div class="mt-3 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <i class="fas fa-cubes text-xs {{ $sinStock ? 'text-red-400' : ($stockBajo ? 'text-amber-400' : 'text-gray-400') }}"></i>
                            <span class="text-sm font-bold {{ $sinStock ? 'text-red-500' : 'text-gray-700' }}">
                                {{ $producto->stock_actual }}
                            </span>
                            <span class="text-xs text-gray-400">en stock</span>
                        </div>
                        <a href="{{ route('inventario.productos.show', $producto) }}"
                           class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors"
                           style="background:#2B2E2C; color:white;"
                           title="Ver detalle">
                            Ver <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Paginación --}}
        <div class="mt-8">
            {{ $productos->links() }}
        </div>

        @else
        {{-- Sin resultados --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 py-20 text-center text-gray-400">
            <i class="fas fa-box-open text-5xl mb-4"></i>
            <p class="text-lg font-medium text-gray-600">No hay productos en esta categoría</p>
            <p class="text-sm mt-1">
                @if(request()->hasAny(['buscar','marca_id']))
                    Prueba con otros filtros
                @else
                    Aún no se han registrado productos de tipo <strong>{{ $tipoProducto->nombre }}</strong>
                @endif
            </p>
            @if(in_array(auth()->user()->role->nombre, ['Administrador','Almacenero']))
                <a href="{{ route('inventario.productos.create') }}"
                   class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors"
                   style="background:#F7D600; color:#2B2E2C;">
                    <i class="fas fa-plus"></i> Crear primer producto
                </a>
            @endif
        </div>
        @endif

    </div>
</body>
</html>
