@extends('layouts.app')
@section('title', __('lang_v1.payment_receipt'))

@section('content')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('lang_v1.payment_receipt')
        </h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="box-title">
                    <strong>@lang('lang_v1.payment_receipt')</strong>
                </div>
                <a href="{{ action([\App\Http\Controllers\PaymentReceiptController::class, 'print'], [$receipt->id]) }}" target="_blank" class="btn btn-default pull-right">
                    <i class="fa fa-print"></i> @lang('lang_v1.print_receipt')
                </a>
            </div>
            <div class="box-body">
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

                <div class="row">
                    <div class="col-sm-6">
                        @if($businessLogo)
                            <img src="{{ $businessLogo }}" alt="logo" style="max-height:70px; margin-bottom:10px;">
                        @endif
                        <h4 class="tw-font-semibold">{{ $businessName }}</h4>
                        @if(!empty($formattedBusinessAddress))
                            <p class="text-muted">{!! $formattedBusinessAddress !!}</p>
                        @endif
                        @if(!empty($business['tax_number_1']))
                            <p class="text-muted"><strong>{{ $customLabels['business']['tax_number_1'] ?? __('lang_v1.rut') }}:</strong> {{ $business['tax_number_1'] }}</p>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <table class="table table-bordered">
                            <tr>
                                <th>@lang('lang_v1.receipt_no')</th>
                                <td>{{ $receipt->receipt_no }}</td>
                            </tr>
                            <tr>
                                <th>@lang('messages.date')</th>
                                <td>{{ @format_date($receipt->payment_date) }}</td>
                            </tr>
                            <tr>
                                <th>@lang('purchase.ref_no')</th>
                                <td>{{ $receipt->reference ?? __('lang_v1.not_applicable') }}</td>
                            </tr>
                            <tr>
                                <th>@lang('business.location')</th>
                                <td>{{ optional($receipt->location)->name ?? __('lang_v1.not_applicable') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row" style="margin-top:20px;">
                    <div class="col-sm-6">
                        <h4 class="tw-font-semibold">@lang('lang_v1.received_from')</h4>
                        <p class="tw-text-lg tw-font-bold">{{ optional($contact)->name ?? __('messages.na') }}</p>
                        <p><strong>@lang('lang_v1.contact_id'):</strong> {{ $contact->contact_id ?? __('lang_v1.not_applicable') }}</p>
                        @if(!empty(optional($contact)->supplier_business_name))
                            <p><strong>@lang('business.business'):</strong> {{ $contact->supplier_business_name }}</p>
                        @endif
                        @if(!empty(optional($contact)->tax_number))
                            <p><strong>{{ $contactLabels['tax_number'] ?? __('contact.tax_no') }}:</strong> {{ $contact->tax_number }}</p>
                        @endif
                        @if(!empty(optional($contact)->landline))
                            <p><strong>@lang('contact.landline'):</strong> {{ $contact->landline }}</p>
                        @elseif(!empty(optional($contact)->mobile))
                            <p><strong>@lang('contact.mobile'):</strong> {{ $contact->mobile }}</p>
                        @endif
                        @if(!empty(optional($contact)->email))
                            <p><strong>@lang('business.email'):</strong> {{ $contact->email }}</p>
                        @endif
                        @for ($i = 1; $i <= 4; $i++)
                            @php
                                $labelKey = 'custom_field_'.$i;
                                $label = $contactLabels[$labelKey] ?? null;
                                $value = $contact->{'custom_field'.$i} ?? null;
                            @endphp
                            @if(!empty($label) && !empty($value))
                                <p><strong>{{ $label }}:</strong> {{ $value }}</p>
                            @endif
                        @endfor
                    </div>
                    <div class="col-sm-6">
                        <h4 class="tw-font-semibold">@lang('lang_v1.payment_details')</h4>
                        <p><strong>@lang('sale.amount'):</strong> {{ $currencySymbol }} {{ @num_format($receipt->amount) }}</p>
                        <p><strong>@lang('lang_v1.payment_method'):</strong> {{ $paymentMethodLabel }}</p>
                        <p><strong>@lang('lang_v1.bank_name'):</strong> {{ $receipt->bank_name ?? __('lang_v1.not_applicable') }}</p>
                        <p><strong>@lang('lang_v1.bank_reference'):</strong> {{ $receipt->bank_reference ?? __('lang_v1.not_applicable') }}</p>
                    </div>
                </div>

                <div class="table-responsive" style="margin-top:30px;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>@lang('lang_v1.item_description')</th>
                                <th class="text-right">@lang('sale.amount')</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>{!! nl2br(e($receipt->concept)) !!}</td>
                                <td class="text-right">{{ $currencySymbol }} {{ @num_format($receipt->amount) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">@lang('sale.total')</th>
                                <th class="text-right">{{ $currencySymbol }} {{ @num_format($receipt->amount) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row" style="margin-top:15px;">
                    <div class="col-sm-8">
                        <p><strong>@lang('lang_v1.amount_in_words'):</strong> {{ $amountInWords }}</p>
                    </div>
                    <div class="col-sm-4 text-right">
                        <p class="text-muted"><strong>@lang('lang_v1.receipt_generated_by'):</strong> {{ optional($receipt->createdBy)->user_full_name }}</p>
                    </div>
                </div>

                @if(!empty($receipt->notes))
                    <div class="row" style="margin-top:15px;">
                        <div class="col-sm-12">
                            <p><strong>@lang('lang_v1.notes'):</strong></p>
                            <p>{!! nl2br(e($receipt->notes)) !!}</p>
                        </div>
                    </div>
                @endif

                <div class="row" style="margin-top:40px;">
                    <div class="col-sm-6 text-center">
                        <p>______________________________</p>
                        <p class="text-muted">@lang('lang_v1.payer_signature')</p>
                    </div>
                    <div class="col-sm-6 text-center">
                        <p>______________________________</p>
                        <p class="text-muted">@lang('lang_v1.authorized_signature')</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
