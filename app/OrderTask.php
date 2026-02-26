<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderTask extends Model
{
    use SoftDeletes;

    protected $table = 'order_tasks';

    protected $guarded = ['id'];

    protected $casts = [
        'due_date' => 'date',
        'start_date' => 'date',
        'completed_at' => 'datetime',
        'is_milestone' => 'boolean',
    ];

    // ===== Relaciones =====

    public function orderPedido()
    {
        return $this->belongsTo(OrderPedido::class, 'order_pedido_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function checklists()
    {
        return $this->hasMany(OrderTaskChecklist::class, 'order_task_id')->orderBy('sort_order');
    }

    public function comments()
    {
        return $this->morphMany(OrderComment::class, 'commentable')->orderBy('created_at', 'desc');
    }

    public function attachments()
    {
        return $this->morphMany(OrderAttachment::class, 'attachable');
    }

    // ===== Accessors =====

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => ['text' => 'Pendiente', 'class' => 'bg-warning', 'icon' => 'fas fa-clock'],
            'in_progress' => ['text' => 'En Progreso', 'class' => 'bg-primary', 'icon' => 'fas fa-spinner'],
            'completed' => ['text' => 'Completada', 'class' => 'bg-success', 'icon' => 'fas fa-check'],
            'cancelled' => ['text' => 'Cancelada', 'class' => 'bg-danger', 'icon' => 'fas fa-times'],
            'on_hold' => ['text' => 'En Espera', 'class' => 'bg-secondary', 'icon' => 'fas fa-pause'],
        ];
        return $labels[$this->status] ?? ['text' => $this->status, 'class' => 'bg-secondary', 'icon' => 'fas fa-question'];
    }

    public function getPriorityLabelAttribute()
    {
        $labels = [
            'low' => ['text' => 'Baja', 'class' => 'bg-secondary', 'icon' => 'fas fa-arrow-down'],
            'medium' => ['text' => 'Media', 'class' => 'bg-info', 'icon' => 'fas fa-minus'],
            'high' => ['text' => 'Alta', 'class' => 'bg-warning', 'icon' => 'fas fa-arrow-up'],
            'urgent' => ['text' => 'Urgente', 'class' => 'bg-danger', 'icon' => 'fas fa-exclamation'],
        ];
        return $labels[$this->priority] ?? ['text' => $this->priority, 'class' => 'bg-secondary', 'icon' => 'fas fa-minus'];
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date 
            && $this->due_date->isPast() 
            && !in_array($this->status, ['completed', 'cancelled']);
    }

    public function getChecklistProgressAttribute()
    {
        $checklists = $this->checklists;
        if ($checklists->isEmpty()) return null;
        
        $completed = $checklists->where('is_completed', true)->count();
        return [
            'completed' => $completed,
            'total' => $checklists->count(),
            'percent' => round(($completed / $checklists->count()) * 100),
        ];
    }

    // ===== Scopes =====

    public function scopeForBusiness($query, $business_id)
    {
        return $query->where('business_id', $business_id);
    }

    public function scopeAssignedTo($query, $user_id)
    {
        return $query->where('assigned_to', $user_id);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
                     ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereBetween('due_date', [now()->toDateString(), now()->addDays($days)->toDateString()])
                     ->whereNotIn('status', ['completed', 'cancelled']);
    }

    // ===== Métodos =====

    public function markAsCompleted($userId = null)
    {
        $this->status = 'completed';
        $this->progress = 100;
        $this->completed_at = now();
        $this->completed_by = $userId ?? auth()->id();
        $this->save();

        // Marcar todos los checklists como completados
        $this->checklists()->where('is_completed', false)->update([
            'is_completed' => true,
            'completed_by' => $this->completed_by,
            'completed_at' => now(),
        ]);

        // Registrar actividad en la orden
        $this->orderPedido->addSystemComment(
            "Tarea \"{$this->title}\" marcada como completada por " . (auth()->user()->first_name ?? 'Sistema'),
            $this->completed_by
        );
    }

    public function updateProgress()
    {
        $checklist = $this->checklistProgress;
        if ($checklist) {
            $this->progress = $checklist['percent'];
            $this->save();
        }
    }
}
