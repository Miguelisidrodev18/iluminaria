<?php

namespace App\Http\Controllers\Luminaria;

use App\Http\Controllers\Controller;
use App\Models\Luminaria\TipoProyecto;
use Illuminate\Http\Request;

class TipoProyectoController extends Controller
{
    public function index()
    {
        $tipos = TipoProyecto::withCount('espacios')->orderBy('nombre')->get();
        return view('luminarias.tipos-proyecto.index', compact('tipos'));
    }

    public function create()
    {
        return view('luminarias.tipos-proyecto.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:tipos_proyecto,nombre',
            'icono'  => 'nullable|string|max:50',
            'activo' => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        TipoProyecto::create($data);

        return redirect()->route('luminarias.tipos-proyecto.index')
                         ->with('success', 'Tipo de proyecto creado correctamente.');
    }

    public function edit(TipoProyecto $tiposProyecto)
    {
        return view('luminarias.tipos-proyecto.edit', ['tipo' => $tiposProyecto]);
    }

    public function update(Request $request, TipoProyecto $tiposProyecto)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:tipos_proyecto,nombre,' . $tiposProyecto->id,
            'icono'  => 'nullable|string|max:50',
            'activo' => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        $tiposProyecto->update($data);

        return redirect()->route('luminarias.tipos-proyecto.index')
                         ->with('success', 'Tipo de proyecto actualizado.');
    }

    public function destroy(TipoProyecto $tiposProyecto)
    {
        if ($tiposProyecto->clasificaciones()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay productos clasificados con este tipo.');
        }

        $tiposProyecto->delete();
        return redirect()->route('luminarias.tipos-proyecto.index')
                         ->with('success', 'Tipo de proyecto eliminado.');
    }

    public function show(TipoProyecto $tiposProyecto)
    {
        $tiposProyecto->load('espacios');
        return view('luminarias.tipos-proyecto.edit', ['tipo' => $tiposProyecto]);
    }
}
