{{--
    Partial: Clasificaciones de Uso
    Variables: $producto, $clasificaciones, $tiposProyecto
--}}
@php
    $inst = $producto->clasificacion ?? null;

    $clasificacionesAsignadas = old('clasificacion_ids',
        isset($producto) ? $producto->clasificaciones->pluck('id')->toArray() : []
    );
    if (!is_array($clasificacionesAsignadas)) $clasificacionesAsignadas = [];

    $tipoInstalacionActual = old('clasificacion.tipo_instalacion',
        isset($inst?->tipo_instalacion)
            ? (is_array($inst->tipo_instalacion) ? $inst->tipo_instalacion : (json_decode($inst->tipo_instalacion, true) ?? []))
            : []
    );
    $estilosActuales = old('clasificacion.estilo',
        isset($inst?->estilo)
            ? (is_array($inst->estilo) ? $inst->estilo : (json_decode($inst->estilo, true) ?? []))
            : []
    );
    $tiposProyectoAsignados = old('tipo_proyecto_ids',
        isset($producto) ? $producto->tiposProyecto->pluck('id')->toArray() : []
    );
    $ambientesActuales = old('clasificacion.ambientes',
        isset($inst?->ambientes)
            ? (is_array($inst->ambientes) ? $inst->ambientes : (json_decode($inst->ambientes, true) ?? []))
            : []
    );
    if (!is_array($tipoInstalacionActual))  $tipoInstalacionActual  = [];
    if (!is_array($estilosActuales))        $estilosActuales        = [];
    if (!is_array($tiposProyectoAsignados)) $tiposProyectoAsignados = [];
    if (!is_array($ambientesActuales))      $ambientesActuales      = [];
@endphp

<div class="space-y-6">

    {{-- Usos del producto (checkboxes desde BD) --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">
            <i class="fas fa-map-marker-alt mr-1 text-purple-400"></i>
            Usos del producto
            <span class="text-gray-400 font-normal">(selección múltiple)</span>
        </label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            @foreach($clasificaciones as $clf)
            <label class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:border-purple-400 transition-colors
                          {{ in_array($clf->id, (array) $clasificacionesAsignadas) ? 'border-purple-400 bg-[#2B2E2C]/10' : '' }}">
                <input type="checkbox"
                       name="clasificacion_ids[]"
                       value="{{ $clf->id }}"
                       {{ in_array($clf->id, (array) $clasificacionesAsignadas) ? 'checked' : '' }}
                       class="w-4 h-4 text-[#2B2E2C] rounded border-gray-300">
                <span class="text-sm text-gray-700">{{ $clf->nombre }}</span>
                <span class="ml-auto text-xs text-gray-400 font-mono">{{ $clf->codigo }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Tipo de Instalación --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">
            <i class="fas fa-wrench mr-1 text-purple-400"></i>
            Tipo de Instalación
            <span class="text-gray-400 font-normal">(selección múltiple)</span>
        </label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            @foreach(\App\Models\Luminaria\ProductoClasificacion::TIPOS_INSTALACION as $val => $lbl)
            <label class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:border-purple-400 transition-colors
                          {{ in_array($val, $tipoInstalacionActual) ? 'border-purple-400 bg-[#2B2E2C]/10' : '' }}">
                <input type="checkbox"
                       name="clasificacion[tipo_instalacion][]"
                       value="{{ $val }}"
                       {{ in_array($val, $tipoInstalacionActual) ? 'checked' : '' }}
                       class="w-4 h-4 text-[#2B2E2C] rounded border-gray-300">
                <span class="text-sm text-gray-700">{{ $lbl }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Estilo / Línea de Diseño (tag input) --}}
    <div x-data="tabClasifTagInput({ initial: @js($estilosActuales) })">
        <label class="block text-xs font-semibold text-gray-600 mb-2">
            <i class="fas fa-palette mr-1 text-purple-400"></i>
            Estilo / Línea de Diseño
            <span class="text-gray-400 font-normal">(selección múltiple)</span>
        </label>

        <div class="flex flex-wrap gap-1 mb-2">
            @foreach(\App\Models\Luminaria\ProductoClasificacion::ESTILOS_SUGERIDOS as $sug)
            <button type="button" @click="toggle('{{ $sug }}')"
                    :class="tags.includes('{{ $sug }}') ? 'bg-[#2B2E2C] text-white border-purple-600' : 'bg-white text-gray-600 border-gray-300 hover:border-purple-400'"
                    class="text-xs border rounded-full px-3 py-1 transition-colors">
                {{ $sug }}
            </button>
            @endforeach
        </div>

        <div class="flex flex-wrap gap-1 min-h-10 border border-gray-300 rounded-lg px-3 py-2 bg-white focus-within:ring-2 focus-within:ring-purple-400 cursor-text"
             @click="$refs.tagInputClasif.focus()">
            <template x-for="(tag, i) in tags" :key="i">
                <span class="inline-flex items-center gap-1 bg-[#2B2E2C]/10 text-[#2B2E2C] text-xs rounded-full px-2 py-0.5">
                    <span x-text="tag"></span>
                    <input type="hidden" :name="'clasificacion[estilo][]'" :value="tag">
                    <button type="button" @click.stop="remove(i)"
                            class="text-purple-500 hover:text-red-500 leading-none font-bold">&times;</button>
                </span>
            </template>
            <input type="text" x-ref="tagInputClasif"
                   placeholder="Escribir y Enter para agregar..."
                   @keydown.enter.prevent="addFromInput($event.target)"
                   @keydown.backspace="backspace($event)"
                   class="flex-1 min-w-[8rem] text-sm border-none outline-none bg-transparent py-0.5">
        </div>
        <p class="text-xs text-gray-400 mt-1">Selecciona de las sugerencias o escribe un estilo y presiona Enter</p>
    </div>

    {{-- Tipo de Proyecto + Ambientes (EspacioProyecto) --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">
            <i class="fas fa-building mr-1 text-purple-400"></i>
            Tipo de Proyecto
            <span class="text-gray-400 font-normal">(selección múltiple — expande para elegir ambientes)</span>
        </label>
        <div class="space-y-2">
            @foreach($tiposProyecto as $tp)
            @php $tpChecked = in_array($tp->id, $tiposProyectoAsignados); @endphp
            <div x-data="{ open: {{ $tpChecked ? 'true' : 'false' }} }" class="border border-gray-200 rounded-lg overflow-hidden">

                {{-- Cabecera del tipo de proyecto --}}
                <label class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-gray-50 transition-colors
                              {{ $tpChecked ? 'bg-[#2B2E2C]/10 border-purple-400' : 'bg-white' }}">
                    <input type="checkbox"
                           name="tipo_proyecto_ids[]"
                           value="{{ $tp->id }}"
                           {{ $tpChecked ? 'checked' : '' }}
                           @change="open = $event.target.checked"
                           class="w-4 h-4 text-[#2B2E2C] rounded border-gray-300">
                    <span class="text-sm font-medium text-gray-700 flex-1">{{ $tp->nombre }}</span>
                    @if($tp->espacios->count())
                    <button type="button" @click.prevent="open = !open"
                            class="text-xs text-purple-500 hover:text-purple-700 transition-colors flex items-center gap-1">
                        <span x-text="open ? 'ocultar' : 'ver ambientes'"></span>
                        <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    @endif
                </label>

                {{-- Ambientes (EspacioProyecto) --}}
                @if($tp->espacios->count())
                <div x-show="open" x-cloak class="border-t border-gray-100 bg-gray-50 px-4 py-3">
                    <p class="text-xs text-gray-400 mb-2">Ambientes de {{ $tp->nombre }}:</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($tp->espacios->unique('nombre') as $espacio)
                        @php $espChecked = in_array($espacio->id, $ambientesActuales); @endphp
                        <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-1.5 cursor-pointer hover:border-purple-300 transition-colors
                                      {{ $espChecked ? 'border-purple-400 bg-purple-50' : '' }}">
                            <input type="checkbox"
                                   name="clasificacion[ambientes][]"
                                   value="{{ $espacio->id }}"
                                   {{ $espChecked ? 'checked' : '' }}
                                   class="w-3.5 h-3.5 text-purple-500 rounded border-gray-300">
                            <span class="text-xs text-gray-600">{{ $espacio->nombre }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
            @endforeach
        </div>
    </div>

</div>

<script>
function tabClasifTagInput({ initial = [] } = {}) {
    return {
        tags: Array.isArray(initial) ? [...initial] : [],
        toggle(val) {
            const idx = this.tags.indexOf(val);
            if (idx === -1) this.tags.push(val);
            else this.tags.splice(idx, 1);
        },
        addFromInput(input) {
            const v = input.value.trim();
            if (v && !this.tags.includes(v)) this.tags.push(v);
            input.value = '';
        },
        remove(i) { this.tags.splice(i, 1); },
        backspace(e) {
            if (e.target.value === '' && this.tags.length > 0) this.tags.pop();
        },
    };
}
</script>
