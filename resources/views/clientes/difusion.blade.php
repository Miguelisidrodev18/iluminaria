<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listas de Difusión WhatsApp - Luminarios Kyrios</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Listas de Difusión WhatsApp" subtitle="Segmenta y exporta contactos para campañas de difusión" />

        <div class="flex items-center mb-6">
            <a href="{{ route('clientes.index') }}" class="text-[#2B2E2C] hover:opacity-70 mr-4"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">Difusión por Segmento</h2>
        </div>

        {{-- Guía de uso --}}
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex gap-3">
            <div class="text-green-500 text-2xl"><i class="fab fa-whatsapp"></i></div>
            <div>
                <p class="text-sm font-semibold text-green-800">¿Cómo usar las listas de difusión?</p>
                <ol class="text-xs text-green-700 mt-1 list-decimal list-inside space-y-0.5">
                    <li>Selecciona el segmento de clientes (Ej: "Mamá" para el Día de la Madre)</li>
                    <li>Descarga el Excel con los números de celular</li>
                    <li>En WhatsApp Business: <strong>Menú → Nueva Difusión → Agregar contactos</strong></li>
                    <li>Solo se incluyen clientes con celular registrado y que aceptan WhatsApp</li>
                </ol>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Panel de segmentos --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">
                        <i class="fas fa-tag mr-2 text-[#F7D600]"></i>Seleccionar Segmento
                    </h3>

                    @php
                        $grupos = [
                            'Fechas especiales' => ['Mamá', 'Papá'],
                            'Género'            => ['Mujer', 'Hombre'],
                            'Profesión'         => ['Arquitecto/a', 'Ingeniero/a', 'Diseñador/a', 'Decorador/a', 'Paisajista', 'Cliente Final'],
                        ];
                        $colores = [
                            'Mamá'         => 'bg-rose-100 text-rose-700 border-rose-300 hover:bg-rose-200',
                            'Papá'         => 'bg-sky-100 text-sky-700 border-sky-300 hover:bg-sky-200',
                            'Mujer'        => 'bg-pink-100 text-pink-700 border-pink-300 hover:bg-pink-200',
                            'Hombre'       => 'bg-blue-100 text-blue-700 border-blue-300 hover:bg-blue-200',
                            'Arquitecto/a' => 'bg-indigo-100 text-indigo-700 border-indigo-300 hover:bg-indigo-200',
                            'Ingeniero/a'  => 'bg-violet-100 text-violet-700 border-violet-300 hover:bg-violet-200',
                            'Diseñador/a'  => 'bg-purple-100 text-purple-700 border-purple-300 hover:bg-purple-200',
                            'Decorador/a'  => 'bg-fuchsia-100 text-fuchsia-700 border-fuchsia-300 hover:bg-fuchsia-200',
                            'Paisajista'   => 'bg-green-100 text-green-700 border-green-300 hover:bg-green-200',
                            'Cliente Final'=> 'bg-gray-100 text-gray-600 border-gray-300 hover:bg-gray-200',
                        ];
                        $iconos = [
                            'Mamá'         => '💐',
                            'Papá'         => '👔',
                            'Mujer'        => '👩',
                            'Hombre'       => '👨',
                            'Arquitecto/a' => '🏛️',
                            'Ingeniero/a'  => '⚙️',
                            'Diseñador/a'  => '🎨',
                            'Decorador/a'  => '🪴',
                            'Paisajista'   => '🌿',
                            'Cliente Final'=> '🏠',
                        ];
                    @endphp

                    @foreach($grupos as $grupo => $items)
                        <div class="mb-4">
                            <p class="text-xs text-gray-400 uppercase font-medium mb-2">{{ $grupo }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($items as $etiq)
                                    <a href="{{ route('clientes.difusion', ['etiqueta' => $etiq]) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border text-sm font-medium
                                              transition-all cursor-pointer
                                              {{ $colores[$etiq] ?? 'bg-gray-100 text-gray-600 border-gray-300' }}
                                              {{ $etiquetaSeleccionada === $etiq ? 'ring-2 ring-offset-1 ring-gray-500 font-bold' : '' }}">
                                        <span>{{ $iconos[$etiq] ?? '' }}</span>
                                        {{ $etiq }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Info de la campaña --}}
                @if($etiquetaSeleccionada)
                    <div class="mt-4 bg-white rounded-xl shadow-md p-5">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-chart-bar mr-2 text-[#F7D600]"></i>Resumen
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Segmento</span>
                                <span class="font-semibold">{{ $etiquetaSeleccionada }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Contactos listos</span>
                                <span class="font-bold text-green-600">{{ $total }}</span>
                            </div>
                        </div>
                        @if($total > 0)
                            <div class="mt-4">
                                <a href="{{ route('clientes.difusion.exportar', ['etiqueta' => $etiquetaSeleccionada]) }}"
                                   class="w-full flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600
                                          text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors">
                                    <i class="fas fa-file-excel"></i>
                                    Descargar Excel para WhatsApp
                                </a>
                                <p class="text-xs text-gray-400 mt-2 text-center">
                                    Columnas: Nombre, Celular, Empresa
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Lista de contactos --}}
            <div class="lg:col-span-2">
                @if(!$etiquetaSeleccionada)
                    <div class="bg-white rounded-xl shadow-md flex flex-col items-center justify-center py-24 text-center">
                        <div class="text-6xl mb-4">📋</div>
                        <p class="text-gray-500 font-medium">Selecciona un segmento</p>
                        <p class="text-sm text-gray-400 mt-1">para ver los contactos disponibles</p>
                    </div>
                @elseif($total === 0)
                    <div class="bg-white rounded-xl shadow-md flex flex-col items-center justify-center py-24 text-center">
                        <i class="fas fa-user-slash text-5xl text-gray-200 mb-4"></i>
                        <p class="text-gray-500 font-medium">Sin contactos en "{{ $etiquetaSeleccionada }}"</p>
                        <p class="text-sm text-gray-400 mt-1">Asigna esta etiqueta a tus clientes primero</p>
                        <a href="{{ route('clientes.index') }}" class="mt-4 text-sm text-blue-500 hover:underline">
                            <i class="fas fa-users mr-1"></i>Ir a gestionar clientes
                        </a>
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b bg-gray-50">
                            <div>
                                <h3 class="font-semibold text-gray-800">
                                    {{ $iconos[$etiquetaSeleccionada] ?? '' }}
                                    Lista: {{ $etiquetaSeleccionada }}
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $total }} contacto(s) con WhatsApp habilitado</p>
                            </div>
                            <a href="{{ route('clientes.difusion.exportar', ['etiqueta' => $etiquetaSeleccionada]) }}"
                               class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white
                                      font-semibold py-2 px-4 rounded-lg text-sm transition-colors">
                                <i class="fas fa-file-excel"></i> Exportar
                            </a>
                        </div>
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                                    <th class="px-5 py-3 text-left">#</th>
                                    <th class="px-5 py-3 text-left">Nombre</th>
                                    <th class="px-5 py-3 text-left">Celular</th>
                                    <th class="px-5 py-3 text-left hidden md:table-cell">Empresa</th>
                                    <th class="px-5 py-3 text-left hidden lg:table-cell">Otras etiquetas</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($clientes as $i => $c)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 text-xs text-gray-400">{{ $i + 1 }}</td>
                                        <td class="px-5 py-3 text-sm">
                                            <a href="{{ route('clientes.show', $c) }}"
                                               class="font-medium text-[#2B2E2C] hover:text-[#F7D600] hover:underline">
                                                {{ strtoupper($c->apellidos) }}, {{ $c->nombres }}
                                            </a>
                                        </td>
                                        <td class="px-5 py-3 text-sm">
                                            <a href="https://wa.me/51{{ preg_replace('/\D/', '', $c->celular) }}"
                                               target="_blank"
                                               class="text-green-600 hover:text-green-800 font-medium hover:underline">
                                                <i class="fab fa-whatsapp mr-1"></i>{{ $c->celular }}
                                            </a>
                                        </td>
                                        <td class="px-5 py-3 text-sm text-gray-500 hidden md:table-cell">
                                            {{ $c->empresa ?? '—' }}
                                        </td>
                                        <td class="px-5 py-3 hidden lg:table-cell">
                                            @php
                                                $otrasEtiquetas = array_filter($c->etiquetas ?? [], fn($e) => $e !== $etiquetaSeleccionada);
                                            @endphp
                                            @if(count($otrasEtiquetas))
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($otrasEtiquetas as $otra)
                                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $otra }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-sm">
                                            <a href="{{ route('clientes.edit', $c) }}"
                                               class="text-gray-400 hover:text-yellow-500" title="Editar">
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
