<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Productos</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Importar Productos" subtitle="Carga masiva de productos desde Excel o CSV" />

        <div class="max-w-3xl mx-auto space-y-6">

            {{-- Mensajes de error de validación --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-300 rounded-lg p-4">
                    <p class="text-sm font-semibold text-red-700 mb-2">
                        <i class="fas fa-exclamation-circle mr-1"></i>Errores:
                    </p>
                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Resultado de importación --}}
            @if(session('importacion'))
                @php $res = session('importacion'); @endphp
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-green-50 border-b border-green-200 px-6 py-4 flex items-center gap-2">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        <h3 class="font-semibold text-green-800">Importación completada</h3>
                    </div>
                    <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ $res['creados'] }}</div>
                            <div class="text-xs text-gray-500 mt-1">Productos creados</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $res['actualizados'] }}</div>
                            <div class="text-xs text-gray-500 mt-1">Actualizados</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600">{{ $res['variantes'] }}</div>
                            <div class="text-xs text-gray-500 mt-1">Variantes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-amber-600">{{ $res['componentes'] }}</div>
                            <div class="text-xs text-gray-500 mt-1">Componentes BOM</div>
                        </div>
                    </div>
                    @if(!empty($res['errores']))
                        <div class="border-t border-red-100 bg-red-50 px-6 py-4">
                            <p class="text-sm font-semibold text-red-700 mb-2">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ count($res['errores']) }} error(es) durante la importación:
                            </p>
                            <ul class="list-disc list-inside text-xs text-red-600 space-y-0.5 max-h-48 overflow-y-auto">
                                @foreach($res['errores'] as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="border-t border-gray-100 px-6 py-3 bg-gray-50 flex justify-end">
                        <a href="{{ route('inventario.productos.index') }}"
                           class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">
                            <i class="fas fa-list mr-2"></i>Ver productos importados
                        </a>
                    </div>
                </div>
            @endif

            {{-- Formulario de carga --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100" style="background-color:#2B2E2C;">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-excel text-xl" style="color:#F7D600;"></i>
                        <h2 class="font-semibold text-white">Cargar archivo</h2>
                    </div>
                </div>

                <form action="{{ route('inventario.productos.importar.store') }}" method="POST"
                      enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf

                    <div x-data="{ nombre: '' }"
                         class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-400 transition-colors">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-3 block"></i>
                        <p class="text-sm text-gray-600 mb-2">
                            Arrastra tu archivo o <label for="archivo" class="text-indigo-600 font-medium cursor-pointer hover:underline">haz clic aquí</label>
                        </p>
                        <p class="text-xs text-gray-400">Excel (.xlsx, .xls) o CSV (.csv) — máx. 10 MB</p>
                        <input type="file" id="archivo" name="archivo"
                               accept=".csv,.xlsx,.xls,.txt"
                               @change="nombre = $event.target.files[0]?.name ?? ''"
                               class="hidden" required>
                        <p x-show="nombre" x-text="'Archivo seleccionado: ' + nombre"
                           class="mt-2 text-sm text-indigo-700 font-medium"></p>
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('inventario.productos.importar.plantilla') }}"
                           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-50">
                            <i class="fas fa-download text-green-600"></i>
                            Descargar plantilla CSV
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 text-gray-900 rounded-lg font-semibold text-sm shadow-sm hover:opacity-90"
                                style="background-color:#F7D600;">
                            <i class="fas fa-upload mr-2"></i>Importar productos
                        </button>
                    </div>
                </form>
            </div>

            {{-- Instrucciones --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        Instrucciones para el CSV
                    </h3>
                </div>
                <div class="p-6 text-sm text-gray-600 space-y-4">
                    <p>El CSV debe tener <strong>3 secciones separadas</strong> (o puedes usar 3 hojas en Excel):</p>

                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Sección PRODUCTOS <span class="text-red-500">*</span></p>
                        <code class="block bg-gray-100 p-2 rounded text-xs text-gray-700 overflow-x-auto">
                            === HOJA: PRODUCTOS ===<br>
                            codigo,nombre,tipo_sistema,categoria,marca,tipo_kyrios,tipo_luminaria,potencia_w,...
                        </code>
                        <ul class="mt-2 list-disc list-inside text-xs text-gray-500 space-y-0.5">
                            <li><code>tipo_sistema</code>: simple / compuesto / componente</li>
                            <li><code>tipo_kyrios</code>: LU / LA / CL / SM (código del tipo de producto)</li>
                            <li><code>tipo_luminaria</code>: ET / EM / EP / etc.</li>
                            <li>Si <code>codigo</code> ya existe, el producto se actualiza</li>
                        </ul>
                    </div>

                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Sección VARIANTES <span class="text-gray-400">(opcional)</span></p>
                        <code class="block bg-gray-100 p-2 rounded text-xs text-gray-700">
                            === HOJA: VARIANTES ===<br>
                            codigo_padre,especificacion,sobreprecio,stock_inicial
                        </code>
                    </div>

                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Sección COMPONENTES <span class="text-gray-400">(solo para kits)</span></p>
                        <code class="block bg-gray-100 p-2 rounded text-xs text-gray-700">
                            === HOJA: COMPONENTES (para kits) ===<br>
                            codigo_padre,codigo_hijo,cantidad,unidad,es_opcional,orden
                        </code>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
                        <i class="fas fa-lightbulb mr-1"></i>
                        <strong>Tip:</strong> Descarga la plantilla CSV para ver el formato exacto con datos de ejemplo.
                        Las categorías y marcas que no existan se crean automáticamente.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
