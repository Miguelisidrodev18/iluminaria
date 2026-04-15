<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aprobación de Productos</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .detalle-row { display: none; }
        .detalle-row.abierto { display: table-row; }
        .pill { display:inline-block; padding:1px 8px; border-radius:9999px; font-size:11px; font-weight:500; }
        .spec-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:8px; }
        .spec-item label { display:block; font-size:10px; color:#6b7280; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
        .spec-item span  { display:block; font-size:13px; color:#111827; }
    </style>
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Aprobación de Productos"
            subtitle="Productos importados en estado Borrador"
        />

        <div class="max-w-7xl mx-auto space-y-4">

            {{-- ── Toolbar ──────────────────────────────────────────────────── --}}
            <div class="flex flex-wrap items-center justify-between gap-3 bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <form method="GET" class="flex items-center gap-2 flex-1 min-w-0 max-w-sm">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               placeholder="Código, nombre..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F7D600]" />
                    </div>
                    <button type="submit" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors">
                        Buscar
                    </button>
                </form>

                <div class="flex items-center gap-3">
                    <span id="label-seleccionados" class="text-sm text-gray-500 hidden">
                        <span id="cnt-seleccionados" class="font-bold text-[#2B2E2C]">0</span> seleccionados
                    </span>
                    <button id="btn-seleccionar-todo" class="text-sm text-gray-500 hover:text-gray-700 underline">
                        Seleccionar todos
                    </button>
                    <button id="btn-eliminar-lote"
                            disabled
                            class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fas fa-trash"></i>
                        Eliminar seleccionados
                    </button>
                    <button id="btn-aprobar-lote"
                            disabled
                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fas fa-check"></i>
                        Aprobar seleccionados
                    </button>
                </div>
            </div>

            {{-- ── Feedback ─────────────────────────────────────────────────── --}}
            <div id="alerta-aprobacion" style="display:none" class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
                <i class="fas fa-check-circle text-green-600 text-lg"></i>
                <p class="text-sm text-green-700 font-medium" id="msg-aprobacion"></p>
            </div>
            <div id="alerta-eliminacion" style="display:none" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
                <i class="fas fa-trash text-red-600 text-lg"></i>
                <p class="text-sm text-red-700 font-medium" id="msg-eliminacion"></p>
            </div>

            {{-- ── Modal confirmación eliminación ──────────────────────────── --}}
            <div id="modal-eliminar" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-trash text-red-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Eliminar productos</h3>
                            <p class="text-xs text-gray-500" id="modal-eliminar-subtitulo">Se eliminarán los productos seleccionados</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-5">Esta acción no se puede deshacer. Los productos en borrador serán eliminados permanentemente.</p>
                    <div class="flex gap-3">
                        <button type="button" id="modal-eliminar-cancelar"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                            Cancelar
                        </button>
                        <button type="button" id="modal-eliminar-confirmar"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors">
                            <i class="fas fa-trash mr-1"></i>Eliminar
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Modal Stock mín/máx ──────────────────────────────────────── --}}
            <div id="modal-stock" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-boxes text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Stock mínimo / máximo</h3>
                            <p class="text-xs text-gray-500" id="modal-stock-subtitulo">Define los niveles de reposición</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Stock mínimo</label>
                            <input type="number" id="input-stock-minimo" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Stock máximo</label>
                            <input type="number" id="input-stock-maximo" min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                    </div>
                    <p id="error-stock" class="text-xs text-red-600 mb-3 hidden"></p>
                    <div class="flex gap-3">
                        <button type="button" id="modal-stock-cancelar"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                            Cancelar
                        </button>
                        <button type="button" id="modal-stock-guardar"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">
                            <i class="fas fa-save mr-1"></i>Guardar
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Modal Ubicación ───────────────────────────────────────────── --}}
            <div id="modal-ubicacion" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-map-marker-alt text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Ubicación física</h3>
                            <p class="text-xs text-gray-500" id="modal-ubicacion-subtitulo">Dónde se almacena este producto</p>
                        </div>
                    </div>
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Referencia de ubicación</label>
                        <input type="text" id="input-ubicacion" maxlength="100"
                               placeholder="Ej: Estante A-3, Bodega 2, Vitrina principal"
                               class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-purple-400 outline-none">
                        <p class="text-xs text-gray-400 mt-1">Texto libre — no vinculado a almacén de sucursal.</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="modal-ubicacion-cancelar"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                            Cancelar
                        </button>
                        <button type="button" id="modal-ubicacion-guardar"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl transition-colors">
                            <i class="fas fa-save mr-1"></i>Guardar
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Modal contraseña maestra ─────────────────────────────────── --}}
            <div id="modal-password" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-[#F7D600] flex items-center justify-center shrink-0">
                            <i class="fas fa-shield-alt text-[#2B2E2C]"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Confirmar aprobación</h3>
                            <p class="text-xs text-gray-500" id="modal-subtitulo">Se aprobarán los productos seleccionados</p>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-key text-yellow-500 mr-1"></i>
                            Contraseña maestra
                        </label>
                        <input type="password" id="input-master-password"
                               placeholder="Ingresa la contraseña maestra"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-[#F7D600] focus:border-[#F7D600] outline-none" />
                        <p id="error-password" class="text-xs text-red-600 mt-1.5 hidden">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Contraseña incorrecta. Inténtalo de nuevo.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" id="modal-cancelar"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                            Cancelar
                        </button>
                        <button type="button" id="modal-confirmar"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-[#2B2E2C] bg-[#F7D600] hover:bg-[#e8c900] rounded-xl transition-colors">
                            <i class="fas fa-check mr-1"></i>Aprobar
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Tabla ────────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="p-3 w-10">
                                <input type="checkbox" id="chk-todos"
                                       class="w-4 h-4 rounded border-gray-300 text-[#2B2E2C] focus:ring-[#F7D600]" />
                            </th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Cód. Fábrica</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Marca</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Variantes</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Creado</th>
                            <th class="p-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($productos as $producto)

                        {{-- ── Fila principal ── --}}
                        <tr class="hover:bg-[#2B2E2C]/10/30 transition-colors cursor-pointer fila-producto"
                            data-id="{{ $producto->id }}"
                            data-detalle="detalle-{{ $producto->id }}">
                            <td class="p-3" onclick="event.stopPropagation()">
                                <input type="checkbox"
                                       class="chk-producto w-4 h-4 rounded border-gray-300 text-[#2B2E2C] focus:ring-[#F7D600]"
                                       value="{{ $producto->id }}" />
                            </td>
                            <td class="p-3 font-mono text-xs text-gray-500">{{ $producto->codigo_fabrica ?? '—' }}</td>
                            <td class="p-3">
                                <p class="font-medium text-gray-800 leading-tight">{{ $producto->nombre }}</p>
                                @if($producto->nombre_kyrios)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $producto->nombre_kyrios }}</p>
                                @endif
                            </td>
                            <td class="p-3 text-gray-600">{{ $producto->marca?->nombre ?? '—' }}</td>
                            <td class="p-3">
                                @if($producto->tipoProducto)
                                    <span class="pill bg-[#2B2E2C]/10 text-[#2B2E2C]">{{ $producto->tipoProducto->nombre }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="p-3 text-center text-gray-500">
                                {{ $producto->variantes->count() ?: '—' }}
                            </td>
                            <td class="p-3 text-gray-400 text-xs whitespace-nowrap">
                                {{ $producto->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="p-3 text-gray-400">
                                <i class="fas fa-chevron-right text-xs toggle-icon transition-transform duration-200"></i>
                            </td>
                        </tr>

                        {{-- ── Fila detalle (expandible) ── --}}
                        <tr id="detalle-{{ $producto->id }}" class="detalle-row bg-[#2B2E2C]/10/20">
                            <td colspan="8" class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                                    {{-- Datos básicos --}}
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Datos básicos</p>
                                        <div class="space-y-1.5">
                                            @if($producto->tipoLuminaria)
                                            <div class="spec-item">
                                                <label>Tipo luminaria</label>
                                                <span>{{ $producto->tipoLuminaria->nombre }}</span>
                                            </div>
                                            @endif
                                            @if($producto->categoria)
                                            <div class="spec-item">
                                                <label>Categoría</label>
                                                <span>{{ $producto->categoria->nombre }}</span>
                                            </div>
                                            @endif
                                            @if($producto->linea)
                                            <div class="spec-item">
                                                <label>Línea</label>
                                                <span>{{ $producto->linea }}</span>
                                            </div>
                                            @endif
                                            @if($producto->procedencia)
                                            <div class="spec-item">
                                                <label>Procedencia</label>
                                                <span>{{ $producto->procedencia }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Especificaciones --}}
                                    @if($producto->especificacion)
                                    @php $esp = $producto->especificacion; @endphp
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Especificaciones</p>
                                        <div class="space-y-1.5">
                                            @foreach([
                                                'Tipo fuente'    => $esp->tipo_fuente,
                                                'Nivel potencia' => $esp->nivel_potencia,
                                                'Potencia'       => $esp->potencia ? $esp->potencia.'W' : null,
                                                'Voltaje'        => $esp->voltaje,
                                                'IP'             => $esp->ip,
                                                'IK'             => $esp->ik,
                                                'Ángulo'         => $esp->angulo_apertura,
                                                'Driver'         => $esp->driver,
                                                'Socket'         => $esp->socket,
                                                'Nº lámparas'   => $esp->numero_lamparas,
                                                'Vida útil'      => $esp->vida_util_horas ? number_format($esp->vida_util_horas).'h' : null,
                                                'Protocolo'      => $esp->protocolo_regulacion,
                                                'Regulable'      => $esp->regulable ? 'Sí' : null,
                                            ] as $label => $val)
                                                @if($val)
                                                <div class="spec-item">
                                                    <label>{{ $label }}</label>
                                                    <span>{{ $val }}</span>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Fotometría --}}
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Fotometría</p>
                                        <div class="space-y-1.5">
                                            @foreach([
                                                'Tonalidad'          => $esp->tonalidad_luz,
                                                'Temp. color'        => $esp->temperatura_color,
                                                'CRI'                => $esp->cri,
                                                'Lúm. nominales'    => $esp->nominal_lumenes ? number_format($esp->nominal_lumenes).' lm' : null,
                                                'Lúm. reales'       => $esp->real_lumenes    ? number_format($esp->real_lumenes).' lm'    : null,
                                                'Eficacia luminosa'  => $esp->eficacia_luminosa ? $esp->eficacia_luminosa.' lm/W' : null,
                                            ] as $label => $val)
                                                @if($val)
                                                <div class="spec-item">
                                                    <label>{{ $label }}</label>
                                                    <span>{{ $val }}</span>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Dimensiones --}}
                                    @if($producto->dimensiones)
                                    @php $dim = $producto->dimensiones; @endphp
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Dimensiones (mm)</p>
                                        <div class="space-y-1.5">
                                            @foreach([
                                                'Alto'              => $dim->alto,
                                                'Ancho'             => $dim->ancho,
                                                'Profundidad'       => $dim->profundidad,
                                                'Diámetro'         => $dim->diametro,
                                                'Lado'              => $dim->lado,
                                                'Alto suspendido'   => $dim->alto_suspendido,
                                                'Diám. agujero'    => $dim->diametro_agujero,
                                            ] as $label => $val)
                                                @if($val)
                                                <div class="spec-item">
                                                    <label>{{ $label }}</label>
                                                    <span>{{ $val }} mm</span>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Embalaje --}}
                                    @if($producto->embalaje)
                                    @php $emb = $producto->embalaje; @endphp
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Embalaje</p>
                                        <div class="space-y-1.5">
                                            @foreach([
                                                'Peso'           => $emb->peso ? $emb->peso.' kg' : null,
                                                'Volumen'        => $emb->volumen ? $emb->volumen.' cm³' : null,
                                                'Medida caja'    => $emb->medida_embalaje,
                                                'Uds/caja'       => $emb->cantidad_por_caja,
                                                'Embalado'       => $emb->embalado ? 'Sí' : null,
                                            ] as $label => $val)
                                                @if($val)
                                                <div class="spec-item">
                                                    <label>{{ $label }}</label>
                                                    <span>{{ $val }}</span>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Clasificaciones y Tipos de proyecto --}}
                                    <div>
                                        @if($producto->clasificaciones->isNotEmpty())
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Clasificaciones</p>
                                        <div class="flex flex-wrap gap-1 mb-3">
                                            @foreach($producto->clasificaciones as $clf)
                                                <span class="pill bg-[#2B2E2C]/10 text-[#2B2E2C]">{{ $clf->nombre }}</span>
                                            @endforeach
                                        </div>
                                        @endif

                                        @if($producto->tiposProyecto->isNotEmpty())
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Tipos de proyecto</p>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($producto->tiposProyecto as $tp)
                                                <span class="pill bg-orange-50 text-orange-700">{{ $tp->nombre }}</span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>

                                </div>

                                {{-- Variantes --}}
                                @if($producto->variantes->isNotEmpty())
                                <div class="mt-4 border-t border-blue-100 pt-3">
                                    <p class="text-xs font-bold text-gray-400 uppercase mb-2">Variantes ({{ $producto->variantes->count() }})</p>
                                    <div class="overflow-x-auto">
                                        <table class="text-xs w-full">
                                            <thead>
                                                <tr class="text-gray-400">
                                                    <th class="text-left pb-1 pr-4">SKU</th>
                                                    <th class="text-left pb-1 pr-4">Color</th>
                                                    <th class="text-left pb-1 pr-4">Tamaño</th>
                                                    <th class="text-left pb-1 pr-4">Especificación</th>
                                                    <th class="text-right pb-1">Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($producto->variantes as $v)
                                                <tr>
                                                    <td class="py-1 pr-4 font-mono text-gray-500">{{ $v->sku }}</td>
                                                    <td class="py-1 pr-4">{{ $v->color?->nombre ?? '—' }}</td>
                                                    <td class="py-1 pr-4">{{ $v->tamano ?? '—' }}</td>
                                                    <td class="py-1 pr-4">{{ $v->especificacion ?? '—' }}</td>
                                                    <td class="py-1 text-right">{{ $v->stock_actual }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                {{-- Acciones rápidas --}}
                                <div class="mt-3 flex flex-wrap gap-2 border-t border-blue-100 pt-3">
                                    <a href="{{ route('inventario.productos.edit', $producto) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fas fa-pen"></i> Editar en detalle
                                    </a>
                                    <button type="button"
                                            onclick="abrirModalStock({{ $producto->id }}, {{ $producto->stock_minimo }}, {{ $producto->stock_maximo }})"
                                            class="inline-flex items-center gap-1.5 text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fas fa-boxes"></i> Stock mín/máx
                                    </button>
                                    <button type="button"
                                            onclick="abrirModalUbicacion({{ $producto->id }}, '{{ addslashes($producto->ubicacion ?? '') }}')"
                                            class="inline-flex items-center gap-1.5 text-xs bg-purple-100 hover:bg-purple-200 text-purple-800 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fas fa-map-marker-alt"></i> Ubicación
                                    </button>
                                    <button type="button"
                                            onclick="aprobarUno({{ $producto->id }}, this)"
                                            class="inline-flex items-center gap-1.5 text-xs bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fas fa-check"></i> Aprobar este producto
                                    </button>
                                </div>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-gray-400">
                                <i class="fas fa-check-double text-3xl mb-3 block text-gray-300"></i>
                                No hay productos en borrador
                                @if(request('buscar'))
                                    para "<strong>{{ request('buscar') }}</strong>"
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($productos->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $productos->links() }}
                </div>
                @endif
            </div>

            <p class="text-xs text-gray-400 text-right">
                {{ $productos->total() }} producto(s) en borrador
            </p>
        </div>
    </div>

    <script>
    (() => {
        const chkTodos          = document.getElementById('chk-todos');
        const btnAprobar        = document.getElementById('btn-aprobar-lote');
        const btnEliminar       = document.getElementById('btn-eliminar-lote');
        const btnSelTodo        = document.getElementById('btn-seleccionar-todo');
        const labelSel          = document.getElementById('label-seleccionados');
        const cntSel            = document.getElementById('cnt-seleccionados');
        const alerta            = document.getElementById('alerta-aprobacion');
        const msgAlerta         = document.getElementById('msg-aprobacion');
        const alertaEliminacion = document.getElementById('alerta-eliminacion');
        const msgEliminacion    = document.getElementById('msg-eliminacion');
        const csrf              = document.querySelector('meta[name="csrf-token"]').content;

        // ── Expandir / colapsar fila detalle ────────────────────────────────
        document.querySelectorAll('.fila-producto').forEach(fila => {
            fila.addEventListener('click', () => {
                const detalleId = fila.dataset.detalle;
                const detalleRow = document.getElementById(detalleId);
                const icon = fila.querySelector('.toggle-icon');
                const abierto = detalleRow.classList.toggle('abierto');
                icon.style.transform = abierto ? 'rotate(90deg)' : '';
            });
        });

        // ── Selección ────────────────────────────────────────────────────────
        function getSeleccionados() {
            return [...document.querySelectorAll('.chk-producto:checked')].map(c => c.value);
        }

        function actualizarBotones() {
            const ids = getSeleccionados();
            const n   = ids.length;
            btnAprobar.disabled  = n === 0;
            btnEliminar.disabled = n === 0;
            labelSel.classList.toggle('hidden', n === 0);
            cntSel.textContent = n;
        }

        chkTodos.addEventListener('change', () => {
            document.querySelectorAll('.chk-producto').forEach(c => { c.checked = chkTodos.checked; });
            actualizarBotones();
        });

        document.addEventListener('change', e => {
            if (e.target.classList.contains('chk-producto')) {
                actualizarBotones();
                if (!e.target.checked) chkTodos.checked = false;
            }
        });

        btnSelTodo.addEventListener('click', () => {
            const todos = [...document.querySelectorAll('.chk-producto')];
            const hayDesel = todos.some(c => !c.checked);
            todos.forEach(c => c.checked = hayDesel);
            chkTodos.checked = hayDesel;
            actualizarBotones();
        });

        // ── Modal contraseña maestra ─────────────────────────────────────────
        const modal          = document.getElementById('modal-password');
        const inputPassword  = document.getElementById('input-master-password');
        const errorPassword  = document.getElementById('error-password');
        const modalSubtitulo = document.getElementById('modal-subtitulo');
        const modalConfirmar = document.getElementById('modal-confirmar');
        const modalCancelar  = document.getElementById('modal-cancelar');

        let pendingIds    = [];
        let pendingBtn    = null;
        let pendingLabel  = '';

        function abrirModal(ids, label, btn = null) {
            pendingIds   = ids;
            pendingBtn   = btn;
            pendingLabel = label;
            modalSubtitulo.textContent = label;
            inputPassword.value = '';
            errorPassword.classList.add('hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => inputPassword.focus(), 50);
        }

        function cerrarModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (pendingBtn) {
                pendingBtn.disabled = false;
                pendingBtn.innerHTML = '<i class="fas fa-check"></i> Aprobar este producto';
            }
        }

        modalCancelar.addEventListener('click', cerrarModal);
        modal.addEventListener('click', e => { if (e.target === modal) cerrarModal(); });
        inputPassword.addEventListener('keydown', e => { if (e.key === 'Enter') modalConfirmar.click(); });

        modalConfirmar.addEventListener('click', async () => {
            const password = inputPassword.value.trim();
            if (!password) { errorPassword.textContent = 'Ingresa la contraseña.'; errorPassword.classList.remove('hidden'); return; }

            modalConfirmar.disabled = true;
            modalConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Verificando...';

            const resp = await fetch('{{ route("inventario.importacion.aprobar-lote") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ ids: pendingIds, password }),
            });

            const data = await resp.json();

            modalConfirmar.disabled = false;
            modalConfirmar.innerHTML = '<i class="fas fa-check mr-1"></i>Aprobar';

            if (!data.ok) {
                errorPassword.textContent = data.error ?? 'Contraseña incorrecta.';
                errorPassword.classList.remove('hidden');
                inputPassword.value = '';
                inputPassword.focus();
                return;
            }

            // Éxito
            cerrarModal();
            pendingIds.forEach(id => {
                document.querySelector(`tr[data-id="${id}"]`)?.remove();
                document.getElementById(`detalle-${id}`)?.remove();
            });
            alerta.style.display = 'flex';
            msgAlerta.textContent = `${data.aprobados} producto(s) aprobados correctamente.`;
            setTimeout(() => { alerta.style.display = 'none'; }, 5000);
            chkTodos.checked = false;
            actualizarBotones();

            // Si era botón individual, quitar de DOM
            if (pendingBtn) {
                pendingBtn.closest('tr')?.remove();
            }

            // Botones lote
            btnAprobar.disabled = false;
            btnAprobar.innerHTML = '<i class="fas fa-check mr-1"></i> Aprobar seleccionados';
        });

        // ── Aprobar en lote ──────────────────────────────────────────────────
        btnAprobar.addEventListener('click', () => {
            const ids = getSeleccionados();
            if (!ids.length) return;
            abrirModal(ids, `Se aprobarán ${ids.length} producto(s) seleccionados`);
        });

        // ── Eliminar en lote ─────────────────────────────────────────────────
        const modalEliminar         = document.getElementById('modal-eliminar');
        const modalEliminarSubtitulo = document.getElementById('modal-eliminar-subtitulo');
        const modalEliminarCancelar  = document.getElementById('modal-eliminar-cancelar');
        const modalEliminarConfirmar = document.getElementById('modal-eliminar-confirmar');
        let pendingDeleteIds = [];

        btnEliminar.addEventListener('click', () => {
            pendingDeleteIds = getSeleccionados();
            if (!pendingDeleteIds.length) return;
            modalEliminarSubtitulo.textContent = `Se eliminarán ${pendingDeleteIds.length} producto(s) en borrador`;
            modalEliminar.style.display = 'flex';
        });

        modalEliminarCancelar.addEventListener('click', () => { modalEliminar.style.display = 'none'; });
        modalEliminar.addEventListener('click', e => { if (e.target === modalEliminar) modalEliminar.style.display = 'none'; });

        modalEliminarConfirmar.addEventListener('click', async () => {
            modalEliminarConfirmar.disabled = true;
            modalEliminarConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Eliminando...';

            const resp = await fetch('{{ route("inventario.importacion.eliminar-lote") }}', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ ids: pendingDeleteIds }),
            });
            const data = await resp.json();

            modalEliminarConfirmar.disabled = false;
            modalEliminarConfirmar.innerHTML = '<i class="fas fa-trash mr-1"></i>Eliminar';
            modalEliminar.style.display = 'none';

            if (!data.ok) return;

            pendingDeleteIds.forEach(id => {
                document.querySelector(`tr[data-id="${id}"]`)?.remove();
                document.getElementById(`detalle-${id}`)?.remove();
            });
            chkTodos.checked = false;
            actualizarBotones();

            alertaEliminacion.style.display = 'flex';
            msgEliminacion.textContent = `${data.eliminados} producto(s) eliminado(s) correctamente.`;
            setTimeout(() => { alertaEliminacion.style.display = 'none'; }, 5000);
        });
    })();

    // ── Modal Stock mín/máx ──────────────────────────────────────────────────
    const modalStock        = document.getElementById('modal-stock');
    const inputStockMin     = document.getElementById('input-stock-minimo');
    const inputStockMax     = document.getElementById('input-stock-maximo');
    const errorStock        = document.getElementById('error-stock');
    const csrf              = document.querySelector('meta[name="csrf-token"]').content;
    let stockProductoId     = null;

    window.abrirModalStock = function(id, min, max) {
        stockProductoId     = id;
        inputStockMin.value = min ?? 0;
        inputStockMax.value = max ?? 9999;
        errorStock.classList.add('hidden');
        modalStock.style.display = 'flex';
        setTimeout(() => inputStockMin.focus(), 50);
    };

    document.getElementById('modal-stock-cancelar').addEventListener('click', () => { modalStock.style.display = 'none'; });
    modalStock.addEventListener('click', e => { if (e.target === modalStock) modalStock.style.display = 'none'; });

    document.getElementById('modal-stock-guardar').addEventListener('click', async () => {
        const min = parseInt(inputStockMin.value);
        const max = parseInt(inputStockMax.value);
        if (isNaN(min) || isNaN(max) || min < 0 || max < 1) {
            errorStock.textContent = 'Valores inválidos.'; errorStock.classList.remove('hidden'); return;
        }
        if (max <= min) {
            errorStock.textContent = 'El máximo debe ser mayor que el mínimo.'; errorStock.classList.remove('hidden'); return;
        }
        const btn = document.getElementById('modal-stock-guardar');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Guardando...';

        const resp = await fetch(`/inventario/productos/${stockProductoId}/stock-ubicacion`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ stock_minimo: min, stock_maximo: max }),
        });
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-save mr-1"></i>Guardar';
        if ((await resp.json()).ok) {
            modalStock.style.display = 'none';
            errorStock.classList.add('hidden');
        }
    });

    // ── Modal Ubicación ───────────────────────────────────────────────────────
    const modalUbicacion    = document.getElementById('modal-ubicacion');
    const inputUbicacion    = document.getElementById('input-ubicacion');
    let ubicacionProductoId = null;

    window.abrirModalUbicacion = function(id, ubicacion) {
        ubicacionProductoId  = id;
        inputUbicacion.value = ubicacion ?? '';
        modalUbicacion.style.display = 'flex';
        setTimeout(() => inputUbicacion.focus(), 50);
    };

    document.getElementById('modal-ubicacion-cancelar').addEventListener('click', () => { modalUbicacion.style.display = 'none'; });
    modalUbicacion.addEventListener('click', e => { if (e.target === modalUbicacion) modalUbicacion.style.display = 'none'; });

    document.getElementById('modal-ubicacion-guardar').addEventListener('click', async () => {
        const btn = document.getElementById('modal-ubicacion-guardar');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Guardando...';

        const resp = await fetch(`/inventario/productos/${ubicacionProductoId}/stock-ubicacion`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ ubicacion: inputUbicacion.value.trim() }),
        });
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-save mr-1"></i>Guardar';
        if ((await resp.json()).ok) modalUbicacion.style.display = 'none';
    });

    // ── Aprobar producto individual ──────────────────────────────────────────
    function aprobarUno(id, btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        // Reusar modal del scope principal
        const modal         = document.getElementById('modal-password');
        const inputPassword = document.getElementById('input-master-password');
        const errorPassword = document.getElementById('error-password');
        const modalSubtitulo = document.getElementById('modal-subtitulo');
        const modalConfirmar = document.getElementById('modal-confirmar');
        const modalCancelar  = document.getElementById('modal-cancelar');

        modalSubtitulo.textContent = 'Se aprobará 1 producto';
        inputPassword.value = '';
        errorPassword.classList.add('hidden');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => inputPassword.focus(), 50);

        const onCancelar = () => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Aprobar este producto';
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modalConfirmar.replaceWith(modalConfirmar.cloneNode(true));
            modalCancelar.replaceWith(modalCancelar.cloneNode(true));
        };

        const newCancelar = document.getElementById('modal-cancelar');
        newCancelar.addEventListener('click', onCancelar, { once: true });
        modal.addEventListener('click', e => { if (e.target === modal) onCancelar(); }, { once: true });

        document.getElementById('modal-confirmar').addEventListener('click', async () => {
            const password = inputPassword.value.trim();
            if (!password) { errorPassword.textContent = 'Ingresa la contraseña.'; errorPassword.classList.remove('hidden'); return; }

            const resp = await fetch('{{ route("inventario.importacion.aprobar-lote") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ ids: [id], password }),
            });
            const data = await resp.json();

            if (!data.ok) {
                errorPassword.textContent = data.error ?? 'Contraseña incorrecta.';
                errorPassword.classList.remove('hidden');
                inputPassword.value = '';
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.querySelector(`tr[data-id="${id}"]`)?.remove();
            document.getElementById(`detalle-${id}`)?.remove();

            const alerta   = document.getElementById('alerta-aprobacion');
            const msgAlerta = document.getElementById('msg-aprobacion');
            alerta.style.display = 'flex';
            msgAlerta.textContent = 'Producto aprobado correctamente.';
            setTimeout(() => { alerta.style.display = 'none'; }, 5000);
        }, { once: true });
    }
    </script>
</body>
</html>
