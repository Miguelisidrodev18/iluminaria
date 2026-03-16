{{--
    Partial: Ficha Técnica Completa de Luminaria
    Usar en: inventario.productos.create / edit
    Variables disponibles: $producto (nullable), $tiposProyecto
--}}

{{-- ═══════════════════════════════════════════════════════════════════════
    ESPECIFICACIONES ELÉCTRICAS
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mb-8" x-data="{ open: true }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between text-left text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200 mb-4">
        <span><i class="fas fa-bolt mr-2 text-yellow-500"></i>Especificaciones Eléctricas</span>
        <i class="fas fa-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @php $esp = $producto->especificacion ?? null; @endphp

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Potencia (W)</label>
            <input type="text" name="especificacion[potencia]"
                   value="{{ old('especificacion.potencia', $esp->potencia ?? '') }}"
                   placeholder="Ej: 18W, 2×36W"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
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

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">CRI (Índice Reproducción Cromática)</label>
            <input type="number" name="especificacion[cri]" min="0" max="100"
                   value="{{ old('especificacion.cri', $esp->cri ?? '') }}"
                   placeholder="0 - 100"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Índice de Protección (IP)</label>
            <input type="text" name="especificacion[ip]"
                   value="{{ old('especificacion.ip', $esp->ip ?? '') }}"
                   placeholder="Ej: IP65, IP20"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Protección Mecánica (IK)</label>
            <input type="text" name="especificacion[ik]"
                   value="{{ old('especificacion.ik', $esp->ik ?? '') }}"
                   placeholder="Ej: IK08"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Ángulo de Apertura</label>
            <input type="text" name="especificacion[angulo_apertura]"
                   value="{{ old('especificacion.angulo_apertura', $esp->angulo_apertura ?? '') }}"
                   placeholder="Ej: 36°, 120°"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Driver</label>
            <input type="text" name="especificacion[driver]"
                   value="{{ old('especificacion.driver', $esp->driver ?? '') }}"
                   placeholder="Ej: Integrado, Externo Meanwell"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
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
        @php $dim = $producto->dimensiones ?? null; @endphp

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
        @php $mat = $producto->materiales ?? null; @endphp

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
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
    CLASIFICACIÓN
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mb-8" x-data="{ open: true }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between text-left text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200 mb-4">
        <span><i class="fas fa-tags mr-2 text-purple-600"></i>Clasificación de Uso</span>
        <i class="fas fa-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @php $clas = $producto->clasificacion ?? null; @endphp

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Uso</label>
            <select name="clasificacion[uso]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-400">
                <option value="interior"          {{ old('clasificacion.uso', $clas->uso ?? 'interior') === 'interior' ? 'selected' : '' }}>Interior</option>
                <option value="exterior"          {{ old('clasificacion.uso', $clas->uso ?? '') === 'exterior' ? 'selected' : '' }}>Exterior</option>
                <option value="interior_exterior" {{ old('clasificacion.uso', $clas->uso ?? '') === 'interior_exterior' ? 'selected' : '' }}>Interior / Exterior</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de Instalación</label>
            <select name="clasificacion[tipo_instalacion]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-400">
                <option value="">— Seleccione —</option>
                @foreach(['empotrado' => 'Empotrado', 'superficie' => 'Superficie', 'suspendido' => 'Suspendido', 'poste' => 'Poste', 'carril' => 'Carril', 'portatil' => 'Portátil'] as $val => $label)
                    <option value="{{ $val }}" {{ old('clasificacion.tipo_instalacion', $clas->tipo_instalacion ?? '') === $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Estilo / Línea de Diseño</label>
            <input type="text" name="clasificacion[estilo]"
                   value="{{ old('clasificacion.estilo', $clas->estilo ?? '') }}"
                   placeholder="Ej: Moderno, Industrial, Clásico"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de Proyecto</label>
            <select name="clasificacion[tipo_proyecto_id]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-400">
                <option value="">— Sin asignar —</option>
                @foreach($tiposProyecto as $tp)
                    <option value="{{ $tp->id }}"
                        {{ old('clasificacion.tipo_proyecto_id', $clas->tipo_proyecto_id ?? '') == $tp->id ? 'selected' : '' }}>
                        {{ $tp->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>
