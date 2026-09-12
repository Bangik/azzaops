<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AttendanceService;
use App\Exports\AttendancesExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService
    ) {}

    /**
     * Log presensi seluruh staff (super_admin only)
     */
    public function index(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $userId = $request->get('user_id');
        $status = $request->get('status');

        $attendances = $this->attendanceService->getAllLog($from, $to, $userId, $status);
        $staffList = User::active()->orderBy('name')->get();
        $schedule = $this->attendanceService->getWorkSchedule();

        return view('admin.attendances.index', compact('attendances', 'staffList', 'from', 'to', 'userId', 'status', 'schedule'));
    }

    /**
     * Export presensi ke Excel
     */
    public function export(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $userId = $request->get('user_id');
        $status = $request->get('status');
        $format = $request->get('format', 'xlsx');

        $fileName = 'laporan-presensi-' . $from . '-to-' . $to . '.' . $format;
        $writerType = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(new AttendancesExport($from, $to, $userId, $status), $fileName, $writerType);
    }

    /**
     * Halaman presensi untuk admin (check in / check out via web)
     */
    public function myAttendance()
    {
        $user = auth()->user();
        $today = $this->attendanceService->getTodayAttendance($user->id);
        $schedule = $this->attendanceService->getWorkSchedule();
        $logs = $this->attendanceService->getUserLog($user->id, null, null, 10);

        return view('admin.attendances.my-attendance', compact('today', 'schedule', 'logs'));
    }

    /**
     * Admin check-in via web
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $this->attendanceService->checkIn(
            auth()->id(),
            $validated['notes'] ?? null,
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        return redirect()->route('admin.attendances.my')
            ->with('success', 'Presensi masuk berhasil dicatat.');
    }

    /**
     * Admin check-out via web
     */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $this->attendanceService->checkOut(
            auth()->id(),
            $validated['notes'] ?? null,
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        return redirect()->route('admin.attendances.my')
            ->with('success', 'Presensi pulang berhasil dicatat.');
    }

    /**
     * Update jadwal kerja (super_admin only)
     */
    public function updateSchedule(Request $request)
    {
        $request->validate([
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time' => 'required|date_format:H:i|after:work_start_time',
            'attendance_radius_meters' => 'required|integer|min:1|max:100000',
            'attendance_latitude' => 'required|numeric|between:-90,90',
            'attendance_longitude' => 'required|numeric|between:-180,180',
        ]);

        \App\Models\Setting::updateOrCreate(
            ['key' => 'work_start_time'],
            ['value' => $request->work_start_time, 'group' => 'attendance', 'description' => 'Jam masuk kerja']
        );

        \App\Models\Setting::updateOrCreate(
            ['key' => 'work_end_time'],
            ['value' => $request->work_end_time, 'group' => 'attendance', 'description' => 'Jam pulang kerja']
        );

        \App\Models\Setting::updateOrCreate(
            ['key' => 'attendance_radius_meters'],
            ['value' => $request->attendance_radius_meters, 'group' => 'attendance', 'description' => 'Radius presensi dalam meter']
        );

        \App\Models\Setting::updateOrCreate(
            ['key' => 'attendance_latitude'],
            ['value' => $request->attendance_latitude, 'group' => 'attendance', 'description' => 'Latitude lokasi presensi']
        );

        \App\Models\Setting::updateOrCreate(
            ['key' => 'attendance_longitude'],
            ['value' => $request->attendance_longitude, 'group' => 'attendance', 'description' => 'Longitude lokasi presensi']
        );

        return redirect()->route('admin.attendances.index')
            ->with('success', 'Jadwal kerja berhasil diperbarui.');
    }
}
