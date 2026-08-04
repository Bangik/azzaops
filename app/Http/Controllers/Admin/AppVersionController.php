<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'apk_file' => 'required|file|mimetypes:application/vnd.android.package-archive,application/octet-stream|mimes:apk',
            'version_code' => 'required|integer|min:1|unique:app_versions,version_code',
            'version_name' => 'required|string|max:255',
            'release_notes' => 'nullable|string',
        ], [
            'apk_file.required' => 'File APK wajib diupload.',
            'apk_file.file' => 'Upload harus berupa file.',
            'apk_file.mimetypes' => 'Format file harus berupa .apk.',
            'apk_file.mimes' => 'Format file harus berupa .apk.',
            'version_code.required' => 'Version Code wajib diisi.',
            'version_code.integer' => 'Version Code harus berupa angka.',
            'version_code.unique' => 'Version Code sudah terdaftar.',
            'version_name.required' => 'Version Name wajib diisi.',
        ]);

        $path = $request->file('apk_file')->store('apks', 'public');

        AppVersion::create([
            'version_code' => $validated['version_code'],
            'version_name' => $validated['version_name'],
            'release_notes' => $validated['release_notes'],
            'apk_file_path' => $path,
        ]);

        return redirect()->route('admin.app-versions.index')
            ->with('success', 'Versi aplikasi baru berhasil diupload.');
    }

    public function destroy(AppVersion $appVersion)
    {
        Storage::disk('public')->delete($appVersion->apk_file_path);
        $appVersion->delete();

        return redirect()->route('admin.app-versions.index')
            ->with('success', 'Versi aplikasi berhasil dihapus.');
    }
}
