<div class="my-task-card {{ $isOverdue ? 'overdue-task' : '' }}">
    <div class="tw-flex tw-items-center tw-gap-3">
        <span class="priority-dot priority-{{ $task->priority }}" title="{{ $task->priority }}"></span>
        <div>
            <strong>{{ $task->title }}</strong>
            @if($task->is_milestone)
                <span class="badge bg-purple" style="font-size:0.7em;"><i class="fas fa-flag"></i> Hito</span>
            @endif
            <br>
            <a href="{{ route('order-pedidos.show', $task->order_pedido_id) }}" class="order-link">
                <i class="fas fa-clipboard-list"></i> {{ $task->orderPedido->order_number ?? 'N/A' }}
                @if($task->orderPedido && $task->orderPedido->contact)
                    - {{ $task->orderPedido->contact->name }}
                @endif
            </a>
            @if($task->due_date)
                <span class="tw-ml-2 {{ $isOverdue ? 'tw-text-red-600 tw-font-bold' : 'text-muted' }}" style="font-size:0.85em;">
                    <i class="fas fa-calendar"></i> {{ $task->due_date->format('d/m/Y') }}
                    @if($isOverdue)
                        (hace {{ $task->due_date->diffForHumans() }})
                    @endif
                </span>
            @endif
        </div>
    </div>
    <div class="tw-flex tw-gap-2">
        @if($task->status != 'in_progress')
            <button class="btn btn-xs btn-info quick-progress" data-task-id="{{ $task->id }}" title="Marcar en progreso">
                <i class="fas fa-play"></i>
            </button>
        @endif
        <button class="btn btn-xs btn-success quick-complete" data-task-id="{{ $task->id }}" title="Completar">
            <i class="fas fa-check"></i>
        </button>
        <a href="{{ route('order-pedidos.show', $task->order_pedido_id) }}#tab_tasks" class="btn btn-xs btn-default" title="Ver en orden">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
</div>
