@extends('layouts.app')
@section('title', 'Órdenes de Pedido')

@section('content')

<!-- Content Header -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-clipboard-list"></i> Órdenes de Pedido
    </h1>
</section>

<!-- Stats Cards -->
<section class="content no-print">
    <div class="row">
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="info-box bg-light" style="border-left: 4px solid #3c8dbc;">
                <span class="info-box-icon"><i class="fas fa-file-alt" style="color:#3c8dbc"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Órdenes</span>
                    <span class="info-box-number">{{ $stats['total'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="info-box bg-light" style="border-left: 4px solid #f39c12;">
                <span class="info-box-icon"><i class="fas fa-clock" style="color:#f39c12"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendientes</span>
                    <span class="info-box-number">{{ $stats['pending'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="info-box bg-light" style="border-left: 4px solid #0073b7;">
                <span class="info-box-icon"><i class="fas fa-cogs" style="color:#0073b7"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">En Proceso</span>
                    <span class="info-box-number">{{ $stats['in_progress'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="info-box bg-light" style="border-left: 4px solid #00a65a;">
                <span class="info-box-icon"><i class="fas fa-check-circle" style="color:#00a65a"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Completadas</span>
                    <span class="info-box-number">{{ $stats['completed'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="info-box bg-light" style="border-left: 4px solid #dd4b39;">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle" style="color:#dd4b39"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Vencidas</span>
                    <span class="info-box-number">{{ $stats['overdue'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="info-box bg-light" style="border-left: 4px solid #605ca8;">
                <span class="info-box-icon"><i class="fas fa-dollar-sign" style="color:#605ca8"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor Total</span>
                    <span class="info-box-number">${{ number_format($stats['total_value'], 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y botones -->
    @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
            <div class="box-tools">
                <a href="{{ route('order-pedidos.create') }}" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-success tw-text-white">
                    <i class="fas fa-plus"></i> Nueva Orden
                </a>
                <a href="{{ url('/order-pedidos/my-tasks') }}" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-info tw-text-white">
                    <i class="fas fa-tasks"></i> Mis Tareas
                </a>
            </div>
        @endslot

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Estado:</label>
                    <select id="filter_status" class="form-control select2" style="width:100%">
                        <option value="">Todos</option>
                        <option value="draft">Borrador</option>
                        <option value="pending">Pendiente</option>
                        <option value="approved">Aprobada</option>
                        <option value="in_progress">En Proceso</option>
                        <option value="partial">Parcial</option>
                        <option value="completed">Completada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Prioridad:</label>
                    <select id="filter_priority" class="form-control select2" style="width:100%">
                        <option value="">Todas</option>
                        <option value="low">Baja</option>
                        <option value="medium">Media</option>
                        <option value="high">Alta</option>
                        <option value="urgent">Urgente</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Rango de Fechas:</label>
                    <input type="text" id="filter_date_range" class="form-control" placeholder="Seleccionar fechas" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>&nbsp;</label><br>
                    <button id="btn_clear_filters" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-outline tw-dw-btn-secondary">
                        <i class="fas fa-times"></i> Limpiar filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla DataTable -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="order_pedidos_table" style="width:100%">
                <thead>
                    <tr>
                        <th>Nº Orden</th>
                        <th>Proveedor/Cliente</th>
                        <th>Fecha</th>
                        <th>Entrega Esperada</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Total</th>
                        <th>Progreso</th>
                        <th>Tareas</th>
                        <th>Acciones</th>
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
    // DataTable
    var ordersTable = $('#order_pedidos_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("order-pedidos.index") }}',
            data: function(d) {
                d.status = $('#filter_status').val();
                d.priority = $('#filter_priority').val();
                d.date_range = $('#filter_date_range').val();
            }
        },
        columns: [
            { data: 'order_number', name: 'order_number' },
            { data: 'contact_id', name: 'contact_id' },
            { data: 'order_date', name: 'order_date' },
            { data: 'expected_delivery_date', name: 'expected_delivery_date' },
            { data: 'status', name: 'status' },
            { data: 'priority', name: 'priority' },
            { data: 'total', name: 'total' },
            { data: 'progress', name: 'progress', orderable: false, searchable: false },
            { data: 'tasks_count', name: 'tasks_count', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[2, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
        },
        pageLength: 25,
    });

    // Filtros
    $('#filter_status, #filter_priority').change(function() {
        ordersTable.ajax.reload();
    });

    $('#btn_clear_filters').click(function() {
        $('#filter_status').val('').trigger('change');
        $('#filter_priority').val('').trigger('change');
        $('#filter_date_range').val('');
        ordersTable.ajax.reload();
    });

    // Daterangepicker
    if (typeof $.fn.daterangepicker !== 'undefined') {
        $('#filter_date_range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Limpiar',
                applyLabel: 'Aplicar',
                format: 'DD/MM/YYYY',
            }
        });
        $('#filter_date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            ordersTable.ajax.reload();
        });
        $('#filter_date_range').on('cancel.daterangepicker', function() {
            $(this).val('');
            ordersTable.ajax.reload();
        });
    }

    // Eliminar orden
    $(document).on('click', '.delete-order', function(e) {
        e.preventDefault();
        var url = $(this).data('href');
        swal({
            title: '¿Está seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            buttons: ['Cancelar', 'Sí, eliminar'],
            dangerMode: true,
        }).then(function(willDelete) {
            if (willDelete) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            ordersTable.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });
});
</script>
@endsection
