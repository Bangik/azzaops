<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => AttendanceStatus::class,
        ];
    }

    // === Relationships ===

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // === Scopes ===

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeStatus(Builder $query, AttendanceStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    // === Accessors ===

    public function getWorkDurationAttribute(): ?string
    {
        if (!$this->check_in || !$this->check_out) {
            return null;
        }

        $in = \Carbon\Carbon::parse($this->check_in);
        $out = \Carbon\Carbon::parse($this->check_out);
        $diff = $in->diff($out);

        return $diff->format('%H jam %I menit');
    }
}
