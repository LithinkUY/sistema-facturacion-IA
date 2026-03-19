@extends('layouts.app')
@section('title', 'Editar CFE - ' . $cfe->series . '-' . $cfe->number)

@section('content')
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-edit"></i> Editar CFE {{ $cfe_types[$cfe->cfe_type] ?? $cfe->cfe_type }}
        <small>{{ $cfe->series }}-{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('cfe.index') }}"><i class="fa fa-home"></i> Facturas CFE</a></li>
        <li><a href="{{ route('cfe.show', $cfe->id) }}">{{ $cfe->series }}-{{ $cfe->number }}</a></li>
        <li class="active">Editar</li>
    </ol>
</section>

<section class="content">
    <form action="{{ route('cfe.update', $cfe->id) }}" method="POST" id="cfe-edit-form">
        @csrf
        @method('PUT')

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="row">
            {{-- Columna principal --}}
            <div class="col-md-8">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'Datos del Comprobante'])

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo CFE</label>
                                <input type="text" class="form-control" readonly
                                    value="{{ $cfe->cfe_type }} - {{ $cfe_types[$cfe->cfe_type] ?? $cfe->cfe_type }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Serie-Número</label>
                                <input type="text" class="form-control" readonly
                                    value="{{ $cfe->series }}-{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha Emisión</label>
                                <input type="text" class="form-control" readonly
                                    value="{{ $cfe->issue_date->format('d/m/Y') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Estado <span class="text-danger">*</span></label>
                                <select name="status" class="form-control select2" required>
                                    <option value="pending"   @selected($cfe->status === 'pending')>Pendiente</option>
                                    <option value="submitted" @selected($cfe->status === 'submitted')>Enviado</option>
                                    <option value="accepted"  @selected($cfe->status === 'accepted')>Aceptado DGI</option>
                                    <option value="rejected"  @selected($cfe->status === 'rejected')>Rechazado</option>
                                    <option value="error"     @selected($cfe->status === 'error')>Error</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Forma de Pago <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-control select2" required>
                                    @foreach($payment_methods as $key => $label)
                                        <option value="{{ $key }}" @selected($cfe->payment_method == $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notas / Observaciones</label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Notas internas sobre este comprobante">{{ old('notes', $cfe->notes) }}</textarea>
                    </div>

                @endcomponent

                {{-- Items --}}
                @component('components.widget', ['class' => 'box-success', 'title' => 'Líneas del Comprobante'])
                    <p class="text-muted"><i class="fas fa-info-circle"></i> Puede editar las líneas del comprobante. Deje una línea vacía para eliminarla.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="items-table">
                            <thead class="bg-primary">
                                <tr>
                                    <th>Descripción</th>
                                    <th style="width:90px;">Cantidad</th>
                                    <th style="width:80px;">Unidad</th>
                                    <th style="width:110px;">Precio Unit.</th>
                                    <th style="width:80px;">IVA %</th>
                                    <th style="width:110px;">Subtotal</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="items-tbody">
                                @foreach($cfe->items as $index => $item)
                                <tr class="item-row">
                                    <td>
                                        <input type="text" name="items[{{ $index }}][name]"
                                            class="form-control input-sm"
                                            value="{{ $item['name'] ?? $item['description'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][quantity]"
                                            class="form-control input-sm item-qty"
                                            step="0.01" min="0"
                                            value="{{ $item['quantity'] ?? 1 }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][unit]"
                                            class="form-control input-sm"
                                            value="{{ $item['unit'] ?? 'unidad' }}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][unit_price]"
                                            class="form-control input-sm item-price"
                                            step="0.01" min="0"
                                            value="{{ $item['unit_price'] ?? 0 }}">
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][iva_rate]" class="form-control input-sm item-iva">
                                            @foreach($iva_rates as $rate => $label)
                                                <option value="{{ $rate }}" @selected(($item['iva_rate'] ?? 22) == $rate)>{{ $rate }}%</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="item-subtotal text-right">
                                        ${{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0) * (1 + ($item['iva_rate'] ?? 22) / 100), 2) }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-xs remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7">
                                        <button type="button" class="btn btn-success btn-sm" id="add-item-btn">
                                            <i class="fas fa-plus"></i> Agregar línea
                                        </button>
                                    </td>
                                </tr>
                                <tr class="bg-light">
                                    <td colspan="5" class="text-right"><strong>TOTAL:</strong></td>
                                    <td class="text-right" id="total-display"><strong>${{ number_format($cfe->total, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endcomponent
            </div>

            {{-- Columna lateral --}}
            <div class="col-md-4">
                {{-- Datos CAE --}}
                @component('components.widget', ['class' => 'box-warning', 'title' => 'Datos CAE / DGI'])
                    <div class="form-group">
                        <label>Número CAE</label>
                        <input type="text" name="cae" class="form-control"
                            placeholder="Ej: 12345678901234"
                            value="{{ old('cae', $cfe->cae) }}">
                        <small class="text-muted">Dejar vacío para no modificar</small>
                    </div>
                    <div class="form-group">
                        <label>Vencimiento CAE</label>
                        <input type="date" name="cae_due_date" class="form-control"
                            value="{{ old('cae_due_date', $cfe->cae_due_date ? $cfe->cae_due_date->format('Y-m-d') : '') }}">
                    </div>
                    @if($cfe->track_id)
                    <div class="form-group">
                        <label>Track ID DGI</label>
                        <input type="text" class="form-control" readonly value="{{ $cfe->track_id }}">
                    </div>
                    @endif
                @endcomponent

                {{-- Emisor / Receptor (solo lectura) --}}
                @component('components.widget', ['class' => 'box-default', 'title' => 'Partes del Documento'])
                    <h5><strong>Emisor</strong></h5>
                    <p class="text-muted small">
                        {{ $cfe->emitter_name }}<br>
                        RUT: {{ $cfe->emitter_rut }}<br>
                        {{ $cfe->emitter_address }}<br>
                        {{ $cfe->emitter_city }}, {{ $cfe->emitter_department }}
                    </p>
                    <hr>
                    <h5><strong>Receptor</strong></h5>
                    <p class="text-muted small">
                        {{ $cfe->receiver_name }}<br>
                        {{ $cfe->receiver_doc_type }}: {{ $cfe->receiver_document }}<br>
                        {{ $cfe->receiver_address }}<br>
                        {{ $cfe->receiver_city }}, {{ $cfe->receiver_department }}
                    </p>
                @endcomponent

                {{-- Botones --}}
                @component('components.widget', ['class' => 'box-default'])
                    <a href="{{ route('cfe.show', $cfe->id) }}" class="btn btn-default btn-block">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                @endcomponent
            </div>
        </div>
    </form>
</section>
@stop

@section('javascript')
<script>
$(document).ready(function() {
    var itemCount = {{ count($cfe->items) }};

    // Calcular subtotal de una fila
    function calcRowSubtotal(row) {
        var qty   = parseFloat($(row).find('.item-qty').val()) || 0;
        var price = parseFloat($(row).find('.item-price').val()) || 0;
        var iva   = parseFloat($(row).find('.item-iva').val()) || 0;
        var sub   = qty * price * (1 + iva / 100);
        $(row).find('.item-subtotal').text('$' + sub.toFixed(2));
        return sub;
    }

    // Actualizar total general
    function updateTotal() {
        var total = 0;
        $('#items-tbody .item-row').each(function() {
            total += calcRowSubtotal(this);
        });
        $('#total-display').html('<strong>$' + total.toFixed(2) + '</strong>');
    }

    // Recalcular al cambiar valores
    $(document).on('input change', '.item-qty, .item-price, .item-iva', function() {
        updateTotal();
    });

    // Eliminar fila
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
        updateTotal();
        renumberItems();
    });

    // Re-numerar índices de name
    function renumberItems() {
        $('#items-tbody .item-row').each(function(i) {
            $(this).find('input, select').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/items\[\d+\]/, 'items[' + i + ']'));
                }
            });
        });
        itemCount = $('#items-tbody .item-row').length;
    }

    // Agregar nueva fila
    $('#add-item-btn').on('click', function() {
        var idx = itemCount++;
        var row = '<tr class="item-row">' +
            '<td><input type="text" name="items[' + idx + '][name]" class="form-control input-sm" placeholder="Descripción"></td>' +
            '<td><input type="number" name="items[' + idx + '][quantity]" class="form-control input-sm item-qty" step="0.01" min="0" value="1"></td>' +
            '<td><input type="text" name="items[' + idx + '][unit]" class="form-control input-sm" value="unidad"></td>' +
            '<td><input type="number" name="items[' + idx + '][unit_price]" class="form-control input-sm item-price" step="0.01" min="0" value="0"></td>' +
            '<td><select name="items[' + idx + '][iva_rate]" class="form-control input-sm item-iva">' +
            '<option value="0">0%</option><option value="10">10%</option><option value="22" selected>22%</option>' +
            '</select></td>' +
            '<td class="item-subtotal text-right">$0.00</td>' +
            '<td><button type="button" class="btn btn-danger btn-xs remove-item"><i class="fas fa-trash"></i></button></td>' +
            '</tr>';
        $('#items-tbody').append(row);
    });

    // Inicializar totales
    updateTotal();
});
</script>
@endsection
