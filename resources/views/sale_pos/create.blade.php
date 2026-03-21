@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        @php
            $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
            $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_pos_sell_form',
        ]) !!}
        <div class="row mb-12">
            <div class="col-md-12 tw-pt-0 tw-mb-14">
                <div class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
                    {{-- <div class="@if (empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-10 col-md-offset-1 @endif no-padding pr-12"> --}}
                    <div class="tw-px-3 tw-w-full  lg:tw-px-0 lg:tw-pr-0 @if(empty($pos_settings['hide_product_suggestion'])) lg:tw-w-[60%]  @else lg:tw-w-[100%] @endif">

                        <div class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-mb-2 md:tw-mb-8 tw-p-2">

                            {{-- <div class="box box-solid mb-12 @if (!isMobile()) mb-40 @endif"> --}}
                                <div class="box-body pb-0">
                                    {!! Form::hidden('location_id', $default_location->id ?? null, [
                                        'id' => 'location_id',
                                        'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                                            ? $default_location->receipt_printer_type
                                            : 'browser',
                                        'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                                    ]) !!}
                                    <!-- sub_type -->
                                    {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                    <input type="hidden" id="item_addition_method"
                                        value="{{ $business_details->item_addition_method }}">
                                    @include('sale_pos.partials.pos_form')

                                    @include('sale_pos.partials.pos_form_totals')

                                    @include('sale_pos.partials.payment_modal')

                                    @if (empty($pos_settings['disable_suspend']))
                                        @include('sale_pos.partials.suspend_note_modal')
                                    @endif

                                    @if (empty($pos_settings['disable_recurring_invoice']))
                                        @include('sale_pos.partials.recurring_invoice_modal')
                                    @endif
                                </div>
                            {{-- </div> --}}
                        </div>
                    </div>
                    @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                        <div class="md:tw-no-padding tw-w-full lg:tw-w-[40%] tw-px-5">
                            @include('sale_pos.partials.pos_sidebar')
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('sale_pos.partials.pos_form_actions')
        {!! Form::close() !!}
    </section>

    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
    </section>

    <!-- Modal de selección de formato de impresión -->
    <div class="modal fade" id="print_format_modal" tabindex="-1" role="dialog" aria-labelledby="printFormatModalLabel">
        <div class="modal-dialog modal-sm" role="document" style="margin-top: 15%;">
            <div class="modal-content">
                <div class="modal-header" style="background: #3c8dbc; color: #fff; padding: 10px 15px;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="printFormatModalLabel">
                        <i class="fas fa-print"></i> Seleccionar formato de impresión
                    </h4>
                </div>
                <div class="modal-body text-center" style="padding: 25px;">
                    <p style="margin-bottom: 20px; color: #555;">¿Cómo desea imprimir el comprobante?</p>
                    <button type="button" class="btn btn-primary btn-lg btn-block" onclick="pos_print_ticket()" style="margin-bottom: 12px; padding: 12px; font-size: 16px;">
                        <i class="fas fa-receipt"></i> &nbsp; Ticket (80mm)
                    </button>
                    <button type="button" class="btn btn-success btn-lg btn-block" onclick="pos_print_a4()" style="padding: 12px; font-size: 16px;">
                        <i class="fas fa-file-invoice"></i> &nbsp; Factura A4
                    </button>
                </div>
                <div class="modal-footer" style="padding: 8px 15px;">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
        @include('sale_pos.partials.mobile_product_suggestions')
    @endif
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

    <!-- modal producto manual -->
    <div class="modal fade" id="manual_product_modal" tabindex="-1" role="dialog" aria-labelledby="manualProductModalTitle">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="manualProductModalTitle"><i class="fa fa-keyboard-o"></i> Agregar Producto Manual</h4>
          </div>
          <div class="modal-body">
            <p class="text-muted" style="margin-bottom:15px;"><small>El producto se crea en el catálogo automáticamente sin gestión de stock.</small></p>
            <div class="form-group">
              <label>Nombre del producto: <span class="text-danger">*</span></label>
              <input type="text" id="manual_product_name" class="form-control" placeholder="Ej: Servicio de instalación" autofocus>
            </div>
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group">
                  <label>Precio unitario (inc. imp.): <span class="text-danger">*</span></label>
                  <input type="number" id="manual_product_price" class="form-control" placeholder="0.00" min="0" step="0.01">
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group">
                  <label>Cantidad:</label>
                  <input type="number" id="manual_product_qty" class="form-control" value="1" min="1" step="1">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Descuento %:</label>
              <input type="number" id="manual_product_discount" class="form-control" value="0" min="0" max="100" step="0.01">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="save_manual_product"><i class="fa fa-check"></i> Agregar al carrito</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">Cancelar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    @include('sale_pos.partials.configure_search_modal')

    @include('sale_pos.partials.recent_transactions_modal')

    @include('sale_pos.partials.weighing_scale_modal')

@stop
@section('css')
    <!-- include module css -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@stop
@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/exchange_rate.js?v=' . $asset_v) }}"></script>
    @include('sale_pos.partials.keyboard_shortcuts')

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif
@endsection
