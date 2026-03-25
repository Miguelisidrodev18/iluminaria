{{--
    Partial: Tab Embalaje y Logística
    Variables: $producto
--}}
@php $emb = $producto->embalaje ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

    {{-- Peso --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Peso del producto</label>
        <div class="relative">
            <input type="number" step="0.001" min="0" name="embalaje[peso]"
                   value="{{ old('embalaje.peso', $emb?->peso ?? '') }}"
                   placeholder="0.000"
                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-400">
            <span class="absolute right-3 top-2 text-xs text-gray-400">kg</span>
        </div>
    </div>

    {{-- Volumen --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Volumen del embalaje</label>
        <div class="relative">
            <input type="number" step="0.001" min="0" name="embalaje[volumen]"
                   value="{{ old('embalaje.volumen', $emb?->volumen ?? '') }}"
                   placeholder="0.000"
                   class="w-full px-3 py-2 pr-14 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-400">
            <span class="absolute right-3 top-2 text-xs text-gray-400">cm³</span>
        </div>
    </div>

    {{-- Cantidad por caja --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Unidades por caja</label>
        <input type="number" step="1" min="1" name="embalaje[cantidad_por_caja]"
               value="{{ old('embalaje.cantidad_por_caja', $emb?->cantidad_por_caja ?? '') }}"
               placeholder="Ej: 4, 12"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-400">
    </div>

    {{-- Medida de embalaje --}}
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-gray-600 mb-1">Medidas del embalaje</label>
        <input type="text" name="embalaje[medida_embalaje]"
               value="{{ old('embalaje.medida_embalaje', $emb?->medida_embalaje ?? '') }}"
               placeholder="Ej: 62x62x10 cm"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-400">
    </div>

    {{-- Embalado (checkbox) --}}
    <div class="flex items-end pb-1">
        <label class="flex items-center gap-2 cursor-pointer bg-teal-50 border border-teal-200 rounded-lg px-4 py-2 w-full">
            <input type="checkbox" name="embalaje[embalado]" value="1"
                   {{ old('embalaje.embalado', $emb?->embalado ?? false) ? 'checked' : '' }}
                   class="w-4 h-4 text-teal-500 rounded">
            <span class="text-sm text-gray-700 font-medium">Incluye embalaje individual</span>
        </label>
    </div>

</div>
