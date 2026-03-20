<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\CatalogoAtributo;
use App\Models\Catalogo\CatalogoValor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AtributoController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $atributos = CatalogoAtributo::withCount('productoAtributos')
            ->with('valoresActivos')
            ->ordenados()
            ->get()
            ->groupBy('grupo');

        return view('admin.atributos.index', compact('atributos'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.atributos.create');
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'         => 'required|string|max:100',
            'tipo'           => 'required|in:select,multiselect,number,text,checkbox',
            'grupo'          => 'required|in:tecnico,comercial,instalacion,estetico',
            'unidad'         => 'nullable|string|max:30',
            'placeholder'    => 'nullable|string|max:150',
            'requerido'      => 'boolean',
            'en_nombre_auto' => 'boolean',
            'orden_nombre'   => 'integer|min:0',
            'orden'          => 'integer|min:0',
            'descripcion'    => 'nullable|string|max:500',

            // Valores predefinidos (para select/multiselect)
            'valores'              => 'nullable|array',
            'valores.*.valor'      => 'required_with:valores|string|max:150',
            'valores.*.etiqueta'   => 'nullable|string|max:150',
            'valores.*.color_hex'  => 'nullable|string|max:7',
            'valores.*.orden'      => 'integer|min:0',
        ]);

        $slug = $this->generarSlugUnico($validated['nombre']);

        $atributo = CatalogoAtributo::create([
            'nombre'         => $validated['nombre'],
            'slug'           => $slug,
            'tipo'           => $validated['tipo'],
            'grupo'          => $validated['grupo'],
            'unidad'         => $validated['unidad'] ?? null,
            'placeholder'    => $validated['placeholder'] ?? null,
            'requerido'      => $request->boolean('requerido'),
            'en_nombre_auto' => $request->boolean('en_nombre_auto'),
            'orden_nombre'   => $validated['orden_nombre'] ?? 0,
            'orden'          => $validated['orden'] ?? 0,
            'descripcion'    => $validated['descripcion'] ?? null,
            'activo'         => true,
        ]);

        // Guardar valores si es select/multiselect
        if (in_array($validated['tipo'], ['select', 'multiselect']) && !empty($validated['valores'])) {
            foreach ($validated['valores'] as $i => $v) {
                if (empty(trim($v['valor'] ?? ''))) continue;
                CatalogoValor::create([
                    'atributo_id' => $atributo->id,
                    'valor'       => trim($v['valor']),
                    'etiqueta'    => !empty($v['etiqueta']) ? trim($v['etiqueta']) : null,
                    'color_hex'   => $v['color_hex'] ?? null,
                    'orden'       => $v['orden'] ?? $i,
                    'activo'      => true,
                ]);
            }
        }

        return redirect()
            ->route('admin.atributos.index')
            ->with('success', "Atributo «{$atributo->nombre}» creado correctamente.");
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(CatalogoAtributo $atributo)
    {
        $atributo->load('valores');
        return view('admin.atributos.edit', compact('atributo'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, CatalogoAtributo $atributo)
    {
        $validated = $request->validate([
            'nombre'         => 'required|string|max:100',
            'tipo'           => 'required|in:select,multiselect,number,text,checkbox',
            'grupo'          => 'required|in:tecnico,comercial,instalacion,estetico',
            'unidad'         => 'nullable|string|max:30',
            'placeholder'    => 'nullable|string|max:150',
            'requerido'      => 'boolean',
            'en_nombre_auto' => 'boolean',
            'orden_nombre'   => 'integer|min:0',
            'orden'          => 'integer|min:0',
            'activo'         => 'boolean',
            'descripcion'    => 'nullable|string|max:500',

            'valores'              => 'nullable|array',
            'valores.*.id'         => 'nullable|integer',
            'valores.*.valor'      => 'required_with:valores|string|max:150',
            'valores.*.etiqueta'   => 'nullable|string|max:150',
            'valores.*.color_hex'  => 'nullable|string|max:7',
            'valores.*.orden'      => 'integer|min:0',
            'valores.*.activo'     => 'boolean',
        ]);

        $atributo->update([
            'nombre'         => $validated['nombre'],
            'tipo'           => $validated['tipo'],
            'grupo'          => $validated['grupo'],
            'unidad'         => $validated['unidad'] ?? null,
            'placeholder'    => $validated['placeholder'] ?? null,
            'requerido'      => $request->boolean('requerido'),
            'en_nombre_auto' => $request->boolean('en_nombre_auto'),
            'orden_nombre'   => $validated['orden_nombre'] ?? 0,
            'orden'          => $validated['orden'] ?? 0,
            'activo'         => $request->boolean('activo', true),
            'descripcion'    => $validated['descripcion'] ?? null,
        ]);

        // Sincronizar valores
        if (!empty($validated['valores'])) {
            $idsEnviados = [];
            foreach ($validated['valores'] as $i => $v) {
                if (empty(trim($v['valor'] ?? ''))) continue;

                if (!empty($v['id'])) {
                    // Actualizar existente
                    $valor = CatalogoValor::find($v['id']);
                    if ($valor && $valor->atributo_id === $atributo->id) {
                        $valor->update([
                            'valor'     => trim($v['valor']),
                            'etiqueta'  => !empty($v['etiqueta']) ? trim($v['etiqueta']) : null,
                            'color_hex' => $v['color_hex'] ?? null,
                            'orden'     => $v['orden'] ?? $i,
                            'activo'    => isset($v['activo']) ? (bool)$v['activo'] : true,
                        ]);
                        $idsEnviados[] = $valor->id;
                    }
                } else {
                    // Crear nuevo
                    $nuevo = CatalogoValor::create([
                        'atributo_id' => $atributo->id,
                        'valor'       => trim($v['valor']),
                        'etiqueta'    => !empty($v['etiqueta']) ? trim($v['etiqueta']) : null,
                        'color_hex'   => $v['color_hex'] ?? null,
                        'orden'       => $v['orden'] ?? $i,
                        'activo'      => true,
                    ]);
                    $idsEnviados[] = $nuevo->id;
                }
            }

            // Soft-desactivar valores que ya no vienen en el form (en vez de delete)
            $atributo->valores()
                ->whereNotIn('id', $idsEnviados)
                ->update(['activo' => false]);
        }

        return redirect()
            ->route('admin.atributos.index')
            ->with('success', "Atributo «{$atributo->nombre}» actualizado.");
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(CatalogoAtributo $atributo)
    {
        // Solo desactivar; no borrar para preservar historial de productos
        $atributo->update(['activo' => false]);

        return back()->with('success', "Atributo «{$atributo->nombre}» desactivado.");
    }

    // ── API: Agregar valor inline (AJAX) ──────────────────────────────────────

    public function storeValor(Request $request, CatalogoAtributo $atributo)
    {
        $validated = $request->validate([
            'valor'     => 'required|string|max:150',
            'etiqueta'  => 'nullable|string|max:150',
            'color_hex' => 'nullable|string|max:7',
        ]);

        $valor = CatalogoValor::firstOrCreate(
            ['atributo_id' => $atributo->id, 'valor' => trim($validated['valor'])],
            [
                'etiqueta'  => $validated['etiqueta'] ?? null,
                'color_hex' => $validated['color_hex'] ?? null,
                'orden'     => $atributo->valores()->max('orden') + 1,
                'activo'    => true,
            ]
        );

        return response()->json([
            'success' => true,
            'valor'   => [
                'id'           => $valor->id,
                'valor'        => $valor->valor,
                'texto_display'=> $valor->texto_display,
                'color_hex'    => $valor->color_hex,
            ],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function generarSlugUnico(string $nombre): string
    {
        $base = Str::slug($nombre, '_');
        $slug = $base;
        $i = 1;
        while (CatalogoAtributo::where('slug', $slug)->exists()) {
            $slug = $base . '_' . $i++;
        }
        return $slug;
    }
}
