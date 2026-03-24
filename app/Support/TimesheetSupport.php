<?php

namespace App\Support;

use App\Models\Attendance;

class TimesheetSupport
{
    public const SLOT_MINUTES = 30;
    public const SLOT_HEIGHT = 45;
    public const DAY_MINUTES = 24 * 60;
    public const GRID_START_MINUTES = 6 * 60;   // 06:00
    public const GRID_END_MINUTES   = 23 * 60;  // 23:00

    public static function pixelsPerMinute(): float
    {
        return self::SLOT_HEIGHT / self::SLOT_MINUTES;
    }

    public static function timeToMinutes(?string $time): ?int
    {
        if (! $time) {
            return null;
        }

        [$hours, $minutes] = array_map('intval', explode(':', substr($time, 0, 5)));

        return ($hours * 60) + $minutes;
    }

    public static function minutesToTime(int $minutes): string
    {
        $minutes = max(0, min($minutes, self::DAY_MINUTES - 1));
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return str_pad((string) $hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $mins, 2, '0', STR_PAD_LEFT);
    }

    public static function durationMinutes(string $start, string $end): int
    {
        return self::timeToMinutes($end) - self::timeToMinutes($start);
    }

    public static function buildGridMeta(?Attendance $attendance, iterable $entries): array
    {
        return [
            'slot_minutes' => self::SLOT_MINUTES,
            'slot_height' => self::SLOT_HEIGHT,
            'pixels_per_minute' => self::pixelsPerMinute(),
            'start_minutes' => self::GRID_START_MINUTES,
            'end_minutes' => self::GRID_END_MINUTES,
            'total_minutes' => self::GRID_END_MINUTES - self::GRID_START_MINUTES,
            'scroll_anchor_minutes' => $attendance?->punch_in
                ? self::timeToMinutes($attendance->punch_in)
                : 9 * 60,
        ];
    }
}
