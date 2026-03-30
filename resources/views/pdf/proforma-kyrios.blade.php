<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
@page { margin: 14mm 16mm; }
body { font-family: DejaVu Sans, sans-serif; font-size: 8.5pt; color: #1a1a1a; background: #fff; }

/* Header: 2 columns - logo+company left, contact info right */
.header { display: table; width: 100%; margin-bottom: 10px; }
.header-left { display: table-cell; width: 30%; vertical-align: middle; }
.header-right { display: table-cell; width: 70%; vertical-align: middle; text-align: right; font-size: 8pt; color: #2B2E2C; }
.logo { height: 50px; }

/* Title section */
.title-section { text-align: center; margin-bottom: 10px; border-top: 2px solid #2B2E2C; border-bottom: 2px solid #2B2E2C; padding: 6px 0; }
.proforma-num { font-size: 12pt; font-weight: bold; color: #2B2E2C; }
.proforma-date { font-size: 8.5pt; color: #555; margin-top: 2px; }

/* Client section */
.cliente-section { margin-bottom: 10px; }
.cliente-nombre { font-size: 9pt; font-weight: bold; }
.cliente-contacto { font-size: 8.5pt; color: #333; margin-top: 2px; }
.intro-text { font-size: 8.5pt; color: #333; margin-top: 6px; font-weight: bold; }

/* Items table */
table.items { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 8pt; }
table.items thead tr { background: #2B2E2C; color: #F7D600; }
table.items thead th { padding: 5px 6px; text-align: left; font-weight: bold; font-size: 7.5pt; }
table.items thead th.center { text-align: center; }
table.items thead th.right { text-align: right; }
table.items tbody tr:nth-child(even) { background: #f9f9f9; }
table.items tbody td { padding: 4px 6px; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
table.items tbody td.center { text-align: center; }
table.items tbody td.right { text-align: right; }

/* Subtotal area */
.subtotal-row { display: table; width: 100%; margin-bottom: 2px; }
.subtotal-label { display: table-cell; font-size: 8pt; color: #555; padding: 2px 0; }
.subtotal-val { display: table-cell; font-size: 8pt; color: #1a1a1a; font-weight: bold; text-align: right; width: 100px; padding: 2px 0; }
.subtotal-total-label { font-size: 10pt; font-weight: bold; color: #2B2E2C; }
.subtotal-total-val { font-size: 10pt; font-weight: bold; color: #2B2E2C; }
.subtotal-divider { border-top: 1.5px solid #2B2E2C; margin: 3px 0; }
.subtotal-box { width: 200px; float: right; }

/* Condiciones comerciales */
.condiciones { margin-top: 16px; border-top: 1px solid #ccc; padding-top: 8px; clear: both; }
.condiciones-titulo { font-size: 8.5pt; font-weight: bold; color: #2B2E2C; margin-bottom: 4px; }
.condiciones-text { font-size: 7pt; color: #444; line-height: 1.5; }

/* Vendedor pie */
.vendedor-section { margin-top: 12px; display: table; width: 100%; border-top: 1px solid #ccc; padding-top: 8px; }
.vendedor-info { display: table-cell; vertical-align: bottom; font-size: 7.5pt; color: #333; }
.vendedor-nombre { font-weight: bold; font-size: 9pt; color: #2B2E2C; }
.vendedor-pagina { display: table-cell; vertical-align: bottom; text-align: right; font-size: 7.5pt; color: #777; }
</style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    <div class="header-left">
        @php
            $logoFile = $empresa->logo_pdf_path ?? ($empresa->logo_path ?? null);
            $logoPath = $logoFile ? storage_path('app/public/' . $logoFile) : null;
            $logoSrc  = null;
            if ($logoPath && file_exists($logoPath)) {
                $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                $mime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : "image/$ext";
                $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        @endphp
        @if($logoSrc)
            <img src="{{ $logoSrc }}" class="logo" alt="Logo">
        @else
            <div style="font-size:11pt;font-weight:bold;color:#2B2E2C;">{{ $empresa->nombre_comercial ?? $empresa->razon_social }}</div>
        @endif
    </div>
    <div class="header-right">
        {{ $empresa->direccion }}<br>
        @if($empresa->telefono) {{ $empresa->telefono }}<br>@endif
        @if($empresa->email) {{ $empresa->email }}<br>@endif
        @if($empresa->web ?? null) {{ $empresa->web }}@endif
    </div>
</div>

{{-- TITLE --}}
<div class="title-section">
    <div class="proforma-num">PROFORMA {{ $venta->codigo }}/{{ $venta->fecha->format('y') }}</div>
    <div class="proforma-date">{{ ucfirst(\Carbon\Carbon::parse($venta->fecha)->locale('es')->isoFormat('D [de] MMMM [del] YYYY')) }}</div>
</div>

{{-- CLIENT --}}
<div class="cliente-section">
    <div class="cliente-nombre">SRES. {{ $venta->cliente?->nombre ?? 'CLIENTE GENERAL' }}</div>
    @if($venta->contacto)
        <div class="cliente-contacto">Contacto: <strong>{{ $venta->contacto }}</strong></div>
    @endif
    @if($venta->cliente?->email)
        <div class="cliente-contacto">{{ $venta->cliente->email }}</div>
    @endif
    <div class="intro-text">De acuerdo a lo solicitado por Ud. le hacemos llegar el costo por los siguientes items</div>
</div>

{{-- ITEMS TABLE --}}
@php
    $simbolo = $venta->moneda === 'USD' ? 'US$' : 'S/';
    $subtotalArticulos = 0;
    $totalConDescuento = 0;
    foreach ($venta->detalles as $d) {
        $dcto = $d->descuento_pct ?? 0;
        $precioLista = $dcto > 0 ? round($d->precio_unitario / (1 - $dcto / 100), 4) : $d->precio_unitario;
        $subtotalArticulos += $precioLista * $d->cantidad;
        $totalConDescuento += $d->precio_unitario * $d->cantidad;
    }
    $descuentoTotal = $subtotalArticulos - $totalConDescuento;
    $subtotalFinal  = $totalConDescuento;
    $igv            = round($subtotalFinal * 0.18, 2);
    $total          = $subtotalFinal + $igv;
@endphp

<table class="items">
    <thead>
        <tr>
            <th style="width:28px" class="center">ITEM</th>
            <th style="width:40px" class="center">CANT.</th>
            <th>DESCRIPCION</th>
            <th class="right" style="width:75px">P.UNIT.</th>
            <th class="center" style="width:50px">DCTO.</th>
            <th class="right" style="width:80px">P.TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($venta->detalles as $i => $d)
        @php
            $dcto = $d->descuento_pct ?? 0;
            $precioLista = $dcto > 0 ? round($d->precio_unitario / (1 - $dcto / 100), 2) : $d->precio_unitario;
            $pTotal = $d->precio_unitario * $d->cantidad;
        @endphp
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td class="center">{{ $d->cantidad }}</td>
            <td>
                {{ $d->producto?->nombre ?? '&#8212;' }}
                @if($d->variante)
                    <br><span style="font-size:7pt;color:#666;">{{ $d->variante->nombre_completo }}</span>
                @endif
            </td>
            <td class="right">{{ $simbolo }} {{ number_format($precioLista, 2) }}</td>
            <td class="center">{{ $dcto > 0 ? number_format($dcto, 0).'%' : '&#8212;' }}</td>
            <td class="right">{{ $simbolo }} {{ number_format($pTotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- TOTALS --}}
<div style="clear:both;">
    <div class="subtotal-box">
        <div class="subtotal-row">
            <div class="subtotal-label">Subtotal Articulos:</div>
            <div class="subtotal-val">{{ $simbolo }} {{ number_format($subtotalArticulos, 2) }}</div>
        </div>
        <div class="subtotal-row">
            <div class="subtotal-label">Subtotal:</div>
            <div class="subtotal-val">{{ $simbolo }} {{ number_format($subtotalArticulos, 2) }}</div>
        </div>
        @if($descuentoTotal > 0)
        <div class="subtotal-row">
            <div class="subtotal-label">Descuento:</div>
            <div class="subtotal-val">{{ $simbolo }} {{ number_format($descuentoTotal, 2) }}</div>
        </div>
        <div class="subtotal-row">
            <div class="subtotal-label">Subtotal Final:</div>
            <div class="subtotal-val">{{ $simbolo }} {{ number_format($subtotalFinal, 2) }}</div>
        </div>
        @endif
        <div class="subtotal-row">
            <div class="subtotal-label">IGV (18%):</div>
            <div class="subtotal-val">{{ $simbolo }} {{ number_format($igv, 2) }}</div>
        </div>
        <div class="subtotal-divider"></div>
        <div class="subtotal-row">
            <div class="subtotal-label subtotal-total-label">TOTAL:</div>
            <div class="subtotal-val subtotal-total-val">{{ $simbolo }} {{ number_format($total, 2) }}</div>
        </div>
        @if($venta->moneda === 'USD' && $venta->tipo_cambio > 1)
        <div class="subtotal-row" style="margin-top:4px;">
            <div class="subtotal-label" style="font-size:7pt;color:#888;">T.C.: S/ {{ number_format($venta->tipo_cambio, 3) }}</div>
            <div class="subtotal-val" style="font-size:7pt;color:#888;">S/ {{ number_format($total * $venta->tipo_cambio, 2) }}</div>
        </div>
        @endif
    </div>
</div>

{{-- CONDICIONES COMERCIALES --}}
<div class="condiciones">
    <div class="condiciones-titulo">CONDICIONES COMERCIALES:</div>
    <div class="condiciones-text">
        <strong>Forma de Pago:</strong> 100% Con la orden de compra. Deposito o transferencia a Cta Cte del banco INTERBANK
        US$ 2003007861610 / CCI US$003-200-003007861610-36 a nombre de la empresa Kyrios luces S.A.C. con RUC 20606247746.<br>
        <strong>Tiempo de Entrega:</strong> A coordinar / vigencia a partir del conocimiento de realizado el deposito y/o transferencia en cta.
        @if($venta->vigencia_dias)
        <br><strong>Vigencia de la oferta:</strong> {{ $venta->vigencia_dias }} dias calendarios desde la fecha de emision.
        @endif
        <br><strong>Garantia Luminarias:</strong> 01 Anio (en caso realicemos la instalacion). Lampara: 75% vida util de lampara.
        <br><strong>Los precios estan expresados en {{ $venta->moneda === 'USD' ? 'Dolares Americanos' : 'Soles Peruanos' }} y NO INCLUYEN el 18% I.G.V.</strong>
    </div>
</div>

{{-- VENDEDOR --}}
<div class="vendedor-section">
    <div class="vendedor-info">
        @if($venta->vendedor)
            <div class="vendedor-nombre">{{ $venta->vendedor->name }}</div>
            <div>{{ $venta->vendedor->role?->nombre ?? 'Ventas' }}</div>
            <div>{{ $venta->vendedor->email }}</div>
            @if($venta->vendedor->telefono ?? null)<div>{{ $venta->vendedor->telefono }}</div>@endif
        @endif
    </div>
    <div class="vendedor-pagina">
        Pagina 1/1<br>
        <span style="font-size:6.5pt;">kyrios.com.pe</span>
    </div>
</div>

</body>
</html>
