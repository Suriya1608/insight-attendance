<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use App\Models\AttendanceRegularizationComment;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\AttendanceRegularizationCommentNotification;
use App\Notifications\AttendanceRegularizationSubmittedNotification;
use App\Support\AttendanceRegularizationSupport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttendanceRegularizationController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->currentUser();

        $query = AttendanceRegularization::where('user_id', $user->id)
            ->with(['attendance'])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $regularizations = $query->paginate(12)->withQueryString();
        $stats = [
            'pending' => AttendanceRegularization::where('user_id', $user->id)->whereIn('status', ['pending_l1', 'pending_l2'])->count(),
            'approved' => AttendanceRegularization::where('user_id', $user->id)->where('status', 'approved')->count(),
            'rejected' => AttendanceRegularization::where('user_id', $user->id)->where('status', 'rejected')->count(),
        ];
        $types = AttendanceRegularizationSupport::requestTypes();

        return view('employee.attendance-regularizations.index', array_merge(
            compact('regularizations', 'stats', 'types'),
            $this->selfRouteData($user)
        ));
    }

    public function create(Request $request)
    {
        $user = $this->currentUser();
        $date = (string) $request->input('date', '');
        $type = (string) $request->input('request_type', 'missed_punch_out');
        $attendance = $date !== '' ? AttendanceRegularizationSupport::currentAttendanceFor($user->id, $date) : null;

        return view('employee.attendance-regularizations.create', array_merge(
            [
                'selectedDate' => $date,
                'selectedType' => $type,
                'attendance' => $attendance,
                'types' => AttendanceRegularizationSupport::requestTypes(),
            ],
            $this->selfRouteData($user)
        ));
    }

    public function snapshot(Request $request): JsonResponse
    {
        $user = $this->currentUser();
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        if (AttendanceRegularizationSupport::isFutureDate($validated['date'])) {
            throw ValidationException::withMessages(['date' => 'Future dates cannot be regularized.']);
        }

        $attendance = AttendanceRegularizationSupport::currentAttendanceFor($user->id, $validated['date']);

        return response()->json($this->snapshotPayload($validated['date'], $attendance));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->currentUser();
        $payload = $this->resolvePayload($request, $user);

        $attachmentPath = $request->hasFile('attachment')
            ? $request->file('attachment')->store('attendance-regularizations', 'public')
            : null;

        $regularization = AttendanceRegularization::create([
            'user_id' => $user->id,
            'attendance_id' => $payload['attendance']?->id,
            'date' => $payload['date'],
            'request_type' => $payload['request_type'],
            'original_punch_in' => $payload['original_punch_in'],
            'original_punch_out' => $payload['original_punch_out'],
            'requested_punch_in' => $payload['requested_punch_in'],
            'requested_punch_out' => $payload['requested_punch_out'],
            'reason' => $payload['reason'],
            'attachment_path' => $attachmentPath,
            'status' => 'draft',
            'l1_manager_id' => $user->level1_manager_id,
            'l2_manager_id' => $user->level2_manager_id,
        ]);

        AuditLog::record('attendance_regularization', 'created', $regularization->id, $user->id, null, [
            'status' => $regularization->status,
            'date' => $regularization->date->toDateString(),
            'request_type' => $regularization->request_type,
            'requested_punch_in' => $regularization->requested_punch_in,
            'requested_punch_out' => $regularization->requested_punch_out,
        ]);

        if ($request->input('action') === 'submit') {
            return $this->submitRegularization($regularization, $user);
        }

        return redirect()->route($this->selfRouteData($user)['showRoute'], $regularization)
            ->with('success', 'Attendance regularization saved as draft.');
    }

    public function show(AttendanceRegularization $regularization)
    {
        $user = $this->currentUser();

        if ($regularization->user_id !== $user->id) {
            abort(403);
        }

        $regularization->load(['attendance', 'comments.user', 'comments.replies.user', 'l1Manager', 'l2Manager']);
        $types = AttendanceRegularizationSupport::requestTypes();

        return view('employee.attendance-regularizations.show', array_merge(
            compact('regularization', 'types'),
            $this->selfRouteData($user)
        ));
    }

    public function update(Request $request, AttendanceRegularization $regularization): RedirectResponse
    {
        $user = $this->currentUser();

        if ($regularization->user_id !== $user->id) {
            abort(403);
        }

        if (! $regularization->isEditable()) {
            return back()->with('error', 'This request cannot be edited in its current status.');
        }

        $payload = $this->resolvePayload($request, $user, $regularization);
        $oldValues = $regularization->only([
            'date',
            'request_type',
            'requested_punch_in',
            'requested_punch_out',
            'reason',
            'attachment_path',
            'status',
        ]);

        if ($request->hasFile('attachment')) {
            if ($regularization->attachment_path) {
                Storage::disk('public')->delete($regularization->attachment_path);
            }
            $regularization->attachment_path = $request->file('attachment')->store('attendance-regularizations', 'public');
        }

        $regularization->fill([
            'attendance_id' => $payload['attendance']?->id,
            'date' => $payload['date'],
            'request_type' => $payload['request_type'],
            'original_punch_in' => $payload['original_punch_in'],
            'original_punch_out' => $payload['original_punch_out'],
            'requested_punch_in' => $payload['requested_punch_in'],
            'requested_punch_out' => $payload['requested_punch_out'],
            'reason' => $payload['reason'],
        ])->save();

        AuditLog::record('attendance_regularization', 'updated', $regularization->id, $user->id, $oldValues, $regularization->only(array_keys($oldValues)));

        if ($request->input('action') === 'submit') {
            return $this->submitRegularization($regularization->fresh(), $user);
        }

        return redirect()->route($this->selfRouteData($user)['showRoute'], $regularization)
            ->with('success', 'Attendance regularization updated.');
    }

    public function submit(AttendanceRegularization $regularization): RedirectResponse
    {
        $user = $this->currentUser();

        if ($regularization->user_id !== $user->id) {
            abort(403);
        }

        return $this->submitRegularization($regularization, $user);
    }

    public function addComment(Request $request, AttendanceRegularization $regularization): RedirectResponse
    {
        $user = $this->currentUser();

        if ($regularization->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'comment' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:attendance_regularization_comments,id',
        ]);

        AttendanceRegularizationComment::create([
            'attendance_regularization_id' => $regularization->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        AuditLog::record('attendance_regularization', 'commented', $regularization->id, $user->id, null, [
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        $this->notifyCommentParticipants($regularization->fresh(['user', 'l1Manager', 'l2Manager']), $user, $request->comment);

        return back()->with('success', 'Comment added.');
    }

    protected function selfRouteData(User $user): array
    {
        $prefix = $user->isManager() ? 'manager.my-regularizations' : 'employee.regularizations';

        return [
            'indexRoute' => $prefix . '.index',
            'createRoute' => $prefix . '.create',
            'storeRoute' => $prefix . '.store',
            'showRoute' => $prefix . '.show',
            'updateRoute' => $prefix . '.update',
            'submitRoute' => $prefix . '.submit',
            'commentRoute' => $prefix . '.comment',
            'snapshotRoute' => $prefix . '.snapshot',
        ];
    }

    protected function selfRequestUrl(AttendanceRegularization $regularization): string
    {
        $route = $regularization->user->isManager() ? 'manager.my-regularizations.show' : 'employee.regularizations.show';

        return route($route, $regularization);
    }

    protected function resolvePayload(Request $request, User $user, ?AttendanceRegularization $regularization = null): array
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'request_type' => 'required|string|in:' . implode(',', array_keys(AttendanceRegularizationSupport::requestTypes())),
            'requested_punch_in' => 'nullable|date_format:H:i',
            'requested_punch_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:2000',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
            'action' => 'nullable|string|in:save,submit',
        ]);

        if (AttendanceRegularizationSupport::isFutureDate($validated['date'])) {
            throw ValidationException::withMessages(['date' => 'Future dates cannot be regularized.']);
        }

        $attendance = AttendanceRegularizationSupport::currentAttendanceFor($user->id, $validated['date']);
        $originalPunchIn = AttendanceRegularizationSupport::normalizeTime($attendance?->punch_in);
        $originalPunchOut = AttendanceRegularizationSupport::normalizeTime($attendance?->punch_out);
        $requestedPunchInInput = AttendanceRegularizationSupport::normalizeTime($validated['requested_punch_in'] ?? null);
        $requestedPunchOutInput = AttendanceRegularizationSupport::normalizeTime($validated['requested_punch_out'] ?? null);
        $requestedPunchIn = $requestedPunchInInput;
        $requestedPunchOut = $requestedPunchOutInput;
        $effectivePunchIn = $requestedPunchIn ?: $originalPunchIn;
        $effectivePunchOut = $requestedPunchOut ?: $originalPunchOut;
        $errors = [];

        if ($validated['request_type'] === 'missed_punch_out') {
            $requestedPunchIn = null;
            $effectivePunchIn = $originalPunchIn;
            $effectivePunchOut = $requestedPunchOutInput ?: $originalPunchOut;

            if (! $originalPunchIn) {
                $errors['requested_punch_in'] = 'System punch-in is not available for this date.';
            }
            if ($originalPunchOut) {
                $errors['request_type'] = 'Use time correction when a punch-out already exists for this date.';
            }
            if (! $requestedPunchOutInput) {
                $errors['requested_punch_out'] = 'Actual punch-out time is required for a missed punch-out request.';
            }

            $requestedPunchOut = $requestedPunchOutInput;
        }

        if ($validated['request_type'] === 'missed_punch_in') {
            $requestedPunchOut = null;
            $effectivePunchIn = $requestedPunchInInput ?: $originalPunchIn;
            $effectivePunchOut = $originalPunchOut;

            if (! $originalPunchOut) {
                $errors['requested_punch_out'] = 'System punch-out is not available for this date.';
            }
            if ($originalPunchIn) {
                $errors['request_type'] = 'Use time correction when a punch-in already exists for this date.';
            }
            if (! $requestedPunchInInput) {
                $errors['requested_punch_in'] = 'Actual punch-in time is required for a missed punch-in request.';
            }

            $requestedPunchIn = $requestedPunchInInput;
        }

        if ($validated['request_type'] === 'time_correction' && ! $requestedPunchIn && ! $requestedPunchOut) {
            $errors['requested_punch_in'] = 'Provide at least one corrected time.';
        }

        if (! AttendanceRegularizationSupport::withinBounds($requestedPunchIn)) {
            $errors['requested_punch_in'] = 'Punch-in time is outside the allowed shift bounds.';
        }

        if (! AttendanceRegularizationSupport::withinBounds($requestedPunchOut)) {
            $errors['requested_punch_out'] = 'Punch-out time is outside the allowed shift bounds.';
        }

        if ($effectivePunchIn && $effectivePunchOut
            && AttendanceRegularizationSupport::timeToMinutes($effectivePunchIn) >= AttendanceRegularizationSupport::timeToMinutes($effectivePunchOut)) {
            $errors['requested_punch_out'] = 'Punch-out time must be later than punch-in time on the same date.';
        }

        if (AttendanceRegularizationSupport::exceedsMaxDuration($effectivePunchIn, $effectivePunchOut)) {
            $errors['requested_punch_out'] = 'The requested duration exceeds the configured shift limit.';
        }

        if (AttendanceRegularizationSupport::isFutureTime($validated['date'], $requestedPunchIn)) {
            $errors['requested_punch_in'] = 'Future punch-in times are not allowed.';
        }

        if (AttendanceRegularizationSupport::isFutureTime($validated['date'], $requestedPunchOut)) {
            $errors['requested_punch_out'] = 'Future punch-out times are not allowed.';
        }

        $correctionNeeded = ($originalPunchOut === null && $requestedPunchOut)
            || ($requestedPunchIn && $requestedPunchIn !== $originalPunchIn)
            || ($requestedPunchOut && $requestedPunchOut !== $originalPunchOut);

        if (! $correctionNeeded) {
            $errors['requested_punch_in'] = 'A regularization request is only allowed when punch data is missing or needs correction.';
        }

        $duplicateExists = AttendanceRegularization::where('user_id', $user->id)
            ->where('date', $validated['date'])
            ->when($regularization, fn ($query) => $query->where('id', '!=', $regularization->id))
            ->whereIn('status', ['draft', 'pending_l1', 'pending_l2'])
            ->exists();

        if ($duplicateExists) {
            $errors['date'] = 'A draft or pending regularization request already exists for this date.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return [
            'attendance' => $attendance,
            'date' => $validated['date'],
            'request_type' => $validated['request_type'],
            'original_punch_in' => $originalPunchIn,
            'original_punch_out' => $originalPunchOut,
            'requested_punch_in' => $requestedPunchIn,
            'requested_punch_out' => $requestedPunchOut,
            'reason' => $validated['reason'],
        ];
    }

    protected function submitRegularization(AttendanceRegularization $regularization, User $user): RedirectResponse
    {
        if (! $regularization->isEditable()) {
            return back()->with('error', 'This request cannot be submitted in its current status.');
        }

        $targetManager = $regularization->l1Manager ?: $regularization->l2Manager;
        $nextStatus = $regularization->l1_manager_id ? 'pending_l1' : 'pending_l2';

        if (! $targetManager) {
            return back()->with('error', 'No approval manager is configured for this employee.');
        }

        $oldValues = $regularization->only(['status', 'submitted_at']);
        $regularization->update([
            'status' => $nextStatus,
            'submitted_at' => now(),
            'l1_comment' => null,
            'l2_comment' => null,
        ]);

        AuditLog::record('attendance_regularization', 'submitted', $regularization->id, $user->id, $oldValues, [
            'status' => $regularization->status,
            'submitted_at' => $regularization->submitted_at,
        ]);

        // Transition attendance status: missed_punch_out → pending_regularization
        if ($regularization->attendance_id) {
            $attendance = $regularization->attendance ?? \App\Models\Attendance::find($regularization->attendance_id);
            if ($attendance && $attendance->status === 'missed_punch_out') {
                $attendance->update(['status' => 'pending_regularization']);
                AuditLog::record('attendance', 'marked_pending_regularization', $attendance->id, $user->id, ['status' => 'missed_punch_out'], ['status' => 'pending_regularization']);
            }
        }

        $regularization->loadMissing(['user', 'l1Manager', 'l2Manager']);
        $reviewUrl = route('manager.regularizations.show', $regularization);
        $targetManager->notify(new AttendanceRegularizationSubmittedNotification($regularization, $reviewUrl));

        AppNotification::notify(
            $targetManager->id,
            'Attendance Regularization Submitted',
            $regularization->user->name . ' submitted a regularization request for ' . $regularization->date->format('d M Y') . ' (' . $regularization->requested_times_label . ').',
            'attendance_regularization',
            $regularization->id,
            $reviewUrl
        );

        return redirect()->route($this->selfRouteData($user)['showRoute'], $regularization)
            ->with('success', 'Attendance regularization submitted successfully.');
    }

    protected function notifyCommentParticipants(AttendanceRegularization $regularization, User $author, string $comment): void
    {
        $snippet = mb_strlen($comment) > 120 ? mb_substr($comment, 0, 120) . '...' : $comment;

        $participants = User::whereIn('id', array_filter([
            $regularization->user_id,
            $regularization->l1_manager_id,
            $regularization->l2_manager_id,
        ]))
            ->where('id', '!=', $author->id)
            ->get();

        foreach ($participants as $participant) {
            $url = $participant->id === $regularization->user_id
                ? $this->selfRequestUrl($regularization)
                : route('manager.regularizations.show', $regularization);

            $participant->notify(new AttendanceRegularizationCommentNotification($regularization, $author, $comment, $url));

            AppNotification::notify(
                $participant->id,
                'Comment on Attendance Regularization',
                $author->name . ' commented on ' . $regularization->date->format('d M Y') . ': "' . $snippet . '"',
                'attendance_regularization',
                $regularization->id,
                $url
            );
        }
    }

    protected function snapshotPayload(string $date, ?Attendance $attendance): array
    {
        return [
            'date' => $date,
            'formatted_date' => Carbon::parse($date)->format('d M Y'),
            'system_punch_in' => AttendanceRegularizationSupport::normalizeTime($attendance?->punch_in),
            'system_punch_out' => AttendanceRegularizationSupport::normalizeTime($attendance?->punch_out),
            'work_hours' => $attendance?->formatted_work_hours ?? '-',
        ];
    }
}
