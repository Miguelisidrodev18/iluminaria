<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Models\Importacion;
use App\Services\ImportadorMasivoService;

class ProcesarImportacionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Un solo intento: si falla, marcar como 'fallido' y no reintentar
     * (los datos parciales ya estarían en BD).
     */
    public int $tries   = 1;

    /**
     * 90 minutos: suficiente para +5000 filas con operaciones BD.
     */
    public int $timeout = 5400;

    public function __construct(private readonly int $importacionId)
    {
    }

    public function handle(ImportadorMasivoService $service): void
    {
        $importacion = Importacion::findOrFail($this->importacionId);

        $importacion->update([
            'estado'     => 'procesando',
            'started_at' => now(),
        ]);

        try {
            $service->procesar($importacion);

            $importacion->update([
                'estado'      => 'completado',
                'finished_at' => now(),
            ]);

        } catch (\Throwable $e) {
            Log::error('ImportacionJob falló', [
                'importacion_id' => $this->importacionId,
                'error'          => $e->getMessage(),
            ]);

            // Guardar el error en el registro para que la UI lo muestre
            $errores   = $importacion->errores ?? [];
            $errores[] = 'Error crítico: ' . $e->getMessage();

            $importacion->update([
                'estado'      => 'fallido',
                'finished_at' => now(),
                'errores'     => $errores,
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Asegurar que el estado quede como 'fallido' incluso si handle() lanzó
        Importacion::where('id', $this->importacionId)
            ->whereNotIn('estado', ['completado'])
            ->update(['estado' => 'fallido', 'finished_at' => now()]);
    }
}
