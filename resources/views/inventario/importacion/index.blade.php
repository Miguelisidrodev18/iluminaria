<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Importación Masiva de Productos</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Importación Masiva"
            subtitle="Carga de productos desde Excel multi-hoja"
        />

        <div class="max-w-4xl mx-auto space-y-6">

            {{-- ── Formulario de carga ─────────────────────────────────────── --}}
            <div id="card-upload" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">
                    <i class="fas fa-file-excel text-green-600 mr-2"></i>Subir archivo Excel
                </h2>

                <p class="text-sm text-gray-500 mb-3">Usa la plantilla oficial. Hojas requeridas:</p>
                <div class="flex flex-wrap gap-1.5 mb-4">
                    <span class="font-mono text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800">completo_kyrios</span>
                    <span class="font-mono text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-800">ATRIBUTOS_PRODUCTO</span>
                    <span class="font-mono text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">DIMENSIONES</span>
                    <span class="font-mono text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-800">EMBALAJE</span>
                    <span class="font-mono text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800">VARIANTES</span>
                    <span class="font-mono text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">CLASIFICACIONES</span>
                    <span class="font-mono text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">COMPONENTES</span>
                </div>

                <form id="form-upload" enctype="multipart/form-data">
                    @csrf

                    {{-- Input fuera del label para evitar doble disparo del selector --}}
                    <input type="file" id="archivo" name="archivo"
                           accept=".xlsx,.xls" class="hidden" required />

                    <div class="flex items-center gap-4">
                        <div id="drop-zone" class="flex-1 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 transition-colors">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-500">
                                Arrastra tu Excel o
                                <span class="text-[#2B2E2C] font-medium">haz clic para seleccionar</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Solo .xlsx / .xls · Máx 20 MB</p>
                            <p id="nombre-archivo" class="text-xs text-[#2B2E2C] mt-2 font-medium hidden"></p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <a href="{{ route('inventario.importacion.plantilla') }}"
                           class="inline-flex items-center gap-2 text-sm text-green-700 border border-green-300 bg-green-50 hover:bg-green-100 px-4 py-2.5 rounded-lg transition-colors font-medium">
                            <i class="fas fa-file-excel"></i>
                            Descargar plantilla Excel
                        </a>
                        <button type="submit" id="btn-importar"
                                class="inline-flex items-center gap-2 bg-[#F7D600] text-[#2B2E2C] hover:bg-[#e8c900] text-sm font-medium px-5 py-2.5 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-upload"></i>
                            Iniciar importación
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Barra de progreso ───────────────────────────────────────── --}}
            <div id="card-progreso" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hidden">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-semibold text-gray-800">
                        <i class="fas fa-spinner fa-spin text-[#2B2E2C] mr-2" id="icon-progreso"></i>
                        <span id="titulo-progreso">Procesando...</span>
                    </h2>
                    <span id="pct-label" class="text-sm font-bold text-[#2B2E2C]">0%</span>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden mb-4">
                    <div id="barra-progreso"
                         class="h-3 rounded-full transition-all duration-500 bg-[#F7D600] text-[#2B2E2C]"
                         style="width: 0%"></div>
                </div>

                <div class="grid grid-cols-4 gap-3 text-center text-sm mb-4">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-gray-500 text-xs">Total filas</p>
                        <p id="stat-total" class="font-bold text-gray-800 text-lg">—</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-gray-500 text-xs">Procesadas</p>
                        <p id="stat-procesadas" class="font-bold text-gray-800 text-lg">0</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-green-600 text-xs">Exitosas</p>
                        <p id="stat-exitosas" class="font-bold text-green-700 text-lg">0</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3">
                        <p class="text-red-500 text-xs">Con error</p>
                        <p id="stat-fallidas" class="font-bold text-red-600 text-lg">0</p>
                    </div>
                </div>

                {{-- Errores por fila --}}
                <div id="bloque-errores" class="hidden">
                    <p class="text-sm font-semibold text-red-600 mb-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Errores registrados:
                    </p>
                    <div id="lista-errores"
                         class="max-h-48 overflow-y-auto bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-700 space-y-1">
                    </div>
                </div>

                {{-- Enlace a aprobación cuando termina --}}
                <div id="bloque-finalizado" class="hidden mt-4 items-center justify-between bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-green-700 font-medium">
                        <i class="fas fa-check-circle mr-2"></i>Importación completada.
                        Los productos están en estado <strong>borrador</strong>.
                    </p>
                    <a href="{{ route('inventario.importacion.aprobacion') }}"
                       class="text-sm bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Ir a Aprobación
                    </a>
                </div>
            </div>

            {{-- ── Historial de importaciones ──────────────────────────────── --}}
            @if($recientes->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">
                    <i class="fas fa-history text-gray-400 mr-2"></i>Importaciones recientes
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs text-gray-500 uppercase">
                                <th class="pb-2 pr-4">Archivo</th>
                                <th class="pb-2 pr-4">Estado</th>
                                <th class="pb-2 pr-4 text-right">Exitosas</th>
                                <th class="pb-2 pr-4 text-right">Errores</th>
                                <th class="pb-2">Fecha</th>
                                <th class="pb-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($recientes as $imp)
                            <tr>
                                <td class="py-2 pr-4 text-gray-700 truncate max-w-xs">{{ $imp->nombre_archivo }}</td>
                                <td class="py-2 pr-4">
                                    @php
                                        $badge = match($imp->estado) {
                                            'completado' => 'bg-green-100 text-green-700',
                                            'procesando' => 'bg-[#2B2E2C]/10 text-[#2B2E2C]',
                                            'pendiente'  => 'bg-yellow-100 text-yellow-700',
                                            'fallido'    => 'bg-red-100 text-red-700',
                                            default      => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ ucfirst($imp->estado) }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4 text-right text-green-700 font-medium">{{ $imp->exitosas }}</td>
                                <td class="py-2 pr-4 text-right text-red-600">{{ $imp->fallidas }}</td>
                                <td class="py-2 text-gray-400 text-xs">{{ $imp->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-2 text-right">
                                    @if(in_array($imp->estado, ['procesando','pendiente']))
                                        <form method="POST" action="{{ route('inventario.importacion.cancelar', $imp) }}"
                                              onsubmit="return confirm('¿Cancelar esta importación?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="text-xs text-red-600 hover:text-red-800 border border-red-200 rounded px-2 py-0.5">
                                                Cancelar
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

    <script>
    (() => {
        const form        = document.getElementById('form-upload');
        const inputFile   = document.getElementById('archivo');
        const dropZone    = document.getElementById('drop-zone');
        const nombreLabel = document.getElementById('nombre-archivo');
        const btnImportar = document.getElementById('btn-importar');
        const cardProgreso = document.getElementById('card-progreso');
        const barra       = document.getElementById('barra-progreso');
        const pctLabel    = document.getElementById('pct-label');
        const tituloProgreso = document.getElementById('titulo-progreso');
        const iconProgreso = document.getElementById('icon-progreso');

        let pollInterval  = null;

        // ── Drag & Drop ──────────────────────────────────────────────────
        // NOTA: el <label> ya conecta el click al input; solo manejamos dragover/drop aquí
        dropZone.addEventListener('click', e => {
            // Evitar doble disparo: el label ya abre el selector de archivo
            e.preventDefault();
            inputFile.click();
        });
        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-[#F7D600]'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-[#F7D600]'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('border-[#F7D600]');
            if (e.dataTransfer.files.length) {
                inputFile.files = e.dataTransfer.files;
                mostrarNombre(e.dataTransfer.files[0].name);
            }
        });
        inputFile.addEventListener('change', () => {
            if (inputFile.files.length) mostrarNombre(inputFile.files[0].name);
        });

        function mostrarNombre(nombre) {
            nombreLabel.textContent = nombre;
            nombreLabel.classList.remove('hidden');
        }

        // ── Envío del formulario ─────────────────────────────────────────
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!inputFile.files.length) return;

            btnImportar.disabled = true;
            cardProgreso.classList.remove('hidden');
            document.getElementById('bloque-finalizado').style.display = 'none';

            const fd = new FormData(form);
            fd.set('_token', document.querySelector('meta[name="csrf-token"]').content);

            const resp = await fetch('{{ route("inventario.importacion.store") }}', {
                method: 'POST',
                body: fd,
            });

            if (!resp.ok) {
                let msg = `Error ${resp.status}`;
                try {
                    const err = await resp.json();
                    // Laravel validation errors (422)
                    if (err.errors) {
                        msg = Object.values(err.errors).flat().join('\n');
                    } else if (err.message) {
                        msg = err.message;
                    }
                } catch(_) {}
                cardProgreso.classList.add('hidden');
                btnImportar.disabled = false;
                alert('Error al subir:\n' + msg);
                return;
            }

            const json = await resp.json();
            const importacion_id = json.importacion_id;

            // Con QUEUE_CONNECTION=sync el job ya terminó — obtener stats directamente
            if (['completado', 'fallido'].includes(json.estado)) {
                const progResp = await fetch(`/inventario/importacion/${importacion_id}/progreso`);
                const data = await progResp.json();
                actualizarUI(data);
                finalizarUI(data);
            } else {
                iniciarPolling(importacion_id);
            }
        });

        // ── Polling de progreso ──────────────────────────────────────────
        function iniciarPolling(id) {
            pollInterval = setInterval(async () => {
                const resp = await fetch(`/inventario/importacion/${id}/progreso`);
                const data = await resp.json();

                actualizarUI(data);

                if (['completado', 'fallido'].includes(data.estado)) {
                    clearInterval(pollInterval);
                    finalizarUI(data);
                }
            }, 2000); // cada 2 segundos
        }

        function actualizarUI(data) {
            const pct = data.porcentaje ?? 0;
            barra.style.width    = pct + '%';
            pctLabel.textContent = pct + '%';

            document.getElementById('stat-total').textContent      = data.total || '—';
            document.getElementById('stat-procesadas').textContent = data.procesadas;
            document.getElementById('stat-exitosas').textContent   = data.exitosas;
            document.getElementById('stat-fallidas').textContent   = data.fallidas;

            // Mostrar errores
            if (data.errores && data.errores.length) {
                const bloqueErr  = document.getElementById('bloque-errores');
                const listaErr   = document.getElementById('lista-errores');
                bloqueErr.classList.remove('hidden');
                listaErr.innerHTML = data.errores
                    .map(e => `<p class="border-b border-red-100 pb-1">• ${e}</p>`)
                    .join('');
                listaErr.scrollTop = listaErr.scrollHeight;
            }
        }

        function finalizarUI(data) {
            iconProgreso.classList.remove('fa-spin', 'fa-spinner');

            if (data.estado === 'completado') {
                // Forzar barra al 100% al completar
                barra.style.width    = '100%';
                pctLabel.textContent = '100%';

                iconProgreso.classList.add('fa-check-circle');
                iconProgreso.classList.replace('text-[#2B2E2C]', 'text-green-500');
                tituloProgreso.textContent = 'Importación completada';
                barra.classList.replace('bg-[#F7D600]', 'bg-green-500');
                document.getElementById('bloque-finalizado').style.display = 'flex';
            } else {
                iconProgreso.classList.add('fa-times-circle');
                iconProgreso.classList.replace('text-[#2B2E2C]', 'text-red-500');
                tituloProgreso.textContent = 'Importación con errores';
                barra.classList.replace('bg-[#F7D600]', 'bg-red-500');
            }

            btnImportar.disabled = false;
        }
    })();
    </script>
</body>
</html>
