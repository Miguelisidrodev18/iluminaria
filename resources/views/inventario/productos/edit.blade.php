<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar Producto — {{ $producto->nombre }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Editar Producto" subtitle="Actualiza la información de {{ $producto->nombre }}" />

        <div class="max-w-5xl mx-auto">

            {{-- Resumen rápido --}}
            <div class="mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-500">Código:</span>
                        <span class="block text-gray-900 font-mono font-bold">{{ $producto->codigo }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Código Kyrios:</span>
                        <span class="block text-gray-900 font-mono">{{ $producto->codigo_kyrios ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Stock Actual:</span>
                        <span class="block text-gray-900 font-bold text-lg">{{ $producto->stock_actual }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Creado:</span>
                        <span class="block text-gray-900">{{ $producto->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-300 rounded-lg p-4">
                    <p class="text-sm font-semibold text-red-700 mb-2">
                        <i class="fas fa-exclamation-circle mr-1"></i>Corrige los siguientes errores:
                    </p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm text-red-600">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $tpSelId      = old('tipo_producto_id',  $producto->tipo_producto_id);
                $tlSelId      = old('tipo_luminaria_id', $producto->tipo_luminaria_id);
                $tpSel        = $tiposProducto->firstWhere('id', $tpSelId);
                $mostrarTipoLum  = $tpSel && $tpSel->usa_tipo_luminaria;
                $mostrarFicha    = $tpSel && in_array($tpSel->codigo, ['LU','LA','CL']);
            @endphp

            <form action="{{ route('inventario.productos.update', $producto) }}" method="POST" enctype="multipart/form-data" id="formProducto" name="form-producto-principal">
                @csrf
                @method('PUT')
                <input type="hidden" name="tipo_inventario" value="cantidad">

                {{-- ══════════════════════════════════════════════════════════
                    BLOQUE 1 — TIPO DE PRODUCTO (desde BD, relacional)
                ══════════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5">
                    <div class="px-6 py-4 flex items-center gap-3" style="background-color:#2B2E2C;">
                        <i class="fas fa-sliders-h text-xl" style="color:#F7D600;"></i>
                        <div>
                            <h2 class="text-base font-bold text-white">1 — Tipo de Producto</h2>
                            <p class="text-xs text-gray-300">Selecciona el tipo — controla los campos visibles y el código Kyrios</p>
                        </div>
                    </div>
                    <div class="p-6">
                        {{-- Tarjetas de tipo de producto desde BD --}}
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3" id="selectorTipoProducto">
                            @foreach($tiposProducto as $tp)
                            @php
                                $iconos = ['LU'=>'fas fa-lightbulb','LA'=>'fas fa-fire','CL'=>'fas fa-grip-lines','SM'=>'fas fa-cubes','AC'=>'fas fa-tools','EA'=>'fas fa-plug','PE'=>'fas fa-bars','PA'=>'fas fa-tv','VE'=>'fas fa-fan','CA'=>'fas fa-dot-circle','PO'=>'fas fa-map-pin','LE'=>'fas fa-exclamation-triangle','SO'=>'fas fa-sun','RE'=>'fas fa-battery-half'];
                                $icono  = $iconos[$tp->codigo] ?? 'fas fa-box';
                                $selTP  = $tpSelId == $tp->id;
                            @endphp
                            <label class="tipo-card flex flex-col items-center gap-2 p-4 border-2 rounded-xl cursor-pointer transition-all hover:shadow-md
                                          {{ $selTP ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200 bg-white' }}"
                                   data-id="{{ $tp->id }}"
                                   data-codigo="{{ $tp->codigo }}"
                                   data-usa-luminaria="{{ $tp->usa_tipo_luminaria ? '1' : '0' }}">
                                <input type="radio" name="tipo_producto_id" value="{{ $tp->id }}"
                                       class="sr-only" {{ $selTP ? 'checked' : '' }}>
                                <i class="{{ $icono }} text-2xl text-gray-600"></i>
                                <span class="text-sm font-semibold text-gray-800">{{ $tp->nombre }}</span>
                                <span class="text-xs font-mono text-gray-400 bg-gray-100 px-2 py-0.5 rounded">{{ $tp->codigo }}</span>
                            </label>
                            @endforeach
                        </div>

                        {{-- Tipo Luminaria (condicional) --}}
                        <div id="bloqueTipoLuminaria" class="{{ $mostrarTipoLum ? '' : 'hidden' }} mt-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-th-large mr-1 text-yellow-500"></i>
                                Tipo de Luminaria <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-2" id="selectorTipoLuminaria">
                                @foreach($tiposLuminaria as $tl)
                                <label class="tipo-lum-card flex flex-col items-center gap-1 py-3 px-2 border-2 rounded-xl cursor-pointer transition-all hover:border-yellow-400
                                              {{ $tlSelId == $tl->id ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200 bg-white' }}"
                                       data-id="{{ $tl->id }}"
                                       data-codigo="{{ $tl->codigo }}">
                                    <input type="radio" name="tipo_luminaria_id" value="{{ $tl->id }}"
                                           class="sr-only" {{ $tlSelId == $tl->id ? 'checked' : '' }}>
                                    <span class="text-xs font-semibold text-gray-800 text-center">{{ $tl->nombre }}</span>
                                    <span class="text-xs font-mono text-gray-400 bg-gray-100 px-2 rounded">{{ $tl->codigo }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('tipo_luminaria_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @error('tipo_producto_id')
                            <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                    BLOQUE 2 — IDENTIFICACIÓN Y CÓDIGO KYRIOS
                ══════════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5">
                    <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#2B2E2C;">2</span>
                        <h2 class="font-semibold text-gray-800">Identificación</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- Código Kyrios --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Código Kyrios
                                <span class="ml-1 text-xs text-gray-400">— generado por tipo + marca</span>
                            </label>
                            <div class="flex gap-2 items-center">
                                <input type="text" name="codigo_kyrios" id="codigo_kyrios"
                                       value="{{ old('codigo_kyrios', $producto->codigo_kyrios) }}"
                                       placeholder="KY-LUDLPH-0001"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 font-mono">
                                <button type="button" id="btnGenerarKyrios"
                                        title="Regenerar código Kyrios"
                                        class="px-4 py-2 text-gray-900 rounded-lg font-semibold text-sm transition shrink-0"
                                        style="background-color:#F7D600;"
                                        onmouseover="this.style.backgroundColor='#e8c900'"
                                        onmouseout="this.style.backgroundColor='#F7D600'">
                                    <i class="fas fa-sync-alt mr-1"></i>Regenerar
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                Formato: <span class="font-mono">KY-[TP][TL][M]-[NNNN]</span>
                                &nbsp;|&nbsp; Ej: <span class="font-mono text-gray-600">KY-LUDLPH-0001</span>
                            </p>
                        </div>

                        {{-- Código Fábrica --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Código Fábrica
                                <span class="text-xs text-gray-400 font-normal ml-1">(no editable)</span>
                            </label>
                            <input type="text" name="codigo_fabrica"
                                   value="{{ old('codigo_fabrica', $producto->codigo_fabrica) }}"
                                   placeholder="Código del fabricante"
                                   readonly
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                        </div>

                        {{-- Código de Barras --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código de Barras</label>
                            <div class="flex gap-2">
                                <input type="text" name="codigo_barras" id="codigo_barras"
                                       value="{{ old('codigo_barras', $producto->codigo_barras) }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400">
                                <button type="button" id="btnGenerarCodigo"
                                        class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition shrink-0">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            @if($producto->codigo_barras)
                                <p class="text-xs text-gray-500 mt-1">
                                    <a href="{{ route('inventario.productos.codigos-barras', $producto) }}" class="text-[#2B2E2C] hover:underline">
                                        <i class="fas fa-barcode mr-1"></i>Gestionar múltiples códigos
                                    </a>
                                </p>
                            @endif
                        </div>

                        {{-- Categoría oculta (requerida en BD, se mantiene automáticamente) --}}
                        <input type="hidden" name="categoria_id"
                               value="{{ old('categoria_id', $producto->categoria_id ?? $categorias->first()?->id) }}">

                        {{-- Marca --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                            <div class="flex gap-2">
                                <select name="marca_id" id="marca_id"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                                    <option value="">— Sin marca —</option>
                                    @foreach($marcas as $m)
                                        <option value="{{ $m->id }}"
                                                {{ old('marca_id', $producto->marca_id) == $m->id ? 'selected' : '' }}>
                                            {{ $m->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="abrirModalMarca()"
                                        class="px-3 py-2 bg-[#2B2E2C]/10 text-[#2B2E2C] border border-gray-300 rounded-lg hover:bg-[#2B2E2C]/20 transition shrink-0">
                                    <i class="fas fa-plus text-sm"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                    BLOQUE 3 — INFORMACIÓN BÁSICA
                ══════════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5">
                    <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#2B2E2C;">3</span>
                        <h2 class="font-semibold text-gray-800">Información Básica</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Nombre (del fabricante / nombre_origen del Excel) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre del Fabricante <span class="text-red-500">*</span>
                                <span class="font-normal text-xs text-gray-400 ml-1">(nombre_origen del Excel)</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="nombre" id="nombre"
                                       value="{{ old('nombre', $producto->nombre) }}"
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600]" required>
                                <button type="button" id="btnSugerirNombre"
                                        class="px-4 py-2 bg-[#F7D600] text-[#2B2E2C] rounded-lg hover:bg-[#e8c900] text-sm">
                                    <i class="fas fa-magic mr-1"></i>Sugerir
                                </button>
                            </div>
                            @error('nombre')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nombre Kyrios (interno) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre Kyrios
                                <span class="font-normal text-xs text-gray-400 ml-1">(nombre interno del sistema)</span>
                            </label>
                            <input type="text" name="nombre_kyrios"
                                   value="{{ old('nombre_kyrios', $producto->nombre_kyrios) }}"
                                   placeholder="Nombre normalizado para el sistema"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400">
                        </div>

                        {{-- Unidad de medida --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Unidad de Medida <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <select name="unidad_medida_id" id="unidad_medida_id"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($unidades as $unidad)
                                        <option value="{{ $unidad->id }}"
                                                {{ old('unidad_medida_id', $producto->unidad_medida_id) == $unidad->id ? 'selected' : '' }}>
                                            {{ $unidad->nombre }} ({{ $unidad->abreviatura }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="abrirModalUnidad()"
                                        class="px-3 py-2 bg-green-100 text-green-700 border border-green-300 rounded-lg hover:bg-green-200 transition shrink-0">
                                    <i class="fas fa-plus text-sm"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Estado --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado <span class="text-red-500">*</span></label>
                            <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]" required>
                                <option value="activo"        {{ old('estado', $producto->estado) == 'activo'        ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo"      {{ old('estado', $producto->estado) == 'inactivo'      ? 'selected' : '' }}>Inactivo</option>
                                <option value="descontinuado" {{ old('estado', $producto->estado) == 'descontinuado' ? 'selected' : '' }}>Descontinuado</option>
                            </select>
                        </div>

                        {{-- Procedencia --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Procedencia</label>
                            <input type="text" name="procedencia"
                                   value="{{ old('procedencia', $producto->procedencia) }}"
                                   placeholder="Ej: China, Italia"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                        </div>

                        {{-- Línea --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Línea</label>
                            <input type="text" name="linea"
                                   value="{{ old('linea', $producto->linea) }}"
                                   placeholder="Ej: Premium, Básica"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                        </div>

                        {{-- Descripción --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea name="descripcion" rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">{{ old('descripcion', $producto->descripcion) }}</textarea>
                        </div>

                        {{-- URL Ficha Técnica --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL Ficha Técnica (PDF)</label>
                            <input type="text" name="ficha_tecnica_url"
                                   value="{{ old('ficha_tecnica_url', $producto->ficha_tecnica_url) }}"
                                   placeholder="https://..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                        </div>

                        {{-- Observaciones --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea name="observaciones" rows="1"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">{{ old('observaciones', $producto->observaciones) }}</textarea>
                        </div>

                        {{-- Imagen --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imagen</label>
                            <div class="flex items-center gap-4">
                                @if($producto->imagen)
                                    <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}"
                                         class="h-16 w-16 object-cover rounded-lg border-2 border-gray-200">
                                @endif
                                <div id="imagePreviewContainer" class="hidden">
                                    <img id="imagePreview" src="" alt="Vista previa" class="h-16 w-16 object-cover rounded-lg border">
                                </div>
                                <input type="file" name="imagen" id="imagen" accept="image/*"
                                       class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#2B2E2C]/10 file:text-[#2B2E2C] hover:file:bg-[#2B2E2C]/10"
                                       onchange="previewImage(event)">
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Deja vacío para conservar la imagen actual</p>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                    BLOQUE 4 — CONTROL DE STOCK
                ══════════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5">
                    <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#2B2E2C;">4</span>
                        <h2 class="font-semibold text-gray-800">Control de Stock</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo <span class="text-red-500">*</span></label>
                            <input type="number" name="stock_minimo"
                                   value="{{ old('stock_minimo', $producto->stock_minimo) }}" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600]" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Máximo <span class="text-red-500">*</span></label>
                            <input type="number" name="stock_maximo"
                                   value="{{ old('stock_maximo', $producto->stock_maximo) }}" min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600]" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                            <input type="text" name="ubicacion"
                                   value="{{ old('ubicacion', $producto->ubicacion) }}"
                                   placeholder="Ej: Estante A-3"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600]">
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                    BLOQUE UBICACIONES FÍSICAS
                ══════════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5">
                    <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-[#2B2E2C]"></i>
                        <h2 class="font-semibold text-gray-800">Ubicaciones Físicas
                            <span class="text-xs font-normal text-gray-400">(dónde se almacena este producto)</span>
                        </h2>
                    </div>
                    <div class="p-6">
                        @include('inventario.productos.partials.modal-ubicaciones')
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                    FICHA TÉCNICA EN TABS (Especificaciones / Dimensiones /
                    Fotometría / Embalaje / Clasificaciones / Atributos / Variantes)
                ══════════════════════════════════════════════════════════ --}}
                <div x-data="{ tab: 'especificaciones' }" class="mb-5">

                    {{-- Barra de tabs --}}
                    <div class="bg-white rounded-t-xl border border-gray-200 overflow-x-auto">
                        <div class="flex min-w-max">
                            @php
                                $tabs = [
                                    ['id'=>'especificaciones', 'icon'=>'fa-bolt',           'label'=>'Especificaciones', 'color'=>'yellow'],
                                    ['id'=>'fotometria',       'icon'=>'fa-sun',            'label'=>'Fotometría',       'color'=>'amber'],
                                    ['id'=>'dimensiones',      'icon'=>'fa-ruler-combined', 'label'=>'Dimensiones',      'color'=>'blue'],
                                    ['id'=>'embalaje',         'icon'=>'fa-box-open',       'label'=>'Embalaje',         'color'=>'teal'],
                                    ['id'=>'clasificaciones',  'icon'=>'fa-tags',           'label'=>'Clasificaciones',  'color'=>'purple'],
                                    ['id'=>'variantes',        'icon'=>'fa-layer-group',    'label'=>'Variantes',        'color'=>'indigo'],
                                ];
                            @endphp
                            @foreach($tabs as $t)
                            <button type="button"
                                    @click="tab = '{{ $t['id'] }}'"
                                    :class="tab === '{{ $t['id'] }}'
                                        ? 'border-b-2 border-yellow-400 text-gray-900 bg-yellow-50 font-semibold'
                                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                                    class="flex items-center gap-1.5 px-5 py-3 text-sm transition whitespace-nowrap">
                                <i class="fas {{ $t['icon'] }} text-xs opacity-70"></i>
                                {{ $t['label'] }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Panel de tabs --}}
                    <div class="bg-white rounded-b-xl border border-t-0 border-gray-200 p-6">

                        {{-- Tab: Especificaciones --}}
                        <div x-show="tab === 'especificaciones'" x-cloak>
                            @include('inventario.productos.partials.tab-especificaciones')
                        </div>

                        {{-- Tab: Fotometría --}}
                        <div x-show="tab === 'fotometria'" x-cloak>
                            @include('inventario.productos.partials.tab-fotometria')
                        </div>

                        {{-- Tab: Dimensiones --}}
                        <div x-show="tab === 'dimensiones'" x-cloak>
                            @include('inventario.productos.partials.tab-dimensiones')
                        </div>

                        {{-- Tab: Embalaje --}}
                        <div x-show="tab === 'embalaje'" x-cloak>
                            @include('inventario.productos.partials.tab-embalaje')
                        </div>

                        {{-- Tab: Clasificaciones --}}
                        <div x-show="tab === 'clasificaciones'" x-cloak>
                            @include('luminarias.partials.ficha-clasificaciones')
                        </div>

                        {{-- Tab: Variantes --}}
                        <div x-show="tab === 'variantes'" x-cloak>
                        @php $atributosLuminaria = \App\Models\ProductoVariante::ATRIBUTOS_LUMINARIA; @endphp

                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            {{-- Header con toggle + link --}}
                            <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-layer-group text-[#2B2E2C]"></i>
                                    <h2 class="font-semibold text-gray-800">Variantes del Producto</h2>
                                    {{-- Badge estado --}}
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                          {{ $producto->tiene_variantes ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $producto->tiene_variantes ? 'Activas' : 'Desactivadas' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    {{-- Toggle rápido --}}
                                    <form action="{{ route('inventario.productos.toggle-variantes', $producto) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded-lg border font-medium transition
                                                       {{ $producto->tiene_variantes
                                                           ? 'border-red-200 text-red-600 hover:bg-red-50'
                                                           : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                                            <i class="fas {{ $producto->tiene_variantes ? 'fa-toggle-off' : 'fa-toggle-on' }} mr-1"></i>
                                            {{ $producto->tiene_variantes ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('inventario.productos.variantes', $producto) }}"
                                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                        Gestión completa <i class="fas fa-external-link-alt ml-1"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="p-6">
                                @if(!$producto->tiene_variantes)
                                    <div class="text-center py-6 text-gray-400">
                                        <i class="fas fa-layer-group text-3xl text-gray-200 mb-2"></i>
                                        <p class="text-sm">Modo variantes desactivado. Actívalo para registrar variantes de este producto.</p>
                                    </div>
                                @else

                                {{-- Tabla de variantes existentes --}}
                                @if($producto->variantes->count() > 0)
                                <div class="mb-5 overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="text-xs text-gray-500 border-b border-gray-100">
                                                <th class="text-left pb-2 font-medium">Variante</th>
                                                <th class="text-left pb-2 font-medium">Atributos clave</th>
                                                <th class="text-right pb-2 font-medium">Sobreprecio</th>
                                                <th class="text-right pb-2 font-medium">Stock</th>
                                                <th class="text-center pb-2 font-medium">Estado</th>
                                                <th class="pb-2 w-20"></th>
                                            </tr>
                                        </thead>
                                        @foreach($producto->variantes as $variante)
                                        <tbody x-data="{ editando: false }" class="divide-y divide-gray-50">
                                            <tr>
                                                <td class="py-2">
                                                    <div class="flex items-center gap-2">
                                                        @if($variante->color && $variante->color->codigo_hex)
                                                        <div class="w-3.5 h-3.5 rounded-full border border-gray-200 shrink-0"
                                                             style="background-color:{{ $variante->color->codigo_hex }}"></div>
                                                        @endif
                                                        <span class="font-medium text-gray-800">{{ $variante->nombre_completo }}</span>
                                                    </div>
                                                    <span class="font-mono text-xs text-gray-400">{{ $variante->sku }}</span>
                                                </td>
                                                <td class="py-2">
                                                    @php $atribs = array_filter($variante->atributos ?? []); @endphp
                                                    @if(!empty($atribs))
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach(array_slice($atribs, 0, 3) as $clave => $valor)
                                                                <span class="text-xs bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded">
                                                                    {{ $valor }}
                                                                </span>
                                                            @endforeach
                                                            @if(count($atribs) > 3)
                                                                <span class="text-xs text-gray-400">+{{ count($atribs) - 3 }}</span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-gray-300 text-xs">—</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 text-right text-gray-700">
                                                    {{ $variante->sobreprecio > 0 ? '+S/ ' . number_format($variante->sobreprecio, 2) : '—' }}
                                                </td>
                                                <td class="py-2 text-right">
                                                    <span class="{{ $variante->stock_actual > 0 ? 'text-green-700 bg-green-50' : 'text-gray-400 bg-gray-50' }} text-xs px-2 py-0.5 rounded-full">
                                                        {{ $variante->stock_actual }}
                                                    </span>
                                                </td>
                                                <td class="py-2 text-center">
                                                    <span class="{{ $variante->estado === 'activo' ? 'text-green-700 bg-green-50' : 'text-red-600 bg-red-50' }} text-xs px-2 py-0.5 rounded-full">
                                                        {{ $variante->estado }}
                                                    </span>
                                                </td>
                                                <td class="py-2 text-right">
                                                    <button type="button" @click="editando = !editando"
                                                            class="text-indigo-400 hover:text-indigo-600 mr-2">
                                                        <i class="fas fa-pencil-alt text-xs"></i>
                                                    </button>
                                                    @if($variante->estado === 'activo')
                                                    <form method="POST" action="{{ route('inventario.productos.variantes.destroy', $variante) }}"
                                                          class="inline" onsubmit="return confirm('¿Desactivar esta variante?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-red-400 hover:text-red-600">
                                                            <i class="fas fa-ban text-xs"></i>
                                                        </button>
                                                    </form>
                                                    @else
                                                    <form method="POST" action="{{ route('inventario.productos.variantes.reactivar', $variante) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-green-500 hover:text-green-700">
                                                            <i class="fas fa-check-circle text-xs"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                </td>
                                            </tr>
                                            {{-- Fila de edición inline --}}
                                            <tr x-show="editando" x-cloak>
                                                <td colspan="6" class="py-3 px-2">
                                                    <form method="POST" action="{{ route('inventario.productos.variantes.update', $variante) }}"
                                                          class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                                                        @csrf @method('PUT')
                                                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 mb-3">
                                                            <div>
                                                                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                                                                <input type="text" name="nombre" value="{{ $variante->nombre }}"
                                                                       placeholder="Nombre descriptivo"
                                                                       class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                                                <select name="color_id" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                                                    <option value="">Sin color</option>
                                                                    @foreach($colores as $c)
                                                                        <option value="{{ $c->id }}" {{ $variante->color_id == $c->id ? 'selected' : '' }}>
                                                                            {{ $c->nombre }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-medium text-gray-600 mb-1">Sobreprecio S/</label>
                                                                <input type="number" name="sobreprecio" value="{{ $variante->sobreprecio }}"
                                                                       min="0" step="0.01"
                                                                       class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                                                                <select name="estado" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                                                    <option value="activo"   {{ $variante->estado === 'activo'   ? 'selected' : '' }}>Activo</option>
                                                                    <option value="inactivo" {{ $variante->estado === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        {{-- Atributos luminaria --}}
                                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                                                            @foreach($atributosLuminaria as $clave => $meta)
                                                                <div>
                                                                    <label class="block text-xs text-gray-500 mb-0.5">{{ $meta['label'] }}</label>
                                                                    <input type="text" name="atributos[{{ $clave }}]"
                                                                           value="{{ ($variante->atributos ?? [])[$clave] ?? '' }}"
                                                                           placeholder="{{ $meta['placeholder'] }}"
                                                                           class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#F7D600]">
                                                                </div>
                                                            @endforeach
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
                                                </td>
                                            </tr>
                                        </tbody>
                                        @endforeach
                                    </table>
                                </div>
                                @else
                                    <p class="text-sm text-gray-400 mb-4">Este producto no tiene variantes aún.</p>
                                @endif

                                {{-- Formulario para agregar nueva variante --}}
                                <div x-data="{ abierto: false }">
                                    <button type="button" @click="abierto = !abierto"
                                            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                        <i class="fas fa-plus mr-1"></i>
                                        <span x-text="abierto ? 'Cancelar' : 'Agregar variante'"></span>
                                    </button>

                                    <div x-show="abierto" x-cloak class="mt-4">
                                        <form method="POST" action="{{ route('inventario.productos.variantes.store', $producto) }}"
                                              class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                                            @csrf
                                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 mb-3">
                                                <div class="sm:col-span-3">
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre descriptivo</label>
                                                    <input type="text" name="nombre" placeholder="Ej: LED 3000K Negro Mate"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                                    <select name="color_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                                        <option value="">Sin color</option>
                                                        @foreach($colores as $color)
                                                            <option value="{{ $color->id }}">{{ $color->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Sobreprecio S/</label>
                                                    <input type="number" name="sobreprecio" min="0" step="0.01" placeholder="0.00"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                                </div>
                                            </div>
                                            {{-- Atributos luminaria compactos --}}
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                                                @foreach($atributosLuminaria as $clave => $meta)
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-0.5">{{ $meta['label'] }}</label>
                                                        <input type="text" name="atributos[{{ $clave }}]"
                                                               placeholder="{{ $meta['placeholder'] }}"
                                                               class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#F7D600]">
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="submit"
                                                    class="px-4 py-2 bg-[#F7D600] text-[#2B2E2C] text-sm font-medium rounded-lg hover:bg-[#e8c900]">
                                                <i class="fas fa-plus mr-1"></i>Agregar Variante
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>{{-- /variantes border div --}}

                        </div>{{-- /tab variantes x-show --}}

                    </div>{{-- /panel body bg-white rounded-b-xl --}}
                </div>{{-- /Alpine x-data tab wrapper --}}

                {{-- Botones de acción --}}
                <div class="flex items-center justify-between pt-4">
                    <a href="{{ route('inventario.productos.codigos-barras', $producto) }}"
                       class="px-4 py-2 text-sm text-[#2B2E2C] border border-blue-300 rounded-lg hover:bg-[#2B2E2C]/10">
                        <i class="fas fa-barcode mr-2"></i>Gestionar Códigos de Barras
                    </a>
                    <div class="flex gap-3">
                        <a href="{{ route('inventario.productos.show', $producto) }}"
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit"
                                form="formProducto"
                                class="px-8 py-2.5 text-gray-900 rounded-lg font-semibold text-sm shadow-sm"
                                style="background-color:#F7D600;"
                                onmouseover="this.style.backgroundColor='#e8c900'"
                                onmouseout="this.style.backgroundColor='#F7D600'">
                            <i class="fas fa-save mr-2"></i>Guardar Cambios
                        </button>
                    </div>
                </div>

            </form>

            {{-- ══════════════════════════════════════════════════════════
                BLOQUE BOM — COMPONENTES (solo para productos compuestos)
                Va fuera del form principal para evitar forms anidados
            ══════════════════════════════════════════════════════════ --}}
            @if($producto->tipo_sistema === 'compuesto')
                <div class="mb-5">
                    @include('inventario.productos.partials.componentes-form')
                </div>
            @endif

        </div>
    </div>

    @include('inventario.productos.partials.modales-rapidos')

    <script>
    // ─── Tipos de producto (desde BD) ────────────────────────────────────────────
    const TIPOS_DATA         = @json($tiposProducto->keyBy('id'));
    const TIPOS_CON_FICHA    = new Set(['LU','LA','CL','SM','EA','PE','PA','VE','CA','PO','LE','SO','RE']);
    const TIPOS_CON_LUMENES  = new Set(['LU','LA','CL','SM','PE','PA','PO','LE','SO','RE']);
    const TIPOS_CON_CRI      = new Set(['LU','LA','CL','SM','PE','PA']);
    const TIPOS_CON_IP       = new Set(['LU','CL','PO','LE','SO']);
    const TIPOS_CON_ANGULO   = new Set(['LU','LA','CA','PE']);

    let tipoProductoId  = {{ $tpSelId ?? 'null' }};
    let tipoLuminariaId = {{ $tlSelId ?? 'null' }};
    let marcaId         = {{ old('marca_id', $producto->marca_id ?? 'null') }};
    let tipoCodigoActual = '{{ $tpSel ? $tpSel->codigo : '' }}';

    function aplicarTipoProducto(id, codigo, usaLuminaria) {
        tipoProductoId   = id;
        tipoCodigoActual = codigo;

        // Mostrar/ocultar bloque tipo luminaria
        document.getElementById('bloqueTipoLuminaria').classList.toggle('hidden', !usaLuminaria);
        if (!usaLuminaria) {
            tipoLuminariaId = null;
            document.querySelectorAll('#selectorTipoLuminaria input').forEach(i => i.checked = false);
            document.querySelectorAll('.tipo-lum-card').forEach(c => c.classList.remove('border-yellow-400','bg-yellow-50'));
        }

        // Mostrar/ocultar ficha técnica
        const hasFicha = TIPOS_CON_FICHA.has(codigo);
        document.getElementById('bloqueFichaTecnica').classList.toggle('hidden', !hasFicha);

        // Campos condicionales dentro de la ficha
        if (hasFicha) {
            toggleCampo('.campo-lumenes', TIPOS_CON_LUMENES.has(codigo));
            toggleCampo('.campo-cri',     TIPOS_CON_CRI.has(codigo));
            toggleCampo('.campo-ip',      TIPOS_CON_IP.has(codigo));
            toggleCampo('.campo-angulo',  TIPOS_CON_ANGULO.has(codigo));
        }

        // Estilos tarjetas
        document.querySelectorAll('.tipo-card').forEach(c => {
            c.classList.remove('border-yellow-400','bg-yellow-50');
            if (parseInt(c.dataset.id) === id) c.classList.add('border-yellow-400','bg-yellow-50');
        });
    }

    function toggleCampo(sel, vis) {
        document.querySelectorAll(sel).forEach(el => el.style.display = vis ? '' : 'none');
    }

    // Click en tarjetas tipo producto
    document.querySelectorAll('.tipo-card').forEach(card => {
        card.addEventListener('click', () => {
            card.querySelector('input[type=radio]').checked = true;
            aplicarTipoProducto(
                parseInt(card.dataset.id),
                card.dataset.codigo,
                card.dataset.usaLuminaria === '1'
            );
        });
    });

    // Click en tarjetas tipo luminaria
    document.querySelectorAll('.tipo-lum-card').forEach(card => {
        card.addEventListener('click', () => {
            card.querySelector('input[type=radio]').checked = true;
            tipoLuminariaId = parseInt(card.dataset.id);
            document.querySelectorAll('.tipo-lum-card').forEach(c => c.classList.remove('border-yellow-400','bg-yellow-50'));
            card.classList.add('border-yellow-400','bg-yellow-50');
        });
    });

    // Inicializar campos condicionales según tipo actual
    if (tipoCodigoActual) {
        const hasFicha = TIPOS_CON_FICHA.has(tipoCodigoActual);
        if (hasFicha) {
            toggleCampo('.campo-lumenes', TIPOS_CON_LUMENES.has(tipoCodigoActual));
            toggleCampo('.campo-cri',     TIPOS_CON_CRI.has(tipoCodigoActual));
            toggleCampo('.campo-ip',      TIPOS_CON_IP.has(tipoCodigoActual));
            toggleCampo('.campo-angulo',  TIPOS_CON_ANGULO.has(tipoCodigoActual));
        }
    }

    // ─── Generación de Código Kyrios ──────────────────────────────────────────
    document.getElementById('btnGenerarKyrios')?.addEventListener('click', function () {
        if (!tipoProductoId) { alert('Selecciona el tipo de producto primero'); return; }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Generando...';

        const params = new URLSearchParams({
            tipo_producto_id:  tipoProductoId  || '',
            tipo_luminaria_id: tipoLuminariaId || '',
            marca_id:          marcaId         || '',
        });

        fetch(`{{ route('inventario.productos.generar-codigo-kyrios') }}?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const input = document.getElementById('codigo_kyrios');
                input.value = data.codigo;
                input.classList.add('border-green-500','bg-green-50');
                setTimeout(() => input.classList.remove('border-green-500','bg-green-50'), 1400);
            } else {
                alert(data.message || 'No se pudo generar el código');
            }
        })
        .catch(() => alert('Error de conexión al generar código Kyrios'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync-alt mr-1"></i>Regenerar';
        });
    });

    // ─── Código de Barras ────────────────────────────────────────────────────
    document.getElementById('btnGenerarCodigo')?.addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch('{{ route("inventario.productos.generar-codigo-barras") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ tipo: 'accesorio' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const input = document.getElementById('codigo_barras');
                input.value = data.codigo;
                input.classList.add('border-green-500','bg-green-50');
                setTimeout(() => input.classList.remove('border-green-500','bg-green-50'), 1200);
            }
        })
        .catch(() => alert('Error al generar código de barras'))
        .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-sync-alt"></i>'; });
    });

    // ─── Marca — inicializar marcaId desde el select ya renderizado ──────────
    document.getElementById('marca_id')?.addEventListener('change', function () {
        marcaId = this.value ? parseInt(this.value) : null;
    });
    // Inicializar con el valor actual del select
    const _marcaSelectVal = document.getElementById('marca_id')?.value;
    if (_marcaSelectVal) marcaId = parseInt(_marcaSelectVal);

    // ─── Sugerir nombre ───────────────────────────────────────────────────────
    document.getElementById('btnSugerirNombre')?.addEventListener('click', function () {
        const marca  = document.getElementById('marca_id').selectedOptions[0]?.text || '';
        const modelo = document.getElementById('modelo_id').selectedOptions[0]?.text || '';
        const partes = [];
        if (marca  && marca  !== 'Sin marca')  partes.push(marca);
        if (modelo && modelo !== 'Sin modelo') partes.push(modelo);
        if (partes.length > 0) document.getElementById('nombre').value = partes.join(' ');
        else alert('Selecciona al menos marca o modelo');
    });

    // ─── Preview imagen ───────────────────────────────────────────────────────
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    }
    </script>
</body>
</html>
