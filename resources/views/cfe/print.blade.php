<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CFE {{ $cfe->series }}-{{ $cfe->number }}</title>
    <style>
        /* Reset y configuración base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        /* Contenedor principal - Ticket 80mm */
        .ticket {
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            background: #fff;
        }

        /* Para impresión térmica */
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
            }
            .ticket {
                width: 100%;
                padding: 2mm;
            }
            .no-print {
                display: none !important;
            }
        }

        /* Encabezado */
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .header .logo {
            max-width: 60mm;
            max-height: 20mm;
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .company-info {
            font-size: 10px;
        }

        /* Tipo de documento */
        .doc-type {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            padding: 5px;
            margin: 10px 0;
            border: 2px solid #000;
            background: #f0f0f0;
        }

        /* Información del documento */
        .doc-info {
            margin: 10px 0;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .doc-info table {
            width: 100%;
            font-size: 11px;
        }

        .doc-info td {
            padding: 2px 0;
        }

        .doc-info .label {
            font-weight: bold;
            width: 40%;
        }

        /* Datos del cliente */
        .customer {
            margin: 10px 0;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .customer-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
        }

        /* Detalle de items */
        .items {
            margin: 10px 0;
        }

        .items-header {
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-bottom: 5px;
            font-size: 10px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }

        .item-desc {
            flex: 1;
            padding-right: 5px;
        }

        .item-qty {
            width: 15%;
            text-align: center;
        }

        .item-price {
            width: 25%;
            text-align: right;
        }

        .item-total {
            width: 25%;
            text-align: right;
            font-weight: bold;
        }

        /* Totales */
        .totals {
            margin: 15px 0;
            border-top: 2px solid #000;
            padding-top: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 2px 0;
        }

        .total-row.grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }

        /* Información fiscal DGI */
        .fiscal-info {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #000;
            background: #f9f9f9;
            font-size: 10px;
        }

        .fiscal-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .cae-info {
            text-align: center;
            margin: 10px 0;
        }

        .cae-number {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* Código QR */
        .qr-section {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            border: 1px dashed #000;
        }

        .qr-section img {
            max-width: 40mm;
            height: auto;
        }

        .qr-text {
            font-size: 9px;
            margin-top: 5px;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }

        .footer .thanks {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Botones de acción (no se imprimen) */
        .actions {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 5px;
        }

        .actions button {
            padding: 10px 20px;
            margin: 5px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }

        .btn-print {
            background: #007bff;
            color: white;
        }

        .btn-download {
            background: #28a745;
            color: white;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        /* Líneas de corte */
        .cut-line {
            border-top: 1px dashed #000;
            margin: 10px 0;
            position: relative;
        }

        .cut-line::before {
            content: '✂';
            position: absolute;
            left: -5px;
            top: -8px;
            font-size: 12px;
        }

        /* Estado del documento */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-accepted {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Botones de acción -->
    <div class="actions no-print">
        <button class="btn-print" onclick="window.print()">
            🖨️ Imprimir Ticket
        </button>
        <a href="{{ route('cfe.print', $cfe->id) }}?format=a4">
            <button class="btn-download" style="background: #17a2b8;">📄 Ver Factura A4</button>
        </a>
        <a href="{{ route('cfe.download-xml', $cfe->id) }}">
            <button class="btn-download">� Descargar XML</button>
        </a>
        <a href="{{ route('cfe.show', $cfe->id) }}">
            <button class="btn-back">← Volver</button>
        </a>
    </div>

    <div class="ticket">
        <!-- Encabezado con datos del emisor -->
        <div class="header">
            @php
                // Determinar si la sucursal tiene identidad fiscal propia
                $location_has_own_rut = isset($location) && $location
                    && !empty($location->location_id)
                    && $location->location_id !== $business->tax_number_1;

                // Nombre a mostrar: sucursal si tiene RUT propio, si no el negocio principal
                $display_company_name = $location_has_own_rut ? $location->name : ($cfe->emitter_name ?? $business->name);

                // Logo: custom_field3 de sucursal si tiene RUT propio y logo definido, si no logo del negocio
                $location_logo_file = ($location_has_own_rut && !empty($location->custom_field3)
                    && file_exists(public_path('uploads/invoice_logos/' . $location->custom_field3)))
                    ? $location->custom_field3 : null;
                $display_logo_url = $location_logo_file
                    ? asset('uploads/invoice_logos/' . $location_logo_file)
                    : ($location_has_own_rut ? null : ($business->logo ? asset('uploads/business_logos/' . $business->logo) : null));

                // RUT a mostrar: emitter_rut del CFE → RUT propio de sucursal → RUT del negocio
                $display_rut = $cfe->emitter_rut
                    ?? ($location_has_own_rut ? $location->location_id : null)
                    ?? $business->tax_number_1
                    ?? '-';
            @endphp
            @if($display_logo_url)
                <img src="{{ $display_logo_url }}" alt="Logo" class="logo">
            @endif
            <div class="company-name">{{ $display_company_name }}</div>
            <div class="company-info">
                RUT: {{ $display_rut }}<br>
                {{ $cfe->emitter_address ?: ($location->landmark ?? $location->name ?? '-') }}<br>
                {{ $cfe->emitter_city ?? ($location->city ?? 'Montevideo') }}, {{ $cfe->emitter_department ?? ($location->state ?? 'Montevideo') }}<br>
                @if($location && $location->mobile)Tel: {{ $location->mobile }}<br>@endif
                @if($location && $location->email)Email: {{ $location->email }}@endif
            </div>
        </div>

        <!-- Tipo de documento -->
        <div class="doc-type">
            {{ $cfe_types[$cfe->cfe_type] ?? 'CFE' }}
        </div>

        <!-- Información del documento -->
        <div class="doc-info">
            <table>
                <tr>
                    <td class="label">Serie-Número:</td>
                    <td>{{ $cfe->series }}-{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha Emisión:</td>
                    <td>{{ $cfe->issue_date->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha Venc.:</td>
                    <td>{{ $cfe->due_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Moneda:</td>
                    <td>{{ $cfe->currency }}@if($cfe->currency !== 'UYU') (TC: {{ $cfe->exchange_rate }})@endif</td>
                </tr>
            </table>
        </div>

        <!-- Datos del cliente -->
        <div class="customer">
            <div class="customer-title">DATOS DEL CLIENTE</div>
            @php
                // Determinar nombre del cliente
                $clientName = $cfe->receiver_name;
                if (empty($clientName) && isset($customer) && $customer) {
                    $clientName = $customer->name ?? trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));
                }
                $clientName = $clientName ?: 'Consumidor Final';
                
                // Determinar dirección del cliente
                $clientAddress = $cfe->receiver_address;
                if (empty($clientAddress) && isset($customer) && $customer) {
                    $clientAddress = $customer->address_line_1 ?? $customer->landmark ?? '';
                }
                
                // Determinar documento
                $clientDoc = $cfe->receiver_document;
                if (empty($clientDoc) && isset($customer) && $customer) {
                    $clientDoc = $customer->tax_number ?? $customer->custom_field1 ?? '';
                }
                $clientDocType = $cfe->receiver_doc_type ?? 'RUT';
            @endphp
            <table style="width: 100%; font-size: 11px;">
                <tr>
                    <td class="label">{{ $clientDocType }}:</td>
                    <td>{{ $clientDoc ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Nombre:</td>
                    <td>{{ $clientName }}</td>
                </tr>
                @if($clientAddress)
                <tr>
                    <td class="label">Dirección:</td>
                    <td>{{ $clientAddress }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Detalle de items -->
        <div class="items">
            <div class="items-header">
                <span style="width: 40%">DESCRIPCIÓN</span>
                <span style="width: 15%">CANT</span>
                <span style="width: 20%">P.UNIT</span>
                <span style="width: 25%">TOTAL</span>
            </div>
            
            @foreach($cfe->items as $item)
            <div class="item-row">
                <div class="item-desc">{{ $item['name'] ?? $item['description'] ?? 'Producto' }}</div>
                <div class="item-qty">{{ number_format($item['quantity'], 2) }}</div>
                <div class="item-price">${{ number_format($item['unit_price'], 2) }}</div>
                <div class="item-total">${{ number_format($item['line_total'] ?? ($item['quantity'] * $item['unit_price']), 2) }}</div>
            </div>
            @endforeach
        </div>

        <!-- Totales -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($cfe->subtotal, 2) }}</span>
            </div>
            @if($cfe->tax_amount > 0)
            <div class="total-row">
                <span>IVA:</span>
                <span>${{ number_format($cfe->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>TOTAL {{ $cfe->currency }}:</span>
                <span>${{ number_format($cfe->total, 2) }}</span>
            </div>
        </div>

        <!-- Información Fiscal DGI -->
        <div class="fiscal-info">
            <div class="fiscal-title">COMPROBANTE FISCAL ELECTRÓNICO</div>
            <div class="fiscal-title">DGI URUGUAY</div>
            
            @if($cfe->cae)
            <div class="cae-info">
                <strong>CAE:</strong><br>
                <span class="cae-number">{{ $cfe->cae }}</span>
            </div>
            <div class="cae-info" style="margin-top: 3px;">
                <strong>Vto. CAE:</strong>
                <span>{{ $cfe->cae_due_date ? $cfe->cae_due_date->format('d/m/Y') : 'Pendiente' }}</span>
            </div>
            @endif

            <table style="width: 100%; font-size: 9px; margin-top: 5px;">
                <tr>
                    <td>Tipo CFE:</td>
                    <td>{{ $cfe->cfe_type }}</td>
                </tr>
                <tr>
                    <td>Estado:</td>
                    <td>
                        @if($cfe->status === 'accepted')
                            <span class="status-badge status-accepted">✓ Aceptado DGI</span>
                        @elseif($cfe->status === 'pending')
                            <span class="status-badge status-pending">⏳ Pendiente</span>
                        @else
                            <span class="status-badge status-error">⚠ {{ ucfirst($cfe->status) }}</span>
                        @endif
                    </td>
                </tr>
                @if($cfe->track_id)
                <tr>
                    <td>Track ID:</td>
                    <td>{{ $cfe->track_id }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Código QR para verificación -->
        <div class="qr-section">
            <div id="qrcode"></div>
            <div class="qr-text">
                Escanee para verificar en DGI<br>
                <small>www.efactura.dgi.gub.uy</small>
            </div>
        </div>

        <!-- Línea de corte -->
        <div class="cut-line"></div>

        <!-- Footer -->
        <div class="footer">
            <div class="thanks">¡Gracias por su compra!</div>
            <div>
                Documento generado electrónicamente<br>
                Válido como comprobante fiscal<br>
                {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>

    <!-- Script para generar QR -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script>
        // Generar código QR con datos del CFE
        var qr = qrcode(0, 'M');
        var qrData = [
            '{{ $cfe->emitter_rut }}',
            '{{ $cfe->cfe_type }}',
            '{{ $cfe->series }}',
            '{{ $cfe->number }}',
            '{{ number_format($cfe->total, 2, ".", "") }}',
            '{{ $cfe->cae ?? "0" }}',
            '{{ $cfe->issue_date->format("Ymd") }}'
        ].join('|');
        
        qr.addData(qrData);
        qr.make();
        document.getElementById('qrcode').innerHTML = qr.createImgTag(3);
    </script>
</body>
</html>
