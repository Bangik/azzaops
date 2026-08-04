<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function index()
    {
        $versions = AppVersion::orderByDesc('version_code')->paginate(15);
        return view('admin.app-versions.index', compact('versions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apk_url' => 'required|url',
            'version_code' => 'required|integer|min:1|unique:app_versions,version_code',
            'version_name' => 'required|string|max:255',
            'release_notes' => 'nullable|string',
        ], [
            'apk_url.required' => 'Link download APK wajib diisi.',
            'apk_url.url' => 'Format link download APK tidak valid.',
            'version_code.required' => 'Version Code wajib diisi.',
            'version_code.integer' => 'Version Code harus berupa angka.',
            'version_code.unique' => 'Version Code sudah terdaftar.',
            'version_name.required' => 'Version Name wajib diisi.',
        ]);

        AppVersion::create([
            'version_code' => $validated['version_code'],
            'version_name' => $validated['version_name'],
            'release_notes' => $validated['release_notes'],
            'apk_url' => $validated['apk_url'],
        ]);

        return redirect()->route('admin.app-versions.index')
            ->with('success', 'Versi aplikasi baru berhasil disimpan.');
    }

    public function destroy(AppVersion $appVersion)
    {
        $appVersion->delete();

        return redirect()->route('admin.app-versions.index')
            ->with('success', 'Versi aplikasi berhasil dihapus.');
    }
}
