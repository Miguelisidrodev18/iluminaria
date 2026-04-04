<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Variantes · {{ $producto->nombre }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">

        {{-- Breadcrumb --}}
        <div class="flex items-center text-sm text-gray-500 mb-5">
            <a href="{{ route('inventario.productos.index') }}" class="hover:text-[#2B2E2C]">Productos</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <a href="{{ route('inventario.productos.show', $producto) }}" class="hover:text-[#2B2E2C] truncate max-w-xs">{{ $producto->nombre }}</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span class="text-gray-700 font-medium">Variantes y Precios</span>
        </div>

        {{-- Alertas --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ─── Encabezado del producto ──────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-md p-5 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Producto base</p>
                <h1 class="text-xl font-bold text-gray-900">{{ $producto->nombre }}</h1>
                <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-xs">{{ $producto->codigo }}</span>
                    @if($producto->marca)
                        <span>{{ $producto->marca->nombre }}</span>
                    @endif
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $producto->tipo_inventario === 'serie' ? 'bg-gray-200 text-gray-700' : 'bg-green-100 text-green-700' }}">
                        {{ $producto->tipo_inventario === 'serie' ? 'Serie/IMEI' : 'Cantidad' }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('inventario.productos.toggle-variantes', $producto) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition border
                                   {{ $producto->tiene_variantes
                                       ? 'bg-green-50 border-green-300 text-green-700 hover:bg-green-100'
                                       : 'bg-gray-50 border-gray-300 text-gray-600 hover:bg-gray-100' }}">
                        <i class="fas fa-layer-group {{ $producto->tiene_variantes ? 'text-green-600' : 'text-gray-400' }}"></i>
                        {{ $producto->tiene_variantes ? 'Variantes ON' : 'Variantes OFF' }}
                        <span class="w-2 h-2 rounded-full {{ $producto->tiene_variantes ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                    </button>
                </form>
                <a href="{{ route('inventario.productos.show', $producto) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>
        </div>

        @if(!$producto->tiene_variantes)
        {{-- ─── Estado: sin variantes activo ─────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-md p-10 text-center">
            <i class="fas fa-layer-group text-5xl text-gray-200 mb-4"></i>
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Modo variantes desactivado</h2>
            <p class="text-gray-500 text-sm mb-6">
                Activa el modo variantes para gestionar versiones del producto con distintos atributos y precios.
            </p>
            <form action="{{ route('inventario.productos.toggle-variantes', $producto) }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] rounded-xl font-semibold text-sm transition">
                    <i class="fas fa-toggle-on"></i> Activar modo variantes
                </button>
            </form>
        </div>
        @else

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- TABLA DE PRECIOS                                                --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        @if($producto->variantes->isNotEmpty())
        @php
            $variantesJs = $producto->variantes->sortBy([['estado','asc'],['id','asc']])->map(fn($v) => [
                'id'           => $v->id,
                'nombre'       => $v->nombre_completo,
                'sku'          => $v->sku,
                'color_hex'    => $v->color?->codigo_hex ?? null,
                'color_nombre' => $v->color?->nombre ?? null,
                'precio_venta' => $v->precio_venta !== null ? (float)$v->precio_venta : null,
                'moneda'       => $v->moneda ?? 'PEN',
                'estado'       => $v->estado,
            ])->values();
        @endphp

        <div class="bg-white rounded-2xl shadow-md mb-6 overflow-hidden"
             x-data="preciosManager({{ $variantesJs->toJson() }})">

            <div class="bg-gradient-to-r from-[#2B2E2C] to-gray-700 px-5 py-4 flex items-center justify-between">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fas fa-tag text-[#F7D600]"></i>
                    Precios de Variantes
                    <span class="bg-white/20 text-white text-xs px-2 py-0.5 rounded-full">
                        {{ $producto->variantes->count() }}
                    </span>
                </h2>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-300">Haz clic en el precio para editarlo</span>
                    <button @click="guardarTodos()"
                            :disabled="guardandoTodos"
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] disabled:opacity-50 rounded-lg text-xs font-bold transition">
                        <i class="fas" :class="guardandoTodos ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                        Guardar todos
                    </button>
                </div>
            </div>

            {{-- Cabecera tabla --}}
            <div class="grid grid-cols-12 gap-0 bg-gray-50 border-b border-gray-200 px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <div class="col-span-5">Variante</div>
                <div class="col-span-3 text-right">Precio de Venta</div>
                <div class="col-span-2 text-center">Moneda</div>
                <div class="col-span-2 text-center">Estado</div>
            </div>

            <template x-for="v in variantes" :key="v.id">
                <div class="grid grid-cols-12 gap-0 items-center px-4 py-3 border-b border-gray-100 hover:bg-gray-50/60 transition"
                     :class="v.estado === 'inactivo' ? 'opacity-50' : ''">

                    {{-- Variante --}}
                    <div class="col-span-5 flex items-center gap-2.5 min-w-0">
                        <template x-if="v.color_hex">
                            <div class="w-7 h-7 rounded-full border-2 border-white shadow flex-shrink-0"
                                 :style="'background-color:' + v.color_hex"
                                 :title="v.color_nombre"></div>
                        </template>
                        <template x-if="!v.color_hex">
                            <div class="w-7 h-7 rounded-full bg-gray-100 border border-gray-200 shadow flex-shrink-0 flex items-center justify-center">
                                <i class="fas fa-lightbulb text-gray-300 text-xs"></i>
                            </div>
                        </template>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate" x-text="v.nombre"></p>
                            <p class="text-xs text-gray-400 font-mono" x-text="v.sku"></p>
                        </div>
                    </div>

                    {{-- Precio --}}
                    <div class="col-span-3 flex items-center justify-end gap-2">
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium select-none"
                                  x-text="v.moneda === 'USD' ? 'US$' : 'S/'"></span>
                            <input type="number"
                                   x-model="v.precio_venta"
                                   @change="v.editado = true"
                                   @blur="if(v.editado) guardar(v)"
                                   min="0" step="0.01"
                                   placeholder="Sin precio"
                                   :disabled="v.estado === 'inactivo'"
                                   class="w-32 pl-8 pr-2 py-1.5 border rounded-lg text-sm text-right font-semibold focus:ring-2 focus:ring-[#F7D600] focus:border-[#F7D600] transition
                                          disabled:bg-gray-100 disabled:cursor-not-allowed"
                                   :class="v.precio_venta ? 'border-gray-300 bg-white text-gray-900' : 'border-dashed border-gray-300 bg-gray-50 text-gray-400'">
                        </div>
                        {{-- Indicador de estado guardado --}}
                        <div class="w-5 flex-shrink-0 text-center">
                            <template x-if="v.guardando">
                                <i class="fas fa-spinner fa-spin text-gray-400 text-xs"></i>
                            </template>
                            <template x-if="v.guardado && !v.guardando">
                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                            </template>
                            <template x-if="v.error && !v.guardando">
                                <i class="fas fa-exclamation-circle text-red-500 text-sm" :title="v.error"></i>
                            </template>
                        </div>
                    </div>

                    {{-- Moneda --}}
                    <div class="col-span-2 flex justify-center">
                        <select x-model="v.moneda"
                                @change="v.editado = true; guardar(v)"
                                :disabled="v.estado === 'inactivo'"
                                class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-[#F7D600] disabled:opacity-50 bg-white">
                            <option value="PEN">S/ PEN</option>
                            <option value="USD">US$ USD</option>
                        </select>
                    </div>

                    {{-- Estado --}}
                    <div class="col-span-2 flex justify-center">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                              :class="v.estado === 'activo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-500'"
                              x-text="v.estado === 'activo' ? 'Activo' : 'Inactivo'"></span>
                    </div>
                </div>
            </template>

            {{-- Footer info --}}
            <div class="px-4 py-2.5 bg-amber-50 border-t border-amber-100 flex items-center gap-2 text-xs text-amber-700">
                <i class="fas fa-info-circle text-amber-400"></i>
                Los precios se guardan automáticamente al cambiar el campo. Las variantes sin precio no tienen precio fijo asignado.
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- GESTIÓN DE VARIANTES                                            --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- ─── FORMULARIO NUEVA VARIANTE ─────────────────────────────── --}}
            <div class="xl:col-span-1">
                <div class="bg-white rounded-2xl shadow-md overflow-hidden"
                     x-data="{ abierto: {{ $errors->any() ? 'true' : 'false' }} }">

                    <button @click="abierto = !abierto"
                            class="w-full bg-gradient-to-r from-[#1F2220] to-indigo-600 px-5 py-4 flex items-center justify-between">
                        <span class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fas fa-plus-circle"></i> Nueva Variante
                        </span>
                        <i class="fas text-white text-sm transition-transform"
                           :class="abierto ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>

                    <div x-show="abierto" x-collapse>
                        <form action="{{ route('inventario.productos.variantes.store', $producto) }}"
                              method="POST" class="p-5 space-y-3">
                            @csrf

                            {{-- Nombre descriptivo --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">
                                    Nombre descriptivo <span class="text-gray-400 normal-case font-normal">(opcional)</span>
                                </label>
                                <input type="text" name="nombre" value="{{ old('nombre') }}"
                                       placeholder="Ej: LED 3000K Negro Mate"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600] text-sm">
                            </div>

                            {{-- Color --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">
                                    Color <span class="text-gray-400 normal-case font-normal">(opcional)</span>
                                </label>
                                <select name="color_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600] text-sm">
                                    <option value="">Sin color específico</option>
                                    @foreach($colores as $color)
                                        <option value="{{ $color->id }}"
                                                {{ old('color_id') == $color->id ? 'selected' : '' }}>
                                            {{ $color->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Precio de venta --}}
                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-2">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">
                                        Precio de Venta
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">S/</span>
                                        <input type="number" name="precio_venta"
                                               value="{{ old('precio_venta') }}"
                                               min="0" step="0.01"
                                               placeholder="0.00"
                                               class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600] text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Moneda</label>
                                    <select name="moneda"
                                            class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600] text-sm">
                                        <option value="PEN" {{ old('moneda','PEN') === 'PEN' ? 'selected' : '' }}>S/ PEN</option>
                                        <option value="USD" {{ old('moneda') === 'USD' ? 'selected' : '' }}>US$ USD</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Atributos luminaria (colapsable) --}}
                            <div class="border border-gray-100 rounded-xl overflow-hidden"
                                 x-data="{ open: false }">
                                <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between px-3 py-2.5 bg-amber-50 text-xs font-semibold text-amber-700 hover:bg-amber-100 transition">
                                    <span><i class="fas fa-lightbulb mr-1.5"></i> Atributos luminaria</span>
                                    <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                </button>
                                <div x-show="open" x-collapse class="p-3 space-y-2 bg-white">
                                    @foreach($atributosLuminaria as $clave => $meta)
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-0.5">{{ $meta['label'] }}</label>
                                            <input type="text"
                                                   name="atributos[{{ $clave }}]"
                                                   value="{{ old("atributos.{$clave}") }}"
                                                   placeholder="{{ $meta['placeholder'] }}"
                                                   class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#F7D600] focus:border-[#F7D600]">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if($producto->tipo_inventario === 'cantidad')
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">
                                    Stock inicial
                                </label>
                                <input type="number" name="stock_inicial" value="{{ old('stock_inicial', 0) }}"
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600] text-sm">
                            </div>
                            @endif

                            <button type="submit"
                                    class="w-full mt-2 px-4 py-2.5 bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] rounded-xl font-semibold text-sm transition">
                                <i class="fas fa-plus mr-2"></i>Agregar Variante
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Resumen de stock --}}
                <div class="bg-white rounded-2xl shadow-md p-5 mt-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Stock total por variantes</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ $producto->variantes->where('estado', 'activo')->sum('stock_actual') }}
                        <span class="text-sm font-normal text-gray-400">unidades</span>
                    </p>
                    <div class="mt-3 space-y-1 text-sm text-gray-500">
                        <p>{{ $producto->variantes->where('estado', 'activo')->count() }} variante(s) activa(s)</p>
                        @if($producto->variantes->where('estado', 'inactivo')->count() > 0)
                            <p class="text-red-400">{{ $producto->variantes->where('estado', 'inactivo')->count() }} inactiva(s)</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ─── LISTA DE VARIANTES ───────────────────────────────────── --}}
            <div class="xl:col-span-2">
                <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-5 py-4 flex items-center justify-between">
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fas fa-layer-group"></i>
                            Atributos de Variantes
                            <span class="bg-white/20 text-white text-xs px-2 py-0.5 rounded-full">
                                {{ $producto->variantes->count() }}
                            </span>
                        </h2>
                    </div>

                    @if($producto->variantes->isEmpty())
                        <div class="p-10 text-center text-gray-400">
                            <i class="fas fa-layer-group text-4xl mb-3 text-gray-200"></i>
                            <p class="font-medium">Sin variantes registradas</p>
                            <p class="text-sm mt-1">Agrega la primera variante usando el formulario</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($producto->variantes->sortBy([['estado', 'asc'], ['id', 'asc']]) as $variante)
                                <div x-data="{ editando: false }"
                                     class="p-4 {{ $variante->estado === 'inactivo' ? 'opacity-60 bg-gray-50' : '' }}">

                                    {{-- ─── Vista normal ─── --}}
                                    <div x-show="!editando" class="flex items-start justify-between gap-4">
                                        <div class="flex items-start gap-3 min-w-0 flex-1">

                                            {{-- Dot de color --}}
                                            @if($variante->color && $variante->color->codigo_hex)
                                                <div class="w-9 h-9 rounded-full border-2 border-white shadow flex-shrink-0 mt-0.5"
                                                     style="background-color: {{ $variante->color->codigo_hex }};"
                                                     title="{{ $variante->color->nombre }}"></div>
                                            @else
                                                <div class="w-9 h-9 rounded-full bg-gray-100 border-2 border-gray-200 shadow flex-shrink-0 mt-0.5 flex items-center justify-center">
                                                    <i class="fas fa-lightbulb text-gray-300 text-xs"></i>
                                                </div>
                                            @endif

                                            <div class="min-w-0 flex-1">
                                                {{-- Nombre + SKU + estado --}}
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-semibold text-gray-900 text-sm">
                                                        {{ $variante->nombre_completo }}
                                                    </span>
                                                    <span class="font-mono text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded">
                                                        {{ $variante->sku }}
                                                    </span>
                                                    @if($variante->estado === 'inactivo')
                                                        <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium">
                                                            Inactiva
                                                        </span>
                                                    @endif
                                                    {{-- Precio badge --}}
                                                    @if($variante->precio_venta !== null)
                                                        <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full font-semibold">
                                                            {{ $variante->moneda === 'USD' ? 'US$' : 'S/' }}
                                                            {{ number_format($variante->precio_venta, 2) }}
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Atributos luminaria --}}
                                                @php
                                                    $atribs = array_filter($variante->atributos ?? []);
                                                    $labels = collect($atributosLuminaria)->map(fn($m) => $m['label']);
                                                @endphp
                                                @if(!empty($atribs))
                                                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                                                        @foreach($atribs as $clave => $valor)
                                                            <span class="inline-flex items-center gap-1 text-xs bg-amber-50 text-amber-700 border border-amber-100 px-2 py-0.5 rounded-full">
                                                                <span class="text-amber-400 font-medium">{{ $labels[$clave] ?? $clave }}:</span>
                                                                {{ $valor }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                {{-- Color + stock mínimo --}}
                                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                                                    @if($variante->color)
                                                        <span><i class="fas fa-circle mr-1"></i>{{ $variante->color->nombre }}</span>
                                                    @endif
                                                    <span>Stock mín: {{ $variante->stock_minimo }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Stock + acciones --}}
                                        <div class="flex items-center gap-3 flex-shrink-0">
                                            <div class="text-right">
                                                <p class="text-lg font-bold {{ $variante->stock_actual <= $variante->stock_minimo && $variante->estado === 'activo' ? 'text-red-600' : 'text-gray-900' }}">
                                                    {{ $variante->stock_actual }}
                                                </p>
                                                <p class="text-xs text-gray-400">en stock</p>
                                            </div>

                                            <button @click="editando = true"
                                                    class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                                    title="Editar atributos">
                                                <i class="fas fa-pencil text-sm"></i>
                                            </button>

                                            @if($variante->estado === 'activo')
                                                <form action="{{ route('inventario.productos.variantes.destroy', $variante) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('¿Desactivar esta variante?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                            title="Desactivar">
                                                        <i class="fas fa-ban text-sm"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('inventario.productos.variantes.reactivar', $variante) }}"
                                                      method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                            class="p-2 text-green-500 hover:text-green-700 hover:bg-green-50 rounded-lg transition"
                                                            title="Reactivar">
                                                        <i class="fas fa-check-circle text-sm"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- ─── Vista edición ─── --}}
                                    <div x-show="editando" x-cloak>
                                        <form action="{{ route('inventario.productos.variantes.update', $variante) }}"
                                              method="POST" class="space-y-3">
                                            @csrf @method('PUT')

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                {{-- Nombre --}}
                                                <div class="sm:col-span-2">
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre descriptivo</label>
                                                    <input type="text" name="nombre"
                                                           value="{{ $variante->nombre }}"
                                                           placeholder="Ej: LED 3000K Negro Mate"
                                                           class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                                                </div>

                                                {{-- Color --}}
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                                    <select name="color_id"
                                                            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                                                        <option value="">Sin color</option>
                                                        @foreach($colores as $color)
                                                            <option value="{{ $color->id }}"
                                                                    {{ $variante->color_id == $color->id ? 'selected' : '' }}>
                                                                {{ $color->nombre }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                {{-- Stock mínimo --}}
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Stock mínimo</label>
                                                    <input type="number" name="stock_minimo"
                                                           value="{{ $variante->stock_minimo }}"
                                                           min="0"
                                                           class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                                                </div>

                                                {{-- Estado --}}
                                                <div class="sm:col-span-2">
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                                                    <select name="estado"
                                                            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                                                        <option value="activo"   {{ $variante->estado === 'activo'   ? 'selected' : '' }}>Activo</option>
                                                        <option value="inactivo" {{ $variante->estado === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Atributos luminaria en edición --}}
                                            <div class="border-t border-gray-100 pt-3">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                                    <i class="fas fa-lightbulb text-yellow-400 mr-1"></i> Atributos luminaria
                                                </p>
                                                <div class="grid grid-cols-2 gap-2">
                                                    @foreach($atributosLuminaria as $clave => $meta)
                                                        <div>
                                                            <label class="block text-xs text-gray-500 mb-0.5">{{ $meta['label'] }}</label>
                                                            <input type="text"
                                                                   name="atributos[{{ $clave }}]"
                                                                   value="{{ ($variante->atributos ?? [])[$clave] ?? '' }}"
                                                                   placeholder="{{ $meta['placeholder'] }}"
                                                                   class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#F7D600]">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- Botones --}}
                                            <div class="flex items-center gap-2 pt-1">
                                                <button type="submit"
                                                        class="px-4 py-2 bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] rounded-lg font-semibold text-sm transition">
                                                    <i class="fas fa-save mr-1"></i> Guardar
                                                </button>
                                                <button type="button" @click="editando = false"
                                                        class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-lg text-sm transition">
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
        @endif

    </div>

<script>
function preciosManager(variantesData) {
    return {
        variantes: variantesData.map(v => ({
            ...v,
            editado: false,
            guardando: false,
            guardado: false,
            error: null,
        })),
        guardandoTodos: false,

        async guardar(v) {
            if (!v.editado) return;
            v.guardando = true;
            v.error = null;
            v.guardado = false;

            try {
                const res = await fetch(`/inventario/productos/variantes/${v.id}/precio`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        precio_venta: v.precio_venta !== '' && v.precio_venta !== null ? parseFloat(v.precio_venta) : null,
                        moneda: v.moneda,
                    }),
                });

                const data = await res.json();
                if (data.ok) {
                    v.guardado = true;
                    v.editado = false;
                    setTimeout(() => { v.guardado = false; }, 2500);
                } else {
                    v.error = data.error || 'Error al guardar';
                }
            } catch (e) {
                v.error = 'Error de conexión';
            }
            v.guardando = false;
        },

        async guardarTodos() {
            this.guardandoTodos = true;
            const pendientes = this.variantes.filter(v => v.editado && v.estado === 'activo');
            await Promise.all(pendientes.map(v => this.guardar(v)));
            this.guardandoTodos = false;
        },
    };
}
</script>
</body>
</html>
