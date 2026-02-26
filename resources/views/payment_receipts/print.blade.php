<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <title>@lang('lang_v1.payment_receipt')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css?v=' . config('app.asset_version')) }}">
    <style>
        body {
            padding: 20px;
        }

        .receipt-container {
            max-width: 700px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .receipt-section {
            margin-bottom: 15px;
        }

        .receipt-section p {
            margin: 0;
        }
    </style>
</head>

@php
    $businessLogo = ! empty($business['logo']) ? asset('uploads/business_logos/'.rawurlencode($business['logo'])) : null;
    $businessName = $business['name'] ?? config('app.name');
    $businessAddress = $business['business_address'] ?? ($business['address'] ?? '');
    $contact = $receipt->contact;
    $currencySymbol = session('currency')['symbol'] ?? '';
    $contactLabels = $customLabels['contact'] ?? [];
    $formattedBusinessAddress = '';
    if (! empty($businessAddress)) {
        $addressWithoutTags = strip_tags(str_ireplace(['<br />', '<br/>', '<br>'], "\n", $businessAddress));
        $formattedBusinessAddress = nl2br(e($addressWithoutTags));
    }
@endphp

<body onload="window.print()">
    <div class="receipt-container">
        <div class="receipt-header">
            <div>
                @if($businessLogo)
                    <img src="{{ $businessLogo }}" alt="logo" style="max-height:60px; margin-bottom:10px;">
                @endif
                <h3>{{ $businessName }}</h3>
                @if(!empty($businessAddress))
                    <p>{!! $formattedBusinessAddress !!}</p>
                @endif
                @if(!empty($business['tax_number_1']))
                    <p><strong>{{ $customLabels['business']['tax_number_1'] ?? __('lang_v1.rut') }}:</strong> {{ $business['tax_number_1'] }}</p>
                @endif
            </div>
            <div>
                <table style="width:280px; border-collapse:collapse;">
                    <tr>
                        <th style="text-align:left; padding:4px;">@lang('lang_v1.receipt_no'):</th>
                        <td style="text-align:right; padding:4px;">{{ $receipt->receipt_no }}</td>
                    </tr>
                    <tr>
                        <th style="text-align:left; padding:4px;">@lang('messages.date'):</th>
                        <td style="text-align:right; padding:4px;">{{ @format_date($receipt->payment_date) }}</td>
                    </tr>
                    <tr>
                        <th style="text-align:left; padding:4px;">@lang('purchase.ref_no'):</th>
                        <td style="text-align:right; padding:4px;">{{ $receipt->reference ?? __('lang_v1.not_applicable') }}</td>
                    </tr>
                    <tr>
                        <th style="text-align:left; padding:4px;">@lang('business.location'):</th>
                        <td style="text-align:right; padding:4px;">{{ optional($receipt->location)->name ?? __('lang_v1.not_applicable') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="receipt-section">
            <h4 style="margin-bottom:8px;">@lang('lang_v1.received_from')</h4>
            <p style="margin:0;"><strong>{{ optional($contact)->name ?? __('messages.na') }}</strong></p>
            <p style="margin:0;"><strong>@lang('lang_v1.contact_id'):</strong> {{ $contact->contact_id ?? __('lang_v1.not_applicable') }}</p>
            @if(!empty(optional($contact)->supplier_business_name))
                <p style="margin:0;"><strong>@lang('business.business'):</strong> {{ $contact->supplier_business_name }}</p>
            @endif
            @if(!empty(optional($contact)->tax_number))
                <p style="margin:0;"><strong>{{ $contactLabels['tax_number'] ?? __('contact.tax_no') }}:</strong> {{ $contact->tax_number }}</p>
            @endif
            @if(!empty(optional($contact)->landline))
                <p style="margin:0;"><strong>@lang('contact.landline'):</strong> {{ $contact->landline }}</p>
            @elseif(!empty(optional($contact)->mobile))
                <p style="margin:0;"><strong>@lang('contact.mobile'):</strong> {{ $contact->mobile }}</p>
            @endif
            @if(!empty(optional($contact)->email))
                <p style="margin:0;"><strong>@lang('business.email'):</strong> {{ $contact->email }}</p>
            @endif
            @for ($i = 1; $i <= 4; $i++)
                @php
                    $labelKey = 'custom_field_'.$i;
                    $label = $contactLabels[$labelKey] ?? null;
                    $value = $contact->{'custom_field'.$i} ?? null;
                @endphp
                @if(!empty($label) && !empty($value))
                    <p style="margin:0;"><strong>{{ $label }}:</strong> {{ $value }}</p>
                @endif
            @endfor
        </div>

        <div class="receipt-section">
            <table style="width:100%; border-collapse: collapse;" border="1">
                <thead>
                    <tr>
                        <th style="padding:8px; width:50px;">#</th>
                        <th style="padding:8px;">@lang('lang_v1.item_description')</th>
                        <th style="padding:8px; width:150px; text-align:right;">@lang('sale.amount')</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding:8px; text-align:center;">1</td>
                        <td style="padding:8px;">{!! nl2br(e($receipt->concept)) !!}</td>
                        <td style="padding:8px; text-align:right;">{{ $currencySymbol }} {{ @num_format($receipt->amount) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" style="padding:8px; text-align:right;">@lang('sale.total')</th>
                        <th style="padding:8px; text-align:right;">{{ $currencySymbol }} {{ @num_format($receipt->amount) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="receipt-section" style="display:flex; justify-content:space-between; gap:20px;">
            <div style="flex:1;">
                <p style="margin:0;"><strong>@lang('lang_v1.amount_in_words'):</strong> {{ $amountInWords }}</p>
                <p style="margin:0;"><strong>@lang('lang_v1.notes'):</strong> {{ $receipt->notes ?? __('lang_v1.not_applicable') }}</p>
            </div>
            <div style="flex:1;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <th style="text-align:left; padding:4px;">@lang('lang_v1.payment_method'):</th>
                        <td style="text-align:right; padding:4px;">{{ $paymentMethodLabel }}</td>
                    </tr>
                    <tr>
                        <th style="text-align:left; padding:4px;">@lang('lang_v1.bank_name'):</th>
                        <td style="text-align:right; padding:4px;">{{ $receipt->bank_name ?? __('lang_v1.not_applicable') }}</td>
                    </tr>
                    <tr>
                        <th style="text-align:left; padding:4px;">@lang('lang_v1.bank_reference'):</th>
                        <td style="text-align:right; padding:4px;">{{ $receipt->bank_reference ?? __('lang_v1.not_applicable') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="receipt-section" style="display:flex; justify-content:space-around; text-align:center; margin-top:30px;">
            <div>
                <p>______________________________</p>
                <p>@lang('lang_v1.payer_signature')</p>
            </div>
            <div>
                <p>______________________________</p>
                <p>@lang('lang_v1.authorized_signature')</p>
            </div>
        </div>
    </div>
</body>

</html>
