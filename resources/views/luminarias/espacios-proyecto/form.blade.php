{{-- Partial reutilizado en create y edit --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Proyecto <span class="text-red-500">*</span></label>
        <select name="tipo_proyecto_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400" required>
            <option value="">Seleccione...</option>
            @foreach($tipos as $tipo)
                <option value="{{ $tipo->id }}"
                    {{ old('tipo_proyecto_id', $espacio->tipo_proyecto_id ?? '') == $tipo->id ? 'selected' : '' }}>
                    {{ $tipo->nombre }}
                </option>
            @endforeach
        </select>
        @error('tipo_proyecto_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Espacio <span class="text-red-500">*</span></label>
        <input type="text" name="nombre" value="{{ old('nombre', $espacio->nombre ?? '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400"
               placeholder="Ej: Sala, Lobby, Aula" required>
        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="activo" value="1"
                   {{ old('activo', $espacio->activo ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 text-yellow-500 rounded">
            <span class="text-sm text-gray-700">Activo</span>
        </label>
    </div>
</div>
