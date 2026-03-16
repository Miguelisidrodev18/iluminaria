{{-- Partial reutilizado en create y edit --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
        <input type="text" name="nombre" value="{{ old('nombre', $tipo->nombre ?? '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400"
               placeholder="Ej: Residencial" required>
        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Icono <span class="text-gray-400 text-xs">(nombre Font Awesome sin "fa-")</span></label>
        <input type="text" name="icono" value="{{ old('icono', $tipo->icono ?? '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400"
               placeholder="Ej: home, store, hotel">
        @error('icono')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center pt-6">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="activo" value="1"
                   {{ old('activo', $tipo->activo ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 text-yellow-500 rounded">
            <span class="text-sm text-gray-700">Activo</span>
        </label>
    </div>
</div>
