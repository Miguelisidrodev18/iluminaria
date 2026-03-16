@extends('layouts.app-layout')
@section('title', 'Nuevo Espacio')

@section('content')
<div class="max-w-2xl mx-auto px-4">
    <div class="mb-6">
        <a href="{{ route('luminarias.espacios-proyecto.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i>Volver
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Nuevo Espacio</h1>
    </div>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-yellow-500 px-6 py-3">
            <h2 class="text-white font-semibold"><i class="fas fa-plus mr-2"></i>Datos del espacio</h2>
        </div>
        <form method="POST" action="{{ route('luminarias.espacios-proyecto.store') }}" class="p-6 space-y-4">
            @csrf
            @include('luminarias.espacios-proyecto.form')
            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('luminarias.espacios-proyecto.index') }}" class="px-4 py-2 border rounded-lg text-gray-600">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                    <i class="fas fa-save mr-2"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
