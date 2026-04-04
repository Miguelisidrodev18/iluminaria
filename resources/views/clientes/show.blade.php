<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $cliente->nombre_completo }} - Luminarios Kyrios</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Ficha de Cliente" subtitle="{{ $cliente->nombre_completo }}" />

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Cabecera --}}
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl font-bold"
                         style="background-color:#2B2E2C">
                        {{ strtoupper(substr($cliente->nombres ?? $cliente->nombre, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">{{ $cliente->nombre_completo }}</h1>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @if($cliente->tipo_cliente)
                                @php $tipoClases=['ARQ'=>'bg-blue-100 text-blue-800','ING'=>'bg-green-100 text-green-800','DIS'=>'bg-purple-100 text-purple-800','PN'=>'bg-gray-100 text-gray-700','PJ'=>'bg-orange-100 text-orange-800']; @endphp
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $tipoClases[$cliente->tipo_cliente] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $cliente->tipo_cliente }}
                                </span>
                            @endif
                            @if($cliente->empresa)
                                <span class="text-sm text-gray-500"><i class="fas fa-building mr-1"></i>{{ $cliente->empresa }}</span>
                            @endif
                            @if($cliente->celular)
                                <span class="text-sm text-gray-500"><i class="fas fa-phone mr-1"></i>{{ $cliente->celular }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('clientes.edit', $cliente) }}"
                       class="bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] font-semibold py-2 px-4 rounded-lg text-sm transition-colors">
                        <i class="fas fa-edit mr-2"></i>Editar
                    </a>
                    <a href="{{ route('clientes.index') }}" class="text-gray-500 hover:text-gray-700 py-2 px-3 rounded-lg text-sm border border-gray-200 hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="border-b border-gray-200 px-6">
                <nav class="flex space-x-1 -mb-px">
                    <button onclick="showTab('personal')" id="tab-personal"
                            class="tab-btn py-4 px-4 text-sm font-medium border-b-2 border-[#F7D600] text-[#2B2E2C] transition-colors">
                        <i class="fas fa-user mr-2"></i>Datos Personales
                    </button>
                    <button onclick="showTab('visita')" id="tab-visita"
                            class="tab-btn py-4 px-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-calendar-check mr-2"></i>Visitas
                        <span class="ml-1 bg-gray-100 text-gray-600 text-xs rounded-full px-1.5 py-0.5">{{ $cliente->visitas->count() }}</span>
                    </button>
                    <button onclick="showTab('proyectos')" id="tab-proyectos"
                            class="tab-btn py-4 px-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-project-diagram mr-2"></i>Proyectos
                        <span class="ml-1 bg-gray-100 text-gray-600 text-xs rounded-full px-1.5 py-0.5">{{ $cliente->proyectos->count() }}</span>
                    </button>
                </nav>
            </div>

            {{-- Panel: Datos Personales --}}
            <div id="panel-personal" class="tab-panel p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Datos personales --}}
                    <div class="lg:col-span-2 space-y-5">
                        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide border-b pb-2">Información Personal</h3>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div><span class="text-gray-400">Apellidos</span><p class="font-medium text-gray-800">{{ $cliente->apellidos ?? '—' }}</p></div>
                            <div><span class="text-gray-400">Nombres</span><p class="font-medium text-gray-800">{{ $cliente->nombres ?? '—' }}</p></div>
                            <div><span class="text-gray-400">DNI</span><p class="font-medium text-gray-800">{{ $cliente->dni ?? '—' }}</p></div>
                            <div><span class="text-gray-400">F. Cumpleaños</span><p class="font-medium text-gray-800">{{ $cliente->fecha_cumpleanos?->format('d/m/Y') ?? '—' }}</p></div>
                            <div><span class="text-gray-400">Celular</span><p class="font-medium text-gray-800">{{ $cliente->celular ?? '—' }}</p></div>
                            <div><span class="text-gray-400">Tel. Casa</span><p class="font-medium text-gray-800">{{ $cliente->telefono_casa ?? '—' }}</p></div>
                            <div class="col-span-2"><span class="text-gray-400">Correo personal</span><p class="font-medium text-gray-800">{{ $cliente->correo_personal ?? '—' }}</p></div>
                            <div><span class="text-gray-400">Ocupación</span><p class="font-medium text-gray-800">{{ $cliente->ocupacion ?? '—' }}</p></div>
                            <div><span class="text-gray-400">Especialidad</span><p class="font-medium text-gray-800">{{ $cliente->especialidad ?? '—' }}</p></div>
                            <div class="col-span-2"><span class="text-gray-400">Dirección residencia</span><p class="font-medium text-gray-800">{{ $cliente->direccion_residencia ?? '—' }}</p></div>
                            @if($cliente->redes_personales)
                                <div class="col-span-2"><span class="text-gray-400">Redes personales</span><p class="font-medium text-gray-800 text-xs">{{ $cliente->redes_personales }}</p></div>
                            @endif
                        </div>

                        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide border-b pb-2 pt-2">Datos de Empresa</h3>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div><span class="text-gray-400">Empresa</span><p class="font-medium text-gray-800">{{ $cliente->empresa ?? '—' }}</p></div>
                            <div><span class="text-gray-400">RUC</span><p class="font-medium text-gray-800">{{ $cliente->ruc ?? '—' }}</p></div>
                            <div class="col-span-2"><span class="text-gray-400">Correo empresa</span><p class="font-medium text-gray-800">{{ $cliente->correo_empresa ?? '—' }}</p></div>
                            <div><span class="text-gray-400">Tel. empresa</span><p class="font-medium text-gray-800">{{ $cliente->telefono_empresa ?? '—' }}</p></div>
                            <div class="col-span-2"><span class="text-gray-400">Dirección empresa</span><p class="font-medium text-gray-800">{{ $cliente->direccion_empresa ?? '—' }}</p></div>
                        </div>
                    </div>

                    {{-- Panel CRM --}}
                    <div class="space-y-5">
                        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide border-b pb-2">CRM</h3>
                        <div class="space-y-3 text-sm">
                            <div><span class="text-gray-400 block">Tipo cliente</span>
                                @if($cliente->tipo_cliente)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $tipoClases[$cliente->tipo_cliente] ?? 'bg-gray-100' }}">{{ $cliente->tipo_cliente }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </div>
                            <div><span class="text-gray-400 block">Fecha registro</span><p class="font-medium text-gray-800">{{ $cliente->fecha_registro?->format('d/m/Y') ?? '—' }}</p></div>
                            <div><span class="text-gray-400 block">Registrado por</span><p class="font-medium text-gray-800">{{ $cliente->registrado_por ?? '—' }}</p></div>
                            <div><span class="text-gray-400 block">Comisión</span><p class="font-medium text-gray-800">{{ $cliente->comision ? $cliente->comision . '%' : '—' }}</p></div>
                            @if($cliente->preferencias)
                                <div><span class="text-gray-400 block">Preferencias</span><p class="font-medium text-gray-800 text-xs">{{ $cliente->preferencias }}</p></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel: Visitas --}}
            <div id="panel-visita" class="tab-panel p-6 hidden">
                @if($cliente->visitas->isNotEmpty())
                    @php $visita = $cliente->visitas->first(); @endphp
                    <div class="bg-blue-50 rounded-lg p-4 mb-6">
                        <h4 class="text-sm font-semibold text-blue-800 mb-3">
                            <i class="fas fa-star mr-1"></i>Última visita — {{ $visita->fecha_visita->format('d/m/Y') }}
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div><span class="text-gray-500 block text-xs">Atendido por</span><p class="font-medium">{{ $visita->atendido_por ?? '—' }}</p></div>
                            <div><span class="text-gray-500 block text-xs">Hora</span><p class="font-medium">{{ $visita->hora_atencion ?? '—' }}</p></div>
                            <div><span class="text-gray-500 block text-xs">Prob. de venta</span>
                                <p class="font-bold text-lg {{ $visita->probabilidad_venta >= 70 ? 'text-green-600' : ($visita->probabilidad_venta >= 40 ? 'text-yellow-600' : 'text-red-500') }}">
                                    {{ $visita->probabilidad_venta }}%
                                </p>
                            </div>
                            <div><span class="text-gray-500 block text-xs">Medio contacto</span><p class="font-medium">{{ $visita->medio_contacto ?? '—' }}</p></div>
                            <div><span class="text-gray-500 block text-xs">Presup. S/.</span><p class="font-medium">S/ {{ number_format($visita->monto_presup_soles, 2) }}</p></div>
                            <div><span class="text-gray-500 block text-xs">Presup. $</span><p class="font-medium">$ {{ number_format($visita->monto_presup_dolares, 2) }}</p></div>
                            <div><span class="text-gray-500 block text-xs">Comprado S/.</span><p class="font-medium">S/ {{ number_format($visita->monto_comprado_soles, 2) }}</p></div>
                            <div><span class="text-gray-500 block text-xs">Comprado $</span><p class="font-medium">$ {{ number_format($visita->monto_comprado_dolares, 2) }}</p></div>
                        </div>
                        @if($visita->resumen_visita)
                            <div class="mt-3 pt-3 border-t border-blue-200">
                                <span class="text-xs text-gray-500">Resumen</span>
                                <p class="text-sm mt-1">{{ $visita->resumen_visita }}</p>
                            </div>
                        @endif
                        @if($visita->observaciones)
                            <div class="mt-2">
                                <span class="text-xs text-gray-500">Observaciones</span>
                                <p class="text-sm mt-1">{{ $visita->observaciones }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Formulario nueva visita --}}
                <div class="border border-gray-200 rounded-lg p-5">
                    <h4 class="text-sm font-semibold text-gray-700 mb-4">
                        <i class="fas fa-plus mr-2 text-[#F7D600]"></i>Registrar nueva visita
                    </h4>
                    <form action="{{ route('clientes.visitas.store', $cliente) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fecha visita *</label>
                                <input type="date" name="fecha_visita" value="{{ date('Y-m-d') }}" required
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Atendido por</label>
                                <input type="text" name="atendido_por" placeholder="Vendedor..."
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Prob. de venta (%)</label>
                                <input type="number" name="probabilidad_venta" min="0" max="100" placeholder="0-100"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Presup. Soles</label>
                                <input type="number" name="monto_presup_soles" step="0.01" placeholder="0.00"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Presup. Dólares</label>
                                <input type="number" name="monto_presup_dolares" step="0.01" placeholder="0.00"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Medio de contacto</label>
                                <input type="text" name="medio_contacto" placeholder="Referido, web, redes..."
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Resumen de visita</label>
                                <textarea name="resumen_visita" rows="2" placeholder="Resumen de lo tratado..."
                                          class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#F7D600] focus:border-[#F7D600]"></textarea>
                            </div>
                        </div>
                        <div class="mt-4 text-right">
                            <button type="submit" class="bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] font-semibold py-2 px-5 rounded-lg text-sm">
                                <i class="fas fa-save mr-2"></i>Guardar visita
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Panel: Proyectos --}}
            <div id="panel-proyectos" class="tab-panel p-6 hidden">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-sm font-semibold text-gray-700">Proyectos del cliente</h4>
                    <a href="{{ route('proyectos.index') }}?cliente_id={{ $cliente->id }}"
                       class="text-sm bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] font-semibold py-1.5 px-4 rounded-lg">
                        <i class="fas fa-plus mr-1"></i>Nuevo Proyecto
                    </a>
                </div>

                @if($cliente->proyectos->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Proyecto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Prioridad</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Resultado</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">F. Entrega aprox.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($cliente->proyectos as $proyecto)
                                    @php
                                        $prioClases = ['A'=>'bg-red-100 text-red-800','M'=>'bg-yellow-100 text-yellow-800','B'=>'bg-green-100 text-green-800'];
                                        $resClases  = ['G'=>'bg-green-100 text-green-800','P'=>'bg-red-100 text-red-800','EP'=>'bg-blue-100 text-blue-800','ENT'=>'bg-gray-100 text-gray-700','ENV'=>'bg-gray-100 text-gray-700','I'=>'bg-orange-100 text-orange-800'];
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('proyectos.show', $proyecto) }}"
                                               class="font-mono text-blue-600 hover:underline">{{ $proyecto->id_proyecto }}</a>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-800">{{ $proyecto->nombre_proyecto }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $prioClases[$proyecto->prioridad] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ \App\Models\Proyecto::$etiquetasPrioridad[$proyecto->prioridad] ?? $proyecto->prioridad }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($proyecto->resultado)
                                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $resClases[$proyecto->resultado] ?? 'bg-gray-100' }}">
                                                    {{ \App\Models\Proyecto::$etiquetasResultado[$proyecto->resultado] ?? $proyecto->resultado }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">{{ $proyecto->fecha_entrega_aprox?->format('d/m/Y') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-12 text-center text-gray-400">
                        <i class="fas fa-project-diagram text-4xl mb-3 block text-gray-200"></i>
                        No hay proyectos registrados para este cliente
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
    function showTab(name) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-[#F7D600]', 'text-[#2B2E2C]');
            b.classList.add('border-transparent', 'text-gray-500');
        });
        document.getElementById('panel-' + name).classList.remove('hidden');
        const btn = document.getElementById('tab-' + name);
        btn.classList.remove('border-transparent', 'text-gray-500');
        btn.classList.add('border-[#F7D600]', 'text-[#2B2E2C]');
    }
    </script>
</body>
</html>
