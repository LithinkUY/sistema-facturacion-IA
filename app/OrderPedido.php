<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderPedido extends Model
{
    use SoftDeletes;

    protected $table = 'order_pedidos';

    protected $guarded = ['id'];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // ===== Relaciones =====

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function location()
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lines()
    {
        return $this->hasMany(OrderPedidoLine::class, 'order_pedido_id')->orderBy('sort_order');
    }

    public function tasks()
    {
        return $this->hasMany(OrderTask::class, 'order_pedido_id')->orderBy('sort_order');
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
            'draft' => ['text' => 'Borrador', 'class' => 'bg-secondary'],
            'pending' => ['text' => 'Pendiente', 'class' => 'bg-warning'],
            'approved' => ['text' => 'Aprobada', 'class' => 'bg-info'],
            'in_progress' => ['text' => 'En Proceso', 'class' => 'bg-primary'],
            'partial' => ['text' => 'Parcial', 'class' => 'bg-orange'],
            'completed' => ['text' => 'Completada', 'class' => 'bg-success'],
            'cancelled' => ['text' => 'Cancelada', 'class' => 'bg-danger'],
        ];
        return $labels[$this->status] ?? ['text' => $this->status, 'class' => 'bg-secondary'];
    }

    public function getPriorityLabelAttribute()
    {
        $labels = [
            'low' => ['text' => 'Baja', 'class' => 'bg-secondary'],
            'medium' => ['text' => 'Media', 'class' => 'bg-info'],
            'high' => ['text' => 'Alta', 'class' => 'bg-warning'],
            'urgent' => ['text' => 'Urgente', 'class' => 'bg-danger'],
        ];
        return $labels[$this->priority] ?? ['text' => $this->priority, 'class' => 'bg-secondary'];
    }

    public function getProgressPercentAttribute()
    {
        $lines = $this->lines;
        if ($lines->isEmpty()) return 0;
        
        $totalQty = $lines->sum('quantity');
        $receivedQty = $lines->sum('quantity_received');
        
        return $totalQty > 0 ? round(($receivedQty / $totalQty) * 100) : 0;
    }

    public function getTaskProgressAttribute()
    {
        $tasks = $this->tasks;
        $total = $tasks->count();
        $completed = $tasks->where('status', 'completed')->count();
        $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
        
        return [
            'percent' => $percent,
            'completed' => $completed,
            'total' => $total,
        ];
    }

    // ===== Scopes =====

    public function scopeForBusiness($query, $business_id)
    {
        return $query->where('business_id', $business_id);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_delivery_date', '<', now())
                     ->whereNotIn('status', ['completed', 'cancelled']);
    }

    // ===== Helpers =====

    public static function generateOrderNumber($business_id)
    {
        $prefix = 'OP-';
        $year = date('Y');

        // Buscar el número más alto existente (incluye soft-deleted)
        $maxNumber = self::withTrashed()
            ->where('business_id', $business_id)
            ->where('order_number', 'like', $prefix . $year . '-%')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(order_number, '-', -1) AS UNSIGNED)) as max_num")
            ->value('max_num');

        $nextNum = ($maxNumber ?? 0) + 1;
        $candidate = $prefix . $year . '-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        // Protección extra: si por alguna razón existe, incrementar
        while (self::withTrashed()->where('order_number', $candidate)->exists()) {
            $nextNum++;
            $candidate = $prefix . $year . '-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    public function recalculateTotals()
    {
        $subtotal = $this->lines()->sum('line_total');
        $taxTotal = $this->lines()->sum('tax_amount');
        
        $discount = 0;
        if ($this->discount_type === 'percentage') {
            $discount = $subtotal * ($this->discount_amount / 100);
        } else {
            $discount = $this->discount_amount ?? 0;
        }

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxTotal;
        $this->total = $subtotal + $taxTotal - $discount;
        $this->save();
    }

    public function addSystemComment($comment, $userId = null)
    {
        return $this->comments()->create([
            'user_id' => $userId ?? auth()->id(),
            'comment' => $comment,
            'type' => 'system',
        ]);
    }
}
