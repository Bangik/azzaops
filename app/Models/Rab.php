<?php

namespace App\Models;

use App\Enums\RabStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rab extends Model
{
    use HasFactory;

    protected $fillable = [
        'rab_number',
        'work_order_id',
        'customer_id',
        'title',
        'description',
        'subtotal',
        'discount',
        'tax_percentage',
        'tax_amount',
        'total',
        'status',
        'valid_until',
        'notes',
        'created_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => RabStatus::class,
            'valid_until' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    // === Relationships ===

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RabItem::class);
    }
}
