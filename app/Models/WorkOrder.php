<?php

namespace App\Models;

use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'wo_number',
        'type',
        'customer_id',
        'service_category_id',
        'title',
        'description',
        'location',
        'gmaps_link',
        'scheduled_date',
        'scheduled_time',
        'job_order',
        'started_at',
        'completed_at',
        'status',
        'priority',
        'estimated_cost',
        'total_cost',
        'notes',
        'parent_wo_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => WorkOrderType::class,
            'status' => WorkOrderStatus::class,
            'priority' => WorkOrderPriority::class,
            'scheduled_date' => 'date',
            'scheduled_time' => 'string',
            'job_order' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    // === Relationships ===

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(WorkOrderAssignment::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(WorkOrderReport::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function rab(): HasOne
    {
        return $this->hasOne(Rab::class);
    }

    public function parentWorkOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'parent_wo_id');
    }

    public function childWorkOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'parent_wo_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function takeovers(): HasMany
    {
        return $this->hasMany(WorkOrderTakeover::class);
    }

    // === Scopes ===

    public function scopeStatus(Builder $query, WorkOrderStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByType(Builder $query, WorkOrderType $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('scheduled_date', $date);
    }

    // === Accessors ===

    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn ($item) => $item->quantity * $item->unit_price);
    }
}
