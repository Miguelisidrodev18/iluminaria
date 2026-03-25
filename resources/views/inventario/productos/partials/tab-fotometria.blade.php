{{--
    Partial: Tab Fotometría
    Variables: $producto
--}}
@php $esp = $producto->especificacion ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

    {{-- Tonalidad de luz (categoría) --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Tonalidad de luz</label>
        <select name="especificacion[tonalidad_luz]"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
            <option value="">— Sin especificar —</option>
            @foreach(['Cálido','Neutro','Frío','Bicolor','Multicolor'] as $t)
            <option value="{{ $t }}"
                    {{ old('especificacion.tonalidad_luz', $esp?->tonalidad_luz ?? '') === $t ? 'selected' : '' }}>
                {{ $t }}
            </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-400 mt-1">Cálido ≈ 2700–3000K · Neutro ≈ 4000K · Frío ≈ 5000–6500K</p>
    </div>

    {{-- Temperatura de color específica --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">T° de color específica (K)</label>
        <input type="text" name="especificacion[temperatura_color]"
               value="{{ old('especificacion.temperatura_color', $esp?->temperatura_color ?? '') }}"
               placeholder="Ej: 3000K, 4000K, 6500K"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
    </div>

    {{-- CRI --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">CRI (0–100)</label>
        <input type="number" name="especificacion[cri]" min="0" max="100"
               value="{{ old('especificacion.cri', $esp?->cri ?? '') }}"
               placeholder="Ej: 80, 90"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
    </div>

    {{-- Lúmenes nominales --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">
            Lúmenes nominales
            <span class="font-normal text-gray-400">(catálogo fabricante)</span>
        </label>
        <div class="relative">
            <input type="number" step="1" min="0" name="especificacion[nominal_lumenes]"
                   value="{{ old('especificacion.nominal_lumenes', $esp?->nominal_lumenes ?? '') }}"
                   placeholder="Ej: 2000"
                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
            <span class="absolute right-3 top-2 text-xs text-gray-400">lm</span>
        </div>
    </div>

    {{-- Lúmenes reales --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">
            Lúmenes reales
            <span class="font-normal text-gray-400">(medición laboratorio)</span>
        </label>
        <div class="relative">
            <input type="number" step="1" min="0" name="especificacion[real_lumenes]"
                   value="{{ old('especificacion.real_lumenes', $esp?->real_lumenes ?? '') }}"
                   placeholder="Ej: 1850"
                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
            <span class="absolute right-3 top-2 text-xs text-gray-400">lm</span>
        </div>
    </div>

    {{-- Eficacia luminosa --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">
            Eficacia luminosa
            <span class="font-normal text-gray-400">(W_lumenes del Excel)</span>
        </label>
        <div class="relative">
            <input type="number" step="0.01" min="0" name="especificacion[eficacia_luminosa]"
                   value="{{ old('especificacion.eficacia_luminosa', $esp?->eficacia_luminosa ?? '') }}"
                   placeholder="Ej: 100"
                   class="w-full px-3 py-2 pr-14 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
            <span class="absolute right-3 top-2 text-xs text-gray-400">lm/W</span>
        </div>
    </div>

    {{-- Lúmenes (campo legacy -- por compatibilidad) --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">
            Lúmenes (descriptor)
            <span class="font-normal text-gray-400">(texto libre, ej: "1800lm")</span>
        </label>
        <input type="text" name="especificacion[lumenes]"
               value="{{ old('especificacion.lumenes', $esp?->lumenes ?? '') }}"
               placeholder="Ej: 1800lm"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
    </div>

</div>
