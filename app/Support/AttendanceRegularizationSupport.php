<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use Carbon\Carbon;

class AttendanceRegularizationSupport
{
    public static function requestTypes(): array
    {
        return config('attendance_regularization.types', []);
    }

    public static function normalizeTime(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        return substr($time, 0, 5);
    }

    public static function timeToMinutes(?string $time): ?int
    {
        if (! $time) {
            return null;
        }

        [$hours, $minutes] = array_map('intval', explode(':', self::normalizeTime($time)));

        return ($hours * 60) + $minutes;
    }

    public static function calculateWorkHours(?string $punchIn, ?string $punchOut): ?float
    {
        $inMinutes = self::timeToMinutes($punchIn);
        $outMinutes = self::timeToMinutes($punchOut);

        if ($inMinutes === null || $outMinutes === null || $outMinutes < $inMinutes) {
            return null;
        }

        return round(($outMinutes - $inMinutes) / 60, 2);
    }

    public static function formatWorkHours(?float $hours): string
    {
        if ($hours === null) {
            return '-';
        }

        $whole = (int) floor($hours);
        $minutes = (int) round(($hours - $whole) * 60);

        return "{$whole}h {$minutes}m";
    }

    public static function withinBounds(?string $time): bool
    {
        if (! $time) {
            return true;
        }

        $minutes = self::timeToMinutes($time);

        return $minutes !== null
            && $minutes >= self::timeToMinutes(config('attendance_regularization.min_time'))
            && $minutes <= self::timeToMinutes(config('attendance_regularization.max_time'));
    }

    public static function exceedsMaxDuration(?string $punchIn, ?string $punchOut): bool
    {
        $inMinutes = self::timeToMinutes($punchIn);
        $outMinutes = self::timeToMinutes($punchOut);

        if ($inMinutes === null || $outMinutes === null || $outMinutes < $inMinutes) {
            return false;
        }

        return ($outMinutes - $inMinutes) > (int) config('attendance_regularization.max_duration_minutes', 1080);
    }

    public static function currentAttendanceFor(int $userId, string $date): ?Attendance
    {
        return Attendance::where('user_id', $userId)
            ->where('date', $date)
            ->first();
    }

    public static function applyFinalApproval(AttendanceRegularization $regularization): Attendance
    {
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $regularization->user_id, 'date' => $regularization->date->toDateString()],
            ['status' => 'present']
        );

        $punchIn = $regularization->requested_punch_in ?: $attendance->punch_in;
        $punchOut = $regularization->requested_punch_out ?: $attendance->punch_out;
        $workHours = self::calculateWorkHours($punchIn, $punchOut);

        $attendance->update([
            'punch_in' => $punchIn,
            'punch_out' => $punchOut,
            'work_hours' => $workHours,
            'status' => 'present',
        ]);

        if (! $regularization->attendance_id) {
            $regularization->forceFill(['attendance_id' => $attendance->id])->save();
        }

        return $attendance->fresh();
    }

    public static function isFutureDate(string $date): bool
    {
        return Carbon::parse($date)->isFuture();
    }

    public static function isFutureTime(string $date, ?string $time): bool
    {
        $normalizedTime = self::normalizeTime($time);

        if (! $normalizedTime) {
            return false;
        }

        return Carbon::createFromFormat('Y-m-d H:i', "{$date} {$normalizedTime}")->isFuture();
    }
}
