<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrderReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'technician_id',
        'findings',
        'work_done',
        'recommendations',
        'materials_used',
        'is_draft',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_draft' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    // === Scopes ===

    public function scopeFinal($query)
    {
        return $query->where('is_draft', false);
    }

    public function scopeDraft($query)
    {
        return $query->where('is_draft', true);
    }

    // === Relationships ===

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(WorkOrderReportPhoto::class, 'report_id');
    }
}
