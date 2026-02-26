@extends('layouts.app')
@section('title', __('lang_v1.payment_receipts'))

@section('content')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('lang_v1.payment_receipts')
        </h1>
    </section>

    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.payment_receipts_list')])
            @slot('tool')
                <div class="box-tools">
                    <a href="{{ action([\App\Http\Controllers\PaymentReceiptController::class, 'create']) }}"
                        class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        @lang('lang_v1.add_payment_receipt')
                    </a>
                </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="payment_receipts_table">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.receipt_no')</th>
                            <th>@lang('messages.date')</th>
                            <th>@lang('lang_v1.received_from')</th>
                            <th>@lang('business.location')</th>
                            <th>@lang('sale.amount')</th>
                            <th>@lang('lang_v1.payment_method')</th>
                            <th>@lang('lang_v1.bank_name')</th>
                            <th>@lang('lang_v1.bank_reference')</th>
                            <th>@lang('lang_v1.concept')</th>
                            <th>@lang('purchase.ref_no')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            const table = $('#payment_receipts_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ action([\App\Http\Controllers\PaymentReceiptController::class, 'index']) }}",
                columnDefs: [{
                    targets: -1,
                    orderable: false,
                    searchable: false,
                }],
                columns: [
                    {
                        data: 'receipt_no',
                        name: 'receipt_no'
                    },
                    {
                        data: 'payment_date',
                        name: 'payment_date'
                    },
                    {
                        data: 'contact_name',
                        name: 'contact.name'
                    },
                    {
                        data: 'location_name',
                        name: 'location.name'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },
                    {
                        data: 'bank_name',
                        name: 'bank_name'
                    },
                    {
                        data: 'bank_reference',
                        name: 'bank_reference'
                    },
                    {
                        data: 'concept',
                        name: 'concept'
                    },
                    {
                        data: 'reference',
                        name: 'reference'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });
        });
    </script>
@endsection
