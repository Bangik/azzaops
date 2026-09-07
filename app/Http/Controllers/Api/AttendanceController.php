<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AttendanceService $attendanceService
    ) {}

    /**
     * Status presensi hari ini + jadwal kerja
     */
    public function today()
    {
        $user = auth()->user();
        $attendance = $this->attendanceService->getTodayAttendance($user->id);
        $schedule = $this->attendanceService->getWorkSchedule();

        return $this->successResponse([
            'attendance' => $attendance ? [
                'id' => $attendance->id,
                'date' => $attendance->date->toDateString(),
                'check_in' => $attendance->check_in,
                'check_out' => $attendance->check_out,
                'status' => $attendance->status->value,
                'status_label' => $attendance->status->label(),
                'notes' => $attendance->notes,
                'work_duration' => $attendance->work_duration,
            ] : null,
            'schedule' => $schedule,
            'has_checked_in' => $attendance && $attendance->check_in !== null,
            'has_checked_out' => $attendance && $attendance->check_out !== null,
        ]);
    }

    /**
     * Check in
     */
    public function checkIn(Request $request)
    {
        $user = auth()->user();
        $existing = $this->attendanceService->getTodayAttendance($user->id);

        if ($existing && $existing->check_in) {
            return $this->errorResponse('Anda sudah melakukan presensi masuk hari ini.', 422);
        }

        $attendance = $this->attendanceService->checkIn($user->id, $request->input('notes'));

        return $this->successResponse([
            'id' => $attendance->id,
            'date' => $attendance->date->toDateString(),
            'check_in' => $attendance->check_in,
            'status' => $attendance->status->value,
            'status_label' => $attendance->status->label(),
            'notes' => $attendance->notes,
        ], 'Presensi masuk berhasil dicatat.');
    }

    /**
     * Check out
     */
    public function checkOut(Request $request)
    {
        $user = auth()->user();
        $existing = $this->attendanceService->getTodayAttendance($user->id);

        if (!$existing || !$existing->check_in) {
            return $this->errorResponse('Anda belum melakukan presensi masuk hari ini.', 422);
        }

        if ($existing->check_out) {
            return $this->errorResponse('Anda sudah melakukan presensi pulang hari ini.', 422);
        }

        $attendance = $this->attendanceService->checkOut($user->id, $request->input('notes'));

        return $this->successResponse([
            'id' => $attendance->id,
            'date' => $attendance->date->toDateString(),
            'check_in' => $attendance->check_in,
            'check_out' => $attendance->check_out,
            'status' => $attendance->status->value,
            'status_label' => $attendance->status->label(),
            'notes' => $attendance->notes,
            'work_duration' => $attendance->work_duration,
        ], 'Presensi pulang berhasil dicatat.');
    }

    /**
     * Log presensi user yang login
     */
    public function myLog(Request $request)
    {
        $user = auth()->user();
        $from = $request->get('from');
        $to = $request->get('to');
        $perPage = $request->get('per_page', 15);

        $logs = $this->attendanceService->getUserLog($user->id, $from, $to, $perPage);

        $data = $logs->map(function ($a) {
            return [
                'id' => $a->id,
                'date' => $a->date->toDateString(),
                'check_in' => $a->check_in,
                'check_out' => $a->check_out,
                'status' => $a->status->value,
                'status_label' => $a->status->label(),
                'notes' => $a->notes,
                'work_duration' => $a->work_duration,
            ];
        });

        return $this->paginatedResponse($logs, 'Berhasil');
    }
}
