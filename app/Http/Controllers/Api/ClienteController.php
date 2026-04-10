<?php
// app/Http/Controllers/Api/ClienteController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Búsqueda dinámica por nombre, empresa o DNI (autocomplete).
     * Requiere mínimo 3 caracteres.
     */
    public function buscarTexto(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        $clientes = Cliente::where('estado', 'activo')
            ->where(fn ($w) => $w
                ->where('apellidos', 'like', "%{$q}%")
                ->orWhere('nombres',  'like', "%{$q}%")
                ->orWhere('nombre',   'like', "%{$q}%")
                ->orWhere('empresa',  'like', "%{$q}%")
                ->orWhere('dni',      'like', "%{$q}%")
                ->orWhere('celular',  'like', "%{$q}%")
            )
            ->orderBy('apellidos')
            ->limit(10)
            ->get(['id', 'apellidos', 'nombres', 'nombre', 'empresa', 'dni', 'celular', 'tipo_cliente']);

        return response()->json($clientes->map(fn ($c) => [
            'id'           => $c->id,
            'texto'        => $c->nombre_completo,
            'subtexto'     => implode(' · ', array_filter([$c->empresa, $c->dni, $c->celular])),
            'tipo_cliente' => $c->tipo_cliente,
        ]));
    }

    /**
     * Buscar cliente por número de documento
     */
    public function buscarPorDocumento(Request $request)
    {
        $request->validate([
            'documento' => 'required|string'
        ]);

        $documento = $request->documento;

        $cliente = Cliente::where('numero_documento', $documento)
            ->orWhere('ruc', $documento)
            ->orWhere('dni', $documento)
            ->first();

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'cliente' => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'tipo_documento' => $cliente->tipo_documento,
                'numero_documento' => $cliente->numero_documento,
                'email' => $cliente->email,
                'telefono' => $cliente->telefono,
                'direccion' => $cliente->direccion
            ]
        ]);
    }

    /**
     * Crear cliente rápido (desde POS)
     */
    public function storeRapido(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_documento' => 'required|in:dni,ruc,cex',
            'numero_documento' => 'required|string|unique:clientes,numero_documento',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string'
        ]);

        $cliente = Cliente::create([
            'nombre' => $request->nombre,
            'tipo_documento' => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'estado' => 'activo'
        ]);

        return response()->json([
            'success' => true,
            'cliente' => $cliente
        ]);
    }
}