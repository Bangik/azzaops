<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    use ApiResponse;

    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'platform' => 'required|in:android,ios',
            'app_version' => 'required|string',
            'build_number' => 'required|integer',
            'os_version' => 'nullable|string',
            'device_brand' => 'nullable|string',
            'device_model' => 'nullable|string',
            'screen_resolution' => 'nullable|string',
            'network_type' => 'nullable|string',
            'session_id' => 'nullable|string',
        ]);

        $device = UserDevice::updateOrCreate(
            ['device_id' => $validated['device_id']],
            array_merge($validated, ['user_id' => $request->user()->id])
        );

        return $this->successResponse($device, 'Device information updated successfully');
    }
}
