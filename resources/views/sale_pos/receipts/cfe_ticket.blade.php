{{-- Ticket 80mm estilo CFE - usa $receipt_details del sistema POS --}}
<style>
    /* Reset para ticket */
    .cfe-ticket * { margin: 0; padding: 0; box-sizing: border-box; }
    .cfe-ticket {
        font-family: 'Courier New', Courier, monospace;
        font-size: 12px;
        line-height: 1.4;
        color: #000;
        background: #fff;
        width: 80mm;
        max-width: 80mm;
        margin: 0 auto;
        padding: 5mm;
    }
    @media print {
        body { width: 80mm; margin: 0; padding: 0; }
        .cfe-ticket { width: 100%; padding: 2mm; }
    }
    .cfe-ticket .header { text-align: center; margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
    .cfe-ticket .header .logo { max-width: 60mm; max-height: 20mm; margin-bottom: 5px; }
    .cfe-ticket .company-name { font-size: 16px; font-weight: bold; margin-bottom: 3px; }
    .cfe-ticket .company-info { font-size: 10px; }
    .cfe-ticket .doc-type { text-align: center; font-size: 14px; font-weight: bold; padding: 5px; margin: 10px 0; border: 2px solid #000; background: #f0f0f0; }
    .cfe-ticket .doc-info { margin: 10px 0; border-bottom: 1px dashed #000; padding-bottom: 10px; }
    .cfe-ticket .doc-info table { width: 100%; font-size: 11px; }
    .cfe-ticket .doc-info td { padding: 2px 0; }
    .cfe-ticket .doc-info .label { font-weight: bold; width: 40%; }
    .cfe-ticket .customer { margin: 10px 0; border-bottom: 1px dashed #000; padding-bottom: 10px; }
    .cfe-ticket .customer-title { font-weight: bold; font-size: 11px; margin-bottom: 5px; }
    .cfe-ticket .items { margin: 10px 0; }
    .cfe-ticket .items-header { font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 5px; font-size: 10px; display: flex; justify-content: space-between; }
    .cfe-ticket .item-row { display: flex; justify-content: space-between; font-size: 11px; padding: 3px 0; border-bottom: 1px dotted #ccc; }
    .cfe-ticket .item-desc { flex: 1; padding-right: 5px; }
    .cfe-ticket .item-qty { width: 15%; text-align: center; }
    .cfe-ticket .item-price { width: 25%; text-align: right; }
    .cfe-ticket .item-total { width: 25%; text-align: right; font-weight: bold; }
    .cfe-ticket .totals { margin: 15px 0; border-top: 2px solid #000; padding-top: 10px; }
    .cfe-ticket .total-row { display: flex; justify-content: space-between; font-size: 11px; padding: 2px 0; }
    .cfe-ticket .total-row.grand-total { font-size: 14px; font-weight: bold; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px; }
    .cfe-ticket .fiscal-info { margin: 15px 0; padding: 10px; border: 1px solid #000; background: #f9f9f9; font-size: 10px; }
    .cfe-ticket .fiscal-title { font-weight: bold; text-align: center; margin-bottom: 5px; font-size: 11px; }
    .cfe-ticket .payment-section { margin: 10px 0; border-bottom: 1px dashed #000; padding-bottom: 10px; }
    .cfe-ticket .payment-title { font-weight: bold; font-size: 11px; margin-bottom: 5px; }
    .cfe-ticket .payment-row { display: flex; justify-content: space-between; font-size: 11px; padding: 2px 0; }
    .cfe-ticket .footer { text-align: center; margin-top: 15px; padding-top: 10px; border-top: 1px dashed #000; font-size: 10px; }
    .cfe-ticket .footer .thanks { font-size: 12px; font-weight: bold; margin-bottom: 5px; }
    .cfe-ticket .cut-line { border-top: 1px dashed #000; margin: 10px 0; position: relative; }
    .cfe-ticket .cut-line::before { content: '✂'; position: absolute; left: -5px; top: -8px; font-size: 12px; }
    .cfe-ticket .qr-section { text-align: center; margin: 10px 0; padding: 10px; border: 1px dashed #000; }
    .cfe-ticket .qr-section img { max-width: 40mm; height: auto; }
    .cfe-ticket .qr-text { font-size: 9px; margin-top: 5px; }
</style>

<div class="cfe-ticket">
    {{-- ======== ENCABEZADO ======== --}}
    <div class="header">
        @if(!empty($receipt_details->logo))
            <img src="{{ $receipt_details->logo }}" alt="Logo" class="logo">
        @endif
        <div class="company-name">{{ $receipt_details->display_name ?? $receipt_details->business_name ?? '' }}</div>
        <div class="company-info">
            @if(!empty($receipt_details->tax_info1))
                {{ $receipt_details->tax_label1 ?? 'RUT:' }} {{ $receipt_details->tax_info1 }}<br>
            @endif
            @if(!empty($receipt_details->address))
                {!! $receipt_details->address !!}<br>
            @endif
            @if(!empty($receipt_details->contact))
                {!! $receipt_details->contact !!}
            @endif
        </div>
    </div>

    {{-- ======== TIPO DE DOCUMENTO ======== --}}
    <div class="doc-type">
        @if(!empty($receipt_details->invoice_heading))
            {!! $receipt_details->invoice_heading !!}
        @else
            COMPROBANTE DE VENTA
        @endif
    </div>

    {{-- ======== INFORMACIÓN DEL DOCUMENTO ======== --}}
    <div class="doc-info">
        <table>
            <tr>
                <td class="label">Número:</td>
                <td>
                    @if(!empty($receipt_details->invoice_no_prefix))
                        {!! $receipt_details->invoice_no_prefix !!}
                    @endif
                    {{ $receipt_details->invoice_no }}
                </td>
            </tr>
            <tr>
                <td class="label">Fecha Emisión:</td>
                <td>{{ $receipt_details->invoice_date ?? '' }}</td>
            </tr>
            @if(!empty($receipt_details->due_date))
            <tr>
                <td class="label">Fecha Venc.:</td>
                <td>{{ $receipt_details->due_date }}</td>
            </tr>
            @endif
            @if(!empty($receipt_details->sales_person))
            <tr>
                <td class="label">Vendedor:</td>
                <td>{{ $receipt_details->sales_person }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- ======== DATOS DEL CLIENTE ======== --}}
    <div class="customer">
        <div class="customer-title">DATOS DEL CLIENTE</div>
        <table style="width: 100%; font-size: 11px;">
            @if(!empty($receipt_details->customer_tax_number))
            <tr>
                <td class="label">{{ $receipt_details->customer_tax_label ?: 'RUT' }}:</td>
                <td>{{ $receipt_details->customer_tax_number }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Nombre:</td>
                <td>{{ $receipt_details->customer_name ?? $receipt_details->contact_name ?? 'Consumidor Final' }}</td>
            </tr>
            @if(!empty($receipt_details->customer_info))
            <tr>
                <td class="label">Datos:</td>
                <td>{!! $receipt_details->customer_info !!}</td>
            </tr>
            @endif
            @if(!empty($receipt_details->customer_custom_fields))
            <tr>
                <td class="label">Otros:</td>
                <td>{!! $receipt_details->customer_custom_fields !!}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- ======== DETALLE DE ITEMS ======== --}}
    <div class="items">
        <div class="items-header">
            <span style="flex:1">DESCRIPCIÓN</span>
            <span style="width:15%; text-align:center">CANT</span>
            <span style="width:25%; text-align:right">P.UNIT</span>
            <span style="width:25%; text-align:right">TOTAL</span>
        </div>

        @forelse($receipt_details->lines as $line)
        <div class="item-row">
            <div class="item-desc">
                {{ $line['name'] }} {{ $line['product_variation'] ?? '' }} {{ $line['variation'] ?? '' }}
                @if(!empty($line['sub_sku'])) <small>({{ $line['sub_sku'] }})</small> @endif
            </div>
            <div class="item-qty">{{ $line['quantity'] }}</div>
            <div class="item-price">{{ $line['unit_price_before_discount'] ?? $line['unit_price_inc_tax'] ?? '' }}</div>
            <div class="item-total">{{ $line['line_total'] }}</div>
        </div>
        @empty
        <div class="item-row">
            <div class="item-desc" style="text-align:center; width:100%;">Sin ítems</div>
        </div>
        @endforelse
    </div>

    {{-- ======== TOTALES ======== --}}
    <div class="totals">
        @if(!empty($receipt_details->total_quantity_label))
        <div class="total-row">
            <span>{!! $receipt_details->total_quantity_label !!}</span>
            <span>{{ $receipt_details->total_quantity }}</span>
        </div>
        @endif

        <div class="total-row">
            <span>{!! $receipt_details->subtotal_label ?? 'Subtotal:' !!}</span>
            <span>{{ $receipt_details->subtotal }}</span>
        </div>

        @if(!empty($receipt_details->discount))
        <div class="total-row">
            <span>{!! $receipt_details->discount_label ?? 'Descuento:' !!}</span>
            <span>(-) {{ $receipt_details->discount }}</span>
        </div>
        @endif

        @if(!empty($receipt_details->tax))
        <div class="total-row">
            <span>{!! $receipt_details->tax_label ?? 'Impuesto:' !!}</span>
            <span>(+) {{ $receipt_details->tax }}</span>
        </div>
        @endif

        @if(!empty($receipt_details->shipping_charges))
        <div class="total-row">
            <span>{!! $receipt_details->shipping_charges_label ?? 'Envío:' !!}</span>
            <span>{{ $receipt_details->shipping_charges }}</span>
        </div>
        @endif

        <div class="total-row grand-total">
            <span>{!! $receipt_details->total_label ?? 'TOTAL:' !!}</span>
            <span>{{ $receipt_details->total }}</span>
        </div>
    </div>

    {{-- ======== PAGOS ======== --}}
    @if(!empty($receipt_details->payments))
    <div class="payment-section">
        <div class="payment-title">PAGOS</div>
        @foreach($receipt_details->payments as $payment)
        <div class="payment-row">
            <span>{{ $payment['method'] }}</span>
            <span>{{ $payment['amount'] }}</span>
        </div>
        @endforeach

        @if(!empty($receipt_details->total_paid))
        <div class="payment-row" style="font-weight:bold; border-top:1px solid #000; padding-top:3px;">
            <span>{!! $receipt_details->total_paid_label ?? 'Total Pagado:' !!}</span>
            <span>{{ $receipt_details->total_paid }}</span>
        </div>
        @endif

        @if(!empty($receipt_details->total_due) && $receipt_details->total_due != 0)
        <div class="payment-row" style="font-weight:bold;">
            <span>{!! $receipt_details->total_due_label ?? 'Vuelto/Deuda:' !!}</span>
            <span>{{ $receipt_details->total_due }}</span>
        </div>
        @endif
    </div>
    @endif

    {{-- ======== INFORMACIÓN FISCAL ======== --}}
    <div class="fiscal-info">
        <div class="fiscal-title">COMPROBANTE DE VENTA</div>
        @if(!empty($receipt_details->tax_info1))
            <div style="text-align:center; font-size:10px;">
                {{ $receipt_details->tax_label1 ?? 'RUT' }} {{ $receipt_details->tax_info1 }}
            </div>
        @endif
    </div>

    {{-- ======== QR CODE ======== --}}
    @if(!empty($receipt_details->show_qr_code) && !empty($receipt_details->qr_code_text))
    <div class="qr-section">
        <img class="center-block" src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54])}}">
        <div class="qr-text">Escanee para verificar</div>
    </div>
    @endif

    {{-- ======== BARCODE ======== --}}
    @if(!empty($receipt_details->show_barcode))
    <div style="text-align:center; margin: 10px 0;">
        <img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, array(39, 48, 54), true)}}">
    </div>
    @endif

    {{-- ======== LÍNEA DE CORTE ======== --}}
    <div class="cut-line"></div>

    {{-- ======== FOOTER ======== --}}
    <div class="footer">
        <div class="thanks">¡Gracias por su compra!</div>
        @if(!empty($receipt_details->additional_notes))
            <div>{!! nl2br($receipt_details->additional_notes) !!}</div>
        @endif
        @if(!empty($receipt_details->footer_text))
            <div>{!! $receipt_details->footer_text !!}</div>
        @endif
        <div>
            Documento generado electrónicamente<br>
            {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
</div>
