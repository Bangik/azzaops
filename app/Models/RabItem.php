<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RabItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'rab_id',
        'category',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    // === Relationships ===

    public function rab(): BelongsTo
    {
        return $this->belongsTo(Rab::class);
    }
}
