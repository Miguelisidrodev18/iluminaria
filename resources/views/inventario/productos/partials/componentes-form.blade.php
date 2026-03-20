{{-- ─────────────────────────────────────────────────────────────────────────
    Partial: Gestión de componentes BOM para productos compuestos
    Incluir en edit.blade.php cuando tipo_sistema == 'compuesto'
───────────────────────────────────────────────────────────────────────── --}}

<div class="bg-white shadow rounded-lg overflow-hidden" id="seccion-bom">
    <div class="bg-amber-50 border-b border-amber-200 px-6 py-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <h3 class="text-base font-semibold text-amber-800">Componentes del Kit (BOM)</h3>
        <span class="ml-auto text-xs text-amber-600 bg-amber-100 px-2 py-1 rounded-full">
            {{ $producto->componentes->count() }} componente(s)
        </span>
    </div>

    <div class="p-6">

        {{-- ── Lista de componentes existentes ─────────────────────────────── --}}
        @if($producto->componentes->isEmpty())
            <p class="text-sm text-gray-500 italic mb-4">
                Aún no hay componentes. Agrega los productos que forman este kit.
            </p>
        @else
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full text-sm divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Orden</th>
                            <th class="px-3 py-2 text-left">Componente</th>
                            <th class="px-3 py-2 text-right">Cantidad</th>
                            <th class="px-3 py-2 text-left">Unidad</th>
                            <th class="px-3 py-2 text-center">Opcional</th>
                            <th class="px-3 py-2 text-right">Stock disp.</th>
                            <th class="px-3 py-2 text-left">Observación</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($producto->componentes as $comp)
                            <tr class="hover:bg-gray-50" x-data="{ editando: false }">
                                {{-- Vista normal --}}
                                <template x-if="!editando">
                                    <td class="px-3 py-2 text-center text-gray-400">{{ $comp->orden }}</td>
                                </template>
                                <template x-if="!editando">
                                    <td class="px-3 py-2">
                                        <span class="font-medium text-gray-800">{{ $comp->hijo?->nombre ?? '—' }}</span>
                                        @if($comp->variante)
                                            <span class="text-xs text-gray-400 ml-1">({{ $comp->variante->nombre_completo ?? $comp->variante->especificacion }})</span>
                                        @endif
                                        <br>
                                        <span class="text-xs text-gray-400">{{ $comp->hijo?->codigo }}</span>
                                    </td>
                                </template>
                                <template x-if="!editando">
                                    <td class="px-3 py-2 text-right font-medium">{{ number_format($comp->cantidad, 3) }}</td>
                                </template>
                                <template x-if="!editando">
                                    <td class="px-3 py-2 text-gray-500">{{ $comp->unidad }}</td>
                                </template>
                                <template x-if="!editando">
                                    <td class="px-3 py-2 text-center">
                                        @if($comp->es_opcional)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">Opcional</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Requerido</span>
                                        @endif
                                    </td>
                                </template>
                                <template x-if="!editando">
                                    <td class="px-3 py-2 text-right">
                                        @php $stock = $comp->stock_disponible; @endphp
                                        <span class="{{ $stock > 0 ? 'text-green-600' : 'text-red-500' }} font-medium">
                                            {{ $stock }}
                                        </span>
                                    </td>
                                </template>
                                <template x-if="!editando">
                                    <td class="px-3 py-2 text-xs text-gray-400">{{ $comp->observacion ?? '—' }}</td>
                                </template>
                                <template x-if="!editando">
                                    <td class="px-3 py-2 text-right whitespace-nowrap">
                                        <button @click="editando = true"
                                                type="button"
                                                class="text-indigo-600 hover:text-indigo-800 text-xs mr-2">Editar</button>
                                        <form action="{{ route('inventario.productos.componentes.destroy', $comp) }}"
                                              method="POST" class="inline"
                                              onsubmit="return confirm('¿Eliminar este componente?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Quitar</button>
                                        </form>
                                    </td>
                                </template>

                                {{-- Fila de edición inline --}}
                                <template x-if="editando">
                                    <td colspan="8" class="px-3 py-3">
                                        <form action="{{ route('inventario.productos.componentes.update', $comp) }}"
                                              method="POST" class="flex flex-wrap gap-2 items-end">
                                            @csrf @method('PUT')
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Cantidad</label>
                                                <input type="number" name="cantidad" step="0.001" min="0.001"
                                                       value="{{ $comp->cantidad }}"
                                                       class="w-24 border-gray-300 rounded text-sm px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Unidad</label>
                                                <input type="text" name="unidad" value="{{ $comp->unidad }}" maxlength="20"
                                                       class="w-24 border-gray-300 rounded text-sm px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Orden</label>
                                                <input type="number" name="orden" min="0" value="{{ $comp->orden }}"
                                                       class="w-16 border-gray-300 rounded text-sm px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            <div class="flex items-center gap-1 mt-4">
                                                <input type="checkbox" name="es_opcional" id="opc_{{ $comp->id }}" value="1"
                                                       {{ $comp->es_opcional ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-indigo-600">
                                                <label for="opc_{{ $comp->id }}" class="text-xs text-gray-600">Opcional</label>
                                            </div>
                                            <div class="flex-1 min-w-[150px]">
                                                <label class="block text-xs text-gray-500 mb-1">Observación</label>
                                                <input type="text" name="observacion" value="{{ $comp->observacion }}" maxlength="255"
                                                       class="w-full border-gray-300 rounded text-sm px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            <div class="flex gap-2 mt-1">
                                                <button type="submit"
                                                        class="px-3 py-1.5 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                                                    Guardar
                                                </button>
                                                <button type="button" @click="editando = false"
                                                        class="px-3 py-1.5 bg-gray-200 text-gray-700 text-xs rounded hover:bg-gray-300">
                                                    Cancelar
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </template>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ── Formulario para agregar nuevo componente ──────────────────────── --}}
        <div class="border border-dashed border-gray-300 rounded-lg p-4 bg-gray-50"
             x-data="bomBuscador()" x-init="init()">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Agregar componente</h4>

            <form action="{{ route('inventario.productos.componentes.store', $producto) }}"
                  method="POST" class="space-y-3">
                @csrf

                {{-- Búsqueda de producto componente --}}
                <div class="relative" x-ref="contenedor">
                    <label class="block text-xs text-gray-500 mb-1">Producto componente <span class="text-red-500">*</span></label>
                    <input type="hidden" name="hijo_id" x-model="hijoId" required>
                    <input type="text"
                           x-model="busqueda"
                           @input.debounce.300ms="buscar()"
                           @focus="mostrarResultados = true"
                           placeholder="Buscar por código o nombre..."
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                           autocomplete="off">

                    {{-- Dropdown de resultados --}}
                    <div x-show="mostrarResultados && resultados.length > 0"
                         @click.outside="mostrarResultados = false"
                         class="absolute z-20 left-0 right-0 bg-white border border-gray-200 rounded shadow-lg mt-1 max-h-48 overflow-y-auto">
                        <template x-for="p in resultados" :key="p.id">
                            <div @click="seleccionar(p)"
                                 class="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm border-b border-gray-100 last:border-0">
                                <span class="font-medium text-gray-800" x-text="p.nombre"></span>
                                <span class="text-xs text-gray-400 ml-2" x-text="p.codigo"></span>
                                <span class="text-xs ml-2" :class="p.stock_actual > 0 ? 'text-green-600' : 'text-red-400'"
                                      x-text="'Stock: ' + p.stock_actual"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Producto seleccionado --}}
                    <div x-show="productoSeleccionado" class="mt-1 flex items-center gap-2 text-xs text-indigo-700">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="productoSeleccionado ? productoSeleccionado.nombre : ''"></span>
                        <button type="button" @click="limpiar()" class="text-red-400 hover:text-red-600">✕</button>
                    </div>
                </div>

                {{-- Variante (opcional) --}}
                <div x-show="variantes.length > 0">
                    <label class="block text-xs text-gray-500 mb-1">Variante específica (opcional)</label>
                    <select name="variante_id"
                            class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">— Sin variante (stock del producto base) —</option>
                        <template x-for="v in variantes" :key="v.id">
                            <option :value="v.id" x-text="v.nombre_completo ?? (v.especificacion ?? v.id)"></option>
                        </template>
                    </select>
                </div>

                {{-- Cantidad, unidad, orden, opcional --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Cantidad <span class="text-red-500">*</span></label>
                        <input type="number" name="cantidad" step="0.001" min="0.001" value="1" required
                               class="w-full border-gray-300 rounded text-sm px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Unidad</label>
                        <input type="text" name="unidad" value="unidad" maxlength="20"
                               class="w-full border-gray-300 rounded text-sm px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Orden</label>
                        <input type="number" name="orden" min="0" value="{{ $producto->componentes->count() }}"
                               class="w-full border-gray-300 rounded text-sm px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex items-end pb-2 gap-2">
                        <input type="checkbox" name="es_opcional" id="nuevo_opcional" value="1"
                               class="rounded border-gray-300 text-indigo-600">
                        <label for="nuevo_opcional" class="text-xs text-gray-600">Opcional</label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Observación</label>
                    <input type="text" name="observacion" maxlength="255" placeholder="Ej: Incluye tornillo M4 zincado"
                           class="w-full border-gray-300 rounded text-sm px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            :disabled="!hijoId"
                            class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded hover:bg-amber-700 disabled:opacity-40 disabled:cursor-not-allowed">
                        + Agregar componente
                    </button>
                </div>
            </form>
        </div>

    </div>{{-- /p-6 --}}

    {{-- ── Opción: descontar componentes al vender ────────────────────────── --}}
    <div class="border-t border-gray-100 px-6 py-4 bg-gray-50">
        <form action="{{ route('inventario.productos.update', $producto) }}" method="POST" id="form-descontar-comp">
            @csrf @method('PUT')
            {{-- Campos ocultos para no perder datos del producto al hacer submit de solo esta opción --}}
            <input type="hidden" name="_only_descontar_componentes" value="1">
            <div class="flex items-start gap-3">
                <div class="flex items-center h-5 mt-0.5">
                    <input type="checkbox" name="descontar_componentes" id="descontar_componentes"
                           value="1"
                           {{ $producto->descontar_componentes ? 'checked' : '' }}
                           onchange="this.form.submit()"
                           class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                </div>
                <div>
                    <label for="descontar_componentes" class="text-sm font-medium text-gray-700 cursor-pointer">
                        Descontar stock de los componentes al vender este kit
                    </label>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Si está activado, al vender este kit se descuenta el stock de cada componente en lugar del kit mismo.
                        Útil cuando el kit se arma en el momento de la venta. Si lo tienes armado en almacén, déjalo desactivado.
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function bomBuscador() {
    return {
        busqueda: '',
        hijoId: null,
        resultados: [],
        mostrarResultados: false,
        productoSeleccionado: null,
        variantes: [],

        init() {},

        async buscar() {
            if (this.busqueda.length < 2) {
                this.resultados = [];
                return;
            }
            try {
                const resp = await fetch(`/inventario/productos/buscar?q=${encodeURIComponent(this.busqueda)}&limit=10`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await resp.json();
                this.resultados = data.productos ?? data;
                this.mostrarResultados = true;
            } catch (e) {
                console.error('Error buscando productos', e);
            }
        },

        async seleccionar(producto) {
            this.productoSeleccionado = producto;
            this.hijoId = producto.id;
            this.busqueda = producto.nombre + ' (' + producto.codigo + ')';
            this.mostrarResultados = false;

            // Cargar variantes del producto seleccionado
            try {
                const resp = await fetch(`/api/variantes/${producto.id}`);
                const data = await resp.json();
                this.variantes = data;
            } catch (e) {
                this.variantes = [];
            }
        },

        limpiar() {
            this.busqueda = '';
            this.hijoId = null;
            this.productoSeleccionado = null;
            this.variantes = [];
            this.resultados = [];
        }
    }
}
</script>
