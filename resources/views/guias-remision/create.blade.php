<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($guiaRemision) ? 'Editar' : 'Nueva' }} Guía de Remisión</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8" x-data="guiaForm()">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('guias-remision.index') }}"
               class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ isset($guiaRemision) ? 'Editar Guía' : 'Nueva Guía de Remisión' }}
                </h1>
                <p class="text-sm text-gray-500">Ingrese los datos de traslado según SUNAT</p>
            </div>
        </div>

        {{-- Errores --}}
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6">
                <p class="font-medium mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Corrija los errores:</p>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ isset($guiaRemision) ? route('guias-remision.update', $guiaRemision) : route('guias-remision.store') }}">
            @csrf
            @if(isset($guiaRemision)) @method('PUT') @endif

            @if($serie)
                <input type="hidden" name="serie_comprobante_id" value="{{ $serie->id }}">
            @else
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl mb-6">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    No hay una serie de Guía de Remisión (tipo 09) activa para esta sucursal.
                    <a href="{{ route('admin.sucursales.index') }}" class="underline">Configurar en Admin</a>
                </div>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                {{-- Columna izquierda (pasos 1-4) --}}
                <div class="xl:col-span-2 space-y-6">

                    {{-- 1. Cliente / Destinatario --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2 mb-4">
                            <span class="w-7 h-7 bg-[#2B2E2C] text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            Cliente / Destinatario
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente registrado</label>
                                <select name="cliente_id" x-model="clienteId" @change="cargarCliente($event.target.value)"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                                    <option value="">— Sin cliente seleccionado —</option>
                                    @foreach($clientes as $c)
                                        <option value="{{ $c->id }}"
                                                data-nombre="{{ $c->nombre }}"
                                                data-doc-tipo="{{ $c->tipo_documento === 'RUC' ? '6' : ($c->tipo_documento === 'DNI' ? '1' : '4') }}"
                                                data-doc-num="{{ $c->numero_documento }}"
                                                data-dir="{{ $c->direccion }}"
                                            {{ old('cliente_id', $guiaRemision?->cliente_id ?? '') == $c->id ? 'selected' : '' }}>
                                            {{ $c->nombre }} — {{ $c->numero_documento }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo documento destinatario</label>
                                <select name="destinatario_tipo_doc" x-model="destTipoDoc"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                                    <option value="1">DNI</option>
                                    <option value="4">Carné Extranjería</option>
                                    <option value="6">RUC</option>
                                    <option value="7">Pasaporte</option>
                                    <option value="A">C.E. Diplomático</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">N° documento</label>
                                <input type="text" name="destinatario_num_doc" x-model="destNumDoc"
                                       value="{{ old('destinatario_num_doc', $guiaRemision?->destinatario_num_doc ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20"
                                       placeholder="20100070970">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre / Razón social</label>
                                <input type="text" name="destinatario_nombre" x-model="destNombre"
                                       value="{{ old('destinatario_nombre', $guiaRemision?->destinatario_nombre ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20"
                                       placeholder="Empresa o persona destinataria">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección del destinatario</label>
                                <input type="text" name="destinatario_direccion"
                                       value="{{ old('destinatario_direccion', $guiaRemision?->destinatario_direccion ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20"
                                       placeholder="Dirección completa del destinatario">
                            </div>
                        </div>
                    </div>

                    {{-- 2. Datos de Traslado --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2 mb-4">
                            <span class="w-7 h-7 bg-[#2B2E2C] text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            Datos de Traslado
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo del traslado <span class="text-red-500">*</span></label>
                                <select name="motivo_traslado" required
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                                    @foreach(\App\Models\GuiaRemision::MOTIVOS as $key => $label)
                                        <option value="{{ $key }}" {{ old('motivo_traslado', $guiaRemision?->motivo_traslado ?? '01') == $key ? 'selected' : '' }}>
                                            {{ $key }} - {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Modalidad <span class="text-red-500">*</span></label>
                                <div class="flex gap-3">
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="modalidad_transporte" value="01" x-model="modalidad"
                                               class="sr-only peer">
                                        <div class="border-2 rounded-xl p-3 text-center text-sm font-medium transition-all
                                                    peer-checked:border-[#2B2E2C] peer-checked:bg-[#2B2E2C]/5 border-gray-200 hover:border-gray-300">
                                            <i class="fas fa-truck block mb-1 text-lg"></i> Privado
                                        </div>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="modalidad_transporte" value="02" x-model="modalidad"
                                               class="sr-only peer">
                                        <div class="border-2 rounded-xl p-3 text-center text-sm font-medium transition-all
                                                    peer-checked:border-[#2B2E2C] peer-checked:bg-[#2B2E2C]/5 border-gray-200 hover:border-gray-300">
                                            <i class="fas fa-road block mb-1 text-lg"></i> Público
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de emisión <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_emision" required
                                       value="{{ old('fecha_emision', $guiaRemision?->fecha_emision?->format('Y-m-d') ?? date('Y-m-d')) }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de traslado <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_traslado" required
                                       value="{{ old('fecha_traslado', $guiaRemision?->fecha_traslado?->format('Y-m-d') ?? date('Y-m-d')) }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Peso bruto total (KG)</label>
                                <input type="number" name="peso_bruto" step="0.001" min="0"
                                       value="{{ old('peso_bruto', $guiaRemision?->peso_bruto ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20"
                                       placeholder="0.000">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">N° de bultos</label>
                                <input type="number" name="numero_bultos" min="1" step="1"
                                       value="{{ old('numero_bultos', $guiaRemision?->numero_bultos ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20"
                                       placeholder="—">
                            </div>
                        </div>
                    </div>

                    {{-- 3. Punto de Partida --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2 mb-4">
                            <span class="w-7 h-7 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            <i class="fas fa-map-pin text-emerald-500"></i> Punto de Partida
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ubigeo</label>
                                <input type="text" name="partida_ubigeo" maxlength="6"
                                       value="{{ old('partida_ubigeo', $guiaRemision?->partida_ubigeo ?? $partidaDefecto['ubigeo']) }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 font-mono"
                                       placeholder="150101">
                                <p class="text-xs text-gray-400 mt-1">6 dígitos (SUNAT)</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección <span class="text-red-500">*</span></label>
                                <input type="text" name="partida_direccion" required
                                       value="{{ old('partida_direccion', $guiaRemision?->partida_direccion ?? $partidaDefecto['direccion']) }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300"
                                       placeholder="Dirección completa de partida">
                            </div>
                        </div>
                    </div>

                    {{-- 4. Punto de Llegada --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2 mb-4">
                            <span class="w-7 h-7 bg-red-500 text-white rounded-full flex items-center justify-center text-xs font-bold">4</span>
                            <i class="fas fa-flag text-red-500"></i> Punto de Llegada
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ubigeo</label>
                                <input type="text" name="llegada_ubigeo" maxlength="6"
                                       value="{{ old('llegada_ubigeo', $guiaRemision?->llegada_ubigeo ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 font-mono"
                                       placeholder="Ej: 150101">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección <span class="text-red-500">*</span></label>
                                <input type="text" name="llegada_direccion" required
                                       value="{{ old('llegada_direccion', $guiaRemision?->llegada_direccion ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                                       placeholder="Dirección completa de destino">
                            </div>
                        </div>
                    </div>

                    {{-- 5. Detalle de Bienes --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                                <span class="w-7 h-7 bg-[#2B2E2C] text-white rounded-full flex items-center justify-center text-xs font-bold">5</span>
                                Detalle de Bienes
                            </h2>
                            <button type="button" @click="agregarDetalle"
                                    class="inline-flex items-center gap-1.5 text-sm text-[#2B2E2C] font-medium hover:underline">
                                <i class="fas fa-plus-circle"></i> Agregar ítem
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                                        <th class="pb-3 pr-3">Producto</th>
                                        <th class="pb-3 pr-3 w-32">Unidad</th>
                                        <th class="pb-3 pr-3 w-28">Cantidad</th>
                                        <th class="pb-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, i) in detalles" :key="i">
                                        <tr class="border-b border-gray-50">
                                            <td class="py-2 pr-3">
                                                <input type="hidden" :name="`detalles[${i}][producto_id]`" x-model="item.producto_id">
                                                <input type="hidden" :name="`detalles[${i}][codigo]`" x-model="item.codigo">
                                                <select @change="seleccionarProducto(i, $event.target)"
                                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20 mb-1">
                                                    <option value="">— Producto del catálogo —</option>
                                                    @foreach($productos as $p)
                                                        <option value="{{ $p->id }}" data-nombre="{{ $p->nombre }}" data-sku="{{ $p->codigo ?? '' }}" data-unidad="{{ $p->unidadMedida->abreviatura ?? 'NIU' }}">
                                                            {{ $p->nombre }} @if($p->codigo)({{ $p->codigo }})@endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="text" :name="`detalles[${i}][descripcion]`" x-model="item.descripcion"
                                                       placeholder="Descripción del bien *" required
                                                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                                            </td>
                                            <td class="py-2 pr-3">
                                                <select :name="`detalles[${i}][unidad_medida]`" x-model="item.unidad_medida"
                                                        class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                                                    @foreach(\App\Models\GuiaRemision::UNIDADES as $key => $label)
                                                        <option value="{{ $key }}">{{ $key }} - {{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="py-2 pr-3">
                                                <input type="number" :name="`detalles[${i}][cantidad]`" x-model="item.cantidad"
                                                       min="0.01" step="0.01" required
                                                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20"
                                                       placeholder="1.00">
                                            </td>
                                            <td class="py-2 text-center">
                                                <button type="button" @click="detalles.splice(i,1)" x-show="detalles.length > 1"
                                                        class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="pt-3 text-right text-sm text-gray-500">
                                            Total ítems: <strong x-text="detalles.length"></strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>

                {{-- Columna derecha --}}
                <div class="space-y-6">

                    {{-- Transportista --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-base font-semibold text-gray-800 mb-4">
                            <i class="fas fa-truck text-gray-400 mr-1"></i>
                            Datos del Transportista
                        </h2>

                        {{-- Privado --}}
                        <div x-show="modalidad === '01'" class="space-y-4">
                            <p class="text-xs text-blue-600 bg-blue-50 rounded-lg p-2">
                                <i class="fas fa-info-circle mr-1"></i> Transporte privado: datos del vehículo y conductor.
                            </p>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Placa del vehículo</label>
                                <input type="text" name="placa_vehiculo"
                                       value="{{ old('placa_vehiculo', $guiaRemision?->placa_vehiculo ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20 uppercase"
                                       placeholder="ABC-123">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo doc. conductor</label>
                                <select name="conductor_tipo_doc"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                                    <option value="1" {{ old('conductor_tipo_doc', $guiaRemision?->conductor_tipo_doc ?? '1') == '1' ? 'selected' : '' }}>DNI</option>
                                    <option value="4" {{ old('conductor_tipo_doc', $guiaRemision?->conductor_tipo_doc ?? '') == '4' ? 'selected' : '' }}>Carné Extranjería</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">N° documento conductor</label>
                                <input type="text" name="conductor_num_doc"
                                       value="{{ old('conductor_num_doc', $guiaRemision?->conductor_num_doc ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20"
                                       placeholder="12345678">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del conductor</label>
                                <input type="text" name="conductor_nombre"
                                       value="{{ old('conductor_nombre', $guiaRemision?->conductor_nombre ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20"
                                       placeholder="Nombre completo">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">N° licencia conducir</label>
                                <input type="text" name="conductor_licencia"
                                       value="{{ old('conductor_licencia', $guiaRemision?->conductor_licencia ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20"
                                       placeholder="Q12345678">
                            </div>
                        </div>

                        {{-- Público --}}
                        <div x-show="modalidad === '02'" class="space-y-4">
                            <p class="text-xs text-purple-600 bg-purple-50 rounded-lg p-2">
                                <i class="fas fa-info-circle mr-1"></i> Transporte público: datos de la empresa transportista.
                            </p>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">RUC transportista</label>
                                <input type="text" name="transportista_ruc"
                                       value="{{ old('transportista_ruc', $guiaRemision?->transportista_ruc ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20 font-mono"
                                       placeholder="20100070970" maxlength="11">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Razón social transportista</label>
                                <input type="text" name="transportista_nombre"
                                       value="{{ old('transportista_nombre', $guiaRemision?->transportista_nombre ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20"
                                       placeholder="TRANSPORTES SAC">
                            </div>
                        </div>
                    </div>

                    {{-- Venta vinculada --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-base font-semibold text-gray-800 mb-4">
                            <i class="fas fa-link text-gray-400 mr-1"></i> Venta vinculada
                        </h2>
                        <select name="venta_id"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20">
                            <option value="">— Sin venta vinculada —</option>
                            {{-- Cargamos las últimas 50 ventas --}}
                            @php
                                $ventas = \App\Models\Venta::with('cliente')
                                    ->orderBy('id','desc')->limit(50)->get();
                            @endphp
                            @foreach($ventas as $v)
                                <option value="{{ $v->id }}"
                                    {{ old('venta_id', $guiaRemision?->venta_id ?? $ventaPreload?->id) == $v->id ? 'selected' : '' }}>
                                    {{ $v->codigo }} — {{ $v->cliente?->nombre ?? 'Sin cliente' }} ({{ $v->fecha->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Observaciones --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-base font-semibold text-gray-800 mb-3">
                            <i class="fas fa-comment text-gray-400 mr-1"></i> Observaciones
                        </h2>
                        <textarea name="observaciones" rows="3"
                                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2E2C]/20 resize-none"
                                  placeholder="Información adicional...">{{ old('observaciones', $guiaRemision?->observaciones ?? '') }}</textarea>
                    </div>

                    {{-- Botones --}}
                    <div class="flex flex-col gap-3">
                        <button type="submit" :disabled="!serie"
                                class="w-full bg-[#2B2E2C] text-white py-3 rounded-xl font-semibold text-sm hover:bg-[#3d4140] transition-colors flex items-center justify-center gap-2 disabled:opacity-50">
                            <i class="fas fa-save"></i>
                            {{ isset($guiaRemision) ? 'Actualizar Guía' : 'Guardar Guía' }}
                        </button>
                        <a href="{{ route('guias-remision.index') }}"
                           class="w-full border border-gray-200 text-gray-600 py-3 rounded-xl font-medium text-sm hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>

                </div>
            </div>

        </form>
    </div>

    @php
        $detallesIniciales = old('detalles');
        if (!$detallesIniciales && isset($guiaRemision) && $guiaRemision?->detalles) {
            $detallesIniciales = $guiaRemision?->detalles->map(fn($d) => [
                'producto_id'   => $d->producto_id,
                'codigo'        => $d->codigo ?? '',
                'descripcion'   => $d->descripcion ?? '',
                'unidad_medida' => $d->unidad_medida ?? 'NIU',
                'cantidad'      => $d->cantidad,
            ])->toArray();
        }
        if (!$detallesIniciales && isset($ventaPreload) && $ventaPreload) {
            $detallesIniciales = $ventaPreload->detalles->map(fn($d) => [
                'producto_id'   => $d->producto_id,
                'codigo'        => $d->producto?->codigo ?? '',
                'descripcion'   => $d->producto?->nombre ?? '',
                'unidad_medida' => 'NIU',
                'cantidad'      => $d->cantidad,
            ])->toArray();
        }
        if (empty($detallesIniciales)) {
            $detallesIniciales = [['producto_id' => '', 'codigo' => '', 'descripcion' => '', 'unidad_medida' => 'NIU', 'cantidad' => 1]];
        }
    @endphp
    <script>
        function guiaForm() {
            return {
                modalidad: '{{ old('modalidad_transporte', $guiaRemision?->modalidad_transporte ?? '01') }}',
                clienteId: '{{ old('cliente_id', $guiaRemision?->cliente_id ?? '') }}',
                destTipoDoc: '{{ old('destinatario_tipo_doc', $guiaRemision?->destinatario_tipo_doc ?? '1') }}',
                destNumDoc: '{{ old('destinatario_num_doc', $guiaRemision?->destinatario_num_doc ?? '') }}',
                destNombre: '{{ old('destinatario_nombre', $guiaRemision?->destinatario_nombre ?? '') }}',
                detalles: @json($detallesIniciales),

                cargarCliente(id) {
                    if (!id) return;
                    const opt = document.querySelector(`select[name="cliente_id"] option[value="${id}"]`);
                    if (!opt) return;
                    this.destTipoDoc = opt.dataset.docTipo || '1';
                    this.destNumDoc  = opt.dataset.docNum  || '';
                    this.destNombre  = opt.dataset.nombre  || '';
                },

                seleccionarProducto(i, select) {
                    const opt = select.options[select.selectedIndex];
                    if (!opt || !opt.value) return;
                    this.detalles[i].producto_id   = opt.value;
                    this.detalles[i].codigo        = opt.dataset.sku || '';
                    this.detalles[i].descripcion   = opt.dataset.nombre || '';
                    this.detalles[i].unidad_medida = opt.dataset.unidad || 'NIU';
                },

                agregarDetalle() {
                    this.detalles.push({ producto_id: '', codigo: '', descripcion: '', unidad_medida: 'NIU', cantidad: 1 });
                },
            };
        }
    </script>
</body>
</html>
