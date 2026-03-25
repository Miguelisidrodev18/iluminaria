<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Importar Productos</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Importar Productos" subtitle="Carga masiva desde Excel multi-hoja (cola asíncrona)" />

        <div class="max-w-3xl mx-auto space-y-6">

            {{-- ── Formulario de carga ─────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100" style="background-color:#2B2E2C;">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-excel text-xl" style="color:#F7D600;"></i>
                        <h2 class="font-semibold text-white">Cargar archivo</h2>
                    </div>
                </div>

                <form id="form-upload" enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf

                    {{-- Drop zone --}}
                    <div id="drop-zone"
                         class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer hover:border-indigo-400 transition-colors">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-3 block"></i>
                        <p class="text-sm text-gray-600 mb-2">
                            Arrastra tu archivo o
                            <span class="text-indigo-600 font-medium cursor-pointer hover:underline">haz clic aquí</span>
                        </p>
                        <p class="text-xs text-gray-400">Excel (.xlsx, .xls) — máx. 20 MB</p>
                        <p id="nombre-archivo" class="hidden mt-2 text-sm text-indigo-700 font-medium"></p>
                        <input type="file" id="archivo" name="archivo"
                               accept=".xlsx,.xls" class="hidden" required />
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('inventario.importacion.plantilla') }}"
                           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-50">
                            <i class="fas fa-download text-green-600"></i>
                            Descargar plantilla Excel
                        </a>
                        <button type="submit" id="btn-importar"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg font-semibold text-sm shadow-sm hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                                style="background-color:#F7D600; color:#2B2E2C;">
                            <i class="fas fa-upload"></i>
                            Importar productos
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Barra de progreso (oculta hasta que inicia el job) ──────── --}}
            <div id="card-progreso" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100" style="background-color:#2B2E2C;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i id="icon-progreso" class="fas fa-spinner fa-spin text-xl" style="color:#F7D600;"></i>
                            <h2 id="titulo-progreso" class="font-semibold text-white">Procesando...</h2>
                        </div>
                        <span id="pct-label" class="text-sm font-bold" style="color:#F7D600;">0%</span>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Barra --}}
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div id="barra-progreso"
                             class="h-3 rounded-full transition-all duration-500 bg-indigo-500"
                             style="width: 0%"></div>
                    </div>

                    {{-- Contadores --}}
                    <div class="grid grid-cols-4 gap-3 text-center text-sm">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-gray-400 text-xs mb-1">Total filas</p>
                            <p id="stat-total" class="font-bold text-gray-800 text-lg">—</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-gray-400 text-xs mb-1">Procesadas</p>
                            <p id="stat-procesadas" class="font-bold text-gray-800 text-lg">0</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3">
                            <p class="text-green-600 text-xs mb-1">Exitosas</p>
                            <p id="stat-exitosas" class="font-bold text-green-700 text-lg">0</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-3">
                            <p class="text-red-400 text-xs mb-1">Con error</p>
                            <p id="stat-fallidas" class="font-bold text-red-600 text-lg">0</p>
                        </div>
                    </div>

                    {{-- Log de errores --}}
                    <div id="bloque-errores" class="hidden">
                        <p class="text-sm font-semibold text-red-600 mb-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Errores registrados:
                        </p>
                        <div id="lista-errores"
                             class="max-h-40 overflow-y-auto bg-red-50 border border-red-100 rounded-lg p-3 text-xs text-red-700 space-y-1">
                        </div>
                    </div>

                    {{-- Enlace a aprobación al terminar --}}
                    <div id="bloque-finalizado" class="hidden items-center justify-between bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-green-700 font-medium">
                            <i class="fas fa-check-circle mr-2"></i>
                            Importación completada. Los productos están en <strong>borrador</strong>.
                        </p>
                        <div class="flex gap-2">
                            <a href="{{ route('inventario.productos.index') }}"
                               class="text-sm border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-50">
                                Ver productos
                            </a>
                            <a href="{{ route('inventario.importacion.aprobacion') }}"
                               class="text-sm font-semibold text-white px-4 py-1.5 rounded-lg hover:opacity-90"
                               style="background-color:#2B2E2C;">
                                Ir a Aprobación
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Instrucciones ───────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        Estructura del Excel (6 hojas)
                    </h3>
                </div>
                <div class="p-6 text-sm text-gray-600 space-y-4">

                    <p>El archivo debe contener las siguientes hojas con esos nombres exactos:</p>

                    {{-- PRODUCTOS --}}
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">
                            Hoja: <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">PRODUCTOS</code>
                            <span class="text-red-500 ml-1">*requerida</span>
                        </p>
                        <code class="block bg-gray-100 p-2 rounded text-xs text-gray-700 overflow-x-auto">
                            codigo_fabrica · nombre · nombre_kyrios · categoria_codigo<br>
                            tipo_producto_codigo · tipo_luminaria_codigo · marca_codigo<br>
                            linea · procedencia · ficha_tecnica_url · estado · unidad_medida_codigo
                        </code>
                    </div>

                    {{-- ATRIBUTOS --}}
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">
                            Hoja: <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">ATRIBUTOS</code>
                            <span class="text-gray-400 ml-1">(técnicos → se guardan como atributo_slug)</span>
                        </p>
                        <code class="block bg-gray-100 p-2 rounded text-xs text-gray-700 overflow-x-auto">
                            codigo_fabrica · tipo_fuente · potencia · temperatura · cri<br>
                            ip · ik · angulo · voltaje · driver · regulable · protocolo
                        </code>
                    </div>

                    {{-- DIMENSIONES --}}
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">
                            Hoja: <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">DIMENSIONES</code>
                        </p>
                        <code class="block bg-gray-100 p-2 rounded text-xs text-gray-700 overflow-x-auto">
                            codigo_fabrica · alto_mm · ancho_mm · diametro_mm · lado_mm<br>
                            profundidad_mm · alto_suspendido_mm · diametro_agujero_mm
                        </code>
                    </div>

                    {{-- EMBALAJE --}}
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">
                            Hoja: <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">EMBALAJE</code>
                        </p>
                        <code class="block bg-gray-100 p-2 rounded text-xs text-gray-700 overflow-x-auto">
                            codigo_fabrica · peso_kg · volumen_cm3 · medida_embalaje · cantidad_por_caja · embalado
                        </code>
                    </div>

                    {{-- VARIANTES --}}
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">
                            Hoja: <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">VARIANTES</code>
                            <span class="text-gray-400 ml-1">(una fila por combinación única)</span>
                        </p>
                        <code class="block bg-gray-100 p-2 rounded text-xs text-gray-700 overflow-x-auto">
                            codigo_fabrica · tamano · especificacion · color · stock · precio
                        </code>
                        <ul class="mt-1 list-disc list-inside text-xs text-gray-400 space-y-0.5">
                            <li><code>tamano</code>: "600x600mm", "Circular 4\""</li>
                            <li><code>especificacion</code>: "3000K", "18W", "Versión A"</li>
                            <li><code>color</code>: texto — "negro", "blanco", "gris" (se normaliza)</li>
                        </ul>
                    </div>

                    {{-- COMPONENTES --}}
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">
                            Hoja: <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">COMPONENTES</code>
                            <span class="text-gray-400 ml-1">(solo para productos compuestos)</span>
                        </p>
                        <code class="block bg-gray-100 p-2 rounded text-xs text-gray-700 overflow-x-auto">
                            codigo_fabrica_padre · codigo_fabrica_hijo · cantidad
                        </code>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
                        <i class="fas fa-lightbulb mr-1"></i>
                        <strong>Tip:</strong> El proceso se ejecuta en cola (asíncrono). Los productos quedan
                        en estado <strong>borrador</strong> — deberás aprobarlos manualmente antes de que
                        estén disponibles para venta.
                        Marcas, colores y categorías inexistentes se crean automáticamente.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
    (() => {
        const dropZone    = document.getElementById('drop-zone');
        const inputFile   = document.getElementById('archivo');
        const nombreLabel = document.getElementById('nombre-archivo');
        const form        = document.getElementById('form-upload');
        const btnImportar = document.getElementById('btn-importar');
        const cardProgreso = document.getElementById('card-progreso');
        const barra       = document.getElementById('barra-progreso');
        const pctLabel    = document.getElementById('pct-label');

        // ── Drag & Drop ──────────────────────────────────────────────────
        dropZone.addEventListener('click', () => inputFile.click());
        dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('border-indigo-400'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-indigo-400'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('border-indigo-400');
            if (e.dataTransfer.files.length) {
                inputFile.files = e.dataTransfer.files;
                mostrarNombre(e.dataTransfer.files[0].name);
            }
        });
        inputFile.addEventListener('change', () => {
            if (inputFile.files.length) mostrarNombre(inputFile.files[0].name);
        });

        function mostrarNombre(n) {
            nombreLabel.textContent = 'Archivo: ' + n;
            nombreLabel.classList.remove('hidden');
        }

        // ── Envío asíncrono ──────────────────────────────────────────────
        form.addEventListener('submit', async e => {
            e.preventDefault();
            if (!inputFile.files.length) return;

            btnImportar.disabled = true;
            btnImportar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
            cardProgreso.classList.remove('hidden');
            document.getElementById('bloque-finalizado').classList.add('hidden');

            const fd = new FormData(form);

            const resp = await fetch('{{ route("inventario.importacion.store") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: fd,
            });

            if (!resp.ok) {
                alert('Error al subir el archivo. Inténtalo de nuevo.');
                btnImportar.disabled = false;
                btnImportar.innerHTML = '<i class="fas fa-upload mr-2"></i>Importar productos';
                return;
            }

            const data = await resp.json();
            const { importacion_id, estado } = data;

            // Con QUEUE_CONNECTION=sync ya terminó: consultar estado final directo
            if (['completado', 'fallido'].includes(estado)) {
                const r = await fetch(`/inventario/importacion/${importacion_id}/progreso`);
                const progreso = await r.json();
                actualizarUI(progreso);
                finalizarUI(progreso);
            } else {
                iniciarPolling(importacion_id);
            }
        });

        // ── Polling cada 2s ──────────────────────────────────────────────
        function iniciarPolling(id) {
            const timer = setInterval(async () => {
                const r    = await fetch(`/inventario/importacion/${id}/progreso`);
                const data = await r.json();

                actualizarUI(data);

                if (['completado', 'fallido'].includes(data.estado)) {
                    clearInterval(timer);
                    finalizarUI(data);
                }
            }, 2000);
        }

        function actualizarUI(data) {
            const pct = data.porcentaje ?? 0;
            barra.style.width    = pct + '%';
            pctLabel.textContent = pct + '%';

            document.getElementById('stat-total').textContent      = data.total || '—';
            document.getElementById('stat-procesadas').textContent = data.procesadas;
            document.getElementById('stat-exitosas').textContent   = data.exitosas;
            document.getElementById('stat-fallidas').textContent   = data.fallidas;

            if (data.errores?.length) {
                document.getElementById('bloque-errores').classList.remove('hidden');
                const lista = document.getElementById('lista-errores');
                lista.innerHTML = data.errores.map(e => `<p class="border-b border-red-100 pb-0.5">• ${e}</p>`).join('');
                lista.scrollTop = lista.scrollHeight;
            }
        }

        function finalizarUI(data) {
            const icon   = document.getElementById('icon-progreso');
            const titulo = document.getElementById('titulo-progreso');

            icon.classList.remove('fa-spin', 'fa-spinner');

            if (data.estado === 'completado') {
                icon.classList.add('fa-check-circle');
                titulo.textContent = 'Importación completada';
                barra.classList.replace('bg-indigo-500', 'bg-green-500');
                document.getElementById('bloque-finalizado').style.display = 'flex';
            } else {
                icon.classList.add('fa-times-circle');
                icon.style.color = '#ef4444';
                titulo.textContent = 'Importación con errores';
                barra.classList.replace('bg-indigo-500', 'bg-red-500');
            }

            btnImportar.disabled = false;
            btnImportar.innerHTML = '<i class="fas fa-upload mr-2"></i>Importar productos';
        }
    })();
    </script>
</body>
</html>
