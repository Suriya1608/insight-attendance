<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\AttendanceRegularization;
use App\Models\AttendanceRegularizationComment;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\AttendanceRegularizationApprovedNotification;
use App\Notifications\AttendanceRegularizationCommentNotification;
use App\Notifications\AttendanceRegularizationL1ApprovedNotification;
use App\Notifications\AttendanceRegularizationRejectedNotification;
use App\Support\AttendanceRegularizationSupport;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceRegularizationController extends Controller
{
    public function index(Request $request)
    {
        $manager = $this->currentUser();
        $teamIds = $this->teamMemberIds($manager->id);
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->endOfMonth()->toDateString());

        try {
            $from = Carbon::parse($dateFrom)->startOfDay();
            $to = Carbon::parse($dateTo)->endOfDay();
        } catch (\Throwable $e) {
            $from = now()->startOfMonth();
            $to = now()->endOfMonth();
            $dateFrom = $from->toDateString();
            $dateTo = $to->toDateString();
        }

        $query = AttendanceRegularization::whereIn('user_id', $teamIds)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->with(['user', 'attendance']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($employeeId = $request->input('employee_id')) {
            if (in_array((int) $employeeId, $teamIds, true)) {
                $query->where('user_id', $employeeId);
            }
        }

        $regularizations = $query->orderByDesc('date')->orderByDesc('created_at')->paginate(20)->withQueryString();
        $teamMembers = $this->teamMembers($manager->id);
        $pendingCount = AttendanceRegularization::pendingApprovalBy($manager->id)->count();
        $stats = [
            'pending' => AttendanceRegularization::whereIn('user_id', $teamIds)->whereIn('status', ['pending_l1', 'pending_l2'])->count(),
            'approved' => AttendanceRegularization::whereIn('user_id', $teamIds)->where('status', 'approved')->count(),
            'rejected' => AttendanceRegularization::whereIn('user_id', $teamIds)->where('status', 'rejected')->count(),
        ];

        return view('manager.attendance-regularizations.index', compact(
            'regularizations',
            'teamMembers',
            'pendingCount',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }

    public function show(AttendanceRegularization $regularization)
    {
        $manager = $this->currentUser();

        if (! $this->canViewRegularization($manager->id, $regularization)) {
            abort(403);
        }

        $regularization->load(['user', 'attendance', 'comments.user', 'comments.replies.user', 'l1Manager', 'l2Manager']);

        return view('manager.attendance-regularizations.show', compact('regularization', 'manager'));
    }

    public function approve(Request $request, AttendanceRegularization $regularization): RedirectResponse
    {
        $manager = $this->currentUser();

        if (! $this->canViewRegularization($manager->id, $regularization)) {
            abort(403);
        }

        $request->validate(['comment' => 'nullable|string|max:1000']);

        $isL1 = $regularization->l1_manager_id === $manager->id && $regularization->status === 'pending_l1';
        $isL2 = $regularization->l2_manager_id === $manager->id && $regularization->status === 'pending_l2';

        if (! $isL1 && ! $isL2) {
            return back()->with('error', 'You cannot approve this request at this stage.');
        }

        $regularization->loadMissing(['user', 'l2Manager']);
        $requesterUrl = $this->selfRequestUrl($regularization);

        if ($isL1) {
            $oldValues = $regularization->only(['status', 'l1_comment', 'l1_actioned_at']);
            $regularization->update([
                'status' => $regularization->l2_manager_id ? 'pending_l2' : 'approved',
                'l1_comment' => $request->comment,
                'l1_actioned_at' => now(),
            ]);

            AuditLog::record('attendance_regularization', 'approved_l1', $regularization->id, $regularization->user_id, $oldValues, [
                'status' => $regularization->status,
                'l1_comment' => $regularization->l1_comment,
                'l1_actioned_at' => $regularization->l1_actioned_at,
            ]);

            if ($regularization->status === 'pending_l2' && $regularization->l2Manager) {
                $reviewUrl = route('manager.regularizations.show', $regularization);
                $regularization->l2Manager->notify(new AttendanceRegularizationL1ApprovedNotification($regularization, $reviewUrl));

                AppNotification::notify(
                    $regularization->l2_manager_id,
                    'Attendance Regularization Awaiting Final Approval',
                    $regularization->user->name . "'s request for " . $regularization->date->format('d M Y') . ' now requires your review.',
                    'attendance_regularization',
                    $regularization->id,
                    $reviewUrl
                );

                return back()->with('success', 'Approved at L1 and forwarded to L2.');
            }
        }

        $attendanceBefore = AttendanceRegularizationSupport::currentAttendanceFor($regularization->user_id, $regularization->date->toDateString());
        $attendanceOld = $attendanceBefore ? $attendanceBefore->only(['punch_in', 'punch_out', 'work_hours', 'status']) : null;
        $updatedAttendance = AttendanceRegularizationSupport::applyFinalApproval($regularization);

        $oldValues = $regularization->only(['status', 'l2_comment', 'l2_actioned_at', 'finalized_at']);
        $regularization->update([
            'status' => 'approved',
            'l2_comment' => $isL2 ? $request->comment : $regularization->l2_comment,
            'l2_actioned_at' => $isL2 ? now() : ($regularization->l2_actioned_at ?: now()),
            'finalized_at' => now(),
        ]);

        AuditLog::record('attendance_regularization', 'approved', $regularization->id, $regularization->user_id, $oldValues, [
            'status' => $regularization->status,
            'l2_comment' => $regularization->l2_comment,
            'l2_actioned_at' => $regularization->l2_actioned_at,
            'finalized_at' => $regularization->finalized_at,
        ]);

        AuditLog::record('attendance', 'regularization_applied', $updatedAttendance->id, $regularization->user_id, $attendanceOld, [
            'punch_in' => $updatedAttendance->punch_in,
            'punch_out' => $updatedAttendance->punch_out,
            'work_hours' => $updatedAttendance->work_hours,
            'status' => $updatedAttendance->status,
            'regularization_id' => $regularization->id,
        ]);

        $regularization->user->notify(new AttendanceRegularizationApprovedNotification($regularization, $requesterUrl));
        AppNotification::notify(
            $regularization->user_id,
            'Attendance Regularization Approved',
            'Your request for ' . $regularization->date->format('d M Y') . ' was approved. Requested times: ' . $regularization->requested_times_label . '.',
            'attendance_regularization',
            $regularization->id,
            $requesterUrl
        );

        return back()->with('success', 'Attendance regularization approved and attendance updated.');
    }

    public function reject(Request $request, AttendanceRegularization $regularization): RedirectResponse
    {
        $manager = $this->currentUser();

        if (! $this->canViewRegularization($manager->id, $regularization)) {
            abort(403);
        }

        $request->validate(['comment' => 'required|string|max:1000']);

        $isL1 = $regularization->l1_manager_id === $manager->id && $regularization->status === 'pending_l1';
        $isL2 = $regularization->l2_manager_id === $manager->id && $regularization->status === 'pending_l2';

        if (! $isL1 && ! $isL2) {
            return back()->with('error', 'You cannot reject this request at this stage.');
        }

        $oldValues = $regularization->only(['status', 'l1_comment', 'l2_comment', 'l1_actioned_at', 'l2_actioned_at']);
        $regularization->update([
            'status' => 'rejected',
            'l1_comment' => $isL1 ? $request->comment : $regularization->l1_comment,
            'l2_comment' => $isL2 ? $request->comment : $regularization->l2_comment,
            'l1_actioned_at' => $isL1 ? now() : $regularization->l1_actioned_at,
            'l2_actioned_at' => $isL2 ? now() : $regularization->l2_actioned_at,
        ]);

        AuditLog::record('attendance_regularization', 'rejected', $regularization->id, $regularization->user_id, $oldValues, [
            'status' => $regularization->status,
            'l1_comment' => $regularization->l1_comment,
            'l2_comment' => $regularization->l2_comment,
            'l1_actioned_at' => $regularization->l1_actioned_at,
            'l2_actioned_at' => $regularization->l2_actioned_at,
        ]);

        $level = $isL1 ? 'L1 Manager' : 'L2 Manager';
        $requesterUrl = $this->selfRequestUrl($regularization);

        $regularization->user->notify(new AttendanceRegularizationRejectedNotification(
            $regularization,
            $requesterUrl,
            $manager->name . ' (' . $level . ')',
            $request->comment
        ));

        AppNotification::notify(
            $regularization->user_id,
            'Attendance Regularization Rejected',
            'Your request for ' . $regularization->date->format('d M Y') . ' was rejected by ' . $manager->name . '. Comment: "' . $request->comment . '"',
            'attendance_regularization',
            $regularization->id,
            $requesterUrl
        );

        return back()->with('success', 'Attendance regularization rejected.');
    }

    public function addComment(Request $request, AttendanceRegularization $regularization): RedirectResponse
    {
        $manager = $this->currentUser();

        if (! $this->canViewRegularization($manager->id, $regularization)) {
            abort(403);
        }

        $request->validate([
            'comment' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:attendance_regularization_comments,id',
        ]);

        AttendanceRegularizationComment::create([
            'attendance_regularization_id' => $regularization->id,
            'user_id' => $manager->id,
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        AuditLog::record('attendance_regularization', 'commented', $regularization->id, $regularization->user_id, null, [
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        $this->notifyCommentParticipants($regularization->fresh(['user', 'l1Manager', 'l2Manager']), $manager, $request->comment);

        return back()->with('success', 'Comment added.');
    }

    private function notifyCommentParticipants(AttendanceRegularization $regularization, User $author, string $comment): void
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

    private function selfRequestUrl(AttendanceRegularization $regularization): string
    {
        $route = $regularization->user->isManager() ? 'manager.my-regularizations.show' : 'employee.regularizations.show';

        return route($route, $regularization);
    }

    private function canViewRegularization(int $managerId, AttendanceRegularization $regularization): bool
    {
        return in_array($regularization->user_id, $this->teamMemberIds($managerId), true);
    }

    private function teamMembers(int $managerId)
    {
        return User::where(function ($q) use ($managerId) {
            $q->where('level1_manager_id', $managerId)
                ->orWhere('level2_manager_id', $managerId);
        })->where('emp_status', 'active')->orderBy('name')->get();
    }

    private function teamMemberIds(int $managerId): array
    {
        return User::where(function ($q) use ($managerId) {
            $q->where('level1_manager_id', $managerId)
                ->orWhere('level2_manager_id', $managerId);
        })->where('emp_status', 'active')->pluck('id')->toArray();
    }
}
