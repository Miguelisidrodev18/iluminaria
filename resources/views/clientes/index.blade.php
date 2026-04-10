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
        <form method="GET" action="{{ route('clientes.index') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4"
              x-data="busquedaClientes()" x-on:submit="enviarFormulario($event)">
            <div class="flex flex-wrap gap-3 items-end">
                {{-- Filtro unificado: tipos de cliente + etiquetas complementarias --}}
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Segmento</label>
                    <select name="tipo_cliente" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]"
                            onchange="this.form.submit()">
                        <option value="">Todos los clientes</option>
                        <optgroup label="── Profesión">
                            @foreach(['ARQ' => 'Arquitecto/a', 'ING' => 'Ingeniero/a', 'DIS' => 'Diseñador/a', 'PN' => 'Persona Natural', 'PJ' => 'Empresa / Jurídica'] as $val => $label)
                                <option value="{{ $val }}" {{ request('tipo_cliente') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="── Etiqueta">
                            @foreach($etiquetas as $etiq)
                                <option value="etq:{{ $etiq }}" {{ request('tipo_cliente') === 'etq:'.$etiq ? 'selected' : '' }}>{{ $etiq }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    {{-- Campo oculto para transportar etiqueta si viene de chip --}}
                    @if(request('etiqueta'))
                        <input type="hidden" name="etiqueta" value="{{ request('etiqueta') }}">
                    @endif
                </div>

                {{-- Búsqueda dinámica --}}
                <div class="flex-1 min-w-[240px] relative">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Buscar cliente</label>
                    <div class="relative">
                        <input type="text"
                               name="buscar"
                               x-model="texto"
                               x-on:input.debounce.300ms="buscar()"
                               x-on:focus="abrirSiHayResultados()"
                               x-on:keydown.escape="cerrar()"
                               x-on:keydown.arrow-down.prevent="moverAbajo()"
                               x-on:keydown.arrow-up.prevent="moverArriba()"
                               x-on:keydown.enter.prevent="seleccionarActivo()"
                               value="{{ request('buscar') }}"
                               placeholder="Nombre, empresa, DNI, celular..."
                               autocomplete="off"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600] pr-8" />
                        <span class="absolute right-2 top-2 text-gray-400 pointer-events-none">
                            <i class="fas fa-search text-xs" x-show="!cargando"></i>
                            <i class="fas fa-spinner fa-spin text-xs" x-show="cargando"></i>
                        </span>
                    </div>

                    {{-- Dropdown de sugerencias --}}
                    <div x-show="abierto && resultados.length > 0"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-on:click.outside="cerrar()"
                         class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden"
                         style="display:none">

                        <template x-for="(item, idx) in resultados" :key="item.id">
                            <a :href="'/clientes/' + item.id"
                               x-on:mouseenter="activo = idx"
                               :class="activo === idx ? 'bg-yellow-50' : 'hover:bg-gray-50'"
                               class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 last:border-0 transition-colors cursor-pointer">
                                {{-- Ícono tipo cliente --}}
                                <div class="mt-0.5 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                     :class="{
                                         'bg-blue-100 text-blue-700':   item.tipo_cliente === 'ARQ',
                                         'bg-green-100 text-green-700': item.tipo_cliente === 'ING',
                                         'bg-purple-100 text-purple-700': item.tipo_cliente === 'DIS',
                                         'bg-gray-100 text-gray-600':   item.tipo_cliente === 'PN' || !item.tipo_cliente,
                                         'bg-orange-100 text-orange-700': item.tipo_cliente === 'PJ',
                                     }">
                                    <span x-text="item.tipo_cliente || '?'"></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate" x-text="item.texto"></p>
                                    <p class="text-xs text-gray-400 truncate" x-text="item.subtexto" x-show="item.subtexto"></p>
                                </div>
                                <i class="fas fa-arrow-right text-xs text-gray-300 ml-auto mt-1 shrink-0"
                                   x-show="activo === idx"></i>
                            </a>
                        </template>

                        {{-- Opción: buscar todos --}}
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-4 py-2.5 text-xs text-[#2B2E2C] bg-gray-50 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-search text-gray-400"></i>
                            Ver todos los resultados para "<span x-text="texto" class="font-semibold"></span>"
                        </button>
                    </div>

                    {{-- Mensaje sin resultados --}}
                    <div x-show="abierto && resultados.length === 0 && texto.length >= 3 && !cargando"
                         x-on:click.outside="cerrar()"
                         class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg px-4 py-3 text-xs text-gray-400"
                         style="display:none">
                        <i class="fas fa-search mr-1"></i> Sin resultados para "<span x-text="texto"></span>"
                    </div>
                </div>

                <button type="submit" class="bg-[#2B2E2C] text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700 transition-colors">
                    <i class="fas fa-search mr-1"></i>Filtrar
                </button>
                @if(request()->hasAny(['tipo_cliente','buscar']))
                    <a href="{{ route('clientes.index') }}" class="text-sm text-gray-500 hover:text-gray-700 py-2 px-2">
                        <i class="fas fa-times mr-1"></i>Limpiar
                    </a>
                @endif
            </div>

            {{-- Chips de acceso rápido --}}
            <div class="mt-3 flex flex-wrap gap-2 items-center">
                <span class="text-xs text-gray-400">Acceso rápido:</span>
                @php
                    $chipsRapidos = [
                        // [label, param tipo_cliente, valor]
                        ['Arquitecto/a', 'tipo_cliente', 'ARQ',      'bg-indigo-100 text-indigo-700 border-indigo-300'],
                        ['Ingeniero/a',  'tipo_cliente', 'ING',      'bg-violet-100 text-violet-700 border-violet-300'],
                        ['Diseñador/a',  'tipo_cliente', 'DIS',      'bg-purple-100 text-purple-700 border-purple-300'],
                        ['Decorador/a',  'tipo_cliente', 'etq:Decorador/a', 'bg-pink-100 text-pink-700 border-pink-300'],
                        ['Paisajista',   'tipo_cliente', 'etq:Paisajista',  'bg-green-100 text-green-700 border-green-300'],
                        ['Mamá',         'tipo_cliente', 'etq:Mamá',  'bg-rose-100 text-rose-700 border-rose-300'],
                        ['Papá',         'tipo_cliente', 'etq:Papá',  'bg-sky-100 text-sky-700 border-sky-300'],
                        ['Mujer',        'tipo_cliente', 'etq:Mujer', 'bg-pink-100 text-pink-700 border-pink-300'],
                        ['Hombre',       'tipo_cliente', 'etq:Hombre','bg-blue-100 text-blue-700 border-blue-300'],
                    ];
                    $segmentoActual = request('tipo_cliente');
                @endphp
                @foreach($chipsRapidos as [$label, $param, $valor, $clr])
                    <a href="{{ route('clientes.index', array_merge(request()->except('tipo_cliente','etiqueta','page'), [$param => $valor])) }}"
                       class="text-xs px-3 py-1 rounded-full border font-medium transition-all {{ $clr }}
                              {{ $segmentoActual === $valor ? 'ring-2 ring-offset-1 ring-gray-500 font-bold' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </form>

        {{-- Acciones --}}
        <div class="flex justify-between items-center mb-4">
            <div>
                <p class="text-sm text-gray-500">
                    {{ $clientes->total() }} cliente(s) encontrado(s)
                    @if(request('tipo_cliente'))
                        @php
                            $labelActual = match(request('tipo_cliente')) {
                                'ARQ' => 'Arquitecto/a', 'ING' => 'Ingeniero/a', 'DIS' => 'Diseñador/a',
                                'PN'  => 'Persona Natural', 'PJ' => 'Empresa',
                                default => str_starts_with(request('tipo_cliente'), 'etq:')
                                    ? substr(request('tipo_cliente'), 4) : request('tipo_cliente'),
                            };
                        @endphp
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-filter mr-1"></i>{{ $labelActual }}
                        </span>
                    @endif
                </p>
            </div>
            <div class="flex gap-2 flex-wrap justify-end">
                <a href="{{ route('clientes.difusion') }}"
                   class="bg-green-500 text-white hover:bg-green-600 font-semibold py-2 px-4 rounded-lg transition-colors text-sm">
                    <i class="fab fa-whatsapp mr-2"></i>Listas Difusión
                </a>
                <a href="{{ route('clientes.exportar', request()->query()) }}"
                   class="bg-emerald-700 text-white hover:bg-emerald-800 font-semibold py-2 px-4 rounded-lg transition-colors text-sm">
                    <i class="fas fa-file-excel mr-2"></i>Excel
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
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Empresa</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Celular</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Etiquetas</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Prob. Venta</th>
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
                            $tipoLabels = [
                                'ARQ' => 'Arquitecto',
                                'ING' => 'Ingeniero',
                                'DIS' => 'Diseñador',
                                'PN'  => 'P. Natural',
                                'PJ'  => 'P. Jurídica',
                            ];
                            $probVenta = $cliente->visitas->first()?->probabilidad_venta;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 text-sm">
                                <a href="{{ route('clientes.show', $cliente) }}"
                                   class="font-semibold text-[#2B2E2C] hover:text-[#F7D600] hover:underline">
                                    {{ $cliente->nombre_completo }}
                                </a>
                                @if($cliente->dni)
                                    <div class="text-xs text-gray-400 mt-0.5">DNI: {{ $cliente->dni }}</div>
                                @endif
                                {{-- WhatsApp badge --}}
                                @if($cliente->acepta_whatsapp && $cliente->celular)
                                    <span class="inline-flex items-center mt-0.5 text-xs text-green-600">
                                        <i class="fab fa-whatsapp mr-1"></i>
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm">
                                @if($cliente->tipo_cliente)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $tipoClases[$cliente->tipo_cliente] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $tipoLabels[$cliente->tipo_cliente] ?? $cliente->tipo_cliente }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 hidden md:table-cell">{{ $cliente->empresa ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 hidden md:table-cell">
                                @if($cliente->celular)
                                    <a href="https://wa.me/51{{ preg_replace('/\D/', '', $cliente->celular) }}"
                                       target="_blank"
                                       class="text-green-600 hover:text-green-800 hover:underline">
                                        {{ $cliente->celular }}
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm hidden lg:table-cell">
                                @if($cliente->etiquetas && count($cliente->etiquetas))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($cliente->etiquetas as $etiq)
                                            @php
                                                $etiqColorMap = [
                                                    'Mamá'         => 'bg-rose-100 text-rose-700',
                                                    'Papá'         => 'bg-sky-100 text-sky-700',
                                                    'Mujer'        => 'bg-pink-100 text-pink-700',
                                                    'Hombre'       => 'bg-blue-100 text-blue-700',
                                                    'Arquitecto/a' => 'bg-indigo-100 text-indigo-700',
                                                    'Ingeniero/a'  => 'bg-violet-100 text-violet-700',
                                                    'Diseñador/a'  => 'bg-purple-100 text-purple-700',
                                                    'Decorador/a'  => 'bg-fuchsia-100 text-fuchsia-700',
                                                    'Paisajista'   => 'bg-green-100 text-green-700',
                                                    'Cliente Final'=> 'bg-gray-100 text-gray-600',
                                                ];
                                            @endphp
                                            <a href="{{ route('clientes.index', ['etiqueta' => $etiq]) }}"
                                               class="text-xs px-2 py-0.5 rounded-full font-medium hover:opacity-80 transition-opacity
                                                      {{ $etiqColorMap[$etiq] ?? 'bg-gray-100 text-gray-600' }}">
                                                {{ $etiq }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-center hidden sm:table-cell">
                                @if($probVenta !== null)
                                    @php
                                        $pct   = $probVenta;
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
                            <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                                <i class="fas fa-users text-5xl mb-4 block text-gray-200"></i>
                                No se encontraron clientes
                                @if(request()->hasAny(['tipo_cliente','etiqueta','buscar']))
                                    <div class="mt-2">
                                        <a href="{{ route('clientes.index') }}" class="text-sm text-blue-500 hover:underline">Limpiar filtros</a>
                                    </div>
                                @endif
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
    <script>
    function busquedaClientes() {
        return {
            texto:     '{{ addslashes(request("buscar", "")) }}',
            resultados: [],
            abierto:   false,
            cargando:  false,
            activo:    -1,
            timer:     null,

            async buscar() {
                this.activo = -1;
                if (this.texto.length < 3) {
                    this.resultados = [];
                    this.abierto    = false;
                    return;
                }
                this.cargando = true;
                try {
                    const res  = await fetch(`{{ route('api.clientes.buscar-texto') }}?q=${encodeURIComponent(this.texto)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    this.resultados = await res.json();
                    this.abierto    = true;
                } catch(e) {
                    this.resultados = [];
                }
                this.cargando = false;
            },

            abrirSiHayResultados() {
                if (this.resultados.length > 0) this.abierto = true;
            },

            cerrar() {
                this.abierto = false;
                this.activo  = -1;
            },

            moverAbajo() {
                if (!this.abierto) return;
                this.activo = Math.min(this.activo + 1, this.resultados.length - 1);
            },

            moverArriba() {
                if (!this.abierto) return;
                this.activo = Math.max(this.activo - 1, -1);
            },

            seleccionarActivo() {
                if (this.activo >= 0 && this.resultados[this.activo]) {
                    window.location.href = '/clientes/' + this.resultados[this.activo].id;
                }
                // Si no hay activo, el Enter envía el form normalmente
            },

            enviarFormulario(event) {
                // Permite que el form haga submit normal (buscar en la tabla)
                this.cerrar();
            },
        };
    }
    </script>
</body>
</html>
