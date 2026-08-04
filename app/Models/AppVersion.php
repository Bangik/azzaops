<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'version_code',
        'version_name',
        'release_notes',
        'apk_url',
    ];

    protected $casts = [
        'version_code' => 'integer',
    ];

    protected $appends = [
        'download_url',
    ];

    public function getDownloadUrlAttribute(): string
    {
        return $this->apk_url;
    }
}
