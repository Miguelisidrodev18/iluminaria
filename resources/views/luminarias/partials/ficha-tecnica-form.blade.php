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
{{-- ── Especificaciones Técnicas y Eléctricas ──────────────────────────── --}}
<div class="mb-8" x-data="{ open: true }" data-seccion="especificaciones">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between text-left text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200 mb-4">
        <span><i class="fas fa-bolt mr-2 text-yellow-500"></i>Especificaciones Técnicas y Eléctricas</span>
        <i class="fas fa-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Tipo de fuente --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de fuente</label>
            <select name="especificacion[tipo_fuente]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                <option value="">— Sin especificar —</option>
                @foreach(['LED','Fluorescente','Halógena','HID','Incandescente','Fibra óptica'] as $tf)
                <option value="{{ $tf }}" {{ old('especificacion.tipo_fuente', $esp->tipo_fuente ?? '') === $tf ? 'selected' : '' }}>{{ $tf }}</option>
                @endforeach
            </select>
        </div>

        {{-- Nivel de potencia --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Nivel de potencia</label>
            <select name="especificacion[nivel_potencia]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                <option value="">— Sin especificar —</option>
                @foreach(['Baja (0–10W)','Media (11–30W)','Alta (31W+)'] as $np)
                <option value="{{ $np }}" {{ old('especificacion.nivel_potencia', $esp->nivel_potencia ?? '') === $np ? 'selected' : '' }}>{{ $np }}</option>
                @endforeach
            </select>
        </div>

        {{-- Salida de luz --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Salida de luz</label>
            <select name="especificacion[salida_luz]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                <option value="">— Sin especificar —</option>
                @foreach(['Directa','Indirecta','Mixta','Difusa'] as $sl)
                <option value="{{ $sl }}" {{ old('especificacion.salida_luz', $esp->salida_luz ?? '') === $sl ? 'selected' : '' }}>{{ $sl }}</option>
                @endforeach
            </select>
        </div>

        {{-- Potencia --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Potencia (W)</label>
            <input type="text" name="especificacion[potencia]"
                   value="{{ old('especificacion.potencia', $esp->potencia ?? '') }}"
                   placeholder="Ej: 18W, 2×36W"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        {{-- Voltaje --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Voltaje</label>
            <input type="text" name="especificacion[voltaje]"
                   value="{{ old('especificacion.voltaje', $esp->voltaje ?? '') }}"
                   placeholder="Ej: 220V, 100-240V"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        {{-- Socket --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Socket / Casquillo</label>
            <input type="text" name="especificacion[socket]"
                   value="{{ old('especificacion.socket', $esp->socket ?? '') }}"
                   placeholder="Ej: E27, GU10, G13"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        {{-- Nº de lámparas --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Nº de Lámparas</label>
            <input type="number" name="especificacion[numero_lamparas]" min="1"
                   value="{{ old('especificacion.numero_lamparas', $esp->numero_lamparas ?? '') }}"
                   placeholder="Ej: 1, 2"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        {{-- Protección IP --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Protección IP</label>
            <input type="text" name="especificacion[ip]"
                   value="{{ old('especificacion.ip', $esp->ip ?? '') }}"
                   placeholder="Ej: IP65, IP20"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        {{-- Protección IK --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Protección IK</label>
            <input type="text" name="especificacion[ik]"
                   value="{{ old('especificacion.ik', $esp->ik ?? '') }}"
                   placeholder="Ej: IK08"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        {{-- Ángulo de apertura --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Ángulo de Apertura</label>
            <input type="text" name="especificacion[angulo_apertura]"
                   value="{{ old('especificacion.angulo_apertura', $esp->angulo_apertura ?? '') }}"
                   placeholder="Ej: 36°, 120°"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        {{-- Driver --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Driver</label>
            <select name="especificacion[driver]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
                <option value="">— Sin especificar —</option>
                <option value="incluido" {{ old('especificacion.driver', $esp->driver ?? '') === 'incluido' ? 'selected' : '' }}>Incluido</option>
                <option value="no_incluido" {{ old('especificacion.driver', $esp->driver ?? '') === 'no_incluido' ? 'selected' : '' }}>No incluido</option>
                <option value="externo_meanwell" {{ old('especificacion.driver', $esp->driver ?? '') === 'externo_meanwell' ? 'selected' : '' }}>Externo Meanwell</option>
            </select>
        </div>

        {{-- Vida útil --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Vida Útil (horas)</label>
            <input type="number" name="especificacion[vida_util_horas]" min="0" step="500"
                   value="{{ old('especificacion.vida_util_horas', $esp->vida_util_horas ?? '') }}"
                   placeholder="Ej: 25000, 50000"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        {{-- Protocolo de regulación --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Protocolo de Regulación</label>
            <input type="text" name="especificacion[protocolo_regulacion]"
                   value="{{ old('especificacion.protocolo_regulacion', $esp->protocolo_regulacion ?? '') }}"
                   placeholder="Ej: 0-10V, DALI, Triac"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
        </div>

        {{-- Regulable --}}
        <div class="flex items-end pb-1">
            <label class="flex items-center gap-2 cursor-pointer bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-2 w-full">
                <input type="checkbox" name="especificacion[regulable]" value="1"
                       {{ old('especificacion.regulable', $esp->regulable ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 text-yellow-500 rounded">
                <span class="text-sm text-gray-700 font-medium">Regulable (Dimeable)</span>
            </label>
        </div>
    </div>
</div>

{{-- ── Fotometría ─────────────────────────────────────────────────────────── --}}
<div class="mb-8" x-data="{ open: true }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between text-left text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200 mb-4">
        <span><i class="fas fa-sun mr-2 text-amber-400"></i>Fotometría</span>
        <i class="fas fa-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Tonalidad de luz --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tonalidad de luz</label>
            <select name="especificacion[tonalidad_luz]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
                <option value="">— Sin especificar —</option>
                @foreach(['Cálido','Neutro','Frío','Bicolor','Multicolor'] as $t)
                <option value="{{ $t }}" {{ old('especificacion.tonalidad_luz', $esp->tonalidad_luz ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Cálido ≈ 2700–3000K · Neutro ≈ 4000K · Frío ≈ 5000–6500K</p>
        </div>

        {{-- Temperatura de color --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Temperatura de Color (K)</label>
            <input type="text" name="especificacion[temperatura_color]"
                   value="{{ old('especificacion.temperatura_color', $esp->temperatura_color ?? '') }}"
                   placeholder="Ej: 3000K, 4000K"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
        </div>

        {{-- CRI --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">CRI (0–100)</label>
            <input type="number" name="especificacion[cri]" min="0" max="100"
                   value="{{ old('especificacion.cri', $esp->cri ?? '') }}"
                   placeholder="0 – 100"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
        </div>

        {{-- Lúmenes nominales --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Lúmenes nominales <span class="font-normal text-gray-400">(catálogo fabricante)</span></label>
            <div class="relative">
                <input type="number" step="1" min="0" name="especificacion[nominal_lumenes]"
                       value="{{ old('especificacion.nominal_lumenes', $esp->nominal_lumenes ?? '') }}"
                       placeholder="Ej: 2000"
                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
                <span class="absolute right-3 top-2 text-xs text-gray-400">lm</span>
            </div>
        </div>

        {{-- Lúmenes reales --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Lúmenes reales <span class="font-normal text-gray-400">(medición)</span></label>
            <div class="relative">
                <input type="number" step="1" min="0" name="especificacion[real_lumenes]"
                       value="{{ old('especificacion.real_lumenes', $esp->real_lumenes ?? '') }}"
                       placeholder="Ej: 1850"
                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
                <span class="absolute right-3 top-2 text-xs text-gray-400">lm</span>
            </div>
        </div>

        {{-- Eficacia luminosa --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Eficacia luminosa</label>
            <div class="relative">
                <input type="number" step="0.01" min="0" name="especificacion[eficacia_luminosa]"
                       value="{{ old('especificacion.eficacia_luminosa', $esp->eficacia_luminosa ?? '') }}"
                       placeholder="Ej: 100"
                       class="w-full px-3 py-2 pr-14 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
                <span class="absolute right-3 top-2 text-xs text-gray-400">lm/W</span>
            </div>
        </div>

        {{-- Lúmenes texto --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Lúmenes (descriptor)</label>
            <input type="text" name="especificacion[lumenes]"
                   value="{{ old('especificacion.lumenes', $esp->lumenes ?? '') }}"
                   placeholder="Ej: 1800lm"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
    DIMENSIONES
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mb-8" x-data="{ open: true }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between text-left text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200 mb-4">
        <span><i class="fas fa-ruler-combined mr-2 text-[#2B2E2C]"></i>Dimensiones <span class="text-sm font-normal text-gray-400">(en mm)</span></span>
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
    $usosActuales = old('clasificacion.usos',
        isset($inst?->usos) ? (is_array($inst->usos) ? $inst->usos : (json_decode($inst->usos, true) ?? [])) : []
    );
    $ambientesActuales = old('clasificacion.ambientes',
        isset($inst?->ambientes) ? (is_array($inst->ambientes) ? $inst->ambientes : (json_decode($inst->ambientes, true) ?? [])) : []
    );
    $tipoInstalacionActual = old('clasificacion.tipo_instalacion',
        isset($inst?->tipo_instalacion) ? (is_array($inst->tipo_instalacion) ? $inst->tipo_instalacion : (json_decode($inst->tipo_instalacion, true) ?? [])) : []
    );
    $estilosActuales = old('clasificacion.estilo',
        isset($inst?->estilo) ? (is_array($inst->estilo) ? $inst->estilo : (json_decode($inst->estilo, true) ?? [])) : []
    );
    $tiposProyectoAsignados = old('tipo_proyecto_ids',
        isset($producto) ? $producto->tiposProyecto->pluck('id')->toArray() : []
    );
    if (!is_array($usosActuales))            $usosActuales           = [];
    if (!is_array($ambientesActuales))       $ambientesActuales      = [];
    if (!is_array($tipoInstalacionActual))   $tipoInstalacionActual  = [];
    if (!is_array($estilosActuales))         $estilosActuales        = [];
    if (!is_array($tiposProyectoAsignados))  $tiposProyectoAsignados = [];

    // Mapa tipo_proyecto_id → ambientes seleccionados (para el panel Alpine)
    $ambientesActualesInt = array_map('intval', $ambientesActuales);
@endphp

<div class="mb-8" x-data="{ open: true }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between text-left text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200 mb-4">
        <span><i class="fas fa-tags mr-2 text-[#2B2E2C]"></i>Clasificación de Uso</span>
        <i class="fas fa-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open" x-transition class="space-y-6">

        {{-- ── 1. Usos del producto ──────────────────────────────────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">
                <i class="fas fa-map-marker-alt mr-1" style="color:#2B2E2C;"></i>
                Usos del producto
                <span class="text-gray-400 font-normal">(selección múltiple)</span>
            </label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach(\App\Models\Luminaria\ProductoClasificacion::USOS_PRODUCTO as $val => $lbl)
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:border-yellow-400 transition-colors
                              {{ in_array($val, $usosActuales) ? 'border-yellow-400 bg-yellow-50' : '' }}">
                    <input type="checkbox"
                           name="clasificacion[usos][]"
                           value="{{ $val }}"
                           {{ in_array($val, $usosActuales) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300" style="accent-color:#F7D600;">
                    <span class="text-sm font-medium text-gray-700">{{ $lbl }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- ── 2. Tipo de Instalación ───────────────────────────────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">
                <i class="fas fa-wrench mr-1" style="color:#2B2E2C;"></i>
                Tipo de Instalación
                <span class="text-gray-400 font-normal">(selección múltiple)</span>
            </label>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                @foreach(\App\Models\Luminaria\ProductoClasificacion::TIPOS_INSTALACION as $val => $lbl)
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:border-yellow-400 transition-colors
                              {{ in_array($val, $tipoInstalacionActual) ? 'border-yellow-400 bg-yellow-50' : '' }}">
                    <input type="checkbox"
                           name="clasificacion[tipo_instalacion][]"
                           value="{{ $val }}"
                           {{ in_array($val, $tipoInstalacionActual) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300" style="accent-color:#F7D600;">
                    <span class="text-sm text-gray-700">{{ $lbl }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- ── 3. Estilo / Línea de Diseño ─────────────────────────────────── --}}
        <div x-data="tagInput({ initial: @js($estilosActuales) })">
            <label class="block text-xs font-semibold text-gray-600 mb-2">
                <i class="fas fa-palette mr-1" style="color:#2B2E2C;"></i>
                Estilo / Línea de Diseño
                <span class="text-gray-400 font-normal">(selección múltiple)</span>
            </label>
            <div class="flex flex-wrap gap-1 mb-2">
                @foreach(\App\Models\Luminaria\ProductoClasificacion::ESTILOS_SUGERIDOS as $sug)
                <button type="button" @click="toggle('{{ $sug }}')"
                        :class="tags.includes('{{ $sug }}') ? 'text-white' : 'bg-white text-gray-600 border-gray-300 hover:border-yellow-400'"
                        :style="tags.includes('{{ $sug }}') ? 'background:#2B2E2C; border-color:#2B2E2C;' : ''"
                        class="text-xs border rounded-full px-3 py-1 transition-colors">
                    {{ $sug }}
                </button>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-1 min-h-10 border border-gray-300 rounded-lg px-3 py-2 bg-white focus-within:ring-2 focus-within:ring-yellow-300 cursor-text"
                 @click="$refs.tagInput.focus()">
                <template x-for="(tag, i) in tags" :key="i">
                    <span class="inline-flex items-center gap-1 text-xs rounded-full px-2 py-0.5" style="background:#F7D600; color:#2B2E2C;">
                        <span x-text="tag"></span>
                        <input type="hidden" :name="'clasificacion[estilo][]'" :value="tag">
                        <button type="button" @click.stop="remove(i)" class="font-bold hover:text-red-600">&times;</button>
                    </span>
                </template>
                <input type="text" x-ref="tagInput"
                       placeholder="Escribir y Enter para agregar..."
                       @keydown.enter.prevent="addFromInput($event.target)"
                       @keydown.backspace="backspace($event)"
                       class="flex-1 min-w-32 text-sm border-none outline-none bg-transparent py-0.5">
            </div>
            <p class="text-xs text-gray-400 mt-1">Selecciona de las sugerencias o escribe un estilo y presiona Enter</p>
        </div>

        {{-- ── 4. Tipo de Proyecto + Ambientes (Alpine dinámico) ───────────── --}}
        <div x-data="{
                proyectosSeleccionados: @js($tiposProyectoAsignados),
                ambientesSeleccionados: @js($ambientesActualesInt),
                toggleProyecto(id) {
                    const idx = this.proyectosSeleccionados.indexOf(id);
                    if (idx === -1) this.proyectosSeleccionados.push(id);
                    else {
                        this.proyectosSeleccionados.splice(idx, 1);
                        // Quitar ambientes del proyecto deseleccionado
                        const ambientesDelTipo = this.getAmbientesDeProyecto(id);
                        this.ambientesSeleccionados = this.ambientesSeleccionados.filter(a => !ambientesDelTipo.includes(a));
                    }
                },
                getAmbientesDeProyecto(id) {
                    const mapa = @js($tiposProyecto->mapWithKeys(fn($tp) => [$tp->id => $tp->espacios->pluck('id')]));
                    return mapa[id] ?? [];
                },
                toggleAmbiente(id) {
                    const idx = this.ambientesSeleccionados.indexOf(id);
                    if (idx === -1) this.ambientesSeleccionados.push(id);
                    else this.ambientesSeleccionados.splice(idx, 1);
                }
            }">

            <label class="block text-xs font-semibold text-gray-600 mb-2">
                <i class="fas fa-building mr-1" style="color:#2B2E2C;"></i>
                Tipo de Proyecto
                <span class="text-gray-400 font-normal">(selección múltiple)</span>
            </label>

            {{-- Checkboxes de tipos de proyecto --}}
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-2 mb-4">
                @foreach($tiposProyecto as $tp)
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 cursor-pointer transition-colors"
                       :class="proyectosSeleccionados.includes({{ $tp->id }}) ? 'border-yellow-400 bg-yellow-50' : 'hover:border-yellow-300'"
                       @click.prevent="toggleProyecto({{ $tp->id }})">
                    <input type="checkbox"
                           name="tipo_proyecto_ids[]"
                           value="{{ $tp->id }}"
                           :checked="proyectosSeleccionados.includes({{ $tp->id }})"
                           class="w-4 h-4 rounded border-gray-300 pointer-events-none" style="accent-color:#F7D600;">
                    <span class="text-sm text-gray-700">{{ $tp->nombre }}</span>
                </label>
                @endforeach
            </div>

            {{-- Ambientes dinámicos por tipo seleccionado --}}
            @foreach($tiposProyecto as $tp)
            @if($tp->espacios->isNotEmpty())
            <div x-show="proyectosSeleccionados.includes({{ $tp->id }})"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mb-3 bg-gray-50 border border-gray-200 rounded-xl p-4">
                <p class="text-xs font-bold text-gray-700 mb-2 flex items-center gap-2">
                    <i class="fas fa-map-pin text-yellow-500"></i>
                    Ambientes — {{ $tp->nombre }}
                </p>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                    @foreach($tp->espacios as $espacio)
                    <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 cursor-pointer transition-colors text-xs"
                           :class="ambientesSeleccionados.includes({{ $espacio->id }}) ? 'border-yellow-400 bg-yellow-50' : 'hover:border-gray-300'"
                           @click.prevent="toggleAmbiente({{ $espacio->id }})">
                        <input type="checkbox"
                               name="clasificacion[ambientes][]"
                               value="{{ $espacio->id }}"
                               :checked="ambientesSeleccionados.includes({{ $espacio->id }})"
                               class="w-3.5 h-3.5 rounded border-gray-300 pointer-events-none" style="accent-color:#F7D600;">
                        <span class="text-gray-700">{{ $espacio->nombre }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach

            {{-- Hidden inputs para ambientes seleccionados (Alpine los controla arriba vía :checked) --}}
        </div>

    </div>
</div>

{{-- Alpine component: tag input --}}
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
            if (e.target.value === '' && this.tags.length > 0) this.tags.pop();
        },
    };
}
</script>
