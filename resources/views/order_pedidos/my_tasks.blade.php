@extends('layouts.app')
@section('title', 'Mis Tareas')

@section('css')
<style>
    .task-section { margin-bottom: 30px; }
    .task-section-title { font-weight: 700; font-size: 1.1em; padding: 8px 12px; border-radius: 4px; margin-bottom: 12px; }
    .task-section-title.overdue { background: #fce4e4; color: #c0392b; }
    .task-section-title.today { background: #fff8e1; color: #f39c12; }
    .task-section-title.upcoming { background: #e8f5e9; color: #27ae60; }
    .my-task-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 15px; margin-bottom: 8px; background: #fff; transition: 0.2s; display: flex; justify-content: space-between; align-items: center; }
    .my-task-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .my-task-card.completed { opacity: 0.6; background: #f8fff8; }
    .my-task-card.overdue-task { border-left: 3px solid #e74c3c; }
    .priority-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    .priority-low { background: #95a5a6; }
    .priority-medium { background: #3498db; }
    .priority-high { background: #f39c12; }
    .priority-urgent { background: #e74c3c; }
    .order-link { font-size: 0.85em; color: #3c8dbc; }
</style>
@endsection

@section('content')

<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-user-check"></i> Mis Tareas
    </h1>
    <div class="tw-mt-2">
        <a href="{{ route('order-pedidos.index') }}" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-ghost">
            <i class="fas fa-arrow-left"></i> Volver a Órdenes
        </a>
    </div>
</section>

<section class="content no-print">
    <!-- Stats -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Vencidas</span>
                    <span class="info-box-number">{{ $overdue->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Para Hoy</span>
                    <span class="info-box-number">{{ $today->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Próximas</span>
                    <span class="info-box-number">{{ $upcoming->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fas fa-tasks"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Pendientes</span>
                    <span class="info-box-number">{{ $overdue->count() + $today->count() + $upcoming->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Vencidas -->
    @if($overdue->count() > 0)
    <div class="task-section">
        <div class="task-section-title overdue">
            <i class="fas fa-exclamation-triangle"></i> Vencidas ({{ $overdue->count() }})
        </div>
        @foreach($overdue as $task)
            @include('order_pedidos.partials.my_task_row', ['task' => $task, 'isOverdue' => true])
        @endforeach
    </div>
    @endif

    <!-- Hoy -->
    @if($today->count() > 0)
    <div class="task-section">
        <div class="task-section-title today">
            <i class="fas fa-clock"></i> Para Hoy ({{ $today->count() }})
        </div>
        @foreach($today as $task)
            @include('order_pedidos.partials.my_task_row', ['task' => $task, 'isOverdue' => false])
        @endforeach
    </div>
    @endif

    <!-- Próximas -->
    @if($upcoming->count() > 0)
    <div class="task-section">
        <div class="task-section-title upcoming">
            <i class="fas fa-calendar-alt"></i> Próximas ({{ $upcoming->count() }})
        </div>
        @foreach($upcoming as $task)
            @include('order_pedidos.partials.my_task_row', ['task' => $task, 'isOverdue' => false])
        @endforeach
    </div>
    @endif

    @if($overdue->count() == 0 && $today->count() == 0 && $upcoming->count() == 0)
    <div class="box box-default">
        <div class="box-body text-center tw-py-12">
            <i class="fas fa-check-circle fa-4x tw-text-green-400 tw-mb-4"></i>
            <h3 class="tw-text-gray-500">¡No tienes tareas pendientes!</h3>
            <p class="text-muted">Todas tus tareas están al día. Buen trabajo.</p>
        </div>
    </div>
    @endif
</section>

@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Quick status change
    $(document).on('click', '.quick-complete', function() {
        var taskId = $(this).data('task-id');
        var $card = $(this).closest('.my-task-card');
        $.ajax({
            url: '/order-tasks/' + taskId + '/status',
            type: 'POST',
            data: { status: 'completed', _token: csrfToken },
            success: function(res) {
                if (res.success) {
                    $card.addClass('completed');
                    toastr.success('Tarea completada');
                    setTimeout(function() { $card.slideUp(); }, 1000);
                }
            }
        });
    });

    $(document).on('click', '.quick-progress', function() {
        var taskId = $(this).data('task-id');
        $.ajax({
            url: '/order-tasks/' + taskId + '/status',
            type: 'POST',
            data: { status: 'in_progress', _token: csrfToken },
            success: function(res) {
                if (res.success) {
                    toastr.success('Tarea en progreso');
                    location.reload();
                }
            }
        });
    });
});
</script>
@endsection
