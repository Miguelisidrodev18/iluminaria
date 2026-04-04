<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Luminarios Kyrios</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Gestión de Clientes" subtitle="Base de datos de clientes y prospectos" />

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Filtros --}}
        <form method="GET" action="{{ route('clientes.index') }}" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-45">
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de cliente</label>
                <select name="tipo_cliente" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]">
                    <option value="">Todos los tipos</option>
                    @foreach(['ARQ' => 'Arquitecto', 'ING' => 'Ingeniero', 'DIS' => 'Diseñador', 'PN' => 'Persona Natural', 'PJ' => 'Persona Jurídica'] as $val => $label)
                        <option value="{{ $val }}" {{ request('tipo_cliente') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-55">
                <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, empresa, DNI..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]" />
            </div>
            <button type="submit" class="bg-[#2B2E2C] text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700 transition-colors">
                <i class="fas fa-search mr-1"></i>Filtrar
            </button>
            @if(request()->hasAny(['tipo_cliente','buscar']))
                <a href="{{ route('clientes.index') }}" class="text-sm text-gray-500 hover:text-gray-700 py-2 px-2">
                    <i class="fas fa-times mr-1"></i>Limpiar
                </a>
            @endif
        </form>

        {{-- Acciones --}}
        <div class="flex justify-between items-center mb-4">
            <p class="text-sm text-gray-500">{{ $clientes->total() }} cliente(s) encontrado(s)</p>
            <div class="flex gap-2">
                <a href="{{ route('clientes.exportar', request()->query()) }}"
                   class="bg-green-600 text-white hover:bg-green-700 font-semibold py-2 px-4 rounded-lg transition-colors text-sm">
                    <i class="fas fa-file-excel mr-2"></i>Exportar Excel
                </a>
                @if($canCreate)
                    <a href="{{ route('clientes.create') }}"
                       class="bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] font-semibold py-2 px-4 rounded-lg transition-colors text-sm">
                        <i class="fas fa-plus mr-2"></i>Nuevo Cliente
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Celular</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase">Prob. Venta</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($clientes as $cliente)
                        @php
                            $tipoClases = [
                                'ARQ' => 'bg-blue-100 text-blue-800',
                                'ING' => 'bg-green-100 text-green-800',
                                'DIS' => 'bg-purple-100 text-purple-800',
                                'PN'  => 'bg-gray-100 text-gray-700',
                                'PJ'  => 'bg-orange-100 text-orange-800',
                            ];
                            $probVenta = $cliente->visitas->first()?->probabilidad_venta;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 text-sm">
                                <a href="{{ route('clientes.show', $cliente) }}"
                                   class="font-medium text-[#2B2E2C] hover:text-[#F7D600] hover:underline">
                                    {{ $cliente->nombre_completo }}
                                </a>
                                @if($cliente->dni)
                                    <div class="text-xs text-gray-400 mt-0.5">DNI: {{ $cliente->dni }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm">
                                @if($cliente->tipo_cliente)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $tipoClases[$cliente->tipo_cliente] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $cliente->tipo_cliente }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $cliente->empresa ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $cliente->celular ?? $cliente->telefono ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm text-center">
                                @if($probVenta !== null)
                                    @php
                                        $pct = $probVenta;
                                        $color = $pct >= 70 ? 'bg-green-500' : ($pct >= 40 ? 'bg-yellow-400' : 'bg-red-400');
                                    @endphp
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="{{ $color }} h-2 rounded-full" style="width:{{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-700">{{ $pct }}%</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('clientes.show', $cliente) }}" class="text-blue-500 hover:text-blue-700" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($canEdit)
                                        <a href="{{ route('clientes.edit', $cliente) }}" class="text-yellow-500 hover:text-yellow-700" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if($canDelete)
                                        <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="inline"
                                              onsubmit="return confirm('¿Archivar a {{ addslashes($cliente->nombre_completo) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="Archivar">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                                <i class="fas fa-users text-5xl mb-4 block text-gray-200"></i>
                                No se encontraron clientes
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($clientes->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $clientes->links() }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>
