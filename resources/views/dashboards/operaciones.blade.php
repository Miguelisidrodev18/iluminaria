<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Operaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Dashboard Operaciones" subtitle="Bienvenido, {{ auth()->user()->name }}" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Clientes</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $clientes_activos }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Ventas Pendientes</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $ventas_pendientes }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 {{ $traslados_pendientes > 0 ? 'border-orange-500' : 'border-green-500' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Traslados Pendientes</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $traslados_pendientes }}</p>
                    </div>
                    <div class="{{ $traslados_pendientes > 0 ? 'bg-orange-100' : 'bg-green-100' }} rounded-full p-3">
                        <i class="fas fa-truck-loading {{ $traslados_pendientes > 0 ? 'text-orange-600' : 'text-green-600' }} text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-tools mr-2 text-[#2B2E2C]"></i>Módulos de Operaciones
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('clientes.index') }}" class="flex items-center p-4 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                    <i class="fas fa-users mr-3 text-blue-600 text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Clientes</p>
                        <p class="text-xs text-gray-500">Gestionar clientes</p>
                    </div>
                </a>
                <a href="{{ route('ventas.index') }}" class="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                    <i class="fas fa-receipt mr-3 text-green-600 text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Ventas / Proyectos</p>
                        <p class="text-xs text-gray-500">Ver historial</p>
                    </div>
                </a>
                <a href="{{ route('inventario.productos.index') }}" class="flex items-center p-4 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-box mr-3 text-gray-600 text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Inventario</p>
                        <p class="text-xs text-gray-500">Consultar stock</p>
                    </div>
                </a>
                <a href="{{ route('traslados.pendientes') }}" class="flex items-center p-4 bg-orange-50 border border-orange-200 rounded-lg hover:bg-orange-100 transition-colors">
                    <i class="fas fa-truck-loading mr-3 text-orange-600 text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Traslados Pendientes</p>
                        <p class="text-xs text-gray-500">Coordinar envíos</p>
                    </div>
                </a>
                <a href="{{ route('proyectos.index') }}" class="flex items-center p-4 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors">
                    <i class="fas fa-project-diagram mr-3 text-purple-600 text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Proyectos</p>
                        <p class="text-xs text-gray-500">Instalaciones</p>
                    </div>
                </a>
                <a href="{{ route('inventario.almacenes.index') }}" class="flex items-center p-4 bg-[#2B2E2C]/5 border border-[#2B2E2C]/20 rounded-lg hover:bg-[#2B2E2C]/10 transition-colors">
                    <i class="fas fa-warehouse mr-3 text-[#2B2E2C] text-xl"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Almacenes</p>
                        <p class="text-xs text-gray-500">Ver almacenes</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
