<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cfe_types[$cfe->cfe_type] ?? 'CFE' }} {{ $cfe->series }}-{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* ---- ESTILOS OFICIALES DGI ---- */
        
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #000;
            background: #ccc;
        }

        @media print {
            body { background: #fff; margin: 0; }
            .invoice-wrap { width: 100%; margin: 0; box-shadow: none; border: none; }
            .no-print { display: none !important; }
            @page { size: A4; margin: 8mm; }
        }

        /* Contenedor principal */
        .invoice-wrap {
            width: 210mm;
            min-height: 297mm;
            margin: 10px auto;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            padding: 8mm 10mm;
            position: relative;
        }

        /* ---- CABECERA PRINCIPAL ---- */
        .cfe-header {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .cfe-header-left {
            display: table-cell;
            width: 45%;
            vertical-align: top;
            padding-right: 10px;
        }
        .cfe-header-right {
            display: table-cell;
            width: 55%;
            vertical-align: top;
            text-align: right;
        }

        /* Bloque emisor (izquierda) */
        .emisor-name {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .emisor-details {
            font-size: 10px;
            line-height: 1.6;
        }

        /* Bloque RUC/Tipo/Serie (derecha) */
        .doc-box {
            border: 1px solid #000;
            display: inline-block;
            min-width: 180px;
            text-align: center;
            margin-bottom: 4px;
        }
        .doc-box-ruc {
            font-size: 13px;
            font-weight: bold;
            padding: 3px 8px;
            border-bottom: 1px solid #000;
        }
        .doc-box-tipo {
            font-size: 11px;
            font-weight: bold;
            padding: 3px 8px;
            border-bottom: 1px solid #000;
            background: #f5f5f5;
        }
        .doc-box-subtipo {
            font-size: 10px;
            padding: 2px 8px;
        }

        /* Tabla Serie/Número/Moneda */
        .serie-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 4px;
            font-size: 10px;
        }
        .serie-table th {
            background: #e0e0e0;
            border: 1px solid #000;
            padding: 2px 5px;
            font-weight: bold;
            text-align: center;
        }
        .serie-table td {
            border: 1px solid #000;
            padding: 2px 5px;
            text-align: center;
        }

        /* Tabla de fechas */
        .fechas-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 3px;
            font-size: 10px;
        }
        .fechas-table th {
            background: #e0e0e0;
            border: 1px solid #000;
            padding: 2px 5px;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }
        .fechas-table td {
            border: 1px solid #000;
            padding: 2px 5px;
            text-align: center;
        }

        /* ---- SECCION RUC COMPRADOR / CLIENTE ---- */
        .comprador-section {
            display: table;
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin: 6px 0;
        }
        .comprador-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 4px 6px;
            border-right: 1px solid #000;
        }
        .cliente-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 4px 6px;
        }
        .comprador-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #444;
            margin-bottom: 1px;
        }
        .comprador-value {
            font-size: 12px;
            font-weight: bold;
        }
        .cliente-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #444;
            margin-bottom: 1px;
        }
        .cliente-value {
            font-size: 11px;
            font-weight: bold;
        }

        /* ---- DOMICILIO FISCAL ---- */
        .domicilio-section {
            border: 1px solid #000;
            border-top: none;
            padding: 3px 6px;
            margin-bottom: 6px;
        }
        .domicilio-label {
            font-size: 9px;
            font-weight: bold;
            color: #444;
            display: inline;
        }
        .domicilio-value {
            font-size: 10px;
            display: inline;
            margin-left: 4px;
        }
        .domicilio-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #ccc;
            margin-top: 3px;
            font-size: 9px;
        }
        .domicilio-table th {
            color: #666;
            font-weight: bold;
            padding: 1px 4px;
            text-align: left;
        }
        .domicilio-table td {
            padding: 1px 4px;
        }

        /* ---- TABLA DE CONCEPTOS ---- */
        .conceptos-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 10px;
            margin-bottom: 0;
        }
        .conceptos-table thead tr {
            background: #d0d0d0;
        }
        .conceptos-table th {
            border: 1px solid #000;
            padding: 3px 5px;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
        }
        .conceptos-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: top;
        }
        .conceptos-table td.text-center { text-align: center; }
        .conceptos-table td.text-right { text-align: right; }

        /* Fila de totales bajo la tabla */
        .totales-section {
            border: 1px solid #000;
            border-top: none;
            display: table;
            width: 100%;
        }
        .totales-left {
            display: table-cell;
            width: 55%;
            border-right: 1px solid #000;
            padding: 3px 5px;
            font-size: 9px;
            vertical-align: middle;
        }
        .totales-right {
            display: table-cell;
            width: 45%;
            text-align: right;
        }

        .subtotales-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .subtotales-table td {
            padding: 2px 5px;
            border-bottom: 1px solid #eee;
        }
        .subtotales-table tr:last-child td {
            border-bottom: none;
        }
        .subtotales-table td.label-col {
            color: #555;
        }
        .subtotales-table td.value-col {
            text-align: right;
            font-weight: bold;
        }

        /* Bloque TOTAL FACTURA */
        .total-factura-box {
            border-top: 1px solid #000;
            border-left: 1px solid #ccc;
            padding: 4px 8px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            font-weight: bold;
            background: #f5f5f5;
        }

        /* ---- TOTAL A PAGAR ---- */
        .total-pagar-box {
            border: 2px solid #000;
            text-align: right;
            padding: 4px 8px;
            margin-top: 6px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: bold;
        }

        /* ---- FOOTER DGI ---- */
        .dgi-footer {
            margin-top: 20px;
            border-top: 1px solid #aaa;
            padding-top: 6px;
            font-size: 9px;
            color: #333;
        }
        .dgi-footer-grid {
            display: table;
            width: 100%;
        }
        .dgi-footer-left {
            display: table-cell;
            vertical-align: top;
            width: 65%;
            line-height: 1.7;
        }
        .dgi-footer-right {
            display: table-cell;
            vertical-align: bottom;
            width: 35%;
            text-align: right;
        }
        .cae-box {
            border: 1px solid #aaa;
            padding: 3px 8px;
            display: inline-block;
            text-align: center;
            font-size: 9px;
        }
        .cae-box .cae-title {
            font-size: 8px;
            color: #666;
        }
        .cae-box .cae-value {
            font-weight: bold;
        }

        /* Botones no-print */
        .actions {
            text-align: center;
            padding: 12px;
            background: #fff;
            margin: 10px auto;
            max-width: 210mm;
        }
        .actions .btn {
            display: inline-block;
            padding: 8px 18px;
            margin: 4px;
            font-size: 12px;
            cursor: pointer;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-decoration: none;
            background: #f5f5f5;
            color: #333;
        }
        .actions .btn-primary { background: #1a56db; color: #fff; border-color: #1a56db; }
        .actions .btn-success { background: #057a55; color: #fff; border-color: #057a55; }
    </style>
</head>
<body>

@php
    /* -------- DATOS DEL EMISOR -------- */
    $location_has_own_rut = isset($location) && $location
        && !empty($location->location_id)
        && $location->location_id !== $business->tax_number_1;

    $display_company_name = $location_has_own_rut ? $location->name : ($cfe->emitter_name ?? $business->name);

    $location_logo = ($location_has_own_rut && !empty($location->custom_field3)
        && file_exists(public_path('uploads/invoice_logos/' . $location->custom_field3)))
        ? asset('uploads/invoice_logos/' . $location->custom_field3) : null;
    $business_logo = $business->logo ? asset('uploads/business_logos/' . $business->logo) : null;
    $display_logo  = $location_logo ?? ($location_has_own_rut ? null : $business_logo);

    // RUT emisor
    $emitterRut = '';
    if (!empty($cfe->emitter_rut)) $emitterRut = $cfe->emitter_rut;
    elseif ($location_has_own_rut) $emitterRut = $location->location_id;
    elseif (!empty($business->tax_number_1)) $emitterRut = $business->tax_number_1;

    // Dirección emisor
    $emitterAddress = $cfe->emitter_address ?? ($location->landmark ?? $location->name ?? '');
    $emitterCity    = $cfe->emitter_city    ?? ($location->city  ?? 'Montevideo');
    $emitterDept    = $cfe->emitter_department ?? ($location->state ?? 'Montevideo');
    $emitterPhone   = isset($location) && $location ? ($location->mobile ?? '') : '';

    /* -------- DATOS DEL RECEPTOR -------- */
    $clientName = '';
    if (!empty($cfe->receiver_name)) $clientName = $cfe->receiver_name;
    elseif (isset($customer) && $customer) {
        $clientName = $customer->name
            ?? trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''))
            ?: ($customer->supplier_business_name ?? '');
    }
    if (empty($clientName)) $clientName = 'Consumidor Final';

    $clientAddress = $cfe->receiver_address ?? ($customer->address_line_1 ?? $customer->landmark ?? '');
    $clientCity    = $cfe->receiver_city    ?? ($customer->city  ?? '');
    $clientDept    = $cfe->receiver_department ?? ($customer->state ?? '');
    $clientDoc     = $cfe->receiver_document ?? ($customer->tax_number ?? $customer->custom_field1 ?? '');
    $clientDocType = $cfe->receiver_doc_type ?? 'RUT';
    $clientCountry = 'Uruguay';

    /* -------- ITEMS -------- */
    $items = is_array($cfe->items) ? $cfe->items : json_decode($cfe->items, true) ?? [];

    /* -------- TIPO CFE -------- */
    $tipoCfe = $cfe_types[$cfe->cfe_type] ?? 'CFE';
    // "e-Factura" → "e-Factura", "e-Remito" → "e-Remito", etc.
    $tipoLabel = $tipoCfe;

    /* -------- PAGO -------- */
    $payment_methods = [1=>'Contado',2=>'Crédito',3=>'Contra Entrega',4=>'Cheque',5=>'Transferencia',6=>'Débito',7=>'Crédito',8=>'Mercado Pago',9=>'Otro'];
    $condPago = $payment_methods[$cfe->payment_method] ?? 'Contado';
@endphp

    <div class="actions no-print">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir</button>
        <a href="{{ route('cfe.print', $cfe->id) }}?format=ticket" class="btn">🎫 Ticket 80mm</a>
        <a href="{{ route('cfe.download-xml', $cfe->id) }}" class="btn btn-success">📥 XML</a>
        <a href="{{ route('cfe.show', $cfe->id) }}" class="btn">← Volver</a>
    </div>

    <div class="invoice-wrap">

        {{-- ======== CABECERA ======== --}}
        <div class="cfe-header">
            {{-- IZQUIERDA: Datos del emisor --}}
            <div class="cfe-header-left">
                @if($display_logo)
                    <img src="{{ $display_logo }}" alt="Logo" style="max-height:50px; max-width:160px; display:block; margin-bottom:5px;">
                @endif
                <div class="emisor-name">{{ $display_company_name }}</div>
                <div class="emisor-details">
                    Tel.: {{ $emitterPhone ?: '-' }}<br>
                    {{ $emitterAddress ?: '-' }}<br>
                    {{ $emitterCity }}<br>
                    {{ $emitterDept }} Sucursal:<br>
                    {{ isset($location) ? ($location->name ?? '') : '' }}
                </div>
            </div>

            {{-- DERECHA: Bloque documental estilo DGI --}}
            <div class="cfe-header-right">
                <div class="doc-box">
                    <div class="doc-box-ruc">R.U.C.<br>{{ $emitterRut }}</div>
                    <div class="doc-box-tipo">Tipo CFE</div>
                    <div class="doc-box-subtipo">{{ $tipoLabel }}</div>
                </div>

                <table class="serie-table">
                    <tr>
                        <th>Serie</th>
                        <th>Número</th>
                        <th>Moneda</th>
                    </tr>
                    <tr>
                        <td>{{ $cfe->series }}</td>
                        <td>{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $cfe->currency }}</td>
                    </tr>
                </table>

                <table class="fechas-table">
                    <tr>
                        <th>Período Facturación</th>
                        <th>Fecha de Comprobante</th>
                        <th>Fecha Vencimiento</th>
                    </tr>
                    <tr>
                        <td>{{ $cfe->issue_date->format('d-m-Y') }}</td>
                        <td>{{ $cfe->issue_date->format('d-m-Y') }}</td>
                        <td>{{ $cfe->due_date ? $cfe->due_date->format('d-m-Y') : $cfe->issue_date->format('d-m-Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- ======== RUC COMPRADOR + CLIENTE ======== --}}
        <div class="comprador-section">
            <div class="comprador-cell">
                <div class="comprador-label">RUC Comprador</div>
                <div class="comprador-value">{{ $clientDoc ?: '-' }}</div>
            </div>
            <div class="cliente-cell">
                <div class="cliente-label">Cliente</div>
                <div class="cliente-value">{{ $clientName }}</div>
            </div>
        </div>

        {{-- ======== DOMICILIO FISCAL ======== --}}
        <div class="domicilio-section">
            <span class="domicilio-label">Domicilio Fiscal</span>
            <span class="domicilio-value">{{ $clientAddress ?: '-' }}</span>
            <table class="domicilio-table">
                <tr>
                    <th>Localidad</th>
                    <th>Departamento</th>
                    <th>CP</th>
                    <th>Cód. País</th>
                    <th>País</th>
                </tr>
                <tr>
                    <td>{{ $clientCity ?: 'Montevideo' }}</td>
                    <td>{{ $clientDept ?: 'Montevideo' }}</td>
                    <td></td>
                    <td>UY</td>
                    <td>{{ $clientCountry }}</td>
                </tr>
            </table>
        </div>

        {{-- ======== TABLA DE CONCEPTOS ======== --}}
        <table class="conceptos-table">
            <thead>
                <tr>
                    <th style="width:40%; text-align:left;">CONCEPTO</th>
                    <th style="width:10%;">UNIDAD</th>
                    <th style="width:14%;">P/UNITARIO</th>
                    <th style="width:8%;">DESC.</th>
                    <th style="width:8%;">DESC. %</th>
                    <th style="width:20%;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                @php
                    $qty        = floatval($item['quantity']   ?? 1);
                    $unitPrice  = floatval($item['unit_price'] ?? 0);
                    $discPct    = floatval($item['discount']   ?? 0);
                    $discAmt    = $unitPrice * $qty * ($discPct / 100);
                    $lineTotal  = $qty * $unitPrice - $discAmt;
                @endphp
                <tr>
                    <td>{{ $item['name'] ?? $item['description'] ?? 'Producto/Servicio' }}</td>
                    <td class="text-center">{{ number_format($qty, 0) }}</td>
                    <td class="text-right">${{ number_format($unitPrice, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $discAmt > 0 ? '$'.number_format($discAmt,2,',','.') : '' }}</td>
                    <td class="text-center">{{ $discPct > 0 ? number_format($discPct,0).'%' : '' }}</td>
                    <td class="text-right">${{ number_format($lineTotal, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:12px; color:#777;">Sin ítems</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- ======== SUBTOTALES / TOTALES ======== --}}
        <div class="totales-section">
            <div class="totales-left">
                <strong>Subtot No Gravado</strong>
                &nbsp;&nbsp;&nbsp;
                <strong>Subtot IVA Susp.</strong>
                &nbsp;&nbsp;&nbsp;
                <strong>Subtot T. Básica</strong>
                &nbsp;&nbsp;&nbsp;
                <strong>Subtot T. Mínima</strong>
                &nbsp;&nbsp;&nbsp;
                <strong>IVA T. Básica</strong>
                &nbsp;&nbsp;&nbsp;
                <strong>IVA T. Mínima</strong>
                &nbsp;&nbsp;&nbsp;
                <strong style="font-size:10px; float:right; padding-right:6px;">TOTAL FACTURA</strong>
            </div>
            <div class="totales-right">
                <table class="subtotales-table">
                    <tr>
                        <td class="label-col">Subtotal:</td>
                        <td class="value-col">${{ number_format($cfe->subtotal, 2, ',', '.') }}</td>
                    </tr>
                    @if($cfe->tax_amount > 0)
                    <tr>
                        <td class="label-col">IVA:</td>
                        <td class="value-col">${{ number_format($cfe->tax_amount, 2, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label-col" style="font-weight:bold;">Total Factura:</td>
                        <td class="value-col" style="font-weight:bold;">${{ number_format($cfe->total, 2, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- ======== TOTAL A PAGAR ======== --}}
        <div class="total-pagar-box">
            <span>TOTAL A PAGAR</span>
            <span>${{ number_format($cfe->total, 2, ',', '.') }}</span>
        </div>

        @if($cfe->notes)
        <div style="border:1px solid #aaa; padding:5px 8px; margin-top:8px; font-size:10px;">
            <strong>Observaciones:</strong> {{ $cfe->notes }}
        </div>
        @endif

        {{-- ======== FOOTER DGI ======== --}}
        <div class="dgi-footer">
            <div class="dgi-footer-grid">
                <div class="dgi-footer-left">
                    Comprobante en: <strong>IVA al día</strong><br>
                    CAE nro. {{ $cfe->cae ?: '—' }}<br>
                    <br>
                    www.dgi.gub.uy<br>
                    <br>
                    Serie {{ $cfe->series }} del 0000001 al 1000000<br>
                    Cód. de Seg.: {{ $cfe->security_code ?? 'AoBVNd' }}
                </div>
                <div class="dgi-footer-right">
                    <div class="cae-box">
                        <div class="cae-title">Fecha de vencimiento</div>
                        <div class="cae-value">CAE {{ $cfe->cae_due_date ? $cfe->cae_due_date->format('d-m-Y') : '31-12-2026' }}</div>
                    </div>
                </div>
            </div>
        </div>

</body>
</html>