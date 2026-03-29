<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $producto->nombre }} - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <!-- Header con navegación -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detalle del Producto</h1>
                    <p class="text-sm text-gray-600 mt-1">Información completa de {{ $producto->nombre }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('inventario.productos.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                    @can('editar_producto')
                        <a href="{{ route('inventario.productos.edit', $producto) }}" class="px-4 py-2 bg-[#2B2E2C] text-white rounded-lg hover:bg-[#2B2E2C] text-sm">
                            <i class="fas fa-edit mr-2"></i>Editar
                        </a>
                    @endcan

                    {{-- Enviar a revisión (Almacenero/creador) --}}
                    @if(in_array($producto->estado_aprobacion, ['borrador', 'rechazado']))
                        <form method="POST" action="{{ route('inventario.productos.enviar-aprobacion', $producto) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm">
                                <i class="fas fa-paper-plane mr-2"></i>Enviar a Revisión
                            </button>
                        </form>
                    @endif

                    {{-- Aprobar (Admin con permiso) --}}
                    @can('aprobar_producto')
                        @if($producto->estado_aprobacion === 'pendiente_aprobacion')
                            <form method="POST" action="{{ route('inventario.productos.aprobar', $producto) }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                    <i class="fas fa-check mr-2"></i>Aprobar
                                </button>
                            </form>

                            {{-- Rechazar con modal --}}
                            <div x-data="{ abierto: false }">
                                <button type="button" @click="abierto = true"
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                                    <i class="fas fa-times mr-2"></i>Rechazar
                                </button>
                                <div x-show="abierto" x-cloak
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                                        <h3 class="text-base font-bold text-gray-900 mb-3">
                                            <i class="fas fa-times-circle text-red-500 mr-2"></i>Rechazar Producto
                                        </h3>
                                        <form method="POST" action="{{ route('inventario.productos.rechazar', $producto) }}">
                                            @csrf
                                            <div class="mb-4">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de rechazo</label>
                                                <textarea name="motivo_rechazo" rows="3" required
                                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-400"
                                                          placeholder="Explica por qué se rechaza el producto..."></textarea>
                                            </div>
                                            <div class="flex gap-3 justify-end">
                                                <button type="button" @click="abierto = false"
                                                        class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">
                                                    Cancelar
                                                </button>
                                                <button type="submit"
                                                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                                                    Confirmar Rechazo
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endcan
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-xl"></i>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <!-- Grid principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna izquierda: Imagen e información básica -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Imagen -->
                    <div class="p-6 flex justify-center bg-gray-50 border-b">
                        @if($producto->imagen)
                            <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="max-h-64 object-contain">
                        @else
                            <div class="h-48 w-48 bg-gray-200 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-6xl text-gray-400"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Información básica -->
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $producto->nombre }}</h2>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-500">Código:</span>
                                <span class="text-sm text-gray-900 font-mono">{{ $producto->codigo }}</span>
                            </div>
                            
                            @if($producto->codigo_barras)
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-500">Código Barras:</span>
                                <span class="text-sm text-gray-900 font-mono">{{ $producto->codigo_barras }}</span>
                            </div>
                            @endif
                            
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-500">Tipo Inventario:</span>
                                <span class="text-sm">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                        <i class="fas fa-boxes mr-1"></i> Cantidad
                                    </span>
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-500">Estado:</span>
                                <span class="text-sm">
                                    @if($producto->estado === 'activo')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Activo</span>
                                    @elseif($producto->estado === 'inactivo')
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">Inactivo</span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Descontinuado</span>
                                    @endif
                                </span>
                            </div>

                            {{-- Estado de Aprobación --}}
                            @if($producto->estado_aprobacion)
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Aprobación:</span>
                                @php
                                    $aprobBadge = match($producto->estado_aprobacion) {
                                        'aprobado'             => ['bg-green-100 text-green-800', 'fa-check-circle'],
                                        'pendiente_aprobacion' => ['bg-yellow-100 text-yellow-800', 'fa-clock'],
                                        'rechazado'            => ['bg-red-100 text-red-800', 'fa-times-circle'],
                                        default                => ['bg-gray-100 text-gray-600', 'fa-pencil-alt'],
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs {{ $aprobBadge[0] }}">
                                    <i class="fas {{ $aprobBadge[1] }} mr-1"></i>
                                    {{ \App\Models\Producto::ESTADOS_APROBACION[$producto->estado_aprobacion] ?? $producto->estado_aprobacion }}
                                </span>
                            </div>
                            @if($producto->estado_aprobacion === 'rechazado' && $producto->motivo_rechazo)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-700">
                                <i class="fas fa-comment-alt mr-1"></i>
                                <strong>Motivo:</strong> {{ $producto->motivo_rechazo }}
                            </div>
                            @endif
                            @endif
                            
                            <div class="pt-3 border-t">
                                <span class="text-sm font-medium text-gray-500 block mb-2">Descripción:</span>
                                <p class="text-sm text-gray-700">{{ $producto->descripcion ?: 'Sin descripción' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Detalles y movimientos -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Tarjeta de clasificación -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#2B2E2C] px-6 py-3">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-tags mr-2"></i>
                            Clasificación
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Categoría</p>
                                <p class="font-medium">{{ $producto->categoria->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Marca</p>
                                <p class="font-medium">{{ $producto->marca->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Modelo</p>
                                <p class="font-medium">{{ $producto->modelo->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Color</p>
                                <p class="font-medium">{{ $producto->color->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Unidad Medida</p>
                                <p class="font-medium">{{ $producto->unidadMedida->nombre ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de stock -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-green-600 px-6 py-3">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-boxes mr-2"></i>
                            Control de Stock
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Stock Actual</p>
                                <p class="text-2xl font-bold {{ $producto->estado_stock === 'bajo' ? 'text-yellow-600' : ($producto->estado_stock === 'sin_stock' ? 'text-red-600' : 'text-green-600') }}">
                                    {{ $producto->stock_actual }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Stock Mínimo</p>
                                <p class="text-xl font-semibold">{{ $producto->stock_minimo }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Stock Máximo</p>
                                <p class="text-xl font-semibold">{{ $producto->stock_maximo }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Ubicación</p>
                                <p class="font-medium">{{ $producto->ubicacion ?: 'No definida' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══ FICHA TÉCNICA LUMINARIA ═══ --}}
                @php
                    $esp   = $producto->especificacion;
                    $dim   = $producto->dimensiones;
                    $mat   = $producto->materiales;
                    $clas  = $producto->clasificacion;
                    $tieneFicha = $esp || $dim || $mat || $clas
                        || $producto->codigo_kyrios || $producto->codigo_fabrica;
                @endphp
                @if($tieneFicha)
                <div class="bg-white rounded-lg shadow-md overflow-hidden" x-data="{}">
                    <div class="bg-yellow-500 px-6 py-3">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-lightbulb mr-2"></i>
                            Ficha Técnica Luminaria
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">

                        {{-- Códigos Kyrios --}}
                        @if($producto->codigo_kyrios || $producto->codigo_fabrica || $producto->procedencia || $producto->linea)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                                <i class="fas fa-tag text-yellow-500"></i> Identificación Kyrios
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                @foreach([
                                    ['Código Kyrios',   $producto->codigo_kyrios],
                                    ['Código Fábrica',  $producto->codigo_fabrica],
                                    ['Procedencia',     $producto->procedencia],
                                    ['Línea',           $producto->linea],
                                ] as [$label, $val])
                                    @if($val)
                                    <div>
                                        <p class="text-xs text-gray-500">{{ $label }}</p>
                                        <p class="font-medium text-gray-900">{{ $val }}</p>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            @if($producto->ficha_tecnica_url)
                            <div class="mt-2">
                                <a href="{{ $producto->ficha_tecnica_url }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-sm text-[#2B2E2C] hover:underline">
                                    <i class="fas fa-file-pdf"></i> Ver ficha técnica PDF
                                </a>
                            </div>
                            @endif
                            @if($producto->observaciones)
                            <p class="mt-2 text-sm text-gray-500 italic">{{ $producto->observaciones }}</p>
                            @endif
                        </div>
                        @endif

                        {{-- Especificaciones eléctricas --}}
                        @if($esp)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                                <i class="fas fa-bolt text-yellow-500"></i> Especificaciones Eléctricas
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                @foreach([
                                    ['Potencia',           $esp->potencia,             ''],
                                    ['Lúmenes',            $esp->lumenes,              ''],
                                    ['Voltaje',            $esp->voltaje,              ''],
                                    ['Temp. de Color',     $esp->temperatura_color,    ''],
                                    ['CRI',                $esp->cri,                  ''],
                                    ['IP',                 $esp->ip,                   ''],
                                    ['IK',                 $esp->ik,                   ''],
                                    ['Ángulo apertura',    $esp->angulo_apertura,      ''],
                                    ['Driver',             $esp->driver,               ''],
                                    ['Socket',             $esp->socket,               ''],
                                    ['Nº Lámparas',        $esp->numero_lamparas,      ''],
                                    ['Prot. Regulación',   $esp->protocolo_regulacion, ''],
                                ] as [$label, $val, $_])
                                    @if($val !== null && $val !== '')
                                    <div>
                                        <p class="text-xs text-gray-500">{{ $label }}</p>
                                        <p class="font-medium text-gray-900">{{ $val }}</p>
                                    </div>
                                    @endif
                                @endforeach
                                @if($esp->regulable)
                                <div>
                                    <p class="text-xs text-gray-500">Regulable</p>
                                    <p class="font-medium text-green-700"><i class="fas fa-check-circle mr-1"></i>Sí</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Dimensiones --}}
                        @if($dim)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                                <i class="fas fa-ruler-combined text-[#2B2E2C]"></i> Dimensiones (mm)
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                @foreach([
                                    ['Alto',              $dim->alto],
                                    ['Ancho',             $dim->ancho],
                                    ['Diámetro',          $dim->diametro],
                                    ['Lado',              $dim->lado],
                                    ['Profundidad',       $dim->profundidad],
                                    ['Alto suspendido',   $dim->alto_suspendido],
                                    ['Diám. agujero',     $dim->diametro_agujero],
                                ] as [$label, $val])
                                    @if($val !== null && $val > 0)
                                    <div>
                                        <p class="text-xs text-gray-500">{{ $label }}</p>
                                        <p class="font-medium text-gray-900">{{ $val }} mm</p>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Materiales --}}
                        @if($mat && ($mat->material_1 || $mat->material_2 || $mat->color_acabado_1 || $mat->color_acabado_2))
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                                <i class="fas fa-layer-group text-green-600"></i> Materiales y Acabados
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                @foreach([
                                    ['Material principal',  $mat->material_1],
                                    ['Material secundario', $mat->material_2],
                                    ['Acabado / Color 1',   $mat->color_acabado_1],
                                    ['Acabado / Color 2',   $mat->color_acabado_2],
                                ] as [$label, $val])
                                    @if($val)
                                    <div>
                                        <p class="text-xs text-gray-500">{{ $label }}</p>
                                        <p class="font-medium text-gray-900">{{ $val }}</p>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Clasificación --}}
                        @if($clas)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                                <i class="fas fa-tags text-[#2B2E2C]"></i> Clasificación de Uso
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                @php
                                    $instalaciones = is_array($clas->tipo_instalacion) ? $clas->tipo_instalacion : [];
                                    $estilos       = is_array($clas->estilo) ? $clas->estilo : [];
                                @endphp
                                @if(!empty($instalaciones))
                                <div>
                                    <p class="text-xs text-gray-500">Instalación</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($instalaciones as $inst)
                                            <span class="px-2 py-0.5 bg-[#2B2E2C]/10 text-[#2B2E2C] rounded text-xs">
                                                {{ ucfirst($inst) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @if(!empty($estilos))
                                <div>
                                    <p class="text-xs text-gray-500">Estilo</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($estilos as $est)
                                            <span class="px-2 py-0.5 bg-[#2B2E2C]/10 text-[#2B2E2C] rounded text-xs">{{ $est }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @if($producto->tiposProyecto->isNotEmpty())
                                <div class="md:col-span-2">
                                    <p class="text-xs text-gray-500">Tipo de Proyecto</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($producto->tiposProyecto as $tp)
                                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded text-xs">{{ $tp->nombre }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
                @endif

                <!-- Variantes -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden" x-data>
                    <div class="bg-[#2B2E2C] px-6 py-3 flex items-center justify-between">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-layer-group mr-2"></i>Variantes del Producto
                        </h3>
                        <span class="text-white/60 text-sm">{{ $producto->variantes->count() }} variante(s)</span>
                    </div>
                    <div class="p-6">

                        {{-- Tabla de variantes existentes --}}
                        @if($producto->variantes->count() > 0)
                        <div class="mb-5 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-xs text-gray-500 border-b border-gray-100">
                                        <th class="text-left pb-2 font-medium">Nombre / Color</th>
                                        <th class="text-left pb-2 font-medium">Especificación</th>
                                        <th class="text-right pb-2 font-medium">Sobreprecio</th>
                                        <th class="text-right pb-2 font-medium">Stock</th>
                                        <th class="text-center pb-2 font-medium">Estado</th>
                                        @can('editar_producto')<th class="pb-2 w-20"></th>@endcan
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($producto->variantes as $variante)
                                    <tr x-data="{ editando: false }">
                                        <td class="py-2">
                                            <div class="flex items-center gap-2">
                                                @if($variante->color)
                                                <div class="w-4 h-4 rounded-full border border-gray-200 shrink-0"
                                                     style="background-color:{{ $variante->color->hex ?? '#e5e7eb' }}"></div>
                                                @endif
                                                <span class="font-medium text-gray-800">
                                                    {{ $variante->nombre ?: ($variante->color?->nombre ?? 'Sin nombre') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-2 text-gray-500">{{ $variante->especificacion ?: '—' }}</td>
                                        <td class="py-2 text-right text-gray-700">
                                            {{ $variante->sobreprecio > 0 ? '+S/ '.number_format($variante->sobreprecio,2) : '—' }}
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
                                        @can('editar_producto')
                                        <td class="py-2 text-right">
                                            <button type="button" @click="editando = !editando"
                                                    class="text-[#2B2E2C] hover:text-[#2B2E2C] mr-2 transition-colors">
                                                <i class="fas fa-pencil-alt text-xs"></i>
                                            </button>
                                            <form method="POST" action="{{ route('inventario.productos.variantes.destroy', $variante) }}"
                                                  class="inline" onsubmit="return confirm('¿Desactivar esta variante?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-600 transition-colors">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </td>
                                        @endcan
                                    </tr>
                                    {{-- Fila edición inline --}}
                                    @can('editar_producto')
                                    <tr x-show="editando" x-cloak>
                                        <td colspan="6" class="py-3 px-2">
                                            <form method="POST" action="{{ route('inventario.productos.variantes.update', $variante) }}"
                                                  class="bg-[#2B2E2C]/10 border border-gray-200 rounded-xl p-4">
                                                @csrf @method('PUT')
                                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                                                        <input type="text" name="nombre" value="{{ $variante->nombre }}"
                                                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Especificación</label>
                                                        <input type="text" name="especificacion" value="{{ $variante->especificacion }}"
                                                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
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
                                                            <option value="activo" {{ $variante->estado === 'activo' ? 'selected' : '' }}>Activo</option>
                                                            <option value="inactivo" {{ $variante->estado === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                                        </select>
                                                    </div>
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
                                    @endcan
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-sm text-gray-400 mb-4">Este producto no tiene variantes aún.</p>
                        @endif

                        {{-- Formulario agregar variante --}}
                        @can('editar_producto')
                        <div x-data="{ abierto: false }">
                            <button type="button" @click="abierto = !abierto"
                                    class="text-sm text-[#2B2E2C] hover:text-[#2B2E2C] font-medium">
                                <i class="fas fa-plus mr-1"></i>
                                <span x-text="abierto ? 'Cancelar' : 'Agregar variante'"></span>
                            </button>
                            <div x-show="abierto" x-cloak class="mt-4">
                                <form method="POST" action="{{ route('inventario.productos.variantes.store', $producto) }}"
                                      class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                                    @csrf
                                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Nombre variante</label>
                                            <input type="text" name="nombre" placeholder="Ej: Versión Cálida"
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
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Especificación</label>
                                            <input type="text" name="capacidad" placeholder="Ej: 3000K, 18W"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Sobreprecio S/</label>
                                            <input type="number" name="sobreprecio" min="0" step="0.01" placeholder="0.00"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                        </div>
                                    </div>
                                    <button type="submit"
                                            class="px-4 py-2 bg-[#F7D600] text-[#2B2E2C] text-sm font-medium rounded-lg hover:bg-[#e8c900]">
                                        <i class="fas fa-plus mr-1"></i>Agregar Variante
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endcan

                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-700 px-6 py-3">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-bolt mr-2"></i>
                            Acciones Rápidas
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('inventario.productos.codigos-barras', $producto) }}"
                               class="px-4 py-2 bg-[#F7D600] text-[#2B2E2C] rounded-lg hover:bg-[#e8c900]">
                                <i class="fas fa-barcode mr-2"></i>
                                Códigos de Barras
                            </a>

                            @if($producto->tipo_inventario === 'cantidad')
                                <a href="{{ route('inventario.movimientos.create', ['producto_id' => $producto->id]) }}"
                                   class="px-4 py-2 bg-[#F7D600] text-[#2B2E2C] rounded-lg hover:bg-[#e8c900]">
                                    <i class="fas fa-exchange-alt mr-2"></i>
                                    Movimiento de Stock
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Últimos movimientos -->
                @if($producto->movimientos && $producto->movimientos->count() > 0)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-800 px-6 py-3">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-history mr-2"></i>
                            Últimos Movimientos
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($producto->movimientos as $movimiento)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4">
                                        @if($movimiento->tipo_movimiento === 'ingreso')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Ingreso</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Salida</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm {{ $movimiento->tipo_movimiento === 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $movimiento->tipo_movimiento === 'ingreso' ? '+' : '-' }}{{ $movimiento->cantidad }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $movimiento->motivo }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($producto->movimientos->count() >= 10)
                    <div class="px-6 py-3 bg-gray-50 border-t">
                        <a href="#" class="text-sm text-[#2B2E2C] hover:text-[#2B2E2C]">Ver todos los movimientos →</a>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Información de costos (solo administradores) -->
                @if(auth()->user()->role->nombre === 'Administrador')
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-yellow-600 px-6 py-3">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-chart-line mr-2"></i>
                            Información de Costos
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Costo Promedio</p>
                                <p class="text-lg font-semibold">S/ {{ number_format($producto->costo_promedio, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Último Costo Compra</p>
                                <p class="text-lg font-semibold">S/ {{ number_format($producto->ultimo_costo_compra, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Última Compra</p>
                                <p class="text-lg font-semibold">{{ $producto->fecha_ultima_compra ? $producto->fecha_ultima_compra->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>