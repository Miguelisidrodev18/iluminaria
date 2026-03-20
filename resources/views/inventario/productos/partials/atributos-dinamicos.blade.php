{{--
    Partial: Configurador Dinámico de Atributos
    Variables esperadas:
      - $atributosGrupos   : Collection<string, Collection<CatalogoAtributo>> — agrupados por grupo
      - $atributosActuales : array  — { slug => valor } para pre-poblar en edit (nullable)
--}}

@php
    $atributosActuales ??= [];
    $grupoLabels = \App\Models\Catalogo\CatalogoAtributo::GRUPOS;
    $grupoIconos = [
        'tecnico'     => 'fa-bolt',
        'comercial'   => 'fa-store',
        'instalacion' => 'fa-tools',
        'estetico'    => 'fa-paint-brush',
    ];
    $grupoColores = [
        'tecnico'     => 'blue',
        'comercial'   => 'green',
        'instalacion' => 'orange',
        'estetico'    => 'purple',
    ];
@endphp

{{-- ══════════════════════════════════════════════════════
    BLOQUE CONFIGURADOR DE ATRIBUTOS DINÁMICOS
══════════════════════════════════════════════════════ --}}
<div id="bloqueAtributos"
     class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5"
     x-data="configuradorAtributos(@js($atributosActuales))">

    {{-- Header --}}
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100"
         style="background: linear-gradient(135deg, #1a3a2a 0%, #2B4A3A 100%);">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                 style="background-color:#F7D600;">
                <i class="fas fa-sliders-h text-sm" style="color:#1a3a2a;"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-white">Configurador de Atributos</h2>
                <p class="text-xs text-gray-300">Características técnicas y comerciales del producto</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-300">
                <span x-text="contarAtributosLlenos()"></span> atributo(s) configurado(s)
            </span>
            <button type="button" @click="limpiarTodo()"
                    class="text-xs text-gray-400 hover:text-red-300 transition-colors px-2 py-1 rounded">
                <i class="fas fa-undo mr-1"></i>Limpiar
            </button>
        </div>
    </div>

    <div class="p-6">

        {{-- Preview del nombre auto-generado --}}
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl"
             x-show="nombreAutoGenerado !== ''">
            <div class="flex items-start gap-3">
                <i class="fas fa-magic text-yellow-500 mt-0.5 shrink-0"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-yellow-700 mb-1">Nombre sugerido</p>
                    <p class="text-sm font-medium text-gray-900 break-words"
                       x-text="nombreAutoGenerado"></p>
                </div>
                <button type="button" @click="aplicarNombre()"
                        title="Usar este nombre"
                        class="shrink-0 px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded-lg hover:bg-yellow-600 transition-colors">
                    <i class="fas fa-check mr-1"></i>Usar
                </button>
            </div>
        </div>

        {{-- Grupos de atributos --}}
        @foreach($atributosGrupos as $grupo => $atributos)
            @php
                $color  = $grupoColores[$grupo] ?? 'gray';
                $icono  = $grupoIconos[$grupo] ?? 'fa-tag';
                $label  = $grupoLabels[$grupo] ?? ucfirst($grupo);
                $colorClasses = [
                    'blue'   => ['header' => 'bg-blue-50 border-blue-200',   'icon' => 'text-blue-600',  'badge' => 'bg-blue-100 text-blue-700'],
                    'green'  => ['header' => 'bg-green-50 border-green-200', 'icon' => 'text-green-600', 'badge' => 'bg-green-100 text-green-700'],
                    'orange' => ['header' => 'bg-orange-50 border-orange-200','icon' => 'text-orange-600','badge' => 'bg-orange-100 text-orange-700'],
                    'purple' => ['header' => 'bg-purple-50 border-purple-200','icon' => 'text-purple-600','badge' => 'bg-purple-100 text-purple-700'],
                    'gray'   => ['header' => 'bg-gray-50 border-gray-200',   'icon' => 'text-gray-600',  'badge' => 'bg-gray-100 text-gray-700'],
                ][$color] ?? [];
            @endphp
            <div class="mb-6">
                {{-- Encabezado del grupo --}}
                <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100">
                    <div class="w-6 h-6 rounded-md flex items-center justify-center {{ $colorClasses['badge'] }}">
                        <i class="fas {{ $icono }} text-xs"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">{{ $label }}</h3>
                    <span class="text-xs text-gray-400">({{ $atributos->count() }} campos)</span>
                </div>

                {{-- Grid de atributos --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($atributos as $atributo)
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-gray-600">
                                {{ $atributo->nombre }}
                                @if($atributo->unidad)
                                    <span class="text-gray-400 font-normal">({{ $atributo->unidad }})</span>
                                @endif
                                @if($atributo->requerido)
                                    <span class="text-red-500 ml-0.5">*</span>
                                @endif
                            </label>

                            {{-- TYPE: select --}}
                            @if($atributo->tipo === 'select')
                                <select name="atributos[{{ $atributo->slug }}]"
                                        @change="valores['{{ $atributo->slug }}'] = $event.target.value; generarNombre()"
                                        {{ $atributo->requerido ? 'required' : '' }}
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                                    <option value="">— Seleccionar —</option>
                                    @foreach($atributo->valoresActivos as $valor)
                                        <option value="{{ $valor->id }}"
                                                @selected(isset($atributosActuales[$atributo->slug]) && $atributosActuales[$atributo->slug] == $valor->id)>
                                            {{ $valor->texto_display }}
                                        </option>
                                    @endforeach
                                </select>

                            {{-- TYPE: multiselect (chips interactivos) --}}
                            @elseif($atributo->tipo === 'multiselect')
                                @php
                                    $selectedIds = isset($atributosActuales[$atributo->slug])
                                        ? (array) $atributosActuales[$atributo->slug]
                                        : [];
                                @endphp
                                <div x-data="multiSelectField('{{ $atributo->slug }}', @js($selectedIds))"
                                     class="space-y-2">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($atributo->valoresActivos as $valor)
                                            <button type="button"
                                                    @click="toggle({{ $valor->id }}); $nextTick(() => generarNombre())"
                                                    :class="seleccionados.includes({{ $valor->id }})
                                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                                        : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400'"
                                                    class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs border transition-all">
                                                @if($valor->color_hex)
                                                    <span class="w-3 h-3 rounded-full border border-white/50 shrink-0"
                                                          style="background-color:{{ $valor->color_hex }}"></span>
                                                @endif
                                                {{ $valor->texto_display }}
                                            </button>
                                        @endforeach
                                    </div>
                                    {{-- Hidden inputs para submit --}}
                                    <template x-for="id in seleccionados" :key="id">
                                        <input type="hidden"
                                               name="atributos[{{ $atributo->slug }}][]"
                                               :value="id">
                                    </template>
                                    <p x-show="seleccionados.length === 0"
                                       class="text-xs text-gray-400 italic">Ninguno seleccionado</p>
                                </div>

                            {{-- TYPE: number --}}
                            @elseif($atributo->tipo === 'number')
                                <input type="number"
                                       name="atributos[{{ $atributo->slug }}]"
                                       value="{{ $atributosActuales[$atributo->slug] ?? '' }}"
                                       placeholder="{{ $atributo->placeholder ?? '' }}"
                                       @input="valores['{{ $atributo->slug }}'] = $event.target.value; generarNombre()"
                                       {{ $atributo->requerido ? 'required' : '' }}
                                       step="any" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">

                            {{-- TYPE: text --}}
                            @elseif($atributo->tipo === 'text')
                                <input type="text"
                                       name="atributos[{{ $atributo->slug }}]"
                                       value="{{ $atributosActuales[$atributo->slug] ?? '' }}"
                                       placeholder="{{ $atributo->placeholder ?? '' }}"
                                       @input="valores['{{ $atributo->slug }}'] = $event.target.value; generarNombre()"
                                       {{ $atributo->requerido ? 'required' : '' }}
                                       maxlength="250"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400">

                            {{-- TYPE: checkbox --}}
                            @elseif($atributo->tipo === 'checkbox')
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="atributos[{{ $atributo->slug }}]" value="0">
                                    <input type="checkbox"
                                           name="atributos[{{ $atributo->slug }}]"
                                           value="1"
                                           @change="valores['{{ $atributo->slug }}'] = $event.target.checked ? '1' : '0'"
                                           {{ isset($atributosActuales[$atributo->slug]) && $atributosActuales[$atributo->slug] ? 'checked' : '' }}
                                           class="w-4 h-4 text-yellow-500 rounded focus:ring-yellow-400 border-gray-300">
                                    <span class="text-sm text-gray-700">Sí</span>
                                </label>
                            @endif

                            {{-- Descripción del atributo --}}
                            @if($atributo->descripcion)
                                <p class="text-xs text-gray-400">{{ $atributo->descripcion }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if($atributosGrupos->isEmpty())
            <div class="text-center py-10 text-gray-400">
                <i class="fas fa-database text-4xl mb-3 opacity-30"></i>
                <p class="text-sm">No hay atributos configurados.</p>
                <p class="text-xs mt-1">
                    <a href="{{ route('admin.atributos.create') }}" class="text-indigo-500 hover:underline">
                        Agregar atributos desde el panel admin
                    </a>
                </p>
            </div>
        @endif
    </div>
</div>

{{-- ── Script Alpine ────────────────────────────────────────────────────────── --}}
<script>
// ─── Configurador dinámico de atributos ──────────────────────────────────────
function configuradorAtributos(inicial = {}) {
    return {
        // valores = { slug: valor_id|texto } para atributos simples
        // multiselect se maneja en subcomponente multiSelectField
        valores: Object.assign({}, inicial),
        nombreAutoGenerado: '',

        // Atributos que participan en el nombre auto-generado, por orden
        // Se inyecta desde PHP en el script inline de cada página
        atributosParaNombre: window.ATRIBUTOS_PARA_NOMBRE ?? [],
        valoresMap:          window.VALORES_MAP ?? {},

        contarAtributosLlenos() {
            return Object.values(this.valores).filter(v => {
                if (Array.isArray(v)) return v.length > 0;
                return v !== null && v !== '' && v !== undefined;
            }).length;
        },

        generarNombre() {
            const partes = [];
            for (const item of this.atributosParaNombre) {
                const slug  = item.slug;
                const valor = this.valores[slug];
                if (!valor || valor === '') continue;

                if (Array.isArray(valor)) {
                    // multiselect: tomar primer valor
                    const textos = valor.map(id => this.valoresMap[id] ?? '').filter(Boolean);
                    if (textos.length > 0) partes.push(textos[0]);
                } else {
                    // select: buscar texto del valor_id; o number: usar directamente
                    const esNumerico = !isNaN(parseFloat(valor)) && !this.valoresMap[valor];
                    if (esNumerico) {
                        // Valor numérico libre (type=number) — agregar con unidad si aplica
                        partes.push(item.unidad ? valor + item.unidad : valor);
                    } else {
                        const texto = this.valoresMap[valor] ?? '';
                        if (texto) partes.push(texto);
                    }
                }
            }
            this.nombreAutoGenerado = partes.join(' ');
            this.$dispatch('nombre-generado', { nombre: this.nombreAutoGenerado });
        },

        aplicarNombre() {
            const inputNombre = document.getElementById('nombre');
            if (inputNombre && this.nombreAutoGenerado) {
                inputNombre.value = this.nombreAutoGenerado;
                inputNombre.dispatchEvent(new Event('input'));
            }
        },

        limpiarTodo() {
            this.valores = {};
            this.nombreAutoGenerado = '';
            // Resetear todos los selects e inputs
            this.$el.querySelectorAll('select').forEach(s => s.value = '');
            this.$el.querySelectorAll('input[type=number], input[type=text]').forEach(i => i.value = '');
        },
    };
}

// ─── Subcomponente: multiselect tipo chips ────────────────────────────────────
function multiSelectField(slug, inicial = []) {
    return {
        slug,
        seleccionados: [...inicial],

        toggle(id) {
            const idx = this.seleccionados.indexOf(id);
            if (idx >= 0) {
                this.seleccionados.splice(idx, 1);
            } else {
                this.seleccionados.push(id);
            }
            // Propaga al componente padre
            this.$dispatch('multiselect-change', { slug: this.slug, valores: [...this.seleccionados] });

            // También actualiza el objeto valores del padre (usando $closest)
            try {
                const padre = this.$root._x_dataStack?.find(d => d.valores !== undefined);
                if (padre) padre.valores[this.slug] = [...this.seleccionados];
            } catch(e) {}
        },
    };
}
</script>
