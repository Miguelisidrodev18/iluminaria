@extends('layouts.app-layout')
@section('title', 'Tipos de Proyecto')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-folder-open mr-2 text-yellow-500"></i>Tipos de Proyecto</h1>
            <p class="text-sm text-gray-500 mt-1">Clasificación de proyectos de iluminación</p>
        </div>
        <a href="{{ route('luminarias.tipos-proyecto.create') }}" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
            <i class="fas fa-plus mr-2"></i>Nuevo tipo
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Espacios</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tipos as $tipo)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-900">
                        @if($tipo->icono)<i class="fas fa-{{ $tipo->icono }} mr-2 text-yellow-500"></i>@endif
                        {{ $tipo->nombre }}
                    </td>
                    <td class="px-6 py-4 text-center text-gray-600">{{ $tipo->espacios_count }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs rounded-full {{ $tipo->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $tipo->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('luminarias.tipos-proyecto.edit', $tipo) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('luminarias.tipos-proyecto.destroy', $tipo) }}" class="inline"
                              onsubmit="return confirm('¿Eliminar este tipo?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Sin tipos de proyecto registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
