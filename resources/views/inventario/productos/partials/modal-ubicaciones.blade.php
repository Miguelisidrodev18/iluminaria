{{--
    Partial: Modal de Ubicaciones Múltiples
    Variables: $ubicaciones (Collection), $producto (nullable para edit)
--}}

@php
    // Ubicaciones ya asignadas (edit) o vacío (create)
    $ubicacionesAsignadas = [];
    if (isset($producto) && $producto->ubicaciones) {
        foreach ($producto->ubicaciones as $ub) {
            $ubicacionesAsignadas[] = [
                'id'          => $ub->id,
                'nombre'      => $ub->nombre,
                'tipo'        => $ub->tipo,
                'cantidad'    => $ub->pivot->cantidad,
                'observacion' => $ub->pivot->observacion ?? '',
            ];
        }
    }

    // Catálogo completo para lookups en Alpine (evita document.querySelector)
    $catalogoUbicaciones = $ubicaciones->map(fn($u) => [
        'id'     => $u->id,
        'nombre' => $u->nombre,
        'tipo'   => $u->tipo,
    ])->values()->all();
@endphp

<div x-data="ubicacionesManager(@js($ubicacionesAsignadas), @js($catalogoUbicaciones))">

    {{-- Botón trigger --}}
    <button type="button" @click="abrirModal()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#F7D600] text-[#2B2E2C] text-sm font-medium rounded-lg hover:bg-[#e8c900] transition-colors shadow-sm">
        <i class="fas fa-map-marker-alt"></i>
        Asignar Ubicaciones
        <span x-show="items.length > 0"
              class="ml-1 bg-[#F7D600]/20 text-[#2B2E2C] text-xs rounded-full px-2 py-0.5"
              x-text="items.length + ' asignadas'"></span>
    </button>

    {{-- Resumen compacto de ubicaciones asignadas --}}
    <template x-if="items.length > 0">
        <div class="mt-3 space-y-1">
            <template x-for="(item, i) in items" :key="i">
                <div class="flex items-center justify-between bg-[#2B2E2C]/10 border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <span class="text-[#2B2E2C] font-medium" x-text="item.nombre"></span>
                    <span class="text-[#2B2E2C]">
                        <span x-text="item.cantidad"></span> uds.
                    </span>
                    {{-- Campos ocultos para enviar al formulario --}}
                    <input type="hidden" :name="'ubicaciones[' + i + '][id]'" :value="item.id">
                    <input type="hidden" :name="'ubicaciones[' + i + '][cantidad]'" :value="item.cantidad">
                    <input type="hidden" :name="'ubicaciones[' + i + '][observacion]'" :value="item.observacion || ''">
                </div>
            </template>
        </div>
    </template>

    {{-- MODAL --}}
    <div x-show="modalAbierto" x-transition.opacity
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display:none">

        {{-- Overlay --}}
        <div class="fixed inset-0 bg-black/40" @click="cerrarModal()"></div>

        {{-- Panel --}}
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg"
                 @click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            <i class="fas fa-map-marker-alt mr-2 text-[#2B2E2C]"></i>
                            Ubicaciones del Producto
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">Asigna dónde se almacena este producto</p>
                    </div>
                    <button type="button" @click="cerrarModal()"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 space-y-5">

                    {{-- Selector + cantidad --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-medium text-gray-600">Ubicación</label>
                                <a href="{{ route('catalogo.ubicaciones.index') }}" target="_blank"
                                   class="text-xs text-[#2B2E2C] hover:text-[#2B2E2C]">
                                    <i class="fas fa-cog mr-1"></i>Gestionar ubicaciones
                                </a>
                            </div>
                            @if($ubicaciones->isEmpty())
                            <div class="w-full px-3 py-2 border border-dashed border-orange-300 rounded-lg text-xs text-orange-500 text-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                No hay ubicaciones. <a href="{{ route('catalogo.ubicaciones.index') }}" target="_blank" class="font-semibold underline">Crear aquí</a>
                            </div>
                            @else
                            <select x-model="seleccionId"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                                <option value="">-- Seleccionar --</option>
                                @foreach($ubicaciones as $ub)
                                <option value="{{ $ub->id }}"
                                        data-nombre="{{ $ub->nombre }}"
                                        data-tipo="{{ $ub->tipo }}">
                                    {{ $ub->nombre }} ({{ \App\Models\Ubicacion::TIPOS[$ub->tipo] ?? $ub->tipo }})
                                </option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Cantidad</label>
                            <input type="number" x-model="seleccionCantidad" min="0"
                                   placeholder="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Observación (opcional)</label>
                        <input type="text" x-model="seleccionObservacion"
                               placeholder="Ej: Estante 3, nivel B..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F7D600]">
                    </div>

                    <button type="button" @click="agregar()"
                            class="w-full py-2 bg-[#F7D600] text-[#2B2E2C] text-sm font-medium rounded-lg hover:bg-[#e8c900] transition-colors">
                        <i class="fas fa-plus mr-2"></i>Agregar Ubicación
                    </button>

                    {{-- Lista dinámica --}}
                    <div x-show="items.length > 0">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Ubicaciones asignadas</p>
                        <div class="space-y-2 max-h-52 overflow-y-auto">
                            <template x-for="(item, i) in items" :key="i">
                                <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800" x-text="item.nombre"></p>
                                        <p class="text-xs text-gray-400" x-text="item.observacion || '—'"></p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <input type="number"
                                               x-model.number="item.cantidad"
                                               min="0"
                                               class="w-16 text-center px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-[#F7D600]">
                                        <button type="button" @click="quitar(i)"
                                                class="text-red-400 hover:text-red-600 transition-colors">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="items.length === 0" class="text-center py-6 text-gray-400">
                        <i class="fas fa-map-marker-alt text-2xl mb-2 opacity-30"></i>
                        <p class="text-sm">Sin ubicaciones asignadas</p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end">
                    <button type="button" @click="cerrarModal()"
                            class="px-5 py-2 bg-[#F7D600] text-[#2B2E2C] text-sm font-medium rounded-lg hover:bg-[#e8c900] transition-colors">
                        <i class="fas fa-check mr-2"></i>Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function ubicacionesManager(inicial = [], catalogo = []) {
    return {
        modalAbierto: false,
        items: inicial,
        catalogoMap: Object.fromEntries(catalogo.map(u => [String(u.id), u])),
        seleccionId: '',
        seleccionCantidad: 0,
        seleccionObservacion: '',

        abrirModal() { this.modalAbierto = true; },
        cerrarModal() { this.modalAbierto = false; },

        agregar() {
            if (!this.seleccionId || this.seleccionCantidad < 0) return;

            const existente = this.items.find(i => String(i.id) === String(this.seleccionId));
            if (existente) {
                existente.cantidad = parseInt(this.seleccionCantidad);
                existente.observacion = this.seleccionObservacion;
            } else {
                const ub = this.catalogoMap[String(this.seleccionId)];
                if (!ub) return;
                this.items.push({
                    id:          ub.id,
                    nombre:      ub.nombre,
                    tipo:        ub.tipo,
                    cantidad:    parseInt(this.seleccionCantidad),
                    observacion: this.seleccionObservacion,
                });
            }

            this.seleccionId = '';
            this.seleccionCantidad = 0;
            this.seleccionObservacion = '';
        },

        quitar(i) { this.items.splice(i, 1); },
    };
}
</script>
