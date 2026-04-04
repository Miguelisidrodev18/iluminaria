<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectos - Luminarios Kyrios</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Proyectos" subtitle="Gestión de proyectos de clientes" />

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Filtros --}}
        <form method="GET" action="{{ route('proyectos.index') }}" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Prioridad</label>
                <select name="prioridad" class="rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]">
                    <option value="">Todas</option>
                    <option value="A" {{ request('prioridad') === 'A' ? 'selected' : '' }}>Alta</option>
                    <option value="M" {{ request('prioridad') === 'M' ? 'selected' : '' }}>Media</option>
                    <option value="B" {{ request('prioridad') === 'B' ? 'selected' : '' }}>Baja</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Resultado</label>
                <select name="resultado" class="rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]">
                    <option value="">Todos</option>
                    @foreach(\App\Models\Proyecto::$etiquetasResultado as $val => $label)
                        <option value="{{ $val }}" {{ request('resultado') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="ID, nombre, cliente..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]">
            </div>
            <button type="submit" class="bg-[#2B2E2C] text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">
                <i class="fas fa-search mr-1"></i>Filtrar
            </button>
            @if(request()->hasAny(['prioridad','resultado','buscar']))
                <a href="{{ route('proyectos.index') }}" class="text-sm text-gray-500 hover:text-gray-700 py-2">
                    <i class="fas fa-times mr-1"></i>Limpiar
                </a>
            @endif
        </form>

        <div class="flex justify-between items-center mb-4">
            <p class="text-sm text-gray-500">{{ $proyectos->total() }} proyecto(s) encontrado(s)</p>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Proyecto</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase">Prioridad</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase">Resultado</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">F. Entrega aprox.</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">A cargo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($proyectos as $proyecto)
                        @php
                            $prioClases = ['A'=>'bg-red-100 text-red-800','M'=>'bg-yellow-100 text-yellow-800','B'=>'bg-green-100 text-green-800'];
                            $resClases  = ['G'=>'bg-green-100 text-green-800','P'=>'bg-red-100 text-red-800','EP'=>'bg-blue-100 text-blue-800','ENT'=>'bg-gray-100 text-gray-700','ENV'=>'bg-gray-100 text-gray-700','I'=>'bg-orange-100 text-orange-800'];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 text-sm">
                                <a href="{{ route('proyectos.show', $proyecto) }}"
                                   class="font-mono font-semibold text-blue-600 hover:underline">{{ $proyecto->id_proyecto }}</a>
                            </td>
                            <td class="px-5 py-4 text-sm font-medium text-gray-800 max-w-48 truncate" title="{{ $proyecto->nombre_proyecto }}">
                                {{ $proyecto->nombre_proyecto }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600">
                                @if($proyecto->cliente)
                                    <a href="{{ route('clientes.show', $proyecto->cliente) }}"
                                       class="hover:underline text-gray-700">{{ $proyecto->cliente->nombre_completo }}</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $prioClases[$proyecto->prioridad] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ \App\Models\Proyecto::$etiquetasPrioridad[$proyecto->prioridad] ?? $proyecto->prioridad }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($proyecto->resultado)
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $resClases[$proyecto->resultado] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ \App\Models\Proyecto::$etiquetasResultado[$proyecto->resultado] ?? $proyecto->resultado }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Sin estado</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600">
                                {{ $proyecto->fecha_entrega_aprox?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $proyecto->persona_cargo ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm">
                                <a href="{{ route('proyectos.show', $proyecto) }}"
                                   class="text-blue-500 hover:text-blue-700" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-400">
                                <i class="fas fa-project-diagram text-5xl mb-4 block text-gray-200"></i>
                                No se encontraron proyectos
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($proyectos->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $proyectos->links() }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>
