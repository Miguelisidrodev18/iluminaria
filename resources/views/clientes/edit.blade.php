<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Luminarios Kyrios</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Editar Cliente" subtitle="{{ $cliente->nombre_completo }}" />

        <div class="max-w-4xl mx-auto">
            <div class="flex items-center mb-6">
                <a href="{{ route('clientes.show', $cliente) }}" class="text-[#2B2E2C] hover:opacity-70 mr-4"><i class="fas fa-arrow-left"></i></a>
                <h2 class="text-2xl font-bold text-gray-800">Editar Cliente</h2>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <form action="{{ route('clientes.update', $cliente) }}" method="POST">
                    @csrf @method('PUT')

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <ul class="text-sm text-red-700 list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    @php
                        $inp = 'w-full rounded-lg shadow-sm text-sm focus:border-[#F7D600] focus:ring focus:ring-yellow-100 border-gray-300';
                        $v = fn($field) => old($field, $cliente->$field);
                        $err = fn($field) => $errors->has($field) ? 'border-red-500' : 'border-gray-300';
                    @endphp

                    {{-- Sección 1: Identificación --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4 pb-2 border-b">
                            <i class="fas fa-id-card mr-2 text-[#F7D600]"></i>Identificación
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Documento</label>
                                <select name="tipo_documento" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-[#F7D600] focus:ring focus:ring-yellow-100">
                                    <option value="">— Seleccionar —</option>
                                    @foreach(['DNI','RUC','CE'] as $td)
                                        <option value="{{ $td }}" {{ $v('tipo_documento') === $td ? 'selected' : '' }}>{{ $td }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Número Documento</label>
                                <input type="text" name="numero_documento" maxlength="15"
                                       class="w-full rounded-lg shadow-sm text-sm focus:border-[#F7D600] focus:ring focus:ring-yellow-100 {{ $err('numero_documento') }}"
                                       value="{{ $v('numero_documento') }}">
                                @error('numero_documento')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="estado" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-[#F7D600] focus:ring focus:ring-yellow-100">
                                    <option value="activo" {{ $v('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ $v('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Sección 2: Datos Personales --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4 pb-2 border-b">
                            <i class="fas fa-user mr-2 text-[#F7D600]"></i>Datos Personales
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos <span class="text-red-500">*</span></label>
                                <input type="text" name="apellidos" required
                                       class="w-full rounded-lg shadow-sm text-sm focus:border-[#F7D600] focus:ring focus:ring-yellow-100 {{ $err('apellidos') }}"
                                       value="{{ $v('apellidos') }}">
                                @error('apellidos')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombres <span class="text-red-500">*</span></label>
                                <input type="text" name="nombres" required
                                       class="w-full rounded-lg shadow-sm text-sm focus:border-[#F7D600] focus:ring focus:ring-yellow-100 {{ $err('nombres') }}"
                                       value="{{ $v('nombres') }}">
                                @error('nombres')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                                <input type="text" name="dni" maxlength="20"
                                       class="w-full rounded-lg shadow-sm text-sm focus:border-[#F7D600] focus:ring focus:ring-yellow-100 {{ $err('dni') }}"
                                       value="{{ $v('dni') }}">
                                @error('dni')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de cumpleaños</label>
                                <input type="date" name="fecha_cumpleanos"
                                       class="{{ $inp }}"
                                       value="{{ $cliente->fecha_cumpleanos?->format('Y-m-d') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Celular <span class="text-red-500">*</span></label>
                                <input type="text" name="celular" required maxlength="20"
                                       class="w-full rounded-lg shadow-sm text-sm focus:border-[#F7D600] focus:ring focus:ring-yellow-100 {{ $err('celular') }}"
                                       value="{{ $v('celular') }}">
                                @error('celular')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono casa</label>
                                <input type="text" name="telefono_casa" maxlength="20" class="{{ $inp }}" value="{{ $v('telefono_casa') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo personal</label>
                                <input type="email" name="correo_personal" class="{{ $inp }}" value="{{ $v('correo_personal') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ocupación / Cargo</label>
                                <input type="text" name="ocupacion" maxlength="100" class="{{ $inp }}" value="{{ $v('ocupacion') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
                                <input type="text" name="especialidad" maxlength="100" class="{{ $inp }}" value="{{ $v('especialidad') }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección de residencia</label>
                                <input type="text" name="direccion_residencia" class="{{ $inp }}" value="{{ $v('direccion_residencia') }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Redes sociales personales</label>
                                <textarea name="redes_personales" rows="2" class="{{ $inp }}">{{ $v('redes_personales') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Sección 3: Empresa --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4 pb-2 border-b">
                            <i class="fas fa-building mr-2 text-[#F7D600]"></i>Empresa
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Razón social / Empresa</label>
                                <input type="text" name="empresa" maxlength="200" class="{{ $inp }}" value="{{ $v('empresa') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">RUC</label>
                                <input type="text" name="ruc" maxlength="20" class="{{ $inp }}" value="{{ $v('ruc') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo empresa</label>
                                <input type="email" name="correo_empresa" class="{{ $inp }}" value="{{ $v('correo_empresa') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono empresa</label>
                                <input type="text" name="telefono_empresa" maxlength="20" class="{{ $inp }}" value="{{ $v('telefono_empresa') }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección empresa</label>
                                <input type="text" name="direccion_empresa" class="{{ $inp }}" value="{{ $v('direccion_empresa') }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Redes sociales empresa</label>
                                <textarea name="redes_empresa" rows="2" class="{{ $inp }}">{{ $v('redes_empresa') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Sección 4: CRM --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4 pb-2 border-b">
                            <i class="fas fa-chart-line mr-2 text-[#F7D600]"></i>CRM
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de cliente</label>
                                <select name="tipo_cliente" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-[#F7D600] focus:ring focus:ring-yellow-100">
                                    <option value="">— Seleccionar —</option>
                                    @foreach(['ARQ'=>'Arquitecto','ING'=>'Ingeniero','DIS'=>'Diseñador','PN'=>'Persona Natural','PJ'=>'Persona Jurídica'] as $val => $label)
                                        <option value="{{ $val }}" {{ $v('tipo_cliente') === $val ? 'selected' : '' }}>{{ $val }} — {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de registro</label>
                                <input type="date" name="fecha_registro" class="{{ $inp }}" value="{{ $cliente->fecha_registro?->format('Y-m-d') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Registrado por</label>
                                <input type="text" name="registrado_por" maxlength="100" class="{{ $inp }}" value="{{ $v('registrado_por') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Comisión (%)</label>
                                <input type="number" name="comision" min="0" max="100" step="0.01" class="{{ $inp }}" value="{{ $v('comision') }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Preferencias de productos</label>
                                <textarea name="preferencias" rows="2" class="{{ $inp }}">{{ $v('preferencias') }}</textarea>
                            </div>

                            {{-- Etiquetas de segmentación --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tags mr-1 text-[#F7D600]"></i>
                                    Etiquetas / Segmentos
                                    <span class="text-xs text-gray-400 ml-1">(para listas de difusión WhatsApp)</span>
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($etiquetasDisponibles as $etiq => $meta)
                                        @php
                                            $colorClasses = [
                                                'blue'   => 'border-blue-300 text-blue-700 peer-checked:bg-blue-100 peer-checked:border-blue-500',
                                                'indigo' => 'border-indigo-300 text-indigo-700 peer-checked:bg-indigo-100 peer-checked:border-indigo-500',
                                                'purple' => 'border-purple-300 text-purple-700 peer-checked:bg-purple-100 peer-checked:border-purple-500',
                                                'pink'   => 'border-pink-300 text-pink-700 peer-checked:bg-pink-100 peer-checked:border-pink-500',
                                                'green'  => 'border-green-300 text-green-700 peer-checked:bg-green-100 peer-checked:border-green-500',
                                                'gray'   => 'border-gray-300 text-gray-600 peer-checked:bg-gray-100 peer-checked:border-gray-500',
                                                'rose'   => 'border-rose-300 text-rose-700 peer-checked:bg-rose-100 peer-checked:border-rose-500',
                                                'sky'    => 'border-sky-300 text-sky-700 peer-checked:bg-sky-100 peer-checked:border-sky-500',
                                            ];
                                            $cls = $colorClasses[$meta['color']] ?? $colorClasses['gray'];
                                            $etiquetasActivas = old('etiquetas', $cliente->etiquetas ?? []);
                                            $checked = in_array($etiq, $etiquetasActivas);
                                            $inputId = 'etiq_' . Str::slug($etiq);
                                        @endphp
                                        <label for="{{ $inputId }}" class="cursor-pointer">
                                            <input type="checkbox" id="{{ $inputId }}" name="etiquetas[]"
                                                   value="{{ $etiq }}" class="sr-only peer" {{ $checked ? 'checked' : '' }}>
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full border text-sm font-medium select-none
                                                         transition-all {{ $cls }}">
                                                {{ $etiq }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Acepta WhatsApp --}}
                            <div class="md:col-span-2">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="acepta_whatsapp" value="0">
                                    <input type="checkbox" name="acepta_whatsapp" value="1"
                                           class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                           {{ old('acepta_whatsapp', $cliente->acepta_whatsapp ? '1' : '0') === '1' ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">
                                        <i class="fab fa-whatsapp text-green-500 mr-1"></i>
                                        Acepta recibir difusiones por WhatsApp
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <a href="{{ route('clientes.show', $cliente) }}"
                           class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-6 rounded-lg text-sm">Cancelar</a>
                        <button type="submit"
                                class="bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] font-semibold py-2 px-6 rounded-lg text-sm">
                            <i class="fas fa-save mr-2"></i>Actualizar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
