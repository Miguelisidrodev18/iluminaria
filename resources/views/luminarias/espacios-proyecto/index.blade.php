@extends('layouts.app-layout')
@section('title', 'Espacios de Proyecto')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-map-marker-alt mr-2 text-yellow-500"></i>Espacios de Proyecto</h1>
            <p class="text-sm text-gray-500 mt-1">Espacios dentro de cada tipo de proyecto</p>
        </div>
        <a href="{{ route('luminarias.espacios-proyecto.create') }}" class="px-4 py-2 bg-[#2B2E2C] text-white rounded-lg hover:bg-[#2B2E2C]">
            <i class="fas fa-plus mr-2"></i>Nuevo espacio
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Espacio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo de Proyecto</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($espacios as $espacio)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $espacio->nombre }}</td>
                    <td class="px-6 py-4 text-gray-600">
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">
                            {{ $espacio->tipoProyecto->nombre ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs rounded-full {{ $espacio->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $espacio->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('luminarias.espacios-proyecto.edit', $espacio) }}" class="text-[#2B2E2C] hover:text-[#2B2E2C]">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('luminarias.espacios-proyecto.destroy', $espacio) }}" class="inline"
                              onsubmit="return confirm('¿Eliminar este espacio?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Sin espacios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
