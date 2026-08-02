<?php

namespace App\Models;

use App\Enums\CustomerType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'company_name',
        'pic_name',
        'phone',
        'phone_alt',
        'email',
        'address',
        'gmaps_link',
        'city',
        'market',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => CustomerType::class,
        ];
    }

    // === Relationships ===

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function rabs(): HasMany
    {
        return $this->hasMany(Rab::class);
    }

    // === Accessors ===

    public function getDisplayNameAttribute(): string
    {
        return $this->type === CustomerType::Business
            ? ($this->company_name ?? $this->name)
            : $this->name;
    }
}
