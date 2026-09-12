<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function getWorkSchedule(): array
    {
        return [
            'work_start_time' => Setting::where('key', 'work_start_time')->value('value') ?? '08:00',
            'work_end_time' => Setting::where('key', 'work_end_time')->value('value') ?? '17:00',
            'attendance_radius_meters' => (int) (Setting::get('attendance_radius_meters') ?? 100),
            'attendance_latitude' => Setting::get('attendance_latitude'),
            'attendance_longitude' => Setting::get('attendance_longitude'),
        ];
    }

    public function checkIn(int $userId, ?string $notes = null, ?float $latitude = null, ?float $longitude = null): Attendance
    {
        $this->ensureWithinAttendanceRadius($latitude, $longitude);

        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->format('H:i:s');
        $schedule = $this->getWorkSchedule();

        $status = Carbon::parse($now)->gt(Carbon::parse($schedule['work_start_time']))
            ? AttendanceStatus::Late
            : AttendanceStatus::Present;

        return Attendance::updateOrCreate(
            ['user_id' => $userId, 'date' => $today],
            [
                'check_in' => $now,
                'check_in_latitude' => $latitude,
                'check_in_longitude' => $longitude,
                'status' => $status,
                'notes' => $notes,
            ]
        );
    }

    public function checkOut(int $userId, ?string $notes = null, ?float $latitude = null, ?float $longitude = null): Attendance
    {
        $this->ensureWithinAttendanceRadius($latitude, $longitude);

        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->format('H:i:s');

        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->firstOrFail();

        $updateData = [
            'check_out' => $now,
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
        ];
        if ($notes) {
            $updateData['notes'] = $attendance->notes
                ? $attendance->notes . ' | Pulang: ' . $notes
                : 'Pulang: ' . $notes;
        }

        $attendance->update($updateData);

        return $attendance->fresh();
    }

    private function ensureWithinAttendanceRadius(?float $latitude, ?float $longitude): void
    {
        $schedule = $this->getWorkSchedule();

        if ($latitude === null || $longitude === null) {
            throw ValidationException::withMessages([
                'location' => 'Lokasi perangkat wajib diaktifkan untuk melakukan presensi.',
            ]);
        }

        if ($schedule['attendance_latitude'] === null || $schedule['attendance_longitude'] === null) {
            throw ValidationException::withMessages([
                'location' => 'Lokasi presensi belum dikonfigurasi oleh Super Admin.',
            ]);
        }

        $distance = $this->distanceInMeters(
            $latitude,
            $longitude,
            (float) $schedule['attendance_latitude'],
            (float) $schedule['attendance_longitude']
        );

        if ($distance > $schedule['attendance_radius_meters']) {
            throw ValidationException::withMessages([
                'location' => sprintf(
                    'Anda berada di luar radius presensi (%d meter). Jarak Anda sekitar %d meter.',
                    $schedule['attendance_radius_meters'],
                    round($distance)
                ),
            ]);
        }
    }

    private function distanceInMeters(float $latitude, float $longitude, float $targetLatitude, float $targetLongitude): float
    {
        $earthRadius = 6_371_000;
        $latitudeDelta = deg2rad($targetLatitude - $latitude);
        $longitudeDelta = deg2rad($targetLongitude - $longitude);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad($targetLatitude))
            * sin($longitudeDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
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
