<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nuevo Producto — Configurador</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Configurador de Producto" subtitle="Crea un nuevo producto guiado por tipo" />

        <div class="max-w-5xl mx-auto">

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-300 rounded-lg p-4">
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

            <form action="{{ route('inventario.productos.store') }}" method="POST" enctype="multipart/form-data" id="formProducto">
                @csrf
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
                                $selTP  = old('tipo_producto_id') == $tp->id;
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

                        {{-- Tipo Luminaria (condicional — aparece si usa_tipo_luminaria = true) --}}
                        <div id="bloqueTipoLuminaria" class="{{ old('tipo_producto_id') && $tiposProducto->find(old('tipo_producto_id'))?->usa_tipo_luminaria ? '' : 'hidden' }} mt-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-th-large mr-1 text-yellow-500"></i>
                                Tipo de Luminaria <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-2" id="selectorTipoLuminaria">
                                @foreach($tiposLuminaria as $tl)
                                <label class="tipo-lum-card flex flex-col items-center gap-1 py-3 px-2 border-2 rounded-xl cursor-pointer transition-all hover:border-yellow-400
                                              {{ old('tipo_luminaria_id') == $tl->id ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200 bg-white' }}"
                                       data-id="{{ $tl->id }}"
                                       data-codigo="{{ $tl->codigo }}">
                                    <input type="radio" name="tipo_luminaria_id" value="{{ $tl->id }}"
                                           class="sr-only" {{ old('tipo_luminaria_id') == $tl->id ? 'checked' : '' }}>
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

                        {{-- Código Kyrios auto-generado --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Código Kyrios
                                <span class="ml-1 text-xs text-gray-400">— generado por tipo + marca</span>
                            </label>
                            <div class="flex gap-2 items-center">
                                <input type="text" name="codigo_kyrios" id="codigo_kyrios"
                                       value="{{ old('codigo_kyrios') }}"
                                       placeholder="Selecciona tipo de producto y marca primero"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 font-mono bg-gray-50"
                                       readonly>
                                <button type="button" id="btnGenerarKyrios"
                                        class="px-4 py-2 text-gray-900 rounded-lg font-semibold text-sm transition shrink-0"
                                        style="background-color:#F7D600;"
                                        onmouseover="this.style.backgroundColor='#e8c900'"
                                        onmouseout="this.style.backgroundColor='#F7D600'">
                                    <i class="fas fa-magic mr-1"></i>Generar
                                </button>
                                <button type="button" id="btnEditarKyrios"
                                        title="Editar manualmente"
                                        class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition shrink-0">
                                    <i class="fas fa-pencil-alt text-sm"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                Formato: <span class="font-mono">KY-[TP][TL][M]-[NNNN]</span>
                                &nbsp;|&nbsp; Ej: <span class="font-mono text-gray-600">KY-LUDLPH-0001</span>
                            </p>
                        </div>

                        {{-- Código Fábrica --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código Fábrica</label>
                            <input type="text" name="codigo_fabrica" value="{{ old('codigo_fabrica') }}"
                                   placeholder="Código del fabricante"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                        </div>

                        {{-- Código de Barras --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código de Barras</label>
                            <div class="flex gap-2">
                                <input type="text" name="codigo_barras" id="codigo_barras"
                                       value="{{ old('codigo_barras') }}"
                                       placeholder="Dejar vacío para auto-generar"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400">
                                <button type="button" id="btnGenerarCodigo"
                                        class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 shrink-0">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Categoría --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Categoría <span class="text-red-500">*</span>
                            </label>
                            <select name="categoria_id" id="categoria_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]" required>
                                <option value="">Seleccione una categoría</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categoria_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Marca --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                            <div class="flex gap-2">
                                <select name="marca_id" id="marca_id"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                                    <option value="">Seleccione categoría primero</option>
                                </select>
                                <button type="button" onclick="abrirModalMarca()"
                                        class="px-3 py-2 bg-[#2B2E2C]/10 text-[#2B2E2C] border border-blue-300 rounded-lg hover:bg-[#2B2E2C]/10 transition shrink-0">
                                    <i class="fas fa-plus text-sm"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Modelo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                            <div class="flex gap-2">
                                <select name="modelo_id" id="modelo_id"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                                    <option value="">Seleccione marca primero</option>
                                </select>
                                <button type="button" onclick="abrirModalModelo()"
                                        class="px-3 py-2 bg-[#2B2E2C]/10 text-[#2B2E2C] border border-[#F7D600]/40 rounded-lg hover:bg-[#F7D600]/20 transition shrink-0">
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

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre del Producto <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="nombre" id="nombre"
                                       value="{{ old('nombre') }}"
                                       placeholder="Nombre descriptivo del producto"
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Unidad de Medida <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <select name="unidad_medida_id" id="unidad_medida_id"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($unidades as $unidad)
                                        <option value="{{ $unidad->id }}" {{ old('unidad_medida_id') == $unidad->id ? 'selected' : '' }}>
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado <span class="text-red-500">*</span></label>
                            <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]" required>
                                <option value="activo"        {{ old('estado', 'activo') == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo"      {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                <option value="descontinuado" {{ old('estado') == 'descontinuado' ? 'selected' : '' }}>Descontinuado</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Procedencia</label>
                            <input type="text" name="procedencia" value="{{ old('procedencia') }}"
                                   placeholder="Ej: China, Italia"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Línea</label>
                            <input type="text" name="linea" value="{{ old('linea') }}"
                                   placeholder="Ej: Premium, Básica"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea name="descripcion" rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                                      placeholder="Descripción del producto">{{ old('descripcion') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL Ficha Técnica (PDF)</label>
                            <input type="text" name="ficha_tecnica_url" value="{{ old('ficha_tecnica_url') }}"
                                   placeholder="https://..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea name="observaciones" rows="1"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">{{ old('observaciones') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imagen</label>
                            <div class="flex items-center gap-3">
                                <div id="imagePreviewContainer" class="hidden">
                                    <img id="imagePreview" src="" alt="Vista previa" class="h-16 w-16 object-cover rounded-lg border">
                                </div>
                                <input type="file" name="imagen" accept="image/*"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#2B2E2C]/10 file:text-[#2B2E2C] hover:file:bg-[#2B2E2C]/10"
                                       onchange="previewImage(event)">
                            </div>
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
                            <input type="number" name="stock_minimo" value="{{ old('stock_minimo', 5) }}" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600]" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Máximo <span class="text-red-500">*</span></label>
                            <input type="number" name="stock_maximo" value="{{ old('stock_maximo', 500) }}" min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600]" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                            <input type="text" name="ubicacion" value="{{ old('ubicacion') }}"
                                   placeholder="Ej: Estante A-3"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Inicial</label>
                            <input type="number" name="stock_inicial" value="{{ old('stock_inicial', 0) }}" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Almacén (stock inicial)</label>
                            <select name="almacen_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F7D600]">
                                <option value="">Sin asignar</option>
                                @foreach($almacenes as $alm)
                                    <option value="{{ $alm->id }}" {{ old('almacen_id') == $alm->id ? 'selected' : '' }}>{{ $alm->nombre }}</option>
                                @endforeach
                            </select>
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
                            <span class="text-xs font-normal text-gray-400">(opcional — dónde se almacena)</span>
                        </h2>
                    </div>
                    <div class="p-6">
                        @include('inventario.productos.partials.modal-ubicaciones')
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                    BLOQUES 5-7 — FICHA TÉCNICA (se oculta si tipo no la requiere)
                ══════════════════════════════════════════════════════════ --}}
                <div id="bloqueFichaTecnica" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5">
                    <div class="px-6 py-4 flex items-center gap-3" style="background-color:#1a3a2a;">
                        <i class="fas fa-lightbulb text-xl" style="color:#F7D600;"></i>
                        <div>
                            <h2 class="text-base font-bold text-white">Configuración Técnica — Ficha Kyrios</h2>
                            <p class="text-xs text-gray-300">Especificaciones técnicas según el tipo seleccionado</p>
                        </div>
                    </div>
                    <div class="p-6">
                        @include('luminarias.partials.ficha-tecnica-form')
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                    BLOQUE CONFIGURADOR DE ATRIBUTOS DINÁMICOS
                ══════════════════════════════════════════════════════════ --}}
                @include('inventario.productos.partials.atributos-dinamicos', [
                    'atributosGrupos'  => $atributosGrupos,
                    'atributosActuales'=> [],
                ])

                {{-- ══════════════════════════════════════════════════════════
                    BLOQUE 8 — VARIANTES
                ══════════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5"
                     x-data="variantesManager(@js($colores->map(fn($c)=>['id'=>$c->id,'nombre'=>$c->nombre,'hex'=>$c->hex??'#cccccc'])->values()))">
                    <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#2B2E2C;">8</span>
                            <h2 class="font-semibold text-gray-800">Variantes del Producto
                                <span class="text-xs font-normal text-gray-400">(opcional)</span>
                            </h2>
                        </div>
                        <span x-show="variantes.length > 0"
                              class="text-xs bg-[#2B2E2C]/10 text-[#2B2E2C] rounded-full px-2 py-0.5 font-medium"
                              x-text="variantes.length + ' variante' + (variantes.length > 1 ? 's' : '')"></span>
                    </div>
                    <div class="p-6">

                        {{-- Lista de variantes agregadas --}}
                        <template x-if="variantes.length > 0">
                            <div class="mb-5">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-xs text-gray-500 border-b border-gray-100">
                                            <th class="text-left pb-2 font-medium">Nombre / Color</th>
                                            <th class="text-left pb-2 font-medium">Especificación</th>
                                            <th class="text-right pb-2 font-medium">Sobreprecio</th>
                                            <th class="pb-2 w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <template x-for="(v, idx) in variantes" :key="idx">
                                            <tr class="py-2">
                                                <td class="py-2">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-4 h-4 rounded-full border border-gray-200 shrink-0"
                                                             :style="v.color_hex ? `background-color:${v.color_hex}` : 'background:#e5e7eb'"></div>
                                                        <span class="font-medium text-gray-800"
                                                              x-text="v.nombre || v.color_nombre || 'Sin nombre'"></span>
                                                    </div>
                                                </td>
                                                <td class="py-2 text-gray-500" x-text="v.especificacion || '—'"></td>
                                                <td class="py-2 text-right text-gray-700"
                                                    x-text="v.sobreprecio > 0 ? '+S/ ' + parseFloat(v.sobreprecio).toFixed(2) : '—'"></td>
                                                <td class="py-2 text-right">
                                                    <button type="button" @click="eliminar(idx)"
                                                            class="text-red-400 hover:text-red-600 transition-colors">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </td>
                                                {{-- Hidden inputs para envío al servidor --}}
                                                <input type="hidden" :name="`variantes_iniciales[${idx}][nombre]`" :value="v.nombre">
                                                <input type="hidden" :name="`variantes_iniciales[${idx}][color_id]`" :value="v.color_id || ''">
                                                <input type="hidden" :name="`variantes_iniciales[${idx}][capacidad]`" :value="v.especificacion">
                                                <input type="hidden" :name="`variantes_iniciales[${idx}][sobreprecio]`" :value="v.sobreprecio || 0">
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        {{-- Formulario para agregar nueva variante --}}
                        <div class="grid grid-cols-2 gap-3 mb-3 sm:grid-cols-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre variante</label>
                                <input type="text" x-model="nueva.nombre" placeholder="Ej: Versión Calida"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                <select x-model="nueva.color_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                                    <option value="">Sin color</option>
                                    <template x-for="c in coloresCatalogo" :key="c.id">
                                        <option :value="c.id" x-text="c.nombre"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Especificación</label>
                                <input type="text" x-model="nueva.especificacion" placeholder="Ej: 3000K, 18W"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Sobreprecio S/</label>
                                <input type="number" x-model="nueva.sobreprecio" min="0" step="0.01" placeholder="0.00"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                            </div>
                        </div>
                        <button type="button" @click="agregar()"
                                class="px-4 py-2 bg-[#F7D600] text-[#2B2E2C] rounded-lg text-sm hover:bg-[#e8c900] transition-colors">
                            <i class="fas fa-plus mr-1"></i>Agregar Variante
                        </button>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-between pt-4">
                    <a href="{{ route('inventario.productos.index') }}"
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="submit"
                            class="px-8 py-2.5 text-gray-900 rounded-lg font-semibold text-sm shadow-sm"
                            style="background-color:#F7D600;"
                            onmouseover="this.style.backgroundColor='#e8c900'"
                            onmouseout="this.style.backgroundColor='#F7D600'">
                        <i class="fas fa-save mr-2"></i>Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('inventario.productos.partials.modales-rapidos')

    @php
        $atributosParaNombre = $atributosGrupos->flatten()
            ->filter(fn($a) => $a->en_nombre_auto)
            ->sortBy('orden_nombre')
            ->map(fn($a) => ['slug' => $a->slug, 'nombre' => $a->nombre, 'unidad' => $a->unidad])
            ->values();
        $valoresMap = \App\Models\Catalogo\CatalogoValor::whereHas('atributo', fn($q) => $q->where('en_nombre_auto', true))
            ->get()
            ->mapWithKeys(fn($v) => [$v->id => $v->etiqueta ?? $v->valor]);
    @endphp
    <script>
    // ─── Atributos para nombre auto-generado ──────────────────────────────────────
    window.ATRIBUTOS_PARA_NOMBRE = @json($atributosParaNombre);
    window.VALORES_MAP = @json($valoresMap);

    // ─── Tipos de producto (desde BD, datos serializados) ────────────────────────
    const TIPOS_DATA = @json($tiposProducto->keyBy('id'));
    // Tipos que muestran ficha técnica (los que no son puro "accesorio/fuente sin specs")
    // Por ahora mostramos ficha para todos (el partial siempre está disponible)
    // Tipos que muestran ficha técnica (todos excepto AC - accesorios sin specs eléctricas)
    const TIPOS_CON_FICHA    = new Set(['LU','LA','CL','SM','EA','PE','PA','VE','CA','PO','LE','SO','RE']);
    const TIPOS_CON_LUMENES  = new Set(['LU','LA','CL','SM','PE','PA','PO','LE','SO','RE']);
    const TIPOS_CON_CRI      = new Set(['LU','LA','CL','SM','PE','PA']);
    const TIPOS_CON_IP       = new Set(['LU','CL','PO','LE','SO']);
    const TIPOS_CON_ANGULO   = new Set(['LU','LA','CA','PE']);

    let tipoProductoId  = {{ old('tipo_producto_id', 'null') }};
    let tipoLuminariaId = {{ old('tipo_luminaria_id', 'null') }};
    let marcaId         = {{ old('marca_id', 'null') }};
    let tipoCodigoActual = '';

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

        limpiarKyrios();
    }

    function toggleCampo(sel, vis) {
        document.querySelectorAll(sel).forEach(el => el.style.display = vis ? '' : 'none');
    }

    function limpiarKyrios() {
        const input = document.getElementById('codigo_kyrios');
        if (!input.readOnly) return; // si está en modo edición manual, no limpiar
        input.value = '';
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
            limpiarKyrios();
        });
    });

    // Inicializar tipo si hay old value
    if (tipoProductoId) {
        const card = document.querySelector(`.tipo-card[data-id="${tipoProductoId}"]`);
        if (card) aplicarTipoProducto(parseInt(card.dataset.id), card.dataset.codigo, card.dataset.usaLuminaria === '1');
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
            btn.innerHTML = '<i class="fas fa-magic mr-1"></i>Generar';
        });
    });

    // Botón editar manualmente el código Kyrios
    document.getElementById('btnEditarKyrios')?.addEventListener('click', function () {
        const input = document.getElementById('codigo_kyrios');
        if (input.readOnly) {
            input.readOnly = false;
            input.classList.remove('bg-gray-50');
            input.classList.add('bg-white');
            input.focus();
            this.innerHTML = '<i class="fas fa-lock text-sm"></i>';
            this.title = 'Bloquear edición';
        } else {
            input.readOnly = true;
            input.classList.remove('bg-white');
            input.classList.add('bg-gray-50');
            this.innerHTML = '<i class="fas fa-pencil-alt text-sm"></i>';
            this.title = 'Editar manualmente';
        }
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

    // ─── Marca/Modelo en cascada ──────────────────────────────────────────────
    function cargarMarcasPorCategoria(categoriaId, marcaSeleccionada = null) {
        const marcaSelect  = document.getElementById('marca_id');
        const modeloSelect = document.getElementById('modelo_id');
        marcaSelect.innerHTML = '<option value="">Cargando...</option>';
        marcaSelect.disabled = true;
        modeloSelect.innerHTML = '<option value="">Primero selecciona marca</option>';
        modeloSelect.disabled = true;

        if (!categoriaId) {
            marcaSelect.innerHTML = '<option value="">Sin marca</option>';
            marcaSelect.disabled = false;
            return;
        }

        fetch(`/catalogo/marcas-por-categoria/${categoriaId}`)
            .then(r => r.json())
            .then(data => {
                marcaSelect.innerHTML = '<option value="">Sin marca</option>';
                data.forEach(m => {
                    const sel = (marcaSeleccionada && m.id == marcaSeleccionada) ? 'selected' : '';
                    marcaSelect.innerHTML += `<option value="${m.id}" ${sel}>${m.nombre}</option>`;
                });
                marcaSelect.disabled = false;
                if (marcaSeleccionada) cargarModelosPorMarca(marcaSeleccionada);
            })
            .catch(() => { marcaSelect.innerHTML = '<option value="">Error</option>'; marcaSelect.disabled = false; });
    }

    function cargarModelosPorMarca(marcaId, modeloSel = null) {
        const modeloSelect = document.getElementById('modelo_id');
        modeloSelect.innerHTML = '<option value="">Cargando...</option>';
        modeloSelect.disabled = true;

        if (!marcaId) {
            modeloSelect.innerHTML = '<option value="">Sin modelo</option>';
            modeloSelect.disabled = false;
            return;
        }

        fetch(`/catalogo/modelos-por-marca/${marcaId}`)
            .then(r => r.json())
            .then(data => {
                modeloSelect.innerHTML = '<option value="">Sin modelo</option>';
                data.forEach(m => {
                    const sel = (modeloSel && m.id == modeloSel) ? 'selected' : '';
                    modeloSelect.innerHTML += `<option value="${m.id}" ${sel}>${m.nombre}</option>`;
                });
                modeloSelect.disabled = false;
            })
            .catch(() => { modeloSelect.innerHTML = '<option value="">Error</option>'; modeloSelect.disabled = false; });
    }

    document.getElementById('categoria_id')?.addEventListener('change', function () {
        cargarMarcasPorCategoria(this.value);
    });
    document.getElementById('marca_id')?.addEventListener('change', function () {
        marcaId = this.value || null;
        cargarModelosPorMarca(this.value);
        limpiarKyrios();
    });

    // Pre-cargar si hay old values
    const _cat = {{ old('categoria_id', 'null') }};
    const _mar = {{ old('marca_id', 'null') }};
    const _mod = {{ old('modelo_id', 'null') }};
    if (_cat) cargarMarcasPorCategoria(_cat, _mar);

    // ─── Sugerir nombre ───────────────────────────────────────────────────────
    document.getElementById('btnSugerirNombre')?.addEventListener('click', () => {
        const marca  = document.getElementById('marca_id')?.selectedOptions[0]?.text || '';
        const modelo = document.getElementById('modelo_id')?.selectedOptions[0]?.text || '';
        const partes = [];
        if (marca  && marca  !== 'Sin marca')  partes.push(marca);
        if (modelo && modelo !== 'Sin modelo') partes.push(modelo);
        if (partes.length) document.getElementById('nombre').value = partes.join(' ');
        else alert('Selecciona marca o modelo primero');
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

    // ─── Alpine: variantes ────────────────────────────────────────────────────
    function variantesManager(coloresCatalogo = []) {
        return {
            variantes: [],
            coloresCatalogo,
            colorMap: Object.fromEntries(coloresCatalogo.map(c => [String(c.id), c])),
            nueva: { nombre: '', color_id: '', especificacion: '', sobreprecio: 0 },

            agregar() {
                const color = this.colorMap[String(this.nueva.color_id)] || null;
                this.variantes.push({
                    nombre:         this.nueva.nombre.trim(),
                    color_id:       this.nueva.color_id || null,
                    color_nombre:   color ? color.nombre : 'Sin color',
                    color_hex:      color ? color.hex : null,
                    especificacion: this.nueva.especificacion.trim(),
                    sobreprecio:    parseFloat(this.nueva.sobreprecio) || 0,
                });
                this.nueva = { nombre: '', color_id: '', especificacion: '', sobreprecio: 0 };
            },

            eliminar(idx) { this.variantes.splice(idx, 1); },
        };
    }
    </script>
</body>
</html>
