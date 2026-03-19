<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            background: #1a237e;
            color: #fff;
            padding: 20px 25px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
        }
        .header .subtitle {
            font-size: 11px;
            opacity: 0.8;
            margin-top: 3px;
        }
        .header-right {
            text-align: right;
        }
        .section {
            margin-bottom: 18px;
        }
        .section-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
            font-weight: bold;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 6px 10px;
            vertical-align: top;
        }
        .info-table .label {
            color: #999;
            font-size: 10px;
            text-transform: uppercase;
            width: 120px;
        }
        .info-table .value {
            font-weight: 600;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .items-table thead th {
            background: #f5f5f5;
            padding: 8px 10px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            border-bottom: 2px solid #ddd;
            text-align: left;
        }
        .items-table tbody td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .items-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-box {
            width: 250px;
            float: right;
            margin-top: 15px;
        }
        .totals-row {
            display: flex;
            padding: 5px 0;
        }
        .totals-table {
            width: 250px;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 8px;
        }
        .totals-table .grand-total td {
            border-top: 2px solid #1a237e;
            padding-top: 10px;
            font-size: 14px;
            font-weight: 800;
        }
        .totals-table .grand-total .amount {
            color: #1a237e;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-draft { background: #f5f5f5; color: #757575; }
        .status-pending { background: #fff3e0; color: #e65100; }
        .status-approved { background: #e8f5e9; color: #2e7d32; }
        .status-in_progress { background: #e3f2fd; color: #1565c0; }
        .status-partial { background: #fce4ec; color: #c62828; }
        .status-completed { background: #e8f5e9; color: #1b5e20; }
        .status-cancelled { background: #fafafa; color: #9e9e9e; }
        .priority-low { color: #2e7d32; }
        .priority-medium { color: #f57f17; }
        .priority-high { color: #e65100; }
        .priority-urgent { color: #c62828; font-weight: bold; }
        .notes-box {
            background: #f8f9fa;
            border-left: 3px solid #1a237e;
            padding: 10px 15px;
            margin-top: 10px;
            border-radius: 0 4px 4px 0;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding: 8px 0;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .col-half {
            width: 48%;
            float: left;
        }
        .col-half-right {
            width: 48%;
            float: right;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background:#1a237e;color:#fff;padding:20px 25px;border-radius:6px;">
                <table width="100%">
                    <tr>
                        <td style="color:#fff;">
                            <div style="font-size:10px;opacity:0.8;margin-bottom:4px;">REMITO / ORDEN DE PEDIDO</div>
                            <div style="font-size:22px;font-weight:bold;">{{ $order->order_number }}</div>
                        </td>
                        <td style="text-align:right;color:#fff;">
                            @php
                                $statusTexts = [
                                    'draft' => 'Borrador', 'pending' => 'Pendiente', 'approved' => 'Aprobada',
                                    'in_progress' => 'En Proceso', 'partial' => 'Parcial',
                                    'completed' => 'Completada', 'cancelled' => 'Cancelada'
                                ];
                            @endphp
                            <span class="status-badge status-{{ $order->status }}">
                                {{ $statusTexts[$order->status] ?? $order->status }}
                            </span>
                            <div style="font-size:10px;opacity:0.8;margin-top:6px;">
                                Fecha: {{ $order->created_at->format('d/m/Y H:i') }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <br>

    <!-- Info General -->
    <div class="section">
        <div class="section-title">Informacion General</div>
        <table width="100%">
            <tr>
                <td width="50%" valign="top">
                    <table class="info-table">
                        <tr>
                            <td class="label">Cliente:</td>
                            <td class="value">{{ $order->contact->name ?? 'N/A' }}</td>
                        </tr>
                        @if($order->contact && $order->contact->tax_number)
                        <tr>
                            <td class="label">RUT:</td>
                            <td class="value">{{ $order->contact->tax_number }}</td>
                        </tr>
                        @endif
                        @if($order->contact && ($order->contact->mobile || $order->contact->landline))
                        <tr>
                            <td class="label">Telefono:</td>
                            <td class="value">{{ $order->contact->mobile ?? $order->contact->landline }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Ubicacion:</td>
                            <td class="value">{{ $order->location->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </td>
                <td width="50%" valign="top">
                    <table class="info-table">
                        <tr>
                            <td class="label">Fecha Orden:</td>
                            <td class="value">{{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Entrega:</td>
                            <td class="value">{{ $order->expected_delivery_date ? $order->expected_delivery_date->format('d/m/Y') : 'No definida' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Prioridad:</td>
                            <td class="value priority-{{ $order->priority }}">
                                @php
                                    $priorityTexts = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente'];
                                @endphp
                                {{ $priorityTexts[$order->priority] ?? $order->priority }}
                            </td>
                        </tr>
                        @if($order->reference)
                        <tr>
                            <td class="label">Referencia:</td>
                            <td class="value">{{ $order->reference }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Creado por:</td>
                            <td class="value">{{ ($order->createdBy->first_name ?? '') . ' ' . ($order->createdBy->last_name ?? '') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    @if($order->shipping_address)
    <div class="section">
        <div class="section-title">Direccion de Envio</div>
        <p style="margin:0;">{{ $order->shipping_address }}</p>
        @if($order->shipping_method)
        <p style="margin:4px 0 0;color:#666;font-size:11px;">Metodo: {{ $order->shipping_method }}</p>
        @endif
    </div>
    @endif

    <!-- Items del Pedido -->
    <div class="section">
        <div class="section-title">Items del Pedido</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
                    <th>Producto</th>
                    <th style="width:60px;" class="text-center">Cant.</th>
                    <th style="width:90px;" class="text-right">P. Unit.</th>
                    <th style="width:90px;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->lines as $i => $item)
                <tr>
                    <td style="color:#999;">{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name ?? $item->product_name ?? 'Producto' }}</strong>
                        @if($item->variation && $item->variation->name && $item->variation->name != 'DUMMY')
                        <br><span style="font-size:10px;color:#999;">{{ $item->variation->name }}</span>
                        @endif
                        @if($item->sku)
                        <br><span style="font-size:10px;color:#aaa;">SKU: {{ $item->sku }}</span>
                        @endif
                        @if($item->notes)
                        <br><span style="font-size:10px;color:#888;">Nota: {{ $item->notes }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">$ {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right" style="font-weight:600;">$ {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding:20px;color:#999;">No hay items en este pedido</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($order->lines->count() > 0)
        <table width="100%">
            <tr>
                <td>&nbsp;</td>
                <td width="250" style="padding-top:15px;">
                    <table class="totals-table">
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-right">$ {{ number_format($subtotal, 2) }}</td>
                        </tr>
                        @if($tax > 0)
                        <tr>
                            <td>Impuestos:</td>
                            <td class="text-right">$ {{ number_format($tax, 2) }}</td>
                        </tr>
                        @endif
                        @if($discount > 0)
                        <tr>
                            <td>Descuento:</td>
                            <td class="text-right" style="color:#c62828;">-$ {{ number_format($discount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="grand-total">
                            <td><strong>TOTAL:</strong></td>
                            <td class="text-right amount"><strong>$ {{ number_format($total, 2) }}</strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        @endif
    </div>

    @if($order->notes)
    <div class="section">
        <div class="section-title">Notas</div>
        <div class="notes-box">
            {{ $order->notes }}
        </div>
    </div>
    @endif

    @if($order->terms_conditions)
    <div class="section">
        <div class="section-title">Terminos y Condiciones</div>
        <div style="font-size:10px;color:#666;line-height:1.5;">
            {{ $order->terms_conditions }}
        </div>
    </div>
    @endif

    <!-- Firmas -->
    <div style="margin-top:40px;">
        <table width="100%">
            <tr>
                <td width="45%" style="text-align:center;padding-top:40px;border-top:1px solid #333;">
                    <div style="font-size:10px;color:#666;margin-top:5px;">Firma Autorizada</div>
                    <div style="font-size:11px;font-weight:600;">{{ $business->name ?? 'Empresa' }}</div>
                </td>
                <td width="10%">&nbsp;</td>
                <td width="45%" style="text-align:center;padding-top:40px;border-top:1px solid #333;">
                    <div style="font-size:10px;color:#666;margin-top:5px;">Recibido por</div>
                    <div style="font-size:11px;font-weight:600;">{{ $order->contact->name ?? 'Cliente' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        {{ $business->name ?? 'Empresa' }} | Generado el {{ now()->format('d/m/Y H:i') }} | Orden {{ $order->order_number }}
    </div>
</body>
</html>