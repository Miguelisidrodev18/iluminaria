<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    public function index(Request $request)
    {
        $query = Proyecto::with('cliente');

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }
        if ($request->filled('resultado')) {
            $query->where('resultado', $request->resultado);
        }
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(fn ($w) => $w
                ->where('nombre_proyecto', 'like', "%{$b}%")
                ->orWhere('id_proyecto', 'like', "%{$b}%")
                ->orWhereHas('cliente', fn ($q) => $q
                    ->where('apellidos', 'like', "%{$b}%")
                    ->orWhere('nombres', 'like', "%{$b}%")
                )
            );
        }

        $proyectos = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('proyectos.index', compact('proyectos'));
    }

    public function show(Proyecto $proyecto)
    {
        $proyecto->load('cliente');
        return view('proyectos.show', compact('proyecto'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'          => 'required|exists:clientes,id',
            'id_proyecto'         => 'required|string|max:50|unique:proyectos,id_proyecto',
            'nombre_proyecto'     => 'required|string|max:255',
            'prioridad'           => 'required|in:A,M,B',
            'persona_cargo'       => 'nullable|string|max:100',
            'fecha_recepcion'     => 'nullable|date',
            'fecha_entrega_aprox' => 'nullable|date',
            'max_revisiones'      => 'nullable|integer|min:1|max:10',
            'fecha_entrega_real'  => 'nullable|date',
            'monto_presup_proy'   => 'nullable|numeric|min:0',
            'monto_vendido_proy'  => 'nullable|numeric|min:0',
            'centro_costos'       => 'nullable|string|max:100',
            'resultado'           => 'nullable|in:G,P,EP,ENT,ENV,I',
            'seguimiento'         => 'nullable|string',
        ]);

        $validated['nombre_proyecto'] = strtoupper($validated['nombre_proyecto']);
        $proyecto = Proyecto::create($validated);

        return redirect()->route('proyectos.show', $proyecto)->with('success', 'Proyecto creado exitosamente');
    }

    public function update(Request $request, Proyecto $proyecto)
    {
        $validated = $request->validate([
            'id_proyecto'         => 'required|string|max:50|unique:proyectos,id_proyecto,' . $proyecto->id,
            'nombre_proyecto'     => 'required|string|max:255',
            'prioridad'           => 'required|in:A,M,B',
            'persona_cargo'       => 'nullable|string|max:100',
            'fecha_recepcion'     => 'nullable|date',
            'fecha_entrega_aprox' => 'nullable|date',
            'max_revisiones'      => 'nullable|integer|min:1|max:10',
            'fecha_entrega_real'  => 'nullable|date',
            'monto_presup_proy'   => 'nullable|numeric|min:0',
            'monto_vendido_proy'  => 'nullable|numeric|min:0',
            'centro_costos'       => 'nullable|string|max:100',
            'resultado'           => 'nullable|in:G,P,EP,ENT,ENV,I',
            'seguimiento'         => 'nullable|string',
        ]);

        $validated['nombre_proyecto'] = strtoupper($validated['nombre_proyecto']);
        $proyecto->update($validated);

        return redirect()->route('proyectos.show', $proyecto)->with('success', 'Proyecto actualizado exitosamente');
    }
}
