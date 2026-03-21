{{-- Factura A4 estilo CFE/DGI - usa $receipt_details del sistema POS --}}
<style>
    /* ---- ESTILOS A4 DGI ---- */
    .cfe-a4 * { margin: 0; padding: 0; box-sizing: border-box; }
    .cfe-a4 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        color: #000;
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

    /* Cabecera principal */
    .cfe-a4 .cfe-header { display: table; width: 100%; margin-bottom: 6px; }
    .cfe-a4 .cfe-header-left { display: table-cell; width: 45%; vertical-align: top; padding-right: 10px; }
    .cfe-a4 .cfe-header-right { display: table-cell; width: 55%; vertical-align: top; text-align: right; }
    .cfe-a4 .emisor-name { font-size: 13px; font-weight: bold; margin-bottom: 2px; }
    .cfe-a4 .emisor-details { font-size: 10px; line-height: 1.6; }

    /* Bloque RUC/Tipo */
    .cfe-a4 .doc-box { border: 1px solid #000; display: inline-block; min-width: 180px; text-align: center; margin-bottom: 4px; }
    .cfe-a4 .doc-box-ruc { font-size: 13px; font-weight: bold; padding: 3px 8px; border-bottom: 1px solid #000; }
    .cfe-a4 .doc-box-tipo { font-size: 11px; font-weight: bold; padding: 3px 8px; border-bottom: 1px solid #000; background: #f5f5f5; }
    .cfe-a4 .doc-box-subtipo { font-size: 10px; padding: 2px 8px; }

    /* Tabla Serie/Número */
    .cfe-a4 .serie-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 4px; font-size: 10px; }
    .cfe-a4 .serie-table th { background: #e0e0e0; border: 1px solid #000; padding: 2px 5px; font-weight: bold; text-align: center; }
    .cfe-a4 .serie-table td { border: 1px solid #000; padding: 2px 5px; text-align: center; }

    /* Tabla de fechas */
    .cfe-a4 .fechas-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 3px; font-size: 10px; }
    .cfe-a4 .fechas-table th { background: #e0e0e0; border: 1px solid #000; padding: 2px 5px; font-weight: bold; text-align: center; font-size: 9px; }
    .cfe-a4 .fechas-table td { border: 1px solid #000; padding: 2px 5px; text-align: center; }

    /* Sección comprador/cliente */
    .cfe-a4 .comprador-section { display: table; width: 100%; border-collapse: collapse; border: 1px solid #000; margin: 6px 0; }
    .cfe-a4 .comprador-cell { display: table-cell; width: 50%; vertical-align: top; padding: 4px 6px; border-right: 1px solid #000; }
    .cfe-a4 .cliente-cell { display: table-cell; width: 50%; vertical-align: top; padding: 4px 6px; }
    .cfe-a4 .comprador-label { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #444; margin-bottom: 1px; }
    .cfe-a4 .comprador-value { font-size: 12px; font-weight: bold; }
    .cfe-a4 .cliente-label { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #444; margin-bottom: 1px; }
    .cfe-a4 .cliente-value { font-size: 11px; font-weight: bold; }

    /* Domicilio fiscal */
    .cfe-a4 .domicilio-section { border: 1px solid #000; border-top: none; padding: 3px 6px; margin-bottom: 6px; }
    .cfe-a4 .domicilio-label { font-size: 9px; font-weight: bold; color: #444; display: inline; }
    .cfe-a4 .domicilio-value { font-size: 10px; display: inline; margin-left: 4px; }

    /* Tabla de conceptos */
    .cfe-a4 .conceptos-table { width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 10px; margin-bottom: 0; }
    .cfe-a4 .conceptos-table thead tr { background: #d0d0d0; }
    .cfe-a4 .conceptos-table th { border: 1px solid #000; padding: 3px 5px; font-weight: bold; text-align: center; font-size: 9px; text-transform: uppercase; }
    .cfe-a4 .conceptos-table td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
    .cfe-a4 .conceptos-table td.text-center { text-align: center; }
    .cfe-a4 .conceptos-table td.text-right { text-align: right; }

    /* Totales */
    .cfe-a4 .totales-section { border: 1px solid #000; border-top: none; display: table; width: 100%; }
    .cfe-a4 .totales-left { display: table-cell; width: 55%; border-right: 1px solid #000; padding: 3px 5px; font-size: 9px; vertical-align: middle; }
    .cfe-a4 .totales-right { display: table-cell; width: 45%; text-align: right; }
    .cfe-a4 .subtotales-table { width: 100%; border-collapse: collapse; font-size: 9px; }
    .cfe-a4 .subtotales-table td { padding: 2px 5px; border-bottom: 1px solid #eee; }
    .cfe-a4 .subtotales-table tr:last-child td { border-bottom: none; }
    .cfe-a4 .subtotales-table td.label-col { color: #555; }
    .cfe-a4 .subtotales-table td.value-col { text-align: right; font-weight: bold; }

    /* Total a pagar */
    .cfe-a4 .total-pagar-box { border: 2px solid #000; text-align: right; padding: 4px 8px; margin-top: 6px; display: flex; justify-content: space-between; font-size: 12px; font-weight: bold; }

    /* Pagos */
    .cfe-a4 .pagos-section { margin-top: 10px; border: 1px solid #000; }
    .cfe-a4 .pagos-title { background: #e0e0e0; padding: 3px 6px; font-weight: bold; font-size: 10px; border-bottom: 1px solid #000; }
    .cfe-a4 .pagos-table { width: 100%; border-collapse: collapse; font-size: 10px; }
    .cfe-a4 .pagos-table td { padding: 2px 6px; border-bottom: 1px solid #eee; }
    .cfe-a4 .pagos-table tr:last-child td { border-bottom: none; }

    /* Footer DGI */
    .cfe-a4 .dgi-footer { margin-top: 20px; border-top: 1px solid #aaa; padding-top: 6px; font-size: 9px; color: #333; }
    .cfe-a4 .dgi-footer-grid { display: table; width: 100%; }
    .cfe-a4 .dgi-footer-left { display: table-cell; vertical-align: top; width: 65%; line-height: 1.7; }
    .cfe-a4 .dgi-footer-right { display: table-cell; vertical-align: bottom; width: 35%; text-align: right; }

    /* Barcode/QR */
    .cfe-a4 .barcode-section { text-align: center; margin-top: 10px; }
</style>

<div class="cfe-a4">

    {{-- ======== CABECERA ======== --}}
    <div class="cfe-header">
        {{-- IZQUIERDA: Datos del emisor --}}
        <div class="cfe-header-left">
            @if(!empty($receipt_details->logo))
                <img src="{{ $receipt_details->logo }}" alt="Logo" style="max-height:50px; max-width:160px; display:block; margin-bottom:5px;">
            @endif
            <div class="emisor-name">{{ $receipt_details->display_name ?? $receipt_details->business_name ?? '' }}</div>
            <div class="emisor-details">
                @if(!empty($receipt_details->contact))
                    {!! $receipt_details->contact !!}<br>
                @endif
                @if(!empty($receipt_details->address))
                    {!! $receipt_details->address !!}<br>
                @endif
                @if(!empty($receipt_details->location_name))
                    Sucursal: {{ $receipt_details->location_name }}
                @endif
            </div>
        </div>

        {{-- DERECHA: Bloque documental estilo DGI --}}
        <div class="cfe-header-right">
            <div class="doc-box">
                <div class="doc-box-ruc">
                    @if(!empty($receipt_details->tax_info1))
                        {{ $receipt_details->tax_label1 ?? 'R.U.C.' }}<br>{{ $receipt_details->tax_info1 }}
                    @else
                        R.U.C.<br>-
                    @endif
                </div>
                <div class="doc-box-tipo">Comprobante</div>
                <div class="doc-box-subtipo">
                    @if(!empty($receipt_details->invoice_heading))
                        {!! $receipt_details->invoice_heading !!}
                    @else
                        Factura de Venta
                    @endif
                </div>
            </div>

            <table class="serie-table">
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
            <table class="fechas-table">
                <tr>
                    <th>Fecha de Comprobante</th>
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

    {{-- ======== RUC COMPRADOR + CLIENTE ======== --}}
    <div class="comprador-section">
        <div class="comprador-cell">
            <div class="comprador-label">{{ $receipt_details->customer_tax_label ?: 'RUC' }} Comprador</div>
            <div class="comprador-value">{{ $receipt_details->customer_tax_number ?: '-' }}</div>
        </div>
        <div class="cliente-cell">
            <div class="cliente-label">Cliente</div>
            <div class="cliente-value">{{ $receipt_details->customer_name ?? $receipt_details->contact_name ?? 'Consumidor Final' }}</div>
        </div>
    </div>

    {{-- ======== DOMICILIO FISCAL ======== --}}
    @if(!empty($receipt_details->customer_info))
    <div class="domicilio-section">
        <span class="domicilio-label">Domicilio / Contacto:</span>
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
    <div style="font-size:10px; margin-bottom:6px; padding:2px 0;">
        <strong>Vendedor:</strong> {{ $receipt_details->sales_person }}
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
                <th style="width:{{ $has_discount ? '35%' : '45%' }}; text-align:left;">{{ $receipt_details->table_product_label ?? 'CONCEPTO' }}</th>
                <th style="width:10%;">{{ $receipt_details->table_qty_label ?? 'CANT' }}</th>
                <th style="width:15%;">{{ $receipt_details->table_unit_price_label ?? 'P/UNITARIO' }}</th>
                @if($has_discount)
                <th style="width:10%;">DESC.</th>
                @endif
                <th style="width:20%;">{{ $receipt_details->table_subtotal_label ?? 'TOTAL' }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receipt_details->lines as $line)
            <tr>
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
                <td colspan="{{ $has_discount ? 5 : 4 }}" style="text-align:center; padding:12px; color:#777;">Sin ítems</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ======== SUBTOTALES / TOTALES ======== --}}
    <div class="totales-section">
        <div class="totales-left">
            @if(!empty($receipt_details->taxes))
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
                <tr>
                    <td class="label-col" style="font-weight:bold;">Total Factura:</td>
                    <td class="value-col" style="font-weight:bold;">{{ $receipt_details->total }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ======== TOTAL A PAGAR ======== --}}
    <div class="total-pagar-box">
        <span>{!! $receipt_details->total_label ?? 'TOTAL A PAGAR' !!}</span>
        <span>{{ $receipt_details->total }}</span>
    </div>

    {{-- ======== PAGOS ======== --}}
    @if(!empty($receipt_details->payments))
    <div class="pagos-section">
        <div class="pagos-title">FORMA DE PAGO</div>
        <table class="pagos-table">
            @foreach($receipt_details->payments as $payment)
            <tr>
                <td style="width:50%">{{ $payment['method'] }}</td>
                <td style="width:25%; text-align:right;">{{ $payment['amount'] }}</td>
                <td style="width:25%; text-align:right;">{{ $payment['date'] ?? '' }}</td>
            </tr>
            @endforeach
            @if(!empty($receipt_details->total_paid))
            <tr style="font-weight:bold; border-top:1px solid #000;">
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

    {{-- ======== NOTAS ADICIONALES ======== --}}
    @if(!empty($receipt_details->additional_notes))
    <div style="border:1px solid #aaa; padding:5px 8px; margin-top:8px; font-size:10px;">
        <strong>Observaciones:</strong> {!! nl2br($receipt_details->additional_notes) !!}
    </div>
    @endif

    {{-- ======== FOOTER ======== --}}
    <div class="dgi-footer">
        <div class="dgi-footer-grid">
            <div class="dgi-footer-left">
                @if(!empty($receipt_details->tax_info1))
                    {{ $receipt_details->tax_label1 ?? 'RUT' }} {{ $receipt_details->tax_info1 }}<br>
                @endif
                @if(!empty($receipt_details->footer_text))
                    {!! $receipt_details->footer_text !!}<br>
                @endif
                <br>
                Documento generado electrónicamente<br>
                {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
            </div>
            <div class="dgi-footer-right">
                @if(!empty($receipt_details->show_barcode))
                <div class="barcode-section">
                    <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, array(39, 48, 54), true)}}">
                </div>
                @endif
                @if(!empty($receipt_details->show_qr_code) && !empty($receipt_details->qr_code_text))
                <div class="barcode-section" style="margin-top:5px;">
                    <img src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54])}}">
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
