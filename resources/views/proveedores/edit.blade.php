<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8" x-data="{ tipo: '{{ old('supplier_type', $proveedor->supplier_type) }}' }">
    <x-header title="Editar Proveedor" subtitle="{{ $proveedor->razon_social }}" />

    <div class="max-w-5xl mx-auto">
        <form action="{{ route('proveedores.update', $proveedor) }}" method="POST">
            @csrf @method('PUT')

            {{-- Sección 1: Datos base --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
                <div class="bg-[#2B2E2C] px-6 py-3">
                    <h3 class="text-sm font-bold text-white"><i class="fas fa-building mr-2"></i>Información General</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Tipo --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Tipo de Proveedor <span class="text-red-500">*</span></label>
                        <div class="flex gap-4 flex-wrap">
                            @foreach(\App\Models\Proveedor::TIPOS as $val => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="supplier_type" value="{{ $val }}"
                                           x-model="tipo"
                                           {{ old('supplier_type', $proveedor->supplier_type) === $val ? 'checked' : '' }}
                                           class="accent-[#2B2E2C]">
                                    <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="tipo === 'nacional'">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">RUC</label>
                        <input type="text" name="ruc" maxlength="11"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600] @error('ruc') border-red-400 @enderror"
                               value="{{ old('ruc', $proveedor->ruc) }}">
                        @error('ruc') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Razón Social <span class="text-red-500">*</span></label>
                        <input type="text" name="razon_social" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600] @error('razon_social') border-red-400 @enderror"
                               value="{{ old('razon_social', $proveedor->razon_social) }}">
                        @error('razon_social') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nombre Comercial</label>
                        <input type="text" name="nombre_comercial"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('nombre_comercial', $proveedor->nombre_comercial) }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Persona(s) de Contacto</label>
                        <input type="text" name="contacto_nombre"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('contacto_nombre', $proveedor->contacto_nombre) }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Teléfono(s)</label>
                        <input type="text" name="telefono"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('telefono', $proveedor->telefono) }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
                        <input type="email" name="email"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('email', $proveedor->email) }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Sitio Web</label>
                        <input type="url" name="website" placeholder="https://"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('website', $proveedor->website) }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Catálogo</label>
                        <input type="text" name="catalog_url"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('catalog_url', $proveedor->catalog_url) }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nivel de Precio</label>
                        <select name="price_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                            <option value="">— Seleccionar —</option>
                            @foreach(\App\Models\Proveedor::PRICE_LEVELS as $val => $label)
                                <option value="{{ $val }}" {{ old('price_level', $proveedor->price_level) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nivel de Calidad</label>
                        <select name="quality_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                            <option value="">— Seleccionar —</option>
                            @foreach(\App\Models\Proveedor::QUALITY_LEVELS as $val => $label)
                                <option value="{{ $val }}" {{ old('quality_level', $proveedor->quality_level) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Estado <span class="text-red-500">*</span></label>
                        <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                            <option value="activo"   {{ old('estado', $proveedor->estado) === 'activo'   ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ old('estado', $proveedor->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Sección 2: Ubicación --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
                <div class="bg-[#2B2E2C] px-6 py-3">
                    <h3 class="text-sm font-bold text-white"><i class="fas fa-map-marker-alt mr-2"></i>Ubicación</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Dirección Fiscal / Oficina</label>
                        <input type="text" name="direccion"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('direccion', $proveedor->direccion) }}">
                    </div>
                    <div x-show="tipo !== 'nacional'">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Dirección de Fábrica</label>
                        <input type="text" name="factory_address"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('factory_address', $proveedor->factory_address) }}">
                    </div>
                    <div x-show="tipo !== 'nacional'">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">País</label>
                        <input type="text" name="country"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('country', $proveedor->country) }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Distrito</label>
                        <input type="text" name="district"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('district', $proveedor->district) }}">
                    </div>
                    <div x-show="tipo === 'extranjero'">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Puerto</label>
                        <input type="text" name="port"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('port', $proveedor->port) }}">
                    </div>
                </div>
            </div>

            {{-- Sección 3: Comercial --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
                <div class="bg-[#2B2E2C] px-6 py-3">
                    <h3 class="text-sm font-bold text-white"><i class="fas fa-handshake mr-2"></i>Datos Comerciales</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">MOQ</label>
                        <input type="text" name="moq"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('moq', $proveedor->moq) }}">
                    </div>
                    <div x-show="tipo === 'importacion'">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Datos Bancarios</label>
                        <input type="text" name="bank_detail"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('bank_detail', $proveedor->bank_detail) }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Observaciones</label>
                        <textarea name="observations" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">{{ old('observations', $proveedor->observations) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Sección 4: Categorías --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
                <div class="bg-[#2B2E2C] px-6 py-3">
                    <h3 class="text-sm font-bold text-white"><i class="fas fa-tags mr-2"></i>Categorías de Producto</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($categorias as $cat => $subs)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <p class="text-xs font-bold text-gray-700 uppercase mb-3">{{ $cat }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($subs as $sub)
                                        @php $key = $cat . ':' . $sub @endphp
                                        <label class="flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox" name="categorias[]" value="{{ $key }}"
                                                   {{ in_array($key, old('categorias', $selCategorias)) ? 'checked' : '' }}
                                                   class="accent-[#2B2E2C]">
                                            <span class="text-xs text-gray-600">{{ $sub }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Sección 5: Certificaciones --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="bg-[#2B2E2C] px-6 py-3">
                    <h3 class="text-sm font-bold text-white"><i class="fas fa-certificate mr-2"></i>Certificaciones</h3>
                </div>
                <div class="p-6 flex gap-6 flex-wrap">
                    @foreach(\App\Models\ProveedorCertificacion::TIPOS as $val => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="certificaciones[]" value="{{ $val }}"
                                   {{ in_array($val, old('certificaciones', $selCertificaciones)) ? 'checked' : '' }}
                                   class="accent-[#2B2E2C]">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('proveedores.show', $proveedor) }}"
                   class="px-6 py-2.5 border-2 border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 text-sm">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-[#2B2E2C] text-white rounded-lg font-semibold hover:bg-[#3A3E3B] text-sm">
                    <i class="fas fa-save mr-1"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
