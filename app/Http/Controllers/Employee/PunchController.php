<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\PunchLog;
use App\Support\AttendancePunchSupport;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PunchController extends Controller
{
    public function punchIn(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'location_label'    => 'nullable|string|max:255',
            'formatted_address' => 'nullable|string|max:1000',
            'suburb'            => 'nullable|string|max:255',
            'city'              => 'nullable|string|max:255',
            'state'             => 'nullable|string|max:255',
            'country'           => 'nullable|string|max:255',
        ]);

        $user  = $this->currentUser();
        $today = Carbon::today()->toDateString();

        // Close any previous day's unclosed attendance before processing today.
        // Runs outside the transaction to avoid cross-row deadlocks.
        AttendancePunchSupport::closePreviousOpenAttendance($user->id);

        try {
            $punchTime = DB::transaction(function () use ($user, $today, $payload, $request) {

                // Lock today's attendance row (if it exists) for the duration of
                // this transaction so concurrent requests queue up instead of racing.
                $attendance = Attendance::where('user_id', $user->id)
                    ->where('date', $today)
                    ->lockForUpdate()
                    ->first();

                if (! $attendance) {
                    // Safe to create inside the transaction; the unique [user_id, date]
                    // constraint on the attendances table prevents concurrent duplicates.
                    $attendance = Attendance::create([
                        'user_id' => $user->id,
                        'date'    => $today,
                        'status'  => 'present',
                    ]);
                }

                // Guard: already punched in (checked atomically under the row lock).
                if ($attendance->punch_in) {
                    return null;
                }

                $now = now();

                $attendance->update([
                    'punch_in'   => $now->format('H:i:s'),
                    'punch_out'  => null,
                    'work_hours' => null,
                    'status'     => 'present',
                ]);

                // The unique constraint on punch_logs(attendance_id, type) acts as the
                // final DB-level barrier; caught below as UniqueConstraintViolationException.
                PunchLog::create([
                    'user_id'           => $user->id,
                    'attendance_id'     => $attendance->id,
                    'type'              => 'in',
                    'punched_at'        => $now,
                    'ip_address'        => $request->ip(),
                    'latitude'          => $payload['latitude']          ?? null,
                    'longitude'         => $payload['longitude']         ?? null,
                    'location_label'    => $payload['location_label']    ?? null,
                    'formatted_address' => $payload['formatted_address'] ?? null,
                    'suburb'            => $payload['suburb']            ?? null,
                    'city'              => $payload['city']              ?? null,
                    'state'             => $payload['state']             ?? null,
                    'country'           => $payload['country']           ?? null,
                ]);

                return $now->format('H:i');
            });

        } catch (UniqueConstraintViolationException $e) {
            // Absolute last resort: the DB unique constraint on punch_logs fired,
            // meaning a concurrent request slipped through the row lock.
            Log::notice('Duplicate punch-in blocked at DB level (unique constraint).', [
                'user_id' => $user->id,
                'date'    => $today,
                'ip'      => $request->ip(),
            ]);

            return back()->with('error', 'You have already punched in for today.');
        }

        if ($punchTime === null) {
            Log::notice('Duplicate punch-in blocked.', [
                'user_id' => $user->id,
                'date'    => $today,
                'ip'      => $request->ip(),
            ]);

            return back()->with('error', 'You have already punched in for today.');
        }

        return back()->with('success', "Punched in at {$punchTime}. Have a productive day!");
    }

    public function punchOut(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'location_label'    => 'nullable|string|max:255',
            'formatted_address' => 'nullable|string|max:1000',
            'suburb'            => 'nullable|string|max:255',
            'city'              => 'nullable|string|max:255',
            'state'             => 'nullable|string|max:255',
            'country'           => 'nullable|string|max:255',
        ]);

        $user  = $this->currentUser();
        $today = Carbon::today()->toDateString();

        try {
            $result = DB::transaction(function () use ($user, $today, $payload, $request) {

                // Lock the attendance row atomically so concurrent punch-out requests
                // cannot read the same pre-punch-out state simultaneously.
                $attendance = Attendance::where('user_id', $user->id)
                    ->where('date', $today)
                    ->lockForUpdate()
                    ->first();

                if (! $attendance || ! $attendance->punch_in) {
                    return 'no_punch_in';
                }

                // Guard: already punched out (checked atomically under the row lock).
                if ($attendance->punch_out) {
                    return 'duplicate';
                }

                $now = now();

                // The unique constraint on punch_logs(attendance_id, type) acts as the
                // final DB-level barrier; caught below as UniqueConstraintViolationException.
                PunchLog::create([
                    'user_id'           => $user->id,
                    'attendance_id'     => $attendance->id,
                    'type'              => 'out',
                    'punched_at'        => $now,
                    'ip_address'        => $request->ip(),
                    'latitude'          => $payload['latitude']          ?? null,
                    'longitude'         => $payload['longitude']         ?? null,
                    'location_label'    => $payload['location_label']    ?? null,
                    'formatted_address' => $payload['formatted_address'] ?? null,
                    'suburb'            => $payload['suburb']            ?? null,
                    'city'              => $payload['city']              ?? null,
                    'state'             => $payload['state']             ?? null,
                    'country'           => $payload['country']           ?? null,
                ]);

                $workHours = AttendancePunchSupport::calculateWorkHours(
                    $today,
                    $attendance->punch_in,
                    $now->format('H:i:s')
                );

                $attendance->update([
                    'punch_out'  => $now->format('H:i:s'),
                    'work_hours' => $workHours,
                    'status'     => 'present',
                ]);

                return [$now->format('H:i'), $workHours];
            });

        } catch (UniqueConstraintViolationException $e) {
            // Absolute last resort: DB unique constraint on punch_logs fired.
            Log::notice('Duplicate punch-out blocked at DB level (unique constraint).', [
                'user_id' => $user->id,
                'date'    => $today,
                'ip'      => $request->ip(),
            ]);

            return back()->with('error', 'You have already punched out for today.');
        }

        if ($result === 'no_punch_in') {
            return back()->with('error', 'No punch-in record found for today.');
        }

        if ($result === 'duplicate') {
            Log::notice('Duplicate punch-out blocked.', [
                'user_id' => $user->id,
                'date'    => $today,
                'ip'      => $request->ip(),
            ]);

            return back()->with('error', 'You have already punched out for today.');
        }

        [$punchTime, $workHours] = $result;
        $timeStr = AttendancePunchSupport::formatWorkHours($workHours);

        if ($workHours < 9) {
            return back()->with('warning',
                "Punched out at {$punchTime}. Work time today: {$timeStr} - below the required 9 hours."
            );
        }

        return back()->with('success',
            "Punched out at {$punchTime}. Total work time: {$timeStr}. Great work!"
        );
    }
}
