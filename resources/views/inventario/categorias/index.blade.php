<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipos de Producto — Kyrios</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">

        {{-- Header --}}
        <x-header
            title="Tipos de Producto"
            subtitle="Explora el catálogo de productos por tipo"
        />

        {{-- Flash --}}
        @if(session('success'))
            <div class="mb-5 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background:#2B2E2C;">
                    <i class="fas fa-layer-group text-xl" style="color:#F7D600;"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Tipos</p>
                    <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $tiposProducto->count() }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-boxes text-xl text-green-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Productos</p>
                    <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $totalProductos }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background:#F7D600;">
                    <i class="fas fa-check-circle text-xl" style="color:#2B2E2C;"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Activos</p>
                    <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $tiposProducto->where('activo', true)->count() }}</p>
                </div>
            </div>
        </div>

        {{-- Grid de categorías (tipos de producto) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($tiposProducto as $tipo)
            @php
                $iconos = [
                    'LU' => 'fa-lightbulb',
                    'LA' => 'fa-lightbulb',
                    'CL' => 'fa-ruler-horizontal',
                    'AC' => 'fa-plug',
                    'FU' => 'fa-bolt',
                    'SM' => 'fa-wifi',
                    'EA' => 'fa-microchip',
                    'PE' => 'fa-shapes',
                    'PA' => 'fa-th-large',
                    'VE' => 'fa-wind',
                    'CA' => 'fa-cable-car',
                    'PO' => 'fa-circle-nodes',
                    'LE' => 'fa-star',
                    'SO' => 'fa-sun',
                    'RE' => 'fa-rotate',
                ];
                $icono = $iconos[$tipo->codigo] ?? 'fa-tag';
                $porcentaje = $tipo->productos_count > 0
                    ? round(($tipo->productos_activos_count / $tipo->productos_count) * 100)
                    : 0;
            @endphp
            <a href="{{ route('inventario.categorias.show', $tipo) }}"
               class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-200 flex flex-col">

                {{-- Cabecera con color Kyrios --}}
                <div class="p-6 flex items-center justify-between" style="background: linear-gradient(135deg, #2B2E2C 0%, #3A3E3B 100%);">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: rgba(247,214,0,0.15);">
                        <i class="fas {{ $icono }} text-2xl" style="color: #F7D600;"></i>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold px-2 py-1 rounded-full" style="background: rgba(247,214,0,0.2); color: #F7D600;">
                            {{ $tipo->codigo }}
                        </span>
                        @if(!$tipo->activo)
                            <p class="text-xs text-gray-400 mt-1">Inactivo</p>
                        @endif
                    </div>
                </div>

                {{-- Cuerpo --}}
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 group-hover:text-[#2B2E2C] transition-colors">
                            {{ $tipo->nombre }}
                        </h3>
                        @if($tipo->usa_tipo_luminaria)
                            <span class="inline-flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <i class="fas fa-sitemap text-[10px]"></i> Con subtipo
                            </span>
                        @endif
                    </div>

                    {{-- Métricas --}}
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                            <span>{{ $tipo->productos_activos_count }} activos</span>
                            <span class="font-semibold text-gray-700">{{ $tipo->productos_count }} total</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full transition-all" style="width: {{ $porcentaje }}%; background-color: #F7D600;"></div>
                        </div>
                    </div>

                    {{-- Pie --}}
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">
                            {{ $tipo->productos_count }}
                            <span class="text-gray-400 font-normal text-xs">{{ $tipo->productos_count === 1 ? 'producto' : 'productos' }}</span>
                        </span>
                        <span class="text-xs font-semibold flex items-center gap-1 group-hover:gap-2 transition-all" style="color: #2B2E2C;">
                            Ver catálogo <i class="fas fa-arrow-right text-[10px]"></i>
                        </span>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-20 text-gray-400">
                <i class="fas fa-layer-group text-5xl mb-4"></i>
                <p class="text-lg font-medium">No hay tipos de producto registrados</p>
            </div>
            @endforelse
        </div>

    </div>
</body>
</html>
