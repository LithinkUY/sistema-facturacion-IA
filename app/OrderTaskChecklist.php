<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderTaskChecklist extends Model
{
    protected $table = 'order_task_checklists';

    protected $guarded = ['id'];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(OrderTask::class, 'order_task_id');
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function toggleComplete($userId = null)
    {
        $this->is_completed = !$this->is_completed;
        if ($this->is_completed) {
            $this->completed_by = $userId ?? auth()->id();
            $this->completed_at = now();
        } else {
            $this->completed_by = null;
            $this->completed_at = null;
        }
        $this->save();

        // Actualizar progreso de la tarea padre
        $this->task->updateProgress();
    }
}
