@php
    $primary_color = '#2c3e50';
    $accent_color = '#3498db';
    $light_bg = '#f8f9fa';
    $border_color = '#dee2e6';
@endphp

<style>
    .presupuesto-container {
        font-family: 'Helvetica Neue', Arial, sans-serif;
        color: {{ $primary_color }};
        font-size: 13px;
        line-height: 1.5;
        max-width: 800px;
        margin: 0 auto;
    }
    .presupuesto-container * {
        box-sizing: border-box;
    }
    .presupuesto-header {
        display: table;
        width: 100%;
        border-bottom: 3px solid {{ $accent_color }};
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    .presupuesto-header-left {
        display: table-cell;
        width: 50%;
        vertical-align: middle;
    }
    .presupuesto-header-right {
        display: table-cell;
        width: 50%;
        vertical-align: middle;
        text-align: right;
    }
    .presupuesto-header-right img {
        max-height: 100px;
        width: auto;
    }
    .presupuesto-title {
        font-size: 32px;
        font-weight: 700;
        color: {{ $accent_color }};
        margin: 0 0 5px 0;
        letter-spacing: 1px;
    }
    .presupuesto-number {
        font-size: 14px;
        color: #666;
        margin: 2px 0;
    }
    .presupuesto-info-row {
        display: table;
        width: 100%;
        margin-bottom: 20px;
    }
    .presupuesto-info-col {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        padding: 12px 15px;
    }
    .presupuesto-info-col.empresa-col {
        background-color: {{ $light_bg }};
        border-radius: 6px;
    }
    .presupuesto-info-col.cliente-col {
        background-color: #eaf4fb;
        border-radius: 6px;
    }
    .info-section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: {{ $accent_color }};
        margin-bottom: 8px;
        letter-spacing: 0.5px;
        border-bottom: 1px solid {{ $border_color }};
        padding-bottom: 4px;
    }
    .info-line {
        font-size: 12px;
        margin: 2px 0;
    }
    .info-line strong {
        color: {{ $primary_color }};
    }
    .presupuesto-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .presupuesto-table thead th {
        background-color: {{ $accent_color }} !important;
        color: white !important;
        padding: 10px 8px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: none;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .presupuesto-table thead th:first-child {
        border-radius: 6px 0 0 0;
    }
    .presupuesto-table thead th:last-child {
        border-radius: 0 6px 0 0;
    }
    .presupuesto-table tbody td {
        padding: 8px;
        border-bottom: 1px solid {{ $border_color }};
        font-size: 12px;
    }
    .presupuesto-table tbody tr:nth-child(even) {
        background-color: {{ $light_bg }};
    }
    .presupuesto-table tbody tr:last-child td {
        border-bottom: 2px solid {{ $accent_color }};
    }
    .presupuesto-totals-row {
        display: table;
        width: 100%;
        margin-bottom: 20px;
    }
    .presupuesto-totals-left {
        display: table-cell;
        width: 50%;
        vertical-align: top;
    }
    .presupuesto-totals-right {
        display: table-cell;
        width: 50%;
        vertical-align: top;
    }
    .totals-table {
        width: 100%;
        border-collapse: collapse;
    }
    .totals-table td {
        padding: 6px 10px;
        font-size: 13px;
    }
    .totals-table .total-row td {
        background-color: {{ $accent_color }} !important;
        color: white !important;
        font-size: 16px;
        font-weight: 700;
        padding: 10px;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .totals-table .total-row td:first-child {
        border-radius: 6px 0 0 6px;
    }
    .totals-table .total-row td:last-child {
        border-radius: 0 6px 6px 0;
    }
    .presupuesto-terms {
        background-color: {{ $light_bg }};
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 20px;
        border-left: 4px solid {{ $accent_color }};
    }
    .presupuesto-terms-title {
        font-size: 13px;
        font-weight: 700;
        color: {{ $accent_color }};
        margin-bottom: 8px;
        text-transform: uppercase;
    }
    .presupuesto-terms p {
        font-size: 11px;
        margin: 3px 0;
        color: #555;
    }
    .presupuesto-signatures {
        display: table;
        width: 100%;
        margin-top: 40px;
        margin-bottom: 20px;
    }
    .signature-col {
        display: table-cell;
        width: 45%;
        text-align: center;
        vertical-align: bottom;
    }
    .signature-spacer {
        display: table-cell;
        width: 10%;
    }
    .signature-line {
        border-top: 2px solid {{ $primary_color }};
        margin: 0 20px;
        padding-top: 8px;
    }
    .signature-label {
        font-size: 12px;
        font-weight: 600;
        color: {{ $primary_color }};
        margin-top: 5px;
    }
    .signature-sublabel {
        font-size: 10px;
        color: #888;
    }
    .presupuesto-footer {
        text-align: center;
        font-size: 10px;
        color: #999;
        border-top: 1px solid {{ $border_color }};
        padding-top: 10px;
        margin-top: 10px;
    }
    .presupuesto-notes {
        margin-bottom: 15px;
    }
    .presupuesto-notes p {
        font-size: 12px;
        margin: 3px 0;
    }
    .presupuesto-validity {
        display: inline-block;
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 4px;
        padding: 6px 12px;
        font-size: 11px;
        color: #856404;
        margin-bottom: 15px;
    }
    @media print {
        .presupuesto-container {
            margin: 0;
            padding: 10px;
        }
        .presupuesto-table thead th,
        .totals-table .total-row td {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<div class="presupuesto-container">
    {{-- ===== HEADER ===== --}}
    <div class="presupuesto-header">
        <div class="presupuesto-header-left">
            <div class="presupuesto-title">
                @if(!empty($receipt_details->invoice_heading))
                    {{ $receipt_details->invoice_heading }}
                @else
                    Presupuesto
                @endif
            </div>
            <div class="presupuesto-number">
                @if(!empty($receipt_details->invoice_no_prefix))
                    {!! $receipt_details->invoice_no_prefix !!}
                @endif
                <strong>{{ $receipt_details->invoice_no }}</strong>
            </div>
            @if(!empty($receipt_details->date_label))
                <div class="presupuesto-number">
                    {{ $receipt_details->date_label }}: <strong>{{ $receipt_details->invoice_date }}</strong>
                </div>
            @endif
            @if(!empty($receipt_details->due_date_label))
                <div class="presupuesto-number">
                    {{ $receipt_details->due_date_label }}: <strong>{{ $receipt_details->due_date ?? '' }}</strong>
                </div>
            @endif
        </div>
        <div class="presupuesto-header-right">
            @if(!empty($receipt_details->logo))
                <img src="{{ $receipt_details->logo }}" alt="Logo">
            @endif
        </div>
    </div>

    {{-- ===== EMPRESA & CLIENTE INFO ===== --}}
    <div class="presupuesto-info-row">
        <div class="presupuesto-info-col empresa-col" style="margin-right: 10px;">
            <div class="info-section-title">Datos de la Empresa</div>
            @if(!empty($receipt_details->display_name))
                <div class="info-line"><strong>{{ $receipt_details->display_name }}</strong></div>
            @endif
            @if(!empty($receipt_details->address))
                <div class="info-line">{!! $receipt_details->address !!}</div>
            @endif
            @if(!empty($receipt_details->contact))
                <div class="info-line">{!! $receipt_details->contact !!}</div>
            @endif
            @if(!empty($receipt_details->website))
                <div class="info-line">{{ $receipt_details->website }}</div>
            @endif
            @if(!empty($receipt_details->tax_info1))
                <div class="info-line"><strong>{{ $receipt_details->tax_label1 }}:</strong> {{ $receipt_details->tax_info1 }}</div>
            @endif
            @if(!empty($receipt_details->tax_info2))
                <div class="info-line"><strong>{{ $receipt_details->tax_label2 }}:</strong> {{ $receipt_details->tax_info2 }}</div>
            @endif
            @if(!empty($receipt_details->location_custom_fields))
                <div class="info-line">{{ $receipt_details->location_custom_fields }}</div>
            @endif
        </div>
        <div class="presupuesto-info-col cliente-col">
            <div class="info-section-title">Datos del Cliente</div>
            @if(!empty($receipt_details->customer_info))
                <div class="info-line">{!! $receipt_details->customer_info !!}</div>
            @endif
            @if(!empty($receipt_details->customer_tax_label))
                <div class="info-line"><strong>{{ $receipt_details->customer_tax_label }}:</strong> {{ $receipt_details->customer_tax_number }}</div>
            @endif
            @if(!empty($receipt_details->client_id_label))
                <div class="info-line"><strong>{{ $receipt_details->client_id_label }}:</strong> {{ $receipt_details->client_id }}</div>
            @endif
            @if(!empty($receipt_details->customer_custom_fields))
                <div class="info-line">{!! $receipt_details->customer_custom_fields !!}</div>
            @endif
            @if(!empty($receipt_details->shipping_address))
                <div class="info-line" style="margin-top: 5px;"><strong>Dir. Envío:</strong> {{ $receipt_details->shipping_address }}</div>
            @endif
            @if(!empty($receipt_details->sales_person_label))
                <div class="info-line" style="margin-top: 5px;"><strong>{{ $receipt_details->sales_person_label }}:</strong> {{ $receipt_details->sales_person }}</div>
            @endif
        </div>
    </div>

    {{-- ===== CUSTOM FIELDS ===== --}}
    @if(!empty($receipt_details->sell_custom_field_1_value) || !empty($receipt_details->sell_custom_field_2_value) || !empty($receipt_details->sell_custom_field_3_value) || !empty($receipt_details->sell_custom_field_4_value))
        <div style="margin-bottom: 15px;">
            @if(!empty($receipt_details->sell_custom_field_1_value))
                <span class="info-line" style="margin-right: 20px;"><strong>{{ $receipt_details->sell_custom_field_1_label }}:</strong> {{ $receipt_details->sell_custom_field_1_value }}</span>
            @endif
            @if(!empty($receipt_details->sell_custom_field_2_value))
                <span class="info-line" style="margin-right: 20px;"><strong>{{ $receipt_details->sell_custom_field_2_label }}:</strong> {{ $receipt_details->sell_custom_field_2_value }}</span>
            @endif
            @if(!empty($receipt_details->sell_custom_field_3_value))
                <span class="info-line" style="margin-right: 20px;"><strong>{{ $receipt_details->sell_custom_field_3_label }}:</strong> {{ $receipt_details->sell_custom_field_3_value }}</span>
            @endif
            @if(!empty($receipt_details->sell_custom_field_4_value))
                <span class="info-line"><strong>{{ $receipt_details->sell_custom_field_4_label }}:</strong> {{ $receipt_details->sell_custom_field_4_value }}</span>
            @endif
        </div>
    @endif

    {{-- ===== PAYMENT TERMS / VALIDITY ===== --}}
    @if(!empty($receipt_details->pay_term))
        <div class="presupuesto-validity">
            <strong>Condición de Pago:</strong> {{ $receipt_details->pay_term }}
        </div>
    @endif

    {{-- ===== PRODUCT TABLE ===== --}}
    @php
        // En presupuesto siempre mostramos la columna descuento
        $show_discount_col = true;
    @endphp
    <table class="presupuesto-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 35%;">Producto</th>
                @if($receipt_details->show_cat_code == 1)
                    <th style="width: 10%;">{{ $receipt_details->cat_code_label }}</th>
                @endif
                <th style="width: 10%; text-align: center;">Cantidad</th>
                <th style="width: 15%; text-align: right;">P. Unitario</th>
                <th style="width: 12%; text-align: right;">Descuento</th>
                <th style="width: 13%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt_details->lines as $line)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td>
                        @if(!empty($line['image']))
                            <img src="{{ $line['image'] }}" alt="Img" width="40" style="float: left; margin-right: 8px; border-radius: 4px;">
                        @endif
                        <strong>{{ $line['name'] }}</strong>
                        @if(!empty($line['product_variation']) && $line['product_variation'] != 'DUMMY')
                            {{ $line['product_variation'] }}
                        @endif
                        @if(!empty($line['variation']) && $line['variation'] != 'DUMMY')
                            {{ $line['variation'] }}
                        @endif
                        @if(!empty($line['sub_sku']))
                            <br><small style="color: #888;">SKU: {{ $line['sub_sku'] }}</small>
                        @endif
                        @if(!empty($line['product_description']))
                            <br><small style="color: #666;">{!! $line['product_description'] !!}</small>
                        @endif
                        @if(!empty($line['sell_line_note']))
                            <br><small style="color: #666;"><em>{!! $line['sell_line_note'] !!}</em></small>
                        @endif
                        @if(!empty($line['warranty_name']))
                            <br><small style="color: #888;">Garantía: {{ $line['warranty_name'] }}
                            @if(!empty($line['warranty_exp_date'])) - {{ @format_date($line['warranty_exp_date']) }} @endif
                            </small>
                        @endif
                    </td>
                    @if($receipt_details->show_cat_code == 1)
                        <td>{{ $line['cat_code'] ?? '' }}</td>
                    @endif
                    <td style="text-align: center;">
                        {{ $line['quantity'] }} {{ $line['units'] }}
                    </td>
                    <td style="text-align: right;">
                        {{ $line['unit_price_before_discount'] }}
                    </td>
                    <td style="text-align: right;">
                        @if(!empty($line['line_discount_percent']) && floatval($line['line_discount_percent']) > 0)
                            {{ $line['line_discount_percent'] }}%
                        @elseif(!empty($line['total_line_discount']) && floatval(str_replace([',','.'], ['','.'], $line['total_line_discount'])) > 0)
                            {{ $line['total_line_discount'] }}
                        @else
                            —
                        @endif
                    </td>
                    <td style="text-align: right;">
                        {{ $line['line_total_exc_tax'] }}
                    </td>
                </tr>

                @if(!empty($line['modifiers']))
                    @foreach($line['modifiers'] as $modifier)
                        <tr style="background-color: #fafafa;">
                            <td></td>
                            <td style="padding-left: 20px;">
                                <small>↳ {{ $modifier['name'] }} {{ $modifier['variation'] }}</small>
                            </td>
                            @if($receipt_details->show_cat_code == 1)
                                <td></td>
                            @endif
                            <td style="text-align: center;">
                                <small>{{ $modifier['quantity'] }} {{ $modifier['units'] }}</small>
                            </td>
                            <td style="text-align: right;">
                                <small>{{ $modifier['unit_price_exc_tax'] }}</small>
                            </td>
                            <td style="text-align: right;"><small>—</small></td>
                            <td style="text-align: right;">
                                <small>{{ $modifier['line_total'] }}</small>
                            </td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>

    {{-- ===== TOTALS SECTION ===== --}}
    <div class="presupuesto-totals-row">
        <div class="presupuesto-totals-left">
            {{-- Payment info (if any) --}}
            @if(!empty($receipt_details->payments))
                <div style="margin-bottom: 10px;">
                    <div class="info-section-title">Pagos Realizados</div>
                    @foreach($receipt_details->payments as $payment)
                        <div class="info-line">
                            {{ $payment['method'] }} - {{ $payment['amount'] }} <small style="color: #888;">({{ $payment['date'] }})</small>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Total quantities --}}
            @if(!empty($receipt_details->total_quantity_label))
                <div class="info-line">
                    <strong>{!! $receipt_details->total_quantity_label !!}:</strong> {{ $receipt_details->total_quantity }}
                </div>
            @endif
            @if(!empty($receipt_details->total_items_label))
                <div class="info-line">
                    <strong>{!! $receipt_details->total_items_label !!}:</strong> {{ $receipt_details->total_items }}
                </div>
            @endif
        </div>

        <div class="presupuesto-totals-right">
            <table class="totals-table">
                <tbody>
                    <tr>
                        <td style="width: 55%;"><strong>{!! $receipt_details->subtotal_label !!}</strong></td>
                        <td style="text-align: right;">{{ $receipt_details->subtotal_exc_tax }}</td>
                    </tr>

                    @if(!empty($receipt_details->shipping_charges))
                        <tr>
                            <td>{!! $receipt_details->shipping_charges_label !!}</td>
                            <td style="text-align: right;">(+) {{ $receipt_details->shipping_charges }}</td>
                        </tr>
                    @endif

                    @if(!empty($receipt_details->packing_charge))
                        <tr>
                            <td>{!! $receipt_details->packing_charge_label !!}</td>
                            <td style="text-align: right;">(+) {{ $receipt_details->packing_charge }}</td>
                        </tr>
                    @endif

                    @if(!empty($receipt_details->taxes))
                        @foreach($receipt_details->taxes as $k => $v)
                            <tr>
                                <td>{{ $k }}</td>
                                <td style="text-align: right;">(+) {{ $v }}</td>
                            </tr>
                        @endforeach
                    @endif

                    @if(!empty($receipt_details->discount))
                        <tr>
                            <td>{!! $receipt_details->discount_label !!}</td>
                            <td style="text-align: right;">(-) {{ $receipt_details->discount }}</td>
                        </tr>
                    @endif

                    @if(!empty($receipt_details->total_line_discount))
                        <tr>
                            <td>{!! $receipt_details->line_discount_label !!}</td>
                            <td style="text-align: right;">(-) {{ $receipt_details->total_line_discount }}</td>
                        </tr>
                    @endif

                    @if(!empty($receipt_details->additional_expenses))
                        @foreach($receipt_details->additional_expenses as $key => $val)
                            <tr>
                                <td>{{ $key }}:</td>
                                <td style="text-align: right;">(+) {{ $val }}</td>
                            </tr>
                        @endforeach
                    @endif

                    @if(!empty($receipt_details->group_tax_details))
                        @foreach($receipt_details->group_tax_details as $key => $value)
                            <tr>
                                <td>{!! $key !!}</td>
                                <td style="text-align: right;">(+) {{ $value }}</td>
                            </tr>
                        @endforeach
                    @else
                        @if(!empty($receipt_details->tax))
                            <tr>
                                <td>{!! $receipt_details->tax_label !!}</td>
                                <td style="text-align: right;">(+) {{ $receipt_details->tax }}</td>
                            </tr>
                        @endif
                    @endif

                    @if($receipt_details->round_off_amount > 0)
                        <tr>
                            <td>{!! $receipt_details->round_off_label !!}</td>
                            <td style="text-align: right;">{{ $receipt_details->round_off }}</td>
                        </tr>
                    @endif

                    <tr class="total-row">
                        <td>{!! $receipt_details->total_label !!}</td>
                        <td style="text-align: right;">{{ $receipt_details->total }}</td>
                    </tr>

                    @if(!empty($receipt_details->total_in_words))
                        <tr>
                            <td colspan="2" style="text-align: right; font-size: 11px; color: #666;">
                                ({{ $receipt_details->total_in_words }})
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== ADDITIONAL NOTES ===== --}}
    @if(!empty($receipt_details->additional_notes))
        <div class="presupuesto-notes">
            <div class="info-section-title">Observaciones</div>
            <p>{!! nl2br($receipt_details->additional_notes) !!}</p>
        </div>
    @endif

    {{-- ===== TERMS & CONDITIONS (from staff_note) ===== --}}
    @if(!empty($receipt_details->terms_conditions))
        <div class="presupuesto-terms">
            <div class="presupuesto-terms-title">Términos y Condiciones</div>
            <p>{!! nl2br($receipt_details->terms_conditions) !!}</p>
            @if(!empty($receipt_details->terms_url))
                <p style="margin-top: 8px;">
                    <strong>Más información:</strong> 
                    <a href="{{ $receipt_details->terms_url }}" style="color: {{ $accent_color }};">{{ $receipt_details->terms_url }}</a>
                </p>
            @endif
        </div>
    @endif

    {{-- ===== SIGNATURES ===== --}}
    <div class="presupuesto-signatures">
        <div class="signature-col">
            <div style="height: 60px;"></div>
            <div class="signature-line">
                <div class="signature-label">Firma de la Empresa</div>
                @if(!empty($receipt_details->display_name))
                    <div class="signature-sublabel">{{ $receipt_details->display_name }}</div>
                @endif
            </div>
        </div>
        <div class="signature-spacer"></div>
        <div class="signature-col">
            <div style="height: 60px;"></div>
            <div class="signature-line">
                <div class="signature-label">Firma del Cliente</div>
                @if(!empty($receipt_details->customer_name))
                    <div class="signature-sublabel">{{ $receipt_details->customer_name }}</div>
                @elseif(!empty($receipt_details->customer_info))
                    <div class="signature-sublabel">{!! strip_tags($receipt_details->customer_info) !!}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== FOOTER ===== --}}
    @if(!empty($receipt_details->footer_text))
        <div class="presupuesto-footer">
            {!! $receipt_details->footer_text !!}
        </div>
    @endif

    {{-- ===== BARCODE / QR ===== --}}
    @if($receipt_details->show_barcode || $receipt_details->show_qr_code)
        <div style="text-align: center; margin-top: 10px;">
            @if($receipt_details->show_barcode)
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, array(39, 48, 54), true)}}">
            @endif
            @if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
                <br>
                <img src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54])}}">
            @endif
        </div>
    @endif
</div>
