<?php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    public function index()
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        return view('catalogo.ubicaciones.index', compact('ubicaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100|unique:ubicaciones,nombre',
            'tipo'        => 'required|in:almacen,tienda,showroom,taller',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Ubicacion::create([
            'nombre'      => $request->nombre,
            'tipo'        => $request->tipo,
            'descripcion' => $request->descripcion,
            'estado'      => 'activo',
        ]);

        return back()->with('success', 'Ubicación "' . $request->nombre . '" creada correctamente.');
    }

    public function update(Request $request, Ubicacion $ubicacion)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100|unique:ubicaciones,nombre,' . $ubicacion->id,
            'tipo'        => 'required|in:almacen,tienda,showroom,taller',
            'descripcion' => 'nullable|string|max:255',
            'estado'      => 'required|in:activo,inactivo',
        ]);

        $ubicacion->update($request->only('nombre', 'tipo', 'descripcion', 'estado'));

        return back()->with('success', 'Ubicación actualizada correctamente.');
    }

    public function destroy(Ubicacion $ubicacion)
    {
        $ubicacion->update(['estado' => 'inactivo']);
        return back()->with('success', 'Ubicación desactivada.');
    }
}
