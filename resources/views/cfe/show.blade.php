@extends('layouts.app')
@section('title', 'Ver CFE - ' . $cfe->series . '-' . $cfe->number)

@section('content')
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-file-invoice"></i> Detalle CFE {{ $cfe_types[$cfe->cfe_type] ?? $cfe->cfe_type }}
        <small>{{ $cfe->series }}-{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}</small>
    </h1>
</section>

<section class="content no-print">
    <div class="row">
        {{-- Información Principal --}}
        <div class="col-md-8">
            @component('components.widget', ['class' => 'box-primary'])
                @slot('title')
                    <span class="pull-right">
                        @if($cfe->status === 'accepted')
                            <span class="label label-success"><i class="fas fa-check"></i> Aceptado DGI</span>
                        @elseif($cfe->status === 'submitted')
                            <span class="label label-info"><i class="fas fa-clock"></i> Enviado</span>
                        @elseif($cfe->status === 'pending')
                            <span class="label label-warning"><i class="fas fa-hourglass"></i> Pendiente</span>
                        @elseif($cfe->status === 'rejected')
                            <span class="label label-danger"><i class="fas fa-times"></i> Rechazado</span>
                        @else
                            <span class="label label-danger"><i class="fas fa-exclamation"></i> Error</span>
                        @endif
                    </span>
                    Comprobante Fiscal Electrónico
                @endslot

                <div class="row">
                    <div class="col-md-6">
                        <h4><strong>Emisor</strong></h4>
                        <address>
                            <strong>{{ $cfe->emitter_name }}</strong><br>
                            RUT: {{ $cfe->emitter_rut }}<br>
                            {{ $cfe->emitter_address }}<br>
                            {{ $cfe->emitter_city }}, {{ $cfe->emitter_department }}
                        </address>
                    </div>
                    <div class="col-md-6">
                        <h4><strong>Receptor</strong></h4>
                        <address>
                            <strong>{{ $cfe->receiver_name }}</strong><br>
                            {{ $cfe->receiver_doc_type }}: {{ $cfe->receiver_document }}<br>
                            {{ $cfe->receiver_address }}<br>
                            {{ $cfe->receiver_city }}, {{ $cfe->receiver_department }}
                        </address>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-3">
                        <strong>Tipo CFE:</strong><br>
                        {{ $cfe->cfe_type }} - {{ $cfe_types[$cfe->cfe_type] ?? 'Desconocido' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Serie-Número:</strong><br>
                        {{ $cfe->series }}-{{ str_pad($cfe->number, 7, '0', STR_PAD_LEFT) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha Emisión:</strong><br>
                        {{ $cfe->issue_date->format('d/m/Y') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Forma de Pago:</strong><br>
                        {{ $payment_methods[$cfe->payment_method] ?? $cfe->payment_method }}
                    </div>
                </div>

                <hr>

                {{-- Detalle de Items --}}
                <h4><strong>Detalle</strong></h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr class="bg-primary">
                            <th>#</th>
                            <th>Descripción</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-center">Unidad</th>
                            <th class="text-right">P. Unit.</th>
                            <th class="text-center">IVA</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cfe->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['name'] ?? $item['description'] ?? 'Producto' }}</td>
                            <td class="text-center">{{ number_format($item['quantity'], 2) }}</td>
                            <td class="text-center">{{ $item['unit'] ?? 'unidad' }}</td>
                            <td class="text-right">$ {{ number_format($item['unit_price'], 2) }}</td>
                            <td class="text-center">{{ $item['iva_rate'] ?? 22 }}%</td>
                            <td class="text-right">$ {{ number_format($item['line_total'] ?? ($item['quantity'] * $item['unit_price']), 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">Subtotal:</th>
                            <th class="text-right">$ {{ number_format($cfe->subtotal, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-right">IVA:</th>
                            <th class="text-right">$ {{ number_format($cfe->tax_amount, 2) }}</th>
                        </tr>
                        <tr class="bg-success">
                            <th colspan="6" class="text-right" style="font-size: 16px;">TOTAL:</th>
                            <th class="text-right" style="font-size: 16px;">$ {{ number_format($cfe->total, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            @endcomponent
        </div>

        {{-- Panel lateral --}}
        <div class="col-md-4">
            {{-- Acciones --}}
            @component('components.widget', ['class' => 'box-info', 'title' => 'Acciones'])
                <div class="list-group">
                    <a href="{{ route('cfe.print', $cfe->id) }}?format=a4" target="_blank" class="list-group-item">
                        <i class="fas fa-file-invoice text-primary"></i> Imprimir Factura A4
                    </a>
                    <a href="{{ route('cfe.print', $cfe->id) }}?format=ticket" target="_blank" class="list-group-item">
                        <i class="fas fa-receipt text-info"></i> Imprimir Ticket (80mm)
                    </a>
                    <a href="{{ route('cfe.download-xml', $cfe->id) }}" class="list-group-item">
                        <i class="fas fa-file-code text-success"></i> Descargar XML
                    </a>
                    @if($cfe->status !== 'accepted')
                    <a href="#" class="list-group-item resend-cfe" data-id="{{ $cfe->id }}">
                        <i class="fas fa-paper-plane text-info"></i> Reenviar a DGI
                    </a>
                    @endif
                    <a href="{{ route('cfe.index') }}" class="list-group-item">
                        <i class="fas fa-arrow-left text-muted"></i> Volver al listado
                    </a>
                </div>
            @endcomponent

            {{-- Información DGI --}}
            @component('components.widget', ['class' => 'box-warning', 'title' => 'Información DGI'])
                <table class="table table-condensed">
                    <tr>
                        <th>Estado:</th>
                        <td>
                            @if($cfe->status === 'accepted')
                                <span class="text-success"><i class="fas fa-check-circle"></i> Aceptado</span>
                            @elseif($cfe->status === 'submitted')
                                <span class="text-info"><i class="fas fa-clock"></i> Enviado</span>
                            @elseif($cfe->status === 'pending')
                                <span class="text-warning"><i class="fas fa-hourglass-half"></i> Pendiente</span>
                            @else
                                <span class="text-danger"><i class="fas fa-times-circle"></i> {{ ucfirst($cfe->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @if($cfe->cae)
                    <tr>
                        <th>CAE:</th>
                        <td><code>{{ $cfe->cae }}</code></td>
                    </tr>
                    @endif
                    @if($cfe->track_id)
                    <tr>
                        <th>Track ID:</th>
                        <td><code>{{ $cfe->track_id }}</code></td>
                    </tr>
                    @endif
                    <tr>
                        <th>Ambiente:</th>
                        <td>
                            @if(config('cfe.environment') === 'production')
                                <span class="label label-success">Producción</span>
                            @else
                                <span class="label label-warning">Testing</span>
                            @endif
                        </td>
                    </tr>
                    @if($cfe->submitted_at)
                    <tr>
                        <th>Enviado:</th>
                        <td>{{ $cfe->submitted_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                </table>

                @if($cfe->dgi_response && isset($cfe->dgi_response['errors']))
                <div class="alert alert-danger">
                    <strong>Errores DGI:</strong>
                    <ul class="mb-0">
                        @foreach($cfe->dgi_response['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            @endcomponent

            {{-- Código QR --}}
            @if($cfe->status === 'accepted' && $cfe->cae)
            @component('components.widget', ['class' => 'box-default', 'title' => 'Código QR Verificación'])
                <div class="text-center">
                    <div id="qrcode"></div>
                    <small class="text-muted">Escanear para verificar en DGI</small>
                </div>
            @endcomponent
            @endif
        </div>
    </div>
</section>
@stop

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
$(document).ready(function() {
    // Generar QR
    @if($cfe->status === 'accepted' && $cfe->cae)
    var qr = qrcode(0, 'M');
    var qrData = '{{ $cfe->emitter_rut }}|{{ $cfe->cfe_type }}|{{ $cfe->series }}|{{ $cfe->number }}|{{ number_format($cfe->total, 2, ".", "") }}|{{ $cfe->cae }}|{{ $cfe->issue_date->format("Ymd") }}';
    qr.addData(qrData);
    qr.make();
    document.getElementById('qrcode').innerHTML = qr.createImgTag(4);
    @endif

    // Reenviar CFE
    $(document).on('click', '.resend-cfe', function(e) {
        e.preventDefault();
        var cfe_id = $(this).data('id');
        
        swal({
            title: '¿Reenviar CFE a DGI?',
            text: 'Se intentará enviar nuevamente este comprobante.',
            icon: 'warning',
            buttons: ['Cancelar', 'Enviar'],
        }).then((confirm) => {
            if (confirm) {
                $.ajax({
                    url: '/cfe/' + cfe_id + '/resend',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('CFE enviado exitosamente');
                            location.reload();
                        } else {
                            toastr.error('Error: ' + (response.errors ? response.errors.join(', ') : 'Error desconocido'));
                        }
                    },
                    error: function() {
                        toastr.error('Error al enviar CFE');
                    }
                });
            }
        });
    });
});
</script>
@endsection
