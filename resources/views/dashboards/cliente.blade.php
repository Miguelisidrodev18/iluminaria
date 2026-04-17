<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Portal de Cliente" subtitle="Bienvenido, {{ auth()->user()->name }}" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Pedidos en Proceso</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $pedidos_pendientes }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-shipping-fast text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Pedidos Completados</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $pedidos_completados }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#2B2E2C]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Pedidos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $pedidos_pendientes + $pedidos_completados }}</p>
                    </div>
                    <div class="bg-[#2B2E2C]/10 rounded-full p-3">
                        <i class="fas fa-box text-[#2B2E2C] text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seguimiento de pedidos --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-map-marker-alt mr-2 text-[#2B2E2C]"></i>Seguimiento de Mis Pedidos
                </h2>
                <a href="{{ route('ventas.create') }}"
                   class="bg-[#F7D600] text-[#2B2E2C] px-4 py-2 rounded-lg text-sm font-semibold hover:bg-yellow-400 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Nuevo Pedido
                </a>
            </div>

            @if($mis_pedidos->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-box-open text-6xl mb-4"></i>
                    <p class="text-lg font-medium">No tienes pedidos aún</p>
                    <p class="text-sm mt-2">Realiza tu primer pedido para ver el seguimiento aquí</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($mis_pedidos as $pedido)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <span class="font-semibold text-gray-800">Pedido #{{ $pedido->id }}</span>
                                <span class="text-sm text-gray-500 ml-2">{{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-bold text-gray-900">S/ {{ number_format($pedido->total, 2) }}</span>
                                @php
                                    $estadoClasses = match($pedido->estado_pago ?? 'pendiente') {
                                        'pagado'    => 'bg-green-100 text-green-700',
                                        'pendiente' => 'bg-yellow-100 text-yellow-700',
                                        default     => 'bg-gray-100 text-gray-700',
                                    };
                                    $estadoLabel = match($pedido->estado_pago ?? 'pendiente') {
                                        'pagado'    => 'Entregado',
                                        'pendiente' => 'En Proceso',
                                        default     => ucfirst($pedido->estado_pago ?? 'pendiente'),
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $estadoClasses }}">
                                    {{ $estadoLabel }}
                                </span>
                            </div>
                        </div>
                        {{-- Barra de progreso tipo tracking DHL --}}
                        @if(($pedido->estado_pago ?? 'pendiente') === 'pendiente')
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                <span>Pedido recibido</span>
                                <span>En preparación</span>
                                <span>En tránsito</span>
                                <span>Listo</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-[#F7D600] h-2 rounded-full" style="width: 40%"></div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-bolt mr-2 text-[#F7D600]"></i>Acciones Rápidas
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <a href="{{ route('ventas.create') }}" class="flex items-center p-4 bg-[#F7D600]/10 border border-[#F7D600] rounded-lg hover:bg-[#F7D600]/20 transition-colors">
                    <i class="fas fa-plus-circle text-[#2B2E2C] text-xl mr-3"></i>
                    <span class="font-medium text-gray-800">Hacer un Pedido</span>
                </a>
                <a href="{{ route('ventas.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-list text-gray-600 text-xl mr-3"></i>
                    <span class="font-medium text-gray-800">Ver Todos Mis Pedidos</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
