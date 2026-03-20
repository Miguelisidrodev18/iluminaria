{{--
    Partial: Ficha Técnica Completa de Luminaria
    Usar en: inventario.productos.create / edit
    Variables disponibles: $producto (nullable), $tiposProyecto, $clasificaciones
--}}

@php
    $esp  = $producto->especificacion ?? null;
    $dim  = $producto->dimensiones    ?? null;
    $mat  = $producto->materiales     ?? null;
    $inst = $producto->clasificacion  ?? null;

    // IDs de clasificaciones actualmente asignadas (pivot)
    $clasificacionesAsignadas = old('clasificacion_ids',
        isset($producto) ? $producto->clasificaciones->pluck('id')->toArray() : []
    );
    if (!is_array($clasificacionesAsignadas)) {
        $clasificacionesAsignadas = [];
    }
@endphp

{{-- ═══════════════════════════════════════════════════════════════════════
    ESPECIFICACIONES TÉCNICAS Y ELÉCTRICAS
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mb-8" x-data="{ open: true }" data-seccion="especificaciones">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between text-left text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200 mb-4">
        <span><i class="fas fa-bolt mr-2 text-yellow-500"></i>Especificaciones Técnicas y Eléctricas</span>
        <i class="fas fa-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Potencia (W)</label>
            <input type="text" name="especificacion[potencia]"
                   value="{{ old('especificacion.potencia', $esp->potencia ?? '') }}"
                   placeholder="Ej: 18W, 2×36W"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div class="campo-lumenes">
            <label class="block text-xs font-medium text-gray-600 mb-1">Lúmenes (lm)</label>
            <input type="text" name="especificacion[lumenes]"
                   value="{{ old('especificacion.lumenes', $esp->lumenes ?? '') }}"
                   placeholder="Ej: 1800lm"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Voltaje</label>
            <input type="text" name="especificacion[voltaje]"
                   value="{{ old('especificacion.voltaje', $esp->voltaje ?? '') }}"
                   placeholder="Ej: 220V, 100-240V"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Temperatura de Color (K)</label>
            <input type="text" name="especificacion[temperatura_color]"
                   value="{{ old('especificacion.temperatura_color', $esp->temperatura_color ?? '') }}"
                   placeholder="Ej: 3000K, 4000K"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div class="campo-cri">
            <label class="block text-xs font-medium text-gray-600 mb-1">CRI</label>
            <input type="number" name="especificacion[cri]" min="0" max="100"
                   value="{{ old('especificacion.cri', $esp->cri ?? '') }}"
                   placeholder="0 – 100"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div class="campo-ip">
            <label class="block text-xs font-medium text-gray-600 mb-1">Protección IP</label>
            <input type="text" name="especificacion[ip]"
                   value="{{ old('especificacion.ip', $esp->ip ?? '') }}"
                   placeholder="Ej: IP65, IP20"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Protección IK</label>
            <input type="text" name="especificacion[ik]"
                   value="{{ old('especificacion.ik', $esp->ik ?? '') }}"
                   placeholder="Ej: IK08"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div class="campo-angulo">
            <label class="block text-xs font-medium text-gray-600 mb-1">Ángulo de Apertura</label>
            <input type="text" name="especificacion[angulo_apertura]"
                   value="{{ old('especificacion.angulo_apertura', $esp->angulo_apertura ?? '') }}"
                   placeholder="Ej: 36°, 120°"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Driver</label>
            <select name="especificacion[driver]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                <option value="">— Sin especificar —</option>
                <option value="incluido"
                    {{ old('especificacion.driver', $esp->driver ?? '') === 'incluido' ? 'selected' : '' }}>
                    Incluido
                </option>
                <option value="no_incluido"
                    {{ old('especificacion.driver', $esp->driver ?? '') === 'no_incluido' ? 'selected' : '' }}>
                    No incluido
                </option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Socket / Casquillo</label>
            <input type="text" name="especificacion[socket]"
                   value="{{ old('especificacion.socket', $esp->socket ?? '') }}"
                   placeholder="Ej: E27, GU10"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Nº de Lámparas</label>
            <input type="number" name="especificacion[numero_lamparas]" min="1"
                   value="{{ old('especificacion.numero_lamparas', $esp->numero_lamparas ?? '') }}"
                   placeholder="Ej: 1, 2"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Protocolo de Regulación</label>
            <input type="text" name="especificacion[protocolo_regulacion]"
                   value="{{ old('especificacion.protocolo_regulacion', $esp->protocolo_regulacion ?? '') }}"
                   placeholder="Ej: 0-10V, DALI, Triac"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Vida Útil (horas)</label>
            <input type="number" name="especificacion[vida_util_horas]" min="0" step="500"
                   value="{{ old('especificacion.vida_util_horas', $esp->vida_util_horas ?? '') }}"
                   placeholder="Ej: 25000, 50000"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div class="flex items-end pb-1">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="especificacion[regulable]" value="1"
                       {{ old('especificacion.regulable', $esp->regulable ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 text-yellow-500 rounded">
                <span class="text-sm text-gray-700">Regulable (Dimeable)</span>
            </label>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
    DIMENSIONES
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mb-8" x-data="{ open: true }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between text-left text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200 mb-4">
        <span><i class="fas fa-ruler-combined mr-2 text-blue-500"></i>Dimensiones <span class="text-sm font-normal text-gray-400">(en mm)</span></span>
        <i class="fas fa-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open" x-transition class="grid grid-cols-2 md:grid-cols-4 gap-4">

        @foreach([
            ['alto',             'Alto'],
            ['ancho',            'Ancho'],
            ['diametro',         'Diámetro'],
            ['lado',             'Lado (cuadrado)'],
            ['profundidad',      'Profundidad'],
            ['alto_suspendido',  'Alto suspendido'],
            ['diametro_agujero', 'Diám. agujero corte'],
        ] as [$campo, $label])
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
            <div class="relative">
                <input type="number" step="0.01" min="0"
                       name="dimensiones[{{ $campo }}]"
                       value="{{ old('dimensiones.' . $campo, $dim->$campo ?? '') }}"
                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400">
                <span class="absolute right-3 top-2 text-xs text-gray-400">mm</span>
            </div>
        </div>
        @endforeach

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Peso</label>
            <div class="relative">
                <input type="number" step="0.001" min="0"
                       name="dimensiones[peso]"
                       value="{{ old('dimensiones.peso', $dim->peso ?? '') }}"
                       placeholder="0.000"
                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400">
                <span class="absolute right-3 top-2 text-xs text-gray-400">kg</span>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
    MATERIALES Y ACABADOS
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mb-8" x-data="{ open: true }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between text-left text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200 mb-4">
        <span><i class="fas fa-layer-group mr-2 text-green-600"></i>Materiales y Acabados</span>
        <i class="fas fa-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Material principal</label>
            <input type="text" name="materiales[material_1]"
                   value="{{ old('materiales.material_1', $mat->material_1 ?? '') }}"
                   placeholder="Ej: Aluminio fundido"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Material secundario</label>
            <input type="text" name="materiales[material_2]"
                   value="{{ old('materiales.material_2', $mat->material_2 ?? '') }}"
                   placeholder="Ej: Vidrio templado"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Material terciario</label>
            <input type="text" name="materiales[material_terciario]"
                   value="{{ old('materiales.material_terciario', $mat->material_terciario ?? '') }}"
                   placeholder="Ej: Policarbonato, Acrílico"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Acabado / Color 1</label>
            <input type="text" name="materiales[color_acabado_1]"
                   value="{{ old('materiales.color_acabado_1', $mat->color_acabado_1 ?? '') }}"
                   placeholder="Ej: Blanco RAL 9003"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Acabado / Color 2</label>
            <input type="text" name="materiales[color_acabado_2]"
                   value="{{ old('materiales.color_acabado_2', $mat->color_acabado_2 ?? '') }}"
                   placeholder="Ej: Negro mate"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Acabado / Color 3</label>
            <input type="text" name="materiales[color_acabado_3]"
                   value="{{ old('materiales.color_acabado_3', $mat->color_acabado_3 ?? '') }}"
                   placeholder="Ej: Gris perla"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
    CLASIFICACIÓN DE USO — todos los campos permiten selección múltiple
═══════════════════════════════════════════════════════════════════════ --}}
@php
    // Valores actuales para los tres campos multi-valor
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
    if (!is_array($tipoInstalacionActual))  $tipoInstalacionActual  = [];
    if (!is_array($estilosActuales))        $estilosActuales        = [];
    if (!is_array($tiposProyectoAsignados)) $tiposProyectoAsignados = [];
@endphp

<div class="mb-8" x-data="{ open: true }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between text-left text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200 mb-4">
        <span><i class="fas fa-tags mr-2 text-purple-600"></i>Clasificación de Uso</span>
        <i class="fas fa-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open" x-transition class="space-y-6">

        {{-- ── Usos del producto (checkboxes desde BD) ──────────────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">
                <i class="fas fa-map-marker-alt mr-1 text-purple-400"></i>
                Usos del producto
                <span class="text-gray-400 font-normal">(selección múltiple)</span>
            </label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach($clasificaciones as $clf)
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:border-purple-400 transition-colors
                              {{ in_array($clf->id, (array) $clasificacionesAsignadas) ? 'border-purple-400 bg-purple-50' : '' }}">
                    <input type="checkbox"
                           name="clasificacion_ids[]"
                           value="{{ $clf->id }}"
                           {{ in_array($clf->id, (array) $clasificacionesAsignadas) ? 'checked' : '' }}
                           class="w-4 h-4 text-purple-600 rounded border-gray-300">
                    <span class="text-sm text-gray-700">{{ $clf->nombre }}</span>
                    <span class="ml-auto text-xs text-gray-400 font-mono">{{ $clf->codigo }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- ── Tipo de Instalación (checkboxes, múltiple) ──────────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">
                <i class="fas fa-wrench mr-1 text-purple-400"></i>
                Tipo de Instalación
                <span class="text-gray-400 font-normal">(selección múltiple)</span>
            </label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach(\App\Models\Luminaria\ProductoClasificacion::TIPOS_INSTALACION as $val => $lbl)
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:border-purple-400 transition-colors
                              {{ in_array($val, $tipoInstalacionActual) ? 'border-purple-400 bg-purple-50' : '' }}">
                    <input type="checkbox"
                           name="clasificacion[tipo_instalacion][]"
                           value="{{ $val }}"
                           {{ in_array($val, $tipoInstalacionActual) ? 'checked' : '' }}
                           class="w-4 h-4 text-purple-600 rounded border-gray-300">
                    <span class="text-sm text-gray-700">{{ $lbl }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- ── Estilo / Línea de Diseño (tag input, múltiple) ──────────────── --}}
        <div x-data="tagInput({ initial: @js($estilosActuales) })">
            <label class="block text-xs font-semibold text-gray-600 mb-2">
                <i class="fas fa-palette mr-1 text-purple-400"></i>
                Estilo / Línea de Diseño
                <span class="text-gray-400 font-normal">(selección múltiple)</span>
            </label>

            {{-- Sugerencias rápidas --}}
            <div class="flex flex-wrap gap-1 mb-2">
                @foreach(\App\Models\Luminaria\ProductoClasificacion::ESTILOS_SUGERIDOS as $sug)
                <button type="button" @click="toggle('{{ $sug }}')"
                        :class="tags.includes('{{ $sug }}') ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-600 border-gray-300 hover:border-purple-400'"
                        class="text-xs border rounded-full px-3 py-1 transition-colors">
                    {{ $sug }}
                </button>
                @endforeach
            </div>

            {{-- Tags seleccionados + input custom --}}
            <div class="flex flex-wrap gap-1 min-h-10 border border-gray-300 rounded-lg px-3 py-2 bg-white focus-within:ring-2 focus-within:ring-purple-400 cursor-text"
                 @click="$refs.tagInput.focus()">
                <template x-for="(tag, i) in tags" :key="i">
                    <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-800 text-xs rounded-full px-2 py-0.5">
                        <span x-text="tag"></span>
                        <input type="hidden" :name="'clasificacion[estilo][]'" :value="tag">
                        <button type="button" @click.stop="remove(i)"
                                class="text-purple-500 hover:text-red-500 leading-none font-bold">&times;</button>
                    </span>
                </template>
                <input type="text" x-ref="tagInput"
                       placeholder="Escribir y Enter para agregar..."
                       @keydown.enter.prevent="addFromInput($event.target)"
                       @keydown.backspace="backspace($event)"
                       class="flex-1 min-w-35 text-sm border-none outline-none bg-transparent py-0.5">
            </div>
            <p class="text-xs text-gray-400 mt-1">Selecciona de las sugerencias o escribe un estilo personalizado y presiona Enter</p>
        </div>

        {{-- ── Tipo de Proyecto (checkboxes desde BD, múltiple) ────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">
                <i class="fas fa-building mr-1 text-purple-400"></i>
                Tipo de Proyecto
                <span class="text-gray-400 font-normal">(selección múltiple)</span>
            </label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach($tiposProyecto as $tp)
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:border-purple-400 transition-colors
                              {{ in_array($tp->id, $tiposProyectoAsignados) ? 'border-purple-400 bg-purple-50' : '' }}">
                    <input type="checkbox"
                           name="tipo_proyecto_ids[]"
                           value="{{ $tp->id }}"
                           {{ in_array($tp->id, $tiposProyectoAsignados) ? 'checked' : '' }}
                           class="w-4 h-4 text-purple-600 rounded border-gray-300">
                    <span class="text-sm text-gray-700">{{ $tp->nombre }}</span>
                </label>
                @endforeach
            </div>
        </div>

    </div>
</div>

{{-- Alpine component: tag input reutilizable --}}
<script>
function tagInput({ initial = [] } = {}) {
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
            if (e.target.value === '' && this.tags.length > 0) {
                this.tags.pop();
            }
        },
    };
}
</script>
