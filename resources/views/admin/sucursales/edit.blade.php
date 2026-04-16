<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar Sucursal — {{ $sucursal->nombre }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8" x-data="{ tab: '{{ session('_tab', 'info') }}' }">

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('admin.sucursales.index') }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $sucursal->nombre }}</h1>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#2B2E2C]/10 text-[#2B2E2C]">{{ $sucursal->codigo }}</span>
            @if($sucursal->es_principal)
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800"><i class="fas fa-star mr-1"></i>Principal</span>
            @endif
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sucursal->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                {{ ucfirst($sucursal->estado) }}
            </span>
        </div>
        <p class="text-sm text-gray-500 ml-7">{{ $sucursal->direccion }}</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded flex items-center gap-2">
            <i class="fas fa-check-circle"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex overflow-x-auto -mb-px">
                @foreach([
                    ['key'=>'info',      'label'=>'Info Sucursal',           'icon'=>'store'],
                    ['key'=>'almacenes', 'label'=>'Almacenes',               'icon'=>'warehouse'],
                    ['key'=>'series',    'label'=>'Series / Correlativos',   'icon'=>'list-ol'],
                    ['key'=>'pagos',     'label'=>'Yape / Plin / Pagos',     'icon'=>'qrcode'],
                ] as $t)
                    <button @click="tab = '{{ $t['key'] }}'"
                        :class="tab === '{{ $t['key'] }}' ? 'border-[#F7D600] text-[#2B2E2C] bg-[#2B2E2C]/10/50' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="flex items-center gap-2 px-5 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                        <i class="fas fa-{{ $t['icon'] }}"></i> {{ $t['label'] }}
                    </button>
                @endforeach
                <a href="{{ route('admin.sucursales.comprobantes', $sucursal) }}"
                    class="flex items-center gap-2 px-5 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap">
                    <i class="fas fa-file-invoice"></i> Comprobantes Emitidos
                </a>
            </nav>
        </div>

        {{-- ─── TAB: INFO ────────────────────────────────────────────────────── --}}
        <div x-show="tab === 'info'" x-cloak class="p-6">
            <form action="{{ route('admin.sucursales.update', $sucursal) }}" method="POST">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $sucursal->nombre) }}" maxlength="150" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input type="text" name="direccion" value="{{ old('direccion', $sucursal->direccion) }}" maxlength="300"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                        <input type="text" name="departamento" value="{{ old('departamento', $sucursal->departamento) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                        <input type="text" name="provincia" value="{{ old('provincia', $sucursal->provincia) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Distrito</label>
                        <input type="text" name="distrito" value="{{ old('distrito', $sucursal->distrito) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ubigeo</label>
                        <input type="text" name="ubigeo" value="{{ old('ubigeo', $sucursal->ubigeo) }}" maxlength="6"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $sucursal->telefono) }}" maxlength="20"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $sucursal->email) }}" maxlength="150"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Almacén principal</label>
                        <div class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-sm text-gray-700 flex items-center gap-2">
                            <i class="fas fa-warehouse text-gray-400"></i>
                            {{ $sucursal->almacen?->nombre ?? 'Sin almacén principal' }}
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            Gestiona los almacenes en la pestaña <strong>Almacenes</strong>.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                            <option value="activo" {{ old('estado', $sucursal->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ old('estado', $sucursal->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="es_principal" id="es_principal" value="1"
                            {{ old('es_principal', $sucursal->es_principal) ? 'checked' : '' }}
                            class="w-4 h-4 text-[#2B2E2C] border-gray-300 rounded focus:ring-[#F7D600]">
                        <label for="es_principal" class="text-sm font-medium text-gray-700">Sucursal Principal</label>
                    </div>
                </div>
                <div class="flex justify-end mt-6 pt-4 border-t">
                    <button type="submit"
                        class="bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] font-semibold py-2 px-6 rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-save"></i> Guardar Info
                    </button>
                </div>
            </form>
        </div>

        {{-- ─── TAB: ALMACENES ──────────────────────────────────────────────── --}}
        <div x-show="tab === 'almacenes'" x-cloak class="p-6">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-5">
                <i class="fas fa-warehouse text-[#2B2E2C]"></i> Almacenes de la Sucursal
            </h3>

            {{-- Listado de almacenes actuales --}}
            @if($sucursal->almacenes->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-warehouse text-4xl mb-3 block"></i>
                    <p>Esta sucursal no tiene almacenes vinculados aún.</p>
                </div>
            @else
                <div class="space-y-3 mb-6">
                    @foreach($sucursal->almacenes->sortBy('nombre') as $alm)
                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center
                                {{ $alm->id === $sucursal->almacen_id ? 'bg-yellow-100' : 'bg-gray-100' }}">
                                <i class="fas fa-warehouse text-sm
                                    {{ $alm->id === $sucursal->almacen_id ? 'text-yellow-600' : 'text-gray-500' }}"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $alm->nombre }}
                                    @if($alm->id === $sucursal->almacen_id)
                                        <span class="ml-2 text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Principal</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">{{ $alm->codigo }} · {{ ucfirst($alm->tipo) }} · {{ ucfirst($alm->estado) }}</p>
                                @if($alm->direccion)
                                    <p class="text-xs text-gray-400">{{ $alm->direccion }}</p>
                                @endif
                            </div>
                        </div>
                        @if($alm->id !== $sucursal->almacen_id)
                        <form action="{{ route('admin.sucursales.almacenes.destroy', [$sucursal, $alm]) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar este almacén? Solo es posible si no tiene movimientos.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 transition p-2 rounded-lg hover:bg-red-50">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            @endif

            {{-- Formulario para agregar almacén --}}
            <div class="border border-dashed border-gray-300 rounded-xl p-5">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-green-500"></i> Agregar otro almacén
                </h4>
                <form action="{{ route('admin.sucursales.almacenes.store', $sucursal) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nombre *</label>
                            <input type="text" name="nombre" required maxlength="100"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#F7D600] focus:border-[#F7D600]"
                                   placeholder="Ej: Almacén Piso 2, Depósito Norte…">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                            <select name="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#F7D600]">
                                <option value="sucursal">Sucursal</option>
                                <option value="temporal">Temporal</option>
                                <option value="principal">Principal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                            <select name="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#F7D600]">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Dirección</label>
                            <input type="text" name="direccion" maxlength="300"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#F7D600]"
                                   placeholder="Dirección del almacén">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                                class="bg-[#2B2E2C] text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-[#3d4140] transition">
                            <i class="fas fa-plus mr-2"></i> Agregar Almacén
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─── TAB: SERIES ──────────────────────────────────────────────────── --}}
        <div x-show="tab === 'series'" x-cloak class="p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list-ol text-[#2B2E2C]"></i> Series de Comprobantes
                </h3>
                <form action="{{ route('admin.sucursales.generar-series', $sucursal) }}" method="POST">
                    @csrf
                    <button type="submit"
                        onclick="return confirm('¿Generar las series estándar faltantes (FA, BA, FC, FD, T, CO)?')"
                        class="flex items-center gap-2 bg-[#2B2E2C]/10 hover:bg-[#2B2E2C]/10 text-[#2B2E2C] text-xs font-semibold px-3 py-2 rounded-lg transition-colors border border-gray-200">
                        <i class="fas fa-magic"></i> Generar series estándar
                    </button>
                </form>
            </div>

            @if($sucursal->series->isEmpty())
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-list-ol text-4xl mb-3 block"></i>
                    No hay series configuradas.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($sucursal->series->sortBy('tipo_comprobante') as $serie)
                        <div class="border rounded-xl overflow-hidden {{ $serie->activo ? 'border-gray-200' : 'border-gray-100 opacity-60' }}">
                            {{-- Cabecera de la serie --}}
                            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-800">{{ $serie->tipo_nombre }}</span>
                                    <span class="text-xs text-gray-400 font-mono bg-gray-200 px-1.5 py-0.5 rounded">{{ $serie->tipo_comprobante }}</span>
                                    <span class="text-xs font-mono font-bold text-[#2B2E2C] bg-[#2B2E2C]/10 px-2 py-0.5 rounded">{{ $serie->serie }}</span>
                                </div>
                                {{-- Toggle activo --}}
                                <form action="{{ route('admin.sucursales.series.update', [$sucursal, $serie]) }}" method="POST">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="serie" value="{{ $serie->serie }}">
                                    <input type="hidden" name="correlativo_actual" value="{{ $serie->correlativo_actual }}">
                                    <input type="hidden" name="formato_impresion" value="{{ $serie->formato_impresion }}">
                                    <input type="hidden" name="activo" value="{{ $serie->activo ? '0' : '1' }}">
                                    <button type="submit"
                                        title="{{ $serie->activo ? 'Desactivar' : 'Activar' }}"
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors {{ $serie->activo ? 'bg-green-500' : 'bg-gray-300' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $serie->activo ? 'translate-x-4' : 'translate-x-1' }}"></span>
                                    </button>
                                </form>
                            </div>
                            {{-- Formulario de edición --}}
                            <form action="{{ route('admin.sucursales.series.update', [$sucursal, $serie]) }}" method="POST"
                                class="flex flex-wrap items-end gap-4 p-4">
                                @csrf @method('PUT')
                                <input type="hidden" name="activo" value="{{ $serie->activo ? '1' : '0' }}">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Serie</label>
                                    <input type="text" name="serie" value="{{ $serie->serie }}" maxlength="5"
                                        class="w-20 border border-gray-300 rounded-lg px-2 py-1.5 text-sm font-mono uppercase focus:ring-2 focus:ring-[#F7D600] text-center">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Correlativo Actual</label>
                                    <input type="number" name="correlativo_actual" value="{{ $serie->correlativo_actual }}" min="1"
                                        class="w-28 border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-[#F7D600] text-center">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Formato Impresión</label>
                                    <select name="formato_impresion"
                                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-[#F7D600]">
                                        <option value="A4" {{ $serie->formato_impresion === 'A4' ? 'selected' : '' }}>A4</option>
                                        <option value="ticket" {{ $serie->formato_impresion === 'ticket' ? 'selected' : '' }}>Ticket 80mm</option>
                                        <option value="A5" {{ $serie->formato_impresion === 'A5' ? 'selected' : '' }}>A5</option>
                                    </select>
                                </div>
                                <div class="ml-auto">
                                    <button type="submit"
                                        class="bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] text-sm px-4 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                                        <i class="fas fa-save text-xs"></i> Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ─── TAB: PAGOS ───────────────────────────────────────────────────── --}}
        <div x-show="tab === 'pagos'" x-cloak class="p-6">
            <form action="{{ route('admin.sucursales.pagos.update', $sucursal) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @php
                        $metodosConfig = [
                            'yape'          => ['color'=>'purple', 'icon'=>'mobile-alt',  'label'=>'Yape',                'qr'=>true],
                            'plin'          => ['color'=>'green',  'icon'=>'mobile-alt',  'label'=>'Plin',                'qr'=>true],
                            'transferencia' => ['color'=>'blue',   'icon'=>'university',  'label'=>'Transferencia Bancaria','qr'=>false],
                            'pos'           => ['color'=>'orange', 'icon'=>'credit-card', 'label'=>'POS / Tarjeta',       'qr'=>false],
                        ];
                    @endphp
                    @foreach($metodosConfig as $tipo => $cfg)
                        @php $pago = $pagosIndexed->get($tipo); @endphp
                        <div x-data="{ activo: {{ ($pago && $pago->activo) ? 'true' : 'false' }} }"
                            class="border rounded-xl overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-{{ $cfg['icon'] }} text-{{ $cfg['color'] }}-600"></i>
                                    <span class="font-semibold text-gray-800">{{ $cfg['label'] }}</span>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="pagos[{{ $tipo }}][activo]" value="1"
                                        {{ ($pago && $pago->activo) ? 'checked' : '' }}
                                        x-model="activo"
                                        class="w-4 h-4 text-{{ $cfg['color'] }}-600 rounded border-gray-300 focus:ring-{{ $cfg['color'] }}-500">
                                    <span class="text-xs font-medium text-gray-600">Activar</span>
                                </label>
                            </div>
                            <div x-show="activo" class="p-4 space-y-3">
                                <input type="hidden" name="pagos[{{ $tipo }}][tipo_pago]" value="{{ $tipo }}">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Titular / Nombre</label>
                                    <input type="text" name="pagos[{{ $tipo }}][titular]"
                                        value="{{ old("pagos.{$tipo}.titular", $pago?->titular) }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-{{ $cfg['color'] }}-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        {{ in_array($tipo, ['yape','plin']) ? 'Número de Celular' : 'Número de Cuenta' }}
                                    </label>
                                    <input type="text" name="pagos[{{ $tipo }}][numero]"
                                        value="{{ old("pagos.{$tipo}.numero", $pago?->numero) }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-{{ $cfg['color'] }}-500">
                                </div>
                                @if($tipo === 'transferencia')
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Banco</label>
                                        <input type="text" name="pagos[{{ $tipo }}][banco]"
                                            value="{{ old("pagos.{$tipo}.banco", $pago?->banco) }}"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#F7D600]">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">CCI (Código Interbancario)</label>
                                        <input type="text" name="pagos[{{ $tipo }}][cci]"
                                            value="{{ old("pagos.{$tipo}.cci", $pago?->cci) }}"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#F7D600]">
                                    </div>
                                @endif
                                @if($cfg['qr'])
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Código QR</label>
                                        @if($pago && $pago->qr_imagen_path)
                                            <div class="mb-2">
                                                <img src="{{ $pago->qr_url }}" alt="QR {{ $cfg['label'] }}"
                                                    class="w-28 h-28 object-contain border rounded-lg p-1 bg-white shadow-sm">
                                            </div>
                                        @endif
                                        <input type="file" name="pagos[{{ $tipo }}][qr]" accept="image/*"
                                            class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-{{ $cfg['color'] }}-50 file:text-{{ $cfg['color'] }}-700 hover:file:bg-{{ $cfg['color'] }}-100">
                                    </div>
                                @endif
                            </div>
                            <div x-show="!activo" class="px-4 py-6 text-center text-gray-400 text-sm">
                                <i class="fas fa-toggle-off text-xl mb-1 block"></i>
                                Método desactivado
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-end mt-6 pt-4 border-t">
                    <button type="submit"
                        class="bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] font-semibold py-2 px-6 rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-save"></i> Guardar Pagos
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Zona de peligro: Eliminar sucursal --}}
    @if(!$sucursal->es_principal)
    <div class="mt-6 bg-white rounded-2xl shadow border border-red-100">
        <div class="px-6 py-4 border-b border-red-100">
            <h3 class="text-sm font-semibold text-red-600 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i> Zona de peligro
            </h3>
        </div>
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-800">Eliminar esta sucursal</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    Solo es posible si no hay ventas, compras ni guías de remisión asociadas.
                    Los almacenes vinculados quedarán sin sucursal asignada.
                </p>
            </div>
            <form action="{{ route('admin.sucursales.destroy', $sucursal) }}" method="POST"
                  onsubmit="return confirm('¿Está seguro de eliminar la sucursal {{ $sucursal->nombre }}? Esta acción no se puede deshacer.')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="ml-6 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-trash"></i> Eliminar Sucursal
                </button>
            </form>
        </div>
    </div>
    @endif
</div>

<style>[x-cloak] { display: none !important; }</style>
</body>
</html>
