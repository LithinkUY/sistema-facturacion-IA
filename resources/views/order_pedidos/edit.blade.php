@extends('layouts.app')
@section('title', 'Editar Orden ' . $order->order_number)

@section('css')
<style>
    .product-line-table th { font-size: 0.85em; white-space: nowrap; }
    .product-line-table td { vertical-align: middle !important; }
    .product-line-table .form-control { font-size: 0.9em; padding: 4px 6px; }
    .total-row { font-size: 1.1em; }
    .total-highlight { background: #28a745; color: #fff; padding: 10px 15px; border-radius: 6px; font-size: 1.4em; }
    .status-badge { font-size: 0.85em; padding: 4px 10px; }
</style>
@endsection

@section('content')

<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-edit"></i> Editar Orden: {{ $order->order_number }}
        <span class="badge status-badge {{ $order->status_label['class'] }}">{{ $order->status_label['text'] }}</span>
    </h1>
</section>

<section class="content no-print">
    <form id="order_form" method="POST" action="{{ url('/order-pedidos/' . $order->id) }}">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-8">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'Información de la Orden'])
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nº Orden:</label>
                                <input type="text" class="form-control" value="{{ $order->order_number }}" readonly style="background:#f5f5f5;font-weight:bold;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo:</label>
                                <select name="type" class="form-control">
                                    <option value="purchase" {{ $order->type == 'purchase' ? 'selected' : '' }}>📦 Orden de Compra</option>
                                    <option value="sale" {{ $order->type == 'sale' ? 'selected' : '' }}>🛒 Orden de Venta</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Prioridad:</label>
                                <select name="priority" class="form-control">
                                    <option value="low" {{ $order->priority == 'low' ? 'selected' : '' }}>🟢 Baja</option>
                                    <option value="medium" {{ $order->priority == 'medium' ? 'selected' : '' }}>🟡 Media</option>
                                    <option value="high" {{ $order->priority == 'high' ? 'selected' : '' }}>🟠 Alta</option>
                                    <option value="urgent" {{ $order->priority == 'urgent' ? 'selected' : '' }}>🔴 Urgente</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Estado:</label>
                                <select name="status" class="form-control">
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ $order->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Proveedor/Cliente: <span class="text-danger">*</span></label>
                                <select name="contact_id" class="form-control select2" style="width:100%" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($contacts as $id => $name)
                                        <option value="{{ $id }}" {{ $order->contact_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ubicación:</label>
                                <select name="location_id" class="form-control select2" style="width:100%">
                                    <option value="">Seleccionar...</option>
                                    @foreach($locations as $id => $name)
                                        <option value="{{ $id }}" {{ $order->location_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Referencia:</label>
                                <input type="text" name="reference" class="form-control" value="{{ $order->reference }}" placeholder="Nº factura proveedor, etc.">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Orden: <span class="text-danger">*</span></label>
                                <input type="date" name="order_date" class="form-control" value="{{ $order->order_date->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Entrega Esperada:</label>
                                <input type="date" name="expected_delivery_date" class="form-control" value="{{ $order->expected_delivery_date ? $order->expected_delivery_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Entrega Real:</label>
                                <input type="date" name="actual_delivery_date" class="form-control" value="{{ $order->actual_delivery_date ? $order->actual_delivery_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Método Envío:</label>
                                <input type="text" name="shipping_method" class="form-control" value="{{ $order->shipping_method }}" placeholder="UPS, DHL, etc.">
                            </div>
                        </div>
                    </div>
                @endcomponent

                <!-- Líneas de productos -->
                @component('components.widget', ['class' => 'box-success', 'title' => '<i class="fas fa-boxes"></i> Items de la Orden'])
                    <div class="tw-mb-3">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fas fa-search"></i></span>
                            <input type="text" id="product_search" class="form-control" placeholder="Buscar producto por nombre o SKU...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered product-line-table" id="product_lines_table">
                            <thead style="background:#f5f5f5;">
                                <tr>
                                    <th style="width:25%;">Producto</th>
                                    <th>SKU</th>
                                    <th style="width:8%;">Cant.</th>
                                    <th>Unidad</th>
                                    <th style="width:10%;">P. Unit.</th>
                                    <th style="width:8%;">IVA %</th>
                                    <th style="width:8%;">Desc.%</th>
                                    <th style="width:12%;">Total</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="product_lines_body">
                                @foreach($order->lines as $index => $line)
                                <tr class="product-line">
                                    <td>
                                        <input type="hidden" name="lines[{{ $index }}][product_id]" value="{{ $line->product_id }}">
                                        <input type="hidden" name="lines[{{ $index }}][variation_id]" value="{{ $line->variation_id }}">
                                        <input type="text" name="lines[{{ $index }}][product_name]" class="form-control" value="{{ $line->product_name }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="lines[{{ $index }}][sku]" class="form-control" value="{{ $line->sku }}">
                                    </td>
                                    <td>
                                        <input type="number" name="lines[{{ $index }}][quantity]" class="form-control line-qty" value="{{ $line->quantity }}" step="0.01" min="0.01" required>
                                    </td>
                                    <td>
                                        <input type="text" name="lines[{{ $index }}][unit]" class="form-control" value="{{ $line->unit }}" placeholder="UND">
                                    </td>
                                    <td>
                                        <input type="number" name="lines[{{ $index }}][unit_price]" class="form-control line-price" value="{{ $line->unit_price }}" step="0.01" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" name="lines[{{ $index }}][tax_percent]" class="form-control line-tax" value="{{ $line->tax_percent }}" step="0.01" min="0">
                                    </td>
                                    <td>
                                        <input type="number" name="lines[{{ $index }}][discount_percent]" class="form-control line-disc" value="{{ $line->discount_percent }}" step="0.01" min="0" max="100">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control line-total" readonly style="background:#f0f0f0;font-weight:bold;" value="{{ number_format($line->line_total, 2) }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-danger remove-line"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="button" id="add_manual_line" class="btn btn-sm btn-default">
                        <i class="fas fa-plus"></i> Agregar línea manual
                    </button>
                @endcomponent
            </div>

            <div class="col-md-4">
                @component('components.widget', ['class' => 'box-default', 'title' => 'Notas y Términos'])
                    <div class="form-group">
                        <label>Notas:</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Notas internas...">{{ $order->notes }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Términos y Condiciones:</label>
                        <textarea name="terms" class="form-control" rows="3" placeholder="Condiciones de pago, garantías...">{{ $order->terms_conditions }}</textarea>
                    </div>
                @endcomponent

                @component('components.widget', ['class' => 'box-warning', 'title' => '<i class="fas fa-calculator"></i> Resumen'])
                    <div class="tw-space-y-2">
                        <div class="tw-flex tw-justify-between">
                            <span>Subtotal:</span>
                            <strong id="display_subtotal">$0.00</strong>
                        </div>
                        <div>
                            <label class="tw-text-sm">Descuento Global:</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="discount_amount" class="form-control" id="global_discount" step="0.01" min="0" value="{{ $order->discount_amount }}">
                                <span class="input-group-addon">
                                    <select name="discount_type" id="discount_type" style="border:none;background:transparent;">
                                        <option value="fixed" {{ $order->discount_type == 'fixed' ? 'selected' : '' }}>$</option>
                                        <option value="percentage" {{ $order->discount_type == 'percentage' ? 'selected' : '' }}>%</option>
                                    </select>
                                </span>
                            </div>
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <span>Impuestos:</span>
                            <strong id="display_taxes">$0.00</strong>
                        </div>
                        <hr>
                        <div class="total-highlight tw-text-center">
                            TOTAL: <span id="display_total">$0.00</span>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-save"></i> Actualizar Orden
                    </button>
                    <a href="{{ route('order-pedidos.show', $order->id) }}" class="btn btn-default btn-block">
                        <i class="fas fa-eye"></i> Ver Detalle
                    </a>
                    <a href="{{ route('order-pedidos.index') }}" class="btn btn-default btn-block">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                @endcomponent
            </div>
        </div>
    </form>
</section>

@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var lineIndex = {{ $order->lines->count() }};

    if ($('.select2').length) {
        $('.select2').select2();
    }

    // Búsqueda de productos
    $('#product_search').autocomplete({
        source: function(request, response) {
            $.get('{{ url("/order-pedidos/search-products") }}', { term: request.term }, function(data) {
                response(data);
            });
        },
        minLength: 2,
        select: function(event, ui) {
            addProductLine(ui.item);
            $(this).val('');
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>").append("<div><strong>" + item.label + "</strong><br><small class='text-muted'>SKU: " + (item.sku || 'N/A') + " | Precio: $" + parseFloat(item.price).toFixed(2) + "</small></div>").appendTo(ul);
    };

    function addProductLine(product) {
        var html = `<tr class="product-line">
            <td>
                <input type="hidden" name="lines[${lineIndex}][product_id]" value="${product.product_id || ''}">
                <input type="hidden" name="lines[${lineIndex}][variation_id]" value="${product.variation_id || ''}">
                <input type="text" name="lines[${lineIndex}][product_name]" class="form-control" value="${product.product_name || product.label || ''}" required>
            </td>
            <td><input type="text" name="lines[${lineIndex}][sku]" class="form-control" value="${product.sku || ''}"></td>
            <td><input type="number" name="lines[${lineIndex}][quantity]" class="form-control line-qty" value="1" step="0.01" min="0.01" required></td>
            <td><input type="text" name="lines[${lineIndex}][unit]" class="form-control" value="${product.unit || 'UND'}" placeholder="UND"></td>
            <td><input type="number" name="lines[${lineIndex}][unit_price]" class="form-control line-price" value="${product.price || 0}" step="0.01" min="0" required></td>
            <td><input type="number" name="lines[${lineIndex}][tax_percent]" class="form-control line-tax" value="22" step="0.01" min="0"></td>
            <td><input type="number" name="lines[${lineIndex}][discount_percent]" class="form-control line-disc" value="0" step="0.01" min="0" max="100"></td>
            <td><input type="text" class="form-control line-total" readonly style="background:#f0f0f0;font-weight:bold;" value="0.00"></td>
            <td><button type="button" class="btn btn-xs btn-danger remove-line"><i class="fas fa-times"></i></button></td>
        </tr>`;
        $('#product_lines_body').append(html);
        lineIndex++;
        recalculateTotals();
    }

    // Agregar línea manual
    $('#add_manual_line').click(function() {
        addProductLine({ product_name: '', sku: '', price: 0, unit: 'UND' });
    });

    // Eliminar línea
    $(document).on('click', '.remove-line', function() {
        $(this).closest('tr').remove();
        recalculateTotals();
    });

    // Recalcular al cambiar valores
    $(document).on('input change', '.line-qty, .line-price, .line-tax, .line-disc, #global_discount, #discount_type', function() {
        recalculateTotals();
    });

    function recalculateTotals() {
        var subtotal = 0;
        var totalTax = 0;

        $('#product_lines_body tr').each(function() {
            var qty = parseFloat($(this).find('.line-qty').val()) || 0;
            var price = parseFloat($(this).find('.line-price').val()) || 0;
            var taxPct = parseFloat($(this).find('.line-tax').val()) || 0;
            var discPct = parseFloat($(this).find('.line-disc').val()) || 0;

            var lineSubtotal = qty * price;
            var discountAmt = lineSubtotal * (discPct / 100);
            var afterDiscount = lineSubtotal - discountAmt;
            var taxAmt = afterDiscount * (taxPct / 100);
            var lineTotal = afterDiscount + taxAmt;

            $(this).find('.line-total').val(lineTotal.toFixed(2));
            subtotal += afterDiscount;
            totalTax += taxAmt;
        });

        var globalDiscType = $('#discount_type').val();
        var globalDiscVal = parseFloat($('#global_discount').val()) || 0;
        var globalDiscAmt = 0;
        if (globalDiscType === 'percentage') {
            globalDiscAmt = subtotal * (globalDiscVal / 100);
        } else {
            globalDiscAmt = globalDiscVal;
        }

        var total = subtotal - globalDiscAmt + totalTax;

        $('#display_subtotal').text('$' + subtotal.toFixed(2));
        $('#display_taxes').text('$' + totalTax.toFixed(2));
        $('#display_total').text('$' + total.toFixed(2));
    }

    // Calcular totales iniciales
    recalculateTotals();

    // Submit form
    $('#order_form').on('submit', function(e) {
        e.preventDefault();

        if ($('#product_lines_body tr').length === 0) {
            toastr.error('Debe agregar al menos un item');
            return;
        }

        // Deshabilitar botón
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        var formAction = $(this).attr('action');
        var formData = $(this).serialize();

        $.ajax({
            url: formAction,
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.msg || 'Orden actualizada');
                    setTimeout(function() {
                        window.location.href = res.redirect || '/order-pedidos/{{ $order->id }}';
                    }, 500);
                } else {
                    toastr.error(res.msg || 'Error al guardar');
                    $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar Orden');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.status, xhr.responseText);
                if (xhr.status === 419) {
                    toastr.error('Sesión expirada. Recarga la página.');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error('Error HTTP ' + xhr.status + '. Revisa la consola.');
                }
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar Orden');
            }
        });
    });
});
</script>
@endsection
