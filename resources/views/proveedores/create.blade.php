<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Proveedor</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8" x-data="proveedorForm('{{ old('supplier_type','nacional') }}')">
    <x-header title="Nuevo Proveedor" subtitle="Registra un nuevo proveedor en el sistema" />

    <div class="max-w-5xl mx-auto">

        {{-- Búsqueda SUNAT (solo nacionales con RUC) --}}
        <div x-show="tipo === 'nacional'" class="bg-white rounded-xl shadow-sm mb-4 overflow-hidden">
            <div class="bg-gradient-to-r from-[#2B2E2C] to-[#3A3E3B] px-6 py-3">
                <h3 class="text-sm font-bold text-white"><i class="fas fa-search mr-2"></i>Búsqueda rápida por RUC (SUNAT)</h3>
            </div>
            <div class="p-4 flex gap-3">
                <input type="text" x-model="rucBuscar" maxlength="11" placeholder="RUC de 11 dígitos"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                <button type="button" @click="consultarSunat()" :disabled="cargando"
                        class="bg-[#2B2E2C] text-white px-5 py-2 rounded-lg text-sm font-semibold disabled:opacity-50">
                    <span x-show="!cargando"><i class="fas fa-search mr-1"></i>Buscar</span>
                    <span x-show="cargando"><i class="fas fa-spinner fa-spin mr-1"></i>...</span>
                </button>
            </div>
            <div x-show="mensajeSunat" class="px-4 pb-3">
                <p x-text="mensajeSunat" class="text-sm px-3 py-1.5 rounded"
                   :class="sunatExito ? 'text-green-700 bg-green-50 border border-green-200' : 'text-red-700 bg-red-50 border border-red-200'"></p>
            </div>
        </div>

        <form action="{{ route('proveedores.store') }}" method="POST">
            @csrf

            {{-- Sección 1: Tipo + Datos base --}}
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
                                @php $colors = ['nacional'=>'green','extranjero'=>'blue','importacion'=>'purple'] @endphp
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="supplier_type" value="{{ $val }}"
                                           x-model="tipo"
                                           {{ old('supplier_type','nacional') === $val ? 'checked' : '' }}
                                           class="accent-[#2B2E2C]">
                                    <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('supplier_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- RUC (solo nacional) --}}
                    <div x-show="tipo === 'nacional'">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">RUC</label>
                        <input type="text" name="ruc" x-model="ruc" maxlength="11" placeholder="11 dígitos"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600] @error('ruc') border-red-400 @enderror"
                               value="{{ old('ruc') }}">
                        @error('ruc') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Razón Social / Nombre Real <span class="text-red-500">*</span></label>
                        <input type="text" name="razon_social" x-model="razonSocial" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600] @error('razon_social') border-red-400 @enderror"
                               value="{{ old('razon_social') }}">
                        @error('razon_social') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nombre Comercial</label>
                        <input type="text" name="nombre_comercial" x-model="nombreComercial"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('nombre_comercial') }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Persona(s) de Contacto</label>
                        <input type="text" name="contacto_nombre" placeholder="Nombre | Nombre2"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('contacto_nombre') }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Teléfono(s)</label>
                        <input type="text" name="telefono" placeholder="+51 999 888 777 | 01-234-5678"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('telefono') }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
                        <input type="email" name="email"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600] @error('email') border-red-400 @enderror"
                               value="{{ old('email') }}">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Sitio Web</label>
                        <input type="url" name="website" placeholder="https://"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('website') }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Catálogo</label>
                        <input type="text" name="catalog_url" placeholder="URL o nombre del catálogo"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('catalog_url') }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nivel de Precio</label>
                        <select name="price_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                            <option value="">— Seleccionar —</option>
                            @foreach(\App\Models\Proveedor::PRICE_LEVELS as $val => $label)
                                <option value="{{ $val }}" {{ old('price_level') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nivel de Calidad</label>
                        <select name="quality_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                            <option value="">— Seleccionar —</option>
                            @foreach(\App\Models\Proveedor::QUALITY_LEVELS as $val => $label)
                                <option value="{{ $val }}" {{ old('quality_level') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Estado <span class="text-red-500">*</span></label>
                        <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                            <option value="activo"   {{ old('estado','activo') === 'activo'   ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ old('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Sección 2: Dirección / Ubicación --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
                <div class="bg-[#2B2E2C] px-6 py-3">
                    <h3 class="text-sm font-bold text-white"><i class="fas fa-map-marker-alt mr-2"></i>Ubicación</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Dirección Fiscal / Oficina</label>
                        <input type="text" name="direccion"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('direccion') }}">
                    </div>
                    <div x-show="tipo !== 'nacional'">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Dirección de Fábrica</label>
                        <input type="text" name="factory_address"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('factory_address') }}">
                    </div>
                    <div x-show="tipo !== 'nacional'">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">País <span x-show="tipo==='extranjero'" class="text-red-400">*</span></label>
                        <input type="text" name="country"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('country') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Distrito <span x-show="tipo==='nacional'" class="text-red-400">*</span></label>
                        <input type="text" name="district"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('district') }}">
                    </div>
                    <div x-show="tipo === 'extranjero'">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Puerto</label>
                        <input type="text" name="port" placeholder="ej: Puerto del Callao"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('port') }}">
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
                        <label class="block text-xs font-semibold text-gray-600 mb-1">MOQ (Mínimo de Pedido)</label>
                        <input type="text" name="moq" placeholder="ej: 500 units"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('moq') }}">
                    </div>
                    <div x-show="tipo === 'importacion'">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Datos Bancarios</label>
                        <input type="text" name="bank_detail" placeholder="Banco, cuenta, SWIFT..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                               value="{{ old('bank_detail') }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Observaciones</label>
                        <textarea name="observations" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]"
                                  placeholder="Notas adicionales...">{{ old('observations') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Sección 4: Categorías de Producto --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
                <div class="bg-[#2B2E2C] px-6 py-3">
                    <h3 class="text-sm font-bold text-white"><i class="fas fa-tags mr-2"></i>Categorías de Producto</h3>
                </div>
                <div class="p-6">
                    @php $oldCats = old('categorias', []) @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($categorias as $cat => $subs)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <p class="text-xs font-bold text-gray-700 uppercase mb-3">{{ $cat }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($subs as $sub)
                                        @php $key = $cat . ':' . $sub @endphp
                                        <label class="flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox" name="categorias[]" value="{{ $key }}"
                                                   {{ in_array($key, $oldCats) ? 'checked' : '' }}
                                                   class="accent-[#2B2E2C] rounded">
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
                                   {{ in_array($val, old('certificaciones',[])) ? 'checked' : '' }}
                                   class="accent-[#2B2E2C]">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('proveedores.index') }}"
                   class="px-6 py-2.5 border-2 border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 text-sm">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-[#2B2E2C] text-white rounded-lg font-semibold hover:bg-[#3A3E3B] text-sm">
                    <i class="fas fa-save mr-1"></i>Guardar Proveedor
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function proveedorForm(tipoInicial) {
    return {
        tipo: tipoInicial,
        rucBuscar: '',
        ruc: '{{ old('ruc') }}',
        razonSocial: '{{ old('razon_social') }}',
        nombreComercial: '{{ old('nombre_comercial') }}',
        cargando: false,
        mensajeSunat: '',
        sunatExito: false,

        async consultarSunat() {
            const ruc = this.rucBuscar.trim();
            if (!/^\d{11}$/.test(ruc)) {
                this.mensajeSunat = 'El RUC debe tener 11 dígitos numéricos.';
                this.sunatExito = false;
                return;
            }
            this.cargando = true;
            this.mensajeSunat = '';
            try {
                const resp = await fetch('{{ route("proveedores.consultar-sunat") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ ruc })
                });
                const data = await resp.json();
                if (data.success) {
                    this.ruc             = data.data.ruc;
                    this.razonSocial     = data.data.razon_social;
                    this.nombreComercial = data.data.nombre_comercial || '';
                    document.querySelector('[name="ruc"]').value              = this.ruc;
                    document.querySelector('[name="razon_social"]').value     = this.razonSocial;
                    document.querySelector('[name="nombre_comercial"]').value = this.nombreComercial;
                    this.mensajeSunat = '✓ Datos encontrados en SUNAT';
                    this.sunatExito   = true;
                } else {
                    this.mensajeSunat = data.message || 'RUC no encontrado.';
                    this.sunatExito   = false;
                }
            } catch (e) {
                this.mensajeSunat = 'Error de conexión.';
                this.sunatExito   = false;
            }
            this.cargando = false;
        }
    }
}
</script>
</body>
</html>
