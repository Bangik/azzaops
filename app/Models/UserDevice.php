<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserDevice extends Model
{
    use HasUuids;

    protected $fillable = [
        'device_id',
        'platform',
        'app_version',
        'build_number',
        'os_version',
        'device_brand',
        'device_model',
        'screen_resolution',
        'network_type',
        'session_id',
        'user_id',
    ];

    protected $casts = [
        'build_number' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
