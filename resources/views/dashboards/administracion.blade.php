<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administración</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Dashboard Administración" subtitle="Bienvenido, {{ auth()->user()->name }}" />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Ventas del Mes</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">S/ {{ number_format($ventas_mes, 2) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-cash-register text-green-600 text-2xl"></i>
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
                        <i class="fas fa-shopping-bag text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#2B2E2C]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Clientes</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $total_clientes }}</p>
                    </div>
                    <div class="bg-[#2B2E2C]/10 rounded-full p-3">
                        <i class="fas fa-users text-[#2B2E2C] text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 {{ $productos_bajo_stock > 0 ? 'border-red-500' : 'border-gray-300' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Bajo Stock</p>
                        <p class="text-3xl font-bold {{ $productos_bajo_stock > 0 ? 'text-red-600' : 'text-gray-900' }} mt-2">{{ $productos_bajo_stock }}</p>
                    </div>
                    <div class="{{ $productos_bajo_stock > 0 ? 'bg-red-100' : 'bg-gray-100' }} rounded-full p-3">
                        <i class="fas fa-exclamation-triangle {{ $productos_bajo_stock > 0 ? 'text-red-600' : 'text-gray-500' }} text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        @if($notif_cuotas > 0)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex items-center gap-3">
            <i class="fas fa-bell text-red-500 text-xl"></i>
            <p class="text-red-700 font-medium">Tienes <strong>{{ $notif_cuotas }}</strong> cuota(s) vencida(s) o por vencer en los próximos 7 días.</p>
            <a href="{{ route('cuentas-por-pagar.index') }}" class="ml-auto text-red-600 underline text-sm">Ver →</a>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="{{ route('ventas.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow flex items-center gap-4">
                <div class="bg-green-100 rounded-full p-4"><i class="fas fa-receipt text-green-600 text-xl"></i></div>
                <div><p class="font-semibold text-gray-800">Registrar Ventas</p><p class="text-sm text-gray-500">Ventas e historial</p></div>
            </a>
            <a href="{{ route('compras.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow flex items-center gap-4">
                <div class="bg-blue-100 rounded-full p-4"><i class="fas fa-file-invoice text-blue-600 text-xl"></i></div>
                <div><p class="font-semibold text-gray-800">Compras</p><p class="text-sm text-gray-500">Órdenes y proveedores</p></div>
            </a>
            <a href="{{ route('caja.actual') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow flex items-center gap-4">
                <div class="bg-yellow-100 rounded-full p-4"><i class="fas fa-cash-register text-yellow-600 text-xl"></i></div>
                <div><p class="font-semibold text-gray-800">Caja</p><p class="text-sm text-gray-500">Caja activa</p></div>
            </a>
            <a href="{{ route('inventario.productos.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow flex items-center gap-4">
                <div class="bg-[#2B2E2C]/10 rounded-full p-4"><i class="fas fa-boxes text-[#2B2E2C] text-xl"></i></div>
                <div><p class="font-semibold text-gray-800">Inventario</p><p class="text-sm text-gray-500">Productos y stock</p></div>
            </a>
            <a href="{{ route('cuentas-por-pagar.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow flex items-center gap-4">
                <div class="bg-red-100 rounded-full p-4"><i class="fas fa-credit-card text-red-600 text-xl"></i></div>
                <div><p class="font-semibold text-gray-800">Cuentas por Pagar</p><p class="text-sm text-gray-500">Cuotas y pagos</p></div>
            </a>
            <a href="{{ route('reportes.ventas') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow flex items-center gap-4">
                <div class="bg-purple-100 rounded-full p-4"><i class="fas fa-chart-line text-purple-600 text-xl"></i></div>
                <div><p class="font-semibold text-gray-800">Reportes</p><p class="text-sm text-gray-500">Ventas y márgenes</p></div>
            </a>
        </div>
    </div>
</body>
</html>
