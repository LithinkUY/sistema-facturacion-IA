@extends('layouts.app')
@section('title', 'Crear CFE - Factura/Ticket Electrónico')

@section('content')
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-file-invoice"></i> Crear Comprobante Fiscal Electrónico
    </h1>
</section>

<section class="content no-print">
    <div class="row">
        <div class="col-md-12">
            {!! Form::open(['route' => 'cfe.store', 'method' => 'POST', 'id' => 'cfe_form']) !!}
            
            {{-- Información del Documento --}}
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Información del Documento'])
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cfe_type">Tipo de CFE: <span class="text-danger">*</span></label>
                            <select name="cfe_type" id="cfe_type" class="form-control select2" required>
                                @foreach($cfe_types as $code => $name)
                                    <option value="{{ $code }}" {{ $code == $default_cfe_type ? 'selected' : '' }}>
                                        {{ $code }} - {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="series">Serie:</label>
                            <input type="text" name="series" id="series" class="form-control" 
                                   value="{{ $default_series }}" maxlength="2">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="number">Número:</label>
                            <input type="text" id="number" class="form-control" 
                                   value="{{ str_pad($next_number, 7, '0', STR_PAD_LEFT) }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="issue_date">Fecha Emisión:</label>
                            <input type="date" name="issue_date" id="issue_date" class="form-control" 
                                   value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="payment_method">Forma de Pago:</label>
                            <select name="payment_method" id="payment_method" class="form-control">
                                @foreach($payment_methods as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="location_id">Sucursal:</label>
                            <select name="location_id" id="location_id" class="form-control select2">
                                @foreach($business_locations as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="currency">Moneda:</label>
                            <select name="currency" id="currency" class="form-control">
                                <option value="UYU" selected>UYU - Peso Uruguayo</option>
                                <option value="USD">USD - Dólar</option>
                                <option value="EUR">EUR - Euro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="exchange_rate">Tipo de Cambio:</label>
                            <input type="number" name="exchange_rate" id="exchange_rate" class="form-control" 
                                   value="1" step="0.0001" min="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="due_date">Fecha Vencimiento:</label>
                            <input type="date" name="due_date" id="due_date" class="form-control" 
                                   value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
            @endcomponent

            {{-- Datos del Cliente (Receptor) --}}
            @component('components.widget', ['class' => 'box-info', 'title' => 'Datos del Cliente (Receptor)'])
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_id">Cliente: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="customer_id" id="customer_id" class="form-control select2" required style="width: 100%;">
                                    <option value="">Seleccionar cliente...</option>
                                    @foreach($customers as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default bg-white btn-flat add_new_customer" 
                                            data-name="" title="Agregar nuevo cliente">
                                        <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>RUT/CI:</label>
                            <input type="text" id="customer_document" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo Doc:</label>
                            <input type="text" id="customer_doc_type" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Dirección:</label>
                            <input type="text" id="customer_address" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ciudad:</label>
                            <input type="text" id="customer_city" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Departamento:</label>
                            <input type="text" id="customer_department" class="form-control" readonly>
                        </div>
                    </div>
                </div>
            @endcomponent

            {{-- Productos/Servicios --}}
            @component('components.widget', ['class' => 'box-success', 'title' => 'Detalle de Productos/Servicios'])
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered" id="items_table">
                            <thead class="bg-primary">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">Producto/Servicio</th>
                                    <th width="10%">Cantidad</th>
                                    <th width="10%">Unidad</th>
                                    <th width="15%">Precio Unit.</th>
                                    <th width="10%">IVA %</th>
                                    <th width="15%">Subtotal</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody id="items_body">
                                <tr class="item-row" data-row="0">
                                    <td>1</td>
                                    <td>
                                        <div class="input-mode-toggle mb-1">
                                            <label class="radio-inline" style="font-size:12px;">
                                                <input type="radio" name="items[0][input_mode]" value="select" class="input-mode-radio" data-row="0" checked>
                                                <i class="fa fa-search"></i> Del sistema
                                            </label>
                                            <label class="radio-inline" style="font-size:12px;">
                                                <input type="radio" name="items[0][input_mode]" value="manual" class="input-mode-radio" data-row="0">
                                                <i class="fa fa-pencil"></i> Manual
                                            </label>
                                        </div>
                                        <div class="product-select-wrapper" data-row="0">
                                            <select name="items[0][product_id]" class="form-control select2 product-select">
                                                <option value="">Seleccionar producto...</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                            data-price="{{ $product->variations->first()->sell_price_inc_tax ?? 0 }}"
                                                            data-name="{{ $product->name }}">
                                                        {{ $product->name }} ({{ $product->sku }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="product-manual-wrapper" data-row="0" style="display:none;">
                                            <input type="text" name="items[0][name]" class="form-control item-name" 
                                                   placeholder="Escribir nombre del producto/servicio...">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control item-quantity" 
                                               value="1" min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][unit]" class="form-control" value="unidad">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][unit_price]" class="form-control item-price" 
                                               value="0" min="0" step="0.01" required>
                                    </td>
                                    <td>
                                        <select name="items[0][iva_rate]" class="form-control item-iva">
                                            <option value="22" selected>22%</option>
                                            <option value="10">10%</option>
                                            <option value="0">Exento</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control item-subtotal" value="0.00" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-xs remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="8">
                                        <button type="button" class="btn btn-success btn-sm" id="add_item">
                                            <i class="fas fa-plus"></i> Agregar línea
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endcomponent

            {{-- Totales --}}
            @component('components.widget', ['class' => 'box-warning', 'title' => 'Totales'])
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notes">Notas / Observaciones:</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th class="text-right">Subtotal (sin IVA):</th>
                                <td class="text-right">
                                    <span class="currency">$</span>
                                    <span id="total_subtotal">0.00</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-right">IVA Tasa Mínima (10%):</th>
                                <td class="text-right">
                                    <span class="currency">$</span>
                                    <span id="total_iva_min">0.00</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-right">IVA Tasa Básica (22%):</th>
                                <td class="text-right">
                                    <span class="currency">$</span>
                                    <span id="total_iva_basica">0.00</span>
                                </td>
                            </tr>
                            <tr class="bg-success">
                                <th class="text-right" style="font-size: 18px;">TOTAL:</th>
                                <td class="text-right" style="font-size: 18px; font-weight: bold;">
                                    <span class="currency">$</span>
                                    <span id="total_final">0.00</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endcomponent

            {{-- Botones de acción --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary btn-lg" id="btn_save">
                            <i class="fas fa-save"></i> Guardar y Generar CFE
                        </button>
                        <button type="button" class="btn btn-success btn-lg" id="btn_save_send">
                            <i class="fas fa-paper-plane"></i> Guardar y Enviar a DGI
                        </button>
                        <a href="{{ route('cfe.index') }}" class="btn btn-default btn-lg">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</section>

{{-- Modal para agregar cliente --}}
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    @include('contact.create', ['quick_add' => true])
</div>

{{-- Modal de vista --}}
<div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
@stop

@section('javascript')
<script>
$(document).ready(function() {
    var rowCount = 1;

    // Inicializar select2
    $('.select2').select2();

    // Toggle entre modo seleccionar producto y modo manual
    $(document).on('change', '.input-mode-radio', function() {
        var row = $(this).data('row');
        var mode = $(this).val();
        if (mode === 'manual') {
            $(`.product-select-wrapper[data-row="${row}"]`).hide();
            $(`.product-manual-wrapper[data-row="${row}"]`).show();
            // Limpiar el select de producto y quitar required
            $(`.product-select-wrapper[data-row="${row}"] select`).val('').trigger('change.select2');
        } else {
            $(`.product-select-wrapper[data-row="${row}"]`).show();
            $(`.product-manual-wrapper[data-row="${row}"]`).hide();
            // Limpiar el campo manual
            $(`.product-manual-wrapper[data-row="${row}"] input`).val('');
        }
    });

    // Botón agregar nuevo cliente - abrir modal
    $(document).on('click', '.add_new_customer', function() {
        $('.contact_modal')
            .find('select#contact_type')
            .val('customer')
            .closest('div.contact_type_div')
            .addClass('hide');
        $('.contact_modal').modal('show');
    });

    // Cuando se guarda un cliente nuevo, agregarlo al select
    $(document).on('submit', 'form#quick_add_contact', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();

        $.ajax({
            method: 'POST',
            url: form.attr('action'),
            dataType: 'json',
            data: data,
            beforeSend: function(xhr) {
                __disable_submit_button(form.find('button[type="submit"]'));
            },
            success: function(result) {
                if (result.success == true) {
                    // Agregar nuevo cliente al select
                    var newOption = new Option(result.data.name, result.data.id, true, true);
                    $('#customer_id').append(newOption).trigger('change');
                    
                    // Cerrar modal
                    $('div.contact_modal').modal('hide');
                    toastr.success(result.msg);
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function(xhr) {
                toastr.error('Error al guardar cliente');
            }
        });
    });

    // Reset form cuando se cierra el modal
    $('.contact_modal').on('hidden.bs.modal', function() {
        $('form#quick_add_contact')[0].reset();
    });

    // Agregar línea de item
    $('#add_item').click(function() {
        var newRow = `
            <tr class="item-row" data-row="${rowCount}">
                <td>${rowCount + 1}</td>
                <td>
                    <div class="input-mode-toggle mb-1">
                        <label class="radio-inline" style="font-size:12px;">
                            <input type="radio" name="items[${rowCount}][input_mode]" value="select" class="input-mode-radio" data-row="${rowCount}" checked>
                            <i class="fa fa-search"></i> Del sistema
                        </label>
                        <label class="radio-inline" style="font-size:12px;">
                            <input type="radio" name="items[${rowCount}][input_mode]" value="manual" class="input-mode-radio" data-row="${rowCount}">
                            <i class="fa fa-pencil"></i> Manual
                        </label>
                    </div>
                    <div class="product-select-wrapper" data-row="${rowCount}">
                        <select name="items[${rowCount}][product_id]" class="form-control select2 product-select">
                            <option value="">Seleccionar producto...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                        data-price="{{ $product->variations->first()->sell_price_inc_tax ?? 0 }}"
                                        data-name="{{ $product->name }}">
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="product-manual-wrapper" data-row="${rowCount}" style="display:none;">
                        <input type="text" name="items[${rowCount}][name]" class="form-control item-name" 
                               placeholder="Escribir nombre del producto/servicio...">
                    </div>
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][quantity]" class="form-control item-quantity" 
                           value="1" min="0.01" step="0.01" required>
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][unit]" class="form-control" value="unidad">
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][unit_price]" class="form-control item-price" 
                           value="0" min="0" step="0.01" required>
                </td>
                <td>
                    <select name="items[${rowCount}][iva_rate]" class="form-control item-iva">
                        <option value="22" selected>22%</option>
                        <option value="10">10%</option>
                        <option value="0">Exento</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control item-subtotal" value="0.00" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-xs remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#items_body').append(newRow);
        $('#items_body tr:last .select2').select2();
        rowCount++;
    });

    // Eliminar línea
    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
        } else {
            toastr.warning('Debe tener al menos una línea');
        }
    });

    // Seleccionar producto del sistema
    $(document).on('change', '.product-select', function() {
        var row = $(this).closest('tr');
        var selected = $(this).find(':selected');
        var price = selected.data('price') || 0;
        row.find('.item-price').val(parseFloat(price).toFixed(2));
        // Copiar nombre al campo manual por si se necesita
        row.find('.item-name').val(selected.data('name') || '');
        calculateRowTotal(row);
    });

    // Calcular totales al cambiar cantidad, precio o IVA
    $(document).on('change keyup', '.item-quantity, .item-price, .item-iva', function() {
        var row = $(this).closest('tr');
        calculateRowTotal(row);
    });

    function calculateRowTotal(row) {
        var quantity = parseFloat(row.find('.item-quantity').val()) || 0;
        var price = parseFloat(row.find('.item-price').val()) || 0;
        var subtotal = quantity * price;
        row.find('.item-subtotal').val(subtotal.toFixed(2));
        calculateTotals();
    }

    function calculateTotals() {
        var subtotal = 0;
        var ivaMin = 0;
        var ivaBasica = 0;

        $('.item-row').each(function() {
            var quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
            var price = parseFloat($(this).find('.item-price').val()) || 0;
            var ivaRate = parseInt($(this).find('.item-iva').val()) || 0;
            
            var lineSubtotal = quantity * price;
            
            // Calcular base sin IVA
            var baseAmount = lineSubtotal / (1 + (ivaRate / 100));
            var ivaAmount = lineSubtotal - baseAmount;
            
            subtotal += baseAmount;
            
            if (ivaRate === 10) {
                ivaMin += ivaAmount;
            } else if (ivaRate === 22) {
                ivaBasica += ivaAmount;
            }
        });

        var total = subtotal + ivaMin + ivaBasica;

        $('#total_subtotal').text(subtotal.toFixed(2));
        $('#total_iva_min').text(ivaMin.toFixed(2));
        $('#total_iva_basica').text(ivaBasica.toFixed(2));
        $('#total_final').text(total.toFixed(2));
    }

    // Cargar datos del cliente
    $('#customer_id').change(function() {
        var customerId = $(this).val();
        if (customerId) {
            $.get('/contacts/' + customerId, function(data) {
                $('#customer_document').val(data.tax_number || data.mobile || '');
                $('#customer_address').val(data.landmark || data.address_line_1 || '');
                $('#customer_city').val(data.city || 'Montevideo');
                $('#customer_department').val(data.state || 'Montevideo');
                
                // Determinar tipo de documento
                var doc = (data.tax_number || '').replace(/[^0-9]/g, '');
                if (doc.length === 12) {
                    $('#customer_doc_type').val('RUT');
                    // Si tiene RUT, sugerir e-Factura
                    $('#cfe_type').val(111).trigger('change');
                } else {
                    $('#customer_doc_type').val('CI');
                    // Si tiene CI, sugerir e-Ticket
                    $('#cfe_type').val(101).trigger('change');
                }
            });
        }
    });

    // Enviar formulario
    $('#cfe_form').submit(function(e) {
        e.preventDefault();
        
        // Validar que cada fila tenga producto seleccionado o nombre manual
        var valid = true;
        $('.item-row').each(function(idx) {
            var row = $(this);
            var rowNum = row.data('row');
            var mode = row.find('.input-mode-radio:checked').val() || 'select';
            var productId = row.find('.product-select').val();
            var manualName = row.find('.item-name').val();
            var price = parseFloat(row.find('.item-price').val()) || 0;
            
            if (mode === 'select' && !productId) {
                toastr.error('Línea ' + (idx+1) + ': Seleccione un producto del sistema');
                valid = false;
                return false;
            }
            if (mode === 'manual' && (!manualName || manualName.trim() === '')) {
                toastr.error('Línea ' + (idx+1) + ': Escriba el nombre del producto/servicio');
                valid = false;
                return false;
            }
            if (price <= 0) {
                toastr.error('Línea ' + (idx+1) + ': El precio debe ser mayor a 0');
                valid = false;
                return false;
            }
        });
        
        if (!valid) return;
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("cfe.store") }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#btn_save, #btn_save_send').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.msg);
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                var errorMsg = Object.values(errors).flat().join('<br>');
                toastr.error(errorMsg || 'Error al guardar CFE');
            },
            complete: function() {
                $('#btn_save, #btn_save_send').prop('disabled', false);
            }
        });
    });

    // Cambiar moneda
    $('#currency').change(function() {
        var currency = $(this).val();
        $('.currency').text(currency === 'UYU' ? '$' : (currency === 'USD' ? 'US$' : '€'));
        
        if (currency !== 'UYU') {
            $('#exchange_rate').prop('readonly', false).focus();
        } else {
            $('#exchange_rate').val(1).prop('readonly', true);
        }
    });
});
</script>
@endsection
