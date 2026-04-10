<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guía de Remisión {{ $guiaRemision->numero_guia }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; }
        .page { padding: 20px 30px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #2B2E2C; padding-bottom: 12px; margin-bottom: 12px; }
        .empresa-nombre { font-size: 14px; font-weight: bold; color: #2B2E2C; }
        .empresa-datos { font-size: 9px; color: #555; margin-top: 3px; line-height: 1.5; }
        .doc-box { border: 2px solid #2B2E2C; border-radius: 6px; text-align: center; padding: 10px 20px; min-width: 200px; }
        .doc-tipo { font-size: 11px; font-weight: bold; color: #2B2E2C; }
        .doc-numero { font-size: 18px; font-weight: bold; color: #2B2E2C; font-family: monospace; margin-top: 4px; }

        /* Estado */
        .estado-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px; }
        .estado-borrador  { background: #f3f4f6; color: #6b7280; }
        .estado-enviado   { background: #dbeafe; color: #1d4ed8; }
        .estado-aceptado  { background: #d1fae5; color: #065f46; }
        .estado-rechazado { background: #fee2e2; color: #991b1b; }
        .estado-anulado   { background: #fed7aa; color: #9a3412; }

        /* Secciones */
        .section { margin-bottom: 10px; }
        .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; margin-bottom: 6px; }
        .grid-2 { display: flex; gap: 12px; }
        .grid-2 > div { flex: 1; }
        .field-label { font-size: 8px; color: #9ca3af; }
        .field-value { font-size: 10px; font-weight: 600; color: #1f2937; margin-top: 2px; }

        /* Ruta */
        .ruta { display: flex; align-items: center; gap: 8px; }
        .punto { flex: 1; padding: 8px; border-radius: 6px; }
        .punto-partida { background: #ecfdf5; border: 1px solid #6ee7b7; }
        .punto-llegada  { background: #fef2f2; border: 1px solid #fca5a5; }
        .punto-label { font-size: 8px; font-weight: bold; text-transform: uppercase; margin-bottom: 3px; }
        .punto-partida .punto-label { color: #059669; }
        .punto-llegada  .punto-label { color: #dc2626; }
        .punto-ubigeo { font-size: 8px; color: #6b7280; font-family: monospace; }
        .punto-dir { font-size: 9px; font-weight: 500; }
        .flecha { font-size: 14px; color: #9ca3af; }

        /* Tabla detalle */
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #2B2E2C; color: white; padding: 5px 8px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; font-size: 9px; }
        .td-right { text-align: right; }

        /* Footer */
        .footer { margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; text-align: center; font-size: 8px; color: #9ca3af; }
        .obs-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
<div class="page">

    {{-- Encabezado --}}
    <div class="header">
        <div>
            @if($empresa?->logo_pdf_url)
                <img src="{{ $empresa->logo_pdf_url }}" style="height:40px; margin-bottom:6px;">
            @endif
            <div class="empresa-nombre">{{ $empresa?->razon_social }}</div>
            <div class="empresa-datos">
                RUC: {{ $empresa?->ruc }}<br>
                {{ $empresa?->direccion }}<br>
                @if($empresa?->telefono) Tel: {{ $empresa->telefono }} @endif
            </div>
        </div>
        <div class="doc-box">
            <div class="doc-tipo">GUÍA DE REMISIÓN REMITENTE</div>
            <div class="doc-tipo" style="font-size:9px;color:#555;margin-top:2px;">Tipo: 09</div>
            <div class="doc-numero">{{ $guiaRemision->numero_guia }}</div>
            <div>
                <span class="estado-badge estado-{{ $guiaRemision->estado }}">
                    {{ $guiaRemision->estado_info['label'] }}
                </span>
            </div>
        </div>
    </div>

    {{-- Destinatario + Fechas --}}
    <div class="section">
        <div class="section-title">Destinatario</div>
        <div class="grid-2">
            <div>
                <div class="field-label">Nombre / Razón social</div>
                <div class="field-value">{{ $guiaRemision->destinatario_nombre ?? $guiaRemision->cliente?->nombre ?? '—' }}</div>
                <div class="field-label" style="margin-top:4px;">N° documento</div>
                <div class="field-value">{{ $guiaRemision->destinatario_num_doc ?? $guiaRemision->cliente?->numero_documento ?? '—' }}</div>
                @if($guiaRemision->destinatario_direccion)
                    <div class="field-label" style="margin-top:4px;">Dirección</div>
                    <div class="field-value">{{ $guiaRemision->destinatario_direccion }}</div>
                @endif
            </div>
            <div>
                <div class="field-label">Fecha de emisión</div>
                <div class="field-value">{{ $guiaRemision->fecha_emision->format('d/m/Y') }}</div>
                <div class="field-label" style="margin-top:4px;">Fecha de traslado</div>
                <div class="field-value">{{ $guiaRemision->fecha_traslado->format('d/m/Y') }}</div>
                <div class="field-label" style="margin-top:4px;">Motivo traslado</div>
                <div class="field-value">{{ $guiaRemision->motivo_traslado }} - {{ $guiaRemision->motivo_label }}</div>
                <div class="field-label" style="margin-top:4px;">Modalidad</div>
                <div class="field-value">{{ $guiaRemision->modalidad_label }}</div>
                @if($guiaRemision->peso_bruto)
                    <div class="field-label" style="margin-top:4px;">Peso bruto / N° bultos</div>
                    <div class="field-value">{{ number_format($guiaRemision->peso_bruto, 3) }} KG / {{ $guiaRemision->numero_bultos ?? '—' }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Ruta --}}
    <div class="section">
        <div class="section-title">Ruta de Traslado</div>
        <div class="ruta">
            <div class="punto punto-partida">
                <div class="punto-label">↑ Punto de Partida</div>
                @if($guiaRemision->partida_ubigeo)
                    <div class="punto-ubigeo">Ubigeo: {{ $guiaRemision->partida_ubigeo }}</div>
                @endif
                <div class="punto-dir">{{ $guiaRemision->partida_direccion }}</div>
            </div>
            <div class="flecha">→</div>
            <div class="punto punto-llegada">
                <div class="punto-label">↓ Punto de Llegada</div>
                @if($guiaRemision->llegada_ubigeo)
                    <div class="punto-ubigeo">Ubigeo: {{ $guiaRemision->llegada_ubigeo }}</div>
                @endif
                <div class="punto-dir">{{ $guiaRemision->llegada_direccion }}</div>
            </div>
        </div>
    </div>

    {{-- Transportista --}}
    <div class="section">
        <div class="section-title">Datos del Transportista ({{ $guiaRemision->modalidad_label }})</div>
        @if($guiaRemision->modalidad_transporte === '01')
            <div class="grid-2">
                <div>
                    <div class="field-label">Placa del vehículo</div>
                    <div class="field-value">{{ strtoupper($guiaRemision->placa_vehiculo ?? '—') }}</div>
                    <div class="field-label" style="margin-top:4px;">N° licencia</div>
                    <div class="field-value">{{ $guiaRemision->conductor_licencia ?? '—' }}</div>
                </div>
                <div>
                    <div class="field-label">Conductor</div>
                    <div class="field-value">{{ $guiaRemision->conductor_nombre ?? '—' }}</div>
                    <div class="field-label" style="margin-top:4px;">N° documento</div>
                    <div class="field-value">{{ $guiaRemision->conductor_num_doc ?? '—' }}</div>
                </div>
            </div>
        @else
            <div class="grid-2">
                <div>
                    <div class="field-label">Empresa transportista</div>
                    <div class="field-value">{{ $guiaRemision->transportista_nombre ?? '—' }}</div>
                </div>
                <div>
                    <div class="field-label">RUC</div>
                    <div class="field-value">{{ $guiaRemision->transportista_ruc ?? '—' }}</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Detalle de bienes --}}
    <div class="section">
        <div class="section-title">Detalle de Bienes</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripción</th>
                    <th>Código</th>
                    <th>Unidad</th>
                    <th class="td-right">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guiaRemision->detalles as $i => $detalle)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $detalle->descripcion }}</td>
                        <td style="font-family:monospace;">{{ $detalle->codigo ?: '—' }}</td>
                        <td>{{ $detalle->unidad_medida }}</td>
                        <td class="td-right">{{ number_format($detalle->cantidad, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($guiaRemision->observaciones)
        <div class="section">
            <div class="section-title">Observaciones</div>
            <div class="obs-box">{{ $guiaRemision->observaciones }}</div>
        </div>
    @endif

    @if($guiaRemision->sunat_hash)
        <div class="section">
            <div class="section-title">Hash SUNAT</div>
            <div style="font-family:monospace; font-size:8px; color:#6b7280; word-break:break-all;">{{ $guiaRemision->sunat_hash }}</div>
        </div>
    @endif

    <div class="footer">
        Representación impresa de la Guía de Remisión Electrónica —
        Emitido por {{ $empresa?->razon_social }} (RUC {{ $empresa?->ruc }}) —
        {{ now()->format('d/m/Y H:i') }}
    </div>
</div>
</body>
</html>
