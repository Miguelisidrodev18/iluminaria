<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Sucursal</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8">
    <x-header title="Nueva Sucursal" subtitle="Se creará automáticamente con sus series de comprobantes y un almacén vinculado" />

    <div class="max-w-2xl">
        <div class="bg-[#2B2E2C]/10 border border-blue-200 rounded-lg p-4 mb-6 flex gap-3">
            <i class="fas fa-info-circle text-[#2B2E2C] mt-0.5"></i>
            <div class="text-sm text-[#2B2E2C]">
                <strong>Proceso automático:</strong> Al crear la sucursal, el sistema generará automáticamente un código único (S001, S002…), un almacén vinculado y las series de comprobantes estándar (FA, BA, FC, FD, T, CO).
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <form action="{{ route('admin.sucursales.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Sucursal *</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" maxlength="150" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600] focus:border-[#F7D600] @error('nombre') border-red-500 @enderror"
                            placeholder="Ej: Sucursal Centro, Tienda Miraflores…">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input type="text" name="direccion" value="{{ old('direccion') }}" maxlength="300"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                        <input type="text" name="departamento" value="{{ old('departamento') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                        <input type="text" name="provincia" value="{{ old('provincia') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Distrito</label>
                        <input type="text" name="distrito" value="{{ old('distrito') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ubigeo</label>
                        <input type="text" name="ubigeo" value="{{ old('ubigeo') }}" maxlength="6"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]"
                            placeholder="150101">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono') }}" maxlength="20"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" maxlength="150"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#F7D600]">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="es_principal" id="es_principal" value="1" {{ old('es_principal') ? 'checked' : '' }}
                            class="w-4 h-4 text-[#2B2E2C] border-gray-300 rounded focus:ring-[#F7D600]">
                        <label for="es_principal" class="text-sm font-medium text-gray-700">Marcar como Sucursal Principal</label>
                    </div>
                </div>

                <div class="flex gap-3 justify-end mt-6 pt-5 border-t">
                    <a href="{{ route('admin.sucursales.index') }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-5 rounded-lg transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" id="btn-crear"
                        class="bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] font-semibold py-2 px-6 rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i> Crear Sucursal
                    </button>
                </div>
            </form>
            <script>
                document.querySelector('form').addEventListener('submit', function () {
                    const btn = document.getElementById('btn-crear');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando…';
                });
            </script>
        </div>
    </div>
</div>
</body>
</html>
