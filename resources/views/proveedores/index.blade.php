<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8">
    <x-header title="Proveedores" subtitle="Gestión de proveedores nacionales, extranjeros e importación" />

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-[#F7D600] p-4">
            <p class="text-xs text-gray-500 mb-1">Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-4">
            <p class="text-xs text-gray-500 mb-1">Activos</p>
            <p class="text-2xl font-bold text-green-700">{{ $stats['activos'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-4">
            <p class="text-xs text-gray-500 mb-1">Extranjeros</p>
            <p class="text-2xl font-bold text-blue-700">{{ $stats['extranjeros'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-purple-500 p-4">
            <p class="text-xs text-gray-500 mb-1">Nacionales</p>
            <p class="text-2xl font-bold text-purple-700">{{ $stats['nacionales'] }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('proveedores.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Nombre, RUC, país..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tipo</label>
                <select name="tipo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                    <option value="">Todos</option>
                    @foreach(\App\Models\Proveedor::TIPOS as $val => $label)
                        <option value="{{ $val }}" {{ request('tipo') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Precio</label>
                <select name="precio" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                    <option value="">Todos</option>
                    @foreach(\App\Models\Proveedor::PRICE_LEVELS as $val => $label)
                        <option value="{{ $val }}" {{ request('precio') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Calidad</label>
                <select name="calidad" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                    <option value="">Todas</option>
                    @foreach(\App\Models\Proveedor::QUALITY_LEVELS as $val => $label)
                        <option value="{{ $val }}" {{ request('calidad') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                <select name="estado" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                    <option value="">Todos</option>
                    <option value="activo"   {{ request('estado') === 'activo'   ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-[#2B2E2C] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#3A3E3B]">
                    <i class="fas fa-search mr-1"></i> Filtrar
                </button>
                @if(request()->hasAny(['buscar','tipo','precio','calidad','estado']))
                    <a href="{{ route('proveedores.index') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50">
                        <i class="fas fa-times mr-1"></i> Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center flex-wrap gap-3">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-list mr-2 text-[#2B2E2C]"></i>
                Lista de Proveedores
                <span class="text-sm font-normal text-gray-400 ml-2">({{ $proveedores->count() }} resultados)</span>
            </h3>
            <div class="flex gap-2">
                @if($canCreate)
                    <a href="{{ route('proveedores.importar.index') }}"
                       class="border border-green-600 text-green-700 hover:bg-green-50 font-semibold py-2 px-4 rounded-lg text-sm transition-colors">
                        <i class="fas fa-file-upload mr-1"></i>Importar Excel
                    </a>
                    <a href="{{ route('proveedores.create') }}"
                       class="bg-[#2B2E2C] text-white font-semibold py-2 px-4 rounded-lg text-sm hover:bg-[#3A3E3B] transition-colors">
                        <i class="fas fa-plus mr-1"></i>Nuevo Proveedor
                    </a>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Proveedor</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">País / Distrito</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Precio</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Calidad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Categorías</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($proveedores as $prov)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-sm text-gray-900">{{ $prov->razon_social }}</p>
                                @if($prov->nombre_comercial)
                                    <p class="text-xs text-gray-400">{{ $prov->nombre_comercial }}</p>
                                @endif
                                @if($prov->ruc)
                                    <p class="text-xs text-gray-400 font-mono">{{ $prov->ruc }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $prov->tipo_badge_class }}">
                                    {{ $prov->tipo_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($prov->country)
                                    <span><i class="fas fa-globe text-xs mr-1 text-gray-400"></i>{{ $prov->country }}</span>
                                @elseif($prov->district)
                                    <span><i class="fas fa-map-marker-alt text-xs mr-1 text-gray-400"></i>{{ $prov->district }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($prov->price_level)
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $prov->price_badge_class }}">
                                        {{ $prov->price_label }}
                                    </span>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($prov->quality_level)
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $prov->quality_badge_class }}">
                                        {{ $prov->quality_label }}
                                    </span>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php $nCats = $prov->categorias_producto_count ?? $prov->categoriasProducto->count() @endphp
                                @if($nCats > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">
                                        <i class="fas fa-tags text-xs"></i> {{ $nCats }}
                                    </span>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $prov->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($prov->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm space-x-3 whitespace-nowrap">
                                <a href="{{ route('proveedores.show', $prov) }}" class="text-[#2B2E2C] hover:text-black" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($canEdit)
                                    <a href="{{ route('proveedores.edit', $prov) }}" class="text-yellow-600 hover:text-yellow-800" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if($canDelete)
                                    <form action="{{ route('proveedores.destroy', $prov) }}" method="POST" class="inline"
                                          onsubmit="return confirm('¿Eliminar este proveedor?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <i class="fas fa-truck text-4xl mb-3 block text-gray-200"></i>
                                No hay proveedores que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
