<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\AuditLog;
use Carbon\Carbon;

class AttendancePunchSupport
{
    public static function closePreviousOpenAttendance(int $userId): ?Attendance
    {
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', '<', today()->toDateString())
            ->whereNotNull('punch_in')
            ->whereNull('punch_out')
            ->whereNotIn('status', ['missed_punch_out', 'pending_regularization'])
            ->orderByDesc('date')
            ->first();

        if (! $attendance) {
            return null;
        }

        $oldValues = $attendance->only(['status', 'note']);
        $note   = trim((string) $attendance->note);
        $marker = 'Marked missed_punch_out because punch-out was not recorded.';
        $attendance->update([
            'status' => 'missed_punch_out',
            'note'   => str_contains($note, $marker) ? $note : trim($note . ' ' . $marker),
        ]);

        AuditLog::record('attendance', 'marked_missed_punch_out', $attendance->id, $userId, $oldValues, [
            'status' => $attendance->status,
            'note'   => $attendance->note,
        ]);

        return $attendance->fresh();
    }

    public static function calculateWorkHours(string $date, string $punchIn, string $punchOut): float
    {
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $punchIn);
        $end = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $punchOut);

        return round($start->diffInMinutes($end) / 60, 2);
    }

    public static function formatWorkHours(float $workHours): string
    {
        $h = (int) floor($workHours);
        $m = (int) round(($workHours - $h) * 60);

        return "{$h}h {$m}m";
    }
}
