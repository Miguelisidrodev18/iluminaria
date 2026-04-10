<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guía {{ $guiaRemision->numero_guia }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('guias-remision.index') }}"
                   class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 font-mono">{{ $guiaRemision->numero_guia }}</h1>
                    <p class="text-sm text-gray-500">Guía de Remisión Electrónica — Emitida el {{ $guiaRemision->fecha_emision->format('d/m/Y') }}</p>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="flex flex-wrap gap-2">
                {{-- Editar --}}
                @if($guiaRemision->estado === 'borrador')
                    <a href="{{ route('guias-remision.edit', $guiaRemision) }}"
                       class="inline-flex items-center gap-2 border border-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-pencil"></i> Editar
                    </a>
                @endif

                {{-- Enviar a SUNAT --}}
                @if($guiaRemision->puede_enviarse)
                    <form action="{{ route('guias-remision.enviar-sunat', $guiaRemision) }}" method="POST"
                          onsubmit="return confirm('¿Enviar esta guía a SUNAT ahora?')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors">
                            <i class="fas fa-paper-plane"></i> Enviar a SUNAT
                        </button>
                    </form>
                @endif

                {{-- PDF --}}
                <a href="{{ route('guias-remision.pdf', $guiaRemision) }}" target="_blank"
                   class="inline-flex items-center gap-2 border border-red-200 text-red-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-red-50 transition-colors">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>

                {{-- Anular --}}
                @if($guiaRemision->puede_anularse)
                    <button onclick="document.getElementById('modal-anular').classList.remove('hidden'); document.getElementById('modal-anular').classList.add('flex')"
                            class="inline-flex items-center gap-2 border border-orange-200 text-orange-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-orange-50 transition-colors">
                        <i class="fas fa-ban"></i> Anular
                    </button>
                @endif

                {{-- Eliminar --}}
                @if($guiaRemision->estado === 'borrador')
                    <form action="{{ route('guias-remision.destroy', $guiaRemision) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar esta guía permanentemente?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 border border-red-200 text-red-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-red-50 transition-colors">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
                <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Estado badge prominente --}}
        @php
            $estadoInfo = $guiaRemision->estado_info;
            $colorMap = [
                'gray'   => ['bg' => 'bg-gray-100',      'text' => 'text-gray-700',    'border' => 'border-gray-200'],
                'blue'   => ['bg' => 'bg-blue-50',       'text' => 'text-blue-700',    'border' => 'border-blue-200'],
                'green'  => ['bg' => 'bg-emerald-50',    'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
                'red'    => ['bg' => 'bg-red-50',        'text' => 'text-red-700',     'border' => 'border-red-200'],
                'orange' => ['bg' => 'bg-orange-50',     'text' => 'text-orange-700',  'border' => 'border-orange-200'],
            ];
            $colors = $colorMap[$estadoInfo['color']] ?? $colorMap['gray'];
        @endphp

        <div class="mb-6 flex items-center gap-4">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-semibold {{ $colors['bg'] }} {{ $colors['text'] }} {{ $colors['border'] }}">
                @if($guiaRemision->estado === 'aceptado') <i class="fas fa-circle-check"></i>
                @elseif($guiaRemision->estado === 'rechazado') <i class="fas fa-circle-xmark"></i>
                @elseif($guiaRemision->estado === 'anulado') <i class="fas fa-ban"></i>
                @elseif($guiaRemision->estado === 'enviado') <i class="fas fa-paper-plane"></i>
                @else <i class="fas fa-pencil"></i>
                @endif
                {{ $estadoInfo['label'] }}
            </span>
            @if($guiaRemision->sunat_enlace_pdf)
                <a href="{{ $guiaRemision->sunat_enlace_pdf }}" target="_blank"
                   class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                    <i class="fas fa-external-link-alt text-xs"></i> Ver PDF en SUNAT
                </a>
            @endif
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Info principal --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- Datos generales --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Datos del Traslado</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Motivo</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $guiaRemision->motivo_label }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Modalidad</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $guiaRemision->modalidad_label }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Fecha traslado</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $guiaRemision->fecha_traslado->format('d/m/Y') }}</p>
                        </div>
                        @if($guiaRemision->peso_bruto)
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Peso bruto</p>
                                <p class="text-sm font-semibold text-gray-800">{{ number_format($guiaRemision->peso_bruto, 3) }} KG</p>
                            </div>
                        @endif
                        @if($guiaRemision->numero_bultos)
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">N° bultos</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $guiaRemision->numero_bultos }}</p>
                            </div>
                        @endif
                        @if($guiaRemision->venta)
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Venta vinculada</p>
                                <a href="{{ route('ventas.show', $guiaRemision->venta) }}"
                                   class="text-sm font-semibold text-blue-600 hover:underline">
                                    {{ $guiaRemision->venta->codigo }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Ruta --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Ruta de Traslado</h2>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="flex-1 bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                            <p class="text-xs font-semibold text-emerald-600 flex items-center gap-1 mb-2">
                                <i class="fas fa-map-pin"></i> PUNTO DE PARTIDA
                            </p>
                            @if($guiaRemision->partida_ubigeo)
                                <p class="text-xs text-gray-400 font-mono mb-1">Ubigeo: {{ $guiaRemision->partida_ubigeo }}</p>
                            @endif
                            <p class="text-sm font-medium text-gray-800">{{ $guiaRemision->partida_direccion ?? '—' }}</p>
                        </div>
                        <div class="text-gray-400 flex-shrink-0">
                            <i class="fas fa-arrow-right text-xl"></i>
                        </div>
                        <div class="flex-1 bg-red-50 border border-red-100 rounded-xl p-4">
                            <p class="text-xs font-semibold text-red-600 flex items-center gap-1 mb-2">
                                <i class="fas fa-flag"></i> PUNTO DE LLEGADA
                            </p>
                            @if($guiaRemision->llegada_ubigeo)
                                <p class="text-xs text-gray-400 font-mono mb-1">Ubigeo: {{ $guiaRemision->llegada_ubigeo }}</p>
                            @endif
                            <p class="text-sm font-medium text-gray-800">{{ $guiaRemision->llegada_direccion ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Detalle de bienes --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">
                            Detalle de Bienes ({{ $guiaRemision->detalles->count() }} ítem(s))
                        </h2>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                                <th class="px-5 py-3">Descripción</th>
                                <th class="px-5 py-3">Código</th>
                                <th class="px-5 py-3">Unidad</th>
                                <th class="px-5 py-3 text-right">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($guiaRemision->detalles as $detalle)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3.5 font-medium text-gray-800">{{ $detalle->descripcion }}</td>
                                    <td class="px-5 py-3.5 font-mono text-gray-500 text-xs">{{ $detalle->codigo ?: '—' }}</td>
                                    <td class="px-5 py-3.5 text-gray-600">
                                        {{ \App\Models\GuiaRemision::UNIDADES[$detalle->unidad_medida] ?? $detalle->unidad_medida }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-semibold text-gray-800">
                                        {{ number_format($detalle->cantidad, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">Sin ítems</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Respuesta SUNAT --}}
                @if($guiaRemision->sunat_respuesta)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Respuesta SUNAT</h2>
                        @if($guiaRemision->sunat_hash)
                            <p class="text-xs text-gray-500 mb-2">Hash: <span class="font-mono text-gray-700">{{ $guiaRemision->sunat_hash }}</span></p>
                        @endif
                        <div class="flex gap-3 flex-wrap">
                            @if($guiaRemision->sunat_enlace_pdf)
                                <a href="{{ $guiaRemision->sunat_enlace_pdf }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 text-xs bg-red-50 text-red-600 border border-red-100 px-3 py-1.5 rounded-lg hover:bg-red-100 transition-colors">
                                    <i class="fas fa-file-pdf"></i> PDF SUNAT
                                </a>
                            @endif
                            @if($guiaRemision->sunat_enlace_xml)
                                <a href="{{ $guiaRemision->sunat_enlace_xml }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 text-xs bg-blue-50 text-blue-600 border border-blue-100 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
                                    <i class="fas fa-file-code"></i> XML
                                </a>
                            @endif
                            @if($guiaRemision->sunat_enlace_cdr)
                                <a href="{{ $guiaRemision->sunat_enlace_cdr }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 text-xs bg-emerald-50 text-emerald-600 border border-emerald-100 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition-colors">
                                    <i class="fas fa-file-shield"></i> CDR
                                </a>
                            @endif
                        </div>
                        <details class="mt-3">
                            <summary class="text-xs text-gray-400 cursor-pointer hover:text-gray-600">Ver respuesta completa</summary>
                            <pre class="mt-2 text-xs bg-gray-50 rounded-lg p-3 overflow-auto max-h-40 font-mono">{{ json_encode($guiaRemision->sunat_respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                    </div>
                @endif

            </div>

            {{-- Sidebar derecha --}}
            <div class="space-y-6">

                {{-- Destinatario --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Destinatario</h2>
                    @php
                        $nombreDest = $guiaRemision->destinatario_nombre ?? $guiaRemision->cliente?->nombre ?? '—';
                        $numDest    = $guiaRemision->destinatario_num_doc ?? $guiaRemision->cliente?->numero_documento ?? '—';
                    @endphp
                    <p class="font-semibold text-gray-800">{{ $nombreDest }}</p>
                    @if($numDest !== '—')
                        <p class="text-sm text-gray-500 font-mono mt-1">{{ $numDest }}</p>
                    @endif
                    @if($guiaRemision->destinatario_direccion)
                        <p class="text-sm text-gray-500 mt-2">{{ $guiaRemision->destinatario_direccion }}</p>
                    @endif
                    @if($guiaRemision->cliente)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <a href="{{ route('clientes.show', $guiaRemision->cliente) }}"
                               class="text-xs text-blue-600 hover:underline">
                                <i class="fas fa-user mr-1"></i> Ver cliente
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Transportista --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Transportista</h2>
                    @if($guiaRemision->modalidad_transporte === '01')
                        <p class="text-xs text-blue-600 font-medium mb-3 flex items-center gap-1">
                            <i class="fas fa-truck"></i> Transporte Privado
                        </p>
                        @if($guiaRemision->placa_vehiculo)
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-id-card text-gray-400 w-4"></i>
                                <span class="text-sm font-mono font-bold tracking-widest">{{ strtoupper($guiaRemision->placa_vehiculo) }}</span>
                            </div>
                        @endif
                        @if($guiaRemision->conductor_nombre)
                            <p class="text-sm text-gray-700"><i class="fas fa-user text-gray-400 w-4"></i> {{ $guiaRemision->conductor_nombre }}</p>
                            <p class="text-xs text-gray-400 ml-5">{{ $guiaRemision->conductor_num_doc }}</p>
                        @endif
                        @if($guiaRemision->conductor_licencia)
                            <p class="text-xs text-gray-500 mt-1"><i class="fas fa-address-card text-gray-400 w-4"></i> Lic: {{ $guiaRemision->conductor_licencia }}</p>
                        @endif
                    @else
                        <p class="text-xs text-purple-600 font-medium mb-3 flex items-center gap-1">
                            <i class="fas fa-road"></i> Transporte Público
                        </p>
                        @if($guiaRemision->transportista_nombre)
                            <p class="text-sm font-semibold text-gray-800">{{ $guiaRemision->transportista_nombre }}</p>
                            <p class="text-xs text-gray-500 font-mono mt-1">{{ $guiaRemision->transportista_ruc }}</p>
                        @else
                            <p class="text-sm text-gray-400">No especificado</p>
                        @endif
                    @endif
                </div>

                {{-- Emisor --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Remitente / Emisor</h2>
                    <p class="font-semibold text-gray-800">{{ $empresa?->razon_social }}</p>
                    <p class="text-xs font-mono text-gray-500 mt-1">RUC: {{ $empresa?->ruc }}</p>
                    @if($empresa?->direccion)
                        <p class="text-xs text-gray-500 mt-1">{{ $empresa->direccion }}</p>
                    @endif
                </div>

                {{-- Meta --}}
                <div class="bg-gray-50 rounded-2xl border border-gray-100 p-4 text-xs text-gray-500 space-y-1">
                    <p><i class="fas fa-user w-4"></i> Creado por: {{ $guiaRemision->usuario?->name ?? '—' }}</p>
                    <p><i class="fas fa-building w-4"></i> Sucursal: {{ $guiaRemision->sucursal?->nombre ?? '—' }}</p>
                    <p><i class="fas fa-clock w-4"></i> {{ $guiaRemision->created_at->format('d/m/Y H:i') }}</p>
                    @if($guiaRemision->observaciones)
                        <p class="mt-2 pt-2 border-t border-gray-200"><i class="fas fa-comment w-4"></i> {{ $guiaRemision->observaciones }}</p>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Modal Anular --}}
    <div id="modal-anular" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800">Anular Guía</h3>
                <p class="text-sm text-gray-500 mt-1 font-mono">{{ $guiaRemision->numero_guia }}</p>
            </div>
            <form action="{{ route('guias-remision.anular', $guiaRemision) }}" method="POST">
                @csrf
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Motivo de anulación <span class="text-red-500">*</span></label>
                    <textarea name="motivo_anulacion" rows="3" required
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 resize-none"
                              placeholder="Ingrese el motivo..."></textarea>
                </div>
                <div class="p-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button type="button"
                            onclick="document.getElementById('modal-anular').classList.add('hidden'); document.getElementById('modal-anular').classList.remove('flex')"
                            class="px-4 py-2 text-sm text-gray-600">Cancelar</button>
                    <button type="submit"
                            class="px-5 py-2 bg-orange-600 text-white text-sm rounded-xl font-medium hover:bg-orange-700">
                        <i class="fas fa-ban mr-1"></i> Anular
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
