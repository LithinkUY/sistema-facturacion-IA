{{-- Factura A4 estilo CFE/DGI oficial - usa $receipt_details del sistema POS --}}
<style>
    /* ======== ESTILOS FACTURA DGI URUGUAY - RECEIPT POS ======== */
    .cfe-a4 * { margin: 0; padding: 0; box-sizing: border-box; }
    .cfe-a4 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        color: #222;
        background: #fff;
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        padding: 8mm 10mm;
        position: relative;
    }
    @media print {
        body { background: #fff; margin: 0; }
        .cfe-a4 { width: 100%; margin: 0; box-shadow: none; border: none; }
        @page { size: A4; margin: 8mm; }
    }

    /* ---- CABECERA PRINCIPAL ---- */
    .cfe-a4 .cfe-header {
        display: table;
        width: 100%;
        margin-bottom: 8px;
        border-bottom: 3px solid #003366;
        padding-bottom: 8px;
    }
    .cfe-a4 .cfe-header-left {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        padding-right: 12px;
    }
    .cfe-a4 .cfe-header-right {
        display: table-cell;
        width: 50%;
        vertical-align: top;
    }

    /* Logo y emisor */
    .cfe-a4 .emisor-name {
        font-size: 16px;
        font-weight: bold;
        color: #003366;
        margin-bottom: 3px;
    }
    .cfe-a4 .emisor-details {
        font-size: 9.5px;
        line-height: 1.7;
        color: #444;
    }

    /* Bloque RUC/Tipo CFE */
    .cfe-a4 .doc-box {
        border: 2px solid #003366;
        display: block;
        width: 100%;
        text-align: center;
        margin-bottom: 6px;
    }
    .cfe-a4 .doc-box-ruc {
        font-size: 13px;
        font-weight: bold;
        padding: 5px 8px;
        background: #003366;
        color: #fff;
    }
    .cfe-a4 .doc-box-tipo {
        font-size: 12px;
        font-weight: bold;
        padding: 4px 8px;
        background: #e8f0fa;
        color: #003366;
        border-top: 1px solid #003366;
        border-bottom: 1px solid #003366;
    }
    .cfe-a4 .doc-box-subtipo {
        font-size: 10px;
        padding: 3px 8px;
        color: #333;
    }

    /* Tablas info */
    .cfe-a4 .info-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #999;
        font-size: 10px;
        margin-top: 5px;
    }
    .cfe-a4 .info-table th {
        background: #003366;
        color: #fff;
        border: 1px solid #003366;
        padding: 3px 6px;
        font-weight: bold;
        text-align: center;
        font-size: 9px;
        text-transform: uppercase;
    }
    .cfe-a4 .info-table td {
        border: 1px solid #999;
        padding: 3px 6px;
        text-align: center;
        font-weight: bold;
    }

    /* ---- RECEPTOR ---- */
    .cfe-a4 .receptor-section {
        border: 1px solid #999;
        margin: 8px 0 0 0;
    }
    .cfe-a4 .receptor-title {
        background: #003366;
        color: #fff;
        padding: 3px 8px;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .cfe-a4 .receptor-grid {
        display: table;
        width: 100%;
    }
    .cfe-a4 .receptor-cell {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        padding: 5px 8px;
        border-right: 1px solid #ddd;
    }
    .cfe-a4 .receptor-cell:last-child {
        border-right: none;
    }
    .cfe-a4 .receptor-label {
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
        color: #666;
        margin-bottom: 1px;
    }
    .cfe-a4 .receptor-value {
        font-size: 11px;
        font-weight: bold;
        color: #222;
    }

    /* Domicilio */
    .cfe-a4 .domicilio-section {
        border: 1px solid #999;
        border-top: none;
        padding: 4px 8px;
    }
    .cfe-a4 .domicilio-label {
        font-size: 8px;
        font-weight: bold;
        color: #666;
        text-transform: uppercase;
        display: inline;
    }
    .cfe-a4 .domicilio-value {
        font-size: 9.5px;
        display: inline;
        margin-left: 4px;
    }

    /* ---- TABLA CONCEPTOS ---- */
    .cfe-a4 .conceptos-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #999;
        font-size: 10px;
        margin-top: 8px;
        margin-bottom: 0;
    }
    .cfe-a4 .conceptos-table thead tr {
        background: #003366;
    }
    .cfe-a4 .conceptos-table th {
        border: 1px solid #003366;
        padding: 4px 6px;
        font-weight: bold;
        text-align: center;
        font-size: 9px;
        text-transform: uppercase;
        color: #fff;
    }
    .cfe-a4 .conceptos-table tbody tr:nth-child(even) {
        background: #e8f0fa;
    }
    .cfe-a4 .conceptos-table td {
        border: 1px solid #ccc;
        padding: 4px 6px;
        vertical-align: top;
    }
    .cfe-a4 .conceptos-table td.text-center { text-align: center; }
    .cfe-a4 .conceptos-table td.text-right { text-align: right; }

    /* ---- TOTALES ---- */
    .cfe-a4 .totales-section {
        border: 1px solid #999;
        border-top: none;
        display: table;
        width: 100%;
    }
    .cfe-a4 .totales-left {
        display: table-cell;
        width: 55%;
        border-right: 1px solid #999;
        padding: 4px 6px;
        font-size: 9px;
        vertical-align: middle;
    }
    .cfe-a4 .totales-right {
        display: table-cell;
        width: 45%;
    }
    .cfe-a4 .subtotales-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.5px;
    }
    .cfe-a4 .subtotales-table td {
        padding: 3px 8px;
        border-bottom: 1px solid #eee;
    }
    .cfe-a4 .subtotales-table tr:last-child td {
        border-bottom: none;
    }
    .cfe-a4 .subtotales-table td.label-col {
        color: #666;
        font-size: 9px;
    }
    .cfe-a4 .subtotales-table td.value-col {
        text-align: right;
        font-weight: bold;
    }

    /* Total a pagar */
    .cfe-a4 .total-pagar-box {
        border: 2px solid #003366;
        margin-top: 8px;
        padding: 6px 10px;
        display: table;
        width: 100%;
        background: #e8f0fa;
    }
    .cfe-a4 .total-pagar-label {
        display: table-cell;
        font-size: 13px;
        font-weight: bold;
        color: #003366;
        vertical-align: middle;
    }
    .cfe-a4 .total-pagar-value {
        display: table-cell;
        text-align: right;
        font-size: 16px;
        font-weight: bold;
        color: #003366;
    }

    /* Pagos */
    .cfe-a4 .pagos-section {
        margin-top: 8px;
        border: 1px solid #999;
    }
    .cfe-a4 .pagos-title {
        background: #003366;
        color: #fff;
        padding: 3px 8px;
        font-weight: bold;
        font-size: 10px;
        text-transform: uppercase;
    }
    .cfe-a4 .pagos-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }
    .cfe-a4 .pagos-table td {
        padding: 3px 8px;
        border-bottom: 1px solid #eee;
    }
    .cfe-a4 .pagos-table tr:last-child td {
        border-bottom: none;
    }

    /* Observaciones */
    .cfe-a4 .observaciones {
        border: 1px solid #ddd;
        padding: 5px 8px;
        margin-top: 6px;
        font-size: 9.5px;
    }
    .cfe-a4 .observaciones strong {
        color: #003366;
    }

    /* ---- FOOTER DGI ---- */
    .cfe-a4 .dgi-footer {
        margin-top: 16px;
        border-top: 3px solid #003366;
        padding-top: 8px;
        font-size: 9px;
        color: #444;
    }
    .cfe-a4 .dgi-footer-grid {
        display: table;
        width: 100%;
    }
    .cfe-a4 .dgi-footer-left {
        display: table-cell;
        vertical-align: top;
        width: 60%;
        line-height: 1.8;
    }
    .cfe-a4 .dgi-footer-right {
        display: table-cell;
        vertical-align: top;
        width: 40%;
        text-align: center;
    }

    /* QR & Barcode */
    .cfe-a4 .qr-section {
        margin-top: 6px;
        text-align: center;
    }
    .cfe-a4 .qr-label {
        font-size: 7.5px;
        color: #666;
        margin-top: 3px;
    }
    .cfe-a4 .barcode-section {
        text-align: center;
        margin-top: 6px;
    }

    /* Sello DGI */
    .cfe-a4 .dgi-sello {
        margin-top: 8px;
        padding: 4px 8px;
        background: #e8f0fa;
        border: 1px solid #003366;
        font-size: 8px;
        text-align: center;
        color: #003366;
        font-weight: bold;
    }
</style>

<div class="cfe-a4">

    {{-- ======== CABECERA ======== --}}
    <div class="cfe-header">
        {{-- IZQUIERDA: Datos del emisor --}}
        <div class="cfe-header-left">
            @if(!empty($receipt_details->logo))
                <img src="{{ $receipt_details->logo }}" alt="Logo" style="max-height:55px; max-width:170px; display:block; margin-bottom:6px;">
            @endif
            <div class="emisor-name">{{ $receipt_details->display_name ?? $receipt_details->business_name ?? '' }}</div>
            <div class="emisor-details">
                @if(!empty($receipt_details->address))
                    {!! $receipt_details->address !!}<br>
                @endif
                @if(!empty($receipt_details->contact))
                    {!! $receipt_details->contact !!}<br>
                @endif
                @if(!empty($receipt_details->location_name))
                    <strong>Sucursal:</strong> {{ $receipt_details->location_name }}
                @endif
            </div>
        </div>

        {{-- DERECHA: Bloque documental DGI --}}
        <div class="cfe-header-right">
            <div class="doc-box">
                <div class="doc-box-ruc">
                    {{ $receipt_details->tax_label1 ?? 'R.U.C.' }} {{ $receipt_details->tax_info1 ?? '-' }}
                </div>
                <div class="doc-box-tipo">
                    @if(!empty($receipt_details->invoice_heading))
                        {!! $receipt_details->invoice_heading !!}
                    @else
                        Factura de Venta
                    @endif
                </div>
                <div class="doc-box-subtipo">Comprobante Fiscal Electrónico</div>
            </div>

            <table class="info-table">
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                </tr>
                <tr>
                    <td>
                        @if(!empty($receipt_details->invoice_no_prefix)){!! $receipt_details->invoice_no_prefix !!}@endif
                        {{ $receipt_details->invoice_no }}
                    </td>
                    <td>{{ $receipt_details->invoice_date ?? '' }}</td>
                </tr>
            </table>

            @if(!empty($receipt_details->due_date))
            <table class="info-table" style="margin-top:3px;">
                <tr>
                    <th>Fecha Emisión</th>
                    <th>Fecha Vencimiento</th>
                </tr>
                <tr>
                    <td>{{ $receipt_details->invoice_date ?? '' }}</td>
                    <td>{{ $receipt_details->due_date ?? '' }}</td>
                </tr>
            </table>
            @endif
        </div>
    </div>

    {{-- ======== DATOS DEL RECEPTOR / COMPRADOR ======== --}}
    <div class="receptor-section">
        <div class="receptor-title">Datos del Receptor</div>
        <div class="receptor-grid">
            <div class="receptor-cell">
                <div class="receptor-label">{{ $receipt_details->customer_tax_label ?: 'RUC' }} Comprador</div>
                <div class="receptor-value">{{ $receipt_details->customer_tax_number ?: '—' }}</div>
            </div>
            <div class="receptor-cell">
                <div class="receptor-label">Razón Social / Nombre</div>
                <div class="receptor-value">{{ $receipt_details->customer_name ?? $receipt_details->contact_name ?? 'Consumidor Final' }}</div>
            </div>
        </div>
    </div>

    {{-- ======== DOMICILIO FISCAL ======== --}}
    @if(!empty($receipt_details->customer_info))
    <div class="domicilio-section">
        <span class="domicilio-label">Dirección:</span>
        <span class="domicilio-value">{!! $receipt_details->customer_info !!}</span>
    </div>
    @endif

    @if(!empty($receipt_details->customer_custom_fields))
    <div class="domicilio-section" style="border-top:none;">
        <span class="domicilio-label">Datos adicionales:</span>
        <span class="domicilio-value">{!! $receipt_details->customer_custom_fields !!}</span>
    </div>
    @endif

    {{-- ======== VENDEDOR ======== --}}
    @if(!empty($receipt_details->sales_person))
    <div style="font-size:10px; margin:6px 0; padding:3px 8px; border:1px solid #ddd;">
        <strong style="color:#003366;">Vendedor:</strong> {{ $receipt_details->sales_person }}
    </div>
    @endif

    {{-- ======== TABLA DE CONCEPTOS ======== --}}
    @php
        $has_discount = false;
        foreach($receipt_details->lines as $line) {
            if (!empty($line['total_line_discount']) && $line['total_line_discount'] != '0.00') {
                $has_discount = true;
                break;
            }
        }
    @endphp
    <table class="conceptos-table">
        <thead>
            <tr>
                <th style="width:5%;">Nro</th>
                <th style="width:{{ $has_discount ? '30%' : '40%' }}; text-align:left;">{{ $receipt_details->table_product_label ?? 'DESCRIPCIÓN' }}</th>
                <th style="width:10%;">{{ $receipt_details->table_qty_label ?? 'CANT' }}</th>
                <th style="width:15%;">{{ $receipt_details->table_unit_price_label ?? 'P/UNITARIO' }}</th>
                @if($has_discount)
                <th style="width:12%;">DESC.</th>
                @endif
                <th style="width:18%;">{{ $receipt_details->table_subtotal_label ?? 'TOTAL' }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receipt_details->lines as $index => $line)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ $line['name'] }} {{ $line['product_variation'] ?? '' }} {{ $line['variation'] ?? '' }}
                    @if(!empty($line['sub_sku'])) <small>({{ $line['sub_sku'] }})</small> @endif
                    @if(!empty($line['brand'])) <small>- {{ $line['brand'] }}</small> @endif
                    @if(!empty($line['sell_line_note'])) <br><small>{!! $line['sell_line_note'] !!}</small> @endif
                </td>
                <td class="text-center">{{ $line['quantity'] }} {{ $line['units'] ?? '' }}</td>
                <td class="text-right">{{ $line['unit_price_before_discount'] ?? $line['unit_price_inc_tax'] ?? '' }}</td>
                @if($has_discount)
                <td class="text-right">
                    @if(!empty($line['total_line_discount']) && $line['total_line_discount'] != '0.00')
                        {{ $line['total_line_discount'] }}
                        @if(!empty($line['line_discount_percent']))
                            ({{ $line['line_discount_percent'] }}%)
                        @endif
                    @endif
                </td>
                @endif
                <td class="text-right">{{ $line['line_total'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $has_discount ? 6 : 5 }}" style="text-align:center; padding:14px; color:#999;">Sin ítems</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ======== SUBTOTALES / TOTALES ======== --}}
    <div class="totales-section">
        <div class="totales-left">
            @if(!empty($receipt_details->taxes))
                <div style="margin-bottom:2px; font-weight:bold; color:#003366; font-size:9px; text-transform:uppercase;">Desglose Impositivo</div>
                @foreach($receipt_details->taxes as $key => $val)
                    <strong>{{ $key }}:</strong> {{ $val }} &nbsp;&nbsp;
                @endforeach
            @endif
        </div>
        <div class="totales-right">
            <table class="subtotales-table">
                @if(!empty($receipt_details->total_quantity_label))
                <tr>
                    <td class="label-col">{!! $receipt_details->total_quantity_label !!}</td>
                    <td class="value-col">{{ $receipt_details->total_quantity }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label-col">{!! $receipt_details->subtotal_label ?? 'Subtotal:' !!}</td>
                    <td class="value-col">{{ $receipt_details->subtotal }}</td>
                </tr>
                @if(!empty($receipt_details->discount))
                <tr>
                    <td class="label-col">{!! $receipt_details->discount_label ?? 'Descuento:' !!}</td>
                    <td class="value-col">(-) {{ $receipt_details->discount }}</td>
                </tr>
                @endif
                @if(!empty($receipt_details->total_line_discount))
                <tr>
                    <td class="label-col">{!! $receipt_details->line_discount_label ?? 'Desc. Línea:' !!}</td>
                    <td class="value-col">(-) {{ $receipt_details->total_line_discount }}</td>
                </tr>
                @endif
                @if(!empty($receipt_details->shipping_charges))
                <tr>
                    <td class="label-col">{!! $receipt_details->shipping_charges_label ?? 'Envío:' !!}</td>
                    <td class="value-col">(+) {{ $receipt_details->shipping_charges }}</td>
                </tr>
                @endif
                @if(!empty($receipt_details->tax))
                <tr>
                    <td class="label-col">{!! $receipt_details->tax_label ?? 'Impuesto:' !!}</td>
                    <td class="value-col">(+) {{ $receipt_details->tax }}</td>
                </tr>
                @endif
                <tr style="border-top: 1px solid #999;">
                    <td class="label-col" style="font-weight:bold; font-size:10px; color:#003366;">Total:</td>
                    <td class="value-col" style="font-size:11px; color:#003366;">{{ $receipt_details->total }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ======== TOTAL A PAGAR ======== --}}
    <div class="total-pagar-box">
        <span class="total-pagar-label">{!! $receipt_details->total_label ?? 'TOTAL A PAGAR' !!}</span>
        <span class="total-pagar-value">{{ $receipt_details->total }}</span>
    </div>

    {{-- ======== PAGOS ======== --}}
    @if(!empty($receipt_details->payments))
    <div class="pagos-section">
        <div class="pagos-title">Forma de Pago</div>
        <table class="pagos-table">
            @foreach($receipt_details->payments as $payment)
            <tr>
                <td style="width:50%">{{ $payment['method'] }}</td>
                <td style="width:25%; text-align:right;">{{ $payment['amount'] }}</td>
                <td style="width:25%; text-align:right;">{{ $payment['date'] ?? '' }}</td>
            </tr>
            @endforeach
            @if(!empty($receipt_details->total_paid))
            <tr style="font-weight:bold; border-top:1px solid #003366;">
                <td>{!! $receipt_details->total_paid_label ?? 'Total Pagado' !!}</td>
                <td style="text-align:right;" colspan="2">{{ $receipt_details->total_paid }}</td>
            </tr>
            @endif
            @if(!empty($receipt_details->total_due) && $receipt_details->total_due != 0)
            <tr style="font-weight:bold;">
                <td>{!! $receipt_details->total_due_label ?? 'Cambio/Deuda' !!}</td>
                <td style="text-align:right;" colspan="2">{{ $receipt_details->total_due }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    {{-- ======== OBSERVACIONES ======== --}}
    @if(!empty($receipt_details->additional_notes))
    <div class="observaciones">
        <strong>Observaciones:</strong> {!! nl2br($receipt_details->additional_notes) !!}
    </div>
    @endif

    {{-- ======== FOOTER DGI CON QR ======== --}}
    <div class="dgi-footer">
        <div class="dgi-footer-grid">
            {{-- IZQUIERDA: Datos fiscales --}}
            <div class="dgi-footer-left">
                @if(!empty($receipt_details->tax_info1))
                    <strong>{{ $receipt_details->tax_label1 ?? 'RUT' }}</strong> {{ $receipt_details->tax_info1 }}<br>
                @endif
                @if(!empty($receipt_details->footer_text))
                    {!! $receipt_details->footer_text !!}<br>
                @endif
                <br>
                Comprobante autorizado por DGI — <strong>IVA al día</strong><br>
                Consulte validez: <strong>www.efactura.dgi.gub.uy</strong><br>
                <br>
                <span style="font-size:8px; color:#888;">
                    Documento generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
                </span>
            </div>

            {{-- DERECHA: Barcode + QR --}}
            <div class="dgi-footer-right">
                @if(!empty($receipt_details->show_barcode))
                <div class="barcode-section">
                    <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, array(0, 51, 102), true)}}">
                </div>
                @endif

                @if(!empty($receipt_details->show_qr_code) && !empty($receipt_details->qr_code_text))
                <div class="qr-section">
                    <img src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 4, 4, [0, 51, 102])}}" style="width:100px; height:100px;">
                    <div class="qr-label">Escanee para verificar</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Sello DGI --}}
        <div class="dgi-sello">
            COMPROBANTE FISCAL ELECTRÓNICO — D.G.I. — DIRECCIÓN GENERAL IMPOSITIVA — URUGUAY
        </div>
    </div>

</div>
