<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

@page {
    margin-top: 28mm;
    margin-bottom: 22mm;
    margin-left: 14mm;
    margin-right: 14mm;
}

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 8.5pt;
    color: #1a1a1a;
    background: #fff;
}

/* ── HEADER ─────────────────────────────────────────────── */
.header {
    display: table;
    width: 100%;
    margin-bottom: 12px;
    border-bottom: 2px solid #e8c900;
    padding-bottom: 8px;
}
.header-logo-cell {
    display: table-cell;
    width: 35%;
    vertical-align: middle;
}
.header-logo-cell img {
    max-height: 52px;
    max-width: 160px;
}
.header-nombre-fallback {
    font-size: 14pt;
    font-weight: bold;
    color: #2B2E2C;
    letter-spacing: -0.5px;
}
.header-info-cell {
    display: table-cell;
    width: 65%;
    vertical-align: middle;
    text-align: right;
}
.header-info-cell .dir {
    font-size: 8pt;
    color: #D97706;
    font-weight: bold;
    line-height: 1.5;
}
.header-info-cell .contact {
    font-size: 7.5pt;
    color: #555;
    line-height: 1.6;
}

/* ── TITLE ───────────────────────────────────────────────── */
.title-wrap {
    margin-bottom: 10px;
}
.proforma-num {
    font-size: 12pt;
    font-weight: bold;
    color: #1a1a1a;
    text-align: center;
    margin-bottom: 3px;
}
.proforma-date {
    font-size: 8.5pt;
    color: #D97706;
    text-align: center;
    font-weight: bold;
}

/* ── CLIENT ──────────────────────────────────────────────── */
.cliente-section {
    margin-bottom: 10px;
}
.cliente-nombre {
    font-size: 9pt;
    font-weight: bold;
    color: #1a1a1a;
    text-transform: uppercase;
}
.cliente-contacto {
    font-size: 8pt;
    color: #333;
    margin-top: 1px;
}
.intro-text {
    font-size: 8.5pt;
    font-weight: bold;
    color: #333;
    margin-top: 6px;
}

/* ── ITEMS TABLE ─────────────────────────────────────────── */
table.items {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4px;
    font-size: 8pt;
}
table.items thead tr {
    background: #F7D600;
    color: #1a1a1a;
}
table.items thead th {
    padding: 5px 6px;
    text-align: left;
    font-weight: bold;
    font-size: 7.5pt;
    border: 1px solid #D4B800;
}
table.items thead th.center { text-align: center; }
table.items thead th.right  { text-align: right; }

table.items tbody tr:nth-child(even) { background: #fafafa; }
table.items tbody td {
    padding: 5px 6px;
    border-bottom: 1px solid #e8e8e8;
    border-left: 1px solid #f0f0f0;
    border-right: 1px solid #f0f0f0;
    vertical-align: top;
}
table.items tbody td.center { text-align: center; }
table.items tbody td.right  { text-align: right; }
.variante-tag {
    font-size: 7pt;
    color: #888;
    display: block;
    margin-top: 1px;
}

/* ── TOTALS ──────────────────────────────────────────────── */
.totals-wrapper {
    width: 100%;
    margin-bottom: 14px;
}
.totals-box {
    float: right;
    width: 210px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    font-size: 8pt;
}
.totals-box .row {
    display: table;
    width: 100%;
    border-bottom: 1px solid #f0f0f0;
}
.totals-box .row:last-child { border-bottom: none; }
.totals-box .lbl {
    display: table-cell;
    padding: 4px 8px;
    color: #555;
}
.totals-box .val {
    display: table-cell;
    padding: 4px 8px;
    text-align: right;
    font-weight: bold;
    color: #1a1a1a;
    width: 90px;
}
.totals-box .row.total-final {
    background: #2B2E2C;
}
.totals-box .row.total-final .lbl,
.totals-box .row.total-final .val {
    color: #F7D600;
    font-size: 10pt;
    font-weight: bold;
    padding: 6px 8px;
}

/* ── CONDICIONES ─────────────────────────────────────────── */
.condiciones {
    clear: both;
    border-top: 1.5px solid #F7D600;
    padding-top: 8px;
    margin-top: 4px;
}
.condiciones-titulo {
    font-size: 8.5pt;
    font-weight: bold;
    color: #1a1a1a;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.3pt;
}
.condicion-row {
    font-size: 7.5pt;
    color: #333;
    line-height: 1.55;
    margin-bottom: 2px;
}
.condicion-row strong { color: #1a1a1a; }

/* ── FOOTER VENDEDOR ─────────────────────────────────────── */
.vendedor-section {
    display: table;
    width: 100%;
    margin-top: 14px;
    border-top: 1px solid #e0e0e0;
    padding-top: 8px;
}
.vendedor-info {
    display: table-cell;
    vertical-align: bottom;
    font-size: 7.5pt;
    color: #333;
}
.vendedor-nombre {
    font-weight: bold;
    font-size: 9pt;
    color: #2B2E2C;
}
.vendedor-pagina {
    display: table-cell;
    vertical-align: bottom;
    text-align: right;
    font-size: 7.5pt;
    color: #999;
}

.clearfix::after { content: ""; display: table; clear: both; }
</style>
</head>
<body>

{{-- ══ HEADER ══════════════════════════════════════════════════ --}}
@php
    /* Logo: usar logo_pdf_path si existe, si no logo_path */
    $logoFile = ($empresa->logo_pdf_path && trim($empresa->logo_pdf_path) !== '')
        ? $empresa->logo_pdf_path
        : ($empresa->logo_path ?? null);

    $logoSrc = null;
    if ($logoFile) {
        $logoPath = storage_path('app/public/' . $logoFile);
        if (file_exists($logoPath)) {
            $ext    = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $mime   = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : "image/{$ext}";
            $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    }

    $telPrincipal  = $empresa->telefono ?? '';
    $telSecundario = $empresa->telefono2 ?? '';
    $emailEmp      = $empresa->email ?? '';
    $webEmp        = $empresa->web ?? 'kyrios.com.pe';
@endphp

<div class="header">
    <div class="header-logo-cell">
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="{{ $empresa->nombre_comercial ?? $empresa->razon_social }}">
        @else
            <div class="header-nombre-fallback">{{ $empresa->nombre_comercial ?? $empresa->razon_social }}</div>
        @endif
    </div>
    <div class="header-info-cell">
        <div class="dir">{{ $empresa->direccion }}</div>
        <div class="contact">
            @if($telPrincipal){{ $telPrincipal }}@if($telSecundario) | {{ $telSecundario }}@endif<br>@endif
            @if($emailEmp){{ $emailEmp }}<br>@endif
            @if($webEmp){{ $webEmp }}@endif
        </div>
    </div>
</div>

{{-- ══ TITLE ═════════════════════════════════════════════════════ --}}
<div class="title-wrap">
    <div class="proforma-num">PROFORMA {{ $venta->codigo }}</div>
    <div class="proforma-date">
        {{ ucfirst(\Carbon\Carbon::parse($venta->fecha)->locale('es')->isoFormat('D [de] MMMM [del] YYYY')) }}
    </div>
</div>

{{-- ══ CLIENT ═════════════════════════════════════════════════════ --}}
<div class="cliente-section">
    <div class="cliente-nombre">SRES. {{ $venta->cliente?->nombre ?? 'CLIENTE GENERAL' }}</div>
    @if($venta->contacto)
        <div class="cliente-contacto">Contacto: <strong>{{ $venta->contacto }}</strong></div>
    @elseif($venta->cliente?->email)
        <div class="cliente-contacto">Contacto: <strong>{{ $venta->cliente->email }}</strong></div>
    @endif
    <div class="intro-text">De acuerdo a lo solicitado por Ud. le hacemos llegar el costo por los siguientes items</div>
</div>

{{-- ══ ITEMS TABLE ════════════════════════════════════════════════ --}}
@php
    $simbolo  = $venta->moneda === 'USD' ? 'US$' : 'S/';
    $subArt   = 0;
    $subDescuentado = 0;

    foreach ($venta->detalles as $d) {
        $dcto        = floatval($d->descuento_pct ?? 0);
        $precioLista = $dcto > 0
            ? round($d->precio_unitario / (1 - $dcto / 100), 4)
            : $d->precio_unitario;
        $subArt          += $precioLista * $d->cantidad;
        $subDescuentado  += $d->precio_unitario * $d->cantidad;
    }

    $descuentoTotal = round($subArt - $subDescuentado, 2);
    $subtotalFinal  = round($subDescuentado, 2);

    /* IGV: si el precio ya incluye IGV no se suma de nuevo */
    $incluyeIgv = (bool)($venta->detalles->first()?->incluye_igv ?? false);
    if ($incluyeIgv) {
        $igv   = round($subtotalFinal * 18 / 118, 2);
        $total = $subtotalFinal;
    } else {
        $igv   = round($subtotalFinal * 0.18, 2);
        $total = $subtotalFinal + $igv;
    }
@endphp

<table class="items">
    <thead>
        <tr>
            <th class="center" style="width:26px;">ITEM</th>
            <th class="center" style="width:38px;">CANT.</th>
            <th>DESCRIPCION</th>
            <th class="right" style="width:78px;">P.UNIT.</th>
            <th class="center" style="width:46px;">DCTO.</th>
            <th class="right" style="width:82px;">P.TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($venta->detalles as $i => $d)
        @php
            $dcto        = floatval($d->descuento_pct ?? 0);
            $precioLista = $dcto > 0
                ? round($d->precio_unitario / (1 - $dcto / 100), 2)
                : $d->precio_unitario;
            $pTotal = round($d->precio_unitario * $d->cantidad, 2);
        @endphp
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td class="center">{{ $d->cantidad }}</td>
            <td>
                <strong>{{ strtoupper($d->producto?->nombre ?? '—') }}</strong>
                @if($d->variante && $d->variante->nombre_completo !== 'Variante base')
                    <span class="variante-tag">{{ $d->variante->nombre_completo }}</span>
                @endif
                @if($d->observacion ?? null)
                    <span class="variante-tag" style="color:#999;">{{ $d->observacion }}</span>
                @endif
            </td>
            <td class="right">{{ $simbolo }} {{ number_format($precioLista, 2) }}</td>
            <td class="center">
                {{ $dcto > 0 ? number_format($dcto, 0).'%' : '—' }}
            </td>
            <td class="right">{{ $simbolo }} {{ number_format($pTotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ══ TOTALS ══════════════════════════════════════════════════════ --}}
<div class="totals-wrapper clearfix">
    <div class="totals-box">
        <div class="row">
            <div class="lbl">Subtotal Artículos:</div>
            <div class="val">{{ $simbolo }} {{ number_format($subArt, 2) }}</div>
        </div>
        <div class="row">
            <div class="lbl">Subtotal:</div>
            <div class="val">{{ $simbolo }} {{ number_format($subArt, 2) }}</div>
        </div>
        @if($descuentoTotal > 0)
        <div class="row">
            <div class="lbl">Descuento:</div>
            <div class="val">{{ $simbolo }} {{ number_format($descuentoTotal, 2) }}</div>
        </div>
        <div class="row">
            <div class="lbl">Subtotal Final:</div>
            <div class="val">{{ $simbolo }} {{ number_format($subtotalFinal, 2) }}</div>
        </div>
        @endif
        <div class="row">
            <div class="lbl">IGV (18%):</div>
            <div class="val">{{ $simbolo }} {{ number_format($igv, 2) }}</div>
        </div>
        <div class="row total-final">
            <div class="lbl">TOTAL:</div>
            <div class="val">{{ $simbolo }} {{ number_format($total, 2) }}</div>
        </div>
        @if($venta->moneda === 'USD' && ($venta->tipo_cambio ?? 0) > 1)
        <div class="row">
            <div class="lbl" style="font-size:6.5pt;color:#aaa;">Equiv. S/ (T.C. {{ number_format($venta->tipo_cambio, 3) }}):</div>
            <div class="val" style="font-size:6.5pt;color:#aaa;">S/ {{ number_format($total * $venta->tipo_cambio, 2) }}</div>
        </div>
        @endif
    </div>
</div>

{{-- ══ CONDICIONES COMERCIALES ════════════════════════════════════ --}}
@php
    /* Resolver datos de pago desde la sucursal */
    $cuentas = [];
    if ($pagos && $pagos->count()) {
        foreach ($pagos as $tipo => $p) {
            if ($tipo === 'transferencia') {
                $cuentas[] = trim(
                    ($p->banco ? $p->banco . ' ' : '') .
                    ($p->numero_cuenta ? 'Cta: ' . $p->numero_cuenta . ' ' : '') .
                    ($p->cci ? '/ CCI: ' . $p->cci : '')
                );
            }
        }
    }
    $cuentaTexto = !empty($cuentas)
        ? implode('; ', $cuentas)
        : 'Depósito o transferencia a Cta Cte del banco INTERBANK US$ 2003007861610 / CCI US$003-200-003007861610-36 a nombre de ' . ($empresa->nombre_comercial ?? $empresa->razon_social) . ' con RUC ' . $empresa->ruc;
@endphp

<div class="condiciones">
    <div class="condiciones-titulo">Condiciones Comerciales:</div>
    <div class="condicion-row">
        <strong>Forma de Pago:</strong>
        100% Con la orden de compra. {{ $cuentaTexto }}.
    </div>
    <div class="condicion-row">
        <strong>Tiempo de Entrega:</strong>
        A coordinar / vigencia a partir del conocimiento de realizado el depósito y/o transferencia en cta.
    </div>
    @if(($venta->vigencia_dias ?? 0) > 0)
    <div class="condicion-row">
        <strong>Vigencia de la oferta:</strong>
        {{ $venta->vigencia_dias }} días calendarios desde la fecha de emisión.
    </div>
    @endif
    <div class="condicion-row">
        <strong>Garantía Luminarias:</strong>
        01 Año (en caso realicemos la instalación). Lámpara: 75% vida útil de lámpara.
    </div>
    @if($venta->observaciones)
    <div class="condicion-row">
        <strong>Notas:</strong> {{ $venta->observaciones }}
    </div>
    @endif
    <div class="condicion-row">
        <strong>Los precios están expresados en {{ $venta->moneda === 'USD' ? 'Dólares Americanos' : 'Soles Peruanos' }} y NO INCLUYEN el 18% I.G.V.</strong>
    </div>
</div>

{{-- ══ VENDEDOR ════════════════════════════════════════════════════ --}}
<div class="vendedor-section">
    <div class="vendedor-info">
        @if($venta->vendedor)
            <div class="vendedor-nombre">{{ $venta->vendedor->name }}</div>
            <div style="color:#666;">{{ $venta->vendedor->role?->nombre ?? 'Ventas' }}</div>
            @if($venta->vendedor->email)<div>{{ $venta->vendedor->email }}</div>@endif
            @if($venta->vendedor->telefono ?? null)<div>{{ $venta->vendedor->telefono }}</div>@endif
        @endif
    </div>
    <div class="vendedor-pagina">
        Pagina 1/1<br>
        <span style="font-size:6.5pt;color:#bbb;">{{ $webEmp ?: 'kyrios.com.pe' }}</span>
    </div>
</div>

</body>
</html>
