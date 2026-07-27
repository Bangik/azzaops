<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($notifications, 'Daftar notifikasi berhasil diambil');
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return $this->errorResponse('Akses ditolak', 403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $this->successResponse($notification, 'Notifikasi ditandai telah dibaca');
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $updated = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->successResponse(['updated_count' => $updated], 'Semua notifikasi ditandai telah dibaca');
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return $this->successResponse(['count' => $count], 'Jumlah notifikasi belum dibaca berhasil diambil');
    }
}
