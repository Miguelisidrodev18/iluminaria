{{--
    Partial: Tab Dimensiones
    Variables: $producto
--}}
@php
    $dim = $producto->dimensiones ?? null;
    $mat = $producto->materiales   ?? null;
@endphp

{{-- DIMENSIONES DEL CUERPO --}}
<div class="mb-8">
    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
        <i class="fas fa-ruler-combined text-blue-500"></i>
        Dimensiones del producto <span class="font-normal text-gray-400">(en mm)</span>
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['alto',            'Alto'],
            ['ancho',           'Ancho'],
            ['diametro',        'Diámetro'],
            ['lado',            'Lado (cuadrado)'],
            ['profundidad',     'Profundidad'],
            ['alto_suspendido', 'Alto suspendido'],
        ] as [$campo, $label])
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
            <div class="relative">
                <input type="number" step="0.01" min="0"
                       name="dimensiones[{{ $campo }}]"
                       value="{{ old('dimensiones.' . $campo, $dim?->$campo ?? '') }}"
                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400">
                <span class="absolute right-3 top-2 text-xs text-gray-400">mm</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- DIMENSIONES DEL AGUJERO / CORTE DE INSTALACIÓN --}}
<div class="mb-8">
    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
        <i class="fas fa-circle-notch text-orange-400"></i>
        Corte / Agujero de instalación
        <span class="font-normal text-gray-400">(para empotrables)</span>
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Diám. agujero circular</label>
            <div class="relative">
                <input type="number" step="0.01" min="0"
                       name="dimensiones[diametro_agujero]"
                       value="{{ old('dimensiones.diametro_agujero', $dim?->diametro_agujero ?? '') }}"
                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-400">
                <span class="absolute right-3 top-2 text-xs text-gray-400">mm</span>
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Ancho corte rectangular</label>
            <div class="relative">
                <input type="number" step="0.01" min="0"
                       name="dimensiones[ancho_agujero]"
                       value="{{ old('dimensiones.ancho_agujero', $dim?->ancho_agujero ?? '') }}"
                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-400">
                <span class="absolute right-3 top-2 text-xs text-gray-400">mm</span>
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Profundidad del hueco</label>
            <div class="relative">
                <input type="number" step="0.01" min="0"
                       name="dimensiones[profundidad_agujero]"
                       value="{{ old('dimensiones.profundidad_agujero', $dim?->profundidad_agujero ?? '') }}"
                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-400">
                <span class="absolute right-3 top-2 text-xs text-gray-400">mm</span>
            </div>
        </div>
    </div>
</div>

{{-- MATERIALES Y ACABADOS --}}
<div>
    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
        <i class="fas fa-layer-group text-green-600"></i>
        Materiales y Acabados
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Material principal</label>
            <input type="text" name="materiales[material_1]"
                   value="{{ old('materiales.material_1', $mat?->material_1 ?? '') }}"
                   placeholder="Ej: Aluminio fundido"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Material secundario</label>
            <input type="text" name="materiales[material_2]"
                   value="{{ old('materiales.material_2', $mat?->material_2 ?? '') }}"
                   placeholder="Ej: Vidrio templado"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Material terciario</label>
            <input type="text" name="materiales[material_terciario]"
                   value="{{ old('materiales.material_terciario', $mat?->material_terciario ?? '') }}"
                   placeholder="Ej: Policarbonato, Acrílico"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Acabado / Color 1</label>
            <input type="text" name="materiales[color_acabado_1]"
                   value="{{ old('materiales.color_acabado_1', $mat?->color_acabado_1 ?? '') }}"
                   placeholder="Ej: Blanco RAL 9003"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Acabado / Color 2</label>
            <input type="text" name="materiales[color_acabado_2]"
                   value="{{ old('materiales.color_acabado_2', $mat?->color_acabado_2 ?? '') }}"
                   placeholder="Ej: Negro mate"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Acabado / Color 3</label>
            <input type="text" name="materiales[color_acabado_3]"
                   value="{{ old('materiales.color_acabado_3', $mat?->color_acabado_3 ?? '') }}"
                   placeholder="Ej: Gris perla"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-400">
        </div>
    </div>
</div>
