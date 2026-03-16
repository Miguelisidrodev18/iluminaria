<?php

namespace App\Http\Controllers\Luminaria;

use App\Http\Controllers\Controller;
use App\Models\Luminaria\EspacioProyecto;
use App\Models\Luminaria\TipoProyecto;
use Illuminate\Http\Request;

class EspacioProyectoController extends Controller
{
    public function index()
    {
        $espacios = EspacioProyecto::with('tipoProyecto')->orderBy('tipo_proyecto_id')->orderBy('nombre')->get();
        return view('luminarias.espacios-proyecto.index', compact('espacios'));
    }

    public function create()
    {
        $tipos = TipoProyecto::activos()->orderBy('nombre')->get();
        return view('luminarias.espacios-proyecto.create', compact('tipos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_proyecto_id' => 'required|exists:tipos_proyecto,id',
            'nombre'           => 'required|string|max:100',
            'activo'           => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        EspacioProyecto::create($data);

        return redirect()->route('luminarias.espacios-proyecto.index')
                         ->with('success', 'Espacio creado correctamente.');
    }

    public function edit(EspacioProyecto $espaciosProyecto)
    {
        $tipos = TipoProyecto::activos()->orderBy('nombre')->get();
        return view('luminarias.espacios-proyecto.edit', ['espacio' => $espaciosProyecto, 'tipos' => $tipos]);
    }

    public function update(Request $request, EspacioProyecto $espaciosProyecto)
    {
        $data = $request->validate([
            'tipo_proyecto_id' => 'required|exists:tipos_proyecto,id',
            'nombre'           => 'required|string|max:100',
            'activo'           => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        $espaciosProyecto->update($data);

        return redirect()->route('luminarias.espacios-proyecto.index')
                         ->with('success', 'Espacio actualizado.');
    }

    public function destroy(EspacioProyecto $espaciosProyecto)
    {
        $espaciosProyecto->delete();
        return redirect()->route('luminarias.espacios-proyecto.index')
                         ->with('success', 'Espacio eliminado.');
    }

    public function show(EspacioProyecto $espaciosProyecto)
    {
        return redirect()->route('luminarias.espacios-proyecto.edit', $espaciosProyecto);
    }
}
