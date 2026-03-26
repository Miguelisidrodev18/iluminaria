<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aprobación de Productos</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .detalle-row { display: none; }
        .detalle-row.abierto { display: table-row; }
        .pill { display:inline-block; padding:1px 8px; border-radius:9999px; font-size:11px; font-weight:500; }
        .spec-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:8px; }
        .spec-item label { display:block; font-size:10px; color:#6b7280; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
        .spec-item span  { display:block; font-size:13px; color:#111827; }
    </style>
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Aprobación de Productos"
            subtitle="Productos importados en estado Borrador"
        />

        <div class="max-w-7xl mx-auto space-y-4">

            {{-- ── Toolbar ──────────────────────────────────────────────────── --}}
            <div class="flex flex-wrap items-center justify-between gap-3 bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <form method="GET" class="flex items-center gap-2 flex-1 min-w-0 max-w-sm">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               placeholder="Código, nombre..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <button type="submit" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors">
                        Buscar
                    </button>
                </form>

                <div class="flex items-center gap-3">
                    <span id="label-seleccionados" class="text-sm text-gray-500 hidden">
                        <span id="cnt-seleccionados" class="font-bold text-blue-600">0</span> seleccionados
                    </span>
                    <button id="btn-seleccionar-todo" class="text-sm text-gray-500 hover:text-gray-700 underline">
                        Seleccionar todos
                    </button>
                    <a href="{{ route('inventario.productos.edit', '__ID__') }}"
                       id="btn-editar-uno"
                       class="hidden inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-pen"></i> Editar producto
                    </a>
                    <button id="btn-aprobar-lote"
                            disabled
                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fas fa-check"></i>
                        Aprobar seleccionados
                    </button>
                </div>
            </div>

            {{-- ── Feedback ─────────────────────────────────────────────────── --}}
            <div id="alerta-aprobacion" class="hidden bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
                <i class="fas fa-check-circle text-green-600 text-lg"></i>
                <p class="text-sm text-green-700 font-medium" id="msg-aprobacion"></p>
            </div>

            {{-- ── Tabla ────────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="p-3 w-10">
                                <input type="checkbox" id="chk-todos"
                                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            </th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Cód. Fábrica</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Marca</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Variantes</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Creado</th>
                            <th class="p-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($productos as $producto)

                        {{-- ── Fila principal ── --}}
                        <tr class="hover:bg-blue-50/30 transition-colors cursor-pointer fila-producto"
                            data-id="{{ $producto->id }}"
                            data-detalle="detalle-{{ $producto->id }}">
                            <td class="p-3" onclick="event.stopPropagation()">
                                <input type="checkbox"
                                       class="chk-producto w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       value="{{ $producto->id }}" />
                            </td>
                            <td class="p-3 font-mono text-xs text-gray-500">{{ $producto->codigo_fabrica ?? '—' }}</td>
                            <td class="p-3">
                                <p class="font-medium text-gray-800 leading-tight">{{ $producto->nombre }}</p>
                                @if($producto->nombre_kyrios)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $producto->nombre_kyrios }}</p>
                                @endif
                            </td>
                            <td class="p-3 text-gray-600">{{ $producto->marca?->nombre ?? '—' }}</td>
                            <td class="p-3">
                                @if($producto->tipoProducto)
                                    <span class="pill bg-blue-50 text-blue-700">{{ $producto->tipoProducto->nombre }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="p-3 text-center text-gray-500">
                                {{ $producto->variantes->count() ?: '—' }}
                            </td>
                            <td class="p-3 text-gray-400 text-xs whitespace-nowrap">
                                {{ $producto->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="p-3 text-gray-400">
                                <i class="fas fa-chevron-right text-xs toggle-icon transition-transform duration-200"></i>
                            </td>
                        </tr>

                        {{-- ── Fila detalle (expandible) ── --}}
                        <tr id="detalle-{{ $producto->id }}" class="detalle-row bg-blue-50/20">
                            <td colspan="8" class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                                    {{-- Datos básicos --}}
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Datos básicos</p>
                                        <div class="space-y-1.5">
                                            @if($producto->tipoLuminaria)
                                            <div class="spec-item">
                                                <label>Tipo luminaria</label>
                                                <span>{{ $producto->tipoLuminaria->nombre }}</span>
                                            </div>
                                            @endif
                                            @if($producto->categoria)
                                            <div class="spec-item">
                                                <label>Categoría</label>
                                                <span>{{ $producto->categoria->nombre }}</span>
                                            </div>
                                            @endif
                                            @if($producto->linea)
                                            <div class="spec-item">
                                                <label>Línea</label>
                                                <span>{{ $producto->linea }}</span>
                                            </div>
                                            @endif
                                            @if($producto->procedencia)
                                            <div class="spec-item">
                                                <label>Procedencia</label>
                                                <span>{{ $producto->procedencia }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Especificaciones --}}
                                    @if($producto->especificacion)
                                    @php $esp = $producto->especificacion; @endphp
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Especificaciones</p>
                                        <div class="space-y-1.5">
                                            @foreach([
                                                'Tipo fuente'    => $esp->tipo_fuente,
                                                'Nivel potencia' => $esp->nivel_potencia,
                                                'Potencia'       => $esp->potencia ? $esp->potencia.'W' : null,
                                                'Voltaje'        => $esp->voltaje,
                                                'IP'             => $esp->ip,
                                                'IK'             => $esp->ik,
                                                'Ángulo'         => $esp->angulo_apertura,
                                                'Driver'         => $esp->driver,
                                                'Socket'         => $esp->socket,
                                                'Nº lámparas'   => $esp->numero_lamparas,
                                                'Vida útil'      => $esp->vida_util_horas ? number_format($esp->vida_util_horas).'h' : null,
                                                'Protocolo'      => $esp->protocolo_regulacion,
                                                'Regulable'      => $esp->regulable ? 'Sí' : null,
                                            ] as $label => $val)
                                                @if($val)
                                                <div class="spec-item">
                                                    <label>{{ $label }}</label>
                                                    <span>{{ $val }}</span>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Fotometría --}}
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Fotometría</p>
                                        <div class="space-y-1.5">
                                            @foreach([
                                                'Tonalidad'          => $esp->tonalidad_luz,
                                                'Temp. color'        => $esp->temperatura_color,
                                                'CRI'                => $esp->cri,
                                                'Lúm. nominales'    => $esp->nominal_lumenes ? number_format($esp->nominal_lumenes).' lm' : null,
                                                'Lúm. reales'       => $esp->real_lumenes    ? number_format($esp->real_lumenes).' lm'    : null,
                                                'Eficacia luminosa'  => $esp->eficacia_luminosa ? $esp->eficacia_luminosa.' lm/W' : null,
                                            ] as $label => $val)
                                                @if($val)
                                                <div class="spec-item">
                                                    <label>{{ $label }}</label>
                                                    <span>{{ $val }}</span>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Dimensiones --}}
                                    @if($producto->dimensiones)
                                    @php $dim = $producto->dimensiones; @endphp
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Dimensiones (mm)</p>
                                        <div class="space-y-1.5">
                                            @foreach([
                                                'Alto'              => $dim->alto,
                                                'Ancho'             => $dim->ancho,
                                                'Profundidad'       => $dim->profundidad,
                                                'Diámetro'         => $dim->diametro,
                                                'Lado'              => $dim->lado,
                                                'Alto suspendido'   => $dim->alto_suspendido,
                                                'Diám. agujero'    => $dim->diametro_agujero,
                                            ] as $label => $val)
                                                @if($val)
                                                <div class="spec-item">
                                                    <label>{{ $label }}</label>
                                                    <span>{{ $val }} mm</span>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Embalaje --}}
                                    @if($producto->embalaje)
                                    @php $emb = $producto->embalaje; @endphp
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Embalaje</p>
                                        <div class="space-y-1.5">
                                            @foreach([
                                                'Peso'           => $emb->peso ? $emb->peso.' kg' : null,
                                                'Volumen'        => $emb->volumen ? $emb->volumen.' cm³' : null,
                                                'Medida caja'    => $emb->medida_embalaje,
                                                'Uds/caja'       => $emb->cantidad_por_caja,
                                                'Embalado'       => $emb->embalado ? 'Sí' : null,
                                            ] as $label => $val)
                                                @if($val)
                                                <div class="spec-item">
                                                    <label>{{ $label }}</label>
                                                    <span>{{ $val }}</span>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Clasificaciones y Tipos de proyecto --}}
                                    <div>
                                        @if($producto->clasificaciones->isNotEmpty())
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Clasificaciones</p>
                                        <div class="flex flex-wrap gap-1 mb-3">
                                            @foreach($producto->clasificaciones as $clf)
                                                <span class="pill bg-purple-50 text-purple-700">{{ $clf->nombre }}</span>
                                            @endforeach
                                        </div>
                                        @endif

                                        @if($producto->tiposProyecto->isNotEmpty())
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Tipos de proyecto</p>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($producto->tiposProyecto as $tp)
                                                <span class="pill bg-orange-50 text-orange-700">{{ $tp->nombre }}</span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>

                                </div>

                                {{-- Variantes --}}
                                @if($producto->variantes->isNotEmpty())
                                <div class="mt-4 border-t border-blue-100 pt-3">
                                    <p class="text-xs font-bold text-gray-400 uppercase mb-2">Variantes ({{ $producto->variantes->count() }})</p>
                                    <div class="overflow-x-auto">
                                        <table class="text-xs w-full">
                                            <thead>
                                                <tr class="text-gray-400">
                                                    <th class="text-left pb-1 pr-4">SKU</th>
                                                    <th class="text-left pb-1 pr-4">Color</th>
                                                    <th class="text-left pb-1 pr-4">Tamaño</th>
                                                    <th class="text-left pb-1 pr-4">Especificación</th>
                                                    <th class="text-right pb-1">Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($producto->variantes as $v)
                                                <tr>
                                                    <td class="py-1 pr-4 font-mono text-gray-500">{{ $v->sku }}</td>
                                                    <td class="py-1 pr-4">{{ $v->color?->nombre ?? '—' }}</td>
                                                    <td class="py-1 pr-4">{{ $v->tamano ?? '—' }}</td>
                                                    <td class="py-1 pr-4">{{ $v->especificacion ?? '—' }}</td>
                                                    <td class="py-1 text-right">{{ $v->stock_actual }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                {{-- Acciones rápidas --}}
                                <div class="mt-3 flex gap-2 border-t border-blue-100 pt-3">
                                    <a href="{{ route('inventario.productos.edit', $producto) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fas fa-pen"></i> Editar en detalle
                                    </a>
                                    <button type="button"
                                            onclick="aprobarUno({{ $producto->id }}, this)"
                                            class="inline-flex items-center gap-1.5 text-xs bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fas fa-check"></i> Aprobar este producto
                                    </button>
                                </div>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-gray-400">
                                <i class="fas fa-check-double text-3xl mb-3 block text-gray-300"></i>
                                No hay productos en borrador
                                @if(request('buscar'))
                                    para "<strong>{{ request('buscar') }}</strong>"
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($productos->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $productos->links() }}
                </div>
                @endif
            </div>

            <p class="text-xs text-gray-400 text-right">
                {{ $productos->total() }} producto(s) en borrador
            </p>
        </div>
    </div>

    <script>
    (() => {
        const chkTodos   = document.getElementById('chk-todos');
        const btnAprobar = document.getElementById('btn-aprobar-lote');
        const btnSelTodo = document.getElementById('btn-seleccionar-todo');
        const labelSel   = document.getElementById('label-seleccionados');
        const cntSel     = document.getElementById('cnt-seleccionados');
        const alerta     = document.getElementById('alerta-aprobacion');
        const msgAlerta  = document.getElementById('msg-aprobacion');
        const csrf       = document.querySelector('meta[name="csrf-token"]').content;

        // ── Expandir / colapsar fila detalle ────────────────────────────────
        document.querySelectorAll('.fila-producto').forEach(fila => {
            fila.addEventListener('click', () => {
                const detalleId = fila.dataset.detalle;
                const detalleRow = document.getElementById(detalleId);
                const icon = fila.querySelector('.toggle-icon');
                const abierto = detalleRow.classList.toggle('abierto');
                icon.style.transform = abierto ? 'rotate(90deg)' : '';
            });
        });

        // ── Selección ────────────────────────────────────────────────────────
        function getSeleccionados() {
            return [...document.querySelectorAll('.chk-producto:checked')].map(c => c.value);
        }

        function actualizarBotones() {
            const ids = getSeleccionados();
            const n   = ids.length;
            btnAprobar.disabled = n === 0;
            labelSel.classList.toggle('hidden', n === 0);
            cntSel.textContent = n;
        }

        chkTodos.addEventListener('change', () => {
            document.querySelectorAll('.chk-producto').forEach(c => { c.checked = chkTodos.checked; });
            actualizarBotones();
        });

        document.addEventListener('change', e => {
            if (e.target.classList.contains('chk-producto')) {
                actualizarBotones();
                if (!e.target.checked) chkTodos.checked = false;
            }
        });

        btnSelTodo.addEventListener('click', () => {
            const todos = [...document.querySelectorAll('.chk-producto')];
            const hayDesel = todos.some(c => !c.checked);
            todos.forEach(c => c.checked = hayDesel);
            chkTodos.checked = hayDesel;
            actualizarBotones();
        });

        // ── Aprobar en lote ──────────────────────────────────────────────────
        btnAprobar.addEventListener('click', async () => {
            const ids = getSeleccionados();
            if (!ids.length) return;
            if (!confirm(`¿Aprobar ${ids.length} producto(s)? Esta acción cambiará su estado a "aprobado".`)) return;

            btnAprobar.disabled = true;
            btnAprobar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Aprobando...';

            const resp = await fetch('{{ route("inventario.importacion.aprobar-lote") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ ids }),
            });

            const data = await resp.json();

            if (data.ok) {
                ids.forEach(id => {
                    document.querySelector(`tr[data-id="${id}"]`)?.remove();
                    document.getElementById(`detalle-${id}`)?.remove();
                });
                alerta.classList.remove('hidden');
                msgAlerta.textContent = `${data.aprobados} producto(s) aprobados correctamente.`;
                setTimeout(() => alerta.classList.add('hidden'), 4000);
                chkTodos.checked = false;
                actualizarBotones();
            } else {
                alert('Ocurrió un error al aprobar. Inténtalo de nuevo.');
            }

            btnAprobar.disabled = false;
            btnAprobar.innerHTML = '<i class="fas fa-check mr-1"></i> Aprobar seleccionados';
        });
    })();

    // ── Aprobar producto individual ──────────────────────────────────────────
    async function aprobarUno(id, btn) {
        if (!confirm('¿Aprobar este producto?')) return;
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        const resp = await fetch('{{ route("inventario.importacion.aprobar-lote") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ ids: [id] }),
        });

        const data = await resp.json();
        if (data.ok) {
            document.querySelector(`tr[data-id="${id}"]`)?.remove();
            document.getElementById(`detalle-${id}`)?.remove();
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Aprobar este producto';
            alert('Error al aprobar.');
        }
    }
    </script>
</body>
</html>
