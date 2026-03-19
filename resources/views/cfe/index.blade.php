@extends('layouts.app')
@section('title', 'Comprobantes Fiscales Electrónicos (CFE)')

@section('content')
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-file-invoice"></i> Comprobantes Fiscales Electrónicos
        <small class="text-muted">DGI Uruguay</small>
    </h1>
</section>

<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                <label for="cfe_type_filter">Tipo de CFE:</label>
                <select id="cfe_type_filter" class="form-control select2" style="width:100%">
                    <option value="">Todos</option>
                    @foreach($cfe_types as $code => $name)
                        <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="cfe_status_filter">Estado:</label>
                <select id="cfe_status_filter" class="form-control select2" style="width:100%">
                    <option value="">Todos</option>
                    <option value="pending">Pendiente</option>
                    <option value="submitted">Enviado</option>
                    <option value="accepted">Aceptado DGI</option>
                    <option value="rejected">Rechazado</option>
                    <option value="error">Error</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="cfe_date_range">Rango de fechas:</label>
                <input type="text" id="cfe_date_range" class="form-control" placeholder="Seleccionar fechas">
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Lista de CFE'])
        @slot('tool')
            <div class="box-tools">
                <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                    href="{{ route('cfe.create') }}">
                    <i class="fas fa-plus"></i> Nueva Factura/Ticket
                </a>
                <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-green-600 tw-to-green-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right tw-mr-2"
                    href="{{ route('cfe.settings') }}">
                    <i class="fas fa-cog"></i> Configuración
                </a>
            </div>
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="cfe_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>Fecha</th>
                        <th>Serie-Número</th>
                        <th>Tipo CFE</th>
                        <th>Cliente</th>
                        <th>RUT/CI</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>CAE</th>
                        <th>Vto. CAE</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @endcomponent
</section>
@stop

@section('javascript')
<script>
$(document).ready(function() {
    // Inicializar DataTable
    var cfe_table = $('#cfe_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("cfe.index") }}',
            data: function(d) {
                d.cfe_type = $('#cfe_type_filter').val();
                d.status = $('#cfe_status_filter').val();
                d.date_range = $('#cfe_date_range').val();
            }
        },
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'series', name: 'series', render: function(data, type, row) {
                return row.series + '-' + String(row.number).padStart(7, '0');
            }},
            { data: 'cfe_type_name', name: 'cfe_type' },
            { data: 'receiver_name', name: 'receiver_name' },
            { data: 'receiver_document', name: 'receiver_document' },
            { data: 'total', name: 'total' },
            { data: 'status_badge', name: 'status' },
            { data: 'cae', name: 'cae' },
            { data: 'cae_due_date_formatted', name: 'cae_due_date', orderable: true, searchable: false,
              render: function(data) {
                  return data ? data : '<span class="text-muted">Pendiente</span>';
              }
            }
        ],
        order: [[1, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        }
    });

    // Filtros
    $('#cfe_type_filter, #cfe_status_filter').change(function() {
        cfe_table.ajax.reload();
    });

    // Date range picker
    $('#cfe_date_range').daterangepicker({
        autoUpdateInput: false,
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Aplicar',
            cancelLabel: 'Limpiar',
            fromLabel: 'Desde',
            toLabel: 'Hasta'
        }
    });

    $('#cfe_date_range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        cfe_table.ajax.reload();
    });

    $('#cfe_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        cfe_table.ajax.reload();
    });

    // Reenviar CFE
    $(document).on('click', '.resend-cfe', function(e) {
        e.preventDefault();
        var cfe_id = $(this).data('id');
        
        swal({
            title: '¿Reenviar CFE a DGI?',
            text: 'Se intentará enviar nuevamente este comprobante a DGI.',
            icon: 'warning',
            buttons: ['Cancelar', 'Enviar'],
            dangerMode: false,
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
                            toastr.success('CFE enviado exitosamente a DGI');
                            cfe_table.ajax.reload();
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
