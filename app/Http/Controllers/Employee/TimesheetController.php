<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Attendance;
use App\Models\Timesheet;
use App\Models\TimesheetComment;
use App\Models\TimesheetEntry;
use App\Models\User;
use App\Notifications\TimesheetCommentNotification;
use App\Notifications\TimesheetSubmittedNotification;
use App\Support\TimesheetSupport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function index(Request $request)
    {
        $employee = $this->currentUser();
        $month = $request->input('month', now()->format('Y-m'));

        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $monthDate = now()->startOfMonth();
        }

        $timesheets = Timesheet::where('user_id', $employee->id)
            ->whereBetween('date', [$monthDate->toDateString(), $monthDate->copy()->endOfMonth()->toDateString()])
            ->with(['entries'])
            ->orderByDesc('date')
            ->get();

        $stats = [
            'total' => $timesheets->count(),
            'draft' => $timesheets->where('status', 'draft')->count(),
            'pending' => $timesheets->whereIn('status', ['pending_l1', 'pending_l2'])->count(),
            'approved' => $timesheets->where('status', 'approved')->count(),
            'rejected' => $timesheets->where('status', 'rejected')->count(),
        ];

        return view('employee.timesheets.index', array_merge(
            compact('timesheets', 'stats', 'month', 'monthDate'),
            $this->selfTimesheetViewData($employee)
        ));
    }

    public function show(Request $request, string $date)
    {
        $employee = $this->currentUser();

        try {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            abort(404);
        }

        $timesheet = Timesheet::firstOrCreate(
            ['user_id' => $employee->id, 'date' => $date],
            [
                'status' => 'draft',
                'l1_manager_id' => $employee->level1_manager_id,
                'l2_manager_id' => $employee->level2_manager_id,
            ]
        );

        $timesheet->load(['entries', 'comments.user', 'comments.replies.user', 'l1Manager', 'l2Manager', 'user']);

        $attendance = Attendance::where('user_id', $employee->id)
            ->where('date', $date)
            ->first();

        $gridMeta = TimesheetSupport::buildGridMeta($attendance, $timesheet->entries);

        return view('employee.timesheets.show', array_merge(
            compact('timesheet', 'dateObj', 'attendance', 'gridMeta'),
            $this->selfTimesheetViewData($employee, $timesheet)
        ));
    }

    public function storeEntry(Request $request, Timesheet $timesheet)
    {
        $employee = $this->currentUser();

        if ($timesheet->user_id !== $employee->id) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        if (! $timesheet->isEditable()) {
            return response()->json(['error' => 'Timesheet cannot be edited in its current status.'], 422);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($attendanceError = $this->validateAttendanceWindow($employee, $timesheet, $validated['start_time'], $validated['end_time'])) {
            return $attendanceError;
        }

        $overlap = $this->checkOverlap($timesheet->id, $validated['start_time'], $validated['end_time']);
        if ($overlap) {
            return response()->json([
                'error' => "This time range overlaps with an existing entry: \"{$overlap->title}\" ({$overlap->start_time} - {$overlap->end_time}).",
            ], 422);
        }

        $duration = TimesheetSupport::durationMinutes($validated['start_time'], $validated['end_time']);

        $entry = TimesheetEntry::create([
            'timesheet_id' => $timesheet->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => $duration,
        ]);

        return response()->json([
            'success' => true,
            'entry' => [
                'id' => $entry->getRouteKey(),
                'title' => $entry->title,
                'description' => $entry->description,
                'start_time' => $entry->start_time,
                'end_time' => $entry->end_time,
                'duration_minutes' => $entry->duration_minutes,
                'formatted_duration' => $entry->formatted_duration,
            ],
            'total_minutes' => $timesheet->fresh()->load('entries')->total_minutes,
        ]);
    }

    public function updateEntry(Request $request, Timesheet $timesheet, TimesheetEntry $entry)
    {
        $employee = $this->currentUser();

        if ($timesheet->user_id !== $employee->id || $entry->timesheet_id !== $timesheet->id) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        if (! $timesheet->isEditable()) {
            return response()->json(['error' => 'Timesheet cannot be edited in its current status.'], 422);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($attendanceError = $this->validateAttendanceWindow($employee, $timesheet, $validated['start_time'], $validated['end_time'])) {
            return $attendanceError;
        }

        $overlap = $this->checkOverlap($timesheet->id, $validated['start_time'], $validated['end_time'], $entry->id);
        if ($overlap) {
            return response()->json([
                'error' => "This time range overlaps with an existing entry: \"{$overlap->title}\" ({$overlap->start_time} - {$overlap->end_time}).",
            ], 422);
        }

        $duration = TimesheetSupport::durationMinutes($validated['start_time'], $validated['end_time']);

        $entry->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => $duration,
        ]);

        return response()->json([
            'success' => true,
            'entry' => [
                'id' => $entry->getRouteKey(),
                'title' => $entry->title,
                'description' => $entry->description,
                'start_time' => $entry->start_time,
                'end_time' => $entry->end_time,
                'duration_minutes' => $entry->duration_minutes,
                'formatted_duration' => $entry->formatted_duration,
            ],
            'total_minutes' => $timesheet->fresh()->load('entries')->total_minutes,
        ]);
    }

    public function deleteEntry(Timesheet $timesheet, TimesheetEntry $entry)
    {
        $employee = $this->currentUser();

        if ($timesheet->user_id !== $employee->id || $entry->timesheet_id !== $timesheet->id) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        if (! $timesheet->isEditable()) {
            return response()->json(['error' => 'Timesheet cannot be edited in its current status.'], 422);
        }

        $entry->delete();

        return response()->json([
            'success' => true,
            'total_minutes' => $timesheet->fresh()->load('entries')->total_minutes,
        ]);
    }

    public function submit(Timesheet $timesheet)
    {
        $employee = $this->currentUser();

        if ($timesheet->user_id !== $employee->id) {
            abort(403);
        }

        if (! $timesheet->isEditable()) {
            return back()->with('error', 'This timesheet cannot be submitted in its current status.');
        }

        if ($timesheet->entries()->count() === 0) {
            return back()->with('error', 'Please add at least one work entry before submitting.');
        }

        $timesheet->update([
            'status' => 'pending_l1',
            'submitted_at' => now(),
            'l1_remarks' => null,
            'l2_remarks' => null,
        ]);

        if ($timesheet->l1_manager_id) {
            $timesheet->load(['user', 'entries']);
            $url = route('manager.timesheets.show', $timesheet);

            $timesheet->l1Manager->notify(new TimesheetSubmittedNotification($timesheet, $url));

            AppNotification::notify(
                $timesheet->l1_manager_id,
                'Timesheet Submitted for Review',
                "{$employee->name} submitted their timesheet for {$timesheet->date->format('d M Y')} ({$timesheet->formatted_total_hours} logged).",
                'general',
                $timesheet->id,
                $url
            );
        }

        return back()->with('success', 'Timesheet submitted successfully. Your L1 manager has been notified.');
    }

    public function addComment(Request $request, Timesheet $timesheet)
    {
        $employee = $this->currentUser();

        if ($timesheet->user_id !== $employee->id) {
            abort(403);
        }

        $request->validate([
            'comment' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:timesheet_comments,id',
        ]);

        TimesheetComment::create([
            'timesheet_id' => $timesheet->id,
            'user_id' => $employee->id,
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        $this->notifyCommentParticipants($timesheet, $employee, $request->comment);

        return back()->with('success', 'Comment added.');
    }

    protected function selfTimesheetViewData(User $user, ?Timesheet $timesheet = null): array
    {
        $routePrefix = $user->isManager() ? 'manager.my-timesheets' : 'employee.timesheets';
        $pathPrefix = $user->isManager() ? '/manager/my-timesheets' : '/employee/timesheets';

        return [
            'timesheetIndexRoute' => $routePrefix . '.index',
            'timesheetShowRoute' => $routePrefix . '.show',
            'timesheetSubmitRoute' => $routePrefix . '.submit',
            'timesheetCommentRoute' => $routePrefix . '.comment',
            'timesheetEntryStoreRoute' => $timesheet ? route($routePrefix . '.entries.store', $timesheet) : null,
            'timesheetEntryBaseUrl' => $timesheet ? url($pathPrefix . '/' . $timesheet->getRouteKey() . '/entries') : null,
            'defaultTimelineUrl' => route($routePrefix . '.show', ['date' => now()->toDateString()]),
        ];
    }

    protected function selfTimesheetUrl(Timesheet $timesheet): string
    {
        $route = $timesheet->user->isManager() ? 'manager.my-timesheets.show' : 'employee.timesheets.show';

        return route($route, ['date' => $timesheet->date->toDateString()]);
    }

    protected function validateAttendanceWindow(User $user, Timesheet $timesheet, string $start, string $end)
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $timesheet->date->toDateString())
            ->first();

        if (! $attendance || ! $attendance->punch_in) {
            return response()->json([
                'error' => 'You can add timesheet entries only after punching in for this date.',
            ], 422);
        }

        $punchIn = substr($attendance->punch_in, 0, 5);
        $maxAllowed = null;

        if ($attendance->punch_out) {
            $maxAllowed = substr($attendance->punch_out, 0, 5);
        } elseif ($timesheet->date->isToday()) {
            $maxAllowed = now()->format('H:i');
        }

        if ($start < $punchIn) {
            return response()->json([
                'error' => "Start time cannot be before your Punch In time ({$punchIn}).",
            ], 422);
        }

        if ($maxAllowed && $end > $maxAllowed) {
            return response()->json([
                'error' => $attendance->punch_out
                    ? "End time cannot be after your Punch Out time ({$maxAllowed})."
                    : "End time cannot be after the current time ({$maxAllowed}) until you punch out.",
            ], 422);
        }

        return null;
    }

    protected function notifyCommentParticipants(Timesheet $timesheet, User $author, string $comment): void
    {
        $timesheet->loadMissing(['user', 'l1Manager', 'l2Manager']);

        $snippet = mb_strlen($comment) > 120
            ? mb_substr($comment, 0, 120) . '...'
            : $comment;

        $participants = User::whereIn('id', array_filter([
            $timesheet->user_id,
            $timesheet->l1_manager_id,
            $timesheet->l2_manager_id,
        ]))
            ->where('id', '!=', $author->id)
            ->get();

        foreach ($participants as $participant) {
            $url = $participant->id === $timesheet->user_id
                ? $this->selfTimesheetUrl($timesheet)
                : route('manager.timesheets.show', $timesheet);

            $participant->notify(new TimesheetCommentNotification($timesheet, $author, $comment, $url));

            AppNotification::notify(
                $participant->id,
                "Comment on Timesheet: {$timesheet->date->format('d M Y')}",
                "{$author->name} commented: \"{$snippet}\" - " . now()->format('d M Y, h:i A'),
                'general',
                $timesheet->id,
                $url
            );
        }
    }

    private function checkOverlap(int $timesheetId, string $start, string $end, ?int $excludeId = null): ?TimesheetEntry
    {
        $query = TimesheetEntry::where('timesheet_id', $timesheetId)
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($q2) use ($start, $end) {
                    $q2->where('start_time', '>=', $start)->where('start_time', '<', $end);
                })->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('end_time', '>', $start)->where('end_time', '<=', $end);
                })->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('start_time', '<=', $start)->where('end_time', '>=', $end);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }
}
