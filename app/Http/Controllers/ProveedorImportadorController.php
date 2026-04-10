<?php

namespace App\Http\Controllers;

use App\Services\ProveedorImportacionService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class ProveedorImportadorController extends Controller
{
    public function __construct(private ProveedorImportacionService $service)
    {
        $this->middleware('role:Administrador,Almacenero');
    }

    public function index()
    {
        $resultados = session('resultados');
        return view('proveedores.importar', compact('resultados'));
    }

    public function descargarPlantilla()
    {
        $spreadsheet = $this->service->generarPlantilla();

        return response()->streamDownload(function () use ($spreadsheet) {
            (new XlsxWriter($spreadsheet))->save('php://output');
        }, 'plantilla_proveedores.xlsx', [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plantilla_proveedores.xlsx"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:20480',
        ], [
            'archivo.required' => 'Selecciona un archivo Excel.',
            'archivo.mimes'    => 'Solo se permiten archivos .xlsx o .xls.',
            'archivo.max'      => 'El archivo no puede superar 20 MB.',
        ]);

        try {
            $path      = $request->file('archivo')->getRealPath();
            $resultado = $this->service->procesar($path);

            return redirect()
                ->route('proveedores.importar.index')
                ->with('resultados', $resultado)
                ->with('success', "Importación completada: {$resultado['insertados']} nuevos, {$resultado['actualizados']} actualizados, {$resultado['errores_count']} errores.");
        } catch (\Throwable $e) {
            return back()->withErrors(['archivo' => 'Error al procesar: ' . $e->getMessage()]);
        }
    }

    public function descargarErrores()
    {
        $resultados = session('resultados');
        if (!$resultados || empty($resultados['errores_csv'])) {
            return redirect()->route('proveedores.importar.index')
                ->with('error', 'No hay errores disponibles para descargar.');
        }

        return response($resultados['errores_csv'], 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="errores_importacion.csv"',
        ]);
    }
}
