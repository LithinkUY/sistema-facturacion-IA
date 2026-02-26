@extends('layouts.app')
@section('title', 'Nueva Orden de Pedido')

@section('content')

<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-plus-circle"></i> Nueva Orden de Pedido
    </h1>
</section>

<section class="content no-print">
    {!! Form::open(['route' => 'order-pedidos.store', 'method' => 'POST', 'id' => 'order_pedido_form']) !!}
    
    <div class="row">
        <!-- Panel izquierdo: Info general -->
        <div class="col-md-8">
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Información de la Orden'])
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nº Orden:</label>
                            <input type="text" name="order_number" class="form-control" value="{{ $order_number }}" readonly style="background:#f5f5f5;font-weight:bold;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipo de Orden: <span class="text-danger">*</span></label>
                            <select name="type" class="form-control select2" style="width:100%">
                                <option value="purchase">Compra (Proveedor)</option>
                                <option value="sale">Venta (Cliente)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Prioridad:</label>
                            <select name="priority" class="form-control select2" style="width:100%">
                                <option value="low">🟢 Baja</option>
                                <option value="medium" selected>🟡 Media</option>
                                <option value="high">🟠 Alta</option>
                                <option value="urgent">🔴 Urgente</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Proveedor/Cliente:</label>
                            <select name="contact_id" class="form-control select2" style="width:100%">
                                <option value="">-- Seleccionar --</option>
                                @foreach($contacts as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ubicación:</label>
                            <select name="location_id" class="form-control select2" style="width:100%">
                                <option value="">-- Seleccionar --</option>
                                @foreach($locations as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Estado Inicial:</label>
                            <select name="status" class="form-control select2" style="width:100%">
                                <option value="draft">Borrador</option>
                                <option value="pending">Pendiente</option>
                                <option value="approved">Aprobada</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha de Orden: <span class="text-danger">*</span></label>
                            <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha de Entrega Esperada:</label>
                            <input type="date" name="expected_delivery_date" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Dirección de Envío:</label>
                            <input type="text" name="shipping_address" class="form-control" placeholder="Dirección de envío">
                        </div>
                    </div>
                </div>
            @endcomponent

            <!-- Líneas de productos -->
            @component('components.widget', ['class' => 'box-warning', 'title' => 'Productos / Items'])
                <!-- Buscador de productos -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fas fa-search"></i></span>
                            <input type="text" id="search_product" class="form-control" placeholder="Buscar producto por nombre o SKU... (o escriba para agregar manualmente)" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" id="btn_add_manual_line" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-outline tw-dw-btn-primary">
                            <i class="fas fa-plus"></i> Agregar Línea Manual
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-condensed" id="order_lines_table">
                        <thead style="background:#f9f9f9;">
                            <tr>
                                <th style="width:25%">Producto</th>
                                <th style="width:8%">SKU</th>
                                <th style="width:8%">Cant.</th>
                                <th style="width:8%">Unidad</th>
                                <th style="width:12%">Precio Unit.</th>
                                <th style="width:8%">IVA %</th>
                                <th style="width:8%">Desc. %</th>
                                <th style="width:12%">Total Línea</th>
                                <th style="width:5%"></th>
                            </tr>
                        </thead>
                        <tbody id="order_lines_body">
                            <!-- Se agregan dinámicamente -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-right"><strong>Subtotal:</strong></td>
                                <td><strong id="display_subtotal">$0.00</strong></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-right"><strong>Descuento Global:</strong></td>
                                <td>
                                    <select name="discount_type" class="form-control input-sm" id="discount_type">
                                        <option value="">Sin descuento</option>
                                        <option value="fixed">Fijo ($)</option>
                                        <option value="percentage">Porcentaje (%)</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="order_discount" class="form-control input-sm" id="order_discount" value="0" step="0.01" min="0">
                                </td>
                                <td><strong id="display_discount">-$0.00</strong></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-right"><strong>Impuestos:</strong></td>
                                <td><strong id="display_tax">$0.00</strong></td>
                                <td></td>
                            </tr>
                            <tr style="background:#e8f5e9;">
                                <td colspan="7" class="text-right"><strong style="font-size:1.2em;">TOTAL:</strong></td>
                                <td><strong style="font-size:1.2em;" id="display_total">$0.00</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>

        <!-- Panel derecho: Notas y acciones -->
        <div class="col-md-4">
            @component('components.widget', ['class' => 'box-info', 'title' => 'Notas y Términos'])
                <div class="form-group">
                    <label>Notas internas:</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Notas visibles solo internamente..."></textarea>
                </div>
                <div class="form-group">
                    <label>Términos y Condiciones:</label>
                    <textarea name="terms_conditions" class="form-control" rows="4" placeholder="Términos y condiciones para el proveedor/cliente..."></textarea>
                </div>
            @endcomponent

            @component('components.widget', ['class' => 'box-success', 'title' => 'Resumen'])
                <div class="tw-space-y-2">
                    <div class="tw-flex tw-justify-between">
                        <span>Items:</span>
                        <strong id="summary_items">0</strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span>Cantidad Total:</span>
                        <strong id="summary_qty">0</strong>
                    </div>
                    <hr>
                    <div class="tw-flex tw-justify-between tw-text-lg">
                        <span><strong>Total:</strong></span>
                        <strong id="summary_total" class="tw-text-green-600">$0.00</strong>
                    </div>
                </div>
                <hr>
                <div class="tw-flex tw-gap-2">
                    <button type="submit" class="tw-dw-btn tw-dw-btn-success tw-dw-btn-block tw-text-white">
                        <i class="fas fa-save"></i> Guardar Orden
                    </button>
                </div>
                <div class="tw-mt-2">
                    <a href="{{ route('order-pedidos.index') }}" class="tw-dw-btn tw-dw-btn-ghost tw-dw-btn-block">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            @endcomponent
        </div>
    </div>
    
    {!! Form::close() !!}
</section>

@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var lineIndex = 0;

    // Autocomplete de productos
    if (typeof $.fn.autocomplete !== 'undefined') {
        $('#search_product').autocomplete({
            source: function(request, response) {
                $.getJSON('{{ url("/order-pedidos/search-products") }}', { term: request.term }, function(data) {
                    response(data.results.map(function(item) {
                        return {
                            label: item.name + (item.sku ? ' [' + item.sku + ']' : ''),
                            value: item.name,
                            data: item
                        };
                    }));
                });
            },
            minLength: 2,
            select: function(event, ui) {
                addProductLine(ui.item.data);
                $(this).val('');
                return false;
            }
        });
    }

    // Agregar producto desde búsqueda
    function addProductLine(product) {
        addLine({
            product_id: product.id || '',
            variation_id: product.variation_id || '',
            product_name: product.name || '',
            sku: product.sku || '',
            unit_price: product.default_purchase_price || 0,
            unit: product.unit || '',
            quantity: 1,
            tax_percent: 22, // IVA Uruguay por defecto
            discount_percent: 0,
        });
    }

    // Agregar línea manual
    $('#btn_add_manual_line').click(function() {
        addLine({
            product_id: '',
            variation_id: '',
            product_name: '',
            sku: '',
            unit_price: 0,
            unit: '',
            quantity: 1,
            tax_percent: 22,
            discount_percent: 0,
        });
    });

    function addLine(data) {
        var idx = lineIndex++;
        var html = `
        <tr class="order-line" data-index="${idx}">
            <td>
                <input type="hidden" name="lines[${idx}][product_id]" value="${data.product_id}">
                <input type="hidden" name="lines[${idx}][variation_id]" value="${data.variation_id}">
                <input type="hidden" name="lines[${idx}][sort_order]" value="${idx}">
                <input type="text" name="lines[${idx}][product_name]" class="form-control input-sm" value="${data.product_name}" placeholder="Nombre del producto" required>
            </td>
            <td><input type="text" name="lines[${idx}][sku]" class="form-control input-sm" value="${data.sku}" placeholder="SKU"></td>
            <td><input type="number" name="lines[${idx}][quantity]" class="form-control input-sm line-qty" value="${data.quantity}" min="0.01" step="0.01" required></td>
            <td><input type="text" name="lines[${idx}][unit]" class="form-control input-sm" value="${data.unit}" placeholder="Unid."></td>
            <td><input type="number" name="lines[${idx}][unit_price]" class="form-control input-sm line-price" value="${data.unit_price}" min="0" step="0.01" required></td>
            <td><input type="number" name="lines[${idx}][tax_percent]" class="form-control input-sm line-tax" value="${data.tax_percent}" min="0" max="100" step="0.01"></td>
            <td><input type="number" name="lines[${idx}][discount_percent]" class="form-control input-sm line-discount" value="${data.discount_percent}" min="0" max="100" step="0.01"></td>
            <td class="line-total-display" style="font-weight:bold;text-align:right;vertical-align:middle;">$0.00</td>
            <td><button type="button" class="btn btn-xs btn-danger remove-line"><i class="fas fa-times"></i></button></td>
        </tr>`;
        $('#order_lines_body').append(html);
        recalculate();
    }

    // Recalcular totales al cambiar valores
    $(document).on('input change', '.line-qty, .line-price, .line-tax, .line-discount, #order_discount, #discount_type', function() {
        recalculate();
    });

    // Eliminar línea
    $(document).on('click', '.remove-line', function() {
        $(this).closest('tr').remove();
        recalculate();
    });

    function recalculate() {
        var subtotal = 0;
        var taxTotal = 0;
        var totalItems = 0;
        var totalQty = 0;

        $('#order_lines_body tr.order-line').each(function() {
            var qty = parseFloat($(this).find('.line-qty').val()) || 0;
            var price = parseFloat($(this).find('.line-price').val()) || 0;
            var taxPct = parseFloat($(this).find('.line-tax').val()) || 0;
            var discPct = parseFloat($(this).find('.line-discount').val()) || 0;

            var lineSubtotal = qty * price;
            var discount = lineSubtotal * (discPct / 100);
            var taxable = lineSubtotal - discount;
            var tax = taxable * (taxPct / 100);
            var lineTotal = taxable + tax;

            $(this).find('.line-total-display').text('$' + lineTotal.toFixed(2));
            subtotal += taxable;
            taxTotal += tax;
            totalItems++;
            totalQty += qty;
        });

        // Descuento global
        var discType = $('#discount_type').val();
        var discVal = parseFloat($('#order_discount').val()) || 0;
        var globalDiscount = 0;
        if (discType === 'percentage') {
            globalDiscount = subtotal * (discVal / 100);
        } else if (discType === 'fixed') {
            globalDiscount = discVal;
        }

        var total = subtotal + taxTotal - globalDiscount;

        $('#display_subtotal').text('$' + subtotal.toFixed(2));
        $('#display_tax').text('$' + taxTotal.toFixed(2));
        $('#display_discount').text('-$' + globalDiscount.toFixed(2));
        $('#display_total').text('$' + total.toFixed(2));
        $('#summary_items').text(totalItems);
        $('#summary_qty').text(totalQty.toFixed(2));
        $('#summary_total').text('$' + total.toFixed(2));
    }

    // Submit form
    $('#order_pedido_form').on('submit', function(e) {
        e.preventDefault();

        if ($('#order_lines_body tr.order-line').length === 0) {
            toastr.error('Debe agregar al menos un producto');
            return;
        }

        var $btn = $(this).find('button[type=submit]');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(result) {
                if (result.success) {
                    toastr.success(result.msg);
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    }
                } else {
                    toastr.error(result.msg);
                    $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Orden');
                }
            },
            error: function(xhr) {
                var msg = 'Error al guardar';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Orden');
            }
        });
    });

    // Agregar una línea vacía por defecto
    $('#btn_add_manual_line').click();
});
</script>
@endsection
