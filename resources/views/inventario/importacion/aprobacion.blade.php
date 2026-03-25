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

                {{-- Búsqueda --}}
                <form method="GET" class="flex items-center gap-2 flex-1 min-w-0 max-w-sm">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               placeholder="Código, nombre..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <button type="submit"
                            class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors">
                        Buscar
                    </button>
                </form>

                {{-- Acciones --}}
                <div class="flex items-center gap-3">
                    <span id="label-seleccionados" class="text-sm text-gray-500 hidden">
                        <span id="cnt-seleccionados" class="font-bold text-blue-600">0</span> seleccionados
                    </span>
                    <button id="btn-seleccionar-todo"
                            class="text-sm text-gray-500 hover:text-gray-700 underline">
                        Seleccionar todos
                    </button>
                    <button id="btn-aprobar-lote"
                            disabled
                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fas fa-check"></i>
                        Aprobar seleccionados
                    </button>
                </div>
            </div>

            {{-- ── Feedback de aprobación ───────────────────────────────────── --}}
            <div id="alerta-aprobacion" class="hidden bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
                <i class="fas fa-check-circle text-green-600 text-lg"></i>
                <p class="text-sm text-green-700 font-medium" id="msg-aprobacion"></p>
            </div>

            {{-- ── Tabla de productos ───────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="p-3 w-10">
                                <input type="checkbox" id="chk-todos"
                                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            </th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Código Fábrica</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Nombre Kyrios</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Marca</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                            <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Creado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($productos as $producto)
                        <tr class="hover:bg-gray-50 transition-colors" data-id="{{ $producto->id }}">
                            <td class="p-3">
                                <input type="checkbox"
                                       class="chk-producto w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       value="{{ $producto->id }}" />
                            </td>
                            <td class="p-3 font-mono text-xs text-gray-600">
                                {{ $producto->codigo_fabrica ?? '—' }}
                            </td>
                            <td class="p-3 font-medium text-gray-800">
                                {{ $producto->nombre }}
                            </td>
                            <td class="p-3 text-gray-500">
                                {{ $producto->nombre_kyrios ?? '—' }}
                            </td>
                            <td class="p-3 text-gray-600">
                                {{ $producto->marca?->nombre ?? '—' }}
                            </td>
                            <td class="p-3">
                                @if($producto->tipoProducto)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700 font-medium">
                                        {{ $producto->tipoProducto->nombre }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="p-3 text-gray-400 text-xs">
                                {{ $producto->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-gray-400">
                                <i class="fas fa-check-double text-3xl mb-3 block text-gray-300"></i>
                                No hay productos en borrador
                                @if(request('buscar'))
                                    para la búsqueda "<strong>{{ request('buscar') }}</strong>"
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Paginación --}}
                @if($productos->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $productos->links() }}
                </div>
                @endif
            </div>

            {{-- Total --}}
            <p class="text-xs text-gray-400 text-right">
                {{ $productos->total() }} producto(s) en borrador
            </p>
        </div>
    </div>

    <script>
    (() => {
        const chkTodos     = document.getElementById('chk-todos');
        const btnAprobar   = document.getElementById('btn-aprobar-lote');
        const btnSelTodo   = document.getElementById('btn-seleccionar-todo');
        const labelSel     = document.getElementById('label-seleccionados');
        const cntSel       = document.getElementById('cnt-seleccionados');
        const alerta       = document.getElementById('alerta-aprobacion');
        const msgAlerta    = document.getElementById('msg-aprobacion');
        const csrf         = document.querySelector('meta[name="csrf-token"]').content;

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

        // Checkbox "todos"
        chkTodos.addEventListener('change', () => {
            document.querySelectorAll('.chk-producto').forEach(c => {
                c.checked = chkTodos.checked;
            });
            actualizarBotones();
        });

        // Cada checkbox individual
        document.addEventListener('change', e => {
            if (e.target.classList.contains('chk-producto')) {
                actualizarBotones();
                // Si uno se deselecciona, quitar el "todos"
                if (!e.target.checked) chkTodos.checked = false;
            }
        });

        // Botón "Seleccionar todos"
        btnSelTodo.addEventListener('click', () => {
            const todos = [...document.querySelectorAll('.chk-producto')];
            const hayDeseleccionados = todos.some(c => !c.checked);
            todos.forEach(c => c.checked = hayDeseleccionados);
            chkTodos.checked = hayDeseleccionados;
            actualizarBotones();
        });

        // Aprobar en lote
        btnAprobar.addEventListener('click', async () => {
            const ids = getSeleccionados();
            if (!ids.length) return;

            if (!confirm(`¿Aprobar ${ids.length} producto(s)? Esta acción cambiará su estado a "aprobado".`)) return;

            btnAprobar.disabled = true;
            btnAprobar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Aprobando...';

            const resp = await fetch('{{ route("inventario.importacion.aprobar-lote") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ ids }),
            });

            const data = await resp.json();

            if (data.ok) {
                // Quitar las filas aprobadas de la tabla
                ids.forEach(id => {
                    document.querySelector(`tr[data-id="${id}"]`)?.remove();
                });

                // Mostrar alerta
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
    </script>
</body>
</html>
