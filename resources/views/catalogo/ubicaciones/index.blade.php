<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubicaciones — Luminaria Kyrios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-map-marker-alt text-[#2B2E2C] mr-2"></i>Ubicaciones
                </h1>
                <p class="text-sm text-gray-500 mt-1">Gestiona los lugares de almacenamiento de productos</p>
            </div>
            <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>

        {{-- Flash --}}
        @if(session('success'))
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center gap-3">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
            <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Formulario nueva ubicación --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-800 mb-4">
                        <i class="fas fa-plus-circle text-[#2B2E2C] mr-1"></i> Nueva Ubicación
                    </h2>
                    <form method="POST" action="{{ route('catalogo.ubicaciones.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" required
                                   placeholder="Ej: Lima, Almacén Principal, Depósito 1..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo <span class="text-red-500">*</span></label>
                            <select name="tipo" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                                <option value="almacen"  {{ old('tipo') === 'almacen'  ? 'selected' : '' }}>Almacén</option>
                                <option value="tienda"   {{ old('tipo') === 'tienda'   ? 'selected' : '' }}>Tienda</option>
                                <option value="showroom" {{ old('tipo') === 'showroom' ? 'selected' : '' }}>Showroom</option>
                                <option value="taller"   {{ old('tipo') === 'taller'   ? 'selected' : '' }}>Taller</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Descripción (opcional)</label>
                            <input type="text" name="descripcion" value="{{ old('descripcion') }}"
                                   placeholder="Ej: Segundo piso, zona de embalaje..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                        </div>
                        <button type="submit"
                                class="w-full py-2 bg-[#F7D600] text-[#2B2E2C] text-sm font-medium rounded-lg hover:bg-[#e8c900] transition-colors">
                            <i class="fas fa-plus mr-1"></i> Agregar Ubicación
                        </button>
                    </form>
                </div>
            </div>

            {{-- Lista de ubicaciones --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-800">Ubicaciones registradas</h2>
                        <span class="text-xs text-gray-400">{{ $ubicaciones->count() }} en total</span>
                    </div>

                    @if($ubicaciones->isEmpty())
                    <div class="p-12 text-center text-gray-400">
                        <i class="fas fa-map-marker-alt text-4xl opacity-20 mb-3"></i>
                        <p class="text-sm">No hay ubicaciones registradas aún.</p>
                        <p class="text-xs mt-1">Crea la primera usando el formulario de la izquierda.</p>
                    </div>
                    @else
                    <div class="divide-y divide-gray-50">
                        @foreach($ubicaciones as $ub)
                        <div x-data="{ editando: false }" class="px-6 py-4">
                            {{-- Vista normal --}}
                            <div x-show="!editando" class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    @php
                                        $iconos = ['almacen'=>'fa-warehouse','tienda'=>'fa-store','showroom'=>'fa-eye','taller'=>'fa-tools'];
                                        $colores = ['almacen'=>'text-[#2B2E2C]','tienda'=>'text-green-500','showroom'=>'text-purple-500','taller'=>'text-orange-500'];
                                    @endphp
                                    <i class="fas {{ $iconos[$ub->tipo] ?? 'fa-map-marker-alt' }} {{ $colores[$ub->tipo] ?? 'text-gray-400' }} w-5 text-center"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $ub->nombre }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ \App\Models\Ubicacion::TIPOS[$ub->tipo] ?? $ub->tipo }}
                                            @if($ub->descripcion) · {{ $ub->descripcion }}@endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="{{ $ub->estado === 'activo' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} text-xs px-2 py-0.5 rounded-full">
                                        {{ $ub->estado }}
                                    </span>
                                    <button type="button" @click="editando = true"
                                            class="text-[#2B2E2C] hover:text-[#2B2E2C] text-xs transition-colors">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <form method="POST" action="{{ route('catalogo.ubicaciones.destroy', $ub) }}"
                                          onsubmit="return confirm('¿Desactivar esta ubicación?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600 text-xs transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Formulario edición inline --}}
                            <div x-show="editando" x-cloak>
                                <form method="POST" action="{{ route('catalogo.ubicaciones.update', $ub) }}"
                                      class="bg-[#2B2E2C]/10 border border-gray-200 rounded-xl p-4 space-y-3">
                                    @csrf @method('PUT')
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                                            <input type="text" name="nombre" value="{{ $ub->nombre }}" required
                                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                                            <select name="tipo" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                                <option value="almacen"  {{ $ub->tipo === 'almacen'  ? 'selected' : '' }}>Almacén</option>
                                                <option value="tienda"   {{ $ub->tipo === 'tienda'   ? 'selected' : '' }}>Tienda</option>
                                                <option value="showroom" {{ $ub->tipo === 'showroom' ? 'selected' : '' }}>Showroom</option>
                                                <option value="taller"   {{ $ub->tipo === 'taller'   ? 'selected' : '' }}>Taller</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                                            <select name="estado" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                                <option value="activo"   {{ $ub->estado === 'activo'   ? 'selected' : '' }}>Activo</option>
                                                <option value="inactivo" {{ $ub->estado === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                                        <input type="text" name="descripcion" value="{{ $ub->descripcion }}"
                                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit"
                                                class="px-4 py-1.5 bg-[#F7D600] text-[#2B2E2C] text-xs font-medium rounded-lg hover:bg-[#e8c900]">
                                            <i class="fas fa-check mr-1"></i>Guardar
                                        </button>
                                        <button type="button" @click="editando = false"
                                                class="px-4 py-1.5 bg-gray-200 text-gray-600 text-xs font-medium rounded-lg hover:bg-gray-300">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</body>
</html>
