<?php

namespace App\Models;

use App\Enums\PhotoType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderReportPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'photo_path',
        'photo_type',
        'caption',
        'file_size',
    ];

    protected $appends = [
        'photo_url',
    ];

    protected function casts(): array
    {
        return [
            'photo_type' => PhotoType::class,
        ];
    }

    // === Relationships ===

    public function report(): BelongsTo
    {
        return $this->belongsTo(WorkOrderReport::class, 'report_id');
    }

    // === Accessors ===

    public function getPhotoUrlAttribute(): string
    {
        return asset($this->photo_path);
    }
}
