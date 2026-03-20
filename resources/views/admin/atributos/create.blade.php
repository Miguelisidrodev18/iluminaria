<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Atributo</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8 max-w-4xl">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('admin.atributos.index') }}" class="hover:text-gray-700">Atributos</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-900 font-medium">Nuevo Atributo</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
         x-data="atributoForm()">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-100"
             style="background: linear-gradient(135deg, #1a3a2a 0%, #2B4A3A 100%);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#F7D600">
                    <i class="fas fa-plus text-sm" style="color:#1a3a2a"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Nuevo Atributo</h2>
                    <p class="text-xs text-gray-300">Define un campo dinámico para el configurador de productos</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.atributos.store') }}" class="p-6 space-y-6">
            @csrf

            {{-- Errores --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Fila 1: Nombre, Tipo, Grupo --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                           placeholder="ej. Potencia (W)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                           required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo de campo <span class="text-red-500">*</span></label>
                    <select name="tipo" x-model="tipo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            required>
                        @foreach(\App\Models\Catalogo\CatalogoAtributo::TIPOS as $key => $label)
                            <option value="{{ $key }}" {{ old('tipo') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Grupo <span class="text-red-500">*</span></label>
                    <select name="grupo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            required>
                        @foreach(\App\Models\Catalogo\CatalogoAtributo::GRUPOS as $key => $label)
                            <option value="{{ $key }}" {{ old('grupo') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Fila 2: Unidad, Placeholder, Descripción --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Unidad</label>
                    <input type="text" name="unidad" value="{{ old('unidad') }}"
                           placeholder="ej. W, lm, °K"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                    <p class="text-xs text-gray-400 mt-1">Se muestra junto al label</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Placeholder</label>
                    <input type="text" name="placeholder" value="{{ old('placeholder') }}"
                           placeholder="ej. Ingrese la potencia..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Descripción</label>
                    <input type="text" name="descripcion" value="{{ old('descripcion') }}"
                           placeholder="Ayuda breve para el usuario"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                </div>
            </div>

            {{-- Fila 3: Orden + Switches --}}
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-start">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Orden en formulario</label>
                    <input type="number" name="orden" value="{{ old('orden', 0) }}" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Orden en nombre auto</label>
                    <input type="number" name="orden_nombre" value="{{ old('orden_nombre', 0) }}" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                    <p class="text-xs text-gray-400 mt-1">Solo si activa nombre auto</p>
                </div>
                <div class="pt-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="requerido" value="0">
                        <input type="checkbox" name="requerido" value="1"
                               {{ old('requerido') ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded border-gray-300 focus:ring-yellow-400">
                        <span class="text-sm text-gray-700 font-medium">Campo requerido</span>
                    </label>
                </div>
                <div class="pt-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="en_nombre_auto" value="0">
                        <input type="checkbox" name="en_nombre_auto" value="1"
                               {{ old('en_nombre_auto') ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded border-gray-300 focus:ring-yellow-400">
                        <span class="text-sm text-gray-700 font-medium">Incluir en nombre auto</span>
                    </label>
                </div>
            </div>

            {{-- Valores predefinidos (select / multiselect) --}}
            <div x-show="tipo === 'select' || tipo === 'multiselect'" x-cloak>
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">
                            <i class="fas fa-list-ul mr-2 text-gray-400"></i>Valores predefinidos
                        </h3>
                        <button type="button" @click="agregarValor()"
                                class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg font-medium transition-colors">
                            <i class="fas fa-plus mr-1"></i>Agregar valor
                        </button>
                    </div>
                    <div class="p-4 space-y-2">
                        <template x-for="(v, i) in valores" :key="i">
                            <div class="grid grid-cols-12 gap-2 items-center">
                                <div class="col-span-4">
                                    <input type="text" :name="`valores[${i}][valor]`" x-model="v.valor"
                                           placeholder="Valor (slug interno)"
                                           class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-yellow-400">
                                </div>
                                <div class="col-span-4">
                                    <input type="text" :name="`valores[${i}][etiqueta]`" x-model="v.etiqueta"
                                           placeholder="Etiqueta visible (opcional)"
                                           class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-yellow-400">
                                </div>
                                <div class="col-span-2">
                                    <div class="flex items-center gap-2">
                                        <input type="color" :name="`valores[${i}][color_hex]`" x-model="v.color_hex"
                                               class="w-8 h-8 rounded border border-gray-300 cursor-pointer p-0.5">
                                        <span class="text-xs text-gray-400">Color</span>
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <input type="number" :name="`valores[${i}][orden]`" x-model="v.orden"
                                           :value="i" min="0"
                                           class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm text-center focus:ring-1 focus:ring-yellow-400">
                                </div>
                                <div class="col-span-1 text-right">
                                    <button type="button" @click="valores.splice(i, 1)"
                                            class="text-red-400 hover:text-red-600 transition-colors p-1">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div x-show="valores.length === 0" class="py-6 text-center text-sm text-gray-400">
                            <i class="fas fa-inbox text-2xl block mb-2 opacity-30"></i>
                            Sin valores. Haz clic en "Agregar valor".
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <a href="{{ route('admin.atributos.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>Cancelar
                </a>
                <button type="submit"
                        class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-save"></i> Guardar Atributo
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function atributoForm() {
    return {
        tipo: '{{ old('tipo', 'select') }}',
        valores: @json(collect(old('valores', []))->values()),
        agregarValor() {
            this.valores.push({ valor: '', etiqueta: '', color_hex: '#cccccc', orden: this.valores.length });
        },
    };
}
</script>
</body>
</html>
