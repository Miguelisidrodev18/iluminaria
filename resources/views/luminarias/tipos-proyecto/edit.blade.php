@extends('layouts.app-layout')
@section('title', 'Editar Tipo de Proyecto')

@section('content')
<div class="max-w-2xl mx-auto px-4">
    <div class="mb-6">
        <a href="{{ route('luminarias.tipos-proyecto.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i>Volver
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Editar: {{ $tipo->nombre }}</h1>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-yellow-500 px-6 py-3">
            <h2 class="text-white font-semibold"><i class="fas fa-edit mr-2"></i>Editar tipo de proyecto</h2>
        </div>
        <form method="POST" action="{{ route('luminarias.tipos-proyecto.update', $tipo) }}" class="p-6 space-y-4">
            @csrf @method('PUT')
            @include('luminarias.tipos-proyecto.form')
            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('luminarias.tipos-proyecto.index') }}" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                    <i class="fas fa-save mr-2"></i>Actualizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
