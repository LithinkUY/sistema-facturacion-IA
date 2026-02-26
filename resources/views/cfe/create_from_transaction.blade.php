@extends('layouts.app')
@section('title', 'Generar CFE desde Venta')

@section('content')
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-file-invoice"></i> Generar CFE desde Venta
        <small>{{ $transaction->invoice_no }}</small>
    </h1>
</section>

<section class="content no-print">
    {!! Form::open(['route' => ['cfe.store-from-transaction', $transaction->id], 'method' => 'POST', 'id' => 'cfe_from_transaction_form']) !!}
    
    <div class="row">
        <div class="col-md-8">
            {{-- Datos de la Venta --}}
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Información de la Venta'])
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-condensed">
                            <tr>
                                <th>Número de Factura:</th>
                                <td><strong>{{ $transaction->invoice_no }}</strong></td>
                            </tr>
                            <tr>
                                <th>Fecha:</th>
                                <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Estado de Pago:</th>
                                <td>
                                    @if($transaction->payment_status === 'paid')
                                        <span class="label label-success">Pagado</span>
                                    @elseif($transaction->payment_status === 'partial')
                                        <span class="label label-warning">Parcial</span>
                                    @else
                                        <span class="label label-danger">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Sucursal:</th>
                                <td>{{ optional($transaction->location)->name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>Cliente</h4>
                        @if($transaction->contact)
                            <address>
                                <strong>{{ $transaction->contact->name }}</strong><br>
                                @if($transaction->contact->tax_number)
                                    RUT/CI: {{ $transaction->contact->tax_number }}<br>
                                @endif
                                @if($transaction->contact->landmark)
                                    {{ $transaction->contact->landmark }}<br>
                                @endif
                                {{ $transaction->contact->city ?? '' }} {{ $transaction->contact->state ?? '' }}
                            </address>
                        @else
                            <p class="text-muted">Consumidor Final</p>
                        @endif
                    </div>
                </div>
            @endcomponent

            {{-- Detalle de Productos --}}
            @component('components.widget', ['class' => 'box-info', 'title' => 'Detalle de la Venta'])
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary">
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-right">Precio Unit.</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->sell_lines as $index => $line)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ optional($line->product)->name ?? 'Producto' }}
                                @if($line->sell_line_note)
                                    <br><small class="text-muted">{{ $line->sell_line_note }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($line->quantity, 2) }}</td>
                            <td class="text-right">${{ number_format($line->unit_price_inc_tax, 2) }}</td>
                            <td class="text-right">${{ number_format($line->quantity * $line->unit_price_inc_tax, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if($transaction->total_before_tax)
                        <tr>
                            <th colspan="4" class="text-right">Subtotal:</th>
                            <th class="text-right">${{ number_format($transaction->total_before_tax, 2) }}</th>
                        </tr>
                        @endif
                        @if($transaction->tax_amount)
                        <tr>
                            <th colspan="4" class="text-right">IVA:</th>
                            <th class="text-right">${{ number_format($transaction->tax_amount, 2) }}</th>
                        </tr>
                        @endif
                        @if($transaction->discount_amount)
                        <tr>
                            <th colspan="4" class="text-right">Descuento:</th>
                            <th class="text-right">-${{ number_format($transaction->discount_amount, 2) }}</th>
                        </tr>
                        @endif
                        <tr class="bg-success">
                            <th colspan="4" class="text-right" style="font-size: 16px;">TOTAL:</th>
                            <th class="text-right" style="font-size: 16px;">${{ number_format($transaction->final_total, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            @endcomponent
        </div>

        <div class="col-md-4">
            {{-- Opciones del CFE --}}
            @component('components.widget', ['class' => 'box-warning', 'title' => 'Opciones del CFE'])
                <div class="form-group">
                    <label for="cfe_type">Tipo de CFE: <span class="text-danger">*</span></label>
                    <select name="cfe_type" id="cfe_type" class="form-control" required>
                        @foreach($cfe_types as $code => $name)
                            <option value="{{ $code }}" {{ $code == $default_cfe_type ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="help-block">
                        @if($transaction->contact && $transaction->contact->tax_number)
                            @php
                                $doc = preg_replace('/[^0-9]/', '', $transaction->contact->tax_number);
                            @endphp
                            @if(strlen($doc) === 12)
                                <span class="text-info"><i class="fas fa-info-circle"></i> Cliente con RUT - Se sugiere e-Factura (111)</span>
                            @else
                                <span class="text-info"><i class="fas fa-info-circle"></i> Cliente con CI - Se sugiere e-Ticket (101)</span>
                            @endif
                        @else
                            <span class="text-muted"><i class="fas fa-user"></i> Consumidor Final - Se sugiere e-Ticket (101)</span>
                        @endif
                    </p>
                </div>

                <div class="form-group">
                    <label for="payment_method">Forma de Pago:</label>
                    <select name="payment_method" id="payment_method" class="form-control">
                        @foreach($payment_methods as $code => $name)
                            <option value="{{ $code }}" {{ $transaction->payment_status === 'paid' && $code == 1 ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="auto_submit" value="1" 
                               {{ config('cfe.auto_submit') ? 'checked' : '' }}>
                        Enviar automáticamente a DGI
                    </label>
                </div>

                <hr>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Ambiente:</strong> 
                    @if(config('cfe.environment') === 'production')
                        <span class="label label-success">Producción</span>
                    @else
                        <span class="label label-warning">Testing</span>
                    @endif
                </div>
            @endcomponent

            {{-- Botones de Acción --}}
            @component('components.widget', ['class' => 'box-success'])
                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-lg btn-block">
                        <i class="fas fa-file-invoice"></i> Generar CFE
                    </button>
                </div>
                <div class="form-group">
                    <a href="{{ route('sells.index') }}" class="btn btn-default btn-block">
                        <i class="fas fa-arrow-left"></i> Volver a Ventas
                    </a>
                </div>
            @endcomponent
        </div>
    </div>

    {!! Form::close() !!}
</section>
@stop

@section('javascript')
<script>
$(document).ready(function() {
    // Confirmar antes de generar
    $('#cfe_from_transaction_form').submit(function(e) {
        e.preventDefault();
        var form = this;
        
        swal({
            title: '¿Generar CFE?',
            text: 'Se generará un Comprobante Fiscal Electrónico para esta venta.',
            icon: 'info',
            buttons: ['Cancelar', 'Generar'],
        }).then((confirm) => {
            if (confirm) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
