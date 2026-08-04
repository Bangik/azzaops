<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppVersion extends Model
{
    protected $fillable = [
        'version_code',
        'version_name',
        'release_notes',
        'apk_file_path',
    ];

    protected $casts = [
        'version_code' => 'integer',
    ];

    protected $appends = [
        'download_url',
    ];

    public function getDownloadUrlAttribute(): string
    {
        return asset(Storage::url($this->apk_file_path));
    }
}
