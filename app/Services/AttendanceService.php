<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Setting;
use Carbon\Carbon;

class AttendanceService
{
    public function getWorkSchedule(): array
    {
        return [
            'work_start_time' => Setting::where('key', 'work_start_time')->value('value') ?? '08:00',
            'work_end_time' => Setting::where('key', 'work_end_time')->value('value') ?? '17:00',
        ];
    }

    public function checkIn(int $userId, ?string $notes = null): Attendance
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->format('H:i:s');
        $schedule = $this->getWorkSchedule();

        $status = Carbon::parse($now)->gt(Carbon::parse($schedule['work_start_time']))
            ? AttendanceStatus::Late
            : AttendanceStatus::Present;

        return Attendance::updateOrCreate(
            ['user_id' => $userId, 'date' => $today],
            ['check_in' => $now, 'status' => $status, 'notes' => $notes]
        );
    }

    public function checkOut(int $userId, ?string $notes = null): Attendance
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->format('H:i:s');

        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->firstOrFail();

        $updateData = ['check_out' => $now];
        if ($notes) {
            $updateData['notes'] = $attendance->notes
                ? $attendance->notes . ' | Pulang: ' . $notes
                : 'Pulang: ' . $notes;
        }

        $attendance->update($updateData);

        return $attendance->fresh();
    }

    public function getTodayAttendance(int $userId): ?Attendance
    {
        return Attendance::where('user_id', $userId)
            ->where('date', Carbon::today()->toDateString())
            ->first();
    }

    public function getUserLog(int $userId, ?string $from = null, ?string $to = null, int $perPage = 15)
    {
        $query = Attendance::where('user_id', $userId)->latest('date');

        if ($from && $to) {
            $query->betweenDates($from, $to);
        }

        return $query->paginate($perPage);
    }

    public function getAllLog(?string $from = null, ?string $to = null, ?int $userId = null, ?string $status = null, int $perPage = 20)
    {
        $query = Attendance::with('user')->latest('date')->latest('check_in');

        if ($from && $to) {
            $query->betweenDates($from, $to);
        }

        if ($userId) {
            $query->forUser($userId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
