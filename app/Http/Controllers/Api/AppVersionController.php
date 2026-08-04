<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use App\Traits\ApiResponse;

class AppVersionController extends Controller
{
    use ApiResponse;

    public function latest()
    {
        $latest = AppVersion::orderByDesc('version_code')->first();

        if (!$latest) {
            return $this->errorResponse('No app version found', 404);
        }

        return response()->json([
            'version_code' => $latest->version_code,
            'version_name' => $latest->version_name,
            'release_notes' => $latest->release_notes,
            'download_url' => $latest->download_url,
        ]);
    }
}
