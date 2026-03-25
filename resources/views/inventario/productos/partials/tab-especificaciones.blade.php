{{--
    Partial: Tab Especificaciones Técnicas
    Variables: $producto
--}}
@php $esp = $producto->especificacion ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

    {{-- Tipo de fuente --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo de fuente</label>
        <select name="especificacion[tipo_fuente]"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
            <option value="">— Sin especificar —</option>
            @foreach(['LED','Fluorescente','Halógena','HID','Incandescente','Fibra óptica'] as $tf)
            <option value="{{ $tf }}"
                    {{ old('especificacion.tipo_fuente', $esp?->tipo_fuente ?? '') === $tf ? 'selected' : '' }}>
                {{ $tf }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- Tipo de salida de luz --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Salida de luz</label>
        <select name="especificacion[salida_luz]"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
            <option value="">— Sin especificar —</option>
            @foreach(['Directa','Indirecta','Mixta','Difusa'] as $sl)
            <option value="{{ $sl }}"
                    {{ old('especificacion.salida_luz', $esp?->salida_luz ?? '') === $sl ? 'selected' : '' }}>
                {{ $sl }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- Nivel de potencia --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Nivel de potencia</label>
        <select name="especificacion[nivel_potencia]"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
            <option value="">— Sin especificar —</option>
            @foreach(['Baja (0–10W)','Media (11–30W)','Alta (31W+)'] as $np)
            <option value="{{ $np }}"
                    {{ old('especificacion.nivel_potencia', $esp?->nivel_potencia ?? '') === $np ? 'selected' : '' }}>
                {{ $np }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- Potencia --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Potencia (W)</label>
        <input type="text" name="especificacion[potencia]"
               value="{{ old('especificacion.potencia', $esp?->potencia ?? '') }}"
               placeholder="Ej: 18W, 2×36W"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
    </div>

    {{-- Voltaje --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Voltaje</label>
        <input type="text" name="especificacion[voltaje]"
               value="{{ old('especificacion.voltaje', $esp?->voltaje ?? '') }}"
               placeholder="Ej: 220V, 100-240V"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
    </div>

    {{-- Protección IP --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Protección IP</label>
        <input type="text" name="especificacion[ip]"
               value="{{ old('especificacion.ip', $esp?->ip ?? '') }}"
               placeholder="Ej: IP65, IP20"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
    </div>

    {{-- Protección IK --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Protección IK</label>
        <input type="text" name="especificacion[ik]"
               value="{{ old('especificacion.ik', $esp?->ik ?? '') }}"
               placeholder="Ej: IK08"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
    </div>

    {{-- Ángulo de apertura --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Ángulo de apertura</label>
        <input type="text" name="especificacion[angulo_apertura]"
               value="{{ old('especificacion.angulo_apertura', $esp?->angulo_apertura ?? '') }}"
               placeholder="Ej: 36°, 120°"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
    </div>

    {{-- Driver --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Driver</label>
        <select name="especificacion[driver]"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
            <option value="">— Sin especificar —</option>
            <option value="incluido" {{ old('especificacion.driver', $esp?->driver ?? '') === 'incluido' ? 'selected' : '' }}>Incluido</option>
            <option value="no_incluido" {{ old('especificacion.driver', $esp?->driver ?? '') === 'no_incluido' ? 'selected' : '' }}>No incluido</option>
            <option value="externo_meanwell" {{ old('especificacion.driver', $esp?->driver ?? '') === 'externo_meanwell' ? 'selected' : '' }}>Externo Meanwell</option>
        </select>
    </div>

    {{-- Socket --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Socket / Casquillo</label>
        <input type="text" name="especificacion[socket]"
               value="{{ old('especificacion.socket', $esp?->socket ?? '') }}"
               placeholder="Ej: E27, GU10, G13"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
    </div>

    {{-- Número de lámparas --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Nº de lámparas</label>
        <input type="number" name="especificacion[numero_lamparas]" min="1"
               value="{{ old('especificacion.numero_lamparas', $esp?->numero_lamparas ?? '') }}"
               placeholder="Ej: 1, 2"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
    </div>

    {{-- Vida útil --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Vida útil (horas)</label>
        <input type="number" name="especificacion[vida_util_horas]" min="0" step="500"
               value="{{ old('especificacion.vida_util_horas', $esp?->vida_util_horas ?? '') }}"
               placeholder="Ej: 25000, 50000"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
    </div>

    {{-- Protocolo de regulación --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Protocolo de regulación</label>
        <input type="text" name="especificacion[protocolo_regulacion]"
               value="{{ old('especificacion.protocolo_regulacion', $esp?->protocolo_regulacion ?? '') }}"
               placeholder="Ej: 0-10V, DALI, Triac"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">
    </div>

    {{-- Regulable --}}
    <div class="flex items-end pb-1">
        <label class="flex items-center gap-2 cursor-pointer bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-2 w-full">
            <input type="checkbox" name="especificacion[regulable]" value="1"
                   {{ old('especificacion.regulable', $esp?->regulable ?? false) ? 'checked' : '' }}
                   class="w-4 h-4 text-yellow-500 rounded">
            <span class="text-sm text-gray-700 font-medium">Regulable (Dimeable)</span>
        </label>
    </div>

</div>
