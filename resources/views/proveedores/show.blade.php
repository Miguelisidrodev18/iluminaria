<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedor: {{ $proveedor->razon_social }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8">
    <x-header title="{{ $proveedor->razon_social }}" subtitle="Ficha completa del proveedor" />

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="max-w-6xl mx-auto" x-data="{ tab: 'info' }">

        {{-- Header card --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
            <div class="bg-[#2B2E2C] px-6 py-4 flex items-start justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $proveedor->razon_social }}</h2>
                    @if($proveedor->nombre_comercial)
                        <p class="text-gray-300 text-sm">{{ $proveedor->nombre_comercial }}</p>
                    @endif
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-bold rounded-full {{ $proveedor->tipo_badge_class }}">
                            {{ $proveedor->tipo_label }}
                        </span>
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-bold rounded-full {{ $proveedor->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($proveedor->estado) }}
                        </span>
                        @if($proveedor->price_level)
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-bold rounded-full {{ $proveedor->price_badge_class }}">
                                <i class="fas fa-tag mr-1"></i>{{ $proveedor->price_label }}
                            </span>
                        @endif
                        @if($proveedor->quality_level)
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-bold rounded-full {{ $proveedor->quality_badge_class }}">
                                <i class="fas fa-star mr-1"></i>{{ $proveedor->quality_label }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('proveedores.edit', $proveedor) }}"
                       class="bg-[#F7D600] text-[#2B2E2C] px-4 py-2 rounded-lg text-sm font-bold hover:bg-yellow-400">
                        <i class="fas fa-edit mr-1"></i>Editar
                    </a>
                    <a href="{{ route('proveedores.index') }}"
                       class="border border-gray-400 text-gray-200 px-4 py-2 rounded-lg text-sm hover:bg-white/10">
                        <i class="fas fa-arrow-left mr-1"></i>Volver
                    </a>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="border-b border-gray-200 bg-gray-50">
                <nav class="flex overflow-x-auto">
                    @foreach([
                        ['info',   'fas fa-info-circle',   'Información'],
                        ['cats',   'fas fa-tags',           'Categorías (' . $proveedor->categoriasProducto->count() . ')'],
                        ['certs',  'fas fa-certificate',    'Certificaciones'],
                        ['import', 'fas fa-ship',           'Importación'],
                        ['compras','fas fa-shopping-cart',  'Compras (' . $proveedor->compras->count() . ')'],
                    ] as [$key, $icon, $label])
                        <button @click="tab = '{{ $key }}'"
                                :class="tab === '{{ $key }}' ? 'border-b-2 border-[#2B2E2C] text-[#2B2E2C] font-semibold' : 'text-gray-500 hover:text-gray-700'"
                                class="px-5 py-3 text-sm whitespace-nowrap transition-colors">
                            <i class="{{ $icon }} mr-1.5"></i>{{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- Tab: Información --}}
        <div x-show="tab === 'info'" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @if($proveedor->ruc)
                        <div><p class="text-xs text-gray-400 mb-0.5">RUC</p><p class="font-mono font-bold text-gray-900">{{ $proveedor->ruc }}</p></div>
                    @endif
                    @if($proveedor->contacto_nombre)
                        <div><p class="text-xs text-gray-400 mb-0.5">Contacto</p><p class="text-sm text-gray-800"><i class="fas fa-user mr-1 text-gray-400"></i>{{ $proveedor->contacto_nombre }}</p></div>
                    @endif
                    @if($proveedor->telefono)
                        <div><p class="text-xs text-gray-400 mb-0.5">Teléfono</p><p class="text-sm text-gray-800"><i class="fas fa-phone mr-1 text-gray-400"></i>{{ $proveedor->telefono }}</p></div>
                    @endif
                    @if($proveedor->email)
                        <div><p class="text-xs text-gray-400 mb-0.5">Email</p><p class="text-sm text-gray-800"><i class="fas fa-envelope mr-1 text-gray-400"></i>{{ $proveedor->email }}</p></div>
                    @endif
                    @if($proveedor->website)
                        <div><p class="text-xs text-gray-400 mb-0.5">Web</p><p class="text-sm"><a href="{{ $proveedor->website }}" target="_blank" class="text-blue-600 hover:underline"><i class="fas fa-globe mr-1"></i>{{ $proveedor->website }}</a></p></div>
                    @endif
                    @if($proveedor->catalog_url)
                        <div><p class="text-xs text-gray-400 mb-0.5">Catálogo</p><p class="text-sm"><a href="{{ $proveedor->catalog_url }}" target="_blank" class="text-blue-600 hover:underline"><i class="fas fa-book mr-1"></i>Ver catálogo</a></p></div>
                    @endif
                    @if($proveedor->direccion)
                        <div class="md:col-span-2"><p class="text-xs text-gray-400 mb-0.5">Dirección Fiscal</p><p class="text-sm text-gray-800"><i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>{{ $proveedor->direccion }}</p></div>
                    @endif
                    @if($proveedor->country)
                        <div><p class="text-xs text-gray-400 mb-0.5">País</p><p class="text-sm text-gray-800"><i class="fas fa-globe mr-1 text-gray-400"></i>{{ $proveedor->country }}</p></div>
                    @endif
                    @if($proveedor->district)
                        <div><p class="text-xs text-gray-400 mb-0.5">Distrito</p><p class="text-sm text-gray-800">{{ $proveedor->district }}</p></div>
                    @endif
                    @if($proveedor->observations)
                        <div class="md:col-span-2"><p class="text-xs text-gray-400 mb-0.5">Observaciones</p><p class="text-sm text-gray-600 italic">{{ $proveedor->observations }}</p></div>
                    @endif
                </div>
            </div>
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-3">Actividad</p>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600"><i class="fas fa-shopping-cart mr-1 text-gray-400"></i>Compras</span>
                            <span class="font-bold text-gray-800">{{ $proveedor->compras->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600"><i class="fas fa-clipboard-list mr-1 text-gray-400"></i>Pedidos</span>
                            <span class="font-bold text-gray-800">{{ $proveedor->pedidos->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600"><i class="fas fa-tags mr-1 text-gray-400"></i>Categorías</span>
                            <span class="font-bold text-gray-800">{{ $proveedor->categoriasProducto->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-3">Fechas</p>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p><span class="text-gray-400">Registrado:</span> {{ $proveedor->created_at->format('d/m/Y') }}</p>
                        <p><span class="text-gray-400">Actualizado:</span> {{ $proveedor->updated_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab: Categorías --}}
        <div x-show="tab === 'cats'" class="bg-white rounded-xl shadow-sm p-6">
            @if($categoriasPorGrupo->isEmpty())
                <div class="text-center py-10 text-gray-400">
                    <i class="fas fa-tags text-4xl mb-3 block text-gray-200"></i>
                    No se han asignado categorías de producto.
                    <a href="{{ route('proveedores.edit', $proveedor) }}" class="block mt-3 text-sm text-blue-600 hover:underline">Agregar categorías</a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($categoriasPorGrupo as $cat => $subs)
                        <div class="border border-gray-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-[#2B2E2C] uppercase mb-3 flex items-center gap-2">
                                <i class="fas fa-layer-group text-gray-400"></i>{{ $cat }}
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($subs as $sub)
                                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">{{ $sub }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tab: Certificaciones --}}
        <div x-show="tab === 'certs'" class="bg-white rounded-xl shadow-sm p-6">
            @if($proveedor->certificaciones->isEmpty())
                <div class="text-center py-10 text-gray-400">
                    <i class="fas fa-certificate text-4xl mb-3 block text-gray-200"></i>
                    No se han registrado certificaciones.
                </div>
            @else
                <div class="flex flex-wrap gap-4">
                    @foreach($proveedor->certificaciones as $cert)
                        @php
                            $colors = ['generales' => 'blue', 'por_producto' => 'green', 'iso' => 'purple'];
                            $c = $colors[$cert->cert_type] ?? 'gray';
                        @endphp
                        <div class="border-2 border-{{ $c }}-200 bg-{{ $c }}-50 rounded-xl p-5 flex items-center gap-3">
                            <i class="fas fa-certificate text-2xl text-{{ $c }}-500"></i>
                            <div>
                                <p class="font-bold text-{{ $c }}-800 text-sm">{{ \App\Models\ProveedorCertificacion::TIPOS[$cert->cert_type] ?? $cert->cert_type }}</p>
                                @if($cert->descripcion)
                                    <p class="text-xs text-{{ $c }}-600 mt-0.5">{{ $cert->descripcion }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tab: Datos de Importación --}}
        <div x-show="tab === 'import'" class="bg-white rounded-xl shadow-sm p-6">
            @if($proveedor->supplier_type === 'nacional' && !$proveedor->port && !$proveedor->moq && !$proveedor->bank_detail)
                <div class="text-center py-10 text-gray-400">
                    <i class="fas fa-ship text-4xl mb-3 block text-gray-200"></i>
                    Este proveedor es nacional. Los datos de importación aplican a proveedores extranjeros o de importación.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
                    @if($proveedor->factory_address)
                        <div class="md:col-span-2">
                            <p class="text-xs text-gray-400 mb-1">Dirección de Fábrica</p>
                            <p class="text-sm text-gray-800"><i class="fas fa-industry mr-1 text-gray-400"></i>{{ $proveedor->factory_address }}</p>
                        </div>
                    @endif
                    @if($proveedor->port)
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Puerto</p>
                            <p class="text-sm text-gray-800"><i class="fas fa-anchor mr-1 text-gray-400"></i>{{ $proveedor->port }}</p>
                        </div>
                    @endif
                    @if($proveedor->moq)
                        <div>
                            <p class="text-xs text-gray-400 mb-1">MOQ (Mínimo de Pedido)</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $proveedor->moq }}</p>
                        </div>
                    @endif
                    @if($proveedor->bank_detail)
                        <div class="md:col-span-2">
                            <p class="text-xs text-gray-400 mb-1">Datos Bancarios</p>
                            <p class="text-sm text-gray-800 bg-gray-50 border border-gray-200 rounded-lg p-3 font-mono">{{ $proveedor->bank_detail }}</p>
                        </div>
                    @endif
                    @if(!$proveedor->port && !$proveedor->moq && !$proveedor->bank_detail && !$proveedor->factory_address)
                        <p class="text-sm text-gray-400 md:col-span-2">No hay datos de importación registrados. <a href="{{ route('proveedores.edit', $proveedor) }}" class="text-blue-600 hover:underline">Agregar</a></p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Tab: Compras --}}
        <div x-show="tab === 'compras'" class="bg-white rounded-xl shadow-sm overflow-hidden">
            @if($proveedor->compras->isEmpty())
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-shopping-cart text-4xl mb-3 block text-gray-200"></i>
                    No hay compras registradas para este proveedor.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Código</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">N° Factura</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Ver</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($proveedor->compras->take(10) as $compra)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3 font-mono text-sm font-semibold text-[#2B2E2C]">{{ $compra->codigo }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-700">{{ $compra->fecha->format('d/m/Y') }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-600">{{ $compra->numero_factura ?? '-' }}</td>
                                    <td class="px-5 py-3 text-sm font-bold text-right text-gray-900">S/ {{ number_format($compra->total, 2) }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ ucfirst($compra->estado) }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <a href="{{ route('compras.show', $compra) }}" class="text-[#2B2E2C] hover:text-black text-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($proveedor->compras->count() > 10)
                    <p class="text-xs text-center text-gray-400 py-3 border-t">Mostrando 10 de {{ $proveedor->compras->count() }} compras</p>
                @endif
            @endif
        </div>

    </div>{{-- max-w --}}
</div>
</body>
</html>
