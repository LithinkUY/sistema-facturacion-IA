@extends('layouts.app')
@section('title', __('lang_v1.add_payment_receipt'))

@section('content')
    @php
        $today = \Carbon\Carbon::now()->format(session('business.date_format', 'm/d/Y'));
    @endphp
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('lang_v1.add_payment_receipt')
        </h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                {!! Form::open(['url' => action([\App\Http\Controllers\PaymentReceiptController::class, 'store']), 'method' => 'post', 'id' => 'payment_receipt_form']) !!}
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('contact_id', __('lang_v1.received_from') . ':*') !!}
                            {!! Form::select('contact_id', $customers, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]) !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('location_id', __('business.location') . ':') !!}
                            {!! Form::select('location_id', $locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('payment_date', __('messages.date') . ':*') !!}
                            {!! Form::text('payment_date', $today, ['class' => 'form-control payment_datepicker', 'required', 'readonly']) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('amount', __('sale.amount') . ':*') !!}
                            {!! Form::text('amount', null, ['class' => 'form-control input_number', 'required', 'id' => 'payment_amount']) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('reference', __('purchase.ref_no') . ':') !!}
                            {!! Form::text('reference', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('concept', __('lang_v1.item_description') . ' / ' . __('lang_v1.concept') . ':*') !!}
                            {!! Form::textarea('concept', null, ['class' => 'form-control', 'required', 'rows' => 3, 'placeholder' => __('lang_v1.item_description') . ' (Ej: Seña por Producto X)']) !!}
                            <small class="help-block">@lang('lang_v1.payment_receipt_concept_hint')</small>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('notes', __('lang_v1.notes') . ':') !!}
                            {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3]) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('payment_method', __('lang_v1.payment_method') . ':') !!}
                            {!! Form::select('payment_method', $paymentTypes, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]) !!}
                            <small class="help-block text-muted">@lang('lang_v1.payment_receipt_method_hint')</small>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('bank_name', __('lang_v1.bank_name') . ':') !!}
                            {!! Form::text('bank_name', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.bank_name')]) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('bank_reference', __('lang_v1.bank_reference') . ':') !!}
                            {!! Form::text('bank_reference', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.bank_reference_placeholder')]) !!}
                            <small class="help-block text-muted">@lang('lang_v1.bank_reference_hint')</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary pull-right">
                            @lang('messages.save')
                        </button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            var $form = $('#payment_receipt_form');
            $('.select2').select2();
            $('.payment_datepicker').datepicker({
                autoclose: true,
                format: datepicker_date_format,
            }).datepicker('setDate', new Date());

            __currency_convert_recursively($form);
        });
    </script>
@endsection
