<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderSession extends Model
{
  use HasFactory;

  protected $fillable = [
    'work_order_id',
    'technician_id',
    'started_at',
    'ended_at',
    'notes',
  ];

  protected $appends = [
    'duration',
    'duration_minutes',
  ];

  protected function casts(): array
  {
    return [
      'started_at' => 'datetime',
      'ended_at' => 'datetime',
    ];
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

  // === Accessors ===

  public function getDurationMinutesAttribute(): int
  {
    if (!$this->started_at) {
      return 0;
    }

    $endTime = $this->ended_at ?? now();

    return (int) $this->started_at->diffInMinutes($endTime);
  }

  public function getDurationAttribute(): string
  {
    $totalMinutes = $this->duration_minutes;
    $hours = intdiv($totalMinutes, 60);
    $minutes = $totalMinutes % 60;

    if ($hours > 0) {
      return "{$hours} jam {$minutes} menit";
    }

    return "{$minutes} menit";
  }
}
