<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderTakeover extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'requested_by',
        'original_technician_id',
        'status', // pending, approved, rejected
        'approved_by',
        'rejected_by',
        'notes',
    ];

    // === Relationships ===

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function originalTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_technician_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
