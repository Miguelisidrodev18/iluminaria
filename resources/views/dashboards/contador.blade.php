<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Contador</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Dashboard Contador" subtitle="Bienvenido, {{ auth()->user()->name }}" />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Ventas del Mes</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">S/ {{ number_format($ventas_mes, 2) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-file-invoice-dollar text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Compras del Mes</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">S/ {{ number_format($compras_mes, 2) }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-receipt text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 {{ $cuotas_vencidas > 0 ? 'border-red-500' : 'border-gray-300' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Cuotas Vencidas</p>
                        <p class="text-3xl font-bold {{ $cuotas_vencidas > 0 ? 'text-red-600' : 'text-gray-900' }} mt-2">{{ $cuotas_vencidas }}</p>
                    </div>
                    <div class="{{ $cuotas_vencidas > 0 ? 'bg-red-100' : 'bg-gray-100' }} rounded-full p-3">
                        <i class="fas fa-exclamation-circle {{ $cuotas_vencidas > 0 ? 'text-red-600' : 'text-gray-500' }} text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 {{ $cuotas_por_vencer > 0 ? 'border-yellow-500' : 'border-gray-300' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Por Vencer (7 días)</p>
                        <p class="text-3xl font-bold {{ $cuotas_por_vencer > 0 ? 'text-yellow-600' : 'text-gray-900' }} mt-2">{{ $cuotas_por_vencer }}</p>
                    </div>
                    <div class="{{ $cuotas_por_vencer > 0 ? 'bg-yellow-100' : 'bg-gray-100' }} rounded-full p-3">
                        <i class="fas fa-clock {{ $cuotas_por_vencer > 0 ? 'text-yellow-600' : 'text-gray-500' }} text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-calculator mr-2 text-[#2B2E2C]"></i>Módulos Contables
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('ventas.index') }}" class="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                    <i class="fas fa-file-invoice mr-3 text-green-600 text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Boletas y Facturas</p>
                        <p class="text-xs text-gray-500">Historial de ventas</p>
                    </div>
                </a>
                <a href="{{ route('compras.index') }}" class="flex items-center p-4 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                    <i class="fas fa-shopping-bag mr-3 text-blue-600 text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Compras</p>
                        <p class="text-xs text-gray-500">Órdenes y costos</p>
                    </div>
                </a>
                <a href="{{ route('cuentas-por-pagar.index') }}" class="flex items-center p-4 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                    <i class="fas fa-credit-card mr-3 text-red-600 text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Cuentas por Pagar</p>
                        <p class="text-xs text-gray-500">Cuotas y vencimientos</p>
                    </div>
                </a>
                <a href="{{ route('reportes.ventas') }}" class="flex items-center p-4 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors">
                    <i class="fas fa-chart-bar mr-3 text-purple-600 text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Reportes</p>
                        <p class="text-xs text-gray-500">Ventas y márgenes</p>
                    </div>
                </a>
                <a href="{{ route('proveedores.index') }}" class="flex items-center p-4 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-truck mr-3 text-gray-600 text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Proveedores</p>
                        <p class="text-xs text-gray-500">Gestión de proveedores</p>
                    </div>
                </a>
                <a href="{{ route('inventario.reportes.stock-valorizado') }}" class="flex items-center p-4 bg-[#2B2E2C]/5 border border-[#2B2E2C]/20 rounded-lg hover:bg-[#2B2E2C]/10 transition-colors">
                    <i class="fas fa-coins mr-3 text-[#2B2E2C] text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Stock Valorizado</p>
                        <p class="text-xs text-gray-500">Valor del inventario</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
