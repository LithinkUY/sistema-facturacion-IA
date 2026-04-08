<!DOCTYPE html><!DOCTYPE html><!DOCTYPE html>

<html lang="es">

<head><html lang="es"><html lang="es">

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0"><head><head>

    <title>{{ $cfe_types[$cfe->cfe_type] ?? 'CFE' }} {{ $cfe->series }}-{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}</title>

    <style>    <meta charset="UTF-8">    <meta charset="UTF-8">

        /* ---- ESTILOS OFICIALES DGI ---- */

        * { margin: 0; padding: 0; box-sizing: border-box; }    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <meta name="viewport" content="width=device-width, initial-scale=1.0">



        body {    <title>{{ $cfe_types[$cfe->cfe_type] ?? 'CFE' }} {{ $cfe->series }}-{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}</title>    <title>{{ $cfe_types[$cfe->cfe_type] ?? 'CFE' }} {{ $cfe->series }}-{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}</title>

            font-family: Arial, Helvetica, sans-serif;

            font-size: 10px;    <style>    <style>

            color: #000;

            background: #ccc;        /* ======== ESTILOS FACTURA DGI URUGUAY - FORMATO OFICIAL ======== */        /* ---- ESTILOS OFICIALES DGI ---- */

        }

        

        @media print {

            body { background: #fff; margin: 0; }        :root {        * { margin: 0; padding: 0; box-sizing: border-box; }

            .invoice-wrap { width: 100%; margin: 0; box-shadow: none; border: none; }

            .no-print { display: none !important; }            --dgi-blue: #003366;

            @page { size: A4; margin: 8mm; }

        }            --dgi-blue-light: #004d99;        body {



        /* Contenedor principal */            --dgi-blue-pale: #e8f0fa;            font-family: Arial, Helvetica, sans-serif;

        .invoice-wrap {

            width: 210mm;            --dgi-gray: #666;            font-size: 10px;

            min-height: 297mm;

            margin: 10px auto;            --dgi-gray-light: #f0f0f0;            color: #000;

            background: #fff;

            box-shadow: 0 2px 10px rgba(0,0,0,0.3);            --dgi-border: #999;            background: #ccc;

            padding: 8mm 10mm;

            position: relative;            --dgi-text: #222;        }

        }

        }

        /* ---- CABECERA PRINCIPAL ---- */

        .cfe-header {        @media print {

            display: table;

            width: 100%;        * { margin: 0; padding: 0; box-sizing: border-box; }            body { background: #fff; margin: 0; }

            border-bottom: 2px solid #003366;

            padding-bottom: 8px;            .invoice-wrap { width: 100%; margin: 0; box-shadow: none; border: none; }

            margin-bottom: 10px;

        }        body {            .no-print { display: none !important; }

        .header-left {

            display: table-cell;            font-family: Arial, Helvetica, sans-serif;            @page { size: A4; margin: 8mm; }

            vertical-align: top;

            width: 60%;            font-size: 10px;        }

        }

        .header-right {            color: var(--dgi-text);

            display: table-cell;

            vertical-align: top;            background: #bbb;        /* Contenedor principal */

            width: 40%;

            text-align: center;        }        .invoice-wrap {

        }

            width: 210mm;

        /* Logo y datos emisor */

        .emisor-name {        @media print {            min-height: 297mm;

            font-size: 16px;

            font-weight: bold;            body { background: #fff; margin: 0; }            margin: 10px auto;

            color: #003366;

            margin-bottom: 4px;            .invoice-wrap { width: 100%; margin: 0; box-shadow: none; }            background: #fff;

        }

        .emisor-info {            .no-print { display: none !important; }            box-shadow: 0 2px 10px rgba(0,0,0,0.3);

            font-size: 9px;

            color: #444;            @page { size: A4; margin: 8mm; }            padding: 8mm 10mm;

            line-height: 1.5;

        }        }            position: relative;



        /* Cuadro tipo documento */        }

        .doc-box {

            border: 2px solid #003366;        /* Contenedor principal A4 */

            padding: 0;

            text-align: center;        .invoice-wrap {        /* ---- CABECERA PRINCIPAL ---- */

        }

        .doc-box .rut-bar {            width: 210mm;        .cfe-header {

            background: #003366;

            color: #fff;            min-height: 297mm;            display: table;

            padding: 4px 8px;

            font-size: 11px;            margin: 10px auto;            width: 100%;

            font-weight: bold;

            letter-spacing: 1px;            background: #fff;            margin-bottom: 6px;

        }

        .doc-box .cfe-type-label {            box-shadow: 0 2px 12px rgba(0,0,0,0.3);        }

            padding: 6px 4px;

            font-size: 11px;            padding: 8mm 10mm;        .cfe-header-left {

            font-weight: bold;

            color: #003366;            position: relative;            display: table-cell;

            border-bottom: 1px solid #003366;

        }        }            width: 45%;

        .doc-box .series-num {

            display: table;            vertical-align: top;

            width: 100%;

        }        /* ---- CABECERA PRINCIPAL ---- */            padding-right: 10px;

        .doc-box .series-num .cell {

            display: table-cell;        .cfe-header {        }

            width: 50%;

            padding: 4px;            display: table;        .cfe-header-right {

            font-size: 10px;

            text-align: center;            width: 100%;            display: table-cell;

        }

        .doc-box .series-num .cell-label {            margin-bottom: 8px;            width: 55%;

            background: #e8f0fa;

            font-weight: bold;            border-bottom: 3px solid var(--dgi-blue);            vertical-align: top;

            color: #003366;

        }            padding-bottom: 8px;            text-align: right;



        /* ---- DATOS DEL RECEPTOR ---- */        }        }

        .receptor-section {

            border: 1px solid #999;        .cfe-header-left {

            margin-bottom: 10px;

        }            display: table-cell;        /* Bloque emisor (izquierda) */

        .receptor-title {

            background: #003366;            width: 50%;        .emisor-name {

            color: #fff;

            padding: 4px 8px;            vertical-align: top;            font-size: 13px;

            font-size: 10px;

            font-weight: bold;            padding-right: 12px;            font-weight: bold;

        }

        .receptor-body {        }            margin-bottom: 2px;

            padding: 6px 8px;

            display: table;        .cfe-header-right {        }

            width: 100%;

        }            display: table-cell;        .emisor-details {

        .receptor-body .col {

            display: table-cell;            width: 50%;            font-size: 10px;

            vertical-align: top;

            width: 50%;            vertical-align: top;            line-height: 1.6;

            padding-right: 10px;

        }        }        }

        .receptor-body .field {

            margin-bottom: 3px;

            font-size: 9px;

        }        /* Logo y emisor */        /* Bloque RUC/Tipo/Serie (derecha) */

        .receptor-body .field-label {

            font-weight: bold;        .emisor-logo {        .doc-box {

            color: #003366;

        }            max-height: 55px;            border: 1px solid #000;



        /* ---- TABLA DE CONCEPTOS ---- */            max-width: 170px;            display: inline-block;

        .items-table {

            width: 100%;            display: block;            min-width: 180px;

            border-collapse: collapse;

            margin-bottom: 10px;            margin-bottom: 6px;            text-align: center;

        }

        .items-table th {        }            margin-bottom: 4px;

            background: #003366;

            color: #fff;        .emisor-name {        }

            padding: 5px 4px;

            font-size: 9px;            font-size: 16px;        .doc-box-ruc {

            text-align: center;

            font-weight: bold;            font-weight: bold;            font-size: 13px;

            border: 1px solid #003366;

        }            color: var(--dgi-blue);            font-weight: bold;

        .items-table td {

            padding: 4px;            margin-bottom: 3px;            padding: 3px 8px;

            font-size: 9px;

            border: 1px solid #ccc;        }            border-bottom: 1px solid #000;

            text-align: right;

        }        .emisor-details {        }

        .items-table td.text-left { text-align: left; }

        .items-table td.text-center { text-align: center; }            font-size: 9.5px;        .doc-box-tipo {

        .items-table tr:nth-child(even) { background: #e8f0fa; }

            line-height: 1.7;            font-size: 11px;

        /* ---- RESUMEN IVA ---- */

        .iva-section {            color: #444;            font-weight: bold;

            display: table;

            width: 100%;        }            padding: 3px 8px;

            margin-bottom: 10px;

        }        .emisor-details strong {            border-bottom: 1px solid #000;

        .iva-box {

            display: table-cell;            color: var(--dgi-text);            background: #f5f5f5;

            vertical-align: top;

        }        }        }

        .iva-table {

            width: 100%;        .doc-box-subtipo {

            border-collapse: collapse;

            font-size: 9px;        /* Bloque RUC/Tipo CFE (derecha) */            font-size: 10px;

        }

        .iva-table th {        .doc-box {            padding: 2px 8px;

            background: #003366;

            color: #fff;            border: 2px solid var(--dgi-blue);        }

            padding: 4px;

            text-align: center;            display: block;

            font-size: 8px;

            border: 1px solid #003366;            width: 100%;        /* Tabla Serie/Número/Moneda */

        }

        .iva-table td {            text-align: center;        .serie-table {

            padding: 4px;

            text-align: right;            margin-bottom: 6px;            width: 100%;

            border: 1px solid #ccc;

        }        }            border-collapse: collapse;



        /* Total general */        .doc-box-ruc {            border: 1px solid #000;

        .total-section {

            display: table;            font-size: 13px;            margin-top: 4px;

            width: 100%;

            margin-bottom: 12px;            font-weight: bold;            font-size: 10px;

        }

        .total-left {            padding: 5px 8px;        }

            display: table-cell;

            vertical-align: middle;            background: var(--dgi-blue);        .serie-table th {

            width: 65%;

        }            color: #fff;            background: #e0e0e0;

        .total-box {

            display: table-cell;        }            border: 1px solid #000;

            vertical-align: middle;

            width: 35%;        .doc-box-tipo {            padding: 2px 5px;

            text-align: right;

            background: #e8f0fa;            font-size: 12px;            font-weight: bold;

            border: 2px solid #003366;

            padding: 8px 12px;            font-weight: bold;            text-align: center;

        }

        .total-label {            padding: 4px 8px;        }

            font-size: 12px;

            font-weight: bold;            background: var(--dgi-blue-pale);        .serie-table td {

            color: #003366;

        }            color: var(--dgi-blue);            border: 1px solid #000;

        .total-amount {

            font-size: 18px;            border-top: 1px solid var(--dgi-blue);            padding: 2px 5px;

            font-weight: bold;

            color: #003366;            border-bottom: 1px solid var(--dgi-blue);            text-align: center;

        }

        }        }

        /* ---- FOOTER: CAE + QR ---- */

        .cfe-footer {        .doc-box-subtipo {

            display: table;

            width: 100%;            font-size: 10px;        /* Tabla de fechas */

            border-top: 2px solid #003366;

            padding-top: 8px;            padding: 3px 8px;        .fechas-table {

            margin-top: 10px;

        }            color: #333;            width: 100%;

        .footer-left {

            display: table-cell;        }            border-collapse: collapse;

            vertical-align: top;

            width: 65%;            border: 1px solid #000;

            padding-right: 15px;

        }        /* Tabla Serie/Número */            margin-top: 3px;

        .footer-right {

            display: table-cell;        .info-table {            font-size: 10px;

            vertical-align: top;

            width: 35%;            width: 100%;        }

            text-align: center;

        }            border-collapse: collapse;        .fechas-table th {

        .cae-info {

            font-size: 9px;            border: 1px solid var(--dgi-border);            background: #e0e0e0;

            line-height: 1.6;

        }            font-size: 10px;            border: 1px solid #000;

        .cae-info .cae-label {

            font-weight: bold;            margin-top: 5px;            padding: 2px 5px;

            color: #003366;

        }        }            font-weight: bold;

        .dgi-text {

            font-size: 8px;        .info-table th {            text-align: center;

            color: #666;

            margin-top: 4px;            background: var(--dgi-blue);            font-size: 9px;

            line-height: 1.4;

        }            color: #fff;        }

        .qr-box {

            border: 1px solid #999;            border: 1px solid var(--dgi-blue);        .fechas-table td {

            padding: 6px;

            display: inline-block;            padding: 3px 6px;            border: 1px solid #000;

        }

        .qr-label {            font-weight: bold;            padding: 2px 5px;

            font-size: 7px;

            color: #666;            text-align: center;            text-align: center;

            margin-top: 3px;

        }            font-size: 9px;        }



        /* Sello DGI */            text-transform: uppercase;

        .dgi-sello {

            text-align: center;        }        /* ---- SECCION RUC COMPRADOR / CLIENTE ---- */

            margin-top: 10px;

            padding-top: 6px;        .info-table td {        .comprador-section {

            border-top: 1px dashed #999;

            font-size: 8px;            border: 1px solid var(--dgi-border);            display: table;

            color: #666;

        }            padding: 3px 6px;            width: 100%;



        /* Boton imprimir */            text-align: center;            border-collapse: collapse;

        .no-print {

            text-align: center;            font-weight: bold;            border: 1px solid #000;

            margin: 12px auto;

        }        }            margin: 6px 0;

        .btn-print {

            background: #003366;        }

            color: #fff;

            border: none;        /* ---- SECCION RECEPTOR / COMPRADOR ---- */        .comprador-cell {

            padding: 10px 30px;

            font-size: 14px;        .receptor-section {            display: table-cell;

            cursor: pointer;

            border-radius: 4px;            border: 1px solid var(--dgi-border);            width: 50%;

        }

        .btn-print:hover { background: #004d99; }            margin: 8px 0 0 0;            vertical-align: top;



        /* Info adicional */        }            padding: 4px 6px;

        .info-row {

            display: table;        .receptor-title {            border-right: 1px solid #000;

            width: 100%;

            margin-bottom: 8px;            background: var(--dgi-blue);        }

        }

        .info-row .cell {            color: #fff;        .cliente-cell {

            display: table-cell;

            vertical-align: top;            padding: 3px 8px;            display: table-cell;

            padding: 0 4px;

        }            font-size: 10px;            width: 50%;

        .info-label {

            font-weight: bold;            font-weight: bold;            vertical-align: top;

            color: #003366;

            font-size: 9px;            text-transform: uppercase;            padding: 4px 6px;

        }

        .info-value {        }        }

            font-size: 9px;

        }        .receptor-grid {        .comprador-label {

    </style>

</head>            display: table;            font-size: 9px;

<body>

            width: 100%;            font-weight: bold;

@php

    /* -------- DATOS DEL EMISOR -------- */        }            text-transform: uppercase;

    $location_has_own_rut = isset($location) && $location

        && !empty($location->location_id)        .receptor-cell {            color: #444;

        && !empty($location->custom_field1);

            display: table-cell;            margin-bottom: 1px;

    $emitter_rut = $cfe->emitter_rut

        ?: ($cfe_settings['cfe_emitter_rut'] ?? '');            width: 50%;        }

    $emitter_name = $cfe->emitter_name

        ?: ($business->name ?? '');            vertical-align: top;        .comprador-value {

    $emitter_address = $cfe->emitter_address

        ?: ($location->city ?? '') . ' - ' . ($location->state ?? '') . ', ' . ($location->country ?? 'Uruguay');            padding: 5px 8px;            font-size: 12px;



    if ($location_has_own_rut) {            border-right: 1px solid #ddd;            font-weight: bold;

        $emitter_rut = $location->custom_field1;

    }        }        }



    /* -------- ITEMS -------- */        .receptor-cell:last-child {        .cliente-label {

    $items = $cfe->items;

    if (is_string($items)) {            border-right: none;            font-size: 9px;

        $items = json_decode($items, true);

    }        }            font-weight: bold;

    if (!is_array($items)) {

        $items = [];        .receptor-label {            text-transform: uppercase;

    }

            font-size: 8px;            color: #444;

    /* -------- TOTALES IVA -------- */

    $subtotal_no_grav = 0;            font-weight: bold;            margin-bottom: 1px;

    $subtotal_basica  = 0;

    $iva_basica       = 0;            text-transform: uppercase;        }

    $subtotal_minima  = 0;

    $iva_minima       = 0;            color: var(--dgi-gray);        .cliente-value {



    foreach ($items as $item) {            margin-bottom: 1px;            font-size: 11px;

        $line_total = ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);

        $discount_amount = 0;        }            font-weight: bold;

        if (!empty($item['discount_percent'])) {

            $discount_amount = $line_total * ($item['discount_percent'] / 100);        .receptor-value {        }

        } elseif (!empty($item['discount_amount'])) {

            $discount_amount = $item['discount_amount'];            font-size: 11px;

        }

        $line_total -= $discount_amount;            font-weight: bold;        /* ---- DOMICILIO FISCAL ---- */



        $tax_rate = $item['tax_rate'] ?? 0;            color: var(--dgi-text);        .domicilio-section {

        if ($tax_rate >= 20) {

            $subtotal_basica += $line_total;        }            border: 1px solid #000;

            $iva_basica += $line_total * 0.22;

        } elseif ($tax_rate >= 8) {            border-top: none;

            $subtotal_minima += $line_total;

            $iva_minima += $line_total * 0.10;        /* Domicilio fiscal */            padding: 3px 6px;

        } else {

            $subtotal_no_grav += $line_total;        .domicilio-section {            margin-bottom: 6px;

        }

    }            border: 1px solid var(--dgi-border);        }



    /* Tipo de CFE legible */            border-top: none;        .domicilio-label {

    $cfe_type_name = $cfe_types[$cfe->cfe_type] ?? 'CFE';

    $cfe_number_formatted = str_pad($cfe->number, 7, '0', STR_PAD_LEFT);            padding: 4px 8px;            font-size: 9px;

@endphp

            margin-bottom: 0;            font-weight: bold;

<!-- Boton imprimir -->

<div class="no-print">        }            color: #444;

    <button class="btn-print" onclick="window.print()">Imprimir Factura</button>

</div>        .domicilio-table {            display: inline;



<div class="invoice-wrap">            width: 100%;        }



    <!-- ======== CABECERA ======== -->            border-collapse: collapse;        .domicilio-value {

    <div class="cfe-header">

        <div class="header-left">            font-size: 9px;            font-size: 10px;

            @if($business->logo)

                <img src="{{ asset('uploads/business_logos/' . $business->logo) }}" alt="Logo" style="max-height:55px; margin-bottom:5px;">        }            display: inline;

            @endif

            <div class="emisor-name">{{ $emitter_name }}</div>        .domicilio-table th {            margin-left: 4px;

            <div class="emisor-info">

                @if(!empty($emitter_address))            color: var(--dgi-gray);        }

                    {{ $emitter_address }}<br>

                @endif            font-weight: bold;        .domicilio-table {

                @if(!empty($location->mobile))

                    Tel: {{ $location->mobile }}<br>            padding: 2px 4px;            width: 100%;

                @endif

                @if(!empty($location->email))            text-align: left;            border-collapse: collapse;

                    {{ $location->email }}<br>

                @endif            font-size: 8px;            border-top: 1px solid #ccc;

                @if(!empty($location->website))

                    {{ $location->website }}            text-transform: uppercase;            margin-top: 3px;

                @endif

            </div>        }            font-size: 9px;

        </div>

        <div class="header-right">        .domicilio-table td {        }

            <div class="doc-box">

                <div class="rut-bar">RUT: {{ $emitter_rut }}</div>            padding: 2px 4px;        .domicilio-table th {

                <div class="cfe-type-label">{{ $cfe_type_name }}</div>

                <div class="series-num">            font-size: 9.5px;            color: #666;

                    <div class="cell cell-label">Serie</div>

                    <div class="cell cell-label">Numero</div>        }            font-weight: bold;

                </div>

                <div class="series-num">            padding: 1px 4px;

                    <div class="cell">{{ $cfe->series }}</div>

                    <div class="cell">{{ $cfe_number_formatted }}</div>        /* ---- TABLA DE CONCEPTOS / DETALLE ---- */            text-align: left;

                </div>

            </div>        .conceptos-table {        }

        </div>

    </div>            width: 100%;        .domicilio-table td {



    <!-- ======== DATOS DE FECHA / MONEDA ======== -->            border-collapse: collapse;            padding: 1px 4px;

    <div class="info-row">

        <div class="cell" style="width:33%;">            border: 1px solid var(--dgi-border);        }

            <span class="info-label">Fecha Emision:</span>

            <span class="info-value">{{ $cfe->issue_date ? $cfe->issue_date->format('d/m/Y') : '—' }}</span>            font-size: 10px;

        </div>

        <div class="cell" style="width:33%;">            margin-top: 8px;        /* ---- TABLA DE CONCEPTOS ---- */

            <span class="info-label">Fecha Vencimiento:</span>

            <span class="info-value">{{ $cfe->due_date ? \Carbon\Carbon::parse($cfe->due_date)->format('d/m/Y') : '—' }}</span>            margin-bottom: 0;        .conceptos-table {

        </div>

        <div class="cell" style="width:33%;">        }            width: 100%;

            <span class="info-label">Moneda:</span>

            <span class="info-value">{{ $cfe->currency ?? 'UYU' }}</span>        .conceptos-table thead tr {            border-collapse: collapse;

        </div>

    </div>            background: var(--dgi-blue);            border: 1px solid #000;



    <!-- ======== DATOS DEL RECEPTOR ======== -->        }            font-size: 10px;

    <div class="receptor-section">

        <div class="receptor-title">DATOS DEL RECEPTOR</div>        .conceptos-table th {            margin-bottom: 0;

        <div class="receptor-body">

            <div class="col">            border: 1px solid var(--dgi-blue);        }

                <div class="field">

                    <span class="field-label">Nombre / Razon Social:</span><br>            padding: 4px 6px;        .conceptos-table thead tr {

                    {{ $cfe->receiver_name ?: ($customer->name ?? '—') }}

                </div>            font-weight: bold;            background: #d0d0d0;

                <div class="field">

                    <span class="field-label">RUT / Cedula:</span><br>            text-align: center;        }

                    {{ $cfe->receiver_document ?: ($customer->tax_number ?? '—') }}

                </div>            font-size: 9px;        .conceptos-table th {

            </div>

            <div class="col">            text-transform: uppercase;            border: 1px solid #000;

                <div class="field">

                    <span class="field-label">Direccion:</span><br>            color: #fff;            padding: 3px 5px;

                    {{ $cfe->receiver_address ?: ($customer->address_line_1 ?? '—') }}

                </div>        }            font-weight: bold;

                <div class="field">

                    <span class="field-label">Ciudad:</span><br>        .conceptos-table tbody tr:nth-child(even) {            text-align: center;

                    {{ $customer->city ?? '—' }}

                </div>            background: var(--dgi-blue-pale);            font-size: 9px;

            </div>

        </div>        }            text-transform: uppercase;

    </div>

        .conceptos-table td {        }

    <!-- ======== TABLA DE CONCEPTOS ======== -->

    <table class="items-table">            border: 1px solid #ccc;        .conceptos-table td {

        <thead>

            <tr>            padding: 4px 6px;            border: 1px solid #000;

                <th style="width:5%;">Nro</th>

                <th style="width:38%;">Descripcion</th>            vertical-align: top;            padding: 3px 5px;

                <th style="width:8%;">Cant.</th>

                <th style="width:12%;">P/Unitario</th>        }            vertical-align: top;

                <th style="width:8%;">Dto.%</th>

                <th style="width:12%;">Dto.$</th>        .conceptos-table td.text-center { text-align: center; }        }

                <th style="width:17%;">Total</th>

            </tr>        .conceptos-table td.text-right { text-align: right; }        .conceptos-table td.text-center { text-align: center; }

        </thead>

        <tbody>        .conceptos-table td.text-right { text-align: right; }

            @forelse($items as $i => $item)

                @php        /* ---- TOTALES ---- */

                    $qty = $item['quantity'] ?? 1;

                    $price = $item['unit_price'] ?? 0;        .totales-section {        /* Fila de totales bajo la tabla */

                    $line_total = $qty * $price;

                    $disc_pct = $item['discount_percent'] ?? 0;            border: 1px solid var(--dgi-border);        .totales-section {

                    $disc_amt = 0;

                    if ($disc_pct > 0) {            border-top: none;            border: 1px solid #000;

                        $disc_amt = $line_total * ($disc_pct / 100);

                    } elseif (!empty($item['discount_amount'])) {            display: table;            border-top: none;

                        $disc_amt = $item['discount_amount'];

                        $disc_pct = $line_total > 0 ? ($disc_amt / $line_total * 100) : 0;            width: 100%;            display: table;

                    }

                    $line_total -= $disc_amt;        }            width: 100%;

                @endphp

                <tr>        .totales-left {        }

                    <td class="text-center">{{ $i + 1 }}</td>

                    <td class="text-left">{{ $item['description'] ?? $item['name'] ?? '—' }}</td>            display: table-cell;        .totales-left {

                    <td class="text-center">{{ $qty }}</td>

                    <td>{{ number_format($price, 2, ',', '.') }}</td>            width: 55%;            display: table-cell;

                    <td class="text-center">{{ $disc_pct > 0 ? number_format($disc_pct, 1) . '%' : '—' }}</td>

                    <td>{{ $disc_amt > 0 ? number_format($disc_amt, 2, ',', '.') : '—' }}</td>            border-right: 1px solid var(--dgi-border);            width: 55%;

                    <td><strong>{{ number_format($line_total, 2, ',', '.') }}</strong></td>

                </tr>            padding: 5px 8px;            border-right: 1px solid #000;

            @empty

                <tr>            font-size: 9px;            padding: 3px 5px;

                    <td colspan="7" class="text-center" style="padding:15px; color:#999;">Sin conceptos</td>

                </tr>            vertical-align: top;            font-size: 9px;

            @endforelse

        </tbody>        }            vertical-align: middle;

    </table>

        .totales-right {        }

    <!-- ======== DESGLOSE IVA ======== -->

    <div class="iva-section">            display: table-cell;        .totales-right {

        <div class="iva-box" style="width:100%;">

            <table class="iva-table">            width: 45%;            display: table-cell;

                <tr>

                    <th>No Gravado</th>        }            width: 45%;

                    <th>T. Basica (22%)</th>

                    <th>IVA T. Basica</th>        .iva-grid {            text-align: right;

                    <th>T. Minima (10%)</th>

                    <th>IVA T. Minima</th>            display: table;        }

                </tr>

                <tr>            width: 100%;

                    <td>{{ number_format($subtotal_no_grav, 2, ',', '.') }}</td>

                    <td>{{ number_format($subtotal_basica, 2, ',', '.') }}</td>            font-size: 8.5px;        .subtotales-table {

                    <td>{{ number_format($iva_basica, 2, ',', '.') }}</td>

                    <td>{{ number_format($subtotal_minima, 2, ',', '.') }}</td>            border-collapse: collapse;            width: 100%;

                    <td>{{ number_format($iva_minima, 2, ',', '.') }}</td>

                </tr>        }            border-collapse: collapse;

            </table>

        </div>        .iva-grid-cell {            font-size: 9px;

    </div>

            display: table-cell;        }

    <!-- ======== TOTAL A PAGAR ======== -->

    <div class="total-section">            text-align: center;        .subtotales-table td {

        <div class="total-left">

            <div class="info-row">            padding: 2px 4px;            padding: 2px 5px;

                <div class="cell">

                    <span class="info-label">Subtotal:</span>            border-right: 1px solid #ddd;            border-bottom: 1px solid #eee;

                    <span class="info-value">{{ number_format($cfe->subtotal ?? ($cfe->total - ($cfe->tax_amount ?? 0)), 2, ',', '.') }}</span>

                </div>        }        }

                <div class="cell">

                    <span class="info-label">IVA:</span>        .iva-grid-cell:last-child {        .subtotales-table tr:last-child td {

                    <span class="info-value">{{ number_format($cfe->tax_amount ?? ($iva_basica + $iva_minima), 2, ',', '.') }}</span>

                </div>            border-right: none;            border-bottom: none;

            </div>

            @if(!empty($cfe->transaction) && $cfe->transaction->additional_notes)        }        }

                <div style="margin-top:5px; font-size:9px; color:#444;">

                    <strong>Observaciones:</strong> {{ $cfe->transaction->additional_notes }}        .iva-grid-label {        .subtotales-table td.label-col {

                </div>

            @endif            font-weight: bold;            color: #555;

        </div>

        <div class="total-box">            color: var(--dgi-gray);        }

            <div class="total-label">TOTAL A PAGAR</div>

            <div class="total-amount">$ {{ number_format($cfe->total, 2, ',', '.') }}</div>            font-size: 7.5px;        .subtotales-table td.value-col {

        </div>

    </div>            text-transform: uppercase;            text-align: right;



    <!-- ======== FOOTER: CAE + QR ======== -->        }            font-weight: bold;

    <div class="cfe-footer">

        <div class="footer-left">        }

            <div class="cae-info">

                <span class="cae-label">CAE N°:</span> {{ $cfe->cae ?? '—' }}<br>        .subtotales-table {

                <span class="cae-label">Vencimiento CAE:</span>

                {{ $cfe->cae_due_date ? \Carbon\Carbon::parse($cfe->cae_due_date)->format('d/m/Y') : '—' }}<br>            width: 100%;        /* Bloque TOTAL FACTURA */

                @if($cfe->security_code)

                    <span class="cae-label">Codigo Seguridad:</span> {{ $cfe->security_code }}<br>            border-collapse: collapse;        .total-factura-box {

                @endif

                <span class="cae-label">Resolucion DGI:</span> N 798/012 y N 2.859/015            font-size: 9.5px;            border-top: 1px solid #000;

            </div>

            <div class="dgi-text">        }            border-left: 1px solid #ccc;

                Comprobante Fiscal Electronico emitido segun normativa de la<br>

                Direccion General Impositiva (DGI) - Republica Oriental del Uruguay.<br>        .subtotales-table td {            padding: 4px 8px;

                Consulte la validez en: <strong>www.efactura.dgi.gub.uy</strong>

            </div>            padding: 3px 8px;            display: flex;

        </div>

        <div class="footer-right">            border-bottom: 1px solid #eee;            justify-content: space-between;

            @if(!empty($qr_data))

                <div class="qr-box">        }            font-size: 10px;

                    {!! DNS2D::getBarcodeSVG($qr_data, 'QRCODE', 3, 3, '#003366') !!}

                </div>        .subtotales-table tr:last-child td {            font-weight: bold;

                <div class="qr-label">Escanee para verificar en DGI</div>

            @else            border-bottom: none;            background: #f5f5f5;

                <div style="padding:10px; border:1px dashed #999; color:#999; font-size:8px;">

                    QR no disponible        }        }

                </div>

            @endif        .subtotales-table td.label-col {

        </div>

    </div>            color: var(--dgi-gray);        /* ---- TOTAL A PAGAR ---- */



    <!-- ======== SELLO DGI ======== -->            font-size: 9px;        .total-pagar-box {

    <div class="dgi-sello">

        Documento emitido de acuerdo a las disposiciones de la DGI — www.dgi.gub.uy        }            border: 2px solid #000;

    </div>

        .subtotales-table td.value-col {            text-align: right;

</div>

            text-align: right;            padding: 4px 8px;

</body>

</html>            font-weight: bold;            margin-top: 6px;


        }            display: flex;

            justify-content: space-between;

        /* TOTAL A PAGAR */            font-size: 12px;

        .total-pagar-box {            font-weight: bold;

            border: 2px solid var(--dgi-blue);        }

            margin-top: 8px;

            padding: 6px 10px;        /* ---- FOOTER DGI ---- */

            display: table;        .dgi-footer {

            width: 100%;            margin-top: 20px;

            background: var(--dgi-blue-pale);            border-top: 1px solid #aaa;

        }            padding-top: 6px;

        .total-pagar-label {            font-size: 9px;

            display: table-cell;            color: #333;

            font-size: 13px;        }

            font-weight: bold;        .dgi-footer-grid {

            color: var(--dgi-blue);            display: table;

            vertical-align: middle;            width: 100%;

        }        }

        .total-pagar-value {        .dgi-footer-left {

            display: table-cell;            display: table-cell;

            text-align: right;            vertical-align: top;

            font-size: 16px;            width: 65%;

            font-weight: bold;            line-height: 1.7;

            color: var(--dgi-blue);        }

        }        .dgi-footer-right {

            display: table-cell;

        /* Condición de pago */            vertical-align: bottom;

        .pago-info {            width: 35%;

            margin-top: 6px;            text-align: right;

            font-size: 9.5px;        }

            border: 1px solid #ddd;        .cae-box {

            padding: 4px 8px;            border: 1px solid #aaa;

        }            padding: 3px 8px;

        .pago-info strong {            display: inline-block;

            color: var(--dgi-blue);            text-align: center;

        }            font-size: 9px;

        }

        /* Observaciones */        .cae-box .cae-title {

        .observaciones {            font-size: 8px;

            border: 1px solid #ddd;            color: #666;

            padding: 5px 8px;        }

            margin-top: 6px;        .cae-box .cae-value {

            font-size: 9.5px;            font-weight: bold;

        }        }

        .observaciones strong {

            color: var(--dgi-blue);        /* Botones no-print */

        }        .actions {

            text-align: center;

        /* ---- FOOTER DGI ---- */            padding: 12px;

        .dgi-footer {            background: #fff;

            margin-top: 16px;            margin: 10px auto;

            border-top: 3px solid var(--dgi-blue);            max-width: 210mm;

            padding-top: 8px;        }

        }        .actions .btn {

        .dgi-footer-grid {            display: inline-block;

            display: table;            padding: 8px 18px;

            width: 100%;            margin: 4px;

        }            font-size: 12px;

        .dgi-footer-left {            cursor: pointer;

            display: table-cell;            border: 1px solid #ccc;

            vertical-align: top;            border-radius: 4px;

            width: 60%;            text-decoration: none;

            font-size: 9px;            background: #f5f5f5;

            line-height: 1.8;            color: #333;

            color: #444;        }

        }        .actions .btn-primary { background: #1a56db; color: #fff; border-color: #1a56db; }

        .dgi-footer-left strong {        .actions .btn-success { background: #057a55; color: #fff; border-color: #057a55; }

            color: var(--dgi-text);    </style>

        }</head>

        .dgi-footer-right {<body>

            display: table-cell;

            vertical-align: top;@php

            width: 40%;    /* -------- DATOS DEL EMISOR -------- */

            text-align: center;    $location_has_own_rut = isset($location) && $location

        }        && !empty($location->location_id)

        && $location->location_id !== $business->tax_number_1;

        /* CAE box */

        .cae-box {    $display_company_name = $location_has_own_rut ? $location->name : ($cfe->emitter_name ?? $business->name);

            border: 2px solid var(--dgi-blue);

            padding: 5px 10px;    $location_logo = ($location_has_own_rut && !empty($location->custom_field3)

            display: inline-block;        && file_exists(public_path('uploads/invoice_logos/' . $location->custom_field3)))

            text-align: center;        ? asset('uploads/invoice_logos/' . $location->custom_field3) : null;

            margin-bottom: 8px;    $business_logo = $business->logo ? asset('uploads/business_logos/' . $business->logo) : null;

        }    $display_logo  = $location_logo ?? ($location_has_own_rut ? null : $business_logo);

        .cae-box .cae-title {

            font-size: 8px;    // RUT emisor

            color: var(--dgi-gray);    $emitterRut = '';

            text-transform: uppercase;    if (!empty($cfe->emitter_rut)) $emitterRut = $cfe->emitter_rut;

        }    elseif ($location_has_own_rut) $emitterRut = $location->location_id;

        .cae-box .cae-value {    elseif (!empty($business->tax_number_1)) $emitterRut = $business->tax_number_1;

            font-weight: bold;

            font-size: 10px;    // Dirección emisor

            color: var(--dgi-blue);    $emitterAddress = $cfe->emitter_address ?? ($location->landmark ?? $location->name ?? '');

        }    $emitterCity    = $cfe->emitter_city    ?? ($location->city  ?? 'Montevideo');

    $emitterDept    = $cfe->emitter_department ?? ($location->state ?? 'Montevideo');

        /* QR Code */    $emitterPhone   = isset($location) && $location ? ($location->mobile ?? '') : '';

        .qr-section {

            margin-top: 6px;    /* -------- DATOS DEL RECEPTOR -------- */

            text-align: center;    $clientName = '';

        }    if (!empty($cfe->receiver_name)) $clientName = $cfe->receiver_name;

        .qr-section img {    elseif (isset($customer) && $customer) {

            display: inline-block;        $clientName = $customer->name

        }            ?? trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''))

        .qr-label {            ?: ($customer->supplier_business_name ?? '');

            font-size: 7.5px;    }

            color: var(--dgi-gray);    if (empty($clientName)) $clientName = 'Consumidor Final';

            margin-top: 3px;

        }    $clientAddress = $cfe->receiver_address ?? ($customer->address_line_1 ?? $customer->landmark ?? '');

    $clientCity    = $cfe->receiver_city    ?? ($customer->city  ?? '');

        /* Sello DGI */    $clientDept    = $cfe->receiver_department ?? ($customer->state ?? '');

        .dgi-sello {    $clientDoc     = $cfe->receiver_document ?? ($customer->tax_number ?? $customer->custom_field1 ?? '');

            margin-top: 8px;    $clientDocType = $cfe->receiver_doc_type ?? 'RUT';

            padding: 4px 8px;    $clientCountry = 'Uruguay';

            background: var(--dgi-blue-pale);

            border: 1px solid var(--dgi-blue);    /* -------- ITEMS -------- */

            font-size: 8px;    $items = is_array($cfe->items) ? $cfe->items : json_decode($cfe->items, true) ?? [];

            text-align: center;

            color: var(--dgi-blue);    /* -------- TIPO CFE -------- */

            font-weight: bold;    $tipoCfe = $cfe_types[$cfe->cfe_type] ?? 'CFE';

        }    // "e-Factura" → "e-Factura", "e-Remito" → "e-Remito", etc.

    $tipoLabel = $tipoCfe;

        /* Botones acción (no-print) */

        .actions {    /* -------- PAGO -------- */

            text-align: center;    $payment_methods = [1=>'Contado',2=>'Crédito',3=>'Contra Entrega',4=>'Cheque',5=>'Transferencia',6=>'Débito',7=>'Crédito',8=>'Mercado Pago',9=>'Otro'];

            padding: 12px;    $condPago = $payment_methods[$cfe->payment_method] ?? 'Contado';

            background: #fff;@endphp

            margin: 10px auto;

            max-width: 210mm;    <div class="actions no-print">

        }        <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir</button>

        .actions .btn {        <a href="{{ route('cfe.print', $cfe->id) }}?format=ticket" class="btn">🎫 Ticket 80mm</a>

            display: inline-block;        <a href="{{ route('cfe.download-xml', $cfe->id) }}" class="btn btn-success">📥 XML</a>

            padding: 8px 18px;        <a href="{{ route('cfe.show', $cfe->id) }}" class="btn">← Volver</a>

            margin: 4px;    </div>

            font-size: 12px;

            cursor: pointer;    <div class="invoice-wrap">

            border: 1px solid #ccc;

            border-radius: 4px;        {{-- ======== CABECERA ======== --}}

            text-decoration: none;        <div class="cfe-header">

            background: #f5f5f5;            {{-- IZQUIERDA: Datos del emisor --}}

            color: #333;            <div class="cfe-header-left">

        }                @if($display_logo)

        .actions .btn-primary { background: var(--dgi-blue); color: #fff; border-color: var(--dgi-blue); }                    <img src="{{ $display_logo }}" alt="Logo" style="max-height:50px; max-width:160px; display:block; margin-bottom:5px;">

        .actions .btn-success { background: #057a55; color: #fff; border-color: #057a55; }                @endif

    </style>                <div class="emisor-name">{{ $display_company_name }}</div>

</head>                <div class="emisor-details">

<body>                    Tel.: {{ $emitterPhone ?: '-' }}<br>

                    {{ $emitterAddress ?: '-' }}<br>

@php                    {{ $emitterCity }}<br>

    /* -------- DATOS DEL EMISOR -------- */                    {{ $emitterDept }} Sucursal:<br>

    $location_has_own_rut = isset($location) && $location                    {{ isset($location) ? ($location->name ?? '') : '' }}

        && !empty($location->location_id)                </div>

        && $location->location_id !== $business->tax_number_1;            </div>



    $display_company_name = $location_has_own_rut ? $location->name : ($cfe->emitter_name ?? $business->name);            {{-- DERECHA: Bloque documental estilo DGI --}}

            <div class="cfe-header-right">

    $location_logo = ($location_has_own_rut && !empty($location->custom_field3)                <div class="doc-box">

        && file_exists(public_path('uploads/invoice_logos/' . $location->custom_field3)))                    <div class="doc-box-ruc">R.U.C.<br>{{ $emitterRut }}</div>

        ? asset('uploads/invoice_logos/' . $location->custom_field3) : null;                    <div class="doc-box-tipo">Tipo CFE</div>

    $business_logo = $business->logo ? asset('uploads/business_logos/' . $business->logo) : null;                    <div class="doc-box-subtipo">{{ $tipoLabel }}</div>

    $display_logo  = $location_logo ?? ($location_has_own_rut ? null : $business_logo);                </div>



    // RUT emisor                <table class="serie-table">

    $emitterRut = '';                    <tr>

    if (!empty($cfe->emitter_rut)) $emitterRut = $cfe->emitter_rut;                        <th>Serie</th>

    elseif ($location_has_own_rut) $emitterRut = $location->location_id;                        <th>Número</th>

    elseif (!empty($business->tax_number_1)) $emitterRut = $business->tax_number_1;                        <th>Moneda</th>

                    </tr>

    // Dirección emisor                    <tr>

    $emitterAddress = $cfe->emitter_address ?? ($location->landmark ?? $location->name ?? '');                        <td>{{ $cfe->series }}</td>

    $emitterCity    = $cfe->emitter_city    ?? ($location->city  ?? 'Montevideo');                        <td>{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}</td>

    $emitterDept    = $cfe->emitter_department ?? ($location->state ?? 'Montevideo');                        <td>{{ $cfe->currency }}</td>

    $emitterPhone   = isset($location) && $location ? ($location->mobile ?? '') : '';                    </tr>

    $emitterEmail   = isset($location) && $location ? ($location->email ?? '') : '';                </table>



    /* -------- DATOS DEL RECEPTOR -------- */                <table class="fechas-table">

    $clientName = '';                    <tr>

    if (!empty($cfe->receiver_name)) $clientName = $cfe->receiver_name;                        <th>Período Facturación</th>

    elseif (isset($customer) && $customer) {                        <th>Fecha de Comprobante</th>

        $clientName = $customer->name                        <th>Fecha Vencimiento</th>

            ?? trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''))                    </tr>

            ?: ($customer->supplier_business_name ?? '');                    <tr>

    }                        <td>{{ $cfe->issue_date->format('d-m-Y') }}</td>

    if (empty($clientName)) $clientName = 'Consumidor Final';                        <td>{{ $cfe->issue_date->format('d-m-Y') }}</td>

                        <td>{{ $cfe->due_date ? $cfe->due_date->format('d-m-Y') : $cfe->issue_date->format('d-m-Y') }}</td>

    $clientAddress = $cfe->receiver_address ?? ($customer->address_line_1 ?? $customer->landmark ?? '');                    </tr>

    $clientCity    = $cfe->receiver_city    ?? ($customer->city  ?? '');                </table>

    $clientDept    = $cfe->receiver_department ?? ($customer->state ?? '');            </div>

    $clientDoc     = $cfe->receiver_document ?? ($customer->tax_number ?? $customer->custom_field1 ?? '');        </div>

    $clientDocType = $cfe->receiver_doc_type ?? 'RUT';

    $clientCountry = 'Uruguay';        {{-- ======== RUC COMPRADOR + CLIENTE ======== --}}

        <div class="comprador-section">

    /* -------- ITEMS -------- */            <div class="comprador-cell">

    $items = is_array($cfe->items) ? $cfe->items : json_decode($cfe->items, true) ?? [];                <div class="comprador-label">RUC Comprador</div>

                <div class="comprador-value">{{ $clientDoc ?: '-' }}</div>

    /* -------- TIPO CFE -------- */            </div>

    $tipoCfe = $cfe_types[$cfe->cfe_type] ?? 'CFE';            <div class="cliente-cell">

    $tipoLabel = $tipoCfe;                <div class="cliente-label">Cliente</div>

                <div class="cliente-value">{{ $clientName }}</div>

    /* -------- PAGO -------- */            </div>

    $payment_methods = [1=>'Contado',2=>'Crédito',3=>'Contra Entrega',4=>'Cheque',5=>'Transferencia Bancaria',6=>'Débito',7=>'Crédito',8=>'Mercado Pago',9=>'Otro'];        </div>

    $condPago = $payment_methods[$cfe->payment_method] ?? 'Contado';

        {{-- ======== DOMICILIO FISCAL ======== --}}

    /* -------- CALCULO TOTALES IVA -------- */        <div class="domicilio-section">

    $subtotalNoGravado = 0;            <span class="domicilio-label">Domicilio Fiscal</span>

    $subtotalBasica = 0;            <span class="domicilio-value">{{ $clientAddress ?: '-' }}</span>

    $subtotalMinima = 0;            <table class="domicilio-table">

    $ivaBasica = 0;                <tr>

    $ivaMinima = 0;                    <th>Localidad</th>

                    <th>Departamento</th>

    foreach ($items as $item) {                    <th>CP</th>

        $qty = floatval($item['quantity'] ?? 1);                    <th>Cód. País</th>

        $unitPrice = floatval($item['unit_price'] ?? 0);                    <th>País</th>

        $discPct = floatval($item['discount'] ?? 0);                </tr>

        $lineNet = $qty * $unitPrice * (1 - $discPct / 100);                <tr>

        $taxRate = floatval($item['tax_percent'] ?? $item['tax_rate'] ?? 0);                    <td>{{ $clientCity ?: 'Montevideo' }}</td>

                    <td>{{ $clientDept ?: 'Montevideo' }}</td>

        if ($taxRate == 22) {                    <td></td>

            $subtotalBasica += $lineNet;                    <td>UY</td>

            $ivaBasica += $lineNet * 0.22;                    <td>{{ $clientCountry }}</td>

        } elseif ($taxRate == 10) {                </tr>

            $subtotalMinima += $lineNet;            </table>

            $ivaMinima += $lineNet * 0.10;        </div>

        } else {

            $subtotalNoGravado += $lineNet;        {{-- ======== TABLA DE CONCEPTOS ======== --}}

        }        <table class="conceptos-table">

    }            <thead>

@endphp                <tr>

                    <th style="width:40%; text-align:left;">CONCEPTO</th>

    <div class="actions no-print">                    <th style="width:10%;">UNIDAD</th>

        <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir</button>                    <th style="width:14%;">P/UNITARIO</th>

        <a href="{{ route('cfe.print', $cfe->id) }}?format=ticket" class="btn">🎫 Ticket 80mm</a>                    <th style="width:8%;">DESC.</th>

        <a href="{{ route('cfe.download-xml', $cfe->id) }}" class="btn btn-success">📥 XML</a>                    <th style="width:8%;">DESC. %</th>

        <a href="{{ route('cfe.show', $cfe->id) }}" class="btn">← Volver</a>                    <th style="width:20%;">TOTAL</th>

    </div>                </tr>

            </thead>

    <div class="invoice-wrap">            <tbody>

                @forelse($items as $index => $item)

        {{-- ======== CABECERA ======== --}}                @php

        <div class="cfe-header">                    $qty        = floatval($item['quantity']   ?? 1);

            {{-- IZQUIERDA: Datos del emisor --}}                    $unitPrice  = floatval($item['unit_price'] ?? 0);

            <div class="cfe-header-left">                    $discPct    = floatval($item['discount']   ?? 0);

                @if($display_logo)                    $discAmt    = $unitPrice * $qty * ($discPct / 100);

                    <img src="{{ $display_logo }}" alt="Logo" class="emisor-logo">                    $lineTotal  = $qty * $unitPrice - $discAmt;

                @endif                @endphp

                <div class="emisor-name">{{ $display_company_name }}</div>                <tr>

                <div class="emisor-details">                    <td>{{ $item['name'] ?? $item['description'] ?? 'Producto/Servicio' }}</td>

                    @if($emitterAddress)                    <td class="text-center">{{ number_format($qty, 0) }}</td>

                        {{ $emitterAddress }}<br>                    <td class="text-right">${{ number_format($unitPrice, 2, ',', '.') }}</td>

                    @endif                    <td class="text-right">{{ $discAmt > 0 ? '$'.number_format($discAmt,2,',','.') : '' }}</td>

                    @if($emitterCity || $emitterDept)                    <td class="text-center">{{ $discPct > 0 ? number_format($discPct,0).'%' : '' }}</td>

                        {{ $emitterCity }}{{ $emitterCity && $emitterDept ? ', ' : '' }}{{ $emitterDept }}<br>                    <td class="text-right">${{ number_format($lineTotal, 2, ',', '.') }}</td>

                    @endif                </tr>

                    @if($emitterPhone)                @empty

                        <strong>Tel.:</strong> {{ $emitterPhone }}<br>                <tr>

                    @endif                    <td colspan="6" style="text-align:center; padding:12px; color:#777;">Sin ítems</td>

                    @if($emitterEmail)                </tr>

                        <strong>Email:</strong> {{ $emitterEmail }}<br>                @endforelse

                    @endif            </tbody>

                    @if(isset($location) && $location && $location->name)        </table>

                        <strong>Sucursal:</strong> {{ $location->name }}

                    @endif        {{-- ======== SUBTOTALES / TOTALES ======== --}}

                </div>        <div class="totales-section">

            </div>            <div class="totales-left">

                <strong>Subtot No Gravado</strong>

            {{-- DERECHA: Bloque documental DGI --}}                &nbsp;&nbsp;&nbsp;

            <div class="cfe-header-right">                <strong>Subtot IVA Susp.</strong>

                <div class="doc-box">                &nbsp;&nbsp;&nbsp;

                    <div class="doc-box-ruc">R.U.C. {{ $emitterRut }}</div>                <strong>Subtot T. Básica</strong>

                    <div class="doc-box-tipo">{{ $tipoLabel }}</div>                &nbsp;&nbsp;&nbsp;

                    <div class="doc-box-subtipo">Comprobante Fiscal Electrónico</div>                <strong>Subtot T. Mínima</strong>

                </div>                &nbsp;&nbsp;&nbsp;

                <strong>IVA T. Básica</strong>

                <table class="info-table">                &nbsp;&nbsp;&nbsp;

                    <tr>                <strong>IVA T. Mínima</strong>

                        <th>Serie</th>                &nbsp;&nbsp;&nbsp;

                        <th>Número</th>                <strong style="font-size:10px; float:right; padding-right:6px;">TOTAL FACTURA</strong>

                        <th>Moneda</th>            </div>

                    </tr>            <div class="totales-right">

                    <tr>                <table class="subtotales-table">

                        <td>{{ $cfe->series }}</td>                    <tr>

                        <td>{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}</td>                        <td class="label-col">Subtotal:</td>

                        <td>{{ $cfe->currency ?? 'UYU' }}</td>                        <td class="value-col">${{ number_format($cfe->subtotal, 2, ',', '.') }}</td>

                    </tr>                    </tr>

                </table>                    @if($cfe->tax_amount > 0)

                    <tr>

                <table class="info-table" style="margin-top:3px;">                        <td class="label-col">IVA:</td>

                    <tr>                        <td class="value-col">${{ number_format($cfe->tax_amount, 2, ',', '.') }}</td>

                        <th>Fecha Emisión</th>                    </tr>

                        <th>Fecha Vencimiento</th>                    @endif

                    </tr>                    <tr>

                    <tr>                        <td class="label-col" style="font-weight:bold;">Total Factura:</td>

                        <td>{{ $cfe->issue_date->format('d/m/Y') }}</td>                        <td class="value-col" style="font-weight:bold;">${{ number_format($cfe->total, 2, ',', '.') }}</td>

                        <td>{{ $cfe->due_date ? $cfe->due_date->format('d/m/Y') : $cfe->issue_date->format('d/m/Y') }}</td>                    </tr>

                    </tr>                </table>

                </table>            </div>

            </div>        </div>

        </div>

        {{-- ======== TOTAL A PAGAR ======== --}}

        {{-- ======== DATOS DEL RECEPTOR / COMPRADOR ======== --}}        <div class="total-pagar-box">

        <div class="receptor-section">            <span>TOTAL A PAGAR</span>

            <div class="receptor-title">Datos del Receptor</div>            <span>${{ number_format($cfe->total, 2, ',', '.') }}</span>

            <div class="receptor-grid">        </div>

                <div class="receptor-cell">

                    <div class="receptor-label">{{ $clientDocType }} Comprador</div>        @if($cfe->notes)

                    <div class="receptor-value">{{ $clientDoc ?: '—' }}</div>        <div style="border:1px solid #aaa; padding:5px 8px; margin-top:8px; font-size:10px;">

                </div>            <strong>Observaciones:</strong> {{ $cfe->notes }}

                <div class="receptor-cell">        </div>

                    <div class="receptor-label">Razón Social / Nombre</div>        @endif

                    <div class="receptor-value">{{ $clientName }}</div>

                </div>        {{-- ======== FOOTER DGI ======== --}}

            </div>        <div class="dgi-footer">

        </div>            <div class="dgi-footer-grid">

                <div class="dgi-footer-left">

        {{-- ======== DOMICILIO FISCAL ======== --}}                    Comprobante en: <strong>IVA al día</strong><br>

        <div class="domicilio-section">                    CAE nro. {{ $cfe->cae ?: '—' }}<br>

            <table class="domicilio-table">                    <br>

                <tr>                    www.dgi.gub.uy<br>

                    <th>Dirección</th>                    <br>

                    <th>Localidad</th>                    Serie {{ $cfe->series }} del 0000001 al 1000000<br>

                    <th>Departamento</th>                    Cód. de Seg.: {{ $cfe->security_code ?? 'AoBVNd' }}

                    <th>País</th>                </div>

                </tr>                <div class="dgi-footer-right">

                <tr>                    <div class="cae-box">

                    <td>{{ $clientAddress ?: '—' }}</td>                        <div class="cae-title">Fecha de vencimiento</div>

                    <td>{{ $clientCity ?: '—' }}</td>                        <div class="cae-value">CAE {{ $cfe->cae_due_date ? $cfe->cae_due_date->format('d-m-Y') : '31-12-2026' }}</div>

                    <td>{{ $clientDept ?: '—' }}</td>                    </div>

                    <td>{{ $clientCountry }}</td>                </div>

                </tr>            </div>

            </table>        </div>

        </div>

</body>

        {{-- ======== TABLA DE CONCEPTOS / DETALLE ======== --}}</html>
        <table class="conceptos-table">
            <thead>
                <tr>
                    <th style="width:6%; text-align:center;">Nro</th>
                    <th style="width:34%; text-align:left;">Descripción</th>
                    <th style="width:10%;">Cantidad</th>
                    <th style="width:14%;">P/Unitario</th>
                    <th style="width:8%;">Dto. %</th>
                    <th style="width:10%;">Dto. $</th>
                    <th style="width:18%;">Total</th>
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
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['name'] ?? $item['description'] ?? 'Producto/Servicio' }}</td>
                    <td class="text-center">{{ number_format($qty, 0) }}</td>
                    <td class="text-right">${{ number_format($unitPrice, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $discPct > 0 ? number_format($discPct, 0).'%' : '' }}</td>
                    <td class="text-right">{{ $discAmt > 0 ? '$'.number_format($discAmt, 2, ',', '.') : '' }}</td>
                    <td class="text-right">${{ number_format($lineTotal, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:14px; color:#999;">Sin ítems</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- ======== SUBTOTALES / DESGLOSE IVA / TOTALES ======== --}}
        <div class="totales-section">
            <div class="totales-left">
                <div style="margin-bottom:4px; font-weight:bold; color:var(--dgi-blue); font-size:9px; text-transform:uppercase;">Desglose Impositivo</div>
                <div class="iva-grid">
                    <div class="iva-grid-cell">
                        <div class="iva-grid-label">Subtot. No Gravado</div>
                        <div>${{ number_format($subtotalNoGravado, 2, ',', '.') }}</div>
                    </div>
                    <div class="iva-grid-cell">
                        <div class="iva-grid-label">Subtot. T. Básica (22%)</div>
                        <div>${{ number_format($subtotalBasica, 2, ',', '.') }}</div>
                    </div>
                    <div class="iva-grid-cell">
                        <div class="iva-grid-label">IVA T. Básica</div>
                        <div>${{ number_format($ivaBasica, 2, ',', '.') }}</div>
                    </div>
                    <div class="iva-grid-cell">
                        <div class="iva-grid-label">Subtot. T. Mínima (10%)</div>
                        <div>${{ number_format($subtotalMinima, 2, ',', '.') }}</div>
                    </div>
                    <div class="iva-grid-cell">
                        <div class="iva-grid-label">IVA T. Mínima</div>
                        <div>${{ number_format($ivaMinima, 2, ',', '.') }}</div>
                    </div>
                </div>
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
                    <tr style="border-top: 1px solid #999;">
                        <td class="label-col" style="font-weight:bold; font-size:10px; color:#003366;">Total:</td>
                        <td class="value-col" style="font-size:11px; color:#003366;">${{ number_format($cfe->total, 2, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- ======== TOTAL A PAGAR ======== --}}
        <div class="total-pagar-box">
            <span class="total-pagar-label">TOTAL A PAGAR</span>
            <span class="total-pagar-value">${{ number_format($cfe->total, 2, ',', '.') }}</span>
        </div>

        {{-- ======== CONDICIÓN DE PAGO ======== --}}
        <div class="pago-info">
            <strong>Condición de Pago:</strong> {{ $condPago }}
            @if($cfe->due_date && $cfe->due_date->gt($cfe->issue_date))
                &nbsp;—&nbsp; Vto.: {{ $cfe->due_date->format('d/m/Y') }}
            @endif
        </div>

        {{-- ======== OBSERVACIONES ======== --}}
        @if($cfe->notes)
        <div class="observaciones">
            <strong>Observaciones:</strong> {{ $cfe->notes }}
        </div>
        @endif

        {{-- ======== FOOTER DGI CON QR ======== --}}
        <div class="dgi-footer">
            <div class="dgi-footer-grid">
                {{-- IZQUIERDA: Datos fiscales --}}
                <div class="dgi-footer-left">
                    @if($cfe->cae)
                        <strong>CAE Nro.:</strong> {{ $cfe->cae }}<br>
                    @endif
                    @if($cfe->cae_due_date)
                        <strong>Vencimiento CAE:</strong> {{ $cfe->cae_due_date->format('d/m/Y') }}<br>
                    @endif
                    <strong>Serie:</strong> {{ $cfe->series }} — del 0000001 al 1000000<br>
                    @if(!empty($cfe->security_code))
                        <strong>Código de Seguridad:</strong> {{ $cfe->security_code }}<br>
                    @endif
                    <br>
                    Comprobante autorizado por DGI — <strong>IVA al día</strong><br>
                    Consulte validez: <strong>www.efactura.dgi.gub.uy</strong><br>
                    <br>
                    <span style="font-size:8px; color:#888;">
                        Documento generado el {{ $cfe->issue_date->format('d/m/Y') }}
                    </span>
                </div>

                {{-- DERECHA: CAE box + QR --}}
                <div class="dgi-footer-right">
                    @if($cfe->cae)
                    <div class="cae-box">
                        <div class="cae-title">CAE — Vencimiento</div>
                        <div class="cae-value">{{ $cfe->cae_due_date ? $cfe->cae_due_date->format('d/m/Y') : '—' }}</div>
                    </div>
                    @endif

                    {{-- QR CODE para verificación DGI --}}
                    @if(!empty($qr_data))
                    <div class="qr-section">
                        <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($qr_data, 'QRCODE', 4, 4, [0, 51, 102]) }}" alt="QR Verificación DGI" style="width:100px; height:100px;">
                        <div class="qr-label">Escanee para verificar en DGI</div>
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

</body>
</html>
