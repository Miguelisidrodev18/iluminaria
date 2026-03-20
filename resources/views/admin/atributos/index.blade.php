<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atributos del Catálogo</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Atributos del Catálogo</h1>
            <p class="text-sm text-gray-500 mt-0.5">Define los campos dinámicos que aparecen en el configurador de productos</p>
        </div>
        <a href="{{ route('admin.atributos.create') }}"
           class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors shadow-sm">
            <i class="fas fa-plus"></i> Nuevo Atributo
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center gap-2">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @php
        $grupoLabels = \App\Models\Catalogo\CatalogoAtributo::GRUPOS;
        $grupoIconos = [
            'tecnico'     => ['icon' => 'fa-bolt',        'color' => 'blue'],
            'comercial'   => ['icon' => 'fa-store',       'color' => 'green'],
            'instalacion' => ['icon' => 'fa-tools',       'color' => 'orange'],
            'estetico'    => ['icon' => 'fa-paint-brush', 'color' => 'purple'],
        ];
        $tipoLabels = \App\Models\Catalogo\CatalogoAtributo::TIPOS;
        $colorBadge = [
            'blue'   => 'bg-blue-100 text-blue-700',
            'green'  => 'bg-green-100 text-green-700',
            'orange' => 'bg-orange-100 text-orange-700',
            'purple' => 'bg-purple-100 text-purple-700',
        ];
    @endphp

    @if($atributos->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 py-20 text-center">
            <i class="fas fa-sliders-h text-5xl text-gray-200 mb-4 block"></i>
            <p class="text-gray-400 mb-4">No hay atributos configurados aún.</p>
            <a href="{{ route('admin.atributos.create') }}"
               class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-5 rounded-lg transition-colors">
                <i class="fas fa-plus"></i> Crear primer atributo
            </a>
        </div>
    @else
        @foreach($atributos as $grupo => $lista)
            @php
                $cfg   = $grupoIconos[$grupo] ?? ['icon' => 'fa-tag', 'color' => 'gray'];
                $label = $grupoLabels[$grupo] ?? ucfirst($grupo);
                $badge = $colorBadge[$cfg['color']] ?? 'bg-gray-100 text-gray-700';
            @endphp
            <div class="mb-8">
                {{-- Grupo header --}}
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                        <i class="fas {{ $cfg['icon'] }}"></i>
                        {{ $label }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $lista->count() }} atributo(s)</span>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre / Slug</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Valores</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Config</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Uso</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($lista as $atributo)
                                <tr class="hover:bg-gray-50/50 transition-colors {{ $atributo->activo ? '' : 'opacity-50' }}">
                                    {{-- Nombre --}}
                                    <td class="px-5 py-3">
                                        <div class="font-medium text-gray-900 text-sm">{{ $atributo->nombre }}</div>
                                        <div class="text-xs text-gray-400 font-mono">{{ $atributo->slug }}</div>
                                        @if($atributo->unidad)
                                            <div class="text-xs text-gray-400">Unidad: {{ $atributo->unidad }}</div>
                                        @endif
                                    </td>

                                    {{-- Tipo --}}
                                    <td class="px-5 py-3">
                                        @php
                                            $tipoCls = [
                                                'select'      => 'bg-blue-50 text-blue-700',
                                                'multiselect' => 'bg-indigo-50 text-indigo-700',
                                                'number'      => 'bg-teal-50 text-teal-700',
                                                'text'        => 'bg-gray-100 text-gray-700',
                                                'checkbox'    => 'bg-pink-50 text-pink-700',
                                            ][$atributo->tipo] ?? 'bg-gray-100 text-gray-600';
                                        @endphp
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $tipoCls }}">
                                            {{ $tipoLabels[$atributo->tipo] ?? $atributo->tipo }}
                                        </span>
                                    </td>

                                    {{-- Valores --}}
                                    <td class="px-5 py-3">
                                        @if(in_array($atributo->tipo, ['select','multiselect']))
                                            <div class="flex flex-wrap gap-1 max-w-xs">
                                                @foreach($atributo->valoresActivos->take(4) as $valor)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-gray-100 rounded text-xs text-gray-600">
                                                        @if($valor->color_hex)
                                                            <span class="w-2 h-2 rounded-full" style="background:{{ $valor->color_hex }}"></span>
                                                        @endif
                                                        {{ $valor->texto_display }}
                                                    </span>
                                                @endforeach
                                                @if($atributo->valoresActivos->count() > 4)
                                                    <span class="text-xs text-gray-400">+{{ $atributo->valoresActivos->count() - 4 }} más</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Libre</span>
                                        @endif
                                    </td>

                                    {{-- Config flags --}}
                                    <td class="px-5 py-3">
                                        <div class="flex flex-col gap-1 text-xs">
                                            @if($atributo->requerido)
                                                <span class="text-red-500"><i class="fas fa-asterisk mr-1 text-xs"></i>Requerido</span>
                                            @endif
                                            @if($atributo->en_nombre_auto)
                                                <span class="text-yellow-600"><i class="fas fa-magic mr-1 text-xs"></i>Auto-nombre ({{ $atributo->orden_nombre }})</span>
                                            @endif
                                            @if(!$atributo->activo)
                                                <span class="text-gray-400"><i class="fas fa-eye-slash mr-1 text-xs"></i>Inactivo</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Uso --}}
                                    <td class="px-5 py-3">
                                        <span class="text-sm font-semibold {{ $atributo->producto_atributos_count > 0 ? 'text-gray-700' : 'text-gray-300' }}">
                                            {{ $atributo->producto_atributos_count }}
                                        </span>
                                        <span class="text-xs text-gray-400 ml-1">prod.</span>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.atributos.edit', $atributo) }}"
                                               class="p-1.5 text-gray-400 hover:text-yellow-600 transition-colors"
                                               title="Editar">
                                                <i class="fas fa-edit text-sm"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.atributos.destroy', $atributo) }}"
                                                  onsubmit="return confirm('¿Desactivar atributo «{{ $atributo->nombre }}»?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 text-gray-400 hover:text-red-500 transition-colors"
                                                        title="{{ $atributo->activo ? 'Desactivar' : 'Ya inactivo' }}"
                                                        {{ $atributo->activo ? '' : 'disabled' }}>
                                                    <i class="fas fa-ban text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif

</div>
</body>
</html>
