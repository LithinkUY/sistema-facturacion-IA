@php
    $isOverdue = $task->is_overdue;
    $checklist = $task->checklists;
    $checkProgress = $task->checklistProgress;
@endphp

<div class="task-card {{ $task->status == 'completed' ? 'completed' : '' }} {{ $isOverdue ? 'overdue' : '' }}" id="task_{{ $task->id }}">
    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
        <div style="display:flex; align-items:center; gap:8px; flex:1;">
            @if($task->status == 'completed')
                <i class="fas fa-check-circle" style="color:#43a047; font-size:1.2em;"></i>
            @elseif($task->status == 'in_progress')
                <i class="fas fa-spinner fa-spin" style="color:#1e88e5; font-size:1.2em;"></i>
            @elseif($task->status == 'on_hold')
                <i class="fas fa-pause-circle" style="color:#ffa726; font-size:1.2em;"></i>
            @else
                <i class="far fa-circle" style="color:#bdbdbd; font-size:1.2em;"></i>
            @endif
            <span class="task-title" style="{{ $task->status == 'completed' ? 'text-decoration:line-through; color:#999;' : '' }}">
                {{ $task->title }}
            </span>
            @if($task->is_milestone)
                <span class="badge" style="background:#7b1fa2; color:#fff; font-size:0.7em;"><i class="fas fa-flag"></i> Hito</span>
            @endif
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
            <span class="priority-pill {{ $task->priority }}">
                @if($task->priority == 'urgent') 🔴 @elseif($task->priority == 'high') 🟠 @elseif($task->priority == 'medium') 🟡 @else 🟢 @endif
                {{ $task->priority_label['text'] }}
            </span>
            <div class="btn-group">
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" style="border-radius:15px; padding:2px 10px;">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    @if($task->status != 'completed')
                        <li><a href="#" class="task-status-btn" data-task-id="{{ $task->id }}" data-status="in_progress"><i class="fas fa-play text-info"></i> En Progreso</a></li>
                        <li><a href="#" class="task-status-btn" data-task-id="{{ $task->id }}" data-status="completed"><i class="fas fa-check text-success"></i> Completar</a></li>
                        <li><a href="#" class="task-status-btn" data-task-id="{{ $task->id }}" data-status="on_hold"><i class="fas fa-pause text-warning"></i> En Espera</a></li>
                    @else
                        <li><a href="#" class="task-status-btn" data-task-id="{{ $task->id }}" data-status="pending"><i class="fas fa-undo"></i> Reabrir</a></li>
                    @endif
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="edit-task-btn" data-task='{!! json_encode([
                            "id" => $task->id,
                            "title" => $task->title,
                            "description" => $task->description,
                            "status" => $task->status,
                            "priority" => $task->priority,
                            "assigned_to" => $task->assigned_to,
                            "start_date" => $task->start_date ? $task->start_date->format("Y-m-d") : "",
                            "due_date" => $task->due_date ? $task->due_date->format("Y-m-d") : ""
                        ]) !!}'><i class="fas fa-edit"></i> Editar</a>
                    </li>
                    <li><a href="#" class="delete-task-btn" data-task-id="{{ $task->id }}"><i class="fas fa-trash text-danger"></i> Eliminar</a></li>
                </ul>
            </div>
        </div>
    </div>

    @if($task->description)
        <p style="color:#666; font-size:0.9em; margin-bottom:8px;">{{ $task->description }}</p>
    @endif

    {{-- Metadatos --}}
    <div style="display:flex; flex-wrap:wrap; gap:12px; font-size:0.82em; color:#999; margin-bottom:8px;">
        @if($task->assignedTo)
            <span><i class="fas fa-user"></i> {{ $task->assignedTo->first_name }} {{ $task->assignedTo->last_name }}</span>
        @endif
        @if($task->due_date)
            <span style="{{ $isOverdue ? 'color:#c62828; font-weight:700;' : '' }}">
                <i class="fas fa-calendar"></i> {{ $task->due_date->format('d/m/Y') }}
                @if($isOverdue) (Vencida) @endif
            </span>
        @endif
        @if($task->start_date)
            <span><i class="fas fa-play"></i> {{ $task->start_date->format('d/m/Y') }}</span>
        @endif
    </div>

    {{-- Checklist --}}
    @if($checklist->count() > 0)
        <div style="margin-bottom:8px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <small style="font-weight:700;"><i class="fas fa-check-square"></i> Checklist</small>
                <small style="color:#999;">{{ $checkProgress['completed'] }}/{{ $checkProgress['total'] }}</small>
            </div>
            <div class="progress-bar-custom" style="height:6px; margin-bottom:6px;">
                <div class="fill {{ $checkProgress['percent'] >= 100 ? 'green' : 'blue' }}" style="width:{{ $checkProgress['percent'] }}%;"></div>
            </div>
            <div data-task-checklist="{{ $task->id }}">
                @foreach($checklist as $item)
                    <div class="checklist-item {{ $item->is_completed ? 'completed' : '' }}" data-checklist-id="{{ $item->id }}">
                        <input type="checkbox" class="checklist-toggle" data-id="{{ $item->id }}" {{ $item->is_completed ? 'checked' : '' }}>
                        <label>{{ $item->description }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>