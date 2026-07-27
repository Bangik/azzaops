<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use ApiResponse;

    public function show(Request $request)
    {
        return $this->successResponse($request->user(), 'Profil berhasil diambil');
    }

    public function update(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Nama wajib diisi',
        ]);

        $user->update($data);

        return $this->successResponse($user, 'Profil berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi',
            'password.required' => 'Password baru wajib diisi',
            'password.min' => 'Password baru minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Password saat ini salah', 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->successResponse(null, 'Password berhasil diperbarui');
    }

    public function updateFcmToken(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return $this->successResponse(null, 'FCM token berhasil diperbarui');
    }
}
