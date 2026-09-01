<?php

namespace App\Models;

use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrder extends Model
{
    use HasFactory;

    protected $appends = [
        'duration',
        'duration_minutes',
    ];

    protected $fillable = [
        'wo_number',
        'work_order_type_id',
        'customer_id',
        'vendor_id',
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
        'estimated_cost',
        'total_cost',
        'notes',
        'parent_wo_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => WorkOrderStatus::class,
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

    public function type(): BelongsTo
    {
        return $this->belongsTo(WorkOrderType::class, 'work_order_type_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
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

    public function scopeByType(Builder $query, int $typeId): Builder
    {
        return $query->where('work_order_type_id', $typeId);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('scheduled_date', $date);
    }

    // === Accessors ===

    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn($item) => $item->quantity * $item->unit_price);
    }

    public function getVendorTotalAttribute(): float
    {
        return $this->items->sum(fn($item) => $item->quantity * ($item->vendor_unit_price ?? 0));
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at) {
            return null;
        }

        $endTime = $this->completed_at ?? ($this->status === WorkOrderStatus::Completed || $this->status === WorkOrderStatus::Reported ? $this->updated_at : now());
        
        $totalMinutes = (int) $this->started_at->diffInMinutes($endTime);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        if ($hours > 0) {
            return "{$hours} jam {$minutes} menit";
        }

        return "{$minutes} menit";
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->started_at) {
            return null;
        }

        $endTime = $this->completed_at ?? ($this->status === WorkOrderStatus::Completed || $this->status === WorkOrderStatus::Reported ? $this->updated_at : now());

        return (int) $this->started_at->diffInMinutes($endTime);
    }
}
