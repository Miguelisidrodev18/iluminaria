<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $proyecto->id_proyecto }} - Luminarios Kyrios</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Proyecto" subtitle="{{ $proyecto->id_proyecto }} — {{ $proyecto->nombre_proyecto }}" />

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @php
            $prioClases = ['A'=>'bg-red-100 text-red-800','M'=>'bg-yellow-100 text-yellow-800','B'=>'bg-green-100 text-green-800'];
            $resClases  = ['G'=>'bg-green-100 text-green-800','P'=>'bg-red-100 text-red-800','EP'=>'bg-blue-100 text-blue-800','ENT'=>'bg-gray-100 text-gray-700','ENV'=>'bg-gray-100 text-gray-700','I'=>'bg-orange-100 text-orange-800'];
        @endphp

        {{-- Cabecera --}}
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-mono text-lg font-bold text-gray-700">{{ $proyecto->id_proyecto }}</span>
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $prioClases[$proyecto->prioridad] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ \App\Models\Proyecto::$etiquetasPrioridad[$proyecto->prioridad] ?? $proyecto->prioridad }}
                        </span>
                        @if($proyecto->resultado)
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $resClases[$proyecto->resultado] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ \App\Models\Proyecto::$etiquetasResultado[$proyecto->resultado] ?? $proyecto->resultado }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-xl font-bold text-gray-800 mb-1">{{ $proyecto->nombre_proyecto }}</h1>
                    @if($proyecto->cliente)
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-user mr-1"></i>
                            <a href="{{ route('clientes.show', $proyecto->cliente) }}" class="hover:underline text-gray-600">
                                {{ $proyecto->cliente->nombre_completo }}
                            </a>
                        </p>
                    @endif
                </div>
                <div class="flex gap-2">
                    <button onclick="document.getElementById('modal-editar').classList.remove('hidden')"
                            class="bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] font-semibold py-2 px-4 rounded-lg text-sm">
                        <i class="fas fa-edit mr-2"></i>Editar
                    </button>
                    <a href="{{ route('proyectos.index') }}" class="text-gray-500 hover:text-gray-700 py-2 px-3 rounded-lg text-sm border border-gray-200 hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        {{-- Detalle --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Fechas y tiempos --}}
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide border-b pb-2 mb-4">Fechas</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-400 block text-xs">Recepción</span><p class="font-medium">{{ $proyecto->fecha_recepcion?->format('d/m/Y') ?? '—' }}</p></div>
                    <div><span class="text-gray-400 block text-xs">Entrega aprox.</span><p class="font-medium">{{ $proyecto->fecha_entrega_aprox?->format('d/m/Y') ?? '—' }}</p></div>
                    <div><span class="text-gray-400 block text-xs">Entrega real</span><p class="font-medium {{ $proyecto->fecha_entrega_real ? 'text-green-600' : 'text-gray-400' }}">{{ $proyecto->fecha_entrega_real?->format('d/m/Y') ?? 'Pendiente' }}</p></div>
                    <div><span class="text-gray-400 block text-xs">Máx. revisiones</span><p class="font-medium">{{ $proyecto->max_revisiones }}</p></div>
                </div>
            </div>

            {{-- Montos --}}
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide border-b pb-2 mb-4">Montos</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-400 block text-xs">Presupuesto</span><p class="font-bold text-gray-800">S/ {{ number_format($proyecto->monto_presup_proy, 2) }}</p></div>
                    <div><span class="text-gray-400 block text-xs">Vendido</span><p class="font-bold text-green-700">S/ {{ number_format($proyecto->monto_vendido_proy, 2) }}</p></div>
                    <div><span class="text-gray-400 block text-xs">Centro de costos</span><p class="font-medium">{{ $proyecto->centro_costos ?? '—' }}</p></div>
                    <div><span class="text-gray-400 block text-xs">Persona a cargo</span><p class="font-medium">{{ $proyecto->persona_cargo ?? '—' }}</p></div>
                </div>
            </div>
        </div>

        {{-- Seguimiento --}}
        @if($proyecto->seguimiento)
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide border-b pb-2 mb-4">Seguimiento / Acciones</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $proyecto->seguimiento }}</p>
            </div>
        @endif
    </div>

    {{-- Modal Editar --}}
    <div id="modal-editar" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-lg font-bold text-gray-800">Editar Proyecto</h3>
                <button onclick="document.getElementById('modal-editar').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('proyectos.update', $proyecto) }}" method="POST" class="p-6">
                @csrf @method('PUT')

                @php $inp = 'w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-[#F7D600] focus:ring focus:ring-yellow-100'; @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ID Proyecto *</label>
                        <input type="text" name="id_proyecto" required class="{{ $inp }}" value="{{ old('id_proyecto', $proyecto->id_proyecto) }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Prioridad *</label>
                        <select name="prioridad" required class="{{ $inp }}">
                            @foreach(['A'=>'Alta','M'=>'Media','B'=>'Baja'] as $val => $label)
                                <option value="{{ $val }}" {{ old('prioridad', $proyecto->prioridad) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del proyecto *</label>
                        <input type="text" name="nombre_proyecto" required class="{{ $inp }}" value="{{ old('nombre_proyecto', $proyecto->nombre_proyecto) }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Persona a cargo</label>
                        <input type="text" name="persona_cargo" maxlength="100" class="{{ $inp }}" value="{{ old('persona_cargo', $proyecto->persona_cargo) }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Resultado</label>
                        <select name="resultado" class="{{ $inp }}">
                            <option value="">— Sin resultado —</option>
                            @foreach(\App\Models\Proyecto::$etiquetasResultado as $val => $label)
                                <option value="{{ $val }}" {{ old('resultado', $proyecto->resultado) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">F. Recepción</label>
                        <input type="date" name="fecha_recepcion" class="{{ $inp }}" value="{{ $proyecto->fecha_recepcion?->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">F. Entrega aprox.</label>
                        <input type="date" name="fecha_entrega_aprox" class="{{ $inp }}" value="{{ $proyecto->fecha_entrega_aprox?->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">F. Entrega real</label>
                        <input type="date" name="fecha_entrega_real" class="{{ $inp }}" value="{{ $proyecto->fecha_entrega_real?->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Máx. revisiones</label>
                        <input type="number" name="max_revisiones" min="1" max="10" class="{{ $inp }}" value="{{ old('max_revisiones', $proyecto->max_revisiones) }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Presupuesto (S/)</label>
                        <input type="number" name="monto_presup_proy" step="0.01" min="0" class="{{ $inp }}" value="{{ old('monto_presup_proy', $proyecto->monto_presup_proy) }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Monto vendido (S/)</label>
                        <input type="number" name="monto_vendido_proy" step="0.01" min="0" class="{{ $inp }}" value="{{ old('monto_vendido_proy', $proyecto->monto_vendido_proy) }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Centro de costos</label>
                        <input type="text" name="centro_costos" maxlength="100" class="{{ $inp }}" value="{{ old('centro_costos', $proyecto->centro_costos) }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Seguimiento / Acciones</label>
                        <textarea name="seguimiento" rows="3" class="{{ $inp }}">{{ old('seguimiento', $proyecto->seguimiento) }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                    <button type="button" onclick="document.getElementById('modal-editar').classList.add('hidden')"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-5 rounded-lg text-sm">Cancelar</button>
                    <button type="submit"
                            class="bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] font-semibold py-2 px-5 rounded-lg text-sm">
                        <i class="fas fa-save mr-2"></i>Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
