<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
        'fcm_token',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'fcm_token',
    ];

    protected $appends = [
        'avatar_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    // === Relationships ===

    public function assignments(): HasMany
    {
        return $this->hasMany(WorkOrderAssignment::class, 'technician_id');
    }

    public function assignedBy(): HasMany
    {
        return $this->hasMany(WorkOrderAssignment::class, 'assigned_by');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(WorkOrderReport::class, 'technician_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function createdWorkOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'created_by');
    }

    // === Scopes ===

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRole(Builder $query, UserRole $role): Builder
    {
        return $query->where('role', $role);
    }

    public function scopeTechnicians(Builder $query): Builder
    {
        return $query->whereIn('role', [UserRole::Teknisi, UserRole::KepalaTeknisi])->where('is_active', true);
    }

    // === Accessors ===

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset($this->avatar) : null;
    }
}
