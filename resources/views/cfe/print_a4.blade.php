<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cfe_types[$cfe->cfe_type] ?? 'CFE' }} {{ $cfe->series }}-{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #374151;
            background: #e8f4fc;
        }

        .invoice-container {
            width: 210mm;
            min-height: 297mm;
            margin: 15px auto;
            background: #fff;
            box-shadow: 0 4px 25px rgba(0,0,0,0.1);
            border: 1px solid #d1e3f0;
        }

        @media print {
            body { background: #fff; margin: 0; }
            .invoice-container { width: 100%; margin: 0; box-shadow: none; border: none; }
            .no-print { display: none !important; }
            @page { size: A4; margin: 0; }
        }

        /* Header superior con info del documento */
        .doc-header {
            background: #eef6fc;
            border-bottom: 1px solid #cde4f4;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .doc-header-left {
            display: flex;
            gap: 30px;
        }

        .doc-header-item {
            font-size: 11px;
        }

        .doc-header-item .label {
            color: #5a7d9a;
            font-weight: 500;
        }

        .doc-header-item .value {
            color: #2c5282;
            font-weight: 600;
        }

        .doc-status {
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .doc-status.pending { background: #fef3c7; color: #92400e; }
        .doc-status.accepted { background: #d1fae5; color: #065f46; }
        .doc-status.sent { background: #dbeafe; color: #1e40af; }

        /* Header principal con título y datos empresa */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            padding: 25px 30px;
            border-bottom: 2px solid #e2e8f0;
        }

        .header-left {
            flex: 1;
        }

        .doc-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e3a5f;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        .doc-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 3px;
        }

        .doc-number-main {
            font-size: 13px;
            color: #475569;
            font-weight: 500;
        }

        .header-right {
            text-align: right;
            min-width: 250px;
        }

        .company-logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 10px;
        }

        .company-monogram {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 18px;
            font-style: italic;
        }

        .company-logo {
            max-height: 45px;
            max-width: 150px;
        }

        .company-name {
            font-size: 18px;
            font-weight: 700;
            color: #1e3a5f;
        }

        .company-details {
            font-size: 11px;
            color: #64748b;
            line-height: 1.6;
            margin-top: 8px;
        }

        /* Sección de datos del cliente */
        .client-section {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 20px 30px;
        }

        .section-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .client-name {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .client-details {
            font-size: 11px;
            color: #475569;
            line-height: 1.6;
        }

        /* Datos del comprobante */
        .invoice-data-section {
            padding: 20px 30px;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px 30px;
        }

        .data-item {
            display: flex;
            font-size: 12px;
        }

        .data-item .label {
            color: #1e3a5f;
            font-weight: 600;
            min-width: 150px;
        }

        .data-item .value {
            color: #475569;
        }

        .total-highlight {
            font-size: 16px;
            font-weight: 700;
            color: #1e3a5f;
            margin-top: 10px;
        }

        /* Tabla de items */
        .items-section {
            padding: 20px 30px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .items-table thead {
            background: #f1f5f9;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }

        .items-table th {
            padding: 12px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: #475569;
        }

        .items-table th.text-center { text-align: center; }
        .items-table th.text-right { text-align: right; }

        .items-table tbody tr { border-bottom: 1px solid #f1f5f9; }

        .items-table td {
            padding: 14px 10px;
            color: #374151;
        }

        .items-table td.text-center { text-align: center; }
        .items-table td.text-right { text-align: right; }
        
        .item-name { 
            font-weight: 600; 
            color: #1e293b; 
        }
        
        .item-code {
            font-size: 10px;
            color: #64748b;
        }

        /* Totales */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            padding: 0 30px 20px;
        }

        .totals-box {
            width: 280px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            font-size: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .total-row:last-child { border-bottom: none; }

        .total-row .label { color: #64748b; }
        .total-row .value { font-weight: 600; color: #1e293b; }

        .total-row.grand-total {
            background: #1e3a5f;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            padding: 12px 15px;
        }

        .total-row.grand-total .label,
        .total-row.grand-total .value { color: #fff; }

        /* Sección fiscal DGI */
        .fiscal-section {
            display: flex;
            gap: 20px;
            background: #eef6fc;
            border-top: 1px solid #cde4f4;
            padding: 20px 30px;
            margin: 0;
        }

        .fiscal-info { flex: 1; }

        .fiscal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .fiscal-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #2563eb, #1e40af);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .fiscal-title { 
            font-size: 14px; 
            font-weight: 700; 
            color: #1e3a5f; 
        }
        
        .fiscal-subtitle { 
            font-size: 11px; 
            color: #5a7d9a; 
        }

        .fiscal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .fiscal-item {
            background: #fff;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #d1e3f0;
        }

        .fiscal-item-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #5a7d9a;
            margin-bottom: 3px;
        }

        .fiscal-item-value { 
            font-size: 12px; 
            font-weight: 600; 
            color: #1e3a5f; 
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-sent { background: #dbeafe; color: #1e40af; }

        /* QR Section */
        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #d1e3f0;
            min-width: 130px;
        }

        .qr-code { width: 90px; height: 90px; }
        .qr-code canvas, .qr-code img { width: 90px !important; height: 90px !important; }

        .qr-text {
            font-size: 8px;
            color: #5a7d9a;
            text-align: center;
            margin-top: 8px;
            line-height: 1.4;
        }

        /* Footer */
        .invoice-footer {
            background: #f8fafc;
            padding: 15px 30px;
            border-top: 1px solid #e2e8f0;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-legal { 
            font-size: 9px; 
            color: #64748b; 
            line-height: 1.5; 
        }
        
        .footer-legal strong { color: #1e3a5f; }
        .footer-timestamp { font-size: 9px; color: #94a3b8; }

        /* Notas */
        .notes-section {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 6px;
            padding: 12px 15px;
            margin: 0 30px 20px;
        }

        .notes-title { font-size: 10px; font-weight: 600; color: #92400e; margin-bottom: 5px; }
        .notes-content { font-size: 11px; color: #78350f; }

        /* Botones de acción */
        .actions {
            text-align: center;
            padding: 20px;
            background: #fff;
            margin: 15px auto;
            max-width: 210mm;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }

        .actions .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            margin: 5px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); }
        .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-success { background: #059669; color: #fff; }
        .btn-success:hover { background: #047857; }
    </style>
</head>
<body>
    <div class="actions no-print">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir Factura</button>
        <a href="{{ route('cfe.print', $cfe->id) }}?format=ticket" class="btn btn-secondary">🎫 Ver Ticket 80mm</a>
        <a href="{{ route('cfe.download-xml', $cfe->id) }}" class="btn btn-success">📥 Descargar XML</a>
        <a href="{{ route('cfe.show', $cfe->id) }}" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="invoice-container">
        <!-- Header superior con info del documento -->
        <div class="doc-header">
            <div class="doc-header-left">
                <div class="doc-header-item">
                    <div class="label">Tipo de Documento:</div>
                    <div class="value">{{ $cfe_types[$cfe->cfe_type] ?? 'CFE' }}</div>
                </div>
                <div class="doc-header-item">
                    <div class="label">Serie y Número:</div>
                    <div class="value">{{ $cfe->series }} {{ str_pad($cfe->number, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="doc-header-item">
                    <div class="label">CAE:</div>
                    <div class="value">{{ $cfe->cae ?: 'CAE000000000000' }}</div>
                </div>
            </div>
            <div class="doc-header-item">
                <div class="label">Estado:</div>
                <span class="doc-status sent">Enviado</span>
            </div>
        </div>

        <!-- Header principal -->
        <div class="invoice-header">
            <div class="header-left">
                <div class="doc-title">COMPROBANTE FISCAL ELECTRÓNICO</div>
                <div class="doc-subtitle">{{ $cfe_types[$cfe->cfe_type] ?? 'Comprobante' }}</div>
                <div class="doc-number-main">Nº {{ $cfe_types[$cfe->cfe_type] ? substr($cfe_types[$cfe->cfe_type], 0, 4) : 'CFE' }}-{{ $cfe->series }}-{{ str_pad($cfe->number, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="header-right">
                <div class="company-logo-wrapper">
                    @if($business->logo)
                        <img src="{{ asset('uploads/business_logos/' . $business->logo) }}" alt="Logo" class="company-logo">
                    @else
                        <div class="company-monogram">{{ strtoupper(substr($business->name, 0, 2)) }}</div>
                    @endif
                    <div class="company-name">{{ $business->name }}</div>
                </div>
                @php
                    // RUT del emisor - buscar en múltiples fuentes
                    $emitterRut = '';
                    // 1. Primero desde el CFE guardado
                    if (!empty($cfe->emitter_rut)) {
                        $emitterRut = $cfe->emitter_rut;
                    }
                    // 2. Luego desde configuración CFE
                    elseif (isset($cfe_settings) && !empty($cfe_settings['cfe_emitter_rut'])) {
                        $emitterRut = $cfe_settings['cfe_emitter_rut'];
                    }
                    // 3. Finalmente desde el negocio
                    elseif (!empty($business->tax_number_1)) {
                        $emitterRut = $business->tax_number_1;
                    }
                    
                    // Dirección del emisor (usar location si está disponible)
                    $emitterAddress = $cfe->emitter_address;
                    if (empty($emitterAddress) && isset($location) && $location) {
                        $emitterAddress = $location->landmark ?? $location->name ?? '';
                    }
                    
                    // Ciudad y departamento
                    $emitterCity = $cfe->emitter_city ?? ($location->city ?? 'Montevideo');
                    $emitterDept = $cfe->emitter_department ?? ($location->state ?? 'Montevideo');
                    
                    // Teléfono y email
                    $emitterPhone = isset($location) && $location ? $location->mobile : '';
                    $emitterEmail = isset($location) && $location ? $location->email : '';
                @endphp
                <div class="company-details">
                    <strong>RUT: {{ $emitterRut ?: 'No configurado' }}</strong><br>
                    {{ $emitterAddress ?: 'Dirección no configurada' }}<br>
                    {{ $emitterCity }}, {{ $emitterDept }}<br>
                    @if($emitterPhone)Tel: {{ $emitterPhone }}<br>@endif
                    @if($emitterEmail)Email: {{ $emitterEmail }}@endif
                </div>
            </div>
        </div>

        <!-- Datos del Cliente -->
        <div class="client-section">
            <div class="section-label">Datos del Cliente</div>
            @php
                // Determinar nombre del cliente - múltiples fuentes
                $clientName = '';
                
                // 1. Primero intentar desde el CFE
                if (!empty($cfe->receiver_name)) {
                    $clientName = $cfe->receiver_name;
                }
                // 2. Si no, desde el contacto
                elseif (isset($customer) && $customer) {
                    if (!empty($customer->name)) {
                        $clientName = $customer->name;
                    } elseif (!empty($customer->first_name) || !empty($customer->last_name)) {
                        $clientName = trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));
                    } elseif (!empty($customer->supplier_business_name)) {
                        $clientName = $customer->supplier_business_name;
                    }
                }
                // 3. Fallback
                if (empty($clientName)) {
                    $clientName = 'Consumidor Final';
                }
                
                // Determinar dirección del cliente
                $clientAddress = '';
                if (!empty($cfe->receiver_address)) {
                    $clientAddress = $cfe->receiver_address;
                } elseif (isset($customer) && $customer) {
                    $clientAddress = $customer->address_line_1 ?? $customer->landmark ?? $customer->address_line_2 ?? '';
                }
                
                // Determinar ciudad/departamento
                $clientCity = $cfe->receiver_city ?? '';
                if (empty($clientCity) && isset($customer) && $customer) {
                    $clientCity = $customer->city ?? '';
                }
                $clientDept = $cfe->receiver_department ?? '';
                if (empty($clientDept) && isset($customer) && $customer) {
                    $clientDept = $customer->state ?? '';
                }
                
                // Determinar documento (RUT/CI)
                $clientDoc = '';
                if (!empty($cfe->receiver_document)) {
                    $clientDoc = $cfe->receiver_document;
                } elseif (isset($customer) && $customer) {
                    $clientDoc = $customer->tax_number ?? $customer->custom_field1 ?? $customer->contact_id ?? '';
                }
                $clientDocType = $cfe->receiver_doc_type ?? 'RUT';
            @endphp
            <div class="client-name">{{ $clientName }}</div>
            <div class="client-details">
                @if($clientAddress){{ $clientAddress }}@endif
                @if($clientCity), {{ $clientCity }}@endif
                @if($clientDept), {{ $clientDept }}@endif
                <br>
                {{ $clientDocType }}: {{ $clientDoc ?: '-' }}
            </div>
        </div>

        <!-- Datos del Comprobante -->
        <div class="invoice-data-section">
            <div class="section-label">Datos del Comprobante</div>
            <div class="data-grid">
                <div class="data-item">
                    <span class="label">Fecha de Emisión:</span>
                    <span class="value">{{ $cfe->issue_date->format('d/m/Y') }}</span>
                </div>
                <div class="data-item">
                    <span class="label">Fecha de Vencimiento:</span>
                    <span class="value">{{ $cfe->due_date ? $cfe->due_date->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="label">Condición de Pago:</span>
                    <span class="value">
                        @php
                            $payment_methods = [1 => 'Contado', 2 => 'Crédito', 3 => 'Contra Entrega', 4 => 'Cheque', 5 => 'Transferencia', 6 => 'Débito', 7 => 'Crédito', 8 => 'Mercado Pago', 9 => 'Otro'];
                        @endphp
                        {{ $payment_methods[$cfe->payment_method] ?? 'Contado' }}
                    </span>
                </div>
                <div class="data-item">
                    <span class="label">Moneda:</span>
                    <span class="value">{{ $cfe->currency }}@if($cfe->currency !== 'UYU') (TC: {{ $cfe->exchange_rate }})@endif</span>
                </div>
            </div>
            <div class="total-highlight">Total a Pagar: ${{ number_format($cfe->total, 0, ',', '.') }}</div>
        </div>

        <!-- Tabla de Items -->
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 35%">Descripción</th>
                        <th class="text-center" style="width: 12%">Cant.</th>
                        <th style="width: 13%">Unidad</th>
                        <th class="text-right" style="width: 15%">Precio Unit.</th>
                        <th class="text-center" style="width: 10%">IVA</th>
                        <th class="text-right" style="width: 15%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $items = is_array($cfe->items) ? $cfe->items : json_decode($cfe->items, true) ?? []; @endphp
                    @forelse($items as $index => $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item['name'] ?? $item['description'] ?? 'Producto/Servicio' }}</div>
                            <div class="item-code">Código: {{ $item['product_id'] ?? 'COL_' . ($index + 1) }}</div>
                        </td>
                        <td class="text-center">{{ number_format($item['quantity'] ?? 1, 0) }}</td>
                        <td>{{ $item['unit'] ?? 'unidad' }}</td>
                        <td class="text-right">$ {{ number_format($item['unit_price'] ?? 0, 2, ',', '.') }}</td>
                        <td class="text-center">{{ $item['iva_rate'] ?? 0 }}%</td>
                        <td class="text-right">$ {{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0), 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 30px; color: #94a3b8;">No hay items</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totales -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="total-row">
                    <span class="label">Subtotal:</span>
                    <span class="value">$ {{ number_format($cfe->subtotal, 2, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span class="label">Total IVA:</span>
                    <span class="value">$ {{ number_format($cfe->tax_amount, 2, ',', '.') }}</span>
                </div>
                <div class="total-row grand-total">
                    <span class="label">Total:</span>
                    <span class="value">$ {{ number_format($cfe->total, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        @if($cfe->notes)
        <div class="notes-section">
            <div class="notes-title">📝 Observaciones</div>
            <div class="notes-content">{{ $cfe->notes }}</div>
        </div>
        @endif

        <!-- Sección Fiscal DGI -->
        <div class="fiscal-section">
            <div class="fiscal-info">
                <div class="fiscal-header">
                    <div class="fiscal-icon">DGI</div>
                    <div>
                        <div class="fiscal-title">Comprobante Fiscal Electrónico</div>
                        <div class="fiscal-subtitle">Dirección General Impositiva - Uruguay</div>
                    </div>
                </div>
                <div class="fiscal-grid">
                    <div class="fiscal-item">
                        <div class="fiscal-item-label">Tipo de CFE</div>
                        <div class="fiscal-item-value">{{ $cfe->cfe_type }} - {{ $cfe_types[$cfe->cfe_type] ?? '' }}</div>
                    </div>
                    <div class="fiscal-item">
                        <div class="fiscal-item-label">Estado</div>
                        <div class="fiscal-item-value">
                            <span class="status-badge status-sent">📤 Enviado</span>
                        </div>
                    </div>
                    @if($cfe->cae)
                    <div class="fiscal-item">
                        <div class="fiscal-item-label">CAE</div>
                        <div class="fiscal-item-value" style="font-family: monospace; letter-spacing: 1px;">{{ $cfe->cae }}</div>
                    </div>
                    @endif
                    @if($cfe->track_id)
                    <div class="fiscal-item">
                        <div class="fiscal-item-label">Track ID</div>
                        <div class="fiscal-item-value" style="font-family: monospace;">{{ $cfe->track_id }}</div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="qr-section">
                <div class="qr-code" id="qrcode"></div>
                <div class="qr-text">
                    Escanee para verificar<br>
                    en <strong>www.efactura.dgi.gub.uy</strong>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-content">
                <div class="footer-legal">
                    Este documento es un <strong>Comprobante Fiscal Electrónico</strong> válido según normativa DGI Uruguay.<br>
                    Puede verificar su autenticidad en <strong>www.efactura.dgi.gub.uy</strong>
                </div>
                <div class="footer-timestamp">
                    Documento generado el {{ now()->format('d/m/Y H:i:s') }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
            
            var img = document.createElement('img');
            img.src = qr.createDataURL(4);
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.display = 'block';
            
            document.getElementById('qrcode').appendChild(img);
        });
    </script>
</body>
</html>
