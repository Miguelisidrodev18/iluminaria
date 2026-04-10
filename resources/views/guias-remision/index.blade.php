<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guías de Remisión</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Guías de Remisión"
            subtitle="Gestiona y envía guías de remisión electrónicas a SUNAT"
        />

        {{-- Flash --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2 shadow-sm">
                <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2 shadow-sm">
                <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @php
                $statCards = [
                    ['label' => 'Total guías',  'value' => $stats['total'],     'icon' => 'fa-file-invoice',    'bg' => 'bg-[#2B2E2C]/10', 'text' => 'text-[#2B2E2C]'],
                    ['label' => 'Borradores',   'value' => $stats['borradores'],'icon' => 'fa-pencil',          'bg' => 'bg-gray-100',      'text' => 'text-gray-500'],
                    ['label' => 'Enviadas',     'value' => $stats['enviadas'],  'icon' => 'fa-paper-plane',     'bg' => 'bg-blue-50',       'text' => 'text-blue-600'],
                    ['label' => 'Aceptadas',    'value' => $stats['aceptadas'], 'icon' => 'fa-circle-check',    'bg' => 'bg-emerald-50',    'text' => 'text-emerald-600'],
                ];
            @endphp
            @foreach($statCards as $card)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 {{ $card['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                        <i class="fas {{ $card['icon'] }} {{ $card['text'] }} text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $card['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ $card['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Table card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            {{-- Header toolbar --}}
            <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <h2 class="font-semibold text-gray-800 text-lg">Listado de Guías</h2>
                <a href="{{ route('guias-remision.create') }}"
                   class="inline-flex items-center gap-2 bg-[#2B2E2C] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#3d4140] transition-colors">
                    <i class="fas fa-plus"></i> Nueva Guía
                </a>
            </div>

            {{-- Filtros --}}
            <div class="p-4 bg-gray-50 border-b border-gray-100">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               placeholder="Cliente o número..."
                               class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20 w-48">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                        <select name="estado" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                            <option value="">Todos</option>
                            @foreach(\App\Models\GuiaRemision::ESTADOS as $key => $info)
                                <option value="{{ $key }}" {{ request('estado') === $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                               class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                               class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                    </div>
                    <button type="submit" class="bg-[#2B2E2C] text-white px-4 py-2 rounded-lg text-sm hover:bg-[#3d4140] transition-colors">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                    @if(request()->hasAny(['buscar','estado','fecha_desde','fecha_hasta']))
                        <a href="{{ route('guias-remision.index') }}" class="text-sm text-gray-500 hover:text-gray-700 underline self-end pb-2">Limpiar</a>
                    @endif
                </form>
            </div>

            {{-- Tabla --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="px-5 py-3">N° Guía</th>
                            <th class="px-5 py-3">Fecha emisión</th>
                            <th class="px-5 py-3">Fecha traslado</th>
                            <th class="px-5 py-3">Destinatario</th>
                            <th class="px-5 py-3">Motivo</th>
                            <th class="px-5 py-3">Modalidad</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($query as $guia)
                            @php
                                $estadoInfo = $guia->estado_info;
                                $colorMap = [
                                    'gray'   => 'bg-gray-100 text-gray-600',
                                    'blue'   => 'bg-blue-100 text-blue-700',
                                    'green'  => 'bg-emerald-100 text-emerald-700',
                                    'red'    => 'bg-red-100 text-red-700',
                                    'orange' => 'bg-orange-100 text-orange-700',
                                ];
                                $badge = $colorMap[$estadoInfo['color']] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-semibold text-[#2B2E2C]">
                                    {{ $guia->numero_guia }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $guia->fecha_emision->format('d/m/Y') }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $guia->fecha_traslado->format('d/m/Y') }}</td>
                                <td class="px-5 py-3.5">
                                    @if($guia->cliente)
                                        <span class="font-medium text-gray-800">{{ $guia->cliente->nombre }}</span>
                                        <br><span class="text-xs text-gray-400">{{ $guia->cliente->numero_documento }}</span>
                                    @elseif($guia->destinatario_nombre)
                                        <span class="font-medium text-gray-800">{{ $guia->destinatario_nombre }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $guia->motivo_label }}</td>
                                <td class="px-5 py-3.5 text-gray-600">
                                    @if($guia->modalidad_transporte === '01')
                                        <span class="inline-flex items-center gap-1 text-xs"><i class="fas fa-truck text-gray-400"></i> Privado</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs"><i class="fas fa-road text-gray-400"></i> Público</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ $estadoInfo['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        {{-- Ver --}}
                                        <a href="{{ route('guias-remision.show', $guia) }}"
                                           class="p-2 text-gray-400 hover:text-[#2B2E2C] hover:bg-gray-100 rounded-lg transition-colors" title="Ver detalle">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        {{-- Editar (solo borrador) --}}
                                        @if($guia->estado === 'borrador')
                                            <a href="{{ route('guias-remision.edit', $guia) }}"
                                               class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Editar">
                                                <i class="fas fa-pencil text-xs"></i>
                                            </a>
                                        @endif
                                        {{-- Enviar a SUNAT --}}
                                        @if($guia->puede_enviarse)
                                            <form action="{{ route('guias-remision.enviar-sunat', $guia) }}" method="POST"
                                                  onsubmit="return confirm('¿Enviar esta guía a SUNAT?')">
                                                @csrf
                                                <button type="submit"
                                                        class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Enviar a SUNAT">
                                                    <i class="fas fa-paper-plane text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                        {{-- PDF --}}
                                        <a href="{{ route('guias-remision.pdf', $guia) }}" target="_blank"
                                           class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Descargar PDF">
                                            <i class="fas fa-file-pdf text-xs"></i>
                                        </a>
                                        {{-- Anular --}}
                                        @if($guia->puede_anularse)
                                            <button onclick="abrirAnular({{ $guia->id }}, '{{ $guia->numero_guia }}')"
                                                    class="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Anular">
                                                <i class="fas fa-ban text-xs"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                                    <i class="fas fa-file-invoice text-4xl mb-3 block opacity-30"></i>
                                    No hay guías de remisión registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($query->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $query->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Anular --}}
    <div id="modal-anular" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800">Anular Guía de Remisión</h3>
                <p class="text-sm text-gray-500 mt-1">Guía: <span id="anular-numero" class="font-mono font-semibold"></span></p>
            </div>
            <form id="form-anular" method="POST">
                @csrf
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Motivo de anulación <span class="text-red-500">*</span></label>
                    <textarea name="motivo_anulacion" rows="3" required
                              placeholder="Ingrese el motivo de anulación..."
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 resize-none"></textarea>
                    <p class="text-xs text-orange-600 mt-2 flex items-center gap-1">
                        <i class="fas fa-exclamation-triangle"></i>
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="p-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button type="button" onclick="cerrarAnular()"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-orange-600 text-white text-sm rounded-xl font-medium hover:bg-orange-700 transition-colors">
                        <i class="fas fa-ban mr-1"></i> Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirAnular(id, numero) {
            document.getElementById('anular-numero').textContent = numero;
            document.getElementById('form-anular').action = '/guias-remision/' + id + '/anular';
            document.getElementById('modal-anular').classList.remove('hidden');
            document.getElementById('modal-anular').classList.add('flex');
        }
        function cerrarAnular() {
            document.getElementById('modal-anular').classList.add('hidden');
            document.getElementById('modal-anular').classList.remove('flex');
        }
    </script>
</body>
</html>
