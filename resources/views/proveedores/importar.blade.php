<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Proveedores</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8" x-data="importarProveedores()">
    <x-header title="Importar Proveedores" subtitle="Carga masiva desde archivo Excel" />

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Instrucciones + Plantilla --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="bg-[#2B2E2C] px-6 py-3">
                <h3 class="text-sm font-bold text-white"><i class="fas fa-info-circle mr-2"></i>Instrucciones</h3>
            </div>
            <div class="p-6">
                <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside mb-5">
                    <li>Descarga la plantilla Excel con el botón de abajo.</li>
                    <li>Completa los datos en la hoja <strong>PROVEEDORES</strong>. La fila 1 son los títulos, la fila 2 son las instrucciones — no las modifiques.</li>
                    <li>Consulta la hoja <strong>CATEGORIAS_REFERENCIA</strong> para los valores permitidos.</li>
                    <li>Sube el archivo completado. Los proveedores existentes (misma razón social + país/distrito) se actualizarán.</li>
                </ol>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('proveedores.importar.plantilla') }}"
                       class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition-colors">
                        <i class="fas fa-file-excel"></i>Descargar Plantilla Excel
                    </a>
                    <a href="{{ route('proveedores.index') }}"
                       class="inline-flex items-center gap-2 border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium px-5 py-2.5 rounded-lg text-sm transition-colors">
                        <i class="fas fa-arrow-left"></i>Volver a Proveedores
                    </a>
                </div>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Zona de carga --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="bg-[#2B2E2C] px-6 py-3">
                <h3 class="text-sm font-bold text-white"><i class="fas fa-upload mr-2"></i>Subir Archivo</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('proveedores.importar.store') }}" method="POST" enctype="multipart/form-data"
                      @submit="procesando = true">
                    @csrf

                    {{-- Drop zone --}}
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-10 text-center cursor-pointer transition-colors hover:border-[#2B2E2C] hover:bg-gray-50"
                         @dragover.prevent="dragging = true"
                         @dragleave.prevent="dragging = false"
                         @drop.prevent="handleDrop($event)"
                         :class="dragging ? 'border-[#2B2E2C] bg-gray-50' : ''"
                         @click="$refs.fileInput.click()">

                        <template x-if="!archivo">
                            <div>
                                <i class="fas fa-file-excel text-5xl text-gray-300 mb-3 block"></i>
                                <p class="text-gray-600 font-medium">Arrastra tu archivo aquí o haz clic para seleccionar</p>
                                <p class="text-xs text-gray-400 mt-1">Solo archivos .xlsx o .xls — máximo 10 MB</p>
                            </div>
                        </template>

                        <template x-if="archivo">
                            <div>
                                <i class="fas fa-file-excel text-5xl text-green-500 mb-3 block"></i>
                                <p class="text-gray-800 font-semibold" x-text="archivo.name"></p>
                                <p class="text-xs text-gray-400 mt-1" x-text="formatSize(archivo.size)"></p>
                                <button type="button" @click.stop="archivo = null; $refs.fileInput.value = ''"
                                        class="mt-2 text-xs text-red-500 hover:text-red-700 underline">
                                    Quitar archivo
                                </button>
                            </div>
                        </template>
                    </div>

                    <input type="file" name="archivo" accept=".xlsx,.xls" x-ref="fileInput" class="hidden"
                           @change="archivo = $event.target.files[0]">

                    @error('archivo')
                        <p class="text-red-500 text-xs mt-2"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}</p>
                    @enderror

                    {{-- Progress bar (visible durante envío) --}}
                    <div x-show="procesando" class="mt-4">
                        <div class="flex items-center gap-2 text-sm text-gray-600 mb-1">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Procesando archivo...</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-[#2B2E2C] h-2 rounded-full animate-pulse" style="width: 80%"></div>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="submit"
                                :disabled="!archivo || procesando"
                                :class="(!archivo || procesando) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#3A3E3B]'"
                                class="bg-[#2B2E2C] text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors inline-flex items-center gap-2">
                            <i class="fas fa-upload"></i>
                            <span x-text="procesando ? 'Procesando...' : 'Importar Proveedores'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Resultados --}}
        @if(isset($resultados))
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <h3 class="text-base font-semibold text-gray-800">
                        <i class="fas fa-clipboard-list mr-2 text-[#2B2E2C]"></i>Resultados de Importación
                    </h3>
                    <div class="flex gap-4 text-sm">
                        <span class="text-green-700 font-semibold">
                            <i class="fas fa-check-circle mr-1"></i>{{ $resultados['insertados'] ?? 0 }} nuevos
                        </span>
                        <span class="text-blue-700 font-semibold">
                            <i class="fas fa-sync-alt mr-1"></i>{{ $resultados['actualizados'] ?? 0 }} actualizados
                        </span>
                        @if(($resultados['errores_count'] ?? 0) > 0)
                            <span class="text-red-700 font-semibold">
                                <i class="fas fa-times-circle mr-1"></i>{{ $resultados['errores_count'] }} errores
                            </span>
                        @endif
                    </div>
                </div>

                @if(!empty($resultados['filas']))
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-12">Fila</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Proveedor</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-24">Resultado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Detalle</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($resultados['filas'] as $fila)
                                    <tr class="{{ $fila['ok'] ? 'bg-green-50' : 'bg-red-50' }}">
                                        <td class="px-4 py-2 text-gray-500 font-mono text-xs">{{ $fila['fila'] }}</td>
                                        <td class="px-4 py-2 font-medium text-gray-800">
                                            {{ $fila['nombre'] ?? '—' }}
                                            @if(!empty($fila['tipo']))
                                                <span class="ml-1 text-xs text-gray-400">({{ $fila['tipo'] }})</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            @if($fila['ok'])
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                                    <i class="fas fa-check text-xs"></i>
                                                    {{ $fila['accion'] === 'actualizado' ? 'Actualizado' : 'Creado' }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                                                    <i class="fas fa-times text-xs"></i>Error
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-xs text-gray-600">
                                            @if($fila['ok'])
                                                @if(!empty($fila['advertencias']))
                                                    <span class="text-amber-600">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>{{ implode(' | ', $fila['advertencias']) }}
                                                    </span>
                                                @else
                                                    <span class="text-green-600">OK</span>
                                                @endif
                                            @else
                                                <span class="text-red-600">{{ implode(' | ', (array)($fila['errores'] ?? [])) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if(($resultados['errores_count'] ?? 0) > 0 && !empty($resultados['errores_csv']))
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                        <a href="{{ route('proveedores.importar.errores') }}"
                           class="inline-flex items-center gap-2 border border-red-400 text-red-600 hover:bg-red-50 font-medium px-4 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-download"></i>Descargar reporte de errores (CSV)
                        </a>
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>

<script>
function importarProveedores() {
    return {
        archivo: null,
        dragging: false,
        procesando: false,

        handleDrop(event) {
            this.dragging = false;
            const file = event.dataTransfer.files[0];
            if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls'))) {
                this.archivo = file;
                this.$refs.fileInput.files = event.dataTransfer.files;
            }
        },

        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }
    }
}
</script>
</body>
</html>
