<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Atributo — {{ $atributo->nombre }}</title>
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
        <span class="text-gray-900 font-medium">{{ $atributo->nombre }}</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
         x-data="atributoEditForm(@js($atributo->valores->map(fn($v) => ['id' => $v->id, 'valor' => $v->valor, 'etiqueta' => $v->etiqueta ?? '', 'color_hex' => $v->color_hex ?? '#cccccc', 'orden' => $v->orden, 'activo' => $v->activo])->values()))">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-100"
             style="background: linear-gradient(135deg, #1a3a2a 0%, #2B4A3A 100%);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#F7D600">
                    <i class="fas fa-edit text-sm" style="color:#1a3a2a"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Editar: {{ $atributo->nombre }}</h2>
                    <p class="text-xs text-gray-300 font-mono">{{ $atributo->slug }}</p>
                </div>
                @if(!$atributo->activo)
                    <span class="ml-auto text-xs bg-red-500/20 text-red-300 px-2 py-1 rounded">INACTIVO</span>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('admin.atributos.update', $atributo) }}" class="p-6 space-y-6">
            @csrf @method('PUT')

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
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 flex items-center gap-2 text-sm text-green-700">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            {{-- Fila 1: Nombre, Tipo, Grupo --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre', $atributo->nombre) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                           required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo de campo <span class="text-red-500">*</span></label>
                    <select name="tipo" x-model="tipo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            required>
                        @foreach(\App\Models\Catalogo\CatalogoAtributo::TIPOS as $key => $label)
                            <option value="{{ $key }}" {{ old('tipo', $atributo->tipo) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Grupo <span class="text-red-500">*</span></label>
                    <select name="grupo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            required>
                        @foreach(\App\Models\Catalogo\CatalogoAtributo::GRUPOS as $key => $label)
                            <option value="{{ $key }}" {{ old('grupo', $atributo->grupo) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Fila 2: Unidad, Placeholder, Descripción --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Unidad</label>
                    <input type="text" name="unidad" value="{{ old('unidad', $atributo->unidad) }}"
                           placeholder="ej. W, lm, °K"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Placeholder</label>
                    <input type="text" name="placeholder" value="{{ old('placeholder', $atributo->placeholder) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Descripción</label>
                    <input type="text" name="descripcion" value="{{ old('descripcion', $atributo->descripcion) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                </div>
            </div>

            {{-- Fila 3: Orden + Switches + Activo --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 items-start">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Orden formulario</label>
                    <input type="number" name="orden" value="{{ old('orden', $atributo->orden) }}" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Orden nombre auto</label>
                    <input type="number" name="orden_nombre" value="{{ old('orden_nombre', $atributo->orden_nombre) }}" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                </div>
                <div class="pt-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="requerido" value="0">
                        <input type="checkbox" name="requerido" value="1"
                               {{ old('requerido', $atributo->requerido) ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded border-gray-300 focus:ring-yellow-400">
                        <span class="text-sm text-gray-700 font-medium">Requerido</span>
                    </label>
                </div>
                <div class="pt-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="en_nombre_auto" value="0">
                        <input type="checkbox" name="en_nombre_auto" value="1"
                               {{ old('en_nombre_auto', $atributo->en_nombre_auto) ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded border-gray-300 focus:ring-yellow-400">
                        <span class="text-sm text-gray-700 font-medium">Nombre auto</span>
                    </label>
                </div>
                <div class="pt-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1"
                               {{ old('activo', $atributo->activo) ? 'checked' : '' }}
                               class="w-4 h-4 text-green-500 rounded border-gray-300 focus:ring-green-400">
                        <span class="text-sm text-gray-700 font-medium">Activo</span>
                    </label>
                </div>
            </div>

            {{-- Valores predefinidos --}}
            <div x-show="tipo === 'select' || tipo === 'multiselect'" x-cloak>
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">
                            <i class="fas fa-list-ul mr-2 text-gray-400"></i>Valores predefinidos
                            <span class="text-gray-400 font-normal ml-1">(<span x-text="valores.filter(v=>v.activo).length"></span> activos)</span>
                        </h3>
                        <button type="button" @click="agregarValor()"
                                class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg font-medium transition-colors">
                            <i class="fas fa-plus mr-1"></i>Agregar valor
                        </button>
                    </div>
                    <div class="p-4 space-y-2">
                        {{-- Cabecera tabla --}}
                        <div class="grid grid-cols-12 gap-2 px-1 pb-1 border-b border-gray-100">
                            <div class="col-span-4 text-xs text-gray-400 font-medium">Valor (clave)</div>
                            <div class="col-span-3 text-xs text-gray-400 font-medium">Etiqueta</div>
                            <div class="col-span-2 text-xs text-gray-400 font-medium">Color</div>
                            <div class="col-span-1 text-xs text-gray-400 font-medium text-center">Ord.</div>
                            <div class="col-span-1 text-xs text-gray-400 font-medium text-center">Act.</div>
                            <div class="col-span-1"></div>
                        </div>

                        <template x-for="(v, i) in valores" :key="i">
                            <div class="grid grid-cols-12 gap-2 items-center py-1"
                                 :class="v.activo ? '' : 'opacity-50'">
                                {{-- id hidden --}}
                                <input type="hidden" :name="`valores[${i}][id]`" :value="v.id ?? ''">

                                <div class="col-span-4">
                                    <input type="text" :name="`valores[${i}][valor]`" x-model="v.valor"
                                           placeholder="Valor clave"
                                           class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-yellow-400">
                                </div>
                                <div class="col-span-3">
                                    <input type="text" :name="`valores[${i}][etiqueta]`" x-model="v.etiqueta"
                                           placeholder="Etiqueta (opcional)"
                                           class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-yellow-400">
                                </div>
                                <div class="col-span-2 flex items-center gap-1.5">
                                    <input type="color" :name="`valores[${i}][color_hex]`" x-model="v.color_hex"
                                           class="w-8 h-8 rounded border border-gray-300 cursor-pointer p-0.5">
                                    <span class="text-xs text-gray-400 font-mono" x-text="v.color_hex"></span>
                                </div>
                                <div class="col-span-1">
                                    <input type="number" :name="`valores[${i}][orden]`" x-model="v.orden"
                                           min="0"
                                           class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs text-center focus:ring-1 focus:ring-yellow-400">
                                </div>
                                <div class="col-span-1 flex justify-center">
                                    <input type="hidden" :name="`valores[${i}][activo]`" :value="v.activo ? '1' : '0'">
                                    <button type="button" @click="v.activo = !v.activo"
                                            :class="v.activo ? 'text-green-500 hover:text-red-500' : 'text-gray-300 hover:text-green-500'"
                                            class="transition-colors p-1" :title="v.activo ? 'Desactivar' : 'Activar'">
                                        <i class="fas" :class="v.activo ? 'fa-eye' : 'fa-eye-slash'"></i>
                                    </button>
                                </div>
                                <div class="col-span-1 text-right">
                                    <button type="button" @click="eliminarValor(i)"
                                            class="text-red-400 hover:text-red-600 transition-colors p-1"
                                            title="Quitar de la lista">
                                        <i class="fas fa-times text-sm"></i>
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
                    <i class="fas fa-arrow-left mr-1"></i>Volver al listado
                </a>
                <button type="submit"
                        class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function atributoEditForm(valoresIniciales = []) {
    return {
        tipo: '{{ old('tipo', $atributo->tipo) }}',
        valores: valoresIniciales,
        agregarValor() {
            this.valores.push({
                id: null,
                valor: '',
                etiqueta: '',
                color_hex: '#cccccc',
                orden: this.valores.length,
                activo: true,
            });
        },
        eliminarValor(i) {
            // Si tiene id, marcar inactivo en lugar de eliminar para no perder datos
            if (this.valores[i].id) {
                this.valores[i].activo = false;
            } else {
                this.valores.splice(i, 1);
            }
        },
    };
}
</script>
</body>
</html>
