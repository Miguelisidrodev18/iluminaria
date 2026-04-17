<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Logística</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Dashboard Logística" subtitle="Bienvenido, {{ auth()->user()->name }}" />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Proveedores Activos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $proveedores_activos }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-truck text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Compras Pendientes</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $compras_pendientes }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Compras Este Mes</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $compras_mes }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-shopping-bag text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#2B2E2C]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Accesos Rápidos</p>
                        <p class="text-sm text-gray-500 mt-2">Gestión de compras</p>
                    </div>
                    <div class="bg-[#2B2E2C]/10 rounded-full p-3">
                        <i class="fas fa-shipping-fast text-[#2B2E2C] text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-file-invoice mr-2 text-[#2B2E2C]"></i>Compras Recientes
            </h2>
            @if($compras_recientes->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-inbox text-6xl mb-4"></i>
                    <p class="text-lg font-medium">No hay compras registradas</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3 text-left">N°</th>
                                <th class="px-4 py-3 text-left">Proveedor</th>
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Total</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($compras_recientes as $compra)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">#{{ $compra->id }}</td>
                                <td class="px-4 py-3">{{ $compra->proveedor->razon_social ?? '-' }}</td>
                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">S/ {{ number_format($compra->total_pen, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        {{ $compra->estado === 'aprobado' ? 'bg-green-100 text-green-700' : ($compra->estado === 'pendiente' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                                        {{ ucfirst($compra->estado) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-bolt mr-2 text-[#F7D600]"></i>Acciones Rápidas
                </h2>
                <div class="space-y-3">
                    <a href="{{ route('proveedores.index') }}" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <i class="fas fa-truck mr-3 text-blue-600"></i>
                        <span class="font-medium text-gray-800">Gestionar Proveedores</span>
                    </a>
                    <a href="{{ route('compras.index') }}" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <i class="fas fa-file-invoice mr-3 text-green-600"></i>
                        <span class="font-medium text-gray-800">Ver Compras</span>
                    </a>
                    <a href="{{ route('compras.create') }}" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <i class="fas fa-plus-circle mr-3 text-[#2B2E2C]"></i>
                        <span class="font-medium text-gray-800">Nueva Orden de Compra</span>
                    </a>
                    <a href="{{ route('pedidos.index') }}" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <i class="fas fa-clipboard-list mr-3 text-yellow-600"></i>
                        <span class="font-medium text-gray-800">Pedidos a Proveedor</span>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-user mr-2 text-[#2B2E2C]"></i>Mi Perfil
                </h2>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><strong>Nombre:</strong> {{ auth()->user()->name }}</p>
                    <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                    <p><strong>Rol:</strong> Logística</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
