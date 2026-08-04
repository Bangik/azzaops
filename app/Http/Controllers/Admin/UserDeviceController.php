<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class UserDeviceController extends Controller
{
    public function index()
    {
        $devices = UserDevice::with('user')->latest()->paginate(15);
        return view('admin.devices.index', compact('devices'));
    }
}
